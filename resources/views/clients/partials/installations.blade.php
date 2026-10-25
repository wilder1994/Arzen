@php
    $installations = $client->installations()->withCount(['posts' => fn ($query) => $query->active()])->orderByDesc('is_client_site')->orderBy('name')->get();
@endphp

<section class="sj-ui-card">
    <div class="sj-ui-card__body p-6">
        <div class="mb-3 flex flex-wrap items-center justify-between gap-3">
            <div class="sj-form-section__title sj-ui-field__label-row mb-0">
                <h3>{{ __('Instalaciones') }}</h3>
                <x-help-tip :text="__('Sedes del cliente. Cada instalación agrupa sus puestos, y los puestos toman su ubicación.')" />
            </div>
            @can('update', $client)
                <a href="{{ route('clients.installations.create', $client) }}" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm">{{ __('Nueva instalación') }}</a>
            @endcan
        </div>

        <div class="sj-table-wrap">
            <table class="sj-table sj-table--compact sj-table--align-left">
                <thead>
                    <tr>
                        <th>{{ __('Instalación') }}</th>
                        <th>{{ __('Dirección') }}</th>
                        <th>{{ __('Municipio') }}</th>
                        <th>{{ __('Puestos') }}</th>
                        <th>{{ __('Mapa') }}</th>
                        <th class="text-right">{{ __('Acciones') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($installations as $installation)
                        <tr @class(['opacity-60' => $installation->isArchived()])>
                            <td>
                                <span class="font-semibold">{{ $installation->name }}</span>
                                @if ($installation->is_client_site)
                                    <span class="sj-chip">{{ __('Principal') }}</span>
                                @endif
                                @if ($installation->isArchived())
                                    <span class="sj-chip">{{ __('Archivada') }}</span>
                                @endif
                            </td>
                            <td>{{ $installation->address ?: '—' }}</td>
                            <td>{{ $installation->city ?: '—' }}</td>
                            <td>{{ $installation->posts_count }}</td>
                            <td>{{ filled($installation->latitude) ? __('Sí') : __('No') }}</td>
                            <td class="text-right">
                                @can('update', $client)
                                    <div class="inline-flex gap-3">
                                        <a href="{{ route('posts.create', ['installation_id' => $installation->id]) }}" class="sj-ui-link">{{ __('Agregar puesto') }}</a>
                                        <a href="{{ route('installations.edit', $installation) }}" class="sj-ui-link">{{ __('Editar') }}</a>
                                        @unless ($installation->is_client_site)
                                            <form method="POST" action="{{ route($installation->isArchived() ? 'installations.restore' : 'installations.archive', $installation) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="sj-ui-link {{ $installation->isArchived() ? '' : 'sj-ui-link--danger' }}">
                                                    {{ $installation->isArchived() ? __('Restaurar') : __('Archivar') }}
                                                </button>
                                            </form>
                                        @endunless
                                    </div>
                                @endcan
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</section>
