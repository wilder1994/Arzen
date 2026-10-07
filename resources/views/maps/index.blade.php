<x-app-layout>
    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide">
            {{-- overflow-visible: el InfoWindow de Google Maps no debe recortarse con el card --}}
            <div class="sj-ui-card overflow-visible">
                <div class="sj-ui-card__body p-6 text-gray-900">
                    <div class="mb-4 sj-ui-field" id="weapons-map-search-shell">
                        <label for="weapons-map-search" class="sj-ui-field__label">
                            {{ __('Buscar arma por serie') }}
                        </label>
                        <div class="relative">
                            <input
                                id="weapons-map-search"
                                type="text"
                                class="sj-ui-field__control"
                                placeholder="{{ __('Escribe la serie del arma') }}"
                                autocomplete="off"
                            >
                            <div
                                id="weapons-map-search-results"
                                class="absolute left-0 right-0 top-full z-30 mt-1 hidden max-h-64 overflow-auto rounded-md border border-gray-200 bg-white shadow-lg"
                            ></div>
                        </div>
                    </div>
                    <div id="weapons-map" data-endpoint="{{ route('maps.weapons') }}" class="h-[70vh] w-full rounded border border-gray-200"></div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>

@include('partials.google-maps')
@vite('resources/js/map.js')




