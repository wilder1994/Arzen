@php
    $moduleNav = $moduleNav ?? ['home_url' => url('/'), 'active_module' => '', 'modules' => []];
    $isAlmacenUser = Auth::user()?->isAlmacen();
@endphp

<div
    x-data="{
        mobileOpen: false,
        sidebarHidden: localStorage.getItem('sj-sidebar-hidden') === '1',
        isDesktop: window.matchMedia('(min-width: 1024px)').matches,
        notificationsOpen: false,
        notificationHistoryMode: false,
        notificationItems: [],
        notificationLoading: false,
        notificationUnread: {{ (int) ($unreadNotificationCount ?? 0) }},
        get sidebarCollapsed() {
            return this.isDesktop ? this.sidebarHidden : !this.mobileOpen;
        },
        init() {
            window.addEventListener('inbox-updated', (e) => {
                const n = e.detail?.unread_count;
                if (typeof n === 'number') {
                    this.notificationUnread = n;
                    if (this.notificationsOpen) {
                        this.loadNotifications();
                    }
                }
            });
            this._mq = window.matchMedia('(min-width: 1024px)');
            this._onMq = () => {
                this.isDesktop = this._mq.matches;
                if (this._mq.matches) {
                    this.mobileOpen = false;
                }
            };
            this._mq.addEventListener('change', this._onMq);
            this.$watch('mobileOpen', (open) => {
                document.body.classList.toggle('sj-sidebar-lock', open);
            });
            this.$watch('sidebarHidden', (hidden) => {
                document.body.classList.toggle('sj-sidebar-hidden', hidden);
            });
            document.body.classList.toggle('sj-sidebar-hidden', this.sidebarHidden);
        },
        toggleSidebar() {
            if (this.isDesktop) {
                this.sidebarHidden = !this.sidebarHidden;
                localStorage.setItem('sj-sidebar-hidden', this.sidebarHidden ? '1' : '0');
                return;
            }
            this.mobileOpen = !this.mobileOpen;
        },
        notificationsIndexUrl(history) {
            const base = '{{ url('/notifications') }}';
            return history ? base + '?history=1' : base;
        },
        async loadNotifications() {
            this.notificationLoading = true;
            try {
                const r = await fetch(this.notificationsIndexUrl(this.notificationHistoryMode), {
                    headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                if (!r.ok) { return; }
                const d = await r.json();
                this.notificationItems = d.notifications || [];
                this.notificationUnread = d.unread_count ?? 0;
            } finally {
                this.notificationLoading = false;
            }
        },
        async openNotificationsModal() {
            this.notificationHistoryMode = false;
            this.notificationsOpen = true;
            await this.loadNotifications();
        },
        async openNotificationsHistory() {
            this.notificationHistoryMode = true;
            this.notificationsOpen = true;
            await this.loadNotifications();
        },
        async markAllNotificationsRead() {
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
            const r = await fetch('{{ route('notifications.read-all') }}', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
            });
            if (!r.ok) { return; }
            this.notificationUnread = 0;
            if (this.notificationHistoryMode) {
                this.notificationItems = this.notificationItems.map(i => ({ ...i, read: true }));
            } else {
                this.notificationItems = [];
            }
        },
        async markOneRead(id, url) {
            const token = document.querySelector('meta[name=csrf-token]')?.getAttribute('content') || '';
            const r = await fetch('{{ url('/notifications') }}/' + id + '/read', {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': token,
                },
            });
            if (r.ok) {
                const d = await r.json();
                this.notificationUnread = d.unread_count ?? 0;
                if (this.notificationHistoryMode) {
                    this.notificationItems = this.notificationItems.map(i => i.id === id ? { ...i, read: true } : i);
                } else {
                    await this.loadNotifications();
                }
            }
            if (url) {
                window.location.href = url;
            }
        },
    }"
    class="sj-nav"
    :class="{ 'is-mobile-open': mobileOpen, 'is-sidebar-hidden': sidebarHidden }"
