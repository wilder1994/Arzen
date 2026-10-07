@php
    $moduleNav = $moduleNav ?? ['home_url' => url('/'), 'active_module' => '', 'modules' => [], 'tabs' => []];
@endphp

<aside class="sj-sidebar" :class="{ 'is-open': mobileOpen }" aria-label="{{ __('Módulos') }}">
    <div class="sj-sidebar__brand">
        <a href="{{ $moduleNav['home_url'] }}" class="sj-sidebar__brand-link">
            <x-application-logo class="sj-sidebar__logo-mark" />
        </a>
    </div>

    <nav class="sj-sidebar__nav">
        <p class="sj-sidebar__module-title">{{ __('Módulos') }}</p>
        <ul class="sj-sidebar__list">
            @foreach ($moduleNav['modules'] as $module)
                <li class="sj-sidebar__node">
                    @if ($module['entry_url'])
                        <a
                            href="{{ $module['entry_url'] }}"
                            @class(['sj-sidebar__link sj-sidebar__link--module', 'is-active' => $module['active']])
                            @if ($module['active']) aria-current="page" @endif
                            @click="mobileOpen = false"
                        >
                            <span class="sj-sidebar__label">{{ $module['label'] }}</span>
                        </a>
                    @else
                        <span
                            class="sj-sidebar__link sj-sidebar__link--module is-disabled"
                            title="{{ __('Próximamente') }}"
                        >
                            <span class="sj-sidebar__label">{{ $module['label'] }}</span>
                            <span class="sj-sidebar__badge">{{ __('Próximamente') }}</span>
                        </span>
                    @endif
                </li>
            @endforeach
        </ul>
    </nav>

    @php
        $sidebarLocales = ['es' => __('Español'), 'en' => __('Inglés')];
        $sidebarLocale = app()->getLocale();
    @endphp
    <div class="sj-sidebar__footer">
        <div class="sj-sidebar__popover-anchor" x-data="{ open: false }" @click.outside="open = false" @keydown.escape.window="open = false">
            <form x-show="open" x-cloak x-transition.opacity method="POST" action="{{ route('locale.switch') }}" class="sj-sidebar__popover">
                @csrf
                @foreach ($sidebarLocales as $code => $label)
                    <button
                        type="submit"
                        name="locale"
                        value="{{ $code }}"
                        @class(['sj-sidebar__popover-option', 'is-active' => $sidebarLocale === $code])
                    >{{ $label }}</button>
                @endforeach
            </form>
            <button type="button" class="sj-sidebar__link sj-sidebar__account-link" @click="open = !open" :aria-expanded="open.toString()">
                <svg class="sj-sidebar__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 008.716-6.747M12 21a9.004 9.004 0 01-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 017.843 4.582M12 3a8.997 8.997 0 00-7.843 4.582m15.686 0A11.953 11.953 0 0112 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0121 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0112 16.5c-3.162 0-6.133-.815-8.716-2.247m0 0A9.015 9.015 0 013 12c0-1.605.42-3.113 1.157-4.418" />
                </svg>
                <span class="sj-sidebar__label">{{ __('Idioma') }}</span>
                <span class="sj-sidebar__value">{{ $sidebarLocales[$sidebarLocale] ?? $sidebarLocale }}</span>
            </button>
        </div>

        @if (($notificationBellEnabled ?? false) && ! ($isAlmacenUser ?? false))
            <button type="button" class="sj-sidebar__link sj-sidebar__account-link" @click="mobileOpen = false; openNotificationsHistory()">
                <svg class="sj-sidebar__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <span class="sj-sidebar__label">{{ __('Historial de notificaciones') }}</span>
            </button>
        @endif

        <a
            href="{{ route('profile.edit') }}"
            @class(['sj-sidebar__user-button', 'is-active' => request()->routeIs('profile.*')])
            title="{{ __('Perfil') }}"
            @click="mobileOpen = false"
        >
            <span class="sj-sidebar__avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr(Auth::user()->name, 0, 1)) }}</span>
            <span class="sj-sidebar__user-name">{{ Auth::user()->name }}</span>
        </a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="sj-sidebar__link sj-sidebar__account-link sj-sidebar__logout">
                <svg class="sj-sidebar__icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15M12 9l-3 3m0 0l3 3m-3-3h12.75" />
                </svg>
                <span class="sj-sidebar__label">{{ __('Cerrar sesión') }}</span>
            </button>
        </form>
    </div>
</aside>
