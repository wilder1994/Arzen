@php
    $post = $post ?? null;
    $currentInstallation = (int) old('installation_id', $selectedInstallationId ?? $post?->installation_id);
@endphp

<h3 class="sj-form-section__title">{{ __('Datos del puesto') }}</h3>
<div class="sj-company__grid">
    <div class="sj-ui-field">
        <div class="sj-ui-field__label-row">
            <label for="post-installation" class="sj-ui-field__label">{{ __('Cliente e instalación') }}</label>
            <x-help-tip :text="__('El puesto queda dentro de esta instalación y toma su ubicación. Las instalaciones se crean en la ficha del cliente.')" />
        </div>
        <select id="post-installation" name="installation_id" class="sj-ui-field__control" required>
            <option value="">{{ __('Seleccione') }}</option>
            @foreach ($installationsByClient as $clientName => $installations)
                <optgroup label="{{ $clientName }}">
                    @foreach ($installations as $installation)
                        <option value="{{ $installation->id }}" @selected($currentInstallation === $installation->id)>
                            {{ $clientName }} › {{ $installation->name }}{{ $installation->is_client_site ? ' ('.__('principal').')' : '' }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('installation_id')" />
    </div>

    <div class="sj-ui-field">
        <label for="post-name" class="sj-ui-field__label">{{ __('Nombre del puesto') }}</label>
        <input id="post-name" name="name" type="text" maxlength="255" value="{{ old('name', $post?->name) }}" class="sj-ui-field__control" required>
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="sj-ui-field sj-location__address">
        <label for="post-notes" class="sj-ui-field__label">{{ __('Notas') }}</label>
        <textarea id="post-notes" name="notes" rows="2" class="sj-ui-field__control">{{ old('notes', $post?->notes) }}</textarea>
        <x-input-error :messages="$errors->get('notes')" />
    </div>
</div>

@if ($post)
    <hr class="sj-ui-divider">

    <div class="sj-form-section__title sj-ui-field__label-row">
        <h3>{{ __('Nota del cambio') }}</h3>
        <x-help-tip :text="__('Obligatoria. Se guarda en el historial del puesto.')" />
    </div>
    <div class="sj-ui-field">
        <textarea id="change_note" name="change_note" rows="2" class="sj-ui-field__control" required aria-label="{{ __('Nota del cambio') }}">{{ old('change_note') }}</textarea>
        <x-input-error :messages="$errors->get('change_note')" />
    </div>
@endif
