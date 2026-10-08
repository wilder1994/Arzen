@php
    $photoDescriptions = \App\Models\VestPhoto::DESCRIPTIONS;
    $photoIndex = 0;
@endphp

<section
    class="border-t border-slate-200 pt-6"
    data-vest-form-photos
    data-images-only-message="{{ __('Solo puede usar archivos de imagen.') }}"
>
    <div class="mb-3">
        <h3 class="sj-type-eyebrow text-gray-500">{{ __('Fotografías del chaleco') }}</h3>
        <p class="sj-form-help mt-1">{{ __('Opcional. 4 fotos: 2 vistas completas y 2 placas de serie. Clic, arrastre o pegue en cada casilla.') }}</p>
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        @foreach ($photoDescriptions as $description => $label)
            @php $photoIndex++; @endphp
            <div
                class="block cursor-pointer"
                data-photo-picker-zone
                data-drop-zone
                tabindex="0"
                title="{{ __('Tomar foto, elegir de galería, arrastrar o pegar') }}"
            >
                <input
                    id="vest_photo_{{ $photoIndex }}"
                    name="photos[]"
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/*"
                    class="hidden"
                    data-photo-description="{{ $description }}"
                    data-preview-target="vest_photo_preview_{{ $photoIndex }}"
                    data-placeholder-target="vest_photo_placeholder_{{ $photoIndex }}"
                >

                <div
                    class="relative flex h-36 w-full items-center justify-center overflow-hidden rounded-lg border border-dashed border-gray-300 bg-gray-50 text-xs text-gray-500 transition"
                    data-drop-surface
                >
                    <div class="sj-paste-proxy absolute inset-0 opacity-0" data-paste-proxy contenteditable="true" spellcheck="false"></div>
                    <span
                        id="vest_photo_placeholder_{{ $photoIndex }}"
                        class="absolute inset-x-0 top-1 z-10 px-2 py-1 text-center text-[11px] font-medium leading-snug text-gray-600"
                    >
                        {{ __('Arrastra, selecciona o pega una foto') }}
                    </span>
                    <img
                        id="vest_photo_preview_{{ $photoIndex }}"
                        alt="{{ $label }}"
                        class="relative z-10 hidden h-full w-full rounded bg-gray-50 object-contain"
                    >
                </div>

                <div class="mt-1.5 text-xs font-medium text-gray-600">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    <x-input-error :messages="$errors->get('photos')" class="mt-2" />
    @php
        $photoErrors = collect($errors->get('photos.*'))->flatten()->all();
    @endphp
    <x-input-error :messages="$photoErrors" class="mt-2" />
</section>

@once
    @push('styles')
        <style>
            [data-vest-form-photos] .sj-paste-proxy {
                position: absolute;
                inset: 0;
                z-index: 20;
                background: transparent;
                border: 0;
                color: transparent;
                caret-color: transparent;
                font-size: 1px;
                line-height: 1;
                margin: 0;
                outline: none;
                padding: 0;
                user-select: none;
                -webkit-user-select: none;
            }

            [data-vest-form-photos] [data-photo-picker-zone]:focus-within [data-drop-surface] {
                border-color: rgba(99, 102, 241, 0.55);
                box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.15);
            }
        </style>
    @endpush
@endonce
