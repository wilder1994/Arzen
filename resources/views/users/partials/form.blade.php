@php
    $levels = collect(old('grants', $grantLevels))->only(array_keys($modules))->all() + $grantLevels;
    $pickedClients = collect(old('client_ids', $selectedClientIds))->map(fn ($id) => (int) $id)->values()->all();
    $nameParts = preg_split('/\s+/u', trim((string) $user->name), 2) ?: [];
    $firstName = old('first_name', $user->first_name ?? ($nameParts[0] ?? ''));
    $lastName = old('last_name', $user->last_name ?? ($nameParts[1] ?? ''));
    $formConfig = [
        'role' => old('role', $user->role),
        'levels' => $levels,
        'defaults' => $roleDefaults,
        'clients' => $clients->map(fn ($client) => ['id' => $client->id, 'name' => $client->name.($client->is_own_company ? ' · '.__('Mi empresa') : '')])->values(),
        'picked' => $pickedClients,
        'accountKind' => old('account_kind', $user->account_kind ?: \App\Models\User::KIND_PLATFORM),
        'fieldShared' => (bool) old('field_shared', $user->field_shared),
        'responsibles' => $responsibles->map(fn ($responsible) => ['id' => $responsible->id, 'name' => $responsible->name])->values(),
        'sharedIds' => collect(old('field_responsible_ids', $sharedResponsibleIds))->map(fn ($id) => (int) $id)->values(),
        'dayStart' => (int) old('field_day_start', $user->field_day_start ?: 1),
        'dayEnd' => (int) old('field_day_end', $user->field_day_end ?: 5),
    ];
@endphp

