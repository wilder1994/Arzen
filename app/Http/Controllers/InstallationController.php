<?php

namespace App\Http\Controllers;

use App\Events\AssignmentChanged;
use App\Events\MapDataChanged;
use App\Events\PostChanged;
use App\Events\WorkerChanged;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Installation;
use App\Models\Post;
use App\Models\PostHistory;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponPostAssignment;
use App\Models\Worker;
use App\Models\WorkerHistory;
use App\Services\WeaponHistoryService;
use App\Support\Installations\InstallationAudit;
use App\Support\MapCoordinates;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class InstallationController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $this->authorize('viewAny', Post::class);

        $user = $request->user();
        $search = trim((string) $request->input('q', ''));

        $query = Installation::query()
            ->with('client:id,name')
            ->withCount(['posts as active_posts_count' => fn ($posts) => $posts->active()]);

        if ($user?->isResponsible() && ! $user->isAdmin()) {
            $query->whereIn('client_id', $user->clients()->select('clients.id'));
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('installations.name', 'like', '%'.$search.'%')
                    ->orWhereHas('client', fn ($clients) => $clients->where('name', 'like', '%'.$search.'%'));
            });
        }

        $installations = $query
            ->select('installations.*')
            ->join('clients', 'clients.id', '=', 'installations.client_id')
            ->orderBy('clients.name')
            ->orderByRaw('CASE WHEN installations.archived_at IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('installations.is_client_site')
            ->orderBy('installations.name')
            ->paginate(20)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json([
                'results' => view('installations.partials.index_results', compact('installations', 'search'))->render(),
            ]);
        }

        return view('installations.index', [
            'installations' => $installations,
            'search' => $search,
        ]);
    }

    public function begin(Request $request): View
    {
        $this->authorize('create', Post::class);

        $clients = $this->selectableClients($request);
        $client = $clients->firstWhere('id', $request->integer('client_id'));

        return view('installations.create', [
            'clients' => $clients,
            'client' => $client,
        ]);
    }

    public function save(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'client_id' => ['required', 'integer', 'exists:clients,id'],
        ]);
        $client = Client::query()->findOrFail($data['client_id']);
        $this->authorize('update', $client);

        return $this->store($request, $client);
    }

    public function create(Client $client): View
    {
        $this->authorize('update', $client);

        return view('installations.form', [
            'client' => $client,
            'installation' => new Installation(['client_id' => $client->id]),
        ]);
    }

    public function store(Request $request, Client $client): RedirectResponse
    {
        $this->authorize('update', $client);

        $installation = new Installation(['client_id' => $client->id]);
        $this->fillFromRequest($installation, $request, $client);
        $installation->save();

        $this->audit($request, 'installation_created', $installation, null);
        event(new MapDataChanged('updated', $client->id));

        return redirect()->route('installations.edit', $installation)->with('status', __('Instalación creada.'));
    }

    public function edit(Installation $installation): View
    {
        $this->authorize('update', $installation->client);

        $installation->load([
            'client',
            'posts' => fn ($posts) => $posts
                ->orderByRaw('CASE WHEN archived_at IS NULL THEN 0 ELSE 1 END')
                ->orderBy('name')
                ->with([
                'workers' => fn ($workers) => $workers->active()->orderBy('name')->with('position:id,name'),
                'assignments' => fn ($assignments) => $assignments->where('is_active', true)->with([
                    'weapon:id,serial_number,weapon_type,caliber,permit_number',
                    'weapon.activeWorkerAssignment.worker:id,name',
                ]),
            ]),
        ]);

        return view('installations.show', [
            'client' => $installation->client,
            'installation' => $installation,
            'histories' => AuditLog::query()
                ->with('user:id,name')
                ->where('auditable_type', Installation::class)
                ->where('auditable_id', $installation->id)
                ->latest('id')
                ->limit(40)
                ->get(),
        ]);
    }

    public function editForm(Installation $installation): View|RedirectResponse
    {
        $this->authorize('update', $installation->client);

        if ($installation->is_client_site) {
            return redirect()->to($this->clientEditorUrl($installation));
        }

        return view('installations.form', [
            'client' => $installation->client,
            'installation' => $installation,
        ]);
    }

    public function update(Request $request, Installation $installation): RedirectResponse
    {
        $client = $installation->client;
        $this->authorize('update', $client);

        $before = $installation->only(['name', 'address', 'city', 'department']);
        $this->fillFromRequest($installation, $request, $client);
        $note = trim((string) ($request->validate([
            'change_note' => ['nullable', 'string', 'max:2000'],
        ])['change_note'] ?? ''));
        $installation->save();

        $this->audit($request, 'installation_updated', $installation, $before, $note);
        event(new MapDataChanged('updated', $client->id));

        return redirect()->route('installations.edit', $installation)->with('status', __('Instalación actualizada.'));
    }

    public function storePost(Request $request, Installation $installation): RedirectResponse
    {
        $this->authorize('update', $installation->client);
        $this->authorize('create', Post::class);

        $data = $request->validate([
            'post_name' => ['required', 'string', 'max:255', $this->postNameRule($installation)],
        ]);

        $post = Post::query()->create([
            'installation_id' => $installation->id,
            'name' => $data['post_name'],
        ]);

        PostHistory::query()->create([
            'post_id' => $post->id,
            'user_id' => $request->user()?->id,
            'body' => __('Registro inicial.'),
        ]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'post_created',
            'auditable_type' => Post::class,
            'auditable_id' => $post->id,
            'before' => null,
            'after' => $post->only(['client_id', 'installation_id', 'name']),
        ]);

        event(new PostChanged('created', $post->id, ['client_id' => $post->client_id]));
        $this->noteMovement($request, $installation, __('Puesto creado: :name.', ['name' => $post->name]));

        return redirect()->to(route('installations.edit', $installation).'#puesto-'.$post->id)
            ->with('status', __('Puesto creado.'));
    }

    public function renamePost(Request $request, Installation $installation, Post $post): RedirectResponse
    {
        $post = $this->postOnInstallation($installation, $post);
        $this->authorize('update', $post);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', $this->postNameRule($installation, $post)],
        ]);

        $before = $post->name;
        if ($before === $data['name']) {
            return redirect()->to(route('installations.edit', $installation).'#puesto-'.$post->id);
        }

        $post->update(['name' => $data['name']]);

        PostHistory::query()->create([
            'post_id' => $post->id,
            'user_id' => $request->user()?->id,
            'body' => __('Nombre: :from → :to', ['from' => $before, 'to' => $post->name]),
        ]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'post_updated',
            'auditable_type' => Post::class,
            'auditable_id' => $post->id,
            'before' => ['name' => $before],
            'after' => ['name' => $post->name],
        ]);

        event(new PostChanged('updated', $post->id, ['client_id' => $post->client_id]));
        $this->noteMovement($request, $installation, __('Puesto renombrado: :from → :to.', ['from' => $before, 'to' => $post->name]));

        return redirect()->to(route('installations.edit', $installation).'#puesto-'.$post->id)
            ->with('status', __('Puesto actualizado.'));
    }

    public function searchWorkers(Request $request, Installation $installation): JsonResponse
    {
        $this->authorize('update', $installation->client);

        $term = trim((string) $request->input('q', ''));
        $post = $this->optionalPost($installation, $request->integer('post'));

        if (mb_strlen($term) < 2) {
            return response()->json(['workers' => []]);
        }

        $workers = $this->crewQuery($request, $installation)
            ->with(['post:id,name,installation_id', 'post.installation:id,name', 'client:id,name'])
            ->where(function ($builder) use ($term) {
                $builder->where('name', 'like', '%'.$term.'%')
                    ->orWhere('document', 'like', '%'.$term.'%')
                    ->orWhere('first_names', 'like', '%'.$term.'%')
                    ->orWhere('last_name_paternal', 'like', '%'.$term.'%')
                    ->orWhere('last_name_maternal', 'like', '%'.$term.'%');
            })
            ->when($post, fn ($query) => $query->where(function ($builder) use ($post) {
                $builder->whereNull('post_id')->orWhere('post_id', '!=', $post->id);
            }))
            ->orderBy('name')
            ->limit(8)
            ->get();

        return response()->json([
            'workers' => $workers->map(fn (Worker $worker) => $this->workerSearchPayload($worker, $installation, $post))->all(),
        ]);
    }

    public function assignWorker(Request $request, Installation $installation, Post $post): RedirectResponse
    {
        $post = $this->postOnInstallation($installation, $post);
        $this->authorize('update', $installation->client);

        $worker = Worker::query()->active()->findOrFail($request->validate([
            'worker_id' => ['required', 'integer', 'exists:workers,id'],
            'confirm' => ['nullable', 'boolean'],
        ])['worker_id']);

        $this->guardCrewMove($request, $installation, $worker);

        $alreadyHere = (int) $worker->post_id === (int) $post->id
            && (int) $worker->client_id === (int) $installation->client_id;

        if ($alreadyHere) {
            return $this->backToPost($installation, $post);
        }

        $needsConfirm = ($worker->post_id && (int) $worker->post_id !== (int) $post->id)
            || ($worker->client_id && (int) $worker->client_id !== (int) $installation->client_id);

        if ($needsConfirm && ! $request->boolean('confirm')) {
            return $this->backToPost($installation, $post)->withErrors([
                'worker' => __('Confirme el traslado antes de mover al trabajador.'),
            ]);
        }

        $before = $worker->only(['client_id', 'post_id']);
        $fromPost = $worker->post_id ? (int) $worker->post_id : null;
        $worker->update([
            'client_id' => $installation->client_id,
            'post_id' => $post->id,
        ]);

        $this->recordWorkerMove($request, $worker, $before, $fromPost, $post->id);
        $this->noteMovement($request, $installation, __('Persona asignada al puesto :post: :name.', [
            'post' => $post->name,
            'name' => $worker->name,
        ]));

        return $this->backToPost($installation, $post)->with('status', __('Trabajador asignado al puesto.'));
    }

    public function detachWorker(Request $request, Installation $installation, Post $post, Worker $worker): RedirectResponse
    {
        $post = $this->postOnInstallation($installation, $post);
        $this->authorize('update', $installation->client);
        $this->guardCrewMove($request, $installation, $worker);

        if ((int) $worker->post_id !== (int) $post->id) {
            abort(404);
        }

        $before = $worker->only(['client_id', 'post_id']);
        $fromPost = (int) $worker->post_id;
        $worker->update(['post_id' => null]);
        $this->recordWorkerMove($request, $worker, $before, $fromPost, null);
        $this->noteMovement($request, $installation, __('Persona quitada del puesto :post: :name.', [
            'post' => $post->name,
            'name' => $worker->name,
        ]));

        return $this->backToPost($installation, $post)->with('status', __('Trabajador quitado del puesto.'));
    }

    public function searchWeapons(Request $request, Installation $installation): JsonResponse
    {
        $this->authorize('update', $installation->client);

        $term = trim((string) $request->input('q', ''));
        $post = $this->optionalPost($installation, $request->integer('post'));

        if (mb_strlen($term) < 2) {
            return response()->json(['weapons' => []]);
        }

        $weapons = Weapon::query()
            ->whereHas('activeClientAssignment', fn ($query) => $query->where('client_id', $installation->client_id))
            ->with(['activePostAssignment.post:id,name', 'activeWorkerAssignment.worker:id,name'])
            ->where(function ($builder) use ($term) {
                $builder->where('serial_number', 'like', '%'.$term.'%')
                    ->orWhere('permit_number', 'like', '%'.$term.'%')
                    ->orWhere('weapon_type', 'like', '%'.$term.'%')
                    ->orWhere('caliber', 'like', '%'.$term.'%');
            })
            ->when($post, fn ($query) => $query->whereDoesntHave(
                'activePostAssignment',
                fn ($assignment) => $assignment->where('post_id', $post->id)
            ))
            ->orderBy('serial_number')
            ->limit(8)
            ->get();

        return response()->json([
            'weapons' => $weapons->map(fn (Weapon $weapon) => $this->weaponSearchPayload($weapon))->all(),
        ]);
    }

    public function assignWeapon(Request $request, Installation $installation, Post $post): RedirectResponse
    {
        $post = $this->postOnInstallation($installation, $post);
        $this->authorize('update', $installation->client);

        $weapon = Weapon::query()->findOrFail($request->validate([
            'weapon_id' => ['required', 'integer', 'exists:weapons,id'],
            'confirm' => ['nullable', 'boolean'],
        ])['weapon_id']);

        $this->guardWeaponMove($request, $installation, $weapon);

        if ($message = $weapon->pendingTransferBlockMessage($request->user())) {
            return $this->backToPost($installation, $post)->withErrors(['weapon' => $message]);
        }

        if (! MapCoordinates::isFilled($post->latitude, $post->longitude)) {
            return $this->backToPost($installation, $post)->withErrors([
                'weapon' => __('El puesto no tiene ubicación. Márquela antes de asignar el arma.'),
            ]);
        }

        $activePost = $weapon->activePostAssignment()->first();
        if ($activePost && (int) $activePost->post_id === (int) $post->id) {
            return $this->backToPost($installation, $post);
        }

        if ($activePost && ! $request->boolean('confirm')) {
            return $this->backToPost($installation, $post)->withErrors([
                'weapon' => __('Confirme el traslado antes de mover el arma.'),
            ]);
        }

        $worker = $weapon->activeWorkerAssignment()->with('worker')->first()?->worker;
        $previous = $activePost;

        DB::transaction(function () use ($weapon, $post, $request, $previous) {
            if ($previous) {
                $previous->update([
                    'end_at' => now()->toDateString(),
                    'is_active' => null,
                ]);
            }

            WeaponPostAssignment::query()->create([
                'weapon_id' => $weapon->id,
                'post_id' => $post->id,
                'assigned_by' => $request->user()?->id,
                'start_at' => now()->toDateString(),
                'is_active' => true,
                'ammo_count' => $previous?->ammo_count,
                'provider_count' => $previous?->provider_count,
            ]);
        });

        app(WeaponHistoryService::class)->recordInternalAssignment(
            $weapon,
            $request->user(),
            $post,
            $worker,
            null,
            $previous?->ammo_count,
            $previous?->provider_count,
            $previous !== null,
        );

        event(new AssignmentChanged('assigned', $weapon->id, [
            'client_id' => $installation->client_id,
            'post_id' => $post->id,
            'worker_id' => $worker?->id,
        ]));
        $this->noteMovement($request, $installation, __('Arma asignada al puesto :post: :weapon.', [
            'post' => $post->name,
            'weapon' => $this->weaponLabel($weapon),
        ]));

        return $this->backToPost($installation, $post)->with('status', __('Arma asignada al puesto.'));
    }

    public function detachWeapon(Request $request, Installation $installation, Post $post, Weapon $weapon): RedirectResponse
    {
        $post = $this->postOnInstallation($installation, $post);
        $this->authorize('update', $installation->client);
        $this->guardWeaponMove($request, $installation, $weapon);

        $assignment = $weapon->activePostAssignment()->where('post_id', $post->id)->first();
        if (! $assignment) {
            abort(404);
        }

        if ($message = $weapon->pendingTransferBlockMessage($request->user())) {
            return $this->backToPost($installation, $post)->withErrors(['weapon' => $message]);
        }

        $assignment->update([
            'end_at' => now()->toDateString(),
            'is_active' => null,
        ]);

        $worker = $weapon->activeWorkerAssignment()->with('worker')->first()?->worker;
        app(WeaponHistoryService::class)->recordInternalAssignment(
            $weapon,
            $request->user(),
            null,
            $worker,
            __('Retirada del puesto :name.', ['name' => $post->name]),
            null,
            null,
            true,
        );

        event(new AssignmentChanged('unassigned', $weapon->id, [
            'client_id' => $installation->client_id,
            'post_id' => $post->id,
            'worker_id' => $worker?->id,
        ]));
        $this->noteMovement($request, $installation, __('Arma quitada del puesto :post: :weapon.', [
            'post' => $post->name,
            'weapon' => $this->weaponLabel($weapon),
        ]));

        return $this->backToPost($installation, $post)->with('status', __('Arma quitada del puesto.'));
    }

    public function archive(Request $request, Installation $installation): RedirectResponse
    {
        $client = $installation->client;
        $this->authorize('update', $client);

        if ($installation->is_client_site) {
            return back()->withErrors(['installation' => __('La instalación principal no se puede archivar.')]);
        }

        if ($installation->posts()->active()->exists()) {
            return back()->withErrors(['installation' => __('Archive primero los puestos activos de esta instalación.')]);
        }

        $installation->update(['archived_at' => now()]);
        $this->audit($request, 'installation_archived', $installation, null, __('Instalación archivada.'));

        return back()->with('status', __('Instalación archivada.'));
    }

    public function restore(Request $request, Installation $installation): RedirectResponse
    {
        $this->authorize('update', $installation->client);

        $installation->update(['archived_at' => null]);
        $this->audit($request, 'installation_restored', $installation, null, __('Instalación reactivada.'));

        return back()->with('status', __('Instalación restaurada.'));
    }

    /**
     * @return \Illuminate\Support\Collection<int, Client>
     */
    private function selectableClients(Request $request)
    {
        $query = Client::query()->active()->orderBy('name');
        $user = $request->user();

        if ($user?->isResponsible() && ! $user->isAdmin()) {
            $query->whereIn('id', $user->clients()->select('clients.id'));
        }

        return $query->get(['id', 'name', 'address', 'city', 'department']);
    }

    private function fillFromRequest(Installation $installation, Request $request, Client $client): void
    {
        if ($installation->is_client_site) {
            $data = $request->validate([
                'code' => ['nullable', 'string', 'max:40'],
                'notes' => ['nullable', 'string', 'max:2000'],
            ]);
            $installation->fill($data);

            return;
        }

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'code' => ['nullable', 'string', 'max:40'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'same_as_client' => ['nullable', 'boolean'],
            'address' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'city' => ['exclude_if:same_as_client,1', 'required', 'string', 'max:120'],
            'department' => ['exclude_if:same_as_client,1', 'required', 'string', 'max:120'],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'coords_source' => ['nullable', 'string', 'max:20'],
        ]);

        $installation->fill([
            'name' => $data['name'],
            'code' => $data['code'] ?? null,
            'notes' => $data['notes'] ?? null,
        ]);

        if ($request->boolean('same_as_client')) {
            $installation->fill($client->only(Installation::LOCATION_FIELDS));

            return;
        }

        $location = MapCoordinates::apply(collect($data)->only(array_merge(Installation::LOCATION_FIELDS, ['coords_source']))->all());
        $installation->fill($location);
    }

    /**
     * @param  array<string, mixed>|null  $before
     */
    private function audit(Request $request, string $action, Installation $installation, ?array $before, ?string $note = null): void
    {
        InstallationAudit::record($request->user()?->id, $action, $installation, $before, $note);
    }

    public static function clientEditorUrl(Installation $installation): string
    {
        $client = $installation->client;

        if ($client?->is_own_company) {
            return route('company.edit', ['return_installation' => $installation->id]);
        }

        return route('clients.edit', [$client, 'return_installation' => $installation->id]);
    }

    private function postNameRule(Installation $installation, ?Post $ignore = null)
    {
        $rule = Rule::unique('posts', 'name')->where(
            fn ($query) => $query->where('installation_id', $installation->id)->whereNull('archived_at')
        );

        return $ignore ? $rule->ignore($ignore->id) : $rule;
    }

    private function postOnInstallation(Installation $installation, Post $post): Post
    {
        if ((int) $post->installation_id !== (int) $installation->id || $post->isArchived()) {
            abort(404);
        }

        return $post;
    }

    private function optionalPost(Installation $installation, int $postId): ?Post
    {
        if ($postId === 0) {
            return null;
        }

        return $this->postOnInstallation($installation, Post::query()->findOrFail($postId));
    }

    private function crewQuery(Request $request, Installation $installation)
    {
        $query = Worker::query()->active();
        $user = $request->user();

        if ($user instanceof User && $user->isResponsible() && ! $user->isAdmin()) {
            $portfolio = $user->clients()->select('clients.id');
            $query->where(function ($builder) use ($portfolio) {
                $builder->whereNull('client_id')->orWhereIn('client_id', $portfolio);
            });
        }

        return $query;
    }

    private function guardCrewMove(Request $request, Installation $installation, Worker $worker): void
    {
        if ($worker->isArchived()) {
            abort(404);
        }

        $user = $request->user();

        if (! $user instanceof User || ! ($user->isAdmin() || $user->isResponsibleLevelOne())) {
            abort(403);
        }

        if ($user->isAdmin()) {
            return;
        }

        $portfolio = $user->clients()->whereKey($installation->client_id)->exists();
        $ownsWorker = $worker->client_id === null || $user->clients()->whereKey($worker->client_id)->exists();

        if (! $portfolio || ! $ownsWorker) {
            abort(403);
        }
    }

    /**
     * @return array{id: int, label: string, hint: string, needs_confirm: bool, confirm: string}
     */
    private function workerSearchPayload(Worker $worker, Installation $installation, ?Post $post): array
    {
        $label = trim($worker->name.($worker->document ? ' · '.$worker->document : ''));
        $where = collect([$worker->post?->name, $worker->client?->name])->filter()->implode(' · ');
        $needsConfirm = ($worker->post_id && (int) $worker->post_id !== (int) $post?->id)
            || ($worker->client_id && (int) $worker->client_id !== (int) $installation->client_id);

        return [
            'id' => $worker->id,
            'label' => $label,
            'hint' => $where !== '' ? $where : __('Sin puesto'),
            'needs_confirm' => $needsConfirm,
            'confirm' => __('¿Mover a :name a este puesto? Queda en esta instalación y en este cliente.', ['name' => $worker->name]),
        ];
    }

    /**
     * @return array{id: int, label: string, hint: string, needs_confirm: bool, confirm: string}
     */
    private function weaponSearchPayload(Weapon $weapon): array
    {
        $label = collect([$weapon->weapon_type, $weapon->serial_number, $weapon->caliber, $weapon->permit_number])->filter()->implode(' · ');
        $postName = $weapon->activePostAssignment?->post?->name;
        $responsible = $weapon->activeWorkerAssignment?->worker?->name;
        $hint = collect([
            $postName,
            $responsible ? __('Responsable: :name', ['name' => $responsible]) : null,
        ])->filter()->implode(' · ');

        return [
            'id' => $weapon->id,
            'label' => $label !== '' ? $label : __('Arma'),
            'hint' => $hint !== '' ? $hint : __('Sin puesto'),
            'needs_confirm' => $postName !== null,
            'confirm' => $postName
                ? __('¿Pasar esta arma desde :post a este puesto?', ['post' => $postName])
                : '',
        ];
    }

    private function guardWeaponMove(Request $request, Installation $installation, Weapon $weapon): void
    {
        $user = $request->user();
        $assignment = $weapon->activeClientAssignment()->first();

        if (! $user instanceof User || ! ($user->isAdmin() || $user->isResponsibleLevelOne())) {
            abort(403);
        }

        if (! $assignment || (int) $assignment->client_id !== (int) $installation->client_id) {
            abort(403, __('El arma no está en este cliente.'));
        }

        if ($user->isAdmin()) {
            return;
        }

        if ((int) $assignment->responsible_user_id !== (int) $user->id) {
            abort(403, __('Solo el responsable del arma puede asignarla.'));
        }
    }

    /**
     * @param  array<string, mixed>  $before
     */
    private function recordWorkerMove(Request $request, Worker $worker, array $before, ?int $fromPostId, ?int $toPostId): void
    {
        $name = fn (?int $id) => $id ? (Post::query()->find($id)?->name ?? '—') : __('sin puesto');

        WorkerHistory::query()->create([
            'worker_id' => $worker->id,
            'user_id' => $request->user()?->id,
            'body' => __('Puesto: :from → :to', ['from' => $name($fromPostId), 'to' => $name($toPostId)]),
        ]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'worker_updated',
            'auditable_type' => Worker::class,
            'auditable_id' => $worker->id,
            'before' => $before,
            'after' => $worker->only(['client_id', 'post_id']),
        ]);

        event(new WorkerChanged('updated', $worker->id, ['client_id' => $worker->client_id]));
    }

    private function backToPost(Installation $installation, Post $post): RedirectResponse
    {
        return redirect()->to(route('installations.edit', $installation).'#puesto-'.$post->id);
    }

    private function noteMovement(Request $request, Installation $installation, string $detail): void
    {
        InstallationAudit::movement($request->user()?->id, $installation, $detail);
    }

    private function weaponLabel(Weapon $weapon): string
    {
        $label = collect([$weapon->weapon_type, $weapon->serial_number, $weapon->caliber])->filter()->implode(' · ');

        return $label !== '' ? $label : __('Arma');
    }
}
