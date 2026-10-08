@php
    $isEdit = isset($vest);
    $user = auth()->user();
    $selectedClientId = old('client_id', $vest->client_id ?? $clientId ?? null);
    $hasClient = filled($selectedClientId);
    $deviceResponsibleValue = old(
        'device_responsible',
        $vest->device_responsible ?? ($lockDeviceResponsible ? $user->name : '')
    );

    if (! $lockDeviceResponsible && $hasClient) {
        $mappedResponsible = $clientResponsibleMap[$selectedClientId]['name'] ?? null;
        if ($mappedResponsible && ! old('device_responsible')) {
            $deviceResponsibleValue = $mappedResponsible;
        }
    }

    $deviceResponsibleDisplay = $deviceResponsibleValue ?: __('Sin responsable asignado');
@endphp

<div class="grid grid-cols-1 gap-4 md:grid-cols-2">
    <div class="sj-ui-field">
        <label for="vest-client-search" class="sj-ui-field__label">{{ __('Cliente') }}</label>
        <div
            class="relative"
            data-assignment-combobox
            data-vest-client-combobox
            data-empty-message="{{ __('No se encontraron clientes.') }}"
        >
            <select name="client_id" id="vest-client-select" class="hidden" required data-combobox-select>
                <option value="">{{ __('Seleccione') }}</option>
                @foreach ($clients as $client)
                    @php
                        $responsibleMeta = $clientResponsibleMap[$client->id] ?? ['id' => null, 'name' => null];
                    @endphp
                    <option
                        value="{{ $client->id }}"
                        data-label="{{ $client->name }}"
                        data-search-text="{{ $client->name }}"
                        data-responsible-id="{{ $responsibleMeta['id'] ?? '' }}"
                        data-responsible-name="{{ $responsibleMeta['name'] ?? '' }}"
                        @selected((string) $selectedClientId === (string) $client->id)
                    >{{ $client->name }}</option>
                @endforeach
            </select>

            <input
                type="text"
                id="vest-client-search"
                data-combobox-search
                class="sj-ui-field__control pr-10"
                placeholder="{{ __('Buscar cliente...') }}"
                autocomplete="off"
                spellcheck="false"
                role="combobox"
                aria-expanded="false"
                aria-controls="vest-client-options"
            >

            <button
                type="button"
                data-combobox-toggle
                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"
                aria-label="{{ __('Mostrar clientes') }}"
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>

            <div
                id="vest-client-options"
                data-combobox-panel
                class="absolute left-0 right-0 z-20 mt-2 hidden max-h-72 overflow-y-auto rounded-md border border-slate-200 bg-white py-1 shadow-xl"
                role="listbox"
            ></div>
        </div>
        <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
    </div>

    <div class="sj-ui-field">
        <label for="serial_number" class="sj-ui-field__label">{{ __('No. serie o código') }}</label>
        <input
            type="text"
            id="serial_number"
            name="serial_number"
            value="{{ old('serial_number', $vest->serial_number ?? '') }}"
            required
            class="sj-ui-field__control"
        >
        <x-input-error :messages="$errors->get('serial_number')" class="mt-2" />
    </div>

    <div class="sj-ui-field">
        <label for="brand" class="sj-ui-field__label">{{ __('Marca') }}</label>
        <input type="text" id="brand" name="brand" value="{{ old('brand', $vest->brand ?? '') }}" class="sj-ui-field__control">
        <x-input-error :messages="$errors->get('brand')" class="mt-2" />
    </div>

    <div class="sj-ui-field">
        <label for="batch" class="sj-ui-field__label">{{ __('Lote') }}</label>
        <input type="text" id="batch" name="batch" value="{{ old('batch', $vest->batch ?? '') }}" class="sj-ui-field__control">
        <x-input-error :messages="$errors->get('batch')" class="mt-2" />
    </div>

    <div class="sj-ui-field">
        <label for="size" class="sj-ui-field__label">{{ __('Talla') }}</label>
        <input type="text" id="size" name="size" value="{{ old('size', $vest->size ?? '') }}" class="sj-ui-field__control">
        <x-input-error :messages="$errors->get('size')" class="mt-2" />
    </div>

    <div class="sj-ui-field">
        <label class="sj-ui-field__label">{{ __('Responsable dispositivo') }}</label>
        @if ($lockDeviceResponsible)
            <input type="hidden" name="device_responsible" value="{{ $user->name }}" data-vest-device-responsible-input>
            <input
                type="text"
                readonly
                tabindex="-1"
                class="sj-ui-field__control cursor-default bg-slate-50 text-slate-800"
                data-vest-device-responsible-display
                value="{{ $user->name }}"
            >
            <p class="sj-form-help">{{ __('Asignado automáticamente a su usuario.') }}</p>
        @else
            <input type="hidden" name="device_responsible" value="{{ $deviceResponsibleValue }}" data-vest-device-responsible-input>
            <input
                type="text"
                readonly
                tabindex="-1"
                class="sj-ui-field__control cursor-default bg-slate-50 text-slate-800"
                data-vest-device-responsible-display
                value="{{ $deviceResponsibleDisplay }}"
            >
        @endif
        <x-input-error :messages="$errors->get('device_responsible')" class="mt-2" />
    </div>

    <div class="sj-ui-field">
        <label for="manufactured_at" class="sj-ui-field__label">{{ __('Fecha fabricación') }}</label>
        <input
            type="date"
            id="manufactured_at"
            name="manufactured_at"
            value="{{ old('manufactured_at', optional($vest->manufactured_at ?? null)?->format('Y-m-d')) }}"
            class="sj-ui-field__control"
        >
        <x-input-error :messages="$errors->get('manufactured_at')" class="mt-2" />
    </div>

    <div class="sj-ui-field">
        <label for="expires_at" class="sj-ui-field__label">{{ __('Fecha vencimiento') }}</label>
        <input
            type="date"
            id="expires_at"
            name="expires_at"
            value="{{ old('expires_at', optional($vest->expires_at ?? null)?->format('Y-m-d')) }}"
            class="sj-ui-field__control"
        >
        <x-input-error :messages="$errors->get('expires_at')" class="mt-2" />
    </div>

    <div class="sj-ui-field">
        <label for="vest-worker-search" class="sj-ui-field__label">{{ __('Trabajador') }}</label>
        <div
            class="relative"
            data-assignment-combobox
            data-vest-worker-combobox
            data-empty-message="{{ __('No se encontraron trabajadores.') }}"
        >
            <select name="worker_id" id="vest-worker-select" class="hidden" data-combobox-select @disabled(! $hasClient)>
                <option value="">{{ __('Sin asignar') }}</option>
                @foreach ($workers as $worker)
                    @php
                        $workerRoleLabel = \App\Models\Worker::roleLabels()[$worker->role] ?? $worker->role;
                        $workerSearchText = trim(implode(' ', array_filter([
                            $worker->name,
                            $worker->document,
                            $workerRoleLabel,
                        ])));
                    @endphp
                    <option
                        value="{{ $worker->id }}"
                        data-label="{{ $worker->name }}"
                        data-subtitle="{{ $workerRoleLabel }}{{ $worker->document ? ' · ' . $worker->document : '' }}"
                        data-search-text="{{ $workerSearchText }}"
                        @selected((string) old('worker_id', $vest->worker_id ?? null) === (string) $worker->id)
                    >{{ $worker->name }}</option>
                @endforeach
            </select>

            <input
                type="text"
                id="vest-worker-search"
                data-combobox-search
                class="sj-ui-field__control pr-10 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400"
                placeholder="{{ __('Buscar trabajador...') }}"
                autocomplete="off"
                spellcheck="false"
                role="combobox"
                aria-expanded="false"
                aria-controls="vest-worker-options"
                @disabled(! $hasClient)
            >

            <button
                type="button"
                data-combobox-toggle
                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 disabled:cursor-not-allowed disabled:opacity-50"
                aria-label="{{ __('Mostrar trabajadores') }}"
                @disabled(! $hasClient)
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>

            <div
                id="vest-worker-options"
                data-combobox-panel
                class="absolute left-0 right-0 z-20 mt-2 hidden max-h-72 overflow-y-auto rounded-md border border-slate-200 bg-white py-1 shadow-xl"
                role="listbox"
            ></div>
        </div>
        <x-input-error :messages="$errors->get('worker_id')" class="mt-2" />
    </div>

    <div class="sj-ui-field">
        <label for="vest-post-search" class="sj-ui-field__label">{{ __('Puesto') }}</label>
        <div
            class="relative"
            data-assignment-combobox
            data-vest-post-combobox
            data-empty-message="{{ __('No se encontraron puestos.') }}"
        >
            <select name="post_id" id="vest-post-select" class="hidden" data-combobox-select @disabled(! $hasClient)>
                <option value="">{{ __('Sin puesto') }}</option>
                @foreach ($posts as $post)
                    <option
                        value="{{ $post->id }}"
                        data-label="{{ $post->name }}"
                        data-subtitle="{{ $post->address }}"
                        data-search-text="{{ trim($post->name . ' ' . ($post->address ?? '')) }}"
                        @selected((string) old('post_id', $vest->post_id ?? null) === (string) $post->id)
                    >{{ $post->name }}</option>
                @endforeach
            </select>

            <input
                type="text"
                id="vest-post-search"
                data-combobox-search
                class="sj-ui-field__control pr-10 disabled:cursor-not-allowed disabled:bg-slate-50 disabled:text-slate-400"
                placeholder="{{ __('Buscar puesto...') }}"
                autocomplete="off"
                spellcheck="false"
                role="combobox"
                aria-expanded="false"
                aria-controls="vest-post-options"
                @disabled(! $hasClient)
            >

            <button
                type="button"
                data-combobox-toggle
                class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500 disabled:cursor-not-allowed disabled:opacity-50"
                aria-label="{{ __('Mostrar puestos') }}"
                @disabled(! $hasClient)
            >
                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>

            <div
                id="vest-post-options"
                data-combobox-panel
                class="absolute left-0 right-0 z-20 mt-2 hidden max-h-72 overflow-y-auto rounded-md border border-slate-200 bg-white py-1 shadow-xl"
                role="listbox"
            ></div>
        </div>
        <x-input-error :messages="$errors->get('post_id')" class="mt-2" />
    </div>

    <div class="md:col-span-2 sj-ui-field">
        <label for="notes" class="sj-ui-field__label">{{ __('Notas') }}</label>
        <textarea id="notes" name="notes" rows="3" class="sj-ui-field__control min-h-[5.5rem]">{{ old('notes', $vest->notes ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('notes')" class="mt-2" />
    </div>
</div>

<div id="vest-missing-responsible-modal" class="fixed inset-0 z-[1400] hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-lg bg-white p-5 shadow-xl">
        <h4 class="sj-type-section text-gray-900">{{ __('Atención') }}</h4>
        <p class="mt-2 text-sm text-gray-700">{{ __('Primero debe realizar la asignación del responsable.') }}</p>
        <div class="mt-4 flex justify-end">
            <button type="button" id="vest-missing-responsible-modal-close" class="sj-ui-btn sj-ui-btn--primary">
                {{ __('Aceptar') }}
            </button>
        </div>
    </div>
</div>
