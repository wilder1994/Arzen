@props([
    'model' => null,
    'required' => true,
    'withNeighborhood' => true,
    'letterCity' => false,
])

@php
    $value = fn (string $field) => old($field, $model?->{$field});
    $latitude = $value('latitude');
    $longitude = $value('longitude');
    $hasCoords = filled($latitude) && filled($longitude);
@endphp

<div {{ $attributes->merge(['class' => 'sj-location']) }} data-location-form>
    <input type="hidden" name="coords_source" value="{{ old('coords_source', $hasCoords ? 'map' : 'geocode') }}" data-coords-source>
    <input type="hidden" name="latitude" value="{{ $latitude }}" data-latitude-input>
    <input type="hidden" name="longitude" value="{{ $longitude }}" data-longitude-input>

    <div class="sj-location__grid">
        <div class="sj-ui-field sj-location__address">
            <label for="location-address" class="sj-ui-field__label">{{ __('Dirección') }}</label>
            <div class="sj-location__address-row">
                <input id="location-address" name="address" type="text" value="{{ $value('address') }}" class="sj-ui-field__control" placeholder="{{ __('Ej: Calle 10 # 25-30') }}" data-address-input>
                <button type="button" class="sj-location__map-button" data-map-trigger aria-label="{{ __('Seleccionar en el mapa') }}" title="{{ __('Seleccionar en el mapa') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498 4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 0 0-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0Z" />
                    </svg>
                </button>
            </div>
            <x-input-error :messages="$errors->get('address')" />
        </div>

        @if ($withNeighborhood)
            <div class="sj-ui-field">
                <label for="location-neighborhood" class="sj-ui-field__label">{{ __('Barrio') }}</label>
                <input id="location-neighborhood" name="neighborhood" type="text" value="{{ $value('neighborhood') }}" class="sj-ui-field__control" placeholder="{{ __('Ej: Centro') }}" data-neighborhood-input>
                <x-input-error :messages="$errors->get('neighborhood')" />
            </div>
        @endif

        <div class="sj-ui-field">
            <label for="location-department" class="sj-ui-field__label">{{ __('Departamento') }}</label>
            <select id="location-department" name="department" class="sj-ui-field__control" data-department-select data-current="{{ $value('department') }}" @required($required)>
                <option value="">{{ __('Seleccione') }}</option>
            </select>
            <x-input-error :messages="$errors->get('department')" />
        </div>

        <div class="sj-ui-field">
            <label for="location-city" class="sj-ui-field__label">{{ __('Municipio') }}</label>
            <select id="location-city" name="city" class="sj-ui-field__control" data-municipality-select data-current="{{ $value('city') }}" @required($required)>
                <option value="">{{ __('Seleccione') }}</option>
            </select>
            <x-input-error :messages="$errors->get('city')" />
        </div>

        @if ($letterCity)
            <div class="sj-ui-field">
                <div class="sj-ui-field__label-row">
                    <label for="location-letter-city" class="sj-ui-field__label">{{ __('Ciudad para la carta') }}</label>
                    <x-help-tip :text="__('Se llena sola con el municipio y se puede editar. Sale en la fecha de las cartas, por ejemplo: Santiago de Cali, 7 de octubre de 2026.')" />
                </div>
                <input id="location-letter-city" name="letter_city" type="text" maxlength="120" value="{{ old('letter_city', $model?->letter_city) }}" class="sj-ui-field__control" data-letter-city-input data-autofill="{{ filled(old('letter_city', $model?->letter_city)) ? 'off' : 'on' }}">
                <x-input-error :messages="$errors->get('letter_city')" />
            </div>
        @endif
    </div>

    <p class="sj-location__meta">
        <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="m9.69 18.933.003.001C9.89 19.02 10 19 10 19s.11.02.308-.066l.002-.001.006-.003.018-.008a5.741 5.741 0 0 0 .281-.14c.186-.096.446-.24.757-.433.62-.384 1.445-.966 2.274-1.765C15.302 14.988 17 12.493 17 9A7 7 0 1 0 3 9c0 3.492 1.698 5.988 3.355 7.584a13.731 13.731 0 0 0 2.273 1.765 11.842 11.842 0 0 0 .976.544l.062.029.018.008.006.003ZM10 11.25a2.25 2.25 0 1 0 0-4.5 2.25 2.25 0 0 0 0 4.5Z" clip-rule="evenodd" /></svg>
        <span data-coords-display data-empty-label="{{ __('Sin ubicación en el mapa: use el ícono de mapa o complete la dirección.') }}">
            {{ $hasCoords ? number_format((float) $latitude, 6, '.', '').', '.number_format((float) $longitude, 6, '.', '') : __('Sin ubicación en el mapa: use el ícono de mapa o complete la dirección.') }}
        </span>
    </p>
    <p class="sj-location__notice hidden" data-geocode-notice></p>
</div>

@once
    @push('scripts')
        @include('partials.google-maps')
        @vite('resources/js/location-picker.js')

        <div id="location-map-modal" class="sj-map-modal hidden" role="dialog" aria-modal="true" aria-labelledby="location-map-title">
            <div class="sj-map-modal__panel">
                <div class="sj-map-modal__header">
                    <h3 id="location-map-title" class="sj-type-section">{{ __('Seleccionar ubicación') }}</h3>
                    <button type="button" class="sj-help-drawer__close" data-map-close aria-label="{{ __('Cerrar') }}">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" /></svg>
                    </button>
                </div>
                <div id="location-map" class="sj-map-modal__map"></div>
                <p class="mt-3 hidden text-sm text-red-600" data-map-error></p>
                <div class="sj-map-modal__actions">
                    <button type="button" class="sj-ui-btn sj-ui-btn--ghost" data-map-close>{{ __('Cancelar') }}</button>
                    <button type="button" class="sj-ui-btn sj-ui-btn--primary disabled:cursor-not-allowed disabled:opacity-60" data-map-accept disabled>{{ __('Aceptar') }}</button>
                </div>
            </div>
        </div>
    @endpush
@endonce
