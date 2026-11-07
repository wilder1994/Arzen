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
use Illuminate\Support\Facades\Storage;

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
        $users = User::with(['position', 'clients'])
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
        if ($request->input('account_kind') === User::KIND_FIELD) {
            return $this->storeFieldUser($request);
        }

        $data = $request->validate($this->identityRules($request));

        $this->guardAdminRole($data['role']);
        [$clientIds, $grants] = $this->pullAccess($data);
        unset($data['photo'], $data['generate_temporary_password']);
        $data = $this->withDisplayName($data);

        $plainPassword = $this->temporaryPasswordGenerator->generate();
        $data['password'] = $plainPassword;
        $data['must_change_password'] = true;

        $user = User::create($data);
        $this->storePortrait($request, $user);
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

        if ($user->isField()) {
            return $this->updateFieldUser($request, $user);
        }

        $data = $request->validate($this->identityRules($request, $user));

        $this->guardAdminRole($data['role']);
        [$clientIds, $grants] = $this->pullAccess($data);
        unset($data['photo']);
        $data = $this->withDisplayName($data);

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
        $this->storePortrait($request, $user);
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

        if ($user->photo_path) {
            Storage::disk('public')->delete($user->photo_path);
        }

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

    /**
     * @return array<string, mixed>
     */
    private function storeFieldUser(Request $request)
    {
        $data = $request->validate($this->fieldRules($request));
        $sharedIds = $request->boolean('field_shared') ? $this->pullFieldShared($data) : $this->discardFieldShared($data);
        $data = $this->withFieldIdentity($data);
        $data['account_kind'] = User::KIND_FIELD;
        $data['role'] = User::ROLE_RESPONSABLE;
        $data['field_shared'] = $request->boolean('field_shared');

        $plainPassword = $this->temporaryPasswordGenerator->generate();
        $data['password'] = $plainPassword;
        $data['must_change_password'] = false;

        $user = User::create($data);
        $user->fieldSharedResponsibles()->sync($sharedIds);

        return redirect()
            ->route('users.index')
            ->with('status', 'Usuario de campo creado. Entregue el usuario y la contraseña una sola vez.')
            ->with('generated_temporary_password', $plainPassword);
    }

    private function updateFieldUser(Request $request, User $user)
    {
        $data = $request->validate($this->fieldRules($request, $user));
        $sharedIds = $request->boolean('field_shared') ? $this->pullFieldShared($data) : $this->discardFieldShared($data);
        $data = $this->withFieldIdentity($data);
        $data['account_kind'] = User::KIND_FIELD;
        $data['role'] = User::ROLE_RESPONSABLE;
        $data['field_shared'] = $request->boolean('field_shared');

        $generateTemporary = $request->boolean('generate_temporary_password');
        $passwordInput = (string) ($data['password'] ?? '');
        unset($data['password']);

        $plainFlash = null;
        if ($generateTemporary) {
            $plainFlash = $this->temporaryPasswordGenerator->generate();
            $data['password'] = $plainFlash;
            $data['must_change_password'] = false;
        } elseif ($passwordInput !== '') {
            $data['password'] = $passwordInput;
            $data['must_change_password'] = false;
        }

        $user->update($data);
        $user->fieldSharedResponsibles()->sync($sharedIds);

        $redirect = redirect()->route('users.index')->with('status', 'Usuario de campo actualizado.');
        if ($plainFlash) {
            $redirect->with('generated_temporary_password', $plainFlash);
        }

        return $redirect;
    }

    /**
     * @return array<string, mixed>
     */
    private function fieldRules(Request $request, ?User $user = null): array
    {
        return [
            'account_kind' => ['required', Rule::in([User::KIND_FIELD])],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'zone' => ['nullable', 'string', 'max:120'],
            'field_owner_id' => ['required', 'integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', User::ROLE_RESPONSABLE)->where('account_kind', User::KIND_PLATFORM))],
            'field_shared' => ['nullable', 'boolean'],
            'field_responsible_ids' => ['nullable', 'array'],
            'field_responsible_ids.*' => ['integer', Rule::exists('users', 'id')->where(fn ($query) => $query->where('role', User::ROLE_RESPONSABLE)->where('account_kind', User::KIND_PLATFORM))],
            'field_day_start' => ['required', 'integer', 'min:1', 'max:31'],
            'field_day_end' => ['required', 'integer', 'min:1', 'max:31', 'gte:field_day_start'],
            'is_active' => ['required', 'boolean'],
            'generate_temporary_password' => ['sometimes', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<int>
     */
    private function discardFieldShared(array &$data): array
    {
        $this->pullFieldShared($data);

        return [];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return list<int>
     */
    private function pullFieldShared(array &$data): array
    {
        $ids = collect($data['field_responsible_ids'] ?? [])
            ->map(fn ($id) => (int) $id)
            ->reject(fn (int $id) => $id === (int) ($data['field_owner_id'] ?? 0))
            ->unique()
            ->values()
            ->all();

        unset($data['field_responsible_ids'], $data['account_kind'], $data['generate_temporary_password']);

        return $ids;
    }

    /**
     * @return array<string, mixed>
     */
    private function identityRules(Request $request, ?User $user = null): array
    {
        $documentUnique = Rule::unique('users', 'document')
            ->where(fn ($query) => $query->where('document_type', $request->input('document_type')));

        if ($user) {
            $documentUnique = $documentUnique->ignore($user->id);
        }

        return [
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user?->id)],
            'document_type' => ['required', Rule::in(array_keys(User::documentTypeLabels()))],
            'document' => ['required', 'string', 'max:50', $documentUnique],
            'phone' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'role' => ['required', 'in:'.implode(',', array_keys($this->roleOptions()))],
            'position_id' => ['nullable', 'exists:positions,id'],
            'client_ids' => ['nullable', 'array'],
            'client_ids.*' => ['integer', 'exists:clients,id'],
            'grants' => ['nullable', 'array'],
            'grants.*' => ['nullable', Rule::in([ModuleAccess::NONE, ModuleAccess::VIEW, ModuleAccess::MANAGE])],
            'is_active' => ['required', 'boolean'],
            'generate_temporary_password' => ['sometimes', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withFieldIdentity(array $data): array
    {
        $zone = trim((string) ($data['zone'] ?? ''));
        $email = trim((string) $data['email']);
        $local = strstr($email, '@', true);

        $data['zone'] = $zone !== '' ? $zone : null;
        $data['name'] = $zone !== '' ? $zone : ($local !== false && $local !== '' ? $local : $email);
        $data['first_name'] = null;
        $data['last_name'] = null;
        $data['document_type'] = null;
        $data['document'] = null;
        $data['phone'] = null;
        $data['position_id'] = null;
        $data['photo_path'] = null;

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    private function withDisplayName(array $data): array
    {
        $data['first_name'] = trim((string) $data['first_name']);
        $data['last_name'] = trim((string) $data['last_name']);
        $data['name'] = trim($data['first_name'].' '.$data['last_name']);
        $data['phone'] = trim((string) ($data['phone'] ?? '')) ?: null;

        return $data;
    }

    private function storePortrait(Request $request, User $user): void
    {
        if (! $request->hasFile('photo')) {
            return;
        }

        if ($user->photo_path) {
            Storage::disk('public')->delete($user->photo_path);
        }

        $path = $request->file('photo')->store('users/'.$user->id, 'public');
        $user->update(['photo_path' => $path]);
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
            'responsibles' => User::query()
                ->where('account_kind', User::KIND_PLATFORM)
                ->where('role', User::ROLE_RESPONSABLE)
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name']),
            'sharedResponsibleIds' => $user->exists ? $user->fieldSharedResponsibles()->pluck('users.id')->map(fn ($id) => (int) $id)->all() : [],
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
