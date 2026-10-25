<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Post;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponClientAssignment;
use App\Models\WeaponDocument;
use App\Models\WeaponIncident;
use App\Models\WeaponTransfer;
use App\Models\Worker;
use App\Services\WeaponIncidentReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index()
    {
        $this->authorizeAdmin();

        return view('reports.index');
    }

    public function weaponsByClient(Request $request)
    {
        $this->authorizeAdmin();

        $clients = Client::orderBy('name')->get();
        $clientId = $request->integer('client_id');

        $query = Weapon::query()->with(['activeClientAssignment.client', 'activeClientAssignment.responsible']);
        if ($clientId) {
            $query->whereHas('clientAssignments', function ($assignmentQuery) use ($clientId) {
                $assignmentQuery->where('is_active', true)->where('client_id', $clientId);
            });
        } else {
            $query->whereHas('clientAssignments', function ($assignmentQuery) {
                $assignmentQuery->where('is_active', true);
            });
        }

        $weapons = $query->orderBy('internal_code')->paginate(50)->withQueryString();

        return view('reports.weapons_by_client', compact('weapons', 'clients', 'clientId'));
    }

    public function weaponsWithoutDestination()
    {
        $this->authorizeAdmin();

        $weapons = Weapon::whereDoesntHave('clientAssignments', function ($assignmentQuery) {
            $assignmentQuery->where('is_active', true);
        })->orderBy('internal_code')->paginate(50)->withQueryString();

        return view('reports.weapons_without_destination', compact('weapons'));
    }

    public function history(Request $request, WeaponIncidentReportService $weaponSearch)
    {
        $this->authorizeAdmin();

        $weaponId = $request->integer('weapon_id');
        $weapon = null;
        $selectedWeapon = null;
        $assignments = collect();
        $documents = collect();
        $incidents = collect();

        if ($weaponId) {
            $weapon = Weapon::query()
                ->with([
                    'activeClientAssignment.client',
                    'activeClientAssignment.responsible',
                ])
                ->find($weaponId);

            if ($weapon) {
                $selectedWeapon = $weaponSearch->findSearchWeapon($request->user(), $weapon->id);
                $assignments = WeaponClientAssignment::with(['client', 'assignedBy', 'responsible'])
                    ->where('weapon_id', $weaponId)
                    ->orderByDesc('start_at')
                    ->get();
                $documents = WeaponDocument::with('file')
                    ->where('weapon_id', $weaponId)
                    ->orderByDesc('created_at')
                    ->get();
                $incidents = WeaponIncident::with([
                    'type',
                    'modality',
                    'reporter',
                    'latestUpdate',
                    'attachmentFile',
                ])
                    ->where('weapon_id', $weaponId)
                    ->orderByDesc('event_at')
                    ->orderByDesc('id')
                    ->get();
            }
        }

        return view('reports.weapon_history', compact('weapon', 'selectedWeapon', 'assignments', 'documents', 'incidents'));
    }

    public function audit(Request $request)
    {
        $this->authorizeAdmin();

        $days = (int) $request->input('days', 30);
        if (! in_array($days, [30, 90], true)) {
            $days = 30;
        }

        $module = (string) $request->input('module', 'all');
        $modules = [
            'all' => 'Todos',
            'clients' => 'Clientes',
            'weapons' => 'Armas',
            'transfers' => 'Transferencias',
            'posts' => 'Puestos',
            'workers' => 'Trabajadores',
            'users' => 'Usuarios',
            'portfolios' => 'Asignaciones',
        ];
        if (! array_key_exists($module, $modules)) {
            $module = 'all';
        }

        $since = now()->subDays($days);
        $logsQuery = AuditLog::with([
            'user',
            'auditable',
        ])
            ->where('created_at', '>=', $since);

        $moduleFilters = [
            'clients' => ['types' => [Client::class], 'actions' => []],
            'weapons' => ['types' => [Weapon::class, WeaponClientAssignment::class], 'actions' => []],
            'transfers' => ['types' => [WeaponTransfer::class], 'actions' => []],
            'posts' => ['types' => [Post::class], 'actions' => []],
            'workers' => ['types' => [Worker::class], 'actions' => []],
            'users' => ['types' => [User::class], 'actions' => []],
            'portfolios' => [
                'types' => [User::class],
                'actions' => ['portfolio_updated', 'portfolio_transferred', 'client_responsible_transferred'],
            ],
        ];

        if ($module !== 'all') {
            $filters = $moduleFilters[$module] ?? ['types' => [], 'actions' => []];
            $types = $filters['types'] ?? [];
            $actions = $filters['actions'] ?? [];
            $logsQuery->where(function ($builder) use ($types, $actions) {
                if (! empty($types)) {
                    $builder->whereIn('auditable_type', $types);
                }
                if (! empty($actions)) {
                    $builder->orWhereIn('action', $actions);
                }
            });
        }

        $logs = $logsQuery
            ->orderByDesc('created_at')
            ->paginate(50)
            ->withQueryString();

        $entityLabels = [
            WeaponClientAssignment::class => __('audit.entities.weapon_client_assignment'),
            User::class => __('audit.entities.user'),
            Client::class => __('audit.entities.client'),
            Weapon::class => __('audit.entities.weapon'),
            WeaponTransfer::class => __('audit.entities.weapon_transfer'),
            Post::class => __('audit.entities.post'),
            Worker::class => __('audit.entities.worker'),
        ];

        $actionLabels = trans('audit.actions');

        return view('reports.audit', compact('logs', 'days', 'actionLabels', 'entityLabels', 'modules', 'module'));
    }

    private function authorizeAdmin(): void
    {
        if (! request()->user()?->canViewModule('reports')) {
            abort(403);
        }
    }
}
