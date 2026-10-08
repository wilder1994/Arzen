@php
    $letterMissing = \App\Models\CompanySetting::current()->missingLetterFields();
@endphp
@if (session('error'))
    <div class="rounded bg-red-50 p-3 text-sm text-red-700" role="alert">{{ session('error') }}</div>
@endif
@if ($letterMissing !== [])
    <div class="rounded bg-amber-50 p-3 text-sm text-amber-800" role="status">
        {{ __('Para generar cartas de revalidación faltan datos en Mi empresa: :fields.', ['fields' => implode(', ', $letterMissing)]) }}
        @if (auth()->user()?->isAdmin())
            <a href="{{ route('company.edit') }}" class="ml-1 font-semibold underline">{{ __('Completar ahora') }}</a>
        @endif
    </div>
@endif
