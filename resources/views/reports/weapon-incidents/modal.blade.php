<div id="weapon-incident-modal" class="sj-incident-modal hidden" aria-hidden="true" data-modal-key="weapon-incident-modal">
    <button type="button" class="sj-incident-modal__backdrop" data-close-modal aria-label="{{ __('Cerrar') }}"></button>

    <div class="sj-incident-modal__wrap">
        <section class="sj-incident-modal__panel" role="dialog" aria-modal="true" aria-labelledby="weapon-incident-modal-title">
            <div class="sj-incident-modal__header">
                <div>
                    <p class="sj-incident-modal__eyebrow">{{ __('Novedades') }}</p>
                    <h3 id="weapon-incident-modal-title" class="sj-incident-modal__title">{{ __('Agregar reporte') }}</h3>
                    <p class="sj-incident-modal__subtitle">{{ __('Captura una novedad operativa y enlázala con un arma. El soporte documental es opcional en el reporte inicial.') }}</p>
                </div>

                <button type="button" class="sj-incident-modal__close" data-close-modal>
                    {{ __('Cerrar') }}
                </button>
            </div>

            <div class="sj-incident-modal__body">
                <form class="sj-incident-form" data-incident-form method="POST" action="{{ route('weapon-incidents.store') }}" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="redirect_to" value="{{ request()->fullUrl() }}">

                    <div class="sj-form-grid sj-form-grid--two">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500" for="incident_event_at">{{ __('Fecha y hora') }}</label>
                            <input id="incident_event_at" name="event_at" type="datetime-local" value="{{ old('event_at', now()->format('Y-m-d\TH:i')) }}" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm" required>
                        </div>

                        <div class="sj-weapon-picker" data-weapon-picker data-search-url="{{ route('reports.weapon-incidents.weapons.search') }}">
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500" for="incident_weapon_search">{{ __('Arma') }}</label>
                            <input type="hidden" name="weapon_id" value="{{ old('weapon_id') }}" data-weapon-picker-value required>
                            <input
                                id="incident_weapon_search"
                                type="text"
                                value="{{ $selectedWeapon['summary'] ?? '' }}"
                                class="sj-ui-field__control mt-1 block w-full"
                                placeholder="{{ __('Buscar por cliente, marca o serie...') }}"
                                autocomplete="off"
                                spellcheck="false"
                                data-weapon-picker-input
                            >
                            <div class="sj-weapon-picker__selected {{ $selectedWeapon ? '' : 'hidden' }}" data-weapon-picker-selected>
                                <div>
                                    <strong data-weapon-picker-selected-summary>{{ $selectedWeapon['summary'] ?? '' }}</strong>
                                    <span data-weapon-picker-selected-meta>
                                        {{ $selectedWeapon ? ($selectedWeapon['client'] . ' / vence ' . $selectedWeapon['permit_expires_label']) : '' }}
                                    </span>
                                </div>
                                <button type="button" class="sj-weapon-picker__clear" data-weapon-picker-clear>{{ __('Cambiar') }}</button>
                            </div>
                            <div class="sj-weapon-picker__menu hidden" data-weapon-picker-menu>
                                <div class="sj-weapon-picker__head">
                                    <span>{{ __('Cliente') }}</span>
                                    <span>{{ __('Marca') }}</span>
                                    <span>{{ __('Serie') }}</span>
                                    <span>{{ __('Vence') }}</span>
                                </div>
                                <div class="sj-weapon-picker__results" data-weapon-picker-results></div>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500" for="incident_type_id">{{ __('Tipo') }}</label>
                            <select id="incident_type_id" name="incident_type_id" class="sj-ui-field__control mt-1 block w-full" data-incident-type-select required>
                                <option value="">{{ __('Seleccione') }}</option>
                                @foreach ($types as $type)
                                    <option value="{{ $type->id }}" @selected((int) old('incident_type_id', $selectedType?->id) === (int) $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500" for="incident_modality_id">{{ __('Modalidad') }}</label>
                            <select
                                id="incident_modality_id"
                                name="incident_modality_id"
                                class="sj-ui-field__control mt-1 block w-full"
                                data-incident-modality-select
                                data-modality-map='@json($modalityMap)'
                                data-selected-modality="{{ old('incident_modality_id') }}"
                            >
                                <option value="">{{ __('No aplica') }}</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500" for="incident_status">{{ __('Estado') }}</label>
                            <select id="incident_status" name="status" class="sj-ui-field__control mt-1 block w-full">
                                <option value="">{{ __('Según tipo') }}</option>
                                @foreach (App\Models\WeaponIncident::initialStatusOptions() as $value => $label)
                                    <option value="{{ $value }}" @selected(old('status') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500" for="incident_attachment">{{ __('Adjunto') }}</label>
                            <input id="incident_attachment" name="attachment" type="file" class="mt-1 block w-full rounded-xl border-slate-300 text-sm shadow-sm" accept=".pdf,.doc,.docx,image/jpeg,image/png,image/webp">
                        </div>
                    </div>

                    <div class="mt-4 grid gap-4">
                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500" for="incident_observation">{{ __('Observación') }}</label>
                            <input id="incident_observation" name="observation" type="text" value="{{ old('observation') }}" class="sj-ui-field__control mt-1 block w-full" maxlength="255" required>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500" for="incident_note">{{ __('Nota') }}</label>
                            <textarea id="incident_note" name="note" rows="4" class="sj-ui-field__control mt-1 block w-full">{{ old('note') }}</textarea>
                        </div>
                    </div>

                    <div class="sj-incident-form__footer">
                        <button type="button" class="sj-btn-secondary rounded-md border px-4 py-2 text-sm font-semibold" data-close-modal>
                            {{ __('Cancelar') }}
                        </button>
                        <button type="submit" class="sj-btn-primary rounded-md border px-4 py-2 text-sm font-semibold">
                            {{ __('Guardar reporte') }}
                        </button>
                    </div>
                </form>
            </div>
        </section>
    </div>
</div>
