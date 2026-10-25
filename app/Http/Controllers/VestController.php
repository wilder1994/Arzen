<?php

namespace App\Http\Controllers;

use App\Events\VestChanged;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Post;
use App\Models\User;
use App\Models\Vest;
use App\Models\VestPhoto;
use App\Models\Worker;
use App\Services\VestPhotoService;
use App\Services\VestQueryService;
use App\Support\VestAlert;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class VestController extends Controller
{
    public function __construct(
        private readonly VestQueryService $queryService,
        private readonly VestPhotoService $photoService,
    ) {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            abort_unless($user && $user->canAccessVestModule(), 403);

            return $next($request);
        });
    }

    public function index(Request $request)
    {
        $this->authorize('viewAny', Vest::class);

        $filters = $this->filtersFromRequest($request);
        $vests = $this->queryService
            ->buildIndexQuery($request->user(), $filters)
            ->paginate(20)
            ->withQueryString();

        $user = $request->user();
        $clients = $user->hasGlobalVestScope()
            ? Client::orderBy('name')->get(['id', 'name'])
            : $user->clients()->orderBy('name')->get(['clients.id', 'clients.name']);

        return view('vests.index', [
            'vests' => $vests,
            'filters' => $filters,
            'kpiCards' => $this->queryService->kpiCards($user),
            'clients' => $clients,
            'alertLabels' => VestAlert::ALERT_LABELS,
        ]);
    }

    public function show(Vest $vest)
    {
        $this->authorize('view', $vest);

        $vest->load(['client', 'worker', 'post', 'photos.file']);

        return view('vests.show', [
            'vest' => $vest,
            'alert' => VestAlert::forVest($vest),
            'photosByDescription' => $vest->photos->keyBy('description'),
        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Vest::class);

        return view('vests.create', $this->formOptions($request));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Vest::class);

        $data = $this->validated($request);
        $this->assertClientInPortfolio($request->user(), (int) $data['client_id']);

        $photos = $request->file('photos', []);
        unset($data['photos']);

        $vest = DB::transaction(function () use ($data, $photos, $request) {
            $vest = Vest::create($data);
            $this->photoService->storeIndexedPhotos($vest, $photos, $request->user());

            return $vest;
        });

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'vest_created',
            'auditable_type' => Vest::class,
            'auditable_id' => $vest->id,
            'before' => null,
            'after' => $vest->only(['serial_number', 'client_id', 'worker_id', 'post_id']),
        ]);

        event(new VestChanged('created', $vest->id, ['client_id' => $vest->client_id]));

        return redirect()
            ->route('vests.show', $vest)
            ->with('status', __('Chaleco registrado correctamente.'));
    }

    public function edit(Request $request, Vest $vest)
    {
        $this->authorize('update', $vest);

        $vest->load(['client', 'photos.file']);

        return view('vests.edit', array_merge(
            [
                'vest' => $vest,
                'photosByDescription' => $vest->photos->keyBy('description'),
            ],
            $this->formOptions($request, $vest)
        ));
    }

    public function update(Request $request, Vest $vest)
    {
        $this->authorize('update', $vest);

        $data = $this->validated($request, $vest);
        $this->assertClientInPortfolio($request->user(), (int) $data['client_id']);

        $before = $vest->only(['serial_number', 'client_id', 'worker_id', 'post_id', 'expires_at']);
        $vest->update($data);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'vest_updated',
            'auditable_type' => Vest::class,
            'auditable_id' => $vest->id,
            'before' => $before,
            'after' => $vest->fresh()->only(['serial_number', 'client_id', 'worker_id', 'post_id', 'expires_at']),
        ]);

        event(new VestChanged('updated', $vest->id, ['client_id' => $vest->client_id]));

        return redirect()
            ->route('vests.show', $vest)
            ->with('status', __('Chaleco actualizado correctamente.'));
    }

    public function formOptionsJson(Request $request)
    {
        $this->authorize('create', Vest::class);

        $clientId = (int) $request->query('client_id');
        abort_unless($clientId > 0, 422);

        $user = $request->user();
        $this->assertClientInPortfolio($user, $clientId);

        return response()->json([
            'workers' => $this->workersForClient($clientId),
            'posts' => $this->postsForClient($clientId),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function formOptions(Request $request, ?Vest $vest = null): array
    {
        $user = $request->user();
        $clients = $user->canManageAllVests()
            ? Client::orderBy('name')->get(['id', 'name'])
            : $user->clients()->orderBy('name')->get(['clients.id', 'clients.name']);

        $clientId = old('client_id', $vest?->client_id);
        $workers = $clientId
            ? Worker::query()->active()->where('client_id', $clientId)->with('position:id,name')->orderBy('name')->get(['id', 'name', 'document', 'position_id'])
            : collect();
        $posts = $clientId
            ? Post::query()->where('client_id', $clientId)->whereNull('archived_at')->orderBy('name')->get(['id', 'name', 'address'])
            : collect();

        $lockDeviceResponsible = $user && ! $user->canManageAllVests() && $user->isResponsibleLevelOne();
        $clientResponsibleMap = $this->buildClientResponsibleMap($clients);

        return compact('clients', 'workers', 'posts', 'clientId', 'lockDeviceResponsible', 'clientResponsibleMap');
    }

    /**
     * @param  iterable<int, Client>  $clients
     * @return array<int, array{id: int|null, name: string|null}>
     */
    private function buildClientResponsibleMap(iterable $clients): array
    {
        $clientIds = collect($clients)->pluck('id')->filter()->values();

        if ($clientIds->isEmpty()) {
            return [];
        }

        return Client::query()
            ->whereIn('id', $clientIds)
            ->with([
                'users' => function ($query) {
                    $query->whereIn('role', ['RESPONSABLE', 'ADMIN'])
                        ->orderByRaw("CASE WHEN role = 'RESPONSABLE' THEN 0 WHEN role = 'ADMIN' THEN 1 ELSE 2 END")
                        ->orderBy('name');
                },
            ])
            ->get()
            ->mapWithKeys(function (Client $client) {
                $responsible = $client->users->first();

                return [
                    $client->id => [
                        'id' => $responsible?->id,
                        'name' => $responsible?->name,
                    ],
                ];
            })
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function workersForClient(int $clientId): array
    {
        return Worker::query()
            ->active()
            ->where('client_id', $clientId)
            ->orderBy('name')
            ->with('position:id,name')
            ->get(['id', 'name', 'document', 'position_id'])
            ->map(function (Worker $worker) {
                $roleLabel = $worker->positionLabel();

                return [
                    'id' => $worker->id,
                    'name' => $worker->name,
                    'document' => $worker->document,
                    'role_label' => $roleLabel,
                    'search_text' => trim(implode(' ', array_filter([
                        $worker->name,
                        $worker->document,
                        $roleLabel,
                    ]))),
                ];
            })
            ->values()
            ->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function postsForClient(int $clientId): array
    {
        return Post::query()
            ->where('client_id', $clientId)
            ->whereNull('archived_at')
            ->orderBy('name')
            ->get(['id', 'name', 'address'])
            ->map(fn (Post $post) => [
                'id' => $post->id,
                'name' => $post->name,
                'address' => $post->address,
                'search_text' => trim(implode(' ', array_filter([$post->name, $post->address]))),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Vest $vest = null): array
    {
        $user = $request->user();

        $clientRules = ['required', 'exists:clients,id'];
        if ($user->isResponsibleLevelOne() && ! $user->canManageAllVests()) {
            $clientRules[] = Rule::in($user->clients()->pluck('clients.id')->all());
        }

        $rules = [
            'client_id' => $clientRules,
            'worker_id' => ['nullable', 'exists:workers,id'],
            'post_id' => ['nullable', 'exists:posts,id'],
            'serial_number' => ['required', 'string', 'max:120', 'unique:vests,serial_number'.($vest ? ','.$vest->id : '')],
            'brand' => ['nullable', 'string', 'max:120'],
            'batch' => ['nullable', 'string', 'max:120'],
            'size' => ['nullable', 'string', 'max:40'],
            'manufactured_at' => ['nullable', 'date'],
            'expires_at' => ['nullable', 'date'],
            'device_responsible' => ['nullable', 'string', 'max:190'],
            'notes' => ['nullable', 'string'],
        ];

        if ($vest === null) {
            $rules['photos'] = ['nullable', 'array', 'max:'.count(VestPhoto::DESCRIPTIONS)];
            $rules['photos.*'] = ['nullable', 'file', 'image', 'max:5120'];
        }

        $data = $request->validate($rules);

        $clientId = (int) $data['client_id'];
        $deviceResponsible = $this->resolveDeviceResponsible($user, $clientId);

        if ($deviceResponsible === null) {
            throw ValidationException::withMessages([
                'client_id' => __('Primero debe realizar la asignación del responsable.'),
            ]);
        }

        $data['device_responsible'] = $deviceResponsible;

        if (! empty($data['worker_id'])) {
            $workerValid = Worker::query()
                ->active()
                ->whereKey($data['worker_id'])
                ->where('client_id', $clientId)
                ->exists();

            if (! $workerValid) {
                throw ValidationException::withMessages([
                    'worker_id' => __('El trabajador no pertenece al cliente seleccionado.'),
                ]);
            }
        }

        if (! empty($data['post_id'])) {
            $postValid = Post::query()
                ->whereKey($data['post_id'])
                ->where('client_id', $clientId)
                ->whereNull('archived_at')
                ->exists();

            if (! $postValid) {
                throw ValidationException::withMessages([
                    'post_id' => __('El puesto no pertenece al cliente seleccionado.'),
                ]);
            }
        }

        return $data;
    }

    private function resolveDeviceResponsible(User $user, int $clientId): ?string
    {
        if ($user->isResponsibleLevelOne() && ! $user->canManageAllVests()) {
            return $user->name;
        }

        if ($user->canManageAllVests()) {
            return Vest::clientDeviceResponsibleName($clientId);
        }

        return null;
    }

    private function assertClientInPortfolio($user, int $clientId): void
    {
        if ($user->hasGlobalVestScope()) {
            return;
        }

        abort_unless($user->clients()->whereKey($clientId)->exists(), 403);
    }

    /**
     * @return array<string, mixed>
     */
    private function filtersFromRequest(Request $request): array
    {
        return [
            'q' => trim((string) $request->query('q', '')),
            'alert' => VestAlert::normalizeAlertFilter($request->query('alert')),
            'client_id' => $request->query('client_id'),
            'post_id' => $request->query('post_id'),
            'brand' => trim((string) $request->query('brand', '')),
            'assigned' => $request->query('assigned'),
        ];
    }
}
