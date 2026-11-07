<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Nueva arma') }}</h2>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('weapons.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al inventario') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide">
            <div class="sj-ui-card p-6">
                <form method="POST" action="{{ route('weapons.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2" enctype="multipart/form-data">
                    @csrf
                    @include('weapons.partials.form', [
                        'weapon' => null,
                        'brands' => $brands,
                        'calibers' => $calibers,
                        'companyName' => $companyName,
                        'showInternalCode' => false,
                        'requirePermitPhoto' => true,
                        'cancelUrl' => route('weapons.index'),
                        'submitLabel' => __('Crear arma'),
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>




