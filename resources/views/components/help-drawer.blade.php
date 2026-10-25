@props(['title'])

@php
    $drawerId = 'help-'.\Illuminate\Support\Str::random(8);
@endphp

<div x-data="{ open: false }" class="sj-help">
    <button
        type="button"
        class="sj-help__trigger"
        @click="open = true"
        :aria-expanded="open.toString()"
        aria-controls="{{ $drawerId }}"
        title="{{ __('Ayuda') }}"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 5.25h.008v.008H12v-.008Z" />
        </svg>
        <span class="sr-only">{{ __('Ayuda: :title', ['title' => $title]) }}</span>
    </button>

    <template x-teleport="body">
        <div
            id="{{ $drawerId }}"
            class="sj-help-drawer"
            x-show="open"
            x-transition:leave="transition duration-150"
            x-cloak
            role="dialog"
            aria-modal="true"
            aria-labelledby="{{ $drawerId }}-title"
            @keydown.escape.window="open = false"
            x-effect="document.body.classList.toggle('sj-help-lock', open); if (open) { $nextTick(() => $refs.close.focus()) }"
        >
            <div class="sj-help-drawer__backdrop" x-show="open" x-transition.opacity.duration.200ms @click="open = false"></div>
            <aside
                class="sj-help-drawer__panel"
                x-show="open"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="translate-x-full"
                x-transition:enter-end="translate-x-0"
                x-transition:leave="transition ease-in duration-150"
                x-transition:leave-start="translate-x-0"
                x-transition:leave-end="translate-x-full"
            >
                <header class="sj-help-drawer__header">
                    <div class="min-w-0">
                        <p class="sj-help-drawer__eyebrow">{{ __('Ayuda') }}</p>
                        <h2 id="{{ $drawerId }}-title" class="sj-help-drawer__title">{{ $title }}</h2>
                    </div>
                    <button type="button" class="sj-help-drawer__close" x-ref="close" @click="open = false" aria-label="{{ __('Cerrar ayuda') }}">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path d="M6.28 5.22a.75.75 0 0 0-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 1 0 1.06 1.06L10 11.06l3.72 3.72a.75.75 0 1 0 1.06-1.06L11.06 10l3.72-3.72a.75.75 0 0 0-1.06-1.06L10 8.94 6.28 5.22Z" />
                        </svg>
                    </button>
                </header>
                <div class="sj-help-drawer__body">
                    {{ $slot }}
                </div>
            </aside>
        </div>
    </template>
</div>
