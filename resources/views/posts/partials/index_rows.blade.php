@forelse ($posts as $post)
    <tr>
        <td class="px-3 py-2">{{ $post->name }}</td>
        <td class="px-3 py-2">{{ $post->client?->name }}</td>
        <td class="px-3 py-2">
            {{ $post->installation?->name ?? '—' }}
            @if ($post->address)
                <span class="block text-xs text-gray-500">{{ $post->address }}</span>
            @endif
        </td>
        <td class="px-3 py-2">
            @if ($post->isArchived())
                <span class="rounded bg-gray-100 px-2 py-0.5 text-xs text-gray-700">{{ __('Archivado') }}</span>
            @else
                <span class="rounded bg-green-50 px-2 py-0.5 text-xs text-green-800">{{ __('Activo') }}</span>
            @endif
        </td>
        <td class="px-3 py-2 text-right space-x-2">
            @can('view', $post)
                <button
                    type="button"
                    class="sj-ui-link sj-ui-link--muted"
                    @click="openHistory(@js($post->name), '{{ route('posts.histories', $post) }}')"
                >
                    {{ __('Historial') }}
                </button>
            @endcan
            @can('update', $post)
                <a href="{{ route('posts.edit', $post) }}" class="sj-ui-link">
                    {{ __('Editar') }}
                </a>
            @endcan
            @can('delete', $post)
                <form action="{{ route('posts.destroy', $post) }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="sj-ui-link sj-ui-link--warn" onclick="return confirm(@js(__('¿Archivar este puesto? Las armas asignadas aquí quedarán sin ubicación interna activa.')))">
                        {{ __('Archivar') }}
                    </button>
                </form>
            @endcan
            @can('restore', $post)
                <form action="{{ route('posts.restore', $post) }}" method="POST" class="inline">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="sj-ui-link sj-ui-link--success" onclick="return confirm(@js(__('¿Reactivar este puesto?')))">
                        {{ __('Reactivar') }}
                    </button>
                </form>
            @endcan
        </td>
    </tr>
@empty
    <tr>
        <td colspan="5" class="px-3 py-6 text-center text-gray-500">
            {{ __('No hay puestos registrados.') }}
        </td>
    </tr>
@endforelse
