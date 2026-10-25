<?php

namespace App\Http\Controllers;

use App\Events\ClientChanged;
use App\Events\MapDataChanged;
use App\Models\AuditLog;
use App\Models\Client;
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
            ->withCount(['installations' => fn ($builder) => $builder->active()])
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

    public function edit(Client $client)
    {
        return view('clients.edit', compact('client'));
    }

    public function update(Request $request, Client $client)
    {
        if ($client->is_own_company) {
            return redirect()->route('company.edit');
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

        return redirect()->route('clients.edit', $client)->with('status', __('Cliente actualizado.'));
    }

    public function destroy(Client $client)
    {
        $hasWeapons = WeaponClientAssignment::query()
            ->where('client_id', $client->id)
            ->where('is_active', true)
            ->exists();

        if ($hasWeapons) {
            return redirect()->route('clients.index')->withErrors([
                'client' => 'No se puede eliminar el cliente porque tiene armas asignadas.',
            ]);
        }

        AuditLog::create([
            'user_id' => request()->user()?->id,
            'action' => 'client_deleted',
            'auditable_type' => Client::class,
            'auditable_id' => $client->id,
            'before' => $client->only(['name', 'nit', 'city', 'department']),
            'after' => null,
        ]);

        $clientId = $client->id;

        event(new ClientChanged('deleted', $clientId));
        event(new MapDataChanged('deleted', $clientId));

        $client->delete();

        return redirect()->route('clients.index')->with('status', 'Cliente eliminado.');
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
