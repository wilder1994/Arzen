import municipios from '../data/colombia_municipios.json';
import { addressComponent, locationMarkerIcon, whenGoogleMapsReady } from './google-maps';

const locale = document.documentElement.lang?.startsWith('en') ? 'en' : 'es';
const t = {
    select: locale === 'en' ? 'Select' : 'Seleccione',
    searching: locale === 'en' ? 'Searching...' : 'Buscando...',
    searchByName: locale === 'en' ? 'Search by city, municipality or department' : 'Buscar por ciudad, municipio o departamento',
    noResults: locale === 'en' ? 'No matches found.' : 'No se encontraron resultados.',
    incompleteAddress: locale === 'en'
        ? 'Address or municipality could not be completed. Adjust it manually if needed.'
        : 'No se pudo completar la direcciÃ³n o el municipio. Ajusta manualmente si es necesario.',
    geocodeFailed: locale === 'en'
        ? 'Location could not be obtained. Try again or complete the data manually.'
        : 'No se pudo obtener la ubicaciÃ³n. Intenta nuevamente o completa los datos manualmente.',
    manualAddressInvalid: locale === 'en'
        ? 'Address not recognized. You can save it as is or choose the location on the map.'
        : 'DirecciÃ³n no reconocida. Puedes guardarla asÃ­ o seleccionar la ubicaciÃ³n en el mapa.',
    missingKey: locale === 'en'
        ? 'Google Maps is not configured. Add GOOGLE_MAPS_API_KEY in the environment file.'
        : 'Google Maps no estÃ¡ configurado. Agrega GOOGLE_MAPS_API_KEY en el archivo de entorno.',
};

const buildOption = (value, label) => {
    const option = document.createElement('option');
    option.value = value;
    option.textContent = label;
    return option;
};

const municipalityAliases = {
    CALI: 'SANTIAGO DE CALI',
};

const getMunicipalityLabel = (municipality) => {
    if (municipality === 'SANTIAGO DE CALI') {
        return 'CALI';
    }

    return municipality;
};

const populateDepartments = (select, selected) => {
    const departments = Object.keys(municipios).sort();
    departments.forEach((department) => {
        select.appendChild(buildOption(department, department));
    });
    if (selected) {
        select.value = selected;
    }
};

