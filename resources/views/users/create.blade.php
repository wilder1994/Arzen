<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Nuevo usuario') }}</h2>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('users.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al listado') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide">
            <div class="sj-ui-card p-6">
                    <form method="POST" action="{{ route('users.store') }}" class="grid grid-cols-1 gap-4 md:grid-cols-2">
                        @csrf

                        <div>
                            <x-input-label for="name" :value="__('Nombre')" />
                            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" value="{{ old('name') }}" required />
                            <x-input-error :messages="$errors->get('name')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="email" :value="__('Correo electrónico')" />
                            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" value="{{ old('email') }}" required />
                            <x-input-error :messages="$errors->get('email')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="role" :value="__('Responsable')" />
                            <select id="role" name="role" class="mt-1 block w-full rounded-md border-gray-300" required>
                                @foreach ($roles as $value => $label)
                                    <option value="{{ $value }}" @selected(old('role') === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('role')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="position_id" :value="__('Cargo')" />
                            <select id="position_id" name="position_id" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">{{ __('Seleccione') }}</option>
                                @foreach ($positions as $position)
                                    <option value="{{ $position->id }}" @selected(old('position_id') == $position->id)>{{ $position->name }}</option>
                                @endforeach
                            </select>
                            @if ($positions->isEmpty())
                                <p class="mt-1 text-xs text-gray-500">{{ __('No hay cargos creados.') }} <a href="{{ route('catalogs.index', ['tab' => 'cargos']) }}" class="font-semibold underline">{{ __('Crear cargos') }}</a></p>
                            @endif
                            <x-input-error :messages="$errors->get('position_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="responsibility_level_id" :value="__('Nivel de responsabilidad')" />
                            <select id="responsibility_level_id" name="responsibility_level_id" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="">{{ __('Seleccione') }}</option>
                                @foreach ($responsibilityLevels as $level)
                                    <option value="{{ $level->id }}" @selected(old('responsibility_level_id') == $level->id)>
                                        {{ $level->level }} - {{ $level->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('responsibility_level_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="is_active" :value="__('Estado activo')" />
                            <select id="is_active" name="is_active" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="1" @selected(old('is_active', '1') === '1')>{{ __('Activo') }}</option>
                                <option value="0" @selected(old('is_active') === '0')>{{ __('Inactivo') }}</option>
                            </select>
                            <x-input-error :messages="$errors->get('is_active')" class="mt-2" />
                        </div>

                        <div class="md:col-span-2 rounded-md border border-amber-100 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                            {{ __('Al guardar se generará una contraseña temporal segura. Podrá copiarla en la siguiente pantalla y enviársela al usuario. En el primer inicio de sesión el usuario deberá definir su propia contraseña.') }}
                        </div>

                        <div class="md:col-span-2 sj-form-actions">
                            <a href="{{ route('users.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar') }}</button>
                        </div>
                    </form>
            </div>
        </div>
    </div>
</x-app-layout>




