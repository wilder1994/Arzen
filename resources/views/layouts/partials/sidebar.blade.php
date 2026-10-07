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
</aside>
