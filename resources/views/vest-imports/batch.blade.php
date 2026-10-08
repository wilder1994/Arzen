<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <p class="sj-section-header__eyebrow">{{ __('Cargas masivas') }} — {{ $selectedBatch->typeLabel() }}</p>
                <h2 class="sj-section-header__title">{{ __('Detalle del lote') }}</h2>
                <p class="sj-section-header__subtitle">{{ __('Revisa el resultado del lote y ejecuta o descarta cambios según el estado actual.') }}</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('vest-imports.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver a lotes') }}</a>
            </div>
        </div>
    </x-slot>

    @php
        $selectedBatchBadge = $selectedBatch->isExecuted()
            ? ['classes' => 'bg-green-100 text-green-700', 'label' => __('Lote ejecutado')]
            : ($selectedBatch->isProcessing()
                ? ['classes' => 'bg-blue-100 text-blue-700', 'label' => __('Lote en ejecución')]
                : ($selectedBatch->isFailed()
                    ? ['classes' => 'bg-rose-100 text-rose-700', 'label' => __('Lote con fallo')]
                    : ['classes' => 'bg-amber-100 text-amber-700', 'label' => __('Lote pendiente')]));
    @endphp

    <div
        class="py-8"
        data-vest-import-page
        data-selected-batch-status="{{ $selectedBatch->status }}"
        data-selected-batch-name="{{ $selectedBatch->source_name }}"
        data-selected-batch-process-url="{{ route('vest-imports.process', $selectedBatch) }}"
        data-selected-batch-status-url="{{ route('vest-imports.status', $selectedBatch) }}"
        data-selected-batch-redirect-url="{{ route('vest-imports.show', $selectedBatch) }}"
    >
        <div class="sj-page-shell sj-page-shell--wide pb-20">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            @if ($errors->has('batch'))
                <div class="mb-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700">{{ $errors->first('batch') }}</div>
            @endif

            <div class="sj-ui-card p-6">
                <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                    <div>
                        <div class="mb-2 flex flex-wrap gap-2">
                            <span class="rounded-full bg-slate-100 px-3 py-1 text-xs font-semibold text-slate-700">{{ $selectedBatch->typeLabel() }}</span>
                            <span class="rounded-full px-3 py-1 text-xs font-semibold {{ $selectedBatchBadge['classes'] }}">{{ $selectedBatchBadge['label'] }}</span>
                        </div>
                        <h3 class="sj-type-section text-gray-800">{{ $selectedBatch->source_name }}</h3>
                        <div class="mt-1 text-sm text-gray-500">{{ __('Subido por') }} {{ $selectedBatch->uploadedBy?->name ?? __('Sistema') }} {{ __('el') }} {{ $selectedBatch->created_at?->format('d/m/Y H:i') }}</div>
                        @if ($selectedBatch->isExecuted() && $selectedBatch->executed_at)
                            <div class="mt-1 text-sm text-gray-500">{{ __('Ejecutado por') }} {{ $selectedBatch->executedBy?->name ?? __('Sistema') }} {{ __('el') }} {{ $selectedBatch->executed_at->format('d/m/Y H:i') }}</div>
                        @elseif ($selectedBatch->isProcessing() && $selectedBatch->started_at)
                            <div class="mt-1 text-sm text-gray-500">{{ __('Iniciado por') }} {{ $selectedBatch->executedBy?->name ?? __('Sistema') }} {{ __('el') }} {{ $selectedBatch->started_at->format('d/m/Y H:i') }}</div>
                        @endif
                    </div>

                    @if ($selectedBatch->isDraft())
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('vest-imports.show', ['vestImportBatch' => $selectedBatch->id, 'preview' => 1]) }}" class="inline-flex items-center rounded-md border border-indigo-200 bg-white px-4 py-2 text-sm font-semibold text-indigo-700 transition hover:bg-indigo-50">{{ __('Revisar lote') }}</a>
                            <form method="POST" action="{{ route('vest-imports.execute', $selectedBatch) }}" class="vest-import-execute-form" data-batch-name="{{ $selectedBatch->source_name }}" data-start-url="{{ route('vest-imports.start', $selectedBatch) }}" data-process-url="{{ route('vest-imports.process', $selectedBatch) }}" data-status-url="{{ route('vest-imports.status', $selectedBatch) }}" data-redirect-url="{{ route('vest-imports.show', $selectedBatch) }}">
                                @csrf
                                <button type="submit" class="sj-ui-btn sj-ui-btn--primary disabled:cursor-not-allowed disabled:bg-gray-300" @disabled($selectedBatch->hasErrors())>{{ __('Ejecutar') }}</button>
                            </form>
                            <form method="POST" action="{{ route('vest-imports.discard', $selectedBatch) }}" class="vest-import-discard-form">
                                @csrf
                                <button type="submit" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar carga') }}</button>
                            </form>
                        </div>
                    @endif
                </div>

                <div class="mt-5 grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-lg bg-blue-50 px-4 py-3"><div class="text-xs font-semibold uppercase tracking-wide text-blue-700">{{ __('Crear') }}</div><div class="mt-1 sj-type-kpi text-blue-900">{{ $selectedBatch->create_count }}</div></div>
                    <div class="rounded-lg bg-amber-50 px-4 py-3"><div class="text-xs font-semibold uppercase tracking-wide text-amber-700">{{ __('Actualizar') }}</div><div class="mt-1 sj-type-kpi text-amber-900">{{ $selectedBatch->update_count }}</div></div>
                    <div class="rounded-lg bg-green-50 px-4 py-3"><div class="text-xs font-semibold uppercase tracking-wide text-green-700">{{ __('Sin cambios') }}</div><div class="mt-1 sj-type-kpi text-green-900">{{ $selectedBatch->no_change_count }}</div></div>
                    <div class="rounded-lg bg-rose-50 px-4 py-3"><div class="text-xs font-semibold uppercase tracking-wide text-rose-700">{{ __('Errores') }}</div><div class="mt-1 sj-type-kpi text-rose-900">{{ $selectedBatch->error_count }}</div></div>
                </div>

                @if ($selectedBatch->isProcessing())
                    <div class="mt-5 rounded-lg border border-blue-100 bg-blue-50 px-4 py-5 text-sm text-blue-800">{{ __('El lote se está ejecutando. Puedes seguir el avance en el panel de progreso.') }}</div>
                @elseif ($selectedBatch->isFailed())
                    <div class="mt-5 rounded-lg border border-rose-100 bg-rose-50 px-4 py-5 text-sm text-rose-700">{{ $selectedBatch->last_error ?: __('La ejecución del lote falló.') }}</div>
                @endif

                @if ($openPreview || $selectedBatch->isExecuted() || $selectedBatch->isFailed() || $selectedBatch->isDraft())
                    <div class="sj-table-wrap mt-6 overflow-x-auto">
                        <table class="sj-table sj-table--align-left min-w-full text-sm min-w-[1400px]">
                            <thead>
                                <tr>
                                    <th>{{ __('Fila') }}</th>
                                    <th>{{ __('Acción') }}</th>
                                    <th>{{ __('Cliente') }}</th>
                                    <th>{{ __('Puesto') }}</th>
                                    <th>{{ __('Cédula') }}</th>
                                    <th>{{ __('Empleado') }}</th>
                                    <th>{{ __('Marca') }}</th>
                                    <th>{{ __('Lote') }}</th>
                                    <th>{{ __('Serie') }}</th>
                                    <th>{{ __('Talla') }}</th>
                                    <th>{{ __('Fabricación') }}</th>
                                    <th>{{ __('Vencimiento') }}</th>
                                    <th>{{ __('Observación') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($selectedBatch->rows as $row)
                                    @php
                                        $raw = $row->raw_payload ?? [];
                                        $normalized = $row->normalized_payload ?? [];
                                        $formatDate = static function ($value) {
                                            if (empty($value)) {
                                                return '-';
                                            }

                                            try {
                                                return \Illuminate\Support\Carbon::parse($value)->format('d/m/Y');
                                            } catch (\Throwable) {
                                                return (string) $value;
                                            }
                                        };
                                        $clientLabel = $normalized['client_name']
                                            ?? $normalized['client_legal_name']
                                            ?? $raw['client_name']
                                            ?? $raw['client_legal_name']
                                            ?? ($row->client?->name ?? '-');
                                    @endphp
                                    <tr class="{{ $row->action === 'error' ? 'bg-rose-50' : ($row->action === 'create' ? 'bg-blue-50' : ($row->action === 'update' ? 'bg-amber-50' : 'bg-green-50')) }}">
                                        <td class="px-3 py-2 font-medium text-gray-800">{{ $row->row_number }}</td>
                                        <td class="px-3 py-2">{{ $row->actionLabel() }}</td>
                                        <td class="px-3 py-2">{{ $clientLabel ?: '-' }}</td>
                                        <td class="px-3 py-2">{{ $normalized['post_name'] ?? $raw['post_name'] ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $normalized['worker_document'] ?? $raw['worker_document'] ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $normalized['worker_name'] ?? $raw['worker_name'] ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $normalized['brand'] ?? $raw['brand'] ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $normalized['batch'] ?? $raw['batch'] ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $normalized['serial_number'] ?? $raw['serial_number'] ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $normalized['size'] ?? $raw['size'] ?? '-' }}</td>
                                        <td class="px-3 py-2">{{ $formatDate($normalized['manufactured_at'] ?? $raw['manufactured_at'] ?? null) }}</td>
                                        <td class="px-3 py-2">{{ $formatDate($normalized['expires_at'] ?? $raw['expires_at'] ?? null) }}</td>
                                        <td class="px-3 py-2 text-gray-700">
                                            @if (!empty($row->errors))
                                                <div class="text-sm text-rose-700">{{ implode(' ', $row->errors) }}</div>
                                            @else
                                                <div>{{ $row->summary ?: '-' }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div id="vest-import-execution-panel" class="{{ $selectedBatch->isProcessing() ? '' : 'hidden' }} fixed bottom-6 right-6 z-[5500] w-full max-w-md rounded-2xl border border-slate-200 bg-white p-5 shadow-2xl">
                <div class="text-base font-semibold text-slate-900">{{ __('Ejecutando lote') }}</div>
                <div id="vest-import-execution-subtitle" class="mt-1 text-sm text-slate-500">{{ $selectedBatch->source_name }}</div>
                <div class="mt-4 h-3 overflow-hidden rounded-full bg-slate-200"><div id="vest-import-execution-fill" class="h-full w-0 rounded-full bg-indigo-600"></div></div>
                <div class="mt-3 flex items-center justify-between text-sm text-slate-600"><span id="vest-import-execution-left">0 / 0 {{ __('filas') }}</span><span id="vest-import-execution-right">{{ __('Calculando ETA...') }}</span></div>
                <div class="mt-4 grid grid-cols-3 gap-3 text-center text-sm">
                    <div class="rounded-xl bg-slate-50 p-3"><div class="text-slate-500">{{ __('Procesadas') }}</div><div id="vest-import-execution-processed" class="mt-1 sj-type-kpi text-slate-900">0</div></div>
                    <div class="rounded-xl bg-slate-50 p-3"><div class="text-slate-500">{{ __('Correctas') }}</div><div id="vest-import-execution-successful" class="mt-1 sj-type-kpi text-slate-900">0</div></div>
                    <div class="rounded-xl bg-slate-50 p-3"><div class="text-slate-500">{{ __('Fallidas') }}</div><div id="vest-import-execution-failed" class="mt-1 sj-type-kpi text-slate-900">0</div></div>
                </div>
                <div id="vest-import-execution-message" class="mt-4 text-sm text-slate-600">{{ __('Esperando inicio...') }}</div>
            </div>
        </div>
    </div>
</x-app-layout>

<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const page = document.querySelector('[data-vest-import-page]');
    const panel = document.getElementById('vest-import-execution-panel');
    const fill = document.getElementById('vest-import-execution-fill');
    const left = document.getElementById('vest-import-execution-left');
    const right = document.getElementById('vest-import-execution-right');
    const processed = document.getElementById('vest-import-execution-processed');
    const successful = document.getElementById('vest-import-execution-successful');
    const failed = document.getElementById('vest-import-execution-failed');
    const message = document.getElementById('vest-import-execution-message');
    const forms = Array.from(document.querySelectorAll('.vest-import-execute-form'));
    const discardButtons = Array.from(document.querySelectorAll('.vest-import-discard-form button[type="submit"]'));

    if (!page || !panel || !fill || !left || !right || !processed || !successful || !failed || !message) return;

    const requestHeaders = {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
    };

    const state = {
        running: false,
        statusUrl: page.dataset.selectedBatchStatusUrl || '',
        processUrl: page.dataset.selectedBatchProcessUrl || '',
        redirectUrl: page.dataset.selectedBatchRedirectUrl || '',
        sourceName: page.dataset.selectedBatchName || 'Lote',
    };

    const formatDuration = (seconds) => {
        if (!seconds || seconds <= 0) return 'Calculando...';
        const mins = Math.floor(seconds / 60);
        const secs = Math.round(seconds % 60);
        if (mins <= 0) return `${secs}s`;
        if (secs <= 0) return `${mins}m`;
        return `${mins}m ${secs}s`;
    };

    const setRunning = (running) => {
        state.running = running;
        panel.classList.toggle('hidden', !running);
        forms.forEach((form) => {
            const button = form.querySelector('button[type="submit"]');
            if (button) button.disabled = running || button.hasAttribute('data-force-disabled');
        });
        discardButtons.forEach((button) => button.disabled = running);
    };

    const updateUrlsFromPayload = (payload = {}) => {
        if (payload.status_url) state.statusUrl = payload.status_url;
        if (payload.process_url) state.processUrl = payload.process_url;
        if (payload.redirect_url) state.redirectUrl = payload.redirect_url;
    };

    const renderProgress = (progress = {}) => {
        const total = Number(progress.total_rows || 0);
        const processedRows = Number(progress.processed_rows || 0);
        const successfulRows = Number(progress.successful_rows || 0);
        const failedRows = Number(progress.failed_rows || 0);
        const percentage = total > 0 ? Math.round((processedRows / total) * 100) : 0;

        fill.style.width = `${percentage}%`;
        left.textContent = `${processedRows} / ${total} filas`;
        right.textContent = progress.status === 'processing'
            ? `ETA ${formatDuration(progress.eta_seconds)}`
            : (progress.status === 'executed' ? 'Completado' : 'Proceso detenido');
        processed.textContent = processedRows;
        successful.textContent = successfulRows;
        failed.textContent = failedRows;
        message.textContent = progress.status === 'failed'
            ? (progress.last_error || 'La ejecución del lote se detuvo.')
            : (progress.status === 'executed'
                ? 'Carga completada. Redirigiendo...'
                : 'El lote se está procesando. Puedes cambiar de pestaña mientras termina.');
    };

    const readJson = async (response) => {
        try {
            return await response.json();
        } catch {
            return {};
        }
    };

    const finishIfTerminal = (progress = {}) => {
        if (progress.status === 'executed') {
            window.setTimeout(() => { window.location.href = state.redirectUrl; }, 800);
            return true;
        }

        if (progress.status === 'failed') {
            setRunning(false);
            return true;
        }

        return false;
    };

    const pollStatus = async () => {
        if (!state.statusUrl) return;

        try {
            const response = await fetch(state.statusUrl, { headers: requestHeaders });
            if (!response.ok) {
                window.setTimeout(pollStatus, 2000);
                return;
            }

            const payload = await readJson(response);
            updateUrlsFromPayload(payload);
            renderProgress(payload.progress);

            if (finishIfTerminal(payload.progress)) return;

            if (payload.progress?.status === 'processing') {
                window.setTimeout(state.processUrl ? processNextChunk : pollStatus, 800);
                return;
            }

            setRunning(false);
        } catch {
            window.setTimeout(pollStatus, 2000);
        }
    };

    const processNextChunk = async () => {
        if (!state.processUrl) {
            pollStatus();
            return;
        }

        try {
            const response = await fetch(state.processUrl, {
                method: 'POST',
                headers: requestHeaders,
            });
            const payload = await readJson(response);

            if (!response.ok) {
                message.textContent = payload.message || 'No se pudo continuar la ejecución del lote.';
                setRunning(false);
                return;
            }

            updateUrlsFromPayload(payload);
            renderProgress(payload.progress);

            if (finishIfTerminal(payload.progress)) return;

            if (payload.progress?.status === 'processing') {
                window.setTimeout(processNextChunk, 150);
                return;
            }

            setRunning(false);
        } catch {
            window.setTimeout(pollStatus, 2000);
        }
    };

    const startExecution = async (form) => {
        setRunning(true);
        message.textContent = 'Iniciando ejecución del lote...';

        try {
            const response = await fetch(form.dataset.startUrl, {
                method: 'POST',
                headers: requestHeaders,
            });
            const payload = await readJson(response);

            if (!response.ok) {
                message.textContent = payload.message || 'No se pudo iniciar la ejecución del lote.';
                setRunning(false);
                return;
            }

            updateUrlsFromPayload({
                ...payload,
                process_url: form.dataset.processUrl || payload.process_url,
            });
            renderProgress(payload.progress);

            if (finishIfTerminal(payload.progress)) return;

            if (payload.progress?.status === 'processing') {
                window.setTimeout(processNextChunk, 150);
                return;
            }

            setRunning(false);
        } catch {
            message.textContent = 'No se pudo iniciar la ejecución del lote.';
            setRunning(false);
        }
    };

    forms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            event.preventDefault();
            if (state.running) return;
            startExecution(form);
        });
    });

    if (page.dataset.selectedBatchStatus === 'processing') {
        setRunning(true);
        window.setTimeout(processNextChunk, 150);
    }
})();
</script>
