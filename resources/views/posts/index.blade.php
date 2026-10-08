<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Puestos') }}</h2>
            </div>

            <div class="sj-section-header__actions">
                @can('create', App\Models\Post::class)
                    <a href="{{ route('posts.create') }}" class="sj-ui-btn sj-ui-btn--primary">
                        {{ __('Nuevo puesto') }}
                    </a>
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

            <div class="sj-ui-card overflow-hidden">
                <div class="sj-ui-card__body p-6">
                    <form method="GET" action="{{ route('posts.index') }}" class="sj-ui-filter-bar">
                        <div class="sj-ui-filter-bar__fields">
                            <div class="sj-ui-field min-w-[11rem] w-44 shrink-0 sm:w-52">
                                <label for="posts-filter-q" class="sj-ui-field__label">{{ __('Buscar') }}</label>
                                <input id="posts-filter-q" type="text" name="q" value="{{ $search }}" class="sj-ui-field__control" placeholder="{{ __('Nombre o dirección') }}">
                            </div>
                            <div class="sj-ui-field min-w-[10rem] w-40 shrink-0 sm:w-48">
                                <label for="posts-filter-client" class="sj-ui-field__label">{{ __('Cliente') }}</label>
                                <select id="posts-filter-client" name="client_id" class="sj-ui-field__control">
                                    <option value="">{{ __('Todos') }}</option>
                                    @foreach ($clients as $client)
                                        <option value="{{ $client->id }}" @selected($clientId == $client->id)>{{ $client->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sj-ui-field w-36 shrink-0 sm:w-44">
                                <label for="posts-filter-archive" class="sj-ui-field__label">{{ __('Estado') }}</label>
                                <select id="posts-filter-archive" name="archive" class="sj-ui-field__control">
                                    <option value="active" @selected($archiveFilter === 'active')>{{ __('Solo activos') }}</option>
                                    <option value="archived" @selected($archiveFilter === 'archived')>{{ __('Solo archivados') }}</option>
                                    <option value="all" @selected($archiveFilter === 'all')>{{ __('Todos') }}</option>
                                </select>
                            </div>
                            <p
                                id="posts-global-total"
                                class="sj-ui-field ml-auto shrink-0 self-end text-sm font-medium text-gray-700 whitespace-nowrap"
                                aria-live="polite"
                            >
                                {{ __('Total') }}:
                                <span class="tabular-nums text-gray-900">{{ number_format($postsGlobalTotal, 0, ',', '.') }}</span>
                                {{ trans_choice('puesto|puestos', $postsGlobalTotal) }}
                            </p>
                            <div class="sj-ui-filter-bar__actions">
                                <a href="{{ route('posts.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Limpiar') }}</a>
                                <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Filtrar') }}</button>
                            </div>
                        </div>
                    </form>

                    <div class="sj-table-wrap overflow-x-auto">
                    <table class="sj-table sj-table--align-left min-w-full text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Puesto') }}</th>
                                <th>{{ __('Cliente') }}</th>
                                <th>{{ __('Dirección') }}</th>
                                <th>{{ __('Estado') }}</th>
                                <th>{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody id="posts-tbody">
                            @include('posts.partials.index_rows', ['posts' => $posts])
                        </tbody>
                    </table>
                    </div>

                    <div id="posts-pagination">
                        @include('posts.partials.index_pagination', ['posts' => $posts])
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
</x-app-layout>
