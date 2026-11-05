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

    <div class="py-4" x-data="{ generateTemp: @js((bool) old('generate_temporary_password')), showSend: false }">
        <div class="sj-page-shell sj-page-shell--wide">
            <form method="POST" action="{{ route('users.update', $user) }}" enctype="multipart/form-data">
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
                        @if ($user->is_active)
                            <button type="button" class="sj-ui-link mt-3" @click="showSend = true">{{ __('Enviar credenciales por correo') }}</button>
                        @endif

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('users.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar cambios') }}</button>
                        </div>
                    </div>
                </section>
            </form>
        </div>

        @if ($user->is_active)
            <div
                x-show="showSend"
                x-cloak
                x-transition.opacity
                class="fixed inset-0 z-[1400] flex items-center justify-center bg-black/50 p-4"
                @click.self="showSend = false"
            >
                <div class="w-full max-w-lg rounded-lg bg-white shadow-xl">
                    <div class="border-b border-gray-200 px-5 py-4">
                        <h3 class="sj-type-section text-gray-900">{{ __('Confirmar envío de credenciales') }}</h3>
                        <p class="mt-1 text-sm text-gray-600">
                            {{ __('Correo destino') }}: <span class="font-medium text-gray-900">{{ $user->email }}</span>
                        </p>
                    </div>
                    <div class="max-h-[70vh] overflow-y-auto px-5 py-4 space-y-3 text-sm text-gray-700">
                        <p>{{ __('Se enviará un correo a este usuario con la siguiente información:') }}</p>
                        <ul class="list-disc space-y-2 pl-5">
                            <li>
                                {{ __('Enlace de acceso a la aplicación:') }}
                                <code class="rounded bg-gray-100 px-1.5 py-0.5 text-xs">{{ rtrim(url('/'), '/') }}</code>
                            </li>
                            <li>
                                {{ __('Usuario (correo electrónico):') }}
                                <span class="font-medium">{{ $user->email }}</span>
                            </li>
                            <li>{{ __('Una contraseña temporal nueva; si el usuario ya tenía una, dejará de ser válida.') }}</li>
                            <li>{{ __('Instrucciones indicando que al iniciar sesión por primera vez deberá cambiar la contraseña de forma obligatoria.') }}</li>
                        </ul>
                        <p class="text-amber-800 bg-amber-50 border border-amber-100 rounded-md px-3 py-2 text-xs">
                            {{ __('¿Desea continuar?') }}
                        </p>
                    </div>
                    <div class="flex items-center justify-end gap-2 border-t border-gray-200 px-5 py-4">
                        <button type="button" class="sj-ui-btn sj-ui-btn--ghost" @click="showSend = false">{{ __('Cancelar') }}</button>
                        <form method="POST" action="{{ route('users.send-access-credentials', $user) }}">
                            @csrf
                            <x-primary-button type="submit" class="text-sm">{{ __('Enviar correo') }}</x-primary-button>
                        </form>
                    </div>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
