<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <div class="sj-section-header__title-row">
                    <h2 class="sj-section-header__title">{{ $installation->name }}</h2>
                    <x-help-drawer :title="__('Instalaciones')">
                        @include('installations.partials.help')
                    </x-help-drawer>
                </div>
                <p class="sj-section-header__subtitle">{{ $client->name }}</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('installations.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al listado') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="sj-page-shell sj-page-shell--wide space-y-4">
            @if (session('status'))
                <div class="rounded bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            @include('installations.partials.board')
        </div>
    </div>
</x-app-layout>
