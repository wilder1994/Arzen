const KEY = 'arzen-theme';

export function currentTheme() {
    return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
}

export function setTheme(theme) {
    const next = theme === 'dark' ? 'dark' : 'light';
    document.documentElement.setAttribute('data-theme', next);

    try {
        localStorage.setItem(KEY, next);
    } catch {
        // El tema sigue aplicado en esta visita.
    }

    const meta = document.querySelector('meta[name="theme-color"]');
    if (meta) {
        meta.setAttribute('content', next === 'dark' ? '#071216' : '#e7eeec');
    }
}

export function toggleTheme() {
    setTheme(currentTheme() === 'dark' ? 'light' : 'dark');
}

window.ArzenTheme = {
    current: currentTheme,
    set: setTheme,
    toggle: toggleTheme,
};
