/**
 * Zonas de carga [data-file-drop]: clic, arrastrar y soltar o pegar (Ctrl+V) sobre el mismo recuadro.
 * Sirve para imágenes (vista previa) y documentos (muestra el nombre del archivo).
 */
let activeDrop = null;
let pasteListenerReady = false;

const acceptsFile = (input, file) => {
    const rules = (input.accept || '')
        .split(',')
        .map((rule) => rule.trim().toLowerCase())
        .filter(Boolean);

    if (rules.length === 0) {
        return true;
    }

    const name = (file.name || '').toLowerCase();
    const type = (file.type || '').toLowerCase();

    return rules.some((rule) => {
        if (rule.startsWith('.')) {
            return name.endsWith(rule);
        }

        if (rule.endsWith('/*')) {
            return type.startsWith(rule.slice(0, -1));
        }

        return type === rule;
    });
};

const showFile = (drop, file) => {
    const current = drop.querySelector('[data-file-drop-current]');
    const image = drop.querySelector('[data-file-drop-image]');
    const selected = drop.querySelector('[data-file-drop-selected]');
    const nameTarget = drop.querySelector('[data-file-drop-name]');
    const remove = drop.querySelector('[data-file-drop-remove]');
    const metaName = drop.querySelector('.sj-file-drop__meta-name');

    if (remove) {
        remove.checked = false;
    }

    if (metaName) {
        metaName.textContent = (drop.dataset.pendingLabel || ':name').replace(':name', file.name);
        metaName.title = file.name;
    }

    if (drop.dataset.kind === 'image' && image) {
        if (image.dataset.objectUrl) {
            URL.revokeObjectURL(image.dataset.objectUrl);
        }

        const objectUrl = URL.createObjectURL(file);
        image.dataset.objectUrl = objectUrl;
        image.src = objectUrl;
        image.classList.remove('hidden');
        current?.classList.add('hidden');

        return;
    }

    current?.classList.add('hidden');
    selected?.classList.remove('hidden');

    if (nameTarget) {
        nameTarget.textContent = file.name;
        nameTarget.title = file.name;
    }
};

const useFile = (drop, file) => {
    const input = drop.querySelector('[data-file-drop-input]');

    if (!input || !file) {
        return;
    }

    if (!acceptsFile(input, file)) {
        window.alert(drop.dataset.invalidMessage);

        return;
    }

    const transfer = new DataTransfer();
    transfer.items.add(file);
    input.files = transfer.files;
    showFile(drop, file);
};

const setupDrop = (drop) => {
    if (drop.dataset.fileDropReady === '1') {
        return;
    }

    drop.dataset.fileDropReady = '1';

    const zone = drop.querySelector('[data-file-drop-zone]');
    const input = drop.querySelector('[data-file-drop-input]');

    if (!zone || !input) {
        return;
    }

    zone.addEventListener('click', (event) => {
        if (event.target === input) {
            return;
        }

        input.click();
    });

    zone.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            input.click();
        }
    });

    input.addEventListener('change', () => {
        const file = input.files?.[0];

        if (!file) {
            return;
        }

        if (!acceptsFile(input, file)) {
            input.value = '';
            window.alert(drop.dataset.invalidMessage);

            return;
        }

        showFile(drop, file);
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
        zone.addEventListener(eventName, (event) => {
            event.preventDefault();
            zone.classList.add('is-dragover');
        });
    });

    ['dragleave', 'dragend'].forEach((eventName) => {
        zone.addEventListener(eventName, (event) => {
            if (!zone.contains(event.relatedTarget)) {
                zone.classList.remove('is-dragover');
            }
        });
    });

    zone.addEventListener('drop', (event) => {
        event.preventDefault();
        zone.classList.remove('is-dragover');
        useFile(drop, event.dataTransfer?.files?.[0]);
    });

    zone.addEventListener('mouseenter', () => {
        activeDrop = drop;
    });

    zone.addEventListener('focus', () => {
        activeDrop = drop;
    });

    zone.addEventListener('mouseleave', () => {
        if (activeDrop === drop && document.activeElement !== zone) {
            activeDrop = null;
        }
    });

    zone.addEventListener('blur', () => {
        if (activeDrop === drop && !zone.matches(':hover')) {
            activeDrop = null;
        }
    });
};

export function initFileDrops(root = document) {
    root.querySelectorAll('[data-file-drop]').forEach(setupDrop);

    if (pasteListenerReady) {
        return;
    }

    pasteListenerReady = true;

    document.addEventListener('paste', (event) => {
        if (!activeDrop || !document.body.contains(activeDrop)) {
            return;
        }

        const file = Array.from(event.clipboardData?.files || [])[0];

        if (!file) {
            return;
        }

        event.preventDefault();
        useFile(activeDrop, file);
    });
}
