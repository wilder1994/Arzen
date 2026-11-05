@if ($clients->count() > 0)
    <div class="sj-table-wrap hidden md:block">
        <table class="sj-table sj-table--align-left sj-table--compact sj-user-list">
            <thead>
                <tr>
                    <th class="sj-col-grow">{{ __('Cliente') }}</th>
                    <th>{{ __('NIT') }}</th>
                    <th>{{ __('Contacto') }}</th>
                    <th>{{ __('Ubicación') }}</th>
                    <th>{{ __('Instalaciones') }}</th>
                    <th class="sj-col-actions">{{ __('Acciones') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($clients as $client)
                    @php
                        $hasPin = \App\Support\MapCoordinates::isFilled($client->latitude, $client->longitude);
                    @endphp
                    <tr @class(['sj-archived-row' => $client->isArchived()])>
                        <td class="sj-col-grow">
                            <div class="sj-client-row__lead">
                                <span>{{ $client->name }}</span>
                                @if ($client->isArchived())
                                    <span class="sj-chip">{{ __('Archivado') }}</span>
                                @endif
                            </div>
                        </td>
                        <td>{{ $client->nit ?: '—' }}</td>
                        <td>{{ $client->contact_name ?: __('Sin contacto') }}</td>
                        <td class="sj-col-center">
                            @if ($hasPin)
                                <button
                                    type="button"
                                    class="sj-map-pin"
                                    aria-label="{{ __('Ver ubicación') }}"
                                    data-map-title="{{ $client->name }}"
                                    data-map-lat="{{ $client->latitude }}"
                                    data-map-lng="{{ $client->longitude }}"
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
                        <td>{{ $client->installationListLabel() }}</td>
                        <td class="sj-col-actions">
                            <div class="sj-user-list__actions">
                                <button type="button" class="sj-ui-link" data-open-ficha="client-ficha-{{ $client->id }}" data-ficha-title="{{ $client->name }}">{{ __('Ver') }}</button>
                                @can('update', $client)
                                    <a href="{{ route('clients.edit', $client) }}" class="sj-ui-link">{{ __('Editar') }}</a>
                                @endcan
                                @can('delete', $client)
                                    @if ($client->isArchived())
                                        <form action="{{ route('clients.restore', $client) }}" method="POST">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="sj-ui-link" onclick="return confirm(@js(__('¿Reactivar este cliente?')))">{{ __('Reactivar') }}</button>
                                        </form>
                                    @else
                                        <form action="{{ route('clients.destroy', $client) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="sj-ui-link sj-ui-link--danger" onclick="return confirm(@js(__('¿Archivar este cliente? La fila queda en el listado y se puede reactivar.')))">{{ __('Archivar') }}</button>
                                        </form>
                                    @endif
                                @endcan
                            </div>
                            <template id="client-ficha-{{ $client->id }}">
                                @include('clients.partials.ficha', ['client' => $client])
                            </template>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="sj-client-cards md:hidden">
        @foreach ($clients as $client)
            <article @class(['sj-client-card', 'sj-archived-row' => $client->isArchived()])>
                <div class="sj-client-card__top">
                    <div>
                        <div class="sj-client-col__label">{{ __('Cliente') }}</div>
                        <h3 class="sj-client-card__title">{{ $client->name }}</h3>
                        <p class="sj-site-empty">{{ $client->installationListLabel() }}</p>
                    </div>
                </div>

                <div class="sj-client-card__actions">
                    <button type="button" class="sj-ui-link" data-open-ficha="client-ficha-{{ $client->id }}" data-ficha-title="{{ $client->name }}">{{ __('Ver') }}</button>
                    @can('update', $client)
                        <a href="{{ route('clients.edit', $client) }}" class="sj-client-action sj-client-action--edit">
                            {{ __('Editar') }}
                        </a>
                    @endcan
                    @can('delete', $client)
                        @if ($client->isArchived())
                            <form action="{{ route('clients.restore', $client) }}" method="POST" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="sj-client-action" onclick="return confirm(@js(__('¿Reactivar este cliente?')))">
                                    {{ __('Reactivar') }}
                                </button>
                            </form>
                        @else
                            <form action="{{ route('clients.destroy', $client) }}" method="POST" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="sj-client-action sj-client-action--delete" onclick="return confirm(@js(__('¿Archivar este cliente? La fila queda en el listado y se puede reactivar.')))">
                                    {{ __('Archivar') }}
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            </article>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $clients->links() }}
    </div>
@else
    <div class="sj-client-empty">
        <div class="sj-client-empty__title">
            {{ $search !== '' ? __('No se encontraron clientes.') : __('No hay clientes registrados.') }}
        </div>
        <p class="sj-client-empty__copy">
            {{ $search !== '' ? __('Ajusta el término de búsqueda para ver resultados por razón social, NIT, contacto, correo o ciudad.') : __('Cuando registres clientes, aquí verás su información principal organizada para edición y seguimiento.') }}
        </p>

        @if ($search === '' && auth()->user()?->can('create', App\Models\Client::class))
            <div>
                <a href="{{ route('clients.create') }}" class="sj-client-action sj-client-action--edit">
                    {{ __('Crear primer cliente') }}
                </a>
            </div>
        @endif
    </div>
@endif
