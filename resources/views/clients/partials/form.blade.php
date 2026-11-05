@php
    $client = $client ?? null;
    $field = fn (string $name, $default = null) => old($name, $client?->{$name} ?? $default);
    $structureTypes = \App\Models\StructureType::query()->active()->ordered()->get();
    if ($client?->structureType && $structureTypes->doesntContain('id', $client->structure_type_id)) {
        $structureTypes->push($client->structureType);
    }
@endphp

<h3 class="sj-form-section__title">{{ __('Datos básicos') }}</h3>
<div class="sj-company__grid">
    <div class="sj-ui-field">
        <label for="client-party_type" class="sj-ui-field__label">{{ __('Tipo de cliente') }}</label>
        <select id="client-party_type" name="party_type" class="sj-ui-field__control" required>
            @foreach (\App\Models\Client::partyTypeLabels() as $value => $label)
                <option value="{{ $value }}" @selected($field('party_type', \App\Models\Client::PARTY_LEGAL) === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('party_type')" />
    </div>
    <div class="sj-ui-field">
        <label for="client-name" class="sj-ui-field__label">{{ __('Nombre comercial') }}</label>
        <input id="client-name" name="name" type="text" value="{{ $field('name') }}" class="sj-ui-field__control" required>
        <x-input-error :messages="$errors->get('name')" />
    </div>
    <div class="sj-ui-field">
        <div class="sj-ui-field__label-row">
            <label for="client-legal_name" class="sj-ui-field__label">{{ __('Razón social') }}</label>
            <x-help-tip :text="__('Nombre legal tal como aparece en el RUT. Si se deja vacío se usa el nombre comercial.')" />
        </div>
        <input id="client-legal_name" name="legal_name" type="text" value="{{ $field('legal_name') }}" class="sj-ui-field__control">
        <x-input-error :messages="$errors->get('legal_name')" />
    </div>
    <div class="sj-ui-field">
        <label for="client-document_type" class="sj-ui-field__label">{{ __('Tipo documento') }}</label>
        <select id="client-document_type" name="document_type" class="sj-ui-field__control" required>
            @foreach (\App\Models\Client::documentTypeLabels() as $value => $label)
                <option value="{{ $value }}" @selected($field('document_type', 'NIT') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('document_type')" />
    </div>
    <div class="sj-ui-field">
        <label for="client-nit" class="sj-ui-field__label">{{ __('Número documento') }}</label>
        <input id="client-nit" name="nit" type="text" value="{{ $field('nit') }}" class="sj-ui-field__control" required>
        <x-input-error :messages="$errors->get('nit')" />
    </div>
    <div class="sj-ui-field">
        <label for="client-email" class="sj-ui-field__label">{{ __('Correo') }}</label>
        <input id="client-email" name="email" type="email" value="{{ $field('email') }}" class="sj-ui-field__control">
        <x-input-error :messages="$errors->get('email')" />
    </div>
    <div class="sj-ui-field">
        <label for="client-phone" class="sj-ui-field__label">{{ __('Teléfono') }}</label>
        <input id="client-phone" name="phone" type="text" value="{{ $field('phone') }}" class="sj-ui-field__control">
        <x-input-error :messages="$errors->get('phone')" />
    </div>
    <div class="sj-ui-field">
        <label for="client-contact_name" class="sj-ui-field__label">{{ __('Contacto') }}</label>
        <input id="client-contact_name" name="contact_name" type="text" value="{{ $field('contact_name') }}" class="sj-ui-field__control">
        <x-input-error :messages="$errors->get('contact_name')" />
    </div>
    <div class="sj-ui-field">
        <label for="client-legal_representative" class="sj-ui-field__label">{{ __('Representante legal') }}</label>
        <input id="client-legal_representative" name="legal_representative" type="text" value="{{ $field('legal_representative') }}" class="sj-ui-field__control">
        <x-input-error :messages="$errors->get('legal_representative')" />
    </div>
    <div class="sj-ui-field">
        <div class="sj-ui-field__label-row">
            <label for="client-structure_type" class="sj-ui-field__label">{{ __('Tipo de estructura') }}</label>
            <x-help-tip :text="__('Opcional. Se administra en Ajustes → Tipos de estructura.')" />
        </div>
        <select id="client-structure_type" name="structure_type_id" class="sj-ui-field__control">
            <option value="">{{ __('Sin tipo') }}</option>
            @foreach ($structureTypes as $type)
                <option value="{{ $type->id }}" @selected((string) $field('structure_type_id') === (string) $type->id)>{{ $type->name }}</option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('structure_type_id')" />
    </div>
    <div class="sj-ui-field">
        <label for="client-representative_email" class="sj-ui-field__label">{{ __('Correo del representante') }}</label>
        <input id="client-representative_email" name="representative_email" type="email" value="{{ $field('representative_email') }}" class="sj-ui-field__control">
        <x-input-error :messages="$errors->get('representative_email')" />
    </div>
</div>

<hr class="sj-ui-divider">

<div class="sj-form-section__title sj-ui-field__label-row">
    <h3>{{ __('Ubicación') }}</h3>
    <x-help-tip :text="__('Sede del cliente. Con estos datos se crea y actualiza su instalación principal.')" />
</div>
<x-location-fields :model="$client" />
