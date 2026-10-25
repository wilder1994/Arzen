<x-app-layout>
    @php
        $isNew = ! $installation->exists;
        $sameAsClient = (bool) old('same_as_client', $isNew);
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
                <a href="{{ route('clients.edit', $client) }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al cliente') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="sj-page-shell sj-page-shell--wide">
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
                                <input id="installation-name" name="name" type="text" maxlength="120" value="{{ old('name', $installation->name) }}" class="sj-ui-field__control" @disabled($installation->is_client_site) required>
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
                            <div class="sj-ui-field sj-location__address">
                                <label for="installation-notes" class="sj-ui-field__label">{{ __('Notas') }}</label>
                                <input id="installation-notes" name="notes" type="text" maxlength="2000" value="{{ old('notes', $installation->notes) }}" class="sj-ui-field__control">
                                <x-input-error :messages="$errors->get('notes')" />
                            </div>
                        </div>

                        <hr class="sj-ui-divider">

                        <h3 class="sj-form-section__title">{{ __('Ubicación') }}</h3>
                        @if ($installation->is_client_site)
                            <x-ui-alert type="success">
                                {{ __('Es la instalación principal: usa siempre el nombre y la ubicación del cliente. Se cambian editando el cliente.') }}
                            </x-ui-alert>
                        @else
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
                        @endif

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('clients.edit', $client) }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar') }}</button>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
</x-app-layout>
