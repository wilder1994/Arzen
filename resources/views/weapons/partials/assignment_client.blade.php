<div class="space-y-3">
    <x-weapon-detail-field :label="__('Cliente actual')" class="md:col-span-12">
        <span class="font-semibold text-gray-900">
            {{ $weapon->activeClientAssignment?->client?->name ?? __('Sin destino') }}
        </span>
    </x-weapon-detail-field>

    <form method="POST" action="{{ route('weapons.client_assignments.store', $weapon) }}" class="space-y-3" id="destination-operational-form">
        @csrf

        <div class="grid grid-cols-1 gap-3 md:grid-cols-12 md:items-end">
            <div class="md:col-span-5">
                <label class="text-xs font-medium text-gray-500 mb-1 block">{{ __('Cliente') }}</label>

                <div
                    class="relative"
                    data-assignment-combobox
                    data-empty-message="{{ __('No se encontraron clientes.') }}"
                    data-selected-badge="{{ __('Actual') }}"
                    id="destination-client-combobox"
                >
                    <select name="client_id" id="destination-client-select" class="hidden" required data-combobox-select>
                        <option value="">{{ __('Seleccione') }}</option>
                        @foreach ($clientOptions as $client)
                            @php
                                $responsibleMeta = $clientResponsibleMap[$client->id] ?? ['id' => null, 'name' => null];
                            @endphp
                            <option
                                value="{{ $client->id }}"
                                data-label="{{ $client->name }}"
                                data-search-text="{{ $client->name }}"
                                data-responsible-id="{{ $responsibleMeta['id'] ?? '' }}"
                                data-responsible-name="{{ $responsibleMeta['name'] ?? '' }}"
                                @selected(old('client_id', $weapon->activeClientAssignment?->client_id) == $client->id)
                            >
                                {{ $client->name }}
                            </option>
                        @endforeach
                    </select>

                    <input
                        type="text"
                        id="destination-client-search"
                        data-combobox-search
                        class="sj-ui-field__control block w-full pr-10"
                        placeholder="{{ __('Buscar cliente...') }}"
                        autocomplete="off"
                        spellcheck="false"
                        role="combobox"
                        aria-expanded="false"
                        aria-controls="destination-client-options"
                    >

                    <button
                        type="button"
                        id="destination-client-toggle"
                        data-combobox-toggle
                        class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500"
                        aria-label="{{ __('Mostrar clientes') }}"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>

                    <div
                        id="destination-client-options"
                        data-combobox-panel
                        class="absolute left-0 right-0 z-20 mt-2 hidden max-h-72 overflow-y-auto rounded-md border border-slate-200 bg-white py-1 shadow-xl"
                        role="listbox"
                    ></div>
                </div>

                <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
            </div>

            <div class="md:col-span-4">
                <label class="text-xs font-medium text-gray-500 mb-1 block">{{ __('Responsable') }}</label>
                <div id="destination-responsible-display" class="sj-weapon-detail-field text-gray-700">
                    {{ $weapon->activeClientAssignment?->responsible?->name ?? __('Sin responsable asignado') }}
                </div>
                <input type="hidden" id="destination-responsible-id" name="responsible_user_id" value="{{ $weapon->activeClientAssignment?->responsible_user_id }}">
            </div>

            <div class="md:col-span-3 flex md:justify-end">
                <button type="submit" class="sj-ui-btn sj-ui-btn--primary w-full md:w-auto">
                    {{ __('Actualizar destino') }}
                </button>
            </div>
        </div>

        <div>
            <label class="text-xs font-medium text-gray-500 mb-1 block">{{ __('Observaciones') }}</label>
            <input type="text" name="reason" class="sj-ui-field__control block w-full" />
            <x-input-error :messages="$errors->get('reason')" class="mt-2" />
        </div>
    </form>
</div>

<div id="missing-responsible-modal" class="fixed inset-0 z-[1400] hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-md rounded-lg bg-white p-5 shadow-xl">
        <h4 class="sj-type-section text-gray-900">{{ __('Atención') }}</h4>
        <p class="mt-2 text-sm text-gray-700">{{ __('Primero debe realizar la asignación del responsable.') }}</p>
        <div class="mt-4 flex justify-end">
            <button type="button" id="missing-responsible-modal-close" class="sj-ui-btn sj-ui-btn--primary">
                {{ __('Aceptar') }}
            </button>
        </div>
    </div>
</div>

<script>
    (() => {
        const form = document.getElementById('destination-operational-form');
        const clientCombobox = document.getElementById('destination-client-combobox');
        const clientSelect = document.getElementById('destination-client-select');
        const responsibleDisplay = document.getElementById('destination-responsible-display');
        const responsibleIdInput = document.getElementById('destination-responsible-id');
        const modal = document.getElementById('missing-responsible-modal');
        const closeBtn = document.getElementById('missing-responsible-modal-close');

        if (!form || !clientCombobox || !clientSelect || !responsibleDisplay || !responsibleIdInput || !modal || !closeBtn) {
            return;
        }

        const showModal = () => {
            modal.classList.remove('hidden');
            modal.classList.add('flex');
        };

        const hideModal = () => {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        };

        const syncResponsible = () => {
            const option = clientSelect.selectedOptions?.[0];
            if (!option || !option.value) {
                responsibleDisplay.textContent = @json(__('Sin responsable asignado'));
                responsibleIdInput.value = '';
                return false;
            }

            const responsibleId = option.dataset.responsibleId || '';
            const responsibleName = option.dataset.responsibleName || '';

            responsibleIdInput.value = responsibleId;
            responsibleDisplay.textContent = responsibleName || @json(__('Sin responsable asignado'));

            return responsibleId !== '';
        };

        clientCombobox.addEventListener('assignment-combobox:change', () => {
            if (!syncResponsible() && clientSelect.value) {
                showModal();
            }
        });

        form.addEventListener('submit', (event) => {
            if (!syncResponsible()) {
                event.preventDefault();
                showModal();
            }
        });

        closeBtn.addEventListener('click', hideModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) {
                hideModal();
            }
        });

        syncResponsible();

        @if ($errors->has('client_id') && str_contains((string) $errors->first('client_id'), 'Primero debe realizar la asignación del responsable.'))
            showModal();
        @endif
    })();
</script>
