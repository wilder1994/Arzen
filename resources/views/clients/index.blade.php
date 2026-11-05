<x-app-layout>
    @php
        $pageTitle = Auth::user()->isResponsible() && !Auth::user()->isAdmin() ? __('Mis clientes') : __('Clientes');
        $clientCountLabel = number_format($clients->total()) . ' ' . ($clients->total() === 1 ? 'cliente' : 'clientes');
    @endphp

    <x-slot name="header">
        <div class="sj-list-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ $pageTitle }}</h2>
            </div>

            <form class="sj-list-header__search" role="search" onsubmit="return false;">
                <input
                    id="clients-search"
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    class="sj-ui-field__control"
                    placeholder="{{ __('Buscar cliente') }}"
                >
                <button type="button" id="clients-search-clear" class="sj-list-header__clear" aria-label="{{ __('Limpiar búsqueda') }}" @if ($search === '') hidden @endif>&times;</button>
            </form>

            <div class="sj-list-header__end">
                <span id="clients-count" class="sj-list-header__count">{{ $clientCountLabel }}</span>
                @can('create', App\Models\Client::class)
                    <a href="{{ route('clients.create') }}" class="sj-ui-btn sj-ui-btn--primary">{{ __('Nuevo cliente') }}</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide space-y-4">
            @if (session('status'))
                <div class="rounded border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                    <ul class="list-disc space-y-1 pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div id="clients-results">
                @include('clients.partials.index_results', ['clients' => $clients, 'search' => $search])
            </div>

            <div id="client-ficha-modal" class="fixed inset-0 z-[1400] hidden items-center justify-center bg-black/50 p-4">
                <div class="absolute inset-0" data-close-ficha></div>
                <div class="relative flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-lg bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                        <h3 id="client-ficha-title" class="sj-type-section text-gray-900"></h3>
                        <button type="button" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm" data-close-ficha>{{ __('Cerrar') }}</button>
                    </div>
                    <div id="client-ficha-body" class="overflow-y-auto px-5 py-4"></div>
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
    </div>

    @push('scripts')
        <script>
            (() => {
                const input = document.getElementById('clients-search');
                const clearButton = document.getElementById('clients-search-clear');
                const results = document.getElementById('clients-results');
                const count = document.getElementById('clients-count');
                const modal = document.getElementById('location-modal');
                const modalTitle = document.getElementById('location-modal-title');
                const modalFrame = document.getElementById('location-modal-frame');
                const fichaModal = document.getElementById('client-ficha-modal');
                const fichaTitle = document.getElementById('client-ficha-title');
                const fichaBody = document.getElementById('client-ficha-body');

                if (!input || !results || !count || !clearButton || !modal || !fichaModal) {
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

                const closeFicha = () => {
                    fichaModal.classList.add('hidden');
                    fichaModal.classList.remove('flex');
                    fichaBody.replaceChildren();
                };

                const openFicha = (id, title) => {
                    const template = document.getElementById(id);
                    if (!template) {
                        return;
                    }

                    fichaTitle.textContent = title;
                    fichaBody.replaceChildren(template.content.cloneNode(true));
                    fichaModal.classList.remove('hidden');
                    fichaModal.classList.add('flex');
                };

                const currentState = () => ({
                    q: input.value.trim(),
                });

                const applyStateToUrl = (url, { resetPage = false } = {}) => {
                    const state = currentState();
                    const page = resetPage ? '1' : (url.searchParams.get('page') || '1');

                    url.search = '';

                    if (state.q !== '') {
                        url.searchParams.set('q', state.q);
                    }

                    url.searchParams.set('page', page);
                };

                const updateList = async (url) => {
                    closeFicha();

                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    });

                    if (!response.ok) {
                        return;
                    }

                    const data = await response.json();
                    results.innerHTML = data.results;
                    count.textContent = data.countLabel;
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

                    timer = setTimeout(() => {
                        updateList(url.toString());
                    }, 300);
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

                fichaModal.addEventListener('click', (event) => {
                    if (event.target.closest('[data-close-ficha]')) {
                        closeFicha();
                    }
                });

                results.addEventListener('click', (event) => {
                    const fichaButton = event.target.closest('[data-open-ficha]');
                    if (fichaButton) {
                        openFicha(fichaButton.dataset.openFicha || '', fichaButton.dataset.fichaTitle || '');
                        return;
                    }

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
