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
                    <a href="{{ route('company.edit') }}" class="ml-1">{{ __('Ir a Mi empresa') }}</a>
                </x-ui-alert>
            @else
                <form method="POST" action="{{ route('clients.update', $client) }}">
                    @csrf
                    @method('PUT')
                    <section class="sj-ui-card">
                        <div class="sj-ui-card__body p-6">
                            @include('clients.partials.form', ['client' => $client])

                            <div class="mt-6 flex justify-end gap-2">
                                <a href="{{ route('clients.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
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
