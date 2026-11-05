@forelse ($workers as $worker)
    <tr>
        <td>{{ $worker->name }}</td>
        <td>{{ $worker->document ?: '—' }}</td>
        <td>{{ $worker->position?->name ?? '—' }}</td>
        <td>{{ $worker->post?->name ?? '—' }}</td>
        <td>{{ $worker->client?->name }}</td>
        <td>{{ $worker->responsible?->name ?: '—' }}</td>
        <td class="sj-col-center">{{ $worker->isArchived() ? __('Archivado') : __('Activo') }}</td>
        <td>
            <div class="sj-user-list__actions sj-user-list__actions--worker">
                @can('view', $worker)
                    <button
                        type="button"
                        class="sj-ui-link sj-ui-link--muted"
                        @click="openHistory(@js($worker->name), '{{ route('workers.histories', $worker) }}')"
                    >
                        {{ __('Historial') }}
                    </button>
                @endcan
                @can('update', $worker)
                    <a href="{{ route('workers.edit', $worker) }}" class="sj-ui-link">{{ __('Editar') }}</a>
                @endcan
                @can('delete', $worker)
                    <form action="{{ route('workers.destroy', $worker) }}" method="POST">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="sj-ui-link sj-ui-link--warn" onclick="return confirm(@js(__('¿Archivar este trabajador? Las armas asignadas aquí quedarán sin ubicación interna activa.')))">{{ __('Archivar') }}</button>
                    </form>
                @endcan
                @can('restore', $worker)
                    <form action="{{ route('workers.restore', $worker) }}" method="POST">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="sj-ui-link sj-ui-link--success" onclick="return confirm(@js(__('¿Reactivar este trabajador?')))">{{ __('Reactivar') }}</button>
                    </form>
                @endcan
            </div>
        </td>
    </tr>
@empty
    <tr>
        <td colspan="8" class="sj-user-list__empty">{{ __('No hay trabajadores registrados.') }}</td>
    </tr>
@endforelse
