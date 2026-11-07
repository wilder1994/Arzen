<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class EnsureRevistaGuestSession
{
    public function handle(Request $request, Closure $next): Response
    {
        $fieldUserId = $request->session()->get('revista_field_user_id');
        $fieldUser = $fieldUserId
            ? User::query()->whereKey($fieldUserId)->where('account_kind', User::KIND_FIELD)->first()
            : null;

        if (! $fieldUser || ! $fieldUser->is_active || ! $fieldUser->fieldAccessOpen()) {
            $request->session()->forget('revista_field_user_id');

            return redirect()
                ->route('revista-armas.guest.login')
                ->withErrors(['email' => __('El ingreso de campo no está vigente.')]);
        }

        $request->attributes->set('revista_field_user', $fieldUser);

        return $next($request);
    }
}
