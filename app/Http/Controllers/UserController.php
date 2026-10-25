<?php

namespace App\Http\Controllers;

use App\Mail\UserAccessCredentialsMail;
use App\Models\AuditLog;
use App\Models\Position;
use App\Models\Client;
use App\Models\User;
use App\Services\UserTemporaryPasswordGenerator;
use App\Support\Access\ModuleAccess;
use Illuminate\Validation\Rule;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserController extends Controller
{
    public function __construct(
        private readonly UserTemporaryPasswordGenerator $temporaryPasswordGenerator
    ) {
        $this->middleware(function ($request, $next) {
            abort_unless($request->user()?->canViewModule('users'), 403);

            return $next($request);
        });
    }

    public function index()
    {
        $users = User::with(['position', 'clients', 'moduleGrants'])
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();
        $roles = $this->roleOptions();

        return view('users.index', compact('users', 'roles'));
    }

    public function create()
    {
        return view('users.create', $this->formData(new User(['role' => User::ROLE_RESPONSABLE, 'is_active' => true])));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:'.implode(',', array_keys($this->roleOptions()))],
            'position_id' => ['nullable', 'exists:positions,id'],
            'client_ids' => ['nullable', 'array'],
            'client_ids.*' => ['integer', 'exists:clients,id'],
            'grants' => ['nullable', 'array'],
            'grants.*' => ['nullable', Rule::in([ModuleAccess::NONE, ModuleAccess::VIEW, ModuleAccess::MANAGE])],
            'is_active' => ['required', 'boolean'],
            'cost_center' => ['nullable', 'string', 'max:100'],
        ]);

        $this->guardAdminRole($data['role']);
        [$clientIds, $grants] = $this->pullAccess($data);

        $plainPassword = $this->temporaryPasswordGenerator->generate();
        $data['password'] = $plainPassword;
        $data['must_change_password'] = true;

        $user = User::create($data);
        $user->clients()->sync($clientIds);
        $user->syncModuleLevels($grants);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'user_created',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'before' => null,
            'after' => $user->only(['name', 'email', 'role', 'position_id', 'is_active']),
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario creado. Guarde la contraseña temporal; no se volverá a mostrar.')
            ->with('generated_temporary_password', $plainPassword);
    }

    public function edit(User $user)
    {
        $this->guardAdminTarget($user);

        return view('users.edit', $this->formData($user));
    }

    public function update(Request $request, User $user)
    {
        $this->guardAdminTarget($user);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'generate_temporary_password' => ['sometimes', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role' => ['required', 'in:'.implode(',', array_keys($this->roleOptions()))],
            'position_id' => ['nullable', 'exists:positions,id'],
            'client_ids' => ['nullable', 'array'],
            'client_ids.*' => ['integer', 'exists:clients,id'],
            'grants' => ['nullable', 'array'],
            'grants.*' => ['nullable', Rule::in([ModuleAccess::NONE, ModuleAccess::VIEW, ModuleAccess::MANAGE])],
            'is_active' => ['required', 'boolean'],
            'cost_center' => ['nullable', 'string', 'max:100'],
        ]);

        $this->guardAdminRole($data['role']);
        [$clientIds, $grants] = $this->pullAccess($data);

        $generateTemporary = $request->boolean('generate_temporary_password');
        $passwordInput = (string) ($data['password'] ?? '');
        unset($data['password'], $data['generate_temporary_password']);

        $plainFlash = null;
        if ($generateTemporary) {
            $plainFlash = $this->temporaryPasswordGenerator->generate();
            $data['password'] = $plainFlash;
            $data['must_change_password'] = true;
        } elseif ($passwordInput !== '') {
            $data['password'] = $passwordInput;
            $data['must_change_password'] = false;
        }

        $before = $user->only(['name', 'email', 'role', 'position_id', 'is_active']);
        $user->update($data);
        $user->clients()->sync($clientIds);
        $user->syncModuleLevels($grants);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'user_updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'before' => $before,
            'after' => $user->only(['name', 'email', 'role', 'position_id', 'is_active']),
        ]);

        $redirect = redirect()->route('users.index')->with('status', 'Usuario actualizado.');

        if ($plainFlash !== null) {
            $redirect->with('generated_temporary_password', $plainFlash)
                ->with('status', 'Usuario actualizado. Guarde la contraseña temporal; no se volverá a mostrar.');
        }

        return $redirect;
    }

    public function sendAccessCredentials(Request $request, User $user)
    {
        $this->guardAdminTarget($user);

        if (! $user->is_active) {
            return redirect()
                ->route('users.index')
                ->withErrors(['email' => __('No se pueden enviar credenciales a un usuario inactivo.')]);
        }

        $plainPassword = $this->temporaryPasswordGenerator->generate();
        $user->update([
            'password' => $plainPassword,
            'must_change_password' => true,
        ]);

        $loginUrl = in_array($request->getHost(), ['localhost', '127.0.0.1', '::1'], true)
            ? rtrim((string) config('app.url'), '/')
            : $request->getSchemeAndHttpHost();
        $appName = (string) config('app.name');

        try {
            Mail::to($user->email)->send(new UserAccessCredentialsMail(
                recipientName: $user->name,
                loginUrl: $loginUrl,
                loginEmail: $user->email,
                temporaryPassword: $plainPassword,
                appName: $appName,
            ));
        } catch (\Throwable $e) {
            Log::error('sendAccessCredentials mail failed', [
                'user_id' => $user->id,
                'exception' => $e,
            ]);

            $errorMessage = __('El correo no pudo enviarse. La contraseña se actualizó; cópiela y compártala manualmente.');
            if (config('app.debug')) {
                $errorMessage .= ' ('.$e->getMessage().')';
            }

            return redirect()
                ->route('users.index')
                ->withErrors(['email' => $errorMessage])
                ->with('generated_temporary_password', $plainPassword);
        }

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'user_credentials_emailed',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'before' => null,
            'after' => [
                'email' => $user->email,
                'must_change_password' => true,
            ],
        ]);

        return redirect()
            ->route('users.index')
            ->with('status', __('Se enviaron las credenciales de acceso por correo a :email.', ['email' => $user->email]));
    }

    public function updateStatus(Request $request, User $user)
    {
        $this->guardAdminTarget($user);

        $data = $request->validate([
            'is_active' => ['nullable', 'boolean'],
        ]);

        $isActive = array_key_exists('is_active', $data)
            ? (bool) $data['is_active']
            : ! $user->is_active;

        $before = ['is_active' => $user->is_active];
        $user->update(['is_active' => $isActive]);

        AuditLog::create([
            'user_id' => $request->user()?->id,
            'action' => 'user_status_updated',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'before' => $before,
            'after' => ['is_active' => $isActive],
        ]);

        return redirect()->route('users.index')->with('status', 'Estado actualizado.');
    }

    public function destroy(User $user)
    {
        $this->guardAdminTarget($user);

        AuditLog::create([
            'user_id' => request()->user()?->id,
            'action' => 'user_deleted',
            'auditable_type' => User::class,
            'auditable_id' => $user->id,
            'before' => $user->only(['name', 'email', 'role', 'is_active']),
            'after' => null,
        ]);

        $user->delete();

        return redirect()->route('users.index')->with('status', 'Usuario eliminado.');
    }

    private function roleOptions(): array
    {
        return User::roleLabels();
    }

    /**
     * @return array<string, mixed>
     */
    private function formData(User $user): array
    {
        $levels = $user->exists ? $user->moduleLevels() : ModuleAccess::defaultsForRole($user->role);

        return [
            'user' => $user,
            'roles' => $this->roleOptions(),
            'positions' => Position::query()->active()->ordered()->get(),
            'clients' => Client::query()->orderByDesc('is_own_company')->orderBy('name')->get(['id', 'name', 'is_own_company']),
            'selectedClientIds' => $user->exists ? $user->clients()->pluck('clients.id')->map(fn ($id) => (int) $id)->all() : [],
            'modules' => ModuleAccess::modules(),
            'levelLabels' => ModuleAccess::levelLabels(),
            'grantLevels' => $levels,
            'roleDefaults' => collect(array_keys($this->roleOptions()))
                ->mapWithKeys(fn (string $role) => [$role => ModuleAccess::defaultsForRole($role)])
                ->all(),
            'canAssignAdmin' => request()->user()?->isAdmin() ?? false,
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array{0: list<int>, 1: array<string, string>}
     */
    private function pullAccess(array &$data): array
    {
        $clientIds = array_values(array_unique(array_map('intval', $data['client_ids'] ?? [])));
        $grants = array_intersect_key($data['grants'] ?? [], array_flip(ModuleAccess::keys()));
        unset($data['client_ids'], $data['grants']);

        return [$clientIds, $grants];
    }

    private function guardAdminRole(string $role): void
    {
        abort_if($role === User::ROLE_ADMIN && ! request()->user()?->isAdmin(), 403, __('Solo un súper administrador puede asignar ese rol.'));
    }

    private function guardAdminTarget(User $user): void
    {
        abort_if($user->isAdmin() && ! request()->user()?->isAdmin(), 403, __('Solo un súper administrador puede editar a otro súper administrador.'));
    }
}
