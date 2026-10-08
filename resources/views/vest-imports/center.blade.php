@push('styles')
    <style>
        .vest-import-progress { display: none; gap: 0.75rem; border: 1px solid #dbeafe; border-radius: 0.9rem; background: #eff6ff; padding: 1rem; }
        .vest-import-progress.is-visible { display: grid; }
        .vest-import-progress__top { display: flex; align-items: center; justify-content: space-between; gap: 1rem; }
        .vest-import-progress__title { color: #1e3a8a; font-size: var(--sj-fs-lg); font-weight: 700; }
        .vest-import-progress__meta { color: #475569; font-size: var(--sj-fs-md); font-weight: 600; white-space: nowrap; }
        .vest-import-progress__bar { width: 100%; height: 0.75rem; overflow: hidden; border-radius: 999px; background: rgba(148, 163, 184, 0.28); }
        .vest-import-progress__fill { height: 100%; width: 0%; border-radius: inherit; background: linear-gradient(90deg, #2563eb 0%, #0ea5e9 100%); transition: width 0.25s ease; }
        .vest-import-progress__fill.is-indeterminate { width: 35%; animation: vest-import-progress-slide 1.25s ease-in-out infinite; }
        .vest-import-progress__details { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 0.75rem; color: #475569; font-size: var(--sj-fs-md); }

        @keyframes vest-import-progress-slide {
            0% { transform: translateX(-120%); }
            100% { transform: translateX(320%); }
        }
    </style>
@endpush

<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Subir chalecos') }}</h2>
                <p class="sj-section-header__subtitle">{{ __('Carga masiva desde Excel con validación previa') }}</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('vest-imports.templates.vest') }}" class="sj-ui-btn sj-ui-btn--ghost">
                    {{ __('Descargar formato') }}
                </a>
                <button
                    type="button"
                    x-data=""
                    x-on:click="$dispatch('open-modal', 'vest-import-upload')"
                    class="sj-ui-btn sj-ui-btn--primary"
                >
                    {{ __('Subir Excel') }}
                </button>
                <a href="{{ route('vests.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al inventario') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide pb-20">
            @if (session('status'))
                <div class="mb-4 rounded-lg bg-green-50 px-4 py-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            @if ($errors->has('document') || $errors->has('batch'))
                <div class="mb-4 rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first('document') ?: $errors->first('batch') }}
                </div>
            @endif

            <section>
                <div class="mb-4 flex items-end justify-between gap-3">
                    <div>
                        <div class="text-sm font-semibold uppercase tracking-[0.18em] text-slate-500">{{ __('Historial') }}</div>
                        <h3 class="sj-type-section text-gray-800">{{ __('Lotes ejecutados') }}</h3>
                    </div>
                    <div class="text-sm text-gray-500">{{ $batches->count() }} {{ __('lote(s)') }}</div>
                </div>

                @if ($batches->isNotEmpty())
                    <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-3">
                        @foreach ($batches as $batch)
                            <a href="{{ route('vest-imports.show', $batch) }}" class="sj-ui-card sj-ui-card--link">
                                <div class="truncate text-base font-semibold text-gray-800">{{ $batch->source_name }}</div>
                                <div class="mt-1 text-sm text-gray-500">{{ $batch->created_at?->format('d/m/Y H:i') }}</div>
                                <div class="mt-4 grid grid-cols-2 gap-3 text-sm">
                                    <div><span class="text-xs uppercase text-gray-400">{{ __('Crear') }}</span><div class="font-semibold text-blue-700">{{ $batch->create_count }}</div></div>
                                    <div><span class="text-xs uppercase text-gray-400">{{ __('Errores') }}</span><div class="font-semibold text-rose-700">{{ $batch->error_count }}</div></div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="sj-ui-card sj-ui-card--dashed text-gray-500">{{ __('Aún no hay lotes ejecutados.') }}</div>
                @endif
            </section>
        </div>
    </div>

    <x-modal name="vest-import-upload" :show="$errors->has('document')" maxWidth="2xl" focusable>
        <div id="vest-import-upload-modal" class="p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h3 class="sj-type-section text-gray-800">{{ __('Subir documento de chalecos') }}</h3>
                    <p class="mt-1 text-sm text-gray-500">{{ __('Validación previa antes de crear o actualizar chalecos.') }}</p>
                </div>
                <button
                    type="button"
                    x-data=""
                    x-on:click.prevent="$dispatch('close-modal', 'vest-import-upload')"
                    class="rounded-md px-2 py-1 text-sm text-gray-500 transition hover:bg-gray-100 hover:text-gray-700"
                >
                    {{ __('Cerrar') }}
                </button>
            </div>

            <form id="vest-import-upload-form" method="POST" action="{{ route('vest-imports.preview') }}" enctype="multipart/form-data" class="mt-6 space-y-4">
                @csrf
                <input id="vest-import-document" type="file" name="document" accept=".xlsx,.csv,.txt" class="hidden" required>

                <label
                    for="vest-import-document"
                    id="vest-import-dropzone"
                    class="flex min-h-[15rem] cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50 px-6 py-10 text-center transition hover:border-indigo-300 hover:bg-indigo-50/40"
                >
                    <div class="rounded-full bg-white p-4 text-indigo-600 shadow-sm">
                        <svg class="h-8 w-8" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                            <path d="M12 16V4" />
                            <path d="M8 8l4-4 4 4" />
                            <path d="M4 16.5v1.5A2 2 0 006 20h12a2 2 0 002-2v-1.5" />
                        </svg>
                    </div>
                    <div id="vest-import-dropzone-title" class="mt-4 text-base font-semibold text-gray-800">{{ __('Arrastra el documento aquí') }}</div>
                    <div class="mt-2 text-sm text-gray-500">{{ __('También puedes pegar el archivo con Ctrl + V o seleccionarlo manualmente.') }}</div>
                    <div class="mt-5 inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm">
                        {{ __('Seleccionar de este equipo') }}
                    </div>
                    <div id="vest-import-file-name" class="mt-4 text-sm font-medium text-indigo-700"></div>
                </label>

                <div id="vest-import-upload-error" class="hidden rounded-lg bg-rose-50 px-4 py-3 text-sm text-rose-700"></div>

                <div id="vest-import-upload-progress" class="vest-import-progress" aria-live="polite">
                    <div class="vest-import-progress__top">
                        <div id="vest-import-upload-progress-title" class="vest-import-progress__title">{{ __('Subiendo archivo...') }}</div>
                        <div id="vest-import-upload-progress-meta" class="vest-import-progress__meta">0%</div>
                    </div>
                    <div class="vest-import-progress__bar">
                        <div id="vest-import-upload-progress-fill" class="vest-import-progress__fill"></div>
                    </div>
                    <div class="vest-import-progress__details">
                        <span id="vest-import-upload-progress-detail-left">{{ __('Esperando archivo') }}</span>
                        <span id="vest-import-upload-progress-detail-right"></span>
                    </div>
                </div>

                <x-input-error :messages="$errors->get('document')" class="mt-2" />

                <div class="rounded-2xl border border-slate-200 bg-slate-50 p-4 text-sm text-slate-600">
                    <div class="font-semibold text-slate-800">{{ __('Columnas soportadas') }}</div>
                    <p class="mt-2">{{ __('cédula, nombres, cargo, cliente, puesto, marca, lote, serie, fechas, talla, responsable dispositivo.') }}</p>
                    <p class="mt-2">{{ __('El cliente y el puesto deben existir previamente en el sistema (coincidencia exacta por nombre). El trabajador se valida por cédula.') }}</p>
                    @if (auth()->user()?->isAdmin())
                        <p class="mt-2 text-amber-800">{{ __('Como administrador, la columna Cliente o Razón social es obligatoria en cada fila.') }}</p>
                    @else
                        <p class="mt-2 text-emerald-800">{{ __('Como responsable, el cliente se infiere de su cartera; la columna Cliente es opcional.') }}</p>
                    @endif
                </div>

                <div class="flex items-center justify-end gap-3">
                    <button
                        type="button"
                        x-data=""
                        x-on:click.prevent="$dispatch('close-modal', 'vest-import-upload')"
                        class="sj-ui-btn sj-ui-btn--ghost"
                    >
                        {{ __('Cancelar') }}
                    </button>
                    <button type="submit" id="vest-import-submit" class="sj-ui-btn sj-ui-btn--primary" disabled>
                        {{ __('Validar archivo') }}
                    </button>
                </div>
            </form>
        </div>
    </x-modal>
@push('scripts')
<script>
(() => {
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const input = document.getElementById('vest-import-document');
    const dropzone = document.getElementById('vest-import-dropzone');
    const fileName = document.getElementById('vest-import-file-name');
    const submit = document.getElementById('vest-import-submit');
    const uploadModal = document.getElementById('vest-import-upload-modal');
    const uploadForm = document.getElementById('vest-import-upload-form');
    const uploadError = document.getElementById('vest-import-upload-error');
    const uploadProgress = document.getElementById('vest-import-upload-progress');
    const uploadProgressTitle = document.getElementById('vest-import-upload-progress-title');
    const uploadProgressMeta = document.getElementById('vest-import-upload-progress-meta');
    const uploadProgressFill = document.getElementById('vest-import-upload-progress-fill');
    const uploadDetailLeft = document.getElementById('vest-import-upload-progress-detail-left');
    const uploadDetailRight = document.getElementById('vest-import-upload-progress-detail-right');

    if (!input || !dropzone || !fileName || !submit || !uploadModal || !uploadForm) {
        return;
    }

    const formatDuration = (seconds) => {
        if (!seconds || seconds <= 0) {
            return '{{ __('Calculando...') }}';
        }

        const mins = Math.floor(seconds / 60);
        const secs = Math.round(seconds % 60);

        if (mins <= 0) {
            return `${secs}s`;
        }

        if (secs <= 0) {
            return `${mins}m`;
        }

        return `${mins}m ${secs}s`;
    };

    const isModalVisible = () => uploadModal.offsetParent !== null;

    const setUploadError = (message = '') => {
        if (!uploadError) {
            return;
        }

        uploadError.textContent = message;
        uploadError.classList.toggle('hidden', !message);
    };

    const setUploadProgress = ({
        visible = false,
        title = '{{ __('Subiendo archivo...') }}',
        percent = null,
        left = '',
        right = '',
        indeterminate = false,
    }) => {
        uploadProgress?.classList.toggle('is-visible', visible);

        if (!uploadProgressFill || !uploadProgressTitle || !uploadProgressMeta || !uploadDetailLeft || !uploadDetailRight) {
            return;
        }

        uploadProgressTitle.textContent = title;
        uploadProgressMeta.textContent = percent === null ? '...' : `${percent}%`;
        uploadDetailLeft.textContent = left;
        uploadDetailRight.textContent = right;
        uploadProgressFill.classList.toggle('is-indeterminate', indeterminate);
        uploadProgressFill.style.width = percent === null ? '35%' : `${percent}%`;
    };

    const setFile = (file) => {
        if (!file) {
            input.value = '';
            fileName.textContent = '';
            submit.disabled = true;
            return;
        }

        const transfer = new DataTransfer();
        transfer.items.add(file);
        input.files = transfer.files;
        fileName.textContent = file.name;
        submit.disabled = false;
        setUploadError('');
    };

    const parseErrorMessage = (payload) => {
        if (!payload) {
            return '{{ __('No se pudo procesar el archivo seleccionado.') }}';
        }

        if (payload.errors) {
            const firstKey = Object.keys(payload.errors)[0];

            if (firstKey && payload.errors[firstKey]?.length) {
                return payload.errors[firstKey][0];
            }
        }

        return payload.message || '{{ __('No se pudo procesar el archivo seleccionado.') }}';
    };

    const resetModalState = () => {
        setFile(null);
        setUploadError('');
        setUploadProgress({ visible: false });
    };

    window.addEventListener('open-modal', (event) => {
        if (event.detail !== 'vest-import-upload') {
            return;
        }

        resetModalState();
    });

    input.addEventListener('change', () => {
        const file = input.files && input.files[0] ? input.files[0] : null;
        setFile(file);
    });

    ['dragenter', 'dragover'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.add('border-indigo-400', 'bg-indigo-50');
        });
    });

    ['dragleave', 'dragend', 'drop'].forEach((eventName) => {
        dropzone.addEventListener(eventName, (event) => {
            event.preventDefault();
            dropzone.classList.remove('border-indigo-400', 'bg-indigo-50');
        });
    });

    dropzone.addEventListener('drop', (event) => {
        const file = event.dataTransfer && event.dataTransfer.files ? event.dataTransfer.files[0] : null;

        if (file) {
            setFile(file);
        }
    });

    window.addEventListener('paste', (event) => {
        if (!isModalVisible()) {
            return;
        }

        const items = event.clipboardData ? Array.from(event.clipboardData.items || []) : [];
        const fileItem = items.find((item) => item.kind === 'file');
        const file = fileItem ? fileItem.getAsFile() : null;

        if (file) {
            event.preventDefault();
            setFile(file);
        }
    });

    uploadForm.addEventListener('submit', (event) => {
        event.preventDefault();

        const file = input.files && input.files[0] ? input.files[0] : null;

        if (!file) {
            return;
        }

        setUploadError('');
        submit.disabled = true;

        const xhr = new XMLHttpRequest();
        const startedAt = Date.now();

        xhr.open('POST', uploadForm.action);
        xhr.responseType = 'json';
        xhr.setRequestHeader('Accept', 'application/json');
        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);

        xhr.upload.addEventListener('progress', (progressEvent) => {
            if (!progressEvent.lengthComputable) {
                setUploadProgress({
                    visible: true,
                    percent: null,
                    left: '{{ __('Transferencia en curso') }}',
                    right: '{{ __('Calculando...') }}',
                    indeterminate: true,
                });
                return;
            }

            const progress = Math.max(1, Math.min(100, Math.round((progressEvent.loaded / progressEvent.total) * 100)));
            const elapsedSeconds = Math.max(1, (Date.now() - startedAt) / 1000);
            const bytesPerSecond = progressEvent.loaded / elapsedSeconds;
            const remainingSeconds = bytesPerSecond > 0 ? (progressEvent.total - progressEvent.loaded) / bytesPerSecond : 0;

            setUploadProgress({
                visible: true,
                percent: progress,
                left: file.name,
                right: `{{ __('Faltan') }} ${formatDuration(remainingSeconds)}`,
            });
        });

        xhr.onerror = () => {
            submit.disabled = false;
            setUploadProgress({ visible: false });
            setUploadError('{{ __('No se pudo subir el archivo. Verifica la conexión e intenta de nuevo.') }}');
        };

        xhr.onload = () => {
            if (xhr.status >= 200 && xhr.status < 300) {
                setUploadProgress({
                    visible: true,
                    title: '{{ __('Validando archivo...') }}',
                    percent: null,
                    left: '{{ __('El documento ya fue cargado') }}',
                    right: '{{ __('Procesando previsualización...') }}',
                    indeterminate: true,
                });

                const payload = xhr.response || {};
                window.location.href = payload.redirect_url || uploadForm.action;
                return;
            }

            submit.disabled = false;
            setUploadProgress({ visible: false });
            setUploadError(parseErrorMessage(xhr.response));
        };

        xhr.send(new FormData(uploadForm));
    });
})();
</script>
@endpush

</x-app-layout>
