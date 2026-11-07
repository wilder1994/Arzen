<section class="sj-ui-card sj-weapon-detail-section p-4">
    <h4 class="sj-weapon-detail-section__title">{{ __('Propiedad') }}</h4>
    <div class="grid grid-cols-1 gap-3 md:grid-cols-2">
        <x-weapon-detail-field :label="__('Entidad propietaria')">
            {{ $weapon->ownership_entity ?: '—' }}
        </x-weapon-detail-field>

        <x-weapon-detail-field :label="__('Responsable')">
            {{ $weapon->activeClientAssignment?->responsible?->name ?? '—' }}
        </x-weapon-detail-field>
    </div>
</section>
