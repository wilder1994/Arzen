import { locationMarkerIcon, whenGoogleMapsReady } from './google-maps';

const locale = document.documentElement.lang?.startsWith('en') ? 'en' : 'es';
const t = {
    viewWeapon: locale === 'en' ? 'View weapon' : 'Ver arma',
    weaponCount: locale === 'en' ? 'Weapon count' : 'Cantidad de armas',
    serial: locale === 'en' ? 'Serial' : 'Serie',
    detail: locale === 'en' ? 'Detail' : 'Detalle',
    noResults: locale === 'en' ? 'No matches found.' : 'No se encontraron coincidencias.',
    writeSerial: locale === 'en' ? 'Type at least 2 characters.' : 'Escribe al menos 2 caracteres.',
    clearSearch: locale === 'en' ? 'Clear' : 'Limpiar',
    missingKey: locale === 'en'
        ? 'Google Maps is not configured. Add GOOGLE_MAPS_API_KEY in the environment file.'
        : 'Google Maps no está configurado. Agrega GOOGLE_MAPS_API_KEY en el archivo de entorno.',
    unavailable: locale === 'en'
        ? 'Google Maps could not be loaded.'
        : 'No se pudo cargar Google Maps.',
};

const normalizeText = (value) => {
    if (!value) {
        return '';
    }

    return value
        .toString()
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();
};

const showMapMessage = (mapElement, message) => {
    mapElement.innerHTML = `<div class="flex h-full items-center justify-center px-6 text-center text-sm text-gray-600">${message}</div>`;
};

