<div class="sj-table-wrap max-h-[min(65vh,620px)] overflow-auto">
    <table class="sj-table sj-table--align-left sj-table--sticky-head min-w-full text-sm">
        <thead>
            <tr>
                <th>{{ __('Fecha') }}</th>
                <th>{{ __('Arma') }}</th>
                <th>{{ __('Tipo') }}</th>
                <th>{{ __('Modalidad') }}</th>
                <th>{{ __('Estado') }}</th>
                <th>{{ __('Cliente') }}</th>
                <th>{{ __('Resumen') }}</th>
                <th>{{ __('Seguimiento') }}</th>
                <th>{{ __('Adjunto') }}</th>
                <th>{{ __('Expediente') }}</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($incidents as $incident)
                @php
                    $updateCount = $incident->updates->reject(
                        fn (App\Models\WeaponIncidentUpdate $update) => $update->event_type === App\Models\WeaponIncidentUpdate::EVENT_REPORTED
                    )->count();
                    $statusTone = match ($incident->status) {
                        App\Models\WeaponIncident::STATUS_OPEN => 'danger',
                        App\Models\WeaponIncident::STATUS_IN_PROGRESS => 'warning',
                        App\Models\WeaponIncident::STATUS_RESOLVED => 'ok',
                        App\Models\WeaponIncident::STATUS_CANCELLED => 'neutral',
                        default => 'notice',
                    };
                @endphp
                <tr
                    class="hover:bg-gray-50/80"
                    x-show="!q.trim() || $el.innerText.toLowerCase().includes(q.trim().toLowerCase())"
                >
                    <td class="px-3 py-2">{{ $incident->event_at?->format('Y-m-d H:i') ?? '-' }}</td>
                    <td class="px-3 py-2">
                        <a href="{{ route('weapons.show', $incident->weapon) }}" class="text-indigo-600 hover:text-indigo-900">
                            {{ $incident->weapon?->serial_number ?? '-' }}
                        </a>
                    </td>
                    <td class="px-3 py-2">{{ $incident->type?->name ?? '-' }}</td>
                    <td class="px-3 py-2">{{ $incident->modality?->name ?? '-' }}</td>
                    <td class="px-3 py-2">
                        <span class="weapon-incident-status">
                            <span class="weapon-incident-status__dot weapon-incident-status__dot--{{ $statusTone }}"></span>
                            <span>{{ $statusOptions[$incident->status] ?? $incident->status }}</span>
                        </span>
                    </td>
                    <td class="px-3 py-2">{{ $incident->weapon?->activeClientAssignment?->client?->name ?? '-' }}</td>
                    <td class="px-3 py-2">{{ $incident->observation ?? ($incident->note ?? '-') }}</td>
                    <td class="px-3 py-2">
                        <div class="text-slate-700">{{ $updateCount }} {{ __('hitos') }}</div>
                        <div class="text-xs text-slate-500">
                            {{ $incident->latestUpdate?->eventTypeLabel() ?? __('Solo reporte inicial') }}
                            @if ($incident->latestActivityAt())
                                &middot; {{ $incident->latestActivityAt()->format('Y-m-d H:i') }}
                            @endif
                        </div>
                    </td>
                    <td class="px-3 py-2">
                        @if ($incident->attachmentFile)
                            <a href="{{ route('weapon-incidents.attachment', $incident) }}" class="text-indigo-600 hover:text-indigo-900">
                                {{ __('Descargar') }}
                            </a>
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-3 py-2">
                        <button
                            type="button"
                            class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm"
                            data-open-modal="incident-case-{{ $incident->id }}"
                        >
                            @can('update', $incident)
                                {{ __('Gestionar') }}
                            @else
                                {{ __('Ver caso') }}
                            @endcan
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="px-3 py-6 text-center text-gray-500">
                        {{ __('No hay novedades con los filtros actuales.') }}
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
