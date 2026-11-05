<x-app-layout>
    <x-slot name="header">
        <div class="sj-list-header">
            <div class="sj-section-header__main">
                <div class="sj-section-header__title-row">
                    <h2 class="sj-section-header__title">{{ __('Instalaciones') }}</h2>
                    <x-help-drawer :title="__('Instalaciones')">
                        @include('installations.partials.help')
                    </x-help-drawer>
                </div>
            </div>

            <form class="sj-list-header__search" role="search" action="{{ route('installations.index') }}" onsubmit="return false;">
                <input
                    id="installations-q"
                    name="q"
                    type="search"
                    value="{{ $search }}"
                    class="sj-ui-field__control"
                    placeholder="{{ __('Cliente o instalación') }}"
                >
                <button type="button" id="installations-search-clear" class="sj-list-header__clear" aria-label="{{ __('Limpiar búsqueda') }}" @if ($search === '') hidden @endif>&times;</button>
            </form>

            <div class="sj-list-header__end">
                @can('create', App\Models\Post::class)
                    <a href="{{ route('installations.create') }}" class="sj-ui-btn sj-ui-btn--primary">{{ __('Nueva instalación') }}</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide space-y-4">
            @if (session('status'))
                <div class="rounded bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded border border-red-200 bg-red-50 p-3 text-sm text-red-700">{{ $errors->first() }}</div>
            @endif

            <div id="installations-results">
                @include('installations.partials.index_results', [
                    'installations' => $installations,
                    'search' => $search,
                ])
            </div>
        </div>

        <div id="location-modal" class="fixed inset-0 z-[1400] hidden items-center justify-center bg-black/50 p-4">
            <div class="absolute inset-0" data-close-map></div>
            <div class="relative w-full max-w-3xl overflow-hidden rounded-lg bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                    <h3 id="location-modal-title" class="sj-type-section text-gray-900"></h3>
                    <button type="button" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm" data-close-map>{{ __('Cerrar') }}</button>
                </div>
                <iframe id="location-modal-frame" class="h-[28rem] w-full" title="{{ __('Ubicación') }}"></iframe>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const input = document.getElementById('installations-q');
                const clearButton = document.getElementById('installations-search-clear');
                const results = document.getElementById('installations-results');
                const modal = document.getElementById('location-modal');
                const modalTitle = document.getElementById('location-modal-title');
                const modalFrame = document.getElementById('location-modal-frame');

                if (!input || !clearButton || !results || !modal) {
                    return;
                }

                const syncClear = () => {
                    clearButton.hidden = input.value.trim() === '';
                };

                const openMap = (title, lat, lng) => {
                    modalTitle.textContent = title;
                    modalFrame.src = 'https://maps.google.com/maps?q=' + encodeURIComponent(lat + ',' + lng) + '&z=16&output=embed';
                    modal.classList.remove('hidden');
                    modal.classList.add('flex');
                };

                const closeMap = () => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                    modalFrame.src = '';
                };

                const applyStateToUrl = (url, { resetPage = false } = {}) => {
                    const query = input.value.trim();
                    const page = resetPage ? '1' : (url.searchParams.get('page') || '1');
                    url.search = '';
                    if (query !== '') {
                        url.searchParams.set('q', query);
                    }
                    url.searchParams.set('page', page);
                };

                const updateList = async (url) => {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });
                    if (!response.ok) {
                        return;
                    }
                    const data = await response.json();
                    results.innerHTML = data.results;
                };

                let timer = null;
                input.addEventListener('input', () => {
                    syncClear();
                    const url = new URL(window.location.href);
                    applyStateToUrl(url, { resetPage: true });
                    window.history.replaceState({}, '', url.toString());
                    if (timer) {
                        clearTimeout(timer);
                    }
                    timer = setTimeout(() => updateList(url.toString()), 300);
                });

                clearButton.addEventListener('click', () => {
                    input.value = '';
                    input.dispatchEvent(new Event('input'));
                    input.focus();
                });

                modal.addEventListener('click', (event) => {
                    if (event.target.closest('[data-close-map]')) {
                        closeMap();
                    }
                });

                results.addEventListener('click', (event) => {
                    const mapButton = event.target.closest('.sj-map-pin');
                    if (mapButton) {
                        openMap(mapButton.dataset.mapTitle || '', mapButton.dataset.mapLat || '', mapButton.dataset.mapLng || '');
                        return;
                    }

                    const link = event.target.closest('a');
                    if (link && link.href && link.closest('.pagination, nav')) {
                        event.preventDefault();
                        const url = new URL(link.href);
                        applyStateToUrl(url);
                        window.history.replaceState({}, '', url.toString());
                        updateList(url.toString());
                    }
                });
            })();
        </script>
    @endpush
</x-app-layout>
