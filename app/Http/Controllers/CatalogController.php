<?php

namespace App\Http\Controllers;

use App\Models\IncidentModality;
use App\Models\IncidentType;
use App\Models\Position;
use App\Models\ResponsibilityLevel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CatalogController extends Controller
{
    public const TABS = ['cargos', 'novedades', 'niveles'];

    public function index(Request $request)
    {
        $this->authorizeAdmin($request);

        $tab = in_array($request->query('tab'), self::TABS, true) ? $request->query('tab') : 'cargos';

        return view('company.catalogs', [
            'tab' => $tab,
            'positions' => Position::query()->withCount('users')->orderBy('name')->get(),
            'incidentTypes' => IncidentType::query()
                ->with('modalities')
                ->orderBy('sort_order')
                ->get(),
            'levels' => ResponsibilityLevel::query()->orderBy('level')->get(),
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

        $position->update($request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('positions', 'name')->ignore($position->id)],
            'description' => ['nullable', 'string', 'max:255'],
        ]));

        return $this->back('cargos', __('Cargo actualizado.'));
    }

    public function destroyPosition(Request $request, Position $position)
    {
        $this->authorizeAdmin($request);

        if ($position->users()->exists()) {
            return $this->back('cargos', null, __('No se puede eliminar un cargo asignado a usuarios.'));
        }

        $position->delete();

        return $this->back('cargos', __('Cargo eliminado.'));
    }

    public function updateIncidentType(Request $request, IncidentType $incidentType)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'sla_hours' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'requires_attachment' => ['nullable', 'boolean'],
            'requires_resolution_note' => ['nullable', 'boolean'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        foreach (['requires_attachment', 'requires_resolution_note', 'is_active'] as $flag) {
            $data[$flag] = $request->boolean($flag);
        }

        $incidentType->update($data);

        return $this->back('novedades', __('Tipo de novedad actualizado.'));
    }

    public function storeModality(Request $request, IncidentType $incidentType)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
        ]);

        $code = $this->uniqueModalityCode($incidentType, $data['name']);

        $incidentType->modalities()->create([
            'code' => $code,
            'name' => $data['name'],
            'sort_order' => ((int) IncidentModality::query()->where('incident_type_id', $incidentType->id)->max('sort_order')) + 10,
            'is_active' => true,
        ]);

        return $this->back('novedades', __('Modalidad creada.'));
    }

    public function updateModality(Request $request, IncidentModality $incidentModality)
    {
        $this->authorizeAdmin($request);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:65535'],
            'is_active' => ['nullable', 'boolean'],
        ]);
        $data['is_active'] = $request->boolean('is_active');

        $incidentModality->update($data);

        return $this->back('novedades', __('Modalidad actualizada.'));
    }

    public function updateLevel(Request $request, ResponsibilityLevel $responsibilityLevel)
    {
        $this->authorizeAdmin($request);

        $responsibilityLevel->update($request->validate([
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:255'],
        ]));

        return $this->back('niveles', __('Nivel actualizado.'));
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
        abort_unless($request->user()?->isAdmin(), 403);
    }
}
