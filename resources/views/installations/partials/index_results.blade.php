<div class="sj-table-wrap">
    <table class="sj-table sj-table--align-left sj-table--compact sj-user-list">
        <thead>
                <tr>
                    <th class="sj-col-grow">{{ __('Instalación') }}</th>
                    <th class="sj-col-grow">{{ __('Cliente') }}</th>
                    <th>{{ __('Principal') }}</th>
                    <th>{{ __('Ubicación') }}</th>
                    <th>{{ __('Puestos') }}</th>
                    <th class="sj-col-actions">{{ __('Acciones') }}</th>
                </tr>
        </thead>
        <tbody>
            @forelse ($installations as $installation)
                @php
                    $hasPin = \App\Support\MapCoordinates::isFilled($installation->latitude, $installation->longitude);
                @endphp
                <tr @class(['sj-archived-row' => $installation->isArchived()])>
                    <td class="sj-col-grow">
                        {{ $installation->name }}
                        @if ($installation->isArchived())
                            <span class="sj-chip">{{ __('Archivada') }}</span>
                        @endif
                    </td>
                    <td class="sj-col-grow">{{ $installation->client?->name }}</td>
                    <td class="sj-col-center">{{ $installation->is_client_site ? __('Sí') : __('No') }}</td>
                    <td class="sj-col-center">
                        @if ($hasPin)
                            <button
                                type="button"
                                class="sj-map-pin"
                                aria-label="{{ __('Ver ubicación') }}"
                                data-map-title="{{ $installation->name }}"
                                data-map-lat="{{ $installation->latitude }}"
                                data-map-lng="{{ $installation->longitude }}"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                            </button>
                        @else
                            {{ __('Ubicación pendiente') }}
                        @endif
                    </td>
                    <td class="sj-col-center">{{ $installation->active_posts_count }}</td>
                    <td class="sj-col-actions">
                        <div class="sj-user-list__actions">
                            @can('update', $installation->client)
                                <a href="{{ route('installations.edit', $installation) }}" class="sj-ui-link">{{ __('Editar') }}</a>
                                @unless ($installation->is_client_site)
                                    @if ($installation->isArchived())
                                        <form method="POST" action="{{ route('installations.restore', $installation) }}" onsubmit="return confirm(@js(__('¿Reactivar esta instalación?')))">
                                            @csrf
                                            @method('PATCH')
                                            <button class="sj-ui-link">{{ __('Reactivar') }}</button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('installations.archive', $installation) }}" onsubmit="return confirm(@js(__('¿Archivar esta instalación? Solo se puede si no tiene puestos activos.')))">
                                            @csrf
                                            @method('PATCH')
                                            <button class="sj-ui-link sj-ui-link--danger">{{ __('Archivar') }}</button>
                                        </form>
                                    @endif
                                @endunless
                            @endcan
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="sj-user-list__empty">{{ __('Sin instalaciones.') }}</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $installations->links() }}</div>
