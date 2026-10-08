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
        ]));

        $activeModule = collect($modules)->first(
            fn (array $module): bool => $module['active']
        ) ?? $modules[0] ?? null;

        return [
            'home_url' => $user->isAlmacen() ? route('vests.index') : route('dashboard'),
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
                ! $user->isAlmacen(),
                request()->routeIs('dashboard'),
            ),
            $this->link(
                'armas',
                __('Armas'),
                route('weapons.index'),
                Gate::forUser($user)->allows('viewAny', Weapon::class),
                request()->routeIs('weapons.*'),
            ),
            $this->link(
                'revista',
                __('Revista de armas'),
                route('revista-armas.index'),
                $user->isAdmin() || $user->isResponsibleLevelOne(),
                request()->routeIs('revista-armas.*'),
            ),
            $this->link(
                'mapa',
                __('Mapa'),
                route('maps.index'),
                $user->isAdmin() || $user->isResponsible() || $user->isAuditor(),
                request()->routeIs('maps.*'),
            ),
            $this->link(
                'transferencias',
                __('Transferencias'),
                route('transfers.index'),
                $user->isAdmin() || $user->isResponsible() || $user->isAuditor(),
                request()->routeIs('transfers.*'),
            ),
            $this->link(
                'asignaciones',
                __('Asignaciones'),
                route('portfolios.index'),
                $user->isAdmin(),
                request()->routeIs('portfolios.*'),
            ),
            $this->link(
                'reportes',
                __('Reportes'),
                route('reports.index'),
                $user->isAdmin() || $user->isAuditor(),
                request()->routeIs('reports.*'),
            ),
            $this->link(
                'alertas',
                __('Alertas'),
                route('alerts.documents'),
                $user->isAdmin() || $user->isAuditor(),
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
                Gate::forUser($user)->allows('viewAny', Vest::class),
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
                $canSeeCatalog,
                request()->routeIs('clients.*'),
            ),
            $this->link(
                'puestos',
                __('Puestos'),
                route('posts.index'),
                Gate::forUser($user)->allows('viewAny', Post::class),
                request()->routeIs('posts.*'),
            ),
            $this->link(
                'trabajadores',
                __('Trabajadores'),
                route('workers.index'),
                Gate::forUser($user)->allows('viewAny', Worker::class),
                request()->routeIs('workers.*'),
            ),
            $this->link(
                'usuarios',
                __('Usuarios'),
                route('users.index'),
                $user->isAdmin(),
                request()->routeIs('users.*'),
            ),
            $this->link(
                'cargas',
                __('Cargas masivas'),
                $user->isAdmin() ? route('weapon-imports.index') : route('vest-imports.index'),
                $user->isAdmin() || $canImportVests,
                request()->routeIs('weapon-imports.*') || request()->routeIs('vest-imports.*'),
            ),
            $this->link(
                'formatos',
                __('Formatos'),
                route('formatos.index'),
                $canSeeFormats,
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
                $user->isAdmin(),
                request()->routeIs('company.*'),
            ),
            $this->link(
                'catalogos',
                __('Catálogos'),
                route('catalogs.index'),
                $user->isAdmin(),
                request()->routeIs('catalogs.*'),
            ),
        ]);
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
