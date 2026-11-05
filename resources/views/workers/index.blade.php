<x-app-layout>
    <x-slot name="header">
        <div class="sj-list-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Trabajadores') }}</h2>
            </div>

            <form class="sj-list-header__search" role="search" onsubmit="return false;">
                <input
                    id="workers-search"
                    type="search"
                    name="q"
                    value="{{ $search }}"
                    class="sj-ui-field__control"
                    placeholder="{{ __('Nombre o cédula') }}"
                >
                <button type="button" id="workers-search-clear" class="sj-list-header__clear" aria-label="{{ __('Limpiar búsqueda') }}" @if ($search === '') hidden @endif>&times;</button>
            </form>

            <div class="sj-list-header__end">
                <span id="workers-global-total" class="sj-list-header__count">{{ $countLabel }}</span>
                <button type="button" id="workers-open-filters" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Filtros') }}</button>
                @can('create', App\Models\Worker::class)
                    <a href="{{ route('workers.create') }}" class="sj-ui-btn sj-ui-btn--primary">{{ __('Nuevo trabajador') }}</a>
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div
            class="sj-page-shell sj-page-shell--wide"
            x-data="{
                historyOpen: false,
                historyTitle: '',
                historyLoading: false,
                historyEntries: [],
                async openHistory(title, url) {
                    this.historyTitle = title;
                    this.historyOpen = true;
                    this.historyLoading = true;
                    this.historyEntries = [];
                    try {
                        const r = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '' }});
                        const d = await r.json();
                        this.historyEntries = d.entries || [];
                    } catch (e) {
                        this.historyEntries = [];
                    } finally {
                        this.historyLoading = false;
                    }
                }
            }"
        >
            @if (session('status'))
                <div class="mb-4 rounded bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @if ($errors->has('restore'))
                <div class="mb-4 rounded bg-red-50 p-3 text-sm text-red-700">
                    {{ $errors->first('restore') }}
                </div>
            @endif

            <div id="workers-filters" class="fixed inset-0 z-[1400] hidden items-center justify-center bg-black/50 p-4">
                <div class="absolute inset-0" data-close-filters></div>
                <form method="GET" action="{{ route('workers.index') }}" class="relative w-full max-w-lg overflow-hidden rounded-lg bg-white shadow-xl">
                    <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                        <h3 class="sj-type-section text-gray-900">{{ __('Filtros') }}</h3>
                        <button type="button" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm" data-close-filters>{{ __('Cerrar') }}</button>
                    </div>
                    <div class="grid gap-4 px-5 py-4 sm:grid-cols-2">
                        <input type="hidden" name="q" id="workers-filter-q" value="{{ $search }}">
                        <div class="sj-ui-field">
                            <label for="workers-filter-client" class="sj-ui-field__label">{{ __('Cliente') }}</label>
                            <select id="workers-filter-client" name="client_id" class="sj-ui-field__control">
                                <option value="">{{ __('Todos') }}</option>
                                @foreach ($clients as $client)
                                    <option value="{{ $client->id }}" @selected($clientId == $client->id)>{{ $client->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sj-ui-field">
                            <label for="workers-filter-position" class="sj-ui-field__label">{{ __('Cargo') }}</label>
                            <select id="workers-filter-position" name="position_id" class="sj-ui-field__control">
                                <option value="">{{ __('Todos') }}</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}" @selected($positionId == $position->id)>{{ $position->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        @if ($showResponsibleFilter)
                            <div class="sj-ui-field">
                                <label for="workers-filter-responsible" class="sj-ui-field__label">{{ __('Responsable') }}</label>
                                <select id="workers-filter-responsible" name="responsible_user_id" class="sj-ui-field__control">
                                    <option value="">{{ __('Todos') }}</option>
                                    @foreach ($responsibles as $responsible)
                                        <option value="{{ $responsible->id }}" @selected($responsibleId == $responsible->id)>{{ $responsible->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif
                        <div class="sj-ui-field">
                            <label for="workers-filter-archive" class="sj-ui-field__label">{{ __('Estado') }}</label>
                            <select id="workers-filter-archive" name="archive" class="sj-ui-field__control">
                                <option value="active" @selected($archiveFilter === 'active')>{{ __('Solo activos') }}</option>
                                <option value="archived" @selected($archiveFilter === 'archived')>{{ __('Solo archivados') }}</option>
                                <option value="all" @selected($archiveFilter === 'all')>{{ __('Todos') }}</option>
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end border-t border-gray-200 px-5 py-4">
                        <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Aplicar') }}</button>
                    </div>
                </form>
            </div>

            <div>
                    <div class="sj-table-wrap overflow-x-auto">
                    <table class="sj-table sj-table--align-left sj-table--compact sj-user-list">
                        <thead>
                            <tr>
                                <th>{{ __('Nombre') }}</th>
                                <th>{{ __('Cédula') }}</th>
                                <th>{{ __('Cargo') }}</th>
                                <th>{{ __('Puesto') }}</th>
                                <th>{{ __('Cliente') }}</th>
                                <th>{{ __('Responsable') }}</th>
                                <th>{{ __('Estado') }}</th>
                                <th>{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody id="workers-tbody">
                            @include('workers.partials.index_rows', ['workers' => $workers])
                        </tbody>
                    </table>
                    </div>

                    <div id="workers-pagination">
                        @include('workers.partials.index_pagination', ['workers' => $workers])
                    </div>

                    <div
                        x-show="historyOpen"
                        x-cloak
                        class="fixed inset-0 z-[4000] flex items-center justify-center bg-black/50 p-4"
                        @keydown.escape.window="historyOpen = false"
                    >
                        <div class="max-h-[85vh] w-full max-w-lg overflow-hidden rounded-lg bg-white shadow-xl" @click.outside="historyOpen = false">
                            <div class="flex items-center justify-between border-b px-4 py-3">
                                <h3 class="sj-type-section text-gray-900">{{ __('Historial') }}: <span x-text="historyTitle"></span></h3>
                                <button type="button" class="text-2xl leading-none text-gray-500 hover:text-gray-800" @click="historyOpen = false">&times;</button>
                            </div>
                            <div class="max-h-[calc(85vh-4rem)] overflow-y-auto p-4 text-sm">
                                <template x-if="historyLoading">
                                    <p class="text-gray-500">{{ __('Cargando…') }}</p>
                                </template>
                                <template x-if="!historyLoading && historyEntries.length === 0">
                                    <p class="text-gray-500">{{ __('No hay entradas en el historial.') }}</p>
                                </template>
                                <ul class="space-y-4" x-show="!historyLoading && historyEntries.length">
                                    <template x-for="e in historyEntries" :key="e.id">
                                        <li class="rounded border border-gray-100 bg-gray-50 p-3">
                                            <div class="text-xs text-gray-500">
                                                <span x-text="e.at"></span>
                                                <span x-show="e.user"> · <span x-text="e.user"></span></span>
                                            </div>
                                            <pre class="mt-2 whitespace-pre-wrap font-sans text-gray-800 text-sm" x-text="e.body"></pre>
                                        </li>
                                    </template>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (() => {
                const input = document.getElementById('workers-search');
                const clearButton = document.getElementById('workers-search-clear');
                const count = document.getElementById('workers-global-total');
                const tbody = document.getElementById('workers-tbody');
                const pagination = document.getElementById('workers-pagination');
                const filters = document.getElementById('workers-filters');
                const openFilters = document.getElementById('workers-open-filters');
                const filterQuery = document.getElementById('workers-filter-q');
                const filterForm = filters?.querySelector('form');

                if (!input || !clearButton || !count || !tbody || !pagination || !filters || !openFilters) {
                    return;
                }

                const syncClear = () => {
                    clearButton.hidden = input.value.trim() === '';
                };

                const applyStateToUrl = (url, { resetPage = false } = {}) => {
                    const query = input.value.trim();
                    const page = resetPage ? '1' : (url.searchParams.get('page') || '1');
                    const kept = new URLSearchParams(url.search);
                    url.search = '';
                    ['client_id', 'position_id', 'responsible_user_id', 'archive'].forEach((key) => {
                        const value = kept.get(key);
                        if (value) {
                            url.searchParams.set(key, value);
                        }
                    });
                    if (query !== '') {
                        url.searchParams.set('q', query);
                    }
                    url.searchParams.set('page', page);
                };

                const updateList = async (url) => {
                    const response = await fetch(url, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', Accept: 'application/json' },
                    });
                    if (!response.ok) {
                        return;
                    }
                    const data = await response.json();
                    tbody.innerHTML = data.tbody;
                    pagination.innerHTML = data.pagination;
                    if (data.count_label) {
                        count.textContent = data.count_label;
                    }
                    window.Alpine?.initTree?.(tbody);
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

                const openFilterModal = () => {
                    if (filterQuery) {
                        filterQuery.value = input.value.trim();
                    }
                    filters.classList.remove('hidden');
                    filters.classList.add('flex');
                };

                const closeFilterModal = () => {
                    filters.classList.add('hidden');
                    filters.classList.remove('flex');
                };

                openFilters.addEventListener('click', openFilterModal);
                filters.addEventListener('click', (event) => {
                    if (event.target.closest('[data-close-filters]')) {
                        closeFilterModal();
                    }
                });
                filterForm?.addEventListener('submit', () => {
                    if (filterQuery) {
                        filterQuery.value = input.value.trim();
                    }
                });

                pagination.addEventListener('click', (event) => {
                    const link = event.target.closest('a');
                    if (!link || !link.href) {
                        return;
                    }
                    event.preventDefault();
                    const url = new URL(link.href);
                    applyStateToUrl(url);
                    window.history.replaceState({}, '', url.toString());
                    updateList(url.toString());
                });
            })();
        </script>
    @endpush
</x-app-layout>
