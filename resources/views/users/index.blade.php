<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Usuarios') }}</h2>
            </div>

            <div class="sj-section-header__actions">
                <a href="{{ route('users.create') }}" class="sj-ui-btn sj-ui-btn--primary">
                    {{ __('Nuevo usuario') }}
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Delimitar x-data con comillas simples: @json() añade " y rompe un atributo x-data="..." --}}
    <div class="py-8" x-data='{
        showClientsModal: false,
        modalUserName: "",
        modalClients: [],
        modalSeesAll: false,
        openClientsModal(name, clients, seesAll) {
            this.modalUserName = name;
            this.modalClients = clients;
            this.modalSeesAll = !!seesAll;
            this.showClientsModal = true;
        }
    }'>
        <div class="sj-page-shell sj-page-shell--wide space-y-6">
            @if ($errors->has('email'))
                <div class="rounded border border-red-200 bg-red-50 p-3 text-sm text-red-800">
                    {{ $errors->first('email') }}
                </div>
            @endif
            @if (session('generated_temporary_password'))
                <div
                    class="rounded border border-amber-200 bg-amber-50 p-4 text-sm text-amber-950 space-y-3"
                    x-data="{ copied: false }"
                >
                    @if (session('status'))
                        <p class="font-medium">{{ session('status') }}</p>
                    @endif
                    <p>{{ __('Contraseña temporal (cópiela ahora; no se volverá a mostrar)') }}</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <code id="sj-temp-user-password" class="flex-1 min-w-[12rem] rounded bg-white px-3 py-2 text-xs tracking-wide border border-amber-100 break-all">
                            {{ session('generated_temporary_password') }}
                        </code>
                        <button
                            type="button"
                            class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm"
                            @click="
                                const el = document.getElementById('sj-temp-user-password');
                                if (el) { navigator.clipboard.writeText(el.textContent.trim()); copied = true; setTimeout(() => copied = false, 2000); }
                            "
                        >
                            <span x-text='copied ? @json(__("Copiado")) : @json(__("Copiar"))'></span>
                        </button>
                    </div>
                </div>
            @elseif (session('status'))
                <div class="rounded bg-green-50 p-3 text-sm text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="sj-ui-card overflow-hidden">
                <div class="sj-ui-card__body p-4">
                    <div class="sj-table-wrap">
                        <table class="sj-table sj-table--align-left sj-table--compact sj-user-list">
                            <thead>
                                <tr>
                                    <th>{{ __('Nombre') }}</th>
                                    <th>{{ __('Correo') }}</th>
                                    <th>{{ __('Rol') }}</th>
                                    <th>{{ __('Cargo') }}</th>
                                    <th class="sj-user-list__clients">{{ __('Clientes') }}</th>
                                    <th class="sj-user-list__state">{{ __('Estado') }}</th>
                                    <th>{{ __('Acciones') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($users as $user)
                                    <tr>
                                        <td>{{ $user->name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $roles[$user->role] ?? $user->role }}</td>
                                        <td>{{ $user->position?->name ?? '—' }}</td>
                                        <td class="sj-user-list__clients">
                                            <button
                                                type="button"
                                                class="sj-ui-link"
                                                @click='openClientsModal(@json($user->name), @json($user->clients->pluck("name")->values()->all()), @json($user->isAdmin()))'
                                            >
                                                {{ $user->isAdmin() ? __('Todos') : $user->clients->count() }}
                                            </button>
                                        </td>
                                        <td class="sj-user-list__state">
                                            <form method="POST" action="{{ route('users.status', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <label class="sj-switch">
                                                    <input
                                                        type="checkbox"
                                                        name="is_active"
                                                        value="1"
                                                        @checked($user->is_active)
                                                        aria-label="{{ $user->is_active ? __('Activo') : __('Inactivo') }}"
                                                        onchange="this.form.submit()"
                                                    >
                                                    <span class="sj-switch__track"></span>
                                                </label>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="sj-user-list__actions">
                                                <a href="{{ route('users.edit', $user) }}" class="sj-ui-link">{{ __('Editar') }}</a>
                                                <form method="POST" action="{{ route('users.destroy', $user) }}">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="sj-ui-link sj-ui-link--danger" onclick="return confirm(@js(__('¿Eliminar usuario?')))">{{ __('Eliminar') }}</button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="sj-user-list__empty">{{ __('Sin usuarios registrados.') }}</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        </div>

        <div
            x-show="showClientsModal"
            x-cloak
            x-transition.opacity
            class="fixed inset-0 z-[1300] flex items-center justify-center bg-black/50 p-4"
            @click.self="showClientsModal = false"
        >
            <div class="w-full max-w-lg rounded-lg bg-white shadow-xl">
                <div class="flex items-center justify-between border-b border-gray-200 px-5 py-4">
                    <h3 class="sj-type-section text-gray-900">
                        {{ __('Clientes asignados') }}:
                        <span class="font-medium" x-text="modalUserName"></span>
                    </h3>
                    <button
                        type="button"
                        class="text-lg leading-none text-gray-500 hover:text-gray-700"
                        @click="showClientsModal = false"
                        aria-label="{{ __('Cerrar') }}"
                    >
                        X
                    </button>
                </div>
                <div class="max-h-80 overflow-y-auto px-5 py-4">
                    <p class="text-sm text-gray-700" x-show="modalSeesAll">{{ __('Ve todos los clientes.') }}</p>
                    <ul class="space-y-2 text-sm text-gray-700" x-show="!modalSeesAll && modalClients.length > 0">
                        <template x-for="(clientName, idx) in modalClients" :key="idx">
                            <li class="rounded border border-gray-200 px-3 py-2" x-text="clientName"></li>
                        </template>
                    </ul>
                    <p class="text-sm text-gray-500" x-show="!modalSeesAll && modalClients.length === 0">
                        {{ __('Sin clientes asignados.') }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
