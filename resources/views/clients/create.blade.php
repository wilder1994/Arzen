<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <div class="sj-section-header__title-row">
                    <h2 class="sj-section-header__title">{{ __('Nuevo cliente') }}</h2>
                    <x-help-drawer :title="__('Clientes')">
                        @include('clients.partials.help')
                    </x-help-drawer>
                </div>
                <p class="sj-section-header__subtitle">{{ __('Al guardar se crea su instalación principal con la misma ubicación.') }}</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('clients.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al listado') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="sj-page-shell sj-page-shell--wide">
            <form method="POST" action="{{ route('clients.store') }}">
                @csrf
                <section class="sj-ui-card">
                    <div class="sj-ui-card__body p-6">
                        @include('clients.partials.form')

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('clients.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar') }}</button>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
</x-app-layout>
