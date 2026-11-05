<x-app-layout>
    @php
        $isNew = ! $installation->exists;
        $sameAsClient = filter_var(old('same_as_client', false), FILTER_VALIDATE_BOOLEAN);
    @endphp

    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <div class="sj-section-header__title-row">
                    <h2 class="sj-section-header__title">{{ $isNew ? __('Nueva instalación') : __('Editar instalación') }}</h2>
                    <x-help-drawer :title="__('Instalaciones')">
                        @include('installations.partials.help')
                    </x-help-drawer>
                </div>
                <p class="sj-section-header__subtitle">{{ $client->name }}</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ $isNew ? route('installations.index') : route('installations.edit', $installation) }}" class="sj-ui-btn sj-ui-btn--ghost">{{ $isNew ? __('Volver al listado') : __('Volver a la instalación') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="sj-page-shell sj-page-shell--wide">
            @if ($errors->any())
                <div class="mb-4 rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ $isNew ? route('clients.installations.store', $client) : route('installations.update', $installation) }}" x-data="{ sameAsClient: @js($sameAsClient) }">
                @csrf
                @unless ($isNew)
                    @method('PUT')
                @endunless

                <section class="sj-ui-card">
                    <div class="sj-ui-card__body p-6">
                        <h3 class="sj-form-section__title">{{ __('Datos de la instalación') }}</h3>
                        <div class="sj-company__grid">
                            <div class="sj-ui-field">
                                <label for="installation-name" class="sj-ui-field__label">{{ __('Nombre') }}</label>
                                <input id="installation-name" name="name" type="text" maxlength="120" value="{{ old('name', $installation->name) }}" class="sj-ui-field__control" required>
                                <x-input-error :messages="$errors->get('name')" />
                            </div>
                            <div class="sj-ui-field">
                                <div class="sj-ui-field__label-row">
                                    <label for="installation-code" class="sj-ui-field__label">{{ __('Código') }}</label>
                                    <x-help-tip :text="__('Opcional. Código interno o del cliente para identificar la sede.')" />
                                </div>
                                <input id="installation-code" name="code" type="text" maxlength="40" value="{{ old('code', $installation->code) }}" class="sj-ui-field__control">
                                <x-input-error :messages="$errors->get('code')" />
                            </div>
                        </div>

                        <hr class="sj-ui-divider">

                        <h3 class="sj-form-section__title">{{ __('Ubicación') }}</h3>
                        <label class="sj-company__check mb-3">
                            <input type="hidden" name="same_as_client" value="0">
                            <input type="checkbox" name="same_as_client" value="1" class="sj-ui-check rounded" x-model="sameAsClient">
                            {{ __('Es la misma ubicación del cliente') }}
                        </label>
                        <div x-show="!sameAsClient" x-cloak>
                            <x-location-fields :model="$installation->exists ? $installation : null" :required="false" />
                        </div>
                        <p x-show="sameAsClient" class="sj-company__hint">
                            {{ collect([$client->address, $client->city, $client->department])->filter()->implode(' · ') ?: __('El cliente aún no tiene ubicación registrada.') }}
                        </p>

                        @unless ($isNew)
                            <hr class="sj-ui-divider">
                            <div class="sj-ui-field">
                                <label for="installation-change-note" class="sj-ui-field__label">{{ __('Nota del cambio') }}</label>
                                <textarea id="installation-change-note" name="change_note" rows="2" maxlength="2000" class="sj-ui-field__control" placeholder="{{ __('Opcional. Queda en el historial de la instalación.') }}">{{ old('change_note') }}</textarea>
                                <x-input-error :messages="$errors->get('change_note')" />
                            </div>
                        @endunless

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ $isNew ? route('installations.index') : route('installations.edit', $installation) }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar') }}</button>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
</x-app-layout>
