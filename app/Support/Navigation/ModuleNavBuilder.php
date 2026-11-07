<?php

namespace App\Support\Navigation;

use App\Models\Client;
use App\Models\Post;
use App\Models\User;
use App\Models\Vest;
use App\Models\Weapon;
use App\Models\Worker;
use Illuminate\Support\Facades\Gate;

class ModuleNavBuilder
{
    public function forUser(User $user): array
    {
        $modules = array_values(array_filter([
            $this->armamento($user),
            $this->dotacion($user),
            $this->plataforma($user),
            $this->empresa($user),
            $this->ajustes($user),
        ]));

        $activeModule = collect($modules)->first(
            fn (array $module): bool => $module['active']
        ) ?? $modules[0] ?? null;

        return [
            'home_url' => $this->homeUrl($user, $modules),
            'active_module' => $activeModule['label'] ?? '',
            'active_module_key' => $activeModule['key'] ?? null,
            'tabs' => $activeModule['items'] ?? [],
            'modules' => $modules,
        ];
    }

    private function armamento(User $user): ?array
    {
        return $this->module('armamento', __('Armamento'), [
            $this->link(
                'inicio',
                __('Inicio'),
                route('dashboard'),
                ! $user->isAlmacen() && $user->canViewModule('dashboard'),
                request()->routeIs('dashboard'),
            ),
            $this->link(
                'armas',
                __('Armas'),
                route('weapons.index'),
                Gate::forUser($user)->allows('viewAny', Weapon::class) && $user->canViewModule('weapons'),
                request()->routeIs('weapons.*'),
            ),
            $this->link(
                'revista',
                __('Revista de armas'),
                route('revista-armas.index'),
                ($user->isAdmin() || $user->isResponsibleLevelOne()) && $user->canViewModule('revista'),
                request()->routeIs('revista-armas.*'),
            ),
            $this->link(
                'mapa',
                __('Mapa'),
                route('maps.index'),
                ($user->isAdmin() || $user->isResponsible() || $user->isAuditor()) && $user->canViewModule('map'),
                request()->routeIs('maps.*'),
            ),
            $this->link(
                'transferencias',
                __('Transferencias'),
                route('transfers.index'),
                ($user->isAdmin() || $user->isResponsible() || $user->isAuditor()) && $user->canViewModule('transfers'),
                request()->routeIs('transfers.*'),
            ),
            $this->link(
                'asignaciones',
                __('Asignaciones'),
                route('portfolios.index'),
                $user->canViewModule('portfolios'),
                request()->routeIs('portfolios.*'),
            ),
            $this->link(
                'reportes',
                __('Reportes'),
                route('reports.index'),
                $user->canViewModule('reports'),
                request()->routeIs('reports.*'),
            ),
            $this->link(
                'alertas',
                __('Alertas'),
                route('alerts.documents'),
                $user->canViewModule('alerts'),
                request()->routeIs('alerts.*'),
            ),
        ]);
    }

    private function dotacion(User $user): ?array
    {
        return $this->module('dotacion', __('Dotación'), [
            $this->link(
                'chalecos',
                __('Chalecos'),
                route('vests.index'),
                Gate::forUser($user)->allows('viewAny', Vest::class) && $user->canViewModule('vests'),
                request()->routeIs('vests.*'),
            ),
        ]);
    }

    private function plataforma(User $user): ?array
    {
        $canSeeCatalog = Gate::forUser($user)->allows('viewAny', Client::class);
        $canImportVests = Gate::forUser($user)->allows('import', Vest::class);
        $canSeeFormats = $user->isAdmin() || $user->isResponsible() || $user->isAuditor();

        return $this->module('plataforma', __('Plataforma'), [
            $this->link(
                'clientes',
                __('Clientes'),
                route('clients.index'),
                $canSeeCatalog && $user->canViewModule('clients'),
                request()->routeIs('clients.*'),
            ),
            $this->link(
                'puestos',
                __('Instalaciones'),
                route('installations.index'),
                Gate::forUser($user)->allows('viewAny', Post::class) && $user->canViewModule('posts'),
                request()->routeIs('installations.*') || request()->routeIs('posts.*'),
            ),
            $this->link(
                'trabajadores',
                __('Trabajadores'),
                route('workers.index'),
                Gate::forUser($user)->allows('viewAny', Worker::class) && $user->canViewModule('workers'),
                request()->routeIs('workers.*'),
            ),
            $this->link(
                'cargas',
                __('Cargas masivas'),
                $user->canViewModule('imports') ? route('weapon-imports.index') : route('vest-imports.index'),
                $user->canViewModule('imports') || $canImportVests,
                request()->routeIs('weapon-imports.*') || request()->routeIs('vest-imports.*'),
            ),
            $this->link(
                'formatos',
                __('Formatos'),
                route('formatos.index'),
                $canSeeFormats && $user->canViewModule('formats'),
                request()->routeIs('formatos.*'),
            ),
        ]);
    }

