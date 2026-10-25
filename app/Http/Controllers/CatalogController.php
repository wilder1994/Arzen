<?php

namespace App\Http\Controllers;

use App\Models\IncidentModality;
use App\Models\IncidentType;
use App\Models\Position;
use App\Models\WeaponIncident;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CatalogController extends Controller
{
    public const TABS = ['cargos', 'novedades'];

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'cargos';

        return view('settings.catalogs', [
            'tab' => $tab,
            'positions' => Position::query()->withCount(['users', 'workers'])->ordered()->get(),
            'incidentTypes' => IncidentType::query()
                ->catalog()
                ->with(['modalities' => fn ($query) => $query->withCount('incidents')->orderBy('sort_order')->orderBy('name')])
                ->withCount('incidents')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
        ]);
    }

    public function storePosition(Request $request)
    {
        $this->authorizeAdmin($request);

        Position::create($request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:positions,name'],
            'description' => ['nullable', 'string', 'max:255'],
        ]));

        return $this->back('cargos', __('Cargo creado.'));
    }

    public function updatePosition(Request $request, Position $position)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('positions', 'name')->ignore($position->id)],
            'description' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['sort_order'] = (int) ($data['sort_order'] ?? 0);
        $data['is_active'] = $request->boolean('is_active');

        $position->update($data);

        return $this->back('cargos', __('Cargo actualizado.'));
    }

    public function destroyPosition(Request $request, Position $position)
    {
        $this->authorizeAdmin($request);

        if ($position->users()->exists() || $position->workers()->exists()) {
            return $this->back('cargos', null, __('No se puede eliminar un cargo en uso. Desactívelo.'));
        }

        $position->delete();

        return $this->back('cargos', __('Cargo eliminado.'));
    }

    public function storeIncident(Request $request)
    {
        $this->authorizeAdmin($request);

        $kind = $request->validate([
            'kind' => ['required', Rule::in(['type', 'modality'])],
        ])['kind'];

        if ($kind === 'type') {
            $data = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            ]);

            IncidentType::query()->create([
                'code' => $this->uniqueTypeCode($data['name']),
                'name' => $data['name'],
                'color' => $data['color'],
                'sort_order' => ((int) IncidentType::query()->catalog()->max('sort_order')) + 10,
                'requires_modality' => false,
                'requires_attachment' => false,
                'requires_resolution_note' => true,
                'default_status' => WeaponIncident::STATUS_OPEN,
                'is_active' => true,
                'is_reportable' => true,
            ]);

            return $this->back('novedades', __('Tipo de novedad creado.'));
        }

        $data = $request->validate([
            'incident_type_id' => ['required', 'exists:incident_types,id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $incidentType = IncidentType::query()->findOrFail($data['incident_type_id']);
        $this->guardCatalogType($incidentType);
        $this->createModality($incidentType, $data['name']);

        return $this->back('novedades', __('Modalidad creada.'));
    }

    public function updateIncidentType(Request $request, IncidentType $incidentType)
    {
        $this->authorizeAdmin($request);
        $this->guardCatalogType($incidentType);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['required', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $incidentType->update($data);

        return $this->back('novedades', __('Tipo de novedad actualizado.'));
    }

    public function destroyIncidentType(Request $request, IncidentType $incidentType)
    {
        $this->authorizeAdmin($request);
        $this->guardCatalogType($incidentType);

        $used = $incidentType->incidents()->exists()
            || IncidentModality::query()
                ->where('incident_type_id', $incidentType->id)
                ->whereHas('incidents')
                ->exists();

        if ($used) {
            return $this->back('novedades', null, __('Este tipo ya está en un arma. Desactívelo para que no salga en reportes nuevos.'));
        }

        $incidentType->delete();

        return $this->back('novedades', __('Tipo de novedad eliminado.'));
    }

    public function moveIncidentType(Request $request, IncidentType $incidentType)
    {
        $this->authorizeAdmin($request);
        $this->guardCatalogType($incidentType);

        $direction = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ])['direction'];

        $this->moveWithin(
            IncidentType::query()->catalog()->orderBy('sort_order')->orderBy('name')->get(),
            $incidentType->id,
            $direction
        );

        return $this->back('novedades', __('Orden actualizado.'));
    }

    public function updateModality(Request $request, IncidentModality $incidentModality)
    {
        $this->authorizeAdmin($request);
        $this->guardCatalogType($incidentModality->incidentType);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $incidentModality->update($data);
        $incidentModality->incidentType->syncModalityRequirement();

        return $this->back('novedades', __('Modalidad actualizada.'));
    }

    public function destroyModality(Request $request, IncidentModality $incidentModality)
    {
        $this->authorizeAdmin($request);
        $type = $incidentModality->incidentType;
        $this->guardCatalogType($type);

        if ($incidentModality->incidents()->exists()) {
            return $this->back('novedades', null, __('Esta modalidad ya está en un arma. Desactívela para que no salga en reportes nuevos.'));
        }

        $incidentModality->delete();
        $type->syncModalityRequirement();

        return $this->back('novedades', __('Modalidad eliminada.'));
    }

    public function moveModality(Request $request, IncidentModality $incidentModality)
    {
        $this->authorizeAdmin($request);
        $this->guardCatalogType($incidentModality->incidentType);

        $direction = $request->validate([
            'direction' => ['required', Rule::in(['up', 'down'])],
        ])['direction'];

        $this->moveWithin(
            IncidentModality::query()
                ->where('incident_type_id', $incidentModality->incident_type_id)
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(),
            $incidentModality->id,
            $direction
        );

        return $this->back('novedades', __('Orden actualizado.'));
    }

    private function createModality(IncidentType $incidentType, string $name): void
    {
        $incidentType->modalities()->create([
            'code' => $this->uniqueModalityCode($incidentType, $name),
            'name' => $name,
            'sort_order' => ((int) IncidentModality::query()->where('incident_type_id', $incidentType->id)->max('sort_order')) + 10,
            'is_active' => true,
        ]);

        $incidentType->syncModalityRequirement();
    }

    private function guardCatalogType(?IncidentType $incidentType): void
    {
        abort_unless($incidentType?->isCatalog(), 404);
    }

    private function uniqueTypeCode(string $name): string
    {
        $base = Str::limit(Str::slug($name, '_'), 60, '') ?: 'tipo';
        $code = $base;
        $suffix = 2;

        while (IncidentType::query()->where('code', $code)->exists()) {
            $code = $base.'_'.$suffix++;
        }

        return $code;
    }

    /**
     * @param  Collection<int, IncidentType|IncidentModality>  $items
     */
    private function moveWithin(Collection $items, int $id, string $direction): void
    {
        $items = $items->values();
        $index = $items->search(fn ($item) => (int) $item->id === $id);
        $target = $direction === 'up' ? $index - 1 : $index + 1;

        if ($index === false || $target < 0 || $target >= $items->count()) {
            return;
        }

        $ordered = $items->all();
        [$ordered[$index], $ordered[$target]] = [$ordered[$target], $ordered[$index]];

        foreach (array_values($ordered) as $position => $item) {
            $item->update(['sort_order' => ($position + 1) * 10]);
        }
    }

    private function uniqueModalityCode(IncidentType $incidentType, string $name): string
    {
        $base = Str::limit(Str::slug($name, '_'), 60, '') ?: 'modalidad';
        $code = $base;
        $suffix = 2;

        while (IncidentModality::query()->where('incident_type_id', $incidentType->id)->where('code', $code)->exists()) {
            $code = $base.'_'.$suffix++;
        }

        return $code;
    }

    private function back(string $tab, ?string $status, ?string $error = null)
    {
        $redirect = redirect()->route('catalogs.index', ['tab' => $tab]);

        return $error ? $redirect->with('error', $error) : $redirect->with('status', $status);
    }

    private function authorizeAdmin(Request $request): void
    {
        abort_unless($request->user()?->canViewModule('settings'), 403);
    }
}
