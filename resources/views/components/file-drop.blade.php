@props([
    'name',
    'accept',
    'label',
    'kind' => 'image',
    'imageUrl' => null,
    'invalidMessage' => null,
])

<div {{ $attributes->merge(['class' => 'sj-file-drop']) }} data-file-drop data-kind="{{ $kind }}" data-invalid-message="{{ $invalidMessage ?? __('Ese tipo de archivo no está permitido aquí.') }}" data-pending-label="{{ __('Se guardará: :name') }}">
    <div class="sj-file-drop__zone" data-file-drop-zone tabindex="0" role="button" aria-label="{{ __(':label: haz clic, arrastra o pega un archivo', ['label' => $label]) }}">
        <input type="file" name="{{ $name }}" accept="{{ $accept }}" class="sr-only" tabindex="-1" data-file-drop-input>

        <div class="sj-file-drop__preview">
            @if ($kind === 'image')
                <img src="{{ $imageUrl }}" alt="{{ $label }}" @class(['sj-file-drop__image', 'hidden' => ! $imageUrl]) data-file-drop-image>
                @unless ($imageUrl)
                    <span class="sj-file-drop__empty" data-file-drop-current>{{ __('Sin archivo') }}</span>
                @endunless
            @else
                <div class="sj-file-drop__current" data-file-drop-current>{{ $slot }}</div>
                <div class="sj-file-drop__document hidden" data-file-drop-selected>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" />
                    </svg>
                    <span data-file-drop-name></span>
                </div>
            @endif
        </div>

        <p class="sj-file-drop__cue">{{ __('Haz clic, arrastra o pega (Ctrl+V)') }}</p>
    </div>

    @isset($meta)
        <div class="sj-file-drop__meta">{{ $meta }}</div>
    @endisset
</div>
