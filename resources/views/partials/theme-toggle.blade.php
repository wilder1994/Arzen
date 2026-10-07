@php($themeToggleClass = $themeToggleClass ?? 'sj-theme-toggle')
<button type="button" class="{{ $themeToggleClass }}" onclick="window.ArzenTheme && window.ArzenTheme.toggle()" title="{{ __('Tema claro u oscuro') }}" aria-label="{{ __('Tema claro u oscuro') }}">
    <svg class="sj-theme-toggle__moon h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 14.5A8.5 8.5 0 1 1 9.5 3 7 7 0 0 0 21 14.5z"/>
    </svg>
    <svg class="sj-theme-toggle__sun h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <circle cx="12" cy="12" r="4"/>
        <path stroke-linecap="round" d="M12 3v2M12 19v2M3 12h2M19 12h2M5.6 5.6l1.4 1.4M17 17l1.4 1.4M18.4 5.6 17 7M7 17l-1.4 1.4"/>
    </svg>
</button>
