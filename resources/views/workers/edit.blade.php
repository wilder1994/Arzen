<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Editar trabajador') }}</h2>
                <p class="sj-section-header__subtitle">{{ $worker->name }}</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('workers.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al listado') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide">
            <div class="sj-ui-card p-6">
                    <form method="POST" action="{{ route('workers.update', $worker) }}" class="sj-form-panel">
                        @csrf
                        @method('PUT')

                        <section class="sj-form-section">
                            <div class="sj-form-section__title">Identificación</div>
                            <div class="sj-form-grid sj-form-grid--two">
                                <div>
                                    <x-input-label for="client_id" :value="__('Cliente')" />
                                    <select id="client_id" name="client_id" class="sj-ui-field__control mt-1 block w-full" required>
                                        <option value="">{{ __('Seleccione') }}</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}" @selected(old('client_id', $worker->client_id) == $client->id)>
                                                {{ $client->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="name" :value="__('Nombre')" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name', $worker->name) }}" required />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="document" :value="__('Cédula')" />
                                    <x-text-input id="document" name="document" type="text" class="mt-1 block w-full" value="{{ old('document', $worker->document) }}" />
                                    <x-input-error :messages="$errors->get('document')" class="mt-2" />
                                </div>
                            </div>
                        </section>

                        <section class="sj-form-section">
                            <div class="sj-form-section__title">Asignación</div>
                            <div class="sj-form-grid sj-form-grid--two">
                                <div>
                                    <x-input-label for="role" :value="__('Rol')" />
                                    <select id="role" name="role" class="sj-ui-field__control mt-1 block w-full" required>
                                        <option value="">{{ __('Seleccione') }}</option>
                                        @foreach ($roles as $value => $label)
                                            <option value="{{ $value }}" @selected(old('role', $worker->role) == $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="responsible_user_id" :value="__('Responsable')" />
                                    @if (!empty($lockResponsible))
                                        <input type="hidden" name="responsible_user_id" value="{{ auth()->id() }}">
                                        <p class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800">{{ auth()->user()->name }}</p>
                                        <p class="sj-form-help">{{ __('No puede reasignar el responsable en su rol.') }}</p>
                                    @else
                                        <select id="responsible_user_id" name="responsible_user_id" class="sj-ui-field__control mt-1 block w-full">
                                            <option value="">{{ __('Sin responsable') }}</option>
                                            @foreach ($responsibles as $responsible)
                                                <option value="{{ $responsible->id }}" @selected(old('responsible_user_id', $worker->responsible_user_id) == $responsible->id)>
                                                    {{ $responsible->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('responsible_user_id')" class="mt-2" />
                                    @endif
                                </div>
                            </div>
                        </section>

                        <section class="sj-form-section">
                            <div class="sj-form-section__title">{{ __('Nota del cambio (historial)') }}</div>
                            <div>
                                <x-input-label for="change_note" :value="__('Descripción del cambio')" />
                                <textarea id="change_note" name="change_note" class="sj-ui-field__control mt-1 block w-full" rows="3" required>{{ old('change_note') }}</textarea>
                                <p class="sj-form-help">{{ __('Obligatorio. Se guarda en el historial del trabajador.') }}</p>
                                <x-input-error :messages="$errors->get('change_note')" class="mt-2" />
                            </div>
                        </section>

                        <section class="sj-form-section">
                            <div class="sj-form-section__title">Notas</div>
                            <div>
                                <x-input-label for="notes" :value="__('Notas')" />
                                <textarea id="notes" name="notes" class="sj-ui-field__control mt-1 block w-full" rows="3">{{ old('notes', $worker->notes) }}</textarea>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </section>

                        <div class="sj-form-actions">
                            <a href="{{ route('workers.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar cambios') }}</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
</x-app-layout>
