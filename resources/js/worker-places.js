import municipios from '../data/colombia_municipios.json';

const departments = Object.keys(municipios).sort((left, right) => left.localeCompare(right, 'es'));

function same(left, right) {
    return left.trim().toLocaleLowerCase('es') === right.trim().toLocaleLowerCase('es');
}

function fillSelect(select, values, selected, placeholder) {
    const chosen = (selected || '').trim();
    const options = [...values];

    if (chosen && !options.some((value) => same(value, chosen))) {
        options.push(chosen);
    }

    select.replaceChildren();

    const empty = document.createElement('option');
    empty.value = '';
    empty.textContent = placeholder;
    select.append(empty);

    options.forEach((value) => {
        const option = document.createElement('option');
        option.value = value;
        option.textContent = value;
        option.selected = chosen !== '' && same(value, chosen);
        select.append(option);
    });
}

export function initWorkerPlaces(root) {
    root.querySelectorAll('[data-place-pair]').forEach((pair) => {
        const department = pair.querySelector('[data-place-department]');
        const city = pair.querySelector('[data-place-city]');

        if (!department || !city) {
            return;
        }

        const selectedDepartment = department.dataset.selected || '';
        const selectedCity = city.dataset.selected || '';

        fillSelect(department, departments, selectedDepartment, 'Seleccione');

        const citiesFor = () => {
            const key = departments.find((name) => same(name, department.value));

            return key ? municipios[key] : [];
        };

        const refreshCities = (selected) => {
            fillSelect(city, citiesFor(), selected, 'Seleccione');
        };

        department.addEventListener('change', () => refreshCities(''));
        refreshCities(selectedCity);
    });
}
