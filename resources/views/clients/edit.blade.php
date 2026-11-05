<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <div class="sj-section-header__title-row">
                    <h2 class="sj-section-header__title">{{ __('Editar cliente') }}</h2>
                    <x-help-drawer :title="__('Clientes')">
                        @include('clients.partials.help')
                    </x-help-drawer>
                </div>
                <p class="sj-section-header__subtitle">{{ $client->name }}@if ($client->is_own_company) · {{ __('Mi empresa') }}@endif</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('clients.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al listado') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="sj-page-shell sj-page-shell--wide space-y-4">
            @if (session('status'))
                <x-ui-alert type="success">{{ session('status') }}</x-ui-alert>
            @endif

            @if ($client->is_own_company)
                <x-ui-alert type="warning">
                    {{ __('Este cliente es su propia empresa. Sus datos y su ubicación se editan en Mi empresa.') }}
                    <a href="{{ route('company.edit', $returnInstallation ? ['return_installation' => $returnInstallation->id] : []) }}" class="ml-1">{{ __('Ir a Mi empresa') }}</a>
                </x-ui-alert>
            @else
                <form method="POST" action="{{ route('clients.update', $client) }}">
                    @csrf
                    @method('PUT')
                    <section class="sj-ui-card">
                        <div class="sj-ui-card__body p-6">
                            @include('clients.partials.form', ['client' => $client])

                            @if ($returnInstallation)
                                <input type="hidden" name="return_installation" value="{{ $returnInstallation->id }}">
                                <div class="sj-ui-field mt-4">
                                    <label for="client-change-note" class="sj-ui-field__label">{{ __('Nota del cambio') }}</label>
                                    <textarea id="client-change-note" name="change_note" rows="2" maxlength="2000" class="sj-ui-field__control" placeholder="{{ __('Opcional. Queda en el historial de la instalación.') }}">{{ old('change_note') }}</textarea>
                                </div>
                            @endif

                            <div class="mt-6 flex justify-end gap-2">
                                <a href="{{ $returnInstallation ? route('installations.edit', $returnInstallation) : route('clients.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
                                <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar cambios') }}</button>
                            </div>
                        </div>
                    </section>
                </form>
            @endif

            @include('clients.partials.installations', ['client' => $client])
        </div>
    </div>
</x-app-layout>
