<x-app-layout>
    <x-slot name="header">
        @php
            $selectedYear = $filters['year'] ?? null;
        @endphp

        <div class="sj-incident-header">
            <div class="sj-incident-header__main">
                <p class="sj-incident-header__eyebrow">{{ __('Centro de reportes') }}</p>
                <h2 class="sj-incident-header__title">
                    {{ $selectedType ? __('Novedades: ') . $selectedType->name : __('Novedades operativas') }}
                </h2>
                <p class="sj-incident-header__subtitle">
                    {{ __('Consolidado histórico por tipo, modalidad y arma para seguimiento gerencial.') }}
                </p>
            </div>

            <div class="sj-incident-header__side">
                <div class="sj-incident-header__actions">
                    <a href="{{ route('reports.index') }}" class="sj-ui-btn sj-ui-btn--ghost">
                        {{ __('Volver a reportes') }}
                    </a>

                    <form
                        method="GET"
                        action="{{ $selectedType ? route('reports.weapon-incidents.show', $selectedType) : route('reports.weapon-incidents.index') }}"
                        class="sj-incident-header__filter"
                    >
                        <div class="sj-incident-header__filter-group">
                            <span class="sj-incident-header__inline-label">{{ __('Año') }}</span>
                            <select id="incident-year" name="year" class="sj-dashboard-filter__select sj-incident-header__control">
                                <option value="" @selected($selectedYear === null)>{{ __('Todos') }}</option>
                                @foreach ($years as $year)
                                    <option value="{{ $year }}" @selected($selectedYear === (int) $year)>{{ $year }}</option>
                                @endforeach
                            </select>
                        </div>

                        <a href="{{ $selectedType ? route('reports.weapon-incidents.show', $selectedType) : route('reports.weapon-incidents.index') }}" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm">
                            {{ __('Limpiar') }}
                        </a>
                        <button type="submit" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm">
                            {{ __('Aplicar') }}
                        </button>
                    </form>

                    <button
                        type="button"
                        class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm"
                        x-data=""
                        x-on:click.prevent="$dispatch('open-modal', 'weapon-incidents-list')"
                    >
                        {{ __('Lista') }}
                    </button>

                    @can('create', App\Models\WeaponIncident::class)
                        <button type="button" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm" data-open-modal="weapon-incident-modal">
                            {{ __('Agregar reporte') }}
                        </button>
                    @endcan
                </div>
            </div>
        </div>
    </x-slot>

    <div
        class="py-8"
        data-incident-module
        data-incident-modality-map='@json($modalityMap)'
        data-open-create-modal="{{ old('incident_type_id') && !old('focus_incident_id') ? '1' : '0' }}"
        data-open-incident-case="{{ old('focus_incident_id', request('focus_incident')) }}"
    >
        <div class="sj-report-shell mx-auto space-y-6 px-4 sm:px-6 lg:px-8">
            @if (session('status'))
                <div class="rounded-xl bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            @include('reports.weapon-incidents.base')
        </div>
    </div>

    <x-modal name="weapon-incidents-list" maxWidth="7xl" focusable>
        <div class="border-b border-gray-100 px-4 py-4 sm:px-6">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500">{{ __('Detalle') }}</p>
                    <h3 class="sj-type-section text-gray-900">{{ __('Listado de reportes') }}</h3>
                    <p class="mt-1 max-w-2xl text-sm text-gray-600">
                        {{ __('Mismo alcance que los filtros de año y tipo. Busca por cualquier texto visible (serie, código, cliente, tipo…).') }}
                    </p>
                </div>
                <button
                    type="button"
                    class="sj-ui-btn sj-ui-btn--ghost shrink-0"
                    x-on:click="$dispatch('close-modal', 'weapon-incidents-list')"
                >
                    {{ __('Cerrar') }}
                </button>
            </div>
        </div>

        <div class="space-y-4 px-4 pb-6 pt-4 sm:px-6" x-data="{ q: '' }">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                <div class="w-full sm:max-w-xl">
                    <label for="weapon-incidents-list-search" class="sr-only">{{ __('Buscar en la tabla') }}</label>
                    <input
                        id="weapon-incidents-list-search"
                        type="search"
                        x-model.debounce.300ms="q"
                        autocomplete="off"
                        spellcheck="false"
                        placeholder="{{ __('Buscar (ej. número de serie, código arma, cliente, tipo…)') }}"
                        class="sj-ui-field__control block w-full"
                    />
                </div>
                <p class="text-xs text-gray-500">
                    {{ __('Mostrando :count registros', ['count' => $incidents->count()]) }}
                </p>
            </div>

            @include('reports.weapon-incidents.partials.incidents-table')
        </div>
    </x-modal>

    @can('create', App\Models\WeaponIncident::class)
        @include('reports.weapon-incidents.modal')
    @endcan
</x-app-layout>
