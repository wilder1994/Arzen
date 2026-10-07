@props(['disabled' => false])

@php
    $type = $attributes->get('type', 'text');
    $spellcheck = in_array($type, ['text', 'search'], true) ? 'true' : 'false';
@endphp

<input {{ $disabled ? 'disabled' : '' }} spellcheck="{{ $spellcheck }}" {!! $attributes->merge(['class' => 'sj-ui-field__control']) !!}>




