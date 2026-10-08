<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Catálogos') }}</h2>
                <p class="sj-section-header__subtitle">{{ __('Listas que usan los formularios de usuarios y de novedades.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell space-y-6">
            @if (session('status'))
                <div class="rounded bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif
            @if (session('error'))
                <div class="rounded bg-red-50 p-3 text-sm text-red-700" role="alert">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="rounded bg-red-50 p-3 text-sm text-red-700" role="alert">{{ $errors->first() }}</div>
            @endif

            <nav class="sj-company__tabs" aria-label="{{ __('Catálogos') }}">
                @foreach (['cargos' => __('Cargos'), 'novedades' => __('Tipos de novedad'), 'niveles' => __('Niveles de responsabilidad')] as $key => $label)
                    <a href="{{ route('catalogs.index', ['tab' => $key]) }}" @class(['sj-company__tab', 'is-active' => $tab === $key])>{{ $label }}</a>
                @endforeach
            </nav>

            @if ($tab === 'cargos')
                <section class="sj-ui-card">
                    <div class="sj-ui-card__body space-y-4 p-6">
                        <form method="POST" action="{{ route('catalogs.positions.store') }}" class="sj-company__row">
                            @csrf
                            <input name="name" type="text" required placeholder="{{ __('Nuevo cargo') }}" class="sj-ui-field__control">
                            <input name="description" type="text" placeholder="{{ __('Descripción (opcional)') }}" class="sj-ui-field__control">
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm">{{ __('Agregar') }}</button>
                        </form>

                        @forelse ($positions as $position)
                            <div class="sj-company__row">
                                <form method="POST" action="{{ route('catalogs.positions.update', $position) }}" class="contents">
                                    @csrf
                                    @method('PUT')
                                    <input name="name" type="text" required value="{{ $position->name }}" class="sj-ui-field__control" aria-label="{{ __('Cargo') }}">
                                    <input name="description" type="text" value="{{ $position->description }}" class="sj-ui-field__control" aria-label="{{ __('Descripción') }}">
                                    <button type="submit" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm">{{ __('Guardar') }}</button>
                                </form>
                                <form method="POST" action="{{ route('catalogs.positions.destroy', $position) }}" onsubmit="return confirm(@js(__('¿Eliminar este cargo?')))">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="sj-ui-btn sj-ui-btn--danger sj-ui-btn--sm" @disabled($position->users_count > 0) title="{{ $position->users_count > 0 ? __('Asignado a :count usuario(s)', ['count' => $position->users_count]) : __('Eliminar') }}">{{ __('Eliminar') }}</button>
                                </form>
                            </div>
                        @empty
                            <p class="sj-company__hint">{{ __('Aún no hay cargos. Agrega los cargos de tu empresa para asignarlos a los usuarios.') }}</p>
                        @endforelse
                    </div>
                </section>
            @elseif ($tab === 'novedades')
                <p class="sj-company__hint">{{ __('Los tipos de novedad son del sistema: los reportes y el tablero dependen de ellos, por eso no se crean ni se eliminan. Puedes ajustar su nombre, color, orden, tiempo de atención y modalidades.') }}</p>

                @foreach ($incidentTypes as $type)
                    <section class="sj-ui-card">
                        <div class="sj-ui-card__body space-y-4 p-6">
                            <form method="POST" action="{{ route('catalogs.incident-types.update', $type) }}" class="space-y-3">
                                @csrf
                                @method('PUT')
                                <div class="sj-company__type-grid">
                                    <div class="sj-ui-field">
                                        <label class="sj-ui-field__label">{{ __('Nombre') }}</label>
                                        <input name="name" type="text" required value="{{ $type->name }}" class="sj-ui-field__control">
                                    </div>
                                    <div class="sj-ui-field">
                                        <label class="sj-ui-field__label">{{ __('Color') }}</label>
                                        <input name="color" type="color" value="{{ $type->color ?: '#1a6572' }}" class="sj-ui-field__control sj-company__color">
                                    </div>
                                    <div class="sj-ui-field">
                                        <label class="sj-ui-field__label">{{ __('Orden') }}</label>
                                        <input name="sort_order" type="number" min="0" required value="{{ $type->sort_order }}" class="sj-ui-field__control">
                                    </div>
                                    <div class="sj-ui-field">
                                        <label class="sj-ui-field__label">{{ __('Horas de atención') }}</label>
                                        <input name="sla_hours" type="number" min="0" value="{{ $type->sla_hours }}" class="sj-ui-field__control">
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-4">
                                    <label class="sj-company__check"><input type="checkbox" name="requires_attachment" value="1" class="sj-ui-check rounded" @checked($type->requires_attachment)> {{ __('Exige adjunto') }}</label>
                                    <label class="sj-company__check"><input type="checkbox" name="requires_resolution_note" value="1" class="sj-ui-check rounded" @checked($type->requires_resolution_note)> {{ __('Exige nota de cierre') }}</label>
                                    <label class="sj-company__check"><input type="checkbox" name="is_active" value="1" class="sj-ui-check rounded" @checked($type->is_active)> {{ __('Activo') }}</label>
                                    <button type="submit" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm ml-auto">{{ __('Guardar tipo') }}</button>
                                </div>
                            </form>

                            @if ($type->requires_modality)
                                <div class="space-y-2 border-t pt-4" style="border-color: var(--sj-ui-surface-border)">
                                    <p class="sj-ui-field__label">{{ __('Modalidades') }}</p>
                                    @foreach ($type->modalities as $modality)
                                        <form method="POST" action="{{ route('catalogs.modalities.update', $modality) }}" class="sj-company__row sj-company__row--modality">
                                            @csrf
                                            @method('PUT')
                                            <input name="name" type="text" required value="{{ $modality->name }}" class="sj-ui-field__control" aria-label="{{ __('Modalidad') }}">
                                            <input name="sort_order" type="number" min="0" required value="{{ $modality->sort_order }}" class="sj-ui-field__control" aria-label="{{ __('Orden') }}">
                                            <label class="sj-company__check"><input type="checkbox" name="is_active" value="1" class="sj-ui-check rounded" @checked($modality->is_active)> {{ __('Activa') }}</label>
                                            <button type="submit" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--xs">{{ __('Guardar') }}</button>
                                        </form>
                                    @endforeach
                                    <form method="POST" action="{{ route('catalogs.modalities.store', $type) }}" class="sj-company__row">
                                        @csrf
                                        <input name="name" type="text" required placeholder="{{ __('Nueva modalidad') }}" class="sj-ui-field__control">
                                        <button type="submit" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--xs">{{ __('Agregar') }}</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </section>
                @endforeach
            @else
                <section class="sj-ui-card">
                    <div class="sj-ui-card__body space-y-4 p-6">
                        <p class="sj-company__hint">{{ __('El sistema usa dos niveles para los responsables: el nivel 1 gestiona sus armas y el nivel 2 solo consulta. Puedes cambiar cómo se llaman.') }}</p>
                        @foreach ($levels as $level)
                            <form method="POST" action="{{ route('catalogs.levels.update', $level) }}" class="sj-company__row">
                                @csrf
                                @method('PUT')
                                <span class="sj-company__level">{{ __('Nivel :level', ['level' => $level->level]) }}</span>
                                <input name="name" type="text" required value="{{ $level->name }}" class="sj-ui-field__control" aria-label="{{ __('Nombre') }}">
                                <input name="description" type="text" value="{{ $level->description }}" class="sj-ui-field__control" aria-label="{{ __('Descripción') }}">
                                <button type="submit" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm">{{ __('Guardar') }}</button>
                            </form>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </div>
</x-app-layout>
