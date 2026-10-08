@props(['value'])

<label {{ $attributes->merge(['class' => 'sj-ui-field__label block']) }}>
    {{ $value ?? $slot }}
</label>




