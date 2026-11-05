<?php

namespace App\Http\Controllers;

use App\Events\ClientChanged;
use App\Events\MapDataChanged;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Installation;
use App\Support\Installations\InstallationAudit;
use App\Models\WeaponClientAssignment;
use App\Support\MapCoordinates;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Validation\Rule;

class ClientController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Client::class, 'client');
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $search = trim((string) $request->input('q', ''));

        if ($user->isResponsible() && !$user->isAdmin()) {
            $query = $user->clients();
        } else {
            $query = Client::query();
        }

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('name', 'like', '%' . $search . '%')
                    ->orWhere('legal_name', 'like', '%' . $search . '%')
                    ->orWhere('nit', 'like', '%' . $search . '%')
                    ->orWhere('contact_name', 'like', '%' . $search . '%')
                    ->orWhere('email', 'like', '%' . $search . '%')
                    ->orWhere('city', 'like', '%' . $search . '%')
                    ->orWhere('department', 'like', '%' . $search . '%');
            });
        }

        $clients = $query
            ->with('structureType:id,name')
            ->withCount([
                'installations as active_installations_count' => fn ($builder) => $builder->active(),
                'installations as extra_installations_count' => fn ($builder) => $builder->active()->where('is_client_site', false),
            ])
            ->orderByRaw('CASE WHEN clients.archived_at IS NULL THEN 0 ELSE 1 END')
            ->orderByDesc('is_own_company')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        if ($request->expectsJson()) {
            return response()->json([
                'results' => view('clients.partials.index_results', compact('clients', 'search'))->render(),
                'countLabel' => $this->clientCountLabel($clients),
            ]);
        }

        return view('clients.index', compact('clients', 'search'));
    }

    public function create()
    {
        return view('clients.create');
    }

    public function store(Request $request)
    {
        $data = MapCoordinates::apply($this->validated($request));

        $client = Client::create($data);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'client_created',
            'auditable_type' => Client::class,
            'auditable_id' => $client->id,
            'before' => null,
            'after' => $client->only(['name', 'nit', 'city', 'department']),
        ]);

        event(new ClientChanged('created', $client->id));
        event(new MapDataChanged('updated', $client->id));

        return redirect()->route('clients.edit', $client)->with('status', __('Cliente creado con su instalación principal.'));
    }

    public function edit(Request $request, Client $client)
    {
        return view('clients.edit', [
            'client' => $client,
            'returnInstallation' => $this->installationFromReturn($request, $client),
        ]);
    }

    public function update(Request $request, Client $client)
    {
        if ($client->is_own_company) {
            return redirect()->route('company.edit');
        }

        $returnInstallation = $this->installationFromReturn($request, $client);
        $note = null;
        $installationBefore = null;
        if ($returnInstallation) {
            $note = trim((string) ($request->validate([
                'change_note' => ['nullable', 'string', 'max:2000'],
            ])['change_note'] ?? ''));
            $installationBefore = $returnInstallation->only(['name', 'address', 'city', 'department']);
        }

        $data = MapCoordinates::apply($this->validated($request));

        $before = $client->only(['name', 'nit', 'city', 'department', 'email', 'contact_name']);

        $client->update($data);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'client_updated',
            'auditable_type' => Client::class,
            'auditable_id' => $client->id,
            'before' => $before,
            'after' => $client->only(['name', 'nit', 'city', 'department', 'email', 'contact_name']),
        ]);

        event(new ClientChanged('updated', $client->id));
        event(new MapDataChanged('updated', $client->id));

        if ($returnInstallation) {
            $returnInstallation->refresh();
            InstallationAudit::record($request->user()?->id, 'installation_updated', $returnInstallation, $installationBefore, $note);

            return redirect()->route('installations.edit', $returnInstallation)->with('status', __('Instalación actualizada.'));
        }

        return redirect()->route('clients.edit', $client)->with('status', __('Cliente actualizado.'));
    }

    public function destroy(Client $client)
    {
        if ($client->is_own_company) {
            return redirect()->route('clients.index')->withErrors([
                'client' => __('La empresa propia no se puede archivar.'),
            ]);
        }

        if ($client->installations()->active()->where('is_client_site', false)->exists()) {
            return redirect()->route('clients.index')->withErrors([
                'client' => __('Archive primero las instalaciones de este cliente.'),
            ]);
        }

        $hasWeapons = WeaponClientAssignment::query()
            ->where('client_id', $client->id)
            ->where('is_active', true)
            ->exists();

        if ($hasWeapons) {
            return redirect()->route('clients.index')->withErrors([
                'client' => __('No se puede archivar el cliente porque tiene armas asignadas.'),
            ]);
        }

        $client->update(['archived_at' => now()]);

        AuditLog::create([
            'user_id' => request()->user()?->id,
            'action' => 'client_archived',
            'auditable_type' => Client::class,
            'auditable_id' => $client->id,
            'before' => ['archived_at' => null],
            'after' => ['archived_at' => $client->archived_at?->toDateTimeString()],
        ]);

        event(new ClientChanged('archived', $client->id));
        event(new MapDataChanged('updated', $client->id));

        return redirect()->route('clients.index')->with('status', __('Cliente archivado.'));
    }

    public function restore(Client $client)
    {
        $this->authorize('delete', $client);

        $wasArchivedAt = $client->archived_at;
        $client->update(['archived_at' => null]);

        AuditLog::create([
            'user_id' => request()->user()?->id,
            'action' => 'client_restored',
            'auditable_type' => Client::class,
            'auditable_id' => $client->id,
            'before' => ['archived_at' => $wasArchivedAt?->toDateTimeString()],
            'after' => ['archived_at' => null],
        ]);

        event(new ClientChanged('restored', $client->id));
        event(new MapDataChanged('updated', $client->id));

        return redirect()->route('clients.index')->with('status', __('Cliente reactivado.'));
    }

    private function installationFromReturn(Request $request, Client $client): ?Installation
    {
        $id = $request->integer('return_installation');
        if ($id === 0) {
            return null;
        }

        return Installation::matchingPrincipal($id, $client->id);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        return $request->validate([
            'party_type' => ['required', Rule::in(array_keys(Client::partyTypeLabels()))],
            'name' => ['required', 'string', 'max:255'],
            'legal_name' => ['nullable', 'string', 'max:255'],
            'document_type' => ['required', Rule::in(array_keys(Client::documentTypeLabels()))],
            'nit' => ['required', 'string', 'max:50'],
            'legal_representative' => ['nullable', 'string', 'max:255'],
            'representative_email' => ['nullable', 'email', 'max:255'],
            'contact_name' => ['nullable', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:60'],
            'address' => ['nullable', 'string', 'max:255'],
            'neighborhood' => ['nullable', 'string', 'max:255'],
            'city' => ['required', 'string', 'max:255'],
            'department' => ['required', 'string', 'max:255'],
            'structure_type_id' => ['nullable', Rule::exists('structure_types', 'id')],
            'latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'coords_source' => ['nullable', 'string', 'max:20'],
        ]);
    }

    private function clientCountLabel(LengthAwarePaginator $clients): string
    {
        $total = $clients->total();
        $label = $total === 1 ? 'cliente' : 'clientes';

        return number_format($total) . ' ' . $label;
    }
}
