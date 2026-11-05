<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                @php
                    [$catalogTitle, $catalogSubtitle] = match ($tab) {
                        'vinculacion' => [__('Tipos de vinculación'), __('Cómo se vincula el personal. Cada empresa conserva, quita o agrega los tipos que usa.')],
                        'contratos' => [__('Tipos de contrato'), __('Contratos de su empresa. Cada empresa conserva, quita o agrega los que usa.')],
                        'estructuras' => [__('Tipos de estructura'), __('Tipos de sede del cliente. Cada empresa conserva, quita o agrega los que usa.')],
                        'novedades' => [__('Tipos de novedad'), __('Tipos y modalidades que salen al registrar una novedad. El adjunto es opcional y la nota de cierre es obligatoria.')],
                        default => [__('Cargos'), __('Cargos de su empresa para usuarios y trabajadores.')],
                    };
                @endphp
                <h2 class="sj-section-header__title">{{ $catalogTitle }}</h2>
                <p class="sj-section-header__subtitle">{{ $catalogSubtitle }}</p>
            </div>
            <div class="sj-section-header__actions">
                <x-help-drawer :title="__('Ajustes')">
                    @include('settings.partials.help')
                </x-help-drawer>
                @if ($tab === 'novedades')
                    <button type="button" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm" x-data x-on:click="$dispatch('open-modal', 'incident-catalog-create')">{{ __('Agregar') }}</button>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="sj-page-shell space-y-3">
            @if (session('status'))
                <x-ui-alert type="success">{{ session('status') }}</x-ui-alert>
            @endif
            @if (session('error'))
                <x-ui-alert type="danger">{{ session('error') }}</x-ui-alert>
            @endif
            @if ($errors->any())
                <x-ui-alert type="danger">{{ $errors->first() }}</x-ui-alert>
            @endif

            @if ($tab === 'cargos')
                <section class="sj-ui-card">
                    <div class="sj-ui-card__body p-6">
                        <form method="POST" action="{{ route('catalogs.positions.store') }}" class="sj-position-add">
                            @csrf
                            <input name="name" type="text" required maxlength="255" placeholder="{{ __('Nuevo cargo, por ejemplo: Escolta') }}" class="sj-ui-field__control" aria-label="{{ __('Nuevo cargo') }}">
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm">{{ __('Agregar') }}</button>
                        </form>

                        <hr class="sj-ui-divider">

                        <div class="sj-position-row sj-position-row--head" aria-hidden="true">
                            <span>{{ __('Cargo') }}</span>
                            <span>{{ __('Orden') }}</span>
                            <span>{{ __('Estado') }}</span>
                            <span>{{ __('Acciones') }}</span>
                        </div>

                        @forelse ($positions as $position)
                            <div class="sj-position-row">
                                <form id="position-{{ $position->id }}" method="POST" action="{{ route('catalogs.positions.update', $position) }}" class="contents">
                                    @csrf
                                    @method('PUT')
                                    <input name="name" type="text" required maxlength="255" value="{{ $position->name }}" class="sj-ui-field__control" aria-label="{{ __('Cargo') }}">
                                </form>
                                <div class="sj-catalog__order">
                                    <form method="POST" action="{{ route('catalogs.positions.move', $position) }}">
                                        @csrf
                                        <input type="hidden" name="direction" value="up">
                                        <button type="submit" class="sj-catalog__arrow" aria-label="{{ __('Subir') }}">↑</button>
                                    </form>
                                    <form method="POST" action="{{ route('catalogs.positions.move', $position) }}">
                                        @csrf
                                        <input type="hidden" name="direction" value="down">
                                        <button type="submit" class="sj-catalog__arrow" aria-label="{{ __('Bajar') }}">↓</button>
                                    </form>
                                </div>
                                <label class="sj-catalog__state">
                                    <input type="hidden" name="is_active" value="0" form="position-{{ $position->id }}">
                                    <input type="checkbox" name="is_active" value="1" form="position-{{ $position->id }}" class="sj-ui-check rounded" @checked($position->is_active)>
                                    {{ __('activo') }}
                                </label>
                                <div class="sj-catalog__actions">
                                    <button type="submit" form="position-{{ $position->id }}" class="sj-ui-btn sj-ui-btn--success sj-ui-btn--sm">{{ __('Guardar') }}</button>
                                    <form method="POST" action="{{ route('catalogs.positions.destroy', $position) }}" onsubmit="return confirm(@js(__('¿Eliminar este cargo?')))">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="sj-ui-btn sj-ui-btn--danger sj-ui-btn--sm" @disabled($position->users_count > 0 || $position->workers_count > 0) title="{{ $position->users_count > 0 || $position->workers_count > 0 ? __('En uso: desactívelo.') : __('Eliminar') }}">{{ __('Eliminar') }}</button>
                                    </form>
                                </div>
                            </div>
                        @empty
                            <p class="sj-company__hint">{{ __('Aún no hay cargos. Agregue los cargos de su empresa para asignarlos a usuarios y trabajadores.') }}</p>
                        @endforelse
                    </div>
                </section>
            @elseif (in_array($tab, ['vinculacion', 'contratos', 'estructuras'], true))
                @php
                    [$catalogItems, $catalogLabel, $catalogPlaceholder, $catalogStore, $catalogUpdate, $catalogMove, $catalogDestroy, $catalogConfirm, $catalogPrefix, $catalogEmpty] = match ($tab) {
                        'contratos' => [$contractTypes, __('Tipo de contrato'), __('Nuevo tipo, por ejemplo: Término fijo'), 'catalogs.contract-types.store', 'catalogs.contract-types.update', 'catalogs.contract-types.move', 'catalogs.contract-types.destroy', __('¿Eliminar este tipo de contrato?'), 'contract', __('Aún no hay tipos de contrato.')],
                        'estructuras' => [$structureTypes, __('Tipo de estructura'), __('Nuevo tipo, por ejemplo: PH'), 'catalogs.structure-types.store', 'catalogs.structure-types.update', 'catalogs.structure-types.move', 'catalogs.structure-types.destroy', __('¿Eliminar este tipo de estructura?'), 'structure', __('Aún no hay tipos de estructura.')],
                        default => [$engagementTypes, __('Tipo de vinculación'), __('Nuevo tipo, por ejemplo: Dependiente'), 'catalogs.engagement-types.store', 'catalogs.engagement-types.update', 'catalogs.engagement-types.move', 'catalogs.engagement-types.destroy', __('¿Eliminar este tipo de vinculación?'), 'engagement', __('Aún no hay tipos de vinculación.')],
                    };
                @endphp
                <section class="sj-ui-card">
                    <div class="sj-ui-card__body p-6">
                        <form method="POST" action="{{ route($catalogStore) }}" class="sj-position-add">
                            @csrf
                            <input name="name" type="text" required maxlength="255" placeholder="{{ $catalogPlaceholder }}" class="sj-ui-field__control" aria-label="{{ $catalogLabel }}">
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm">{{ __('Agregar') }}</button>
                        </form>

                        <hr class="sj-ui-divider">

                        <div class="sj-position-row sj-position-row--head" aria-hidden="true">
                            <span>{{ $catalogLabel }}</span>
                            <span>{{ __('Orden') }}</span>
                            <span>{{ __('Estado') }}</span>
                            <span>{{ __('Acciones') }}</span>
                        </div>

                        @forelse ($catalogItems as $item)
                            @include('settings.partials.name-catalog-row', [
                                'item' => $item,
                                'label' => $catalogLabel,
                                'formPrefix' => $catalogPrefix,
                                'updateRoute' => $catalogUpdate,
                                'moveRoute' => $catalogMove,
                                'destroyRoute' => $catalogDestroy,
                                'deleteConfirm' => $catalogConfirm,
                            ])
                        @empty
                            <p class="sj-company__hint">{{ $catalogEmpty }}</p>
                        @endforelse
                    </div>
                </section>
            @elseif ($tab === 'novedades')
                <section class="sj-ui-card">
                    <div class="sj-ui-card__body sj-catalog">
                        <div class="sj-catalog__head" aria-hidden="true">
                            <span>{{ __('Tipo') }}</span>
                            <span>{{ __('Color') }}</span>
                            <span>{{ __('Estado') }}</span>
                            <span>{{ __('Orden') }}</span>
                            <span>{{ __('Acciones') }}</span>
                        </div>

                        @forelse ($incidentTypes as $type)
                            @if (! $loop->first)
                                <div class="sj-catalog__rule" role="separator"></div>
                            @endif
                            <article class="sj-catalog__group" style="--catalog-ink: {{ $type->displayColor() }}; --catalog-on: {{ $type->contrastOnColor() }};" @if ($type->modalities->isNotEmpty()) x-data="{ open: true }" @endif>
                                <div class="sj-catalog__row">
                                    <form id="incident-type-{{ $type->id }}" method="POST" action="{{ route('catalogs.incident-types.update', $type) }}" class="contents">
                                        @csrf
                                        @method('PUT')
                                        <input name="name" type="text" required maxlength="255" value="{{ $type->name }}" class="sj-catalog__name" aria-label="{{ __('Tipo') }}">
                                        <label class="sj-catalog__picker">
                                            <input name="color" type="color" required value="{{ $type->color ?: '#1a6572' }}" aria-label="{{ __('Color') }}">
                                        </label>
                                    </form>
                                    <label class="sj-catalog__state">
                                        <input type="hidden" name="is_active" value="0" form="incident-type-{{ $type->id }}">
                                        <input type="checkbox" name="is_active" value="1" form="incident-type-{{ $type->id }}" class="sj-ui-check rounded" @checked($type->is_active)>
                                        {{ __('activo') }}
                                    </label>
                                    <div class="sj-catalog__order">
                                        <form method="POST" action="{{ route('catalogs.incident-types.move', $type) }}">
                                            @csrf
                                            <input type="hidden" name="direction" value="up">
                                            <button type="submit" class="sj-catalog__arrow" aria-label="{{ __('Subir') }}">↑</button>
                                        </form>
                                        <form method="POST" action="{{ route('catalogs.incident-types.move', $type) }}">
                                            @csrf
                                            <input type="hidden" name="direction" value="down">
                                            <button type="submit" class="sj-catalog__arrow" aria-label="{{ __('Bajar') }}">↓</button>
                                        </form>
                                    </div>
                                    <div class="sj-catalog__actions">
                                        <button type="submit" form="incident-type-{{ $type->id }}" class="sj-ui-btn sj-ui-btn--success sj-ui-btn--sm">{{ __('Guardar') }}</button>
                                        <form method="POST" action="{{ route('catalogs.incident-types.destroy', $type) }}" onsubmit="return confirm(@js(__('¿Eliminar este tipo?')))">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="sj-ui-btn sj-ui-btn--danger sj-ui-btn--sm" @disabled($type->catalogInUse()) title="{{ $type->catalogInUse() ? __('En uso: desactívelo.') : __('Eliminar') }}">{{ __('Eliminar') }}</button>
                                        </form>
                                    </div>
                                </div>

                                @if ($type->modalities->isNotEmpty())
                                    <button type="button" class="sj-catalog__toggle" x-on:click="open = !open" x-bind:aria-expanded="open.toString()">
                                        <span class="sj-catalog__chevron" x-bind:class="open && 'is-open'" aria-hidden="true"></span>
                                        {{ __('Modalidades') }}
                                    </button>
                                    <div class="sj-catalog__mods" x-show="open" x-cloak>
                                        @foreach ($type->modalities as $modality)
                                            <div class="sj-catalog__row sj-catalog__row--mod">
                                                <form id="incident-modality-{{ $modality->id }}" method="POST" action="{{ route('catalogs.modalities.update', $modality) }}" class="contents">
                                                    @csrf
                                                    @method('PUT')
                                                    <input name="name" type="text" required maxlength="255" value="{{ $modality->name }}" class="sj-catalog__name sj-catalog__name--mod" aria-label="{{ __('Modalidad') }}">
                                                </form>
                                                <span class="sj-catalog__dot" style="background: {{ $type->displayColor() }}" title="{{ __('Usa el color del tipo') }}"></span>
                                                <label class="sj-catalog__state">
                                                    <input type="hidden" name="is_active" value="0" form="incident-modality-{{ $modality->id }}">
                                                    <input type="checkbox" name="is_active" value="1" form="incident-modality-{{ $modality->id }}" class="sj-ui-check rounded" @checked($modality->is_active)>
                                                    {{ __('activo') }}
                                                </label>
                                                <div class="sj-catalog__order">
                                                    <form method="POST" action="{{ route('catalogs.modalities.move', $modality) }}">
                                                        @csrf
                                                        <input type="hidden" name="direction" value="up">
                                                        <button type="submit" class="sj-catalog__arrow" aria-label="{{ __('Subir') }}">↑</button>
                                                    </form>
                                                    <form method="POST" action="{{ route('catalogs.modalities.move', $modality) }}">
                                                        @csrf
                                                        <input type="hidden" name="direction" value="down">
                                                        <button type="submit" class="sj-catalog__arrow" aria-label="{{ __('Bajar') }}">↓</button>
                                                    </form>
                                                </div>
                                                <div class="sj-catalog__actions">
                                                    <button type="submit" form="incident-modality-{{ $modality->id }}" class="sj-ui-btn sj-ui-btn--success sj-ui-btn--sm">{{ __('Guardar') }}</button>
                                                    <form method="POST" action="{{ route('catalogs.modalities.destroy', $modality) }}" onsubmit="return confirm(@js(__('¿Eliminar esta modalidad?')))">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="sj-ui-btn sj-ui-btn--danger sj-ui-btn--sm" @disabled($modality->incidents_count > 0) title="{{ $modality->incidents_count > 0 ? __('En uso: desactívela.') : __('Eliminar') }}">{{ __('Eliminar') }}</button>
                                                    </form>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </article>
                        @empty
                            <p class="sj-company__hint">{{ __('Aún no hay tipos de novedad.') }}</p>
                        @endforelse
                    </div>
                </section>

                <x-modal name="incident-catalog-create" :show="$errors->any() && old('kind')" maxWidth="md" focusable>
                    <form method="POST" action="{{ route('catalogs.incident-types.store') }}" class="space-y-4 p-6" x-data="{ kind: @js(old('kind', 'type')) }">
                        @csrf
                        <h3 class="text-lg font-semibold text-slate-900">{{ __('Agregar') }}</h3>
                        <div class="flex gap-4">
                            <label class="sj-company__check">
                                <input type="radio" name="kind" value="type" class="sj-ui-check" x-model="kind">
                                {{ __('Tipo') }}
                            </label>
                            <label class="sj-company__check">
                                <input type="radio" name="kind" value="modality" class="sj-ui-check" x-model="kind">
                                {{ __('Modalidad') }}
                            </label>
                        </div>
                        <div class="sj-ui-field" x-show="kind === 'type'">
                            <label class="sj-ui-field__label" for="incident-type-name">{{ __('Tipo nuevo') }}</label>
                            <input id="incident-type-name" name="name" type="text" maxlength="255" value="{{ old('kind') === 'type' ? old('name') : '' }}" class="sj-ui-field__control" x-bind:disabled="kind !== 'type'" x-bind:required="kind === 'type'">
                        </div>
                        <div class="sj-ui-field" x-show="kind === 'type'">
                            <label class="sj-ui-field__label" for="incident-type-color">{{ __('Color') }}</label>
                            <input id="incident-type-color" name="color" type="color" value="{{ old('color', '#1a6572') }}" class="sj-ui-field__control sj-company__color" x-bind:disabled="kind !== 'type'" x-bind:required="kind === 'type'">
                        </div>
                        <div class="sj-ui-field" x-show="kind === 'modality'" x-cloak>
                            <label class="sj-ui-field__label" for="incident-modality-type">{{ __('Tipo') }}</label>
                            <select id="incident-modality-type" name="incident_type_id" class="sj-ui-field__control" x-bind:disabled="kind !== 'modality'" x-bind:required="kind === 'modality'">
                                <option value="">{{ __('Seleccione un tipo') }}</option>
                                @foreach ($incidentTypes as $type)
                                    <option value="{{ $type->id }}" @selected((string) old('incident_type_id') === (string) $type->id)>{{ $type->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sj-ui-field" x-show="kind === 'modality'" x-cloak>
                            <label class="sj-ui-field__label" for="incident-modality-name">{{ __('Modalidad') }}</label>
                            <input id="incident-modality-name" name="name" type="text" maxlength="255" value="{{ old('kind') === 'modality' ? old('name') : '' }}" class="sj-ui-field__control" x-bind:disabled="kind !== 'modality'" x-bind:required="kind === 'modality'">
                        </div>
                        <div class="flex justify-end gap-2">
                            <button type="button" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm" x-on:click="$dispatch('close-modal', 'incident-catalog-create')">{{ __('Cancelar') }}</button>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm">{{ __('Guardar') }}</button>
                        </div>
                    </form>
                </x-modal>
            @endif
        </div>
    </div>
</x-app-layout>
