@php
    $levels = collect(old('grants', $grantLevels))->only(array_keys($modules))->all() + $grantLevels;
    $pickedClients = collect(old('client_ids', $selectedClientIds))->map(fn ($id) => (int) $id)->values()->all();
    $formConfig = [
        'role' => old('role', $user->role),
        'levels' => $levels,
        'defaults' => $roleDefaults,
        'clients' => $clients->map(fn ($client) => ['id' => $client->id, 'name' => $client->name.($client->is_own_company ? ' · '.__('Mi empresa') : '')])->values(),
        'picked' => $pickedClients,
    ];
@endphp

<script type="application/json" id="user-form-config">@json($formConfig)</script>
<div x-data="userAccessForm()">
    <h3 class="sj-form-section__title">{{ __('Datos del usuario') }}</h3>
    <div class="sj-company__grid">
        <div class="sj-ui-field">
            <label for="user-name" class="sj-ui-field__label">{{ __('Nombre') }}</label>
            <input id="user-name" name="name" type="text" maxlength="255" value="{{ old('name', $user->name) }}" class="sj-ui-field__control" required>
            <x-input-error :messages="$errors->get('name')" />
        </div>
        <div class="sj-ui-field">
            <label for="user-email" class="sj-ui-field__label">{{ __('Correo electrónico') }}</label>
            <input id="user-email" name="email" type="email" maxlength="255" value="{{ old('email', $user->email) }}" class="sj-ui-field__control" required>
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div class="sj-ui-field">
            <div class="sj-ui-field__label-row">
                <label for="user-role" class="sj-ui-field__label">{{ __('Rol') }}</label>
                <x-help-tip :text="__('El rol define qué datos ve el usuario (por ejemplo, el responsable solo ve los clientes de su cartera). Al cambiarlo se cargan sus permisos por defecto en la matriz.')" />
            </div>
            <select id="user-role" name="role" class="sj-ui-field__control" x-model="role" @change="applyDefaults()" required>
                @foreach ($roles as $value => $label)
                    @continue($value === \App\Models\User::ROLE_ADMIN && ! $canAssignAdmin)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('role')" />
        </div>
        <div class="sj-ui-field">
            <div class="sj-ui-field__label-row">
                <label for="user-position" class="sj-ui-field__label">{{ __('Cargo') }}</label>
                <x-help-tip :text="__('Se crean en Ajustes → Cargos. Es el mismo catálogo que usan los trabajadores.')" />
            </div>
            <select id="user-position" name="position_id" class="sj-ui-field__control">
                <option value="">{{ __('Sin cargo') }}</option>
                @foreach ($positions as $position)
                    <option value="{{ $position->id }}" @selected((int) old('position_id', $user->position_id) === $position->id)>{{ $position->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('position_id')" />
        </div>
        <div class="sj-ui-field">
            <label for="user-active" class="sj-ui-field__label">{{ __('Estado') }}</label>
            <select id="user-active" name="is_active" class="sj-ui-field__control">
                <option value="1" @selected((string) old('is_active', (int) $user->is_active) === '1')>{{ __('Activo') }}</option>
                <option value="0" @selected((string) old('is_active', (int) $user->is_active) === '0')>{{ __('Inactivo') }}</option>
            </select>
            <x-input-error :messages="$errors->get('is_active')" />
        </div>
        <div class="sj-ui-field">
            <label for="user-cost-center" class="sj-ui-field__label">{{ __('Centro de costo') }}</label>
            <input id="user-cost-center" name="cost_center" type="text" maxlength="100" value="{{ old('cost_center', $user->cost_center) }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('cost_center')" />
        </div>
    </div>

    <hr class="sj-ui-divider">

    <div class="sj-form-section__title sj-ui-field__label-row">
        <h3>{{ __('Clientes (cartera)') }}</h3>
        <x-help-tip :text="__('Clientes cuyas armas, puestos y trabajadores puede ver y operar el usuario. El súper administrador ve todos.')" />
    </div>
    <div x-show="role === 'ADMIN'" class="sj-company__hint">{{ __('El súper administrador tiene acceso a todos los clientes.') }}</div>
    <div x-show="role !== 'ADMIN'" class="sj-user-clients">
        <div class="sj-user-clients__toolbar">
            <input type="search" x-model="clientQuery" class="sj-ui-field__control" placeholder="{{ __('Filtrar clientes…') }}" aria-label="{{ __('Filtrar clientes') }}">
            <span class="sj-company__hint" x-text="picked.length + ' {{ __('seleccionados') }}'"></span>
        </div>
        <template x-for="id in picked" :key="'picked-' + id">
            <input type="hidden" name="client_ids[]" :value="id">
        </template>
        <div class="sj-user-clients__list">
            <template x-for="client in filteredClients" :key="client.id">
                <label class="sj-company__check">
                    <input type="checkbox" class="sj-ui-check rounded" :value="client.id" :checked="picked.includes(client.id)" @change="toggleClient(client.id)">
                    <span x-text="client.name"></span>
                </label>
            </template>
            <p x-show="filteredClients.length === 0" class="sj-company__hint">{{ __('Sin coincidencias.') }}</p>
        </div>
        <x-input-error :messages="$errors->get('client_ids')" />
    </div>

    <hr class="sj-ui-divider">

    <div class="sj-form-section__title sj-ui-field__label-row">
        <h3>{{ __('Permisos por módulo') }}</h3>
        <x-help-tip :text="__('Nada: no ve el módulo. Ver: consulta sin modificar. Gestionar: puede crear, editar y eliminar.')" />
    </div>
    <div x-show="role === 'ADMIN'" class="sj-company__hint">{{ __('El súper administrador tiene acceso total a todos los módulos.') }}</div>
    <div x-show="role !== 'ADMIN'">
        <div class="mb-2 flex flex-wrap items-center justify-between gap-2">
            <p class="sj-company__hint">{{ __('Se cargan según el rol y se pueden ajustar uno por uno.') }}</p>
            <button type="button" class="sj-ui-link" @click="applyDefaults()">{{ __('Restablecer según el rol') }}</button>
        </div>
        <div class="sj-table-wrap">
            <table class="sj-table sj-table--compact sj-table--align-left sj-grant-matrix">
                <thead>
                    <tr>
                        <th>{{ __('Módulo') }}</th>
                        @foreach ($levelLabels as $label)
                            <th class="text-center">{{ $label }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($modules as $key => $module)
                        <tr>
                            <td>
                                <span class="sj-ui-field__label-row mb-0">
                                    <span class="font-medium">{{ $module['label'] }}</span>
                                    <x-help-tip :text="$module['hint']" />
                                </span>
                                <span class="block text-xs text-gray-500">{{ $module['group'] }}</span>
                            </td>
                            @foreach (array_keys($levelLabels) as $level)
                                <td class="text-center">
                                    <input type="radio" name="grants[{{ $key }}]" value="{{ $level }}" class="sj-ui-check" x-model="levels['{{ $key }}']" aria-label="{{ $module['label'] }}: {{ $levelLabels[$level] }}">
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-input-error :messages="$errors->get('grants')" />
    </div>
</div>

@once
    @push('scripts')
        <script>
            window.userAccessForm = function () {
                const cfg = JSON.parse(document.getElementById('user-form-config').textContent);

                return {
                    role: cfg.role,
                    levels: { ...cfg.levels },
                    clients: cfg.clients,
                    picked: cfg.picked,
                    clientQuery: '',
                    get filteredClients() {
                        const query = this.clientQuery.trim().toLowerCase();

                        return query ? this.clients.filter((client) => client.name.toLowerCase().includes(query)) : this.clients;
                    },
                    toggleClient(id) {
                        this.picked = this.picked.includes(id) ? this.picked.filter((item) => item !== id) : [...this.picked, id];
                    },
                    applyDefaults() {
                        this.levels = { ...(cfg.defaults[this.role] || {}) };
                    },
                };
            };
        </script>
    @endpush
@endonce
