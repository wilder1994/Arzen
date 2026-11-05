<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $before = $user->only(['name', 'email']);
        $user->fill($request->validated());

        if ($user->isDirty('name')) {
            $parts = preg_split('/\s+/u', trim((string) $user->name), 2) ?: [];
            $user->first_name = $parts[0] ?? '';
            $user->last_name = $parts[1] ?? '';
            $user->name = trim($user->first_name.' '.$user->last_name);
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'profile_updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'before' => $before,
            'after' => $user->only(['name', 'email']),
        ]);

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'profile_deleted',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'before' => $user->only(['name', 'email']),
            'after' => null,
        ]);

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}