<script type="application/json" id="user-form-config">@json($formConfig)</script>
<div x-data="userAccessForm()">
    <h3 class="sj-form-section__title">{{ __('Datos del usuario') }}</h3>
    <div class="sj-user-identity">
        <div
            class="sj-user-portrait"
            x-show="accountKind === 'platform'"
            x-data="userPortrait(@js($user->portraitUrl()))"
        >
            <input x-ref="file" name="photo" type="file" accept="image/jpeg,image/png,image/webp" class="sr-only" :disabled="accountKind === 'field'" @change="useFile($event)">
            <button type="button" class="sj-user-portrait__camera" @click="openCamera()">{{ __('Tomar foto') }}</button>
            <button type="button" class="sj-user-portrait__frame" @click="$refs.file.click()" :aria-label="@js(__('Elegir foto'))">
                <img x-show="preview" :src="preview" alt="">
                <span x-show="!preview" class="sj-user-portrait__empty">{{ __('Foto') }}</span>
            </button>
            <x-input-error :messages="$errors->get('photo')" />

            <div x-show="cameraOpen" x-cloak class="sj-user-camera" @keydown.escape.window="closeCamera()">
                <div class="sj-user-camera__panel" @click.outside="closeCamera()">
                    <video x-ref="video" autoplay playsinline muted></video>
                    <canvas x-ref="canvas" class="sr-only"></canvas>
                    <p x-show="cameraError" class="sj-company__hint" x-text="cameraError"></p>
                    <div class="sj-user-camera__actions">
                        <button type="button" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm" @click="closeCamera()">{{ __('Cancelar') }}</button>
                        <button type="button" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm" @click="capture()" :disabled="cameraError !== ''">{{ __('Usar esta foto') }}</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="sj-user-fields">
            <div class="sj-ui-field" x-show="accountKind === 'platform'">
                <label for="user-first-name" class="sj-ui-field__label">{{ __('Nombre') }}</label>
                <input id="user-first-name" name="first_name" type="text" maxlength="120" value="{{ $firstName }}" class="sj-ui-field__control" :required="accountKind === 'platform'" :disabled="accountKind === 'field'">
                <x-input-error :messages="$errors->get('first_name')" />
            </div>
            <div class="sj-ui-field" x-show="accountKind === 'platform'">
                <label for="user-last-name" class="sj-ui-field__label">{{ __('Apellido') }}</label>
                <input id="user-last-name" name="last_name" type="text" maxlength="120" value="{{ $lastName }}" class="sj-ui-field__control" :required="accountKind === 'platform'" :disabled="accountKind === 'field'">
                <x-input-error :messages="$errors->get('last_name')" />
            </div>
            <div class="sj-ui-field" x-show="accountKind === 'platform'">
                <label for="user-document-type" class="sj-ui-field__label">{{ __('Tipo de documento') }}</label>
                <select id="user-document-type" name="document_type" class="sj-ui-field__control" :required="accountKind === 'platform'" :disabled="accountKind === 'field'">
                    @foreach (\App\Models\User::documentTypeLabels() as $value => $label)
                        <option value="{{ $value }}" @selected(old('document_type', $user->document_type ?? 'CC') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('document_type')" />
            </div>
            <div class="sj-ui-field" x-show="accountKind === 'platform'">
                <label for="user-document" class="sj-ui-field__label">{{ __('Número de documento') }}</label>
                <input id="user-document" name="document" type="text" maxlength="50" value="{{ old('document', $user->document) }}" class="sj-ui-field__control" :required="accountKind === 'platform'" :disabled="accountKind === 'field'">
                <x-input-error :messages="$errors->get('document')" />
            </div>
            <div class="sj-ui-field" x-show="accountKind === 'platform'">
                <label for="user-phone" class="sj-ui-field__label">{{ __('Teléfono de contacto') }}</label>
                <input id="user-phone" name="phone" type="tel" maxlength="30" value="{{ old('phone', $user->phone) }}" class="sj-ui-field__control" :disabled="accountKind === 'field'">
                <x-input-error :messages="$errors->get('phone')" />
            </div>
            <div class="sj-ui-field" x-show="accountKind === 'platform'">
                <label for="user-email" class="sj-ui-field__label">{{ __('Correo corporativo') }}</label>
                <input id="user-email" name="email" type="email" maxlength="255" value="{{ old('email', $user->email) }}" class="sj-ui-field__control" :required="accountKind === 'platform'" :disabled="accountKind === 'field'">
                <x-input-error :messages="$errors->get('email')" />
            </div>
        </div>
        <div class="sj-user-fields sj-user-fields--tail">
            <div class="sj-ui-field" x-show="accountKind === 'platform'">
                <div class="sj-ui-field__label-row">
                    <label for="user-position" class="sj-ui-field__label">{{ __('Cargo') }}</label>
                    <x-help-tip :text="__('Se crean en Ajustes → Cargos. Es el mismo catálogo que usan los trabajadores.')" />
                </div>
                <select id="user-position" name="position_id" class="sj-ui-field__control" :disabled="accountKind === 'field'">
                    <option value="">{{ __('Sin cargo') }}</option>
                    @foreach ($positions as $position)
                        <option value="{{ $position->id }}" @selected((int) old('position_id', $user->position_id) === $position->id)>{{ $position->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('position_id')" />
            </div>
            <div class="sj-ui-field sm:col-span-2">
                @if ($user->exists)
                    <input type="hidden" name="account_kind" value="{{ $user->account_kind ?: \App\Models\User::KIND_PLATFORM }}">
                @endif
                <span class="sj-ui-field__label">{{ __('Tipo de usuario') }}</span>
                <div class="mt-2 flex flex-wrap gap-4">
                    <label class="sj-company__check">
                        <input type="radio" name="account_kind" value="platform" class="sj-ui-check" x-model="accountKind" @disabled($user->exists)>
                        <span>{{ __('Plataforma') }}</span>
                    </label>
                    <label class="sj-company__check">
                        <input type="radio" name="account_kind" value="field" class="sj-ui-check" x-model="accountKind" @disabled($user->exists)>
                        <span>{{ __('Campo') }}</span>
                    </label>
                </div>
            </div>
            <div class="sj-ui-field" x-show="accountKind === 'platform'">
                <div class="sj-ui-field__label-row">
                    <label for="user-role" class="sj-ui-field__label">{{ __('Rol') }}</label>
                    <x-help-tip :text="__('El rol define qué datos ve el usuario (por ejemplo, el responsable solo ve los clientes de su cartera). Al cambiarlo se cargan sus permisos por defecto en la matriz.')" />
                </div>
                <select id="user-role" name="role" class="sj-ui-field__control" x-model="role" @change="applyDefaults()" :required="accountKind === 'platform'">
                    @foreach ($roles as $value => $label)
                        @continue($value === \App\Models\User::ROLE_ADMIN && ! $canAssignAdmin)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('role')" />
            </div>
            <div class="sj-ui-field" x-show="accountKind === 'platform'">
                <label for="user-active" class="sj-ui-field__label">{{ __('Estado') }}</label>
                <select id="user-active" name="is_active" class="sj-ui-field__control" :disabled="accountKind === 'field'">
                    <option value="1" @selected((string) old('is_active', (int) $user->is_active) === '1')>{{ __('Activo') }}</option>
                    <option value="0" @selected((string) old('is_active', (int) $user->is_active) === '0')>{{ __('Inactivo') }}</option>
                </select>
                <x-input-error :messages="$errors->get('is_active')" />
            </div>
        </div>
    </div>

    <div x-show="accountKind === 'field'" x-cloak class="mt-6 space-y-4">
        <h3 class="sj-form-section__title">{{ __('Usuario de campo') }}</h3>
        <input type="hidden" name="is_active" value="{{ (string) old('is_active', (int) $user->is_active) }}" :disabled="accountKind !== 'field'">
        <div class="sj-user-identity">
            <div class="sj-ui-field">
                <label for="field-email" class="sj-ui-field__label">{{ __('Correo') }}</label>
                <input id="field-email" name="email" type="email" maxlength="255" value="{{ old('email', $user->email) }}" class="sj-ui-field__control" :required="accountKind === 'field'" :disabled="accountKind !== 'field'">
                <x-input-error :messages="$errors->get('email')" />
            </div>
            <div class="sj-ui-field">
                <label for="field-zone" class="sj-ui-field__label">{{ __('Zona') }}</label>
                <input id="field-zone" name="zone" type="text" class="sj-ui-field__control" value="{{ old('zone', $user->zone) }}">
                <x-input-error :messages="$errors->get('zone')" />
            </div>
            <div class="sj-ui-field">
                <label for="field-owner" class="sj-ui-field__label">{{ __('Responsable') }}</label>
                <div data-assignment-combobox>
                    <select id="field-owner" name="field_owner_id" class="sj-ui-field__control">
                        <option value="">{{ __('Seleccione') }}</option>
                        @foreach ($responsibles as $responsible)
                            <option value="{{ $responsible->id }}" @selected((int) old('field_owner_id', $user->field_owner_id) === $responsible->id)>{{ $responsible->name }}</option>
                        @endforeach
                    </select>
                </div>
                <x-input-error :messages="$errors->get('field_owner_id')" />
            </div>
            <label class="sj-company__check">
                <input type="checkbox" name="field_shared" value="1" class="sj-ui-check" x-model="fieldShared">
                <span>{{ __('Compartido') }}</span>
            </label>
            <div class="sj-ui-field sm:col-span-2" x-show="fieldShared" x-cloak>
                <span class="sj-ui-field__label">{{ __('Responsables con los que se comparte') }}</span>
                <div class="mt-2 max-h-40 space-y-1 overflow-y-auto rounded border border-slate-200 p-2">
                    <template x-for="responsible in responsibles" :key="responsible.id">
                        <label class="sj-company__check">
                            <input type="checkbox" class="sj-ui-check" name="field_responsible_ids[]" :value="responsible.id" :checked="sharedIds.includes(responsible.id)">
                            <span x-text="responsible.name"></span>
                        </label>
                    </template>
                </div>
            </div>
            <div class="sj-ui-field">
                <label class="sj-ui-field__label">{{ __('Días de cada mes') }}</label>
                <div class="mt-1 flex items-center gap-2">
                    <input name="field_day_start" type="number" min="1" max="31" class="sj-ui-field__control w-24" x-model.number="dayStart">
                    <span>{{ __('al') }}</span>
                    <input name="field_day_end" type="number" min="1" max="31" class="sj-ui-field__control w-24" x-model.number="dayEnd">
                </div>
                <p class="sj-company__hint" x-text="'Del día ' + dayStart + ' al día ' + dayEnd + ' de cada mes.'"></p>
                <x-input-error :messages="$errors->get('field_day_start')" />
                <x-input-error :messages="$errors->get('field_day_end')" />
            </div>
        </div>
    </div>

    <div x-show="accountKind === 'platform'">
    <hr class="sj-ui-divider">

    <div class="sj-form-section__title sj-ui-field__label-row">
        <button type="button" class="sj-user-clients__toggle" x-show="role !== 'ADMIN'" x-on:click="clientsOpen = !clientsOpen" x-bind:aria-expanded="clientsOpen.toString()">
            <span class="sj-catalog__chevron" x-bind:class="clientsOpen && 'is-open'" aria-hidden="true"></span>
            <span>{{ __('Clientes (cartera)') }}</span>
            <span class="sj-company__hint" x-text="picked.length + ' {{ __('seleccionados') }}'"></span>
        </button>
        <h3 x-show="role === 'ADMIN'">{{ __('Clientes (cartera)') }}</h3>
        <x-help-tip :text="__('Clientes cuyas armas, puestos y trabajadores puede ver y operar el usuario. El súper administrador ve todos.')" />
    </div>
    <div x-show="role === 'ADMIN'" class="sj-company__hint">{{ __('El súper administrador tiene acceso a todos los clientes.') }}</div>
    <div x-show="role !== 'ADMIN' && clientsOpen" x-cloak class="sj-user-clients">
        <div class="sj-user-clients__toolbar">
            <input type="search" x-model="clientQuery" class="sj-ui-field__control" placeholder="{{ __('Filtrar clientes…') }}" aria-label="{{ __('Filtrar clientes') }}">
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

    <div class="sj-form-section__title sj-grant-head">
        <h3>{{ __('Permisos por módulo') }}</h3>
        <x-help-tip :text="__('Nada: no ve el módulo. Ver: consulta sin modificar. Gestionar: puede crear, editar y eliminar.')" />
        <button type="button" class="sj-ui-link" x-show="role !== 'ADMIN'" @click="applyDefaults()">{{ __('Restablecer según el rol') }}</button>
    </div>
    <div x-show="role === 'ADMIN'" class="sj-company__hint">{{ __('El súper administrador tiene acceso total a todos los módulos.') }}</div>
    <div x-show="role !== 'ADMIN'">
        <div class="sj-table-wrap">
            <table class="sj-table sj-table--compact sj-grant-matrix">
                <thead>
                    <tr>
                        <th>{{ __('Módulo') }}</th>
                        <th>{{ __('Vista') }}</th>
                        @foreach ($levelLabels as $label)
                            <th>{{ $label }}</th>
                        @endforeach
                        <th>{{ __('Ayuda') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($modules as $key => $module)
                        <tr>
                            <td>{{ $module['group'] }}</td>
                            <td>{{ $module['label'] }}</td>
                            @foreach (array_keys($levelLabels) as $level)
                                <td>
                                    <input type="radio" name="grants[{{ $key }}]" value="{{ $level }}" class="sj-ui-check" x-model="levels['{{ $key }}']" aria-label="{{ $module['label'] }}: {{ $levelLabels[$level] }}">
                                </td>
                            @endforeach
                            <td>
                                <x-help-tip :text="$module['hint']" />
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <x-input-error :messages="$errors->get('grants')" />
    </div>
    </div>
</div>

@once
    @push('scripts')
        <script>
            window.userPortrait = function (initial) {
                return {
                    preview: initial || '',
                    cameraOpen: false,
                    cameraError: '',
                    stream: null,
                    useFile(event) {
                        const file = event.target.files && event.target.files[0];
                        if (!file) {
                            return;
                        }
                        if (this.preview && this.preview.startsWith('blob:')) {
                            URL.revokeObjectURL(this.preview);
                        }
                        this.preview = URL.createObjectURL(file);
                    },
                    async openCamera() {
                        this.cameraError = '';
                        this.cameraOpen = true;
                        try {
                            this.stream = await navigator.mediaDevices.getUserMedia({ video: { facingMode: 'user' }, audio: false });
                            this.$refs.video.srcObject = this.stream;
                        } catch (e) {
                            this.cameraError = @js(__('No se pudo abrir la cámara. Elija una foto desde el recuadro.'));
                        }
                    },
                    closeCamera() {
                        if (this.stream) {
                            this.stream.getTracks().forEach((track) => track.stop());
                            this.stream = null;
                        }
                        this.cameraOpen = false;
                    },
                    capture() {
                        const video = this.$refs.video;
                        const canvas = this.$refs.canvas;
                        if (!video.videoWidth) {
                            return;
                        }
                        canvas.width = video.videoWidth;
                        canvas.height = video.videoHeight;
                        canvas.getContext('2d').drawImage(video, 0, 0);
                        canvas.toBlob((blob) => {
                            if (!blob) {
                                return;
                            }
                            const file = new File([blob], 'selfie.jpg', { type: 'image/jpeg' });
                            const transfer = new DataTransfer();
                            transfer.items.add(file);
                            this.$refs.file.files = transfer.files;
                            if (this.preview && this.preview.startsWith('blob:')) {
                                URL.revokeObjectURL(this.preview);
                            }
                            this.preview = URL.createObjectURL(blob);
                            this.closeCamera();
                        }, 'image/jpeg', 0.9);
                    },
                };
            };

            window.userAccessForm = function () {
                const cfg = JSON.parse(document.getElementById('user-form-config').textContent);

                return {
                    role: cfg.role,
                    accountKind: cfg.accountKind || 'platform',
                    fieldShared: !!cfg.fieldShared,
                    responsibles: cfg.responsibles || [],
                    sharedIds: cfg.sharedIds || [],
                    dayStart: cfg.dayStart || 1,
                    dayEnd: cfg.dayEnd || 5,
                    levels: { ...cfg.levels },
                    clients: cfg.clients,
                    picked: cfg.picked,
                    clientsOpen: cfg.picked.length > 0,
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
