<?php

namespace App\Http\Controllers;

use App\Events\AssignmentChanged;
use App\Events\WorkerChanged;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\ContractType;
use App\Models\EngagementType;
use App\Models\Position;
use App\Models\Post;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponWorkerAssignment;
use App\Models\Worker;
use App\Models\WorkerHistory;
use App\Support\Workers\WorkerProfile;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class WorkerController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $user = $request->user();
            $action = $request->route()?->getActionMethod();

            if ($action === 'index' || $action === 'histories') {
                if (!$user?->isAdmin() && !$user?->isResponsible() && !$user?->isAuditor()) {
                    abort(403);
                }

                return $next($request);
            }

            $writes = ['create', 'store', 'edit', 'update', 'destroy', 'restore'];
            if (in_array($action, $writes, true)) {
                if ($user?->isAdmin()) {
                    return $next($request);
                }
                if ($user?->isResponsibleLevelOne()) {
                    return $next($request);
                }

                abort(403);
            }

            abort(403);
        });
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $search = trim((string) $request->input('q', ''));
        $clientId = $request->integer('client_id');
        $positionId = $request->integer('position_id');
        $showResponsibleFilter = ! ($user?->isResponsible() && ! $user?->isAdmin());
        $responsibleId = $showResponsibleFilter ? $request->integer('responsible_user_id') : 0;
        $archiveFilter = $request->input('archive', 'active');
        if (!in_array($archiveFilter, ['active', 'archived', 'all'], true)) {
            $archiveFilter = 'active';
        }

        $scopedQuery = Worker::query();
        if ($user?->isResponsible() && !$user?->isAdmin()) {
            $scopedQuery->whereIn('client_id', $user->clients()->pluck('clients.id'));
        }

        $workersGlobalTotal = (clone $scopedQuery)->count();
        $query = (clone $scopedQuery)->with(['client', 'responsible', 'position', 'post.installation']);

        if ($archiveFilter === 'archived') {
            $query->archived();
        } elseif ($archiveFilter === 'active') {
            $query->active();
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', '%'.$search.'%')
                    ->orWhere('document', 'like', '%'.$search.'%')
                    ->orWhere('first_names', 'like', '%'.$search.'%')
                    ->orWhere('last_name_paternal', 'like', '%'.$search.'%')
                    ->orWhere('last_name_maternal', 'like', '%'.$search.'%');
            });
        }

        if ($clientId) {
            $query->where('client_id', $clientId);
        }

        if ($positionId) {
            $query->where('position_id', $positionId);
        }

        if ($responsibleId) {
            $query->where('responsible_user_id', $responsibleId);
        }

        $workers = $query->orderBy('name')
            ->paginate(15)
            ->appends($request->except(['_rt']));

        if ($user?->isResponsible() && !$user?->isAdmin()) {
            $clients = $user->clients()->orderBy('name')->get();
            $responsibles = collect([$user]);
        } else {
            $clients = Client::orderBy('name')->get();
            $responsibles = User::where('role', 'RESPONSABLE')->orderBy('name')->get();
        }
        $positions = $this->positionOptions();
        $countLabel = $this->workerCountLabel($workers);

        if ($request->ajax()) {
            return response()->json([
                'tbody' => view('workers.partials.index_rows', compact('workers'))->render(),
                'pagination' => view('workers.partials.index_pagination', compact('workers'))->render(),
                'total_global' => $workers->total(),
                'count_label' => $countLabel,
            ])
                ->withHeaders([
                    'Cache-Control' => 'private, no-store, must-revalidate',
                    'Vary' => 'Cookie',
                ]);
        }

        return response()
            ->view('workers.index', compact(
                'workers',
                'clients',
                'responsibles',
                'positions',
                'search',
                'clientId',
                'positionId',
                'responsibleId',
                'archiveFilter',
                'showResponsibleFilter',
                'workersGlobalTotal',
                'countLabel',
            ))
            ->withHeaders([
                'Cache-Control' => 'private, no-store, must-revalidate',
                'Vary' => 'Cookie',
            ]);
    }

    private function clientsForForm(Request $request)
    {
        $user = $request->user();
        if ($user?->isAdmin()) {
            return Client::orderBy('name')->get();
        }
        if ($user?->isResponsibleLevelOne()) {
            return $user->clients()->orderBy('name')->get();
        }

        return collect();
    }

    public function create(Request $request)
    {
        $this->authorize('create', Worker::class);

        return view('workers.create', $this->formCatalogs());
    }

    public function store(Request $request)
    {
        $this->authorize('create', Worker::class);

        $data = $this->workerData($request);
        $data['archived_at'] = null;

        $worker = Worker::create($data);

        WorkerHistory::create([
            'worker_id' => $worker->id,
            'user_id' => $request->user()?->id,
            'body' => collect([$this->initialHistoryBody($request->user()?->name, $data['notes'] ?? null), $this->postChangeLine(null, $worker->post_id ? (int) $worker->post_id : null)])->filter()->implode("\n"),
        ]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'worker_created',
            'auditable_type' => Worker::class,
            'auditable_id' => $worker->id,
            'before' => null,
            'after' => $worker->only(['client_id', 'post_id', 'name', 'document', 'position_id', 'responsible_user_id']),
        ]);

        event(new WorkerChanged('created', $worker->id, ['client_id' => $worker->client_id]));

        return redirect()->route('workers.index')->with('status', 'Trabajador creado.');
    }

    public function histories(Request $request, Worker $worker)
    {
        $this->authorize('view', $worker);

        $entries = $worker->histories()
            ->with('user:id,name')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (WorkerHistory $h) => [
                'id' => $h->id,
                'body' => $h->body,
                'user' => $h->user?->name,
                'at' => $h->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i'),
            ]);

        return response()->json(['entries' => $entries]);
    }

    public function edit(Request $request, Worker $worker)
    {
        $this->authorize('update', $worker);

        return view('workers.edit', array_merge(['worker' => $worker], $this->formCatalogs($worker)));
    }

    public function update(Request $request, Worker $worker)
    {
        $this->authorize('update', $worker);

        $data = $this->workerData($request, true);

        $changeNote = trim((string) ($data['change_note'] ?? ''));
        unset($data['change_note']);

        $before = $worker->only(['client_id', 'post_id', 'name', 'document', 'position_id', 'responsible_user_id', 'notes']);
        $worker->update($data);

        WorkerHistory::create([
            'worker_id' => $worker->id,
            'user_id' => $request->user()?->id,
            'body' => collect([$changeNote, $this->postChangeLine($before['post_id'] ? (int) $before['post_id'] : null, $worker->post_id ? (int) $worker->post_id : null)])->filter()->implode("\n"),
        ]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'worker_updated',
            'auditable_type' => Worker::class,
            'auditable_id' => $worker->id,
            'before' => $before,
            'after' => $worker->only(['client_id', 'post_id', 'name', 'document', 'position_id', 'responsible_user_id', 'notes']),
        ]);

        event(new WorkerChanged('updated', $worker->id, ['client_id' => $worker->client_id]));

        return redirect()->route('workers.index')->with('status', 'Trabajador actualizado.');
    }

    public function destroy(Worker $worker)
    {
        $this->authorize('delete', $worker);

        $before = $worker->only(['client_id', 'post_id', 'name', 'document', 'position_id', 'responsible_user_id']);

        $weaponIdsAffected = [];

        DB::transaction(function () use ($worker, &$weaponIdsAffected) {
            $weaponIdsAffected = WeaponWorkerAssignment::query()
                ->where('worker_id', $worker->id)
                ->where('is_active', true)
                ->pluck('weapon_id')
                ->all();

            WeaponWorkerAssignment::query()
                ->where('worker_id', $worker->id)
                ->where('is_active', true)
                ->update([
                    'end_at' => now()->toDateString(),
                    'is_active' => null,
                ]);

            $worker->archived_at = now();
            $worker->save();
        });

        foreach ($weaponIdsAffected as $weaponId) {
            $w = Weapon::query()->with('activeClientAssignment')->find($weaponId);
            $clientId = $w?->activeClientAssignment?->client_id;
            event(new AssignmentChanged('unassigned', $weaponId, [
                'client_id' => $clientId,
            ]));
        }

        AuditLog::create([
            'user_id' => request()->user()?->id,
            'action' => 'worker_archived',
            'auditable_type' => Worker::class,
            'auditable_id' => $worker->id,
            'before' => $before,
            'after' => ['archived_at' => $worker->archived_at?->toDateTimeString()],
        ]);

        event(new WorkerChanged('archived', $worker->id, ['client_id' => $worker->client_id]));

        WorkerHistory::create([
            'worker_id' => $worker->id,
            'user_id' => request()->user()?->id,
            'body' => __('Registro: trabajador archivado.'),
        ]);

        return redirect()
            ->route('workers.index', ['archive' => 'archived'])
            ->with('status', 'Trabajador archivado. Las armas asignadas a este trabajador quedaron sin ubicación interna activa.');
    }

    public function restore(Request $request, Worker $worker)
    {
        $this->authorize('restore', $worker);

        $wasArchivedAt = $worker->archived_at;

        $worker->archived_at = null;
        $worker->save();

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'worker_restored',
            'auditable_type' => Worker::class,
            'auditable_id' => $worker->id,
            'before' => ['archived_at' => $wasArchivedAt?->toDateTimeString()],
            'after' => ['archived_at' => null],
        ]);

        event(new WorkerChanged('restored', $worker->id, ['client_id' => $worker->client_id]));

        WorkerHistory::create([
            'worker_id' => $worker->id,
            'user_id' => $request->user()?->id,
            'body' => __('Registro: trabajador reactivado.'),
        ]);

        return redirect()->route('workers.index')->with('status', 'Trabajador reactivado.');
    }

    private function initialHistoryBody(?string $userName, ?string $notes): string
    {
        $header = __('Registro inicial.');
        if ($userName) {
            $header .= ' ' . __('Usuario: :name.', ['name' => $userName]);
        }
        $notes = $notes !== null ? trim($notes) : '';
        if ($notes !== '') {
            return $header . "\n\n" . __('Notas:') . "\n" . $notes;
        }

        return $header;
    }

    /**
     * @return array<string, mixed>
     */
    private function formCatalogs(?Worker $worker = null): array
    {
        $positions = $this->positionOptions();
        if ($worker?->position && $positions->doesntContain('id', $worker->position_id)) {
            $positions->push($worker->position);
        }

        $engagementTypes = EngagementType::query()->active()->ordered()->get();
        if ($worker?->engagementType && $engagementTypes->doesntContain('id', $worker->engagement_type_id)) {
            $engagementTypes->push($worker->engagementType);
        }

        $contractTypes = ContractType::query()->active()->ordered()->get();
        if ($worker?->contractType && $contractTypes->doesntContain('id', $worker->contract_type_id)) {
            $contractTypes->push($worker->contractType);
        }

        return compact('positions', 'engagementTypes', 'contractTypes');
    }

    private function positionOptions()
    {
        return Position::query()->active()->ordered()->get();
    }

    /**
     * @return array<string, mixed>
     */
    private function workerData(Request $request, bool $withChangeNote = false): array
    {
        $rules = array_merge(WorkerProfile::rules(), [
            'document' => ['nullable', 'string', 'max:50'],
            'position_id' => ['nullable', Rule::exists('positions', 'id')],
            'engagement_type_id' => ['nullable', Rule::exists('engagement_types', 'id')],
            'contract_type_id' => ['nullable', Rule::exists('contract_types', 'id')],
            'document_type' => ['required', Rule::in(array_keys(Client::documentTypeLabels()))],
        ]);

        if ($withChangeNote) {
            $rules['change_note'] = ['required', 'string', 'min:3', 'max:5000'];
        }

        $data = $request->validate($rules);
        $data['name'] = WorkerProfile::composeName(
            $data['first_names'] ?? null,
            $data['last_name_paternal'] ?? null,
            $data['last_name_maternal'] ?? null,
        );

        return $data;
    }

    private function workerCountLabel(LengthAwarePaginator $workers): string
    {
        $total = $workers->total();

        return number_format($total, 0, ',', '.').' '.trans_choice('trabajador|trabajadores', $total);
    }

    private function postChangeLine(?int $fromPostId, ?int $toPostId): ?string
    {
        if ($fromPostId === $toPostId) {
            return null;
        }

        $name = fn (?int $id) => $id ? (Post::query()->find($id)?->name ?? '—') : __('sin puesto');

        return __('Puesto: :from → :to', ['from' => $name($fromPostId), 'to' => $name($toPostId)]);
    }
}