const populateMunicipalities = (select, department, selected) => {
    select.innerHTML = '';
    select.appendChild(buildOption('', t.select));
    if (!department || !municipios[department]) {
        return;
    }
    municipios[department].forEach((municipality) => {
        select.appendChild(buildOption(municipality, getMunicipalityLabel(municipality)));
    });
    if (selected) {
        if (!selectByNormalizedMatch(select, selected)) {
            select.value = selected;
        }
    }
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

const selectByNormalizedMatch = (select, value) => {
    if (!select || !value) {
        return false;
    }
    const target = normalizeText(value);
    if (!target) {
        return false;
    }
    const options = Array.from(select.options);
    const targetAlias = municipalityAliases[target.toUpperCase()] || null;
    const exactMatch = options.find((option) => {
        const optionValue = normalizeText(option.value);
        const optionLabel = normalizeText(option.textContent);

        return optionValue === target
            || optionLabel === target
            || (targetAlias && option.value === targetAlias);
    });
    if (exactMatch) {
        select.value = exactMatch.value;
        return true;
    }
    const simplified = target
        .replace(/\bmunicipio\b/g, '')
        .replace(/\bciudad\b/g, '')
        .replace(/\bcity\b/g, '')
        .replace(/\s+/g, ' ')
        .trim();
    if (simplified) {
        const partialMatch = options.find((option) => normalizeText(option.value) === simplified);
        if (partialMatch) {
            select.value = partialMatch.value;
            return true;
        }
    }
    const containsMatch = options.find((option) => {
        const candidate = normalizeText(option.value);
        return candidate && (target.includes(candidate) || candidate.includes(target));
    });
    if (containsMatch) {
        select.value = containsMatch.value;
        return true;
    }
    return false;
};

const normalizeDepartmentName = (value) => {
    const base = normalizeText(value)
        .replace(/\bdepartamento\b/g, '')
        .replace(/\bdistrito\b/g, '')
        .replace(/\bcapital\b/g, '')
        .replace(/\bd\.?\s*c\.?\b/g, '')
        .replace(/\bde\b/g, '')
        .replace(/\s+/g, ' ')
        .trim();

    if (!base) {
        return '';
    }
    if (base.includes('bogota')) {
        return 'BOGOTA D.C.';
    }
    if (base === 'cali' || base.endsWith(' cali') || base.includes('santiago de cali')) {
        return 'SANTIAGO DE CALI';
    }
    return base.toUpperCase();
};

const normalizeMunicipalityName = (value) => {
    const base = normalizeText(value)
        .replace(/\bmunicipio\b/g, '')
        .replace(/\bdistrito\b/g, '')
        .replace(/\bd\.?\s*c\.?\b/g, '')
        .replace(/\s+/g, ' ')
        .trim();

    if (!base) {
        return '';
    }
    if (base.includes('bogota')) {
        return 'BOGOTA D.C.';
    }
    return base.toUpperCase();
};

const initLocationSelects = () => {
    const departmentSelects = document.querySelectorAll('[data-department-select]');
    if (!departmentSelects.length) {
        return;
    }

    departmentSelects.forEach((departmentSelect) => {
        const form = departmentSelect.closest('[data-location-form]');
        if (!form) {
            return;
        }
        const municipalitySelect = form.querySelector('[data-municipality-select]');
        if (!municipalitySelect) {
            return;
        }

        const currentDepartment = departmentSelect.dataset.current || '';
        const currentMunicipality = municipalitySelect.dataset.current || '';

        departmentSelect.innerHTML = '';
        departmentSelect.appendChild(buildOption('', t.select));
        populateDepartments(departmentSelect, currentDepartment);
        populateMunicipalities(municipalitySelect, currentDepartment, currentMunicipality);

        departmentSelect.addEventListener('change', (event) => {
            populateMunicipalities(municipalitySelect, event.target.value, '');
        });
    });
};


const initMapPicker = () => {
    const triggers = document.querySelectorAll('[data-map-trigger]');
    if (!triggers.length) {
        return;
    }

    const modal = document.getElementById('location-map-modal');
    const closeButtons = modal ? modal.querySelectorAll('[data-map-close]') : [];
    const mapElement = modal ? modal.querySelector('#location-map') : null;
    const acceptButton = modal ? modal.querySelector('[data-map-accept]') : null;
    const errorMessage = modal ? modal.querySelector('[data-map-error]') : null;
    if (!modal || !mapElement || !mapElement.parentNode) {
        return;
    }

    const searchWrapper = document.createElement('div');
    searchWrapper.className = 'mt-2';
    searchWrapper.innerHTML = `
        <label for="location-map-search" class="mb-1 block text-sm font-medium text-gray-700">${t.searchByName}</label>
        <div class="relative">
            <input
                id="location-map-search"
                type="text"
                class="w-full rounded-md border border-gray-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-1 focus:ring-blue-500"
                placeholder="${t.searchByName}"
                autocomplete="off"
                data-map-search-input
            />
            <div class="absolute left-0 right-0 top-full z-20 mt-1 max-h-60 overflow-auto rounded-md border border-gray-200 bg-white shadow-lg hidden" data-map-search-results></div>
        </div>
    `;
    mapElement.parentNode.insertBefore(searchWrapper, mapElement);

    const searchInput = searchWrapper.querySelector('[data-map-search-input]');
    const searchResults = searchWrapper.querySelector('[data-map-search-results]');

    let mapInstance = null;
    let marker = null;
    let geocoder = null;
    let selectedLatLng = null;
    let activeForm = null;
    let searchDebounce = null;
    let searchToken = 0;
    let geocodeToken = 0;
    const geocodeDebounces = new WeakMap();

    const resolveInputs = (form = activeForm) => {
        if (!form) {
            return {};
        }
        return {
            latInput: form.querySelector('[data-latitude-input]'),
            lngInput: form.querySelector('[data-longitude-input]'),
            addressInput: form.querySelector('[data-address-input]'),
            neighborhoodInput: form.querySelector('[data-neighborhood-input]'),
            departmentSelect: form.querySelector('[data-department-select]'),
            municipalitySelect: form.querySelector('[data-municipality-select]'),
            coordsSourceInput: form.querySelector('[data-coords-source]'),
            notice: form.querySelector('[data-geocode-notice]'),
        };
    };

    const setNotice = (form, message = '') => {
        const { notice } = resolveInputs(form);
        if (!notice) {
            return;
        }
        if (!message) {
            notice.textContent = '';
            notice.classList.add('hidden');
            return;
        }
        notice.textContent = message;
        notice.classList.remove('hidden');
    };

    const setCoordinates = (form, lat, lng, source = 'geocode') => {
        const { latInput, lngInput, coordsSourceInput } = resolveInputs(form);
        if (latInput) {
            latInput.value = Number(lat).toFixed(6);
        }
        if (lngInput) {
            lngInput.value = Number(lng).toFixed(6);
        }
        if (coordsSourceInput) {
            coordsSourceInput.value = source;
        }
    };

    const clearCoordinates = (form, source = 'geocode') => {
        const { latInput, lngInput, coordsSourceInput } = resolveInputs(form);
        if (latInput) {
            latInput.value = '';
        }
        if (lngInput) {
            lngInput.value = '';
        }
        if (coordsSourceInput) {
            coordsSourceInput.value = source;
        }
    };

    const collectLocationData = (form) => {
        const { addressInput, neighborhoodInput, municipalitySelect, departmentSelect } = resolveInputs(form);

        return {
            address: addressInput?.value?.trim() || '',
            neighborhood: neighborhoodInput?.value?.trim() || '',
            city: municipalitySelect?.value?.trim() || '',
            department: departmentSelect?.value?.trim() || '',
        };
    };

    const hasEnoughDataToGeocode = (data) => data.address.length >= 5 && data.city !== '' && data.department !== '';

    const ensureGeocoder = async () => {
        if (geocoder) {
            return geocoder;
        }
        await whenGoogleMapsReady();
        geocoder = new google.maps.Geocoder();
        return geocoder;
    };

    const geocodeAddress = async (query) => {
        const coder = await ensureGeocoder();

        return new Promise((resolve, reject) => {
            coder.geocode({
                address: query,
                componentRestrictions: { country: 'CO' },
                region: 'co',
            }, (results, status) => {
                if (status !== 'OK' || !Array.isArray(results) || results.length === 0) {
                    reject(new Error(status || 'geocode-failed'));
                    return;
                }
                resolve(results);
            });
        });
    };

    const geocodeManualLocation = async (form) => {
        const data = collectLocationData(form);
        if (!hasEnoughDataToGeocode(data)) {
            setNotice(form, '');
            return;
        }

        const token = ++geocodeToken;
        const query = [data.address, data.neighborhood, data.city, data.department, 'Colombia']
            .filter(Boolean)
            .join(', ');

        try {
            await ensureGeocoder();
            const results = await geocodeAddress(query);
            if (token !== geocodeToken) {
                return;
            }
            const location = results[0]?.geometry?.location;
            if (!location) {
                clearCoordinates(form);
                setNotice(form, t.manualAddressInvalid);
                return;
            }
            setCoordinates(form, location.lat(), location.lng(), 'geocode');
            setNotice(form, '');
        } catch (error) {
            if (token !== geocodeToken) {
                return;
            }
            clearCoordinates(form);
            setNotice(form, t.manualAddressInvalid);
        }
    };

    const scheduleManualGeocode = (form) => {
        const previousTimer = geocodeDebounces.get(form);
        if (previousTimer) {
            clearTimeout(previousTimer);
        }
        const timer = setTimeout(() => {
            geocodeManualLocation(form);
        }, 650);
        geocodeDebounces.set(form, timer);
    };

    const hideSearchResults = () => {
        searchResults.classList.add('hidden');
        searchResults.innerHTML = '';
    };

    const setSelectedLocation = (lat, lng, zoom = 14) => {
        if (!mapInstance) {
            return;
        }
        const position = { lat, lng };
        if (marker) {
            marker.setPosition(position);
        } else {
            marker = new google.maps.Marker({
                position,
                map: mapInstance,
                icon: locationMarkerIcon(),
            });
        }
        selectedLatLng = position;
        mapInstance.setCenter(position);
        mapInstance.setZoom(zoom);
        setCoordinates(activeForm, lat, lng, 'map');
        setNotice(activeForm, '');
        if (acceptButton) {
            acceptButton.disabled = false;
        }
    };

    const guessZoomLevel = (types) => {
        const list = types || [];
        if (list.includes('administrative_area_level_1')) {
            return 8;
        }
        if (list.includes('locality') || list.includes('administrative_area_level_2')) {
            return 11;
        }
        return 14;
    };

    const buildResultRows = (items) => {
        if (!items.length) {
            searchResults.innerHTML = `<div class="px-3 py-2 text-sm text-gray-500">${t.noResults}</div>`;
            searchResults.classList.remove('hidden');
            return;
        }

        searchResults.innerHTML = items
            .map((item, index) => `
                <button
                    type="button"
                    class="block w-full border-b border-gray-100 px-3 py-2 text-left text-sm text-gray-700 hover:bg-blue-50 focus:bg-blue-50 focus:outline-none"
                    data-map-result-index="${index}"
                >
                    <span class="block font-medium">${item.formatted_address || ''}</span>
                </button>
            `)
            .join('');
        searchResults.classList.remove('hidden');

        Array.from(searchResults.querySelectorAll('[data-map-result-index]')).forEach((button) => {
            button.addEventListener('click', () => {
                const index = Number.parseInt(button.dataset.mapResultIndex || '', 10);
                const item = items[index];
                const location = item?.geometry?.location;
                if (!location) {
                    return;
                }
                setSelectedLocation(location.lat(), location.lng(), guessZoomLevel(item.types));
                searchInput.value = item.formatted_address || '';
                hideSearchResults();
            });
        });
    };

    const searchByText = (query) => {
        const text = query.trim();
        const token = ++searchToken;
        if (text.length < 2) {
            hideSearchResults();
            return;
        }

        geocodeAddress(`${text}, Colombia`)
            .then((results) => {
                if (token !== searchToken) {
                    return;
                }
                buildResultRows(results.slice(0, 8));
            })
            .catch(() => {
                if (token !== searchToken) {
                    return;
                }
                hideSearchResults();
            });
    };

    const ensureMap = async () => {
        if (mapInstance) {
            return;
        }
        await ensureGeocoder();
        mapInstance = new google.maps.Map(mapElement, {
            center: { lat: 4.5709, lng: -74.2973 },
            zoom: 6,
            mapTypeId: 'hybrid',
            mapTypeControl: true,
            streetViewControl: false,
            fullscreenControl: true,
        });
        mapInstance.addListener('click', (event) => {
            setSelectedLocation(event.latLng.lat(), event.latLng.lng(), 14);
        });
    };

    const openModal = async (form) => {
        activeForm = form;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        selectedLatLng = null;
        if (acceptButton) {
            acceptButton.disabled = true;
        }
        if (errorMessage) {
            errorMessage.textContent = '';
            errorMessage.classList.add('hidden');
        }
        searchInput.value = '';
        hideSearchResults();
        setNotice(activeForm, '');

        try {
            await ensureMap();
        } catch (error) {
            if (errorMessage) {
                errorMessage.textContent = error?.message === 'missing-google-maps-key' ? t.missingKey : t.geocodeFailed;
                errorMessage.classList.remove('hidden');
            }
            return;
        }

        const { latInput, lngInput } = resolveInputs();
        if (latInput?.value && lngInput?.value) {
            const lat = Number.parseFloat(latInput.value);
            const lng = Number.parseFloat(lngInput.value);
            if (!Number.isNaN(lat) && !Number.isNaN(lng)) {
                setSelectedLocation(lat, lng, 14);
            }
        }

        window.setTimeout(() => {
            google.maps.event.trigger(mapInstance, 'resize');
        }, 200);
    };

    const closeModal = () => {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
        hideSearchResults();
    };

    const reverseGeocode = (lat, lng) => new Promise((resolve, reject) => {
        if (!geocoder) {
            reject(new Error('geocoder-unavailable'));
            return;
        }
        geocoder.geocode({ location: { lat, lng } }, (results, status) => {
            if (status !== 'OK' || !results?.[0]) {
                reject(new Error(status || 'reverse-failed'));
                return;
            }
            resolve(results[0]);
        });
    });

    triggers.forEach((trigger) => {
        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            openModal(trigger.closest('[data-location-form]'));
        });
    });

    searchInput.addEventListener('input', (event) => {
        const query = event.target.value || '';
        if (searchDebounce) {
            clearTimeout(searchDebounce);
        }
        searchDebounce = setTimeout(() => {
            searchByText(query);
        }, 300);
    });

    searchInput.addEventListener('keydown', (event) => {
        if (event.key !== 'Enter') {
            return;
        }
        const firstResult = searchResults.querySelector('[data-map-result-index="0"]');
        if (!firstResult) {
            return;
        }
        event.preventDefault();
        firstResult.click();
    });

    const shouldHandleLocationField = (field) => field.matches('[data-address-input], [data-neighborhood-input], [data-department-select], [data-municipality-select]');

    const handleLocationFieldChange = (field) => {
        const form = field.closest('[data-location-form]');
        if (!form || !shouldHandleLocationField(field)) {
            return;
        }

        const { coordsSourceInput } = resolveInputs(form);
        const isMapSelection = coordsSourceInput?.value === 'map';
        const isOnlyTextAdjustment = field.matches('[data-address-input], [data-neighborhood-input]');

        if (isMapSelection && isOnlyTextAdjustment) {
            setNotice(form, '');
            return;
        }

        clearCoordinates(form);
        setNotice(form, '');
        scheduleManualGeocode(form);
    };

    document.addEventListener('input', (event) => {
        handleLocationFieldChange(event.target);
    });

    document.addEventListener('change', (event) => {
        handleLocationFieldChange(event.target);
    });

    closeButtons.forEach((button) => {
        button.addEventListener('click', (event) => {
            event.preventDefault();
            closeModal();
        });
    });

    if (acceptButton) {
        acceptButton.addEventListener('click', async (event) => {
            event.preventDefault();
            if (!selectedLatLng) {
                return;
            }

            const { addressInput, neighborhoodInput, departmentSelect, municipalitySelect, coordsSourceInput } = resolveInputs();
            const originalText = acceptButton.textContent;
            acceptButton.textContent = t.searching;
            acceptButton.disabled = true;
            if (errorMessage) {
                errorMessage.textContent = '';
                errorMessage.classList.add('hidden');
            }

            try {
                const result = await reverseGeocode(selectedLatLng.lat, selectedLatLng.lng);
                const components = result.address_components || [];
                const route = addressComponent(components, 'route');
                const number = addressComponent(components, 'street_number');
                const addressValue = [route, number].filter(Boolean).join(' ') || result.formatted_address || '';
                const municipality = addressComponent(components, 'locality')
                    || addressComponent(components, 'administrative_area_level_2');
                const neighborhood = addressComponent(components, 'neighborhood')
                    || addressComponent(components, 'sublocality_level_1')
                    || addressComponent(components, 'sublocality');
                const department = addressComponent(components, 'administrative_area_level_1');
                const normalizedDepartment = normalizeDepartmentName(department);
                const normalizedMunicipality = normalizeMunicipalityName(municipality);

                if (addressInput && addressValue) {
                    addressInput.value = addressValue;
                }
                if (neighborhoodInput && neighborhood) {
                    neighborhoodInput.value = neighborhood;
                }
                if (departmentSelect && normalizedDepartment) {
                    if (!selectByNormalizedMatch(departmentSelect, normalizedDepartment)) {
                        departmentSelect.appendChild(buildOption(normalizedDepartment, normalizedDepartment));
                        departmentSelect.value = normalizedDepartment;
                    }
                    populateMunicipalities(municipalitySelect, departmentSelect.value || normalizedDepartment, '');
                }
                if (municipalitySelect && normalizedMunicipality) {
                    if (!selectByNormalizedMatch(municipalitySelect, normalizedMunicipality)) {
                        municipalitySelect.appendChild(buildOption(normalizedMunicipality, normalizedMunicipality));
                        municipalitySelect.value = normalizedMunicipality;
                    }
                }
                if (coordsSourceInput) {
                    coordsSourceInput.value = 'map';
                }
                setNotice(activeForm, '');

                if (!addressValue || !normalizedDepartment || !normalizedMunicipality) {
                    if (errorMessage) {
                        errorMessage.textContent = t.incompleteAddress;
                        errorMessage.classList.remove('hidden');
                    }
                } else {
                    closeModal();
                }
            } catch (error) {
                if (errorMessage) {
                    errorMessage.textContent = t.geocodeFailed;
                    errorMessage.classList.remove('hidden');
                }
            } finally {
                acceptButton.textContent = originalText;
                acceptButton.disabled = false;
            }
        });
    }

    modal.addEventListener('click', (event) => {
        if (event.target === modal) {
            closeModal();
            return;
        }
        if (!searchWrapper.contains(event.target)) {
            hideSearchResults();
        }
    });
};

const init = () => {
    initLocationSelects();
    initMapPicker();
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
} else {
    init();
}
