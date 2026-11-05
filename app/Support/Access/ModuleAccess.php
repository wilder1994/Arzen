<?php

namespace App\Support\Access;

use App\Models\User;

/**
 * Matriz de permisos por módulo (Nada / Ver / Gestionar), con el mismo esquema que Zentra.
 */
final class ModuleAccess
{
    public const NONE = 'none';

    public const VIEW = 'view';

    public const MANAGE = 'manage';

    /**
     * @return array<string, array{label: string, group: string, hint: string}>
     */
    public static function modules(): array
    {
        return [
            'dashboard' => ['label' => __('Inicio (tablero)'), 'group' => __('Armamento'), 'hint' => __('Indicadores y gráficos del inventario.')],
            'weapons' => ['label' => __('Armas'), 'group' => __('Armamento'), 'hint' => __('Gestionar permite asignación interna, custodia, documentos y fotos de las armas de su cartera.')],
            'revista' => ['label' => __('Revista de armas'), 'group' => __('Armamento'), 'hint' => __('Revisión fotográfica mensual con usuarios temporales.')],
            'map' => ['label' => __('Mapa'), 'group' => __('Armamento'), 'hint' => __('Ubicación de las armas en el mapa.')],
            'transfers' => ['label' => __('Transferencias'), 'group' => __('Armamento'), 'hint' => __('Enviar y recibir armas entre responsables.')],
            'portfolios' => ['label' => __('Asignaciones (carteras)'), 'group' => __('Armamento'), 'hint' => __('Carteras de clientes de los responsables.')],
            'reports' => ['label' => __('Reportes'), 'group' => __('Armamento'), 'hint' => __('Reportes de armas, novedades, custodia y auditoría.')],
            'alerts' => ['label' => __('Alertas'), 'group' => __('Armamento'), 'hint' => __('Vencimientos y cartas de revalidación.')],
            'vests' => ['label' => __('Chalecos'), 'group' => __('Dotación'), 'hint' => __('Inventario y asignación de chalecos.')],
            'clients' => ['label' => __('Clientes e instalaciones'), 'group' => __('Plataforma'), 'hint' => __('Ficha del cliente, sus instalaciones y ubicación.')],
            'posts' => ['label' => __('Instalaciones'), 'group' => __('Plataforma'), 'hint' => __('Sedes del cliente y los puestos de cada una.')],
            'workers' => ['label' => __('Trabajadores'), 'group' => __('Plataforma'), 'hint' => __('Personal de los clientes y su puesto.')],
            'imports' => ['label' => __('Cargas masivas'), 'group' => __('Plataforma'), 'hint' => __('Subir armas, clientes, trabajadores y chalecos desde Excel.')],
            'formats' => ['label' => __('Formatos'), 'group' => __('Plataforma'), 'hint' => __('Descarga de la revista mensual y de los formatos de carga.')],
            'company' => ['label' => __('Mi empresa'), 'group' => __('Mi empresa'), 'hint' => __('Datos legales, ubicación, logo y membrete.')],
            'settings' => ['label' => __('Ajustes'), 'group' => __('Ajustes'), 'hint' => __('Cargos, vinculación, contratos y catálogos de novedades.')],
            'users' => ['label' => __('Usuarios'), 'group' => __('Ajustes'), 'hint' => __('Crear usuarios, su rol, cargo, clientes y permisos.')],
        ];
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(self::modules());
    }

    /**
     * @return array<string, string>
     */
    public static function levelLabels(): array
    {
        return [
            self::NONE => __('Nada'),
            self::VIEW => __('Ver'),
            self::MANAGE => __('Gestionar'),
        ];
    }

    /**
     * Permisos que se cargan al elegir el rol; luego se ajustan en la matriz.
     *
     * @return array<string, string>
     */
    public static function defaultsForRole(?string $role): array
    {
        $levels = array_fill_keys(self::keys(), self::NONE);

        $set = function (array $modules, string $level) use (&$levels): void {
            foreach ($modules as $module) {
                $levels[$module] = $level;
            }
        };

        match ($role) {
            User::ROLE_ADMIN => $set(self::keys(), self::MANAGE),
            User::ROLE_RESPONSABLE => (function () use ($set) {
                $set(['dashboard', 'map', 'formats', 'clients'], self::VIEW);
                $set(['weapons', 'revista', 'transfers', 'vests', 'posts', 'workers'], self::MANAGE);
            })(),
            User::ROLE_AUDITOR => $set(['dashboard', 'weapons', 'map', 'transfers', 'reports', 'alerts', 'vests', 'clients', 'posts', 'workers', 'formats'], self::VIEW),
            User::ROLE_ALMACEN => $set(['vests', 'imports'], self::MANAGE),
            default => null,
        };

        return $levels;
    }

    public static function isValidLevel(?string $level): bool
    {
        return in_array($level, [self::NONE, self::VIEW, self::MANAGE], true);
    }
}
