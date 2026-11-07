<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Editar arma') }}</h2>
                <p class="sj-section-header__subtitle">{{ $weapon->internal_code ?? $weapon->serial_number }}</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('weapons.show', $weapon) }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al detalle') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide">
            <div class="sj-ui-card p-6">
                <form method="POST" action="{{ route('weapons.update', $weapon) }}" class="grid grid-cols-1 gap-4 md:grid-cols-2" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('weapons.partials.form', [
                        'weapon' => $weapon,
                        'brands' => $brands,
                        'calibers' => $calibers,
                        'companyName' => $companyName,
                        'showInternalCode' => true,
                        'requirePermitPhoto' => false,
                        'cancelUrl' => route('weapons.show', $weapon),
                        'submitLabel' => __('Guardar cambios'),
                    ])
                </form>
            </div>
        </div>
    </div>
</x-app-layout>