    private function empresa(User $user): ?array
    {
        return $this->module('empresa', __('Mi empresa'), [
            $this->link(
                'datos-empresa',
                __('Datos de la empresa'),
                route('company.edit'),
                $user->canViewModule('company'),
                request()->routeIs('company.*'),
            ),
        ]);
    }

    private function ajustes(User $user): ?array
    {
        $catalogTab = request()->routeIs('catalogs.*') ? (string) request()->query('tab', 'cargos') : null;

        return $this->module('ajustes', __('Ajustes'), [
            $this->link(
                'usuarios',
                __('Usuarios'),
                route('users.index'),
                $user->canViewModule('users'),
                request()->routeIs('users.*'),
            ),
            $this->link(
                'cargos',
                __('Cargos'),
                route('catalogs.index', ['tab' => 'cargos']),
                $user->canViewModule('settings'),
                $catalogTab === 'cargos',
            ),
            $this->link(
                'vinculacion',
                __('Tipos de vinculación'),
                route('catalogs.index', ['tab' => 'vinculacion']),
                $user->canViewModule('settings'),
                $catalogTab === 'vinculacion',
            ),
            $this->link(
                'contratos',
                __('Tipos de contrato'),
                route('catalogs.index', ['tab' => 'contratos']),
                $user->canViewModule('settings'),
                $catalogTab === 'contratos',
            ),
            $this->link(
                'estructuras',
                __('Tipos de estructura'),
                route('catalogs.index', ['tab' => 'estructuras']),
                $user->canViewModule('settings'),
                $catalogTab === 'estructuras',
            ),
            $this->link(
                'marcas',
                __('Marcas'),
                route('catalogs.index', ['tab' => 'marcas']),
                $user->canViewModule('settings'),
                $catalogTab === 'marcas',
            ),
            $this->link(
                'calibres',
                __('Calibres'),
                route('catalogs.index', ['tab' => 'calibres']),
                $user->canViewModule('settings'),
                $catalogTab === 'calibres',
            ),
            $this->link(
                'novedades',
                __('Tipos de novedad'),
                route('catalogs.index', ['tab' => 'novedades']),
                $user->canViewModule('settings'),
                $catalogTab === 'novedades',
            ),
        ]);
    }

    /**
     * @param  list<array<string, mixed>>  $modules
     */
    private function homeUrl(User $user, array $modules): string
    {
        if (! $user->isAlmacen() && $user->canViewModule('dashboard')) {
            return route('dashboard');
        }

        foreach ($modules as $module) {
            if (is_string($module['entry_url'] ?? null)) {
                return $module['entry_url'];
            }
        }

        return route('profile.edit');
    }

    /**
     * @param  list<array<string, mixed>|null>  $items
     * @return array<string, mixed>|null
     */
    private function module(string $key, string $label, array $items): ?array
    {
        $items = array_values(array_filter($items));

        if ($items === []) {
            return null;
        }

        $active = collect($items)->contains(
            fn (array $item): bool => (bool) ($item['active'] ?? false)
        );

        return [
            'key' => $key,
            'label' => $label,
            'active' => $active,
            'entry_url' => $this->entryUrl($items),
            'items' => $items,
        ];
    }

    /**
     * @param  list<array<string, mixed>>  $items
     */
    private function entryUrl(array $items): ?string
    {
        foreach ($items as $item) {
            if ($item['disabled'] ?? false) {
                continue;
            }

            if (is_string($item['url'] ?? null) && $item['url'] !== '') {
                return $item['url'];
            }

            $childUrl = $this->entryUrl($item['children'] ?? []);
            if ($childUrl !== null) {
                return $childUrl;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>|null
     */
    private function link(
        string $key,
        string $label,
        string $url,
        bool $visible,
        bool $active,
        array $extra = [],
    ): ?array {
        return $this->item($key, $label, array_merge([
            'url' => $url,
            'active' => $active,
            'visible' => $visible,
        ], $extra));
    }

    /**
     * @param  array<string, mixed>  $extra
     * @return array<string, mixed>|null
     */
    private function item(string $key, string $label, array $extra = []): ?array
    {
        if (($extra['visible'] ?? true) === false) {
            return null;
        }

        return [
            'key' => $key,
            'label' => $label,
            'url' => $extra['url'] ?? null,
            'active' => (bool) ($extra['active'] ?? false),
            'disabled' => (bool) ($extra['disabled'] ?? false),
            'badge' => $extra['badge'] ?? null,
            'download' => (bool) ($extra['download'] ?? false),
            'children' => $extra['children'] ?? [],
        ];
    }
}
