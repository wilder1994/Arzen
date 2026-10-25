<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <div class="sj-section-header__title-row">
                    <h2 class="sj-section-header__title">{{ __('Editar usuario') }}</h2>
                    <x-help-drawer :title="__('Usuarios')">
                        @include('users.partials.help')
                    </x-help-drawer>
                </div>
                <p class="sj-section-header__subtitle">{{ $user->name }} · {{ $user->roleLabel() }}</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('users.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al listado') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="sj-page-shell sj-page-shell--wide">
            <form method="POST" action="{{ route('users.update', $user) }}" x-data="{ generateTemp: @js((bool) old('generate_temporary_password')) }">
                @csrf
                @method('PUT')
                <section class="sj-ui-card">
                    <div class="sj-ui-card__body p-6">
                        @include('users.partials.form')

                        <hr class="sj-ui-divider">

                        <h3 class="sj-form-section__title">{{ __('Contraseña') }}</h3>
                        <label class="sj-company__check mb-3">
                            <input type="checkbox" name="generate_temporary_password" value="1" class="sj-ui-check rounded" x-model="generateTemp">
                            {{ __('Generar una contraseña temporal y exigir que la cambie al ingresar') }}
                        </label>
                        <div class="sj-company__grid" :class="generateTemp ? 'pointer-events-none opacity-50' : ''">
                            <x-password-reveal-input label="{{ __('Nueva contraseña (opcional)') }}" name="password" id="password" autocomplete="new-password" />
                            <x-password-reveal-input label="{{ __('Confirmar contraseña') }}" name="password_confirmation" id="password_confirmation" autocomplete="new-password" />
                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('users.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar cambios') }}</button>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
</x-app-layout>
