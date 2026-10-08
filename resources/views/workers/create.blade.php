<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Nuevo trabajador') }}</h2>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('workers.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al listado') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide">
            <div class="sj-ui-card p-6">
                    <form method="POST" action="{{ route('workers.store') }}" class="sj-form-panel">
                        @csrf

                        <section class="sj-form-section">
                            <div class="sj-form-section__title">Identificación</div>
                            <div class="sj-form-grid sj-form-grid--two">
                                <div>
                                    <x-input-label for="client_id" :value="__('Cliente')" />
                                    <select id="client_id" name="client_id" class="sj-ui-field__control mt-1 block w-full" required>
                                        <option value="">{{ __('Seleccione') }}</option>
                                        @foreach ($clients as $client)
                                            <option value="{{ $client->id }}" @selected(old('client_id') == $client->id)>
                                                {{ $client->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('client_id')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="name" :value="__('Nombre')" />
                                    <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name') }}" required />
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="document" :value="__('Cédula')" />
                                    <x-text-input id="document" name="document" type="text" class="mt-1 block w-full" value="{{ old('document') }}" />
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
                                            <option value="{{ $value }}" @selected(old('role') == $value)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <x-input-error :messages="$errors->get('role')" class="mt-2" />
                                </div>

                                <div>
                                    <x-input-label for="responsible_user_id" :value="__('Responsable')" />
                                    @if (!empty($lockResponsible))
                                        <input type="hidden" name="responsible_user_id" value="{{ auth()->id() }}">
                                        <p class="mt-1 rounded-md border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-800">{{ auth()->user()->name }}</p>
                                        <p class="sj-form-help">{{ __('Asignado automáticamente a su usuario.') }}</p>
                                    @else
                                        <select id="responsible_user_id" name="responsible_user_id" class="sj-ui-field__control mt-1 block w-full">
                                            <option value="">{{ __('Sin responsable') }}</option>
                                            @foreach ($responsibles as $responsible)
                                                <option value="{{ $responsible->id }}" @selected(old('responsible_user_id') == $responsible->id)>
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
                            <div class="sj-form-section__title">Notas</div>
                            <div>
                                <x-input-label for="notes" :value="__('Notas')" />
                                <textarea id="notes" name="notes" class="sj-ui-field__control mt-1 block w-full" rows="3">{{ old('notes') }}</textarea>
                                <p class="sj-form-help">{{ __('El registro inicial quedará en el historial junto con estas notas si las completa.') }}</p>
                                <x-input-error :messages="$errors->get('notes')" class="mt-2" />
                            </div>
                        </section>

                        <div class="sj-form-actions">
                            <a href="{{ route('workers.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar') }}</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
</x-app-layout>