>
    <div
        class="sj-sidebar-backdrop"
        x-show="mobileOpen"
        x-cloak
        @click="mobileOpen = false"
    ></div>

    @include('layouts.partials.sidebar')

    <button
        type="button"
        class="sj-sidebar-edge-toggle"
        @click="toggleSidebar()"
        :class="{ 'is-collapsed': sidebarCollapsed }"
        :aria-expanded="(!sidebarCollapsed).toString()"
        :aria-label="sidebarCollapsed ? '{{ __('Mostrar módulos') }}' : '{{ __('Ocultar módulos') }}'"
        :title="sidebarCollapsed ? '{{ __('Mostrar módulos') }}' : '{{ __('Ocultar módulos') }}'"
    >
        <svg class="sj-sidebar-edge-toggle__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
            <path fill-rule="evenodd" d="M12.707 5.293a1 1 0 010 1.414L9.414 10l3.293 3.293a1 1 0 01-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z" clip-rule="evenodd" />
        </svg>
    </button>

    <header class="sj-topbar">
        <div class="sj-topbar__left">
            @include('layouts.partials.module-tabs')
        </div>

        <div class="sj-nav-user flex items-center gap-2 shrink-0">
            @include('partials.theme-toggle')
            @if (($notificationBellEnabled ?? false) && ! $isAlmacenUser)
                <button
                    type="button"
                    @click="openNotificationsModal()"
                    class="sj-nav-notify-btn relative inline-flex items-center justify-center rounded p-1 focus-visible:ring-2 focus-visible:ring-amber-400/60"
                    aria-label="{{ __('Notificaciones') }}"
                >
                    <svg class="h-6 w-6 shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.857 17.082a23.848 23.848 0 005.454-1.31A8.967 8.967 0 0118 9.75v-.7V9A6 6 0 006 9v.75a8.967 8.967 0 01-2.312 6.022c1.733.64 3.56 1.085 5.455 1.31m5.714 0a24.255 24.255 0 01-5.714 0m5.714 0a3 3 0 11-5.714 0" />
                    </svg>
                    <span
                        x-show="notificationUnread > 0"
                        x-text="notificationUnread > 99 ? '99+' : notificationUnread"
                        class="sj-nav-notify-badge absolute -right-1 -top-1 flex h-5 min-w-[1.25rem] items-center justify-center rounded-full bg-red-600 px-1 text-[10px] font-bold text-white shadow-sm ring-1 ring-white/30"
                    ></span>
                </button>
            @endif
            <x-dropdown align="right" width="48">
                <x-slot name="trigger">
                    <button class="sj-nav-user-button inline-flex items-center border text-sm leading-4 font-medium rounded-md text-slate-100 hover:text-white focus:outline-none transition ease-in-out duration-150">
                        <div>{{ Auth::user()->name }}</div>
                        <div class="ms-1">
                            <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </div>
                    </button>
                </x-slot>
                <x-slot name="content">
                    <form method="POST" action="{{ route('locale.switch') }}" class="px-4 py-2">
                        @csrf
                        <label for="locale-select" class="mb-1 block text-xs text-gray-700">{{ __('Idioma') }}</label>
                        <select id="locale-select" name="locale" class="block w-full rounded border-gray-300 text-sm text-gray-900" onchange="this.form.submit()">
                            <option value="es" @selected(app()->getLocale() === 'es')>Espa&ntilde;ol</option>
                            <option value="en" @selected(app()->getLocale() === 'en')>Ingl&eacute;s</option>
                        </select>
                    </form>
                    @if (($notificationBellEnabled ?? false) && ! $isAlmacenUser)
                        <button
                            type="button"
                            class="block w-full px-4 py-2 text-start text-sm leading-5 text-gray-900 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 transition duration-150 ease-in-out sj-dropdown-link border-0 bg-white w-full"
                            @click="openNotificationsHistory()"
                        >
                            {{ __('Historial de notificaciones') }}
                        </button>
                    @endif
                    <x-dropdown-link :href="route('profile.edit')">
                        {{ __('Perfil') }}
                    </x-dropdown-link>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <x-dropdown-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                            Cerrar sesi&oacute;n
                        </x-dropdown-link>
                    </form>
                </x-slot>
            </x-dropdown>
        </div>
    </header>

    @if (($notificationBellEnabled ?? false) && ! $isAlmacenUser)
        <div
            x-show="notificationsOpen"
            x-cloak
            class="fixed inset-0 z-[5000] flex items-center justify-center bg-black/50 p-4"
            @keydown.escape.window="notificationsOpen = false"
        >
            <div
                class="flex max-h-[85vh] w-full max-w-md flex-col overflow-hidden rounded-lg bg-white shadow-xl"
                @click.outside="notificationsOpen = false"
            >
                <div class="flex shrink-0 items-center justify-between border-b border-gray-200 px-4 py-3">
                    <h2 class="text-base font-semibold text-gray-900" x-text="notificationHistoryMode ? '{{ __('Historial de notificaciones') }}' : '{{ __('Notificaciones') }}'"></h2>
                    <div class="flex items-center gap-2">
                        <button
                            type="button"
                            @click="markAllNotificationsRead()"
                            class="text-xs font-medium text-indigo-600 hover:text-indigo-800"
                            x-show="notificationUnread > 0"
                        >
                            {{ __('Marcar todas leídas') }}
                        </button>
                        <button type="button" class="text-2xl leading-none text-gray-500 hover:text-gray-800" @click="notificationsOpen = false" aria-label="{{ __('Cerrar') }}">&times;</button>
                    </div>
                </div>
                <div class="min-h-0 flex-1 overflow-y-auto">
                    <div x-show="notificationLoading" class="p-6 text-center text-sm text-gray-500">{{ __('Cargando…') }}</div>
                    <div x-show="!notificationLoading && notificationItems.length === 0 && !notificationHistoryMode" class="p-6 text-center text-sm text-gray-500">{{ __('No tienes notificaciones sin leer.') }}</div>
                    <div x-show="!notificationLoading && notificationItems.length === 0 && notificationHistoryMode" class="p-6 text-center text-sm text-gray-500">{{ __('No hay notificaciones en el historial.') }}</div>
                    <div x-show="!notificationLoading && notificationItems.length > 0" class="divide-y divide-gray-100">
                        <template x-for="item in notificationItems" :key="item.id">
                            <div>
                                <button
                                    type="button"
                                    class="flex w-full flex-col items-start gap-0.5 px-4 py-3 text-left text-sm hover:bg-gray-50"
                                    :class="!item.read ? 'bg-indigo-50/80' : ''"
                                    @click="markOneRead(item.id, item.url || null)"
                                >
                                    <span class="font-medium text-gray-900" x-text="item.title"></span>
                                    <span class="text-gray-600" x-text="item.body"></span>
                                    <span class="text-xs text-gray-400" x-text="item.created_at ? new Date(item.created_at).toLocaleString() : ''"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
