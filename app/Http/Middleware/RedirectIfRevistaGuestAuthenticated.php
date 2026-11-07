<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

final class RedirectIfRevistaGuestAuthenticated
{
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->session()->has('revista_field_user_id')) {
            return redirect()->route('revista-armas.guest.weapons.index');
        }

        return $next($request);
    }
}