const initMap = async () => {
    const mapElement = document.getElementById('weapons-map');
    const searchInput = document.getElementById('weapons-map-search');
    const searchResults = document.getElementById('weapons-map-search-results');
    const searchShell = document.getElementById('weapons-map-search-shell');
    if (!mapElement) {
        return;
    }

    const endpoint = mapElement.dataset.endpoint;
    let maps;

    try {
        maps = await whenGoogleMapsReady();
    } catch (error) {
        showMapMessage(mapElement, error?.message === 'missing-google-maps-key' ? t.missingKey : t.unavailable);
        return;
    }

    const map = new maps.Map(mapElement, {
        center: { lat: 4.5709, lng: -74.2973 },
        zoom: 6,
        mapTypeId: 'hybrid',
        mapTypeControl: true,
        streetViewControl: false,
        fullscreenControl: true,
    });
    const infoWindow = new maps.InfoWindow();
    const icon = locationMarkerIcon();
    let searchIndex = [];
    let searchDebounce = null;

    const hideSearchResults = () => {
        if (!searchResults) {
            return;
        }
        searchResults.classList.add('hidden');
        searchResults.innerHTML = '';
    };

    const zoomToItem = (item) => {
        if (!item) {
            return;
        }
        map.setCenter({ lat: item.lat, lng: item.lng });
        map.setZoom(16);
        if (item.marker) {
            infoWindow.setContent(item.popup);
            infoWindow.open({ map, anchor: item.marker });
        }
    };

    const renderSearchResults = (items) => {
        if (!searchResults) {
            return;
        }
        if (!items.length) {
            searchResults.innerHTML = `<div class="px-3 py-2 text-sm text-gray-500">${t.noResults}</div>`;
            searchResults.classList.remove('hidden');
            return;
        }

        const shown = items.slice(0, 8);
        searchResults.innerHTML = shown
            .map(
                (item, index) => `
                    <button
                        type="button"
                        class="block w-full border-b border-gray-100 px-3 py-2 text-left text-sm hover:bg-blue-50"
                        data-weapon-result-index="${index}"
                    >
                        <span class="block font-medium text-gray-800">${item.serial || '-'}</span>
                        <span class="block text-xs text-gray-500">${item.client || '-'}</span>
                    </button>
                `
            )
            .join('');
        searchResults.classList.remove('hidden');

        Array.from(searchResults.querySelectorAll('[data-weapon-result-index]')).forEach((button) => {
            button.addEventListener('click', () => {
                const index = Number.parseInt(button.dataset.weaponResultIndex || '', 10);
                const item = shown[index];
                if (!item) {
                    return;
                }
                searchInput.value = item.serial || '';
                hideSearchResults();
                zoomToItem(item);
            });
        });
    };

    if (searchInput && searchResults) {
        searchInput.insertAdjacentHTML(
            'afterend',
            `<button id="weapons-map-search-clear" type="button" class="mt-2 text-xs text-blue-700 hover:text-blue-900">${t.clearSearch}</button>`
        );
        const clearButton = document.getElementById('weapons-map-search-clear');

        searchInput.addEventListener('input', (event) => {
            const query = normalizeText(event.target.value || '');
            if (searchDebounce) {
                clearTimeout(searchDebounce);
            }
            searchDebounce = setTimeout(() => {
                if (query.length < 2) {
                    searchResults.innerHTML = `<div class="px-3 py-2 text-sm text-gray-500">${t.writeSerial}</div>`;
                    searchResults.classList.remove('hidden');
                    return;
                }
                const filtered = searchIndex.filter((item) => {
                    const serialMatch = normalizeText(item.serial).includes(query);
                    const clientMatch = normalizeText(item.client).includes(query);
                    return serialMatch || clientMatch;
                });
                renderSearchResults(filtered);
            }, 180);
        });

        searchInput.addEventListener('keydown', (event) => {
            if (event.key !== 'Enter') {
                return;
            }
            const firstButton = searchResults.querySelector('[data-weapon-result-index="0"]');
            if (!firstButton) {
                return;
            }
            event.preventDefault();
            firstButton.click();
        });

        if (clearButton) {
            clearButton.addEventListener('click', () => {
                searchInput.value = '';
                hideSearchResults();
            });
        }

        document.addEventListener('click', (event) => {
            if (!searchShell || searchShell.contains(event.target)) {
                return;
            }
            hideSearchResults();
        });
    }

    if (!endpoint) {
        return;
    }

    let items = [];
    try {
        const response = await fetch(endpoint, { headers: { Accept: 'application/json' } });
        items = await response.json();
    } catch (error) {
        return;
    }

    if (!Array.isArray(items) || items.length === 0) {
        return;
    }

    const grouped = new Map();
    items.forEach((item) => {
        const key = `${item.lat},${item.lng}`;
        if (!grouped.has(key)) {
            grouped.set(key, []);
        }
        grouped.get(key).push(item);
    });

    const bounds = new maps.LatLngBounds();

    grouped.forEach((groupItems) => {
        const lat = Number(groupItems[0].lat);
        const lng = Number(groupItems[0].lng);
        const clientName = groupItems[0].client ?? '-';
        const rows = groupItems
            .map(
                (item) => `
                    <tr>
                        <td class="pr-3 py-1">${item.serial_number ?? '-'}</td>
                        <td class="py-1 text-right">
                            <a href="${item.link}" target="_blank" rel="noopener noreferrer">${t.viewWeapon}</a>
                        </td>
                    </tr>
                `
            )
            .join('');
        const popup = `
            <div class="text-sm sj-weapons-map-popup">
                <div class="font-semibold mb-1">${clientName}</div>
                <div class="mb-2 text-xs text-gray-600">${t.weaponCount}: ${groupItems.length}</div>
                <table class="w-full text-xs">
                    <thead>
                        <tr class="text-left text-gray-600">
                            <th class="pr-3 pb-1">${t.serial}</th>
                            <th class="pb-1 text-right">${t.detail}</th>
                        </tr>
                    </thead>
                </table>
                <div class="sj-weapons-map-popup__table-wrap">
                    <table class="w-full text-xs">
                        <tbody>${rows}</tbody>
                    </table>
                </div>
            </div>
        `;
        const marker = new maps.Marker({
            position: { lat, lng },
            map,
            icon,
            title: clientName,
        });
        marker.addListener('click', () => {
            infoWindow.setContent(popup);
            infoWindow.open({ map, anchor: marker });
        });
        bounds.extend({ lat, lng });

        groupItems.forEach((item) => {
            searchIndex.push({
                serial: item.serial_number ?? '',
                client: item.client ?? '',
                lat,
                lng,
                marker,
                popup,
            });
        });
    });

    if (!bounds.isEmpty()) {
        map.fitBounds(bounds, 30);
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initMap);
} else {
    initMap();
}
