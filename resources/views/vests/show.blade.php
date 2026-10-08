<x-app-layout header-compact>
    <x-slot name="header">
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
            <div class="flex flex-wrap items-center gap-2">
                <h1 class="sj-type-page text-gray-900">{{ $vest->serial_number }}</h1>
                <span class="inline-flex rounded-full px-2.5 py-0.5 text-xs font-semibold {{ $alert['badge_class'] }}">{{ $alert['state'] }}</span>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @can('update', $vest)
                    <a href="{{ route('vests.edit', $vest) }}" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm">{{ __('Editar') }}</a>
                @endcan
                <a href="{{ route('vests.index', request()->only('alert', 'q', 'client_id')) }}" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm">{{ __('Volver al listado') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="sj-page-shell sj-page-shell--wide">
            @if (session('status'))
                <div class="rounded-lg bg-green-50 border border-green-200 p-4 mb-6 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 lg:items-stretch">
                <section class="sj-ui-card flex h-full flex-col p-4">
                    <h3 class="mb-3 shrink-0 sj-type-eyebrow text-gray-500">{{ __('Datos del chaleco') }}</h3>
                    <dl class="grid flex-1 grid-cols-1 gap-2.5 text-sm sm:grid-cols-2 sm:content-start">
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Marca') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $vest->brand ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Lote') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $vest->batch ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Talla') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $vest->size ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Fabricación') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $vest->manufactured_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Vencimiento') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $vest->expires_at?->format('d/m/Y') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Días restantes') }}</dt>
                            <dd class="mt-0.5 font-medium {{ $alert['text_class'] }}">{{ $alert['days_remaining'] ?? '—' }}</dd>
                        </div>
                    </dl>
                </section>

                <section class="sj-ui-card flex h-full flex-col p-4">
                    <h3 class="sj-type-eyebrow mb-3 shrink-0 text-gray-500">{{ __('Asignación') }}</h3>
                    <dl class="grid flex-1 grid-cols-1 gap-2.5 text-sm sm:grid-cols-2 sm:content-start">
                        <div class="sm:col-span-2">
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Cliente') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $vest->client?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Trabajador') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $vest->worker?->name ?? __('Sin asignar') }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Cédula') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $vest->worker?->document ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Puesto') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $vest->post?->name ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium uppercase tracking-wide text-gray-500">{{ __('Responsable dispositivo') }}</dt>
                            <dd class="mt-0.5 font-medium text-gray-900">{{ $vest->displayDeviceResponsible() ?: '—' }}</dd>
                        </div>
                    </dl>
                </section>
            </div>

            @if ($vest->notes)
                <section class="mt-4 sj-ui-card p-4">
                    <h3 class="sj-type-eyebrow mb-2 text-gray-500">{{ __('Notas') }}</h3>
                    <p class="text-sm text-gray-700 whitespace-pre-line">{{ $vest->notes }}</p>
                </section>
            @endif

            <div class="mt-4">
                @include('vests.partials.photos')
            </div>
        </div>
    </div>
</x-app-layout>
