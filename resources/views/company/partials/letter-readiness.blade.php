@php
    $letterMissing = \App\Models\CompanySetting::current()->missingLetterFields();
@endphp
@if (session('error'))
    <x-ui-alert type="danger">{{ session('error') }}</x-ui-alert>
@endif
@if ($letterMissing !== [])
    <x-ui-alert type="warning">
        {{ __('Para generar cartas de revalidación faltan datos en Mi empresa: :fields.', ['fields' => implode(', ', $letterMissing)]) }}
        @if (auth()->user()?->isAdmin())
            <a href="{{ route('company.edit') }}" class="ml-1">{{ __('Completar ahora') }}</a>
        @endif
    </x-ui-alert>
@endif
