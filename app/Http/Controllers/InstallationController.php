<?php

namespace App\Http\Controllers;

use App\Events\MapDataChanged;
use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Installation;
use App\Support\MapCoordinates;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstallationController extends Controller
{
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

        return redirect()->route('clients.edit', $client)->with('status', __('Instalación creada.'));
    }

    public function edit(Installation $installation): View
    {
        $this->authorize('update', $installation->client);

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
        $installation->save();

        $this->audit($request, 'installation_updated', $installation, $before);
        event(new MapDataChanged('updated', $client->id));

        return redirect()->route('clients.edit', $client)->with('status', __('Instalación actualizada.'));
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
        $this->audit($request, 'installation_archived', $installation, null);

        return back()->with('status', __('Instalación archivada.'));
    }

    public function restore(Request $request, Installation $installation): RedirectResponse
    {
        $this->authorize('update', $installation->client);

        $installation->update(['archived_at' => null]);
        $this->audit($request, 'installation_restored', $installation, null);

        return back()->with('status', __('Instalación restaurada.'));
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
    private function audit(Request $request, string $action, Installation $installation, ?array $before): void
    {
        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => $action,
            'auditable_type' => Installation::class,
            'auditable_id' => $installation->id,
            'before' => $before,
            'after' => $installation->only(['client_id', 'name', 'address', 'city', 'department', 'archived_at']),
        ]);
    }
}
