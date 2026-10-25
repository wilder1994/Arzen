<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Aplica la matriz de permisos: Ver permite consultar; Gestionar permite además crear, editar y eliminar.
 */
class EnsureModuleAccess
{
    public function handle(Request $request, Closure $next, string ...$modules): Response
    {
        $user = $request->user();

        if (! $user || $user->isAdmin()) {
            return $next($request);
        }

        $writes = ! $request->isMethodSafe() || $this->isWriteForm($request);

        foreach ($modules as $module) {
            if ($writes ? $user->canManageModule($module) : $user->canViewModule($module)) {
                return $next($request);
            }
        }

        abort(403, __('Su usuario no tiene permiso para este módulo.'));
    }

    private function isWriteForm(Request $request): bool
    {
        $name = (string) $request->route()?->getName();

        return str_ends_with($name, '.create') || str_ends_with($name, '.edit');
    }
}
