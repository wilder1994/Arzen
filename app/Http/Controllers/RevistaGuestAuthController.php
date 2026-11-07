<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RevistaGuestAuthController extends Controller
{
    public function showLogin(): View
    {
        return view('revista-armas.guest.login');
    }

    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $fieldUser = User::query()
            ->where('email', $data['email'])
            ->where('account_kind', User::KIND_FIELD)
            ->first();

        if (! $fieldUser || ! $fieldUser->is_active || ! Hash::check($data['password'], $fieldUser->password)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('Correo o contraseña incorrectos.')]);
        }

        if (! $fieldUser->fieldAccessOpen()) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => __('El ingreso de campo está cerrado. Se abre del día :start al día :end de cada mes.', [
                    'start' => $fieldUser->field_day_start,
                    'end' => $fieldUser->field_day_end,
                ])]);
        }

        $request->session()->put('revista_field_user_id', $fieldUser->id);

        return redirect()->route('revista-armas.guest.weapons.index');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('revista_field_user_id');

        return redirect()->route('revista-armas.guest.login');
    }
}
