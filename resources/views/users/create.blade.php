<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <div class="sj-section-header__title-row">
                    <h2 class="sj-section-header__title">{{ __('Nuevo usuario') }}</h2>
                    <x-help-drawer :title="__('Usuarios')">
                        @include('users.partials.help')
                    </x-help-drawer>
                </div>
                <p class="sj-section-header__subtitle">{{ __('Rol, cargo, clientes y permisos por módulo.') }}</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('users.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al listado') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="sj-page-shell sj-page-shell--wide space-y-3">
            <x-ui-alert type="warning">{{ __('Al guardar se genera una contraseña y se muestra una sola vez. El usuario de plataforma debe cambiarla en su primer ingreso. El de campo usa esa misma contraseña en Revista armas.') }}</x-ui-alert>

            <form method="POST" action="{{ route('users.store') }}" enctype="multipart/form-data">
                @csrf
                <section class="sj-ui-card">
                    <div class="sj-ui-card__body p-6">
                        @include('users.partials.form')

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('users.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar') }}</button>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
</x-app-layout>
