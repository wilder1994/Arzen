<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Revista armas') }}</h2>
                <p class="sj-section-header__subtitle">{{ __('Revise fotos en staging y asigne acceso temporal a colaboradores de campo.') }}</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('revista-armas.temporary-users.index') }}" class="sj-ui-btn sj-ui-btn--ghost">
                    {{ __('Usuarios temporales') }}
                </a>
                <button type="button" id="revista-open-assign" class="sj-ui-btn sj-ui-btn--primary">
                    {{ __('Asignar acceso temporal') }}
                </button>
            </div>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="sj-page-shell sj-page-shell--wide space-y-4">
            @if (session('status'))
                <div class="rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
            @endif

            @if ($activeGrantMissing && $selectedTemporaryUserId)
                <div class="rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    {{ __('Este usuario temporal no tiene un acceso vigente. Se muestran las armas del último acceso asignado para revisar las fotos subidas. Asigne un nuevo acceso solo si el colaborador debe volver a capturar o subir más fotos.') }}
                </div>
            @endif

            <form method="GET" class="sj-ui-card sj-ui-filter-bar p-4">
                <div class="sj-ui-filter-bar__fields">
                    <div class="sj-ui-field min-w-[12rem] flex-1 basis-[14rem] sm:max-w-[22rem]">
                        <label for="temporary_photo_user_id" class="sj-ui-field__label">{{ __('Usuario temporal (columna Realizado)') }}</label>
                        <select name="temporary_photo_user_id" id="temporary_photo_user_id" class="sj-ui-field__control">
                            <option value="">{{ __('Seleccione...') }}</option>
                            @foreach ($temporaryUsers as $tu)
                                <option value="{{ $tu->id }}" @selected($selectedTemporaryUserId === $tu->id)>
                                    {{ $tu->name }} ({{ $tu->email }})@if ($tu->is_shared) — {{ __('Compartido') }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="sj-ui-field min-w-[12rem] flex-[2] basis-[16rem]">
                        <label for="revista-table-filter" class="sj-ui-field__label">{{ __('Buscar armas') }}</label>
                        <input
                            id="revista-table-filter"
                            type="search"
                            autocomplete="off"
                            class="sj-ui-field__control"
                            placeholder="{{ __('Serie, código, marca, calibre, cliente, permiso...') }}"
                        >
                    </div>
                    <div class="sj-ui-filter-bar__actions">
                        <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Filtrar') }}</button>
                    </div>
                </div>
                <p id="revista-table-filter-count" class="mt-2 text-xs text-slate-500"></p>
            </form>

            <div class="sj-ui-card overflow-hidden">
                <div class="sj-table-wrap overflow-x-auto">
                    <table class="sj-table min-w-full text-sm">
                        <thead>
                            <tr>
                                <th>{{ __('Cliente') }}</th>
                                <th>{{ __('Tipo') }}</th>
                                <th>{{ __('Marca') }}</th>
                                <th>{{ __('Serie') }}</th>
                                <th>{{ __('Calibre') }}</th>
                                <th>{{ __('Tipo permiso') }}</th>
                                <th>{{ __('Nº permiso') }}</th>
                                <th>{{ __('Vencimiento') }}</th>
                                <th>{{ __('Realizado') }}</th>
                                <th>{{ __('Acciones') }}</th>
                            </tr>
                        </thead>
                        <tbody id="revista-table-body">
                            @forelse ($rows as $row)
                                @php($weapon = $row['weapon'])
                                @php($done = $selectedTemporaryUserId ? ($row['completions'][$selectedTemporaryUserId] ?? false) : false)
                                @php(
                                    $tableSearchHaystack = mb_strtolower(implode(' ', array_filter([
                                        $weapon->internal_code,
                                        $weapon->serial_number,
                                        $weapon->weapon_type,
                                        $weapon->caliber,
                                        $weapon->brand,
                                        $weapon->permit_type,
                                        $weapon->permit_number,
                                        $weapon->permit_expires_at?->format('Y-m-d'),
                                        $weapon->operationalDisplayClient()?->name,
                                        $weapon->operationalDisplayResponsible()?->name,
                                        $weapon->activePostAssignment?->post?->name,
                                        $weapon->activeWorkerAssignment?->worker?->name,
                                    ], fn ($v) => filled($v))), 'UTF-8')
                                )
                                <tr class="revista-table-row" data-search="{{ $tableSearchHaystack }}">
                                    <td class="max-w-[14rem] truncate px-3 py-2" title="{{ $weapon->operationalDisplayClient()?->name ?? __('Sin destino') }}">
                                        {{ $weapon->operationalDisplayClient()?->name ?? __('Sin destino') }}
                                    </td>
                                    <td class="px-3 py-2">{{ $weapon->weapon_type ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $weapon->brand ?? '—' }}</td>
                                    <td class="px-3 py-2 font-medium">{{ $weapon->serial_number ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $weapon->caliber ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $weapon->permit_type ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $weapon->permit_number ?? '—' }}</td>
                                    <td class="px-3 py-2">{{ $weapon->permit_expires_at?->format('Y-m-d') ?? '—' }}</td>
                                    <td class="px-3 py-2 text-center">
                                        @if ($selectedTemporaryUserId)
                                            @if ($done)
                                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.55)]" title="{{ __('Completo') }}">✓</span>
                                            @else
                                                <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-red-500/15 text-red-500 shadow-[0_0_10px_rgba(239,68,68,0.45)]" title="{{ __('Pendiente') }}">✕</span>
                                            @endif
                                        @else
                                            <span class="text-slate-400">—</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-right">
                                        @if ($selectedTemporaryUserId)
                                            <button
                                                type="button"
                                                class="revista-review-btn rounded-lg border border-slate-300 px-2.5 py-1 text-xs font-semibold text-[#0b6fb6] hover:bg-slate-50"
                                                data-review-url="{{ route('revista-armas.review', [$weapon, $selectedTemporaryUserId]) }}"
                                                data-approve-url="{{ route('revista-armas.review.approve', [$weapon, $selectedTemporaryUserId]) }}"
                                                data-reject-url="{{ route('revista-armas.review.reject', [$weapon, $selectedTemporaryUserId]) }}"
                                                data-serial="{{ $weapon->serial_number }}"
                                            >{{ __('Ver') }}</button>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr class="revista-table-empty-server">
                                    <td colspan="10" class="px-3 py-8 text-center text-slate-500">
                                        @if ($needsTemporaryUserSelection)
                                            {{ __('Seleccione un usuario temporal y pulse Filtrar para ver las armas de su último acceso.') }}
                                        @elseif ($noGrantHistory)
                                            {{ __('Este colaborador no tiene accesos asignados.') }}
                                        @else
                                            {{ __('No hay armas en su alcance.') }}
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($weapons)
                    <div class="border-t border-slate-200 bg-white px-4 py-3">
                        {{ $weapons->links() }}
                    </div>
                @endif
                <p id="revista-table-filter-empty" class="hidden border-t border-slate-200 bg-white px-4 py-6 text-center text-sm text-slate-500">
                    {{ __('Ningún arma coincide con la búsqueda.') }}
                </p>
            </div>
        </div>
    </div>

    {{-- Asignar acceso --}}
    <div id="revista-assign-modal" class="fixed inset-0 z-[1050] hidden items-center justify-center bg-black/40 p-4">
        <div class="flex max-h-[90vh] w-full max-w-2xl flex-col overflow-hidden rounded-xl bg-white shadow-xl">
            <div class="border-b px-4 py-3 font-semibold text-slate-900">{{ __('Asignar acceso temporal') }}</div>
            <form id="revista-assign-form" method="POST" action="{{ route('revista-armas.access.store') }}" class="flex min-h-0 flex-1 flex-col">
                @csrf
                <div class="space-y-4 overflow-y-auto p-4">
                    @if ($errors->has('access') || $errors->has('weapon_ids'))
                        <div class="rounded-lg border border-red-200 bg-red-50 px-3 py-2 text-sm text-red-800">
                            {{ $errors->first('access') ?: $errors->first('weapon_ids') }}
                        </div>
                    @endif

                    <div id="revista-assign-hint" class="hidden rounded-lg border border-amber-200 bg-amber-50 px-3 py-2 text-sm text-amber-900"></div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 sm:items-start">
                        <div class="min-w-0">
                            <label for="revista-assign-temp-user" class="block text-sm font-medium text-slate-700">{{ __('Usuario temporal') }}</label>
                            <select id="revista-assign-temp-user" name="temporary_photo_user_id" required class="sj-ui-field__control mt-1 w-full">
                                <option value="">{{ __('Seleccione...') }}</option>
                                @foreach ($temporaryUsers as $tu)
                                    <option value="{{ $tu->id }}" @selected((int) old('temporary_photo_user_id', $selectedTemporaryUserId) === (int) $tu->id)>
                                        {{ $tu->name }} — {{ $tu->email }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="mt-1 text-xs text-slate-500">
                                <a href="{{ route('revista-armas.temporary-users.create') }}" class="text-[#0b6fb6] font-semibold">{{ __('Crear nuevo usuario temporal') }}</a>
                            </p>
                        </div>
                        <div class="min-w-0">
                            <label for="revista-weapons-filter" class="block text-sm font-medium text-slate-700">{{ __('Buscar armas') }}</label>
                            <input
                                id="revista-weapons-filter"
                                type="search"
                                autocomplete="off"
                                class="sj-ui-field__control mt-1 h-10 w-full"
                                placeholder="{{ __('Serie, código, marca, calibre, cliente, responsable...') }}"
                            >
                            <p class="mt-1 text-xs text-slate-500">
                                {{ __('Las armas con fotos pendientes quedan bloqueadas: debe actualizarlas o eliminarlas antes de sacarlas.') }}
                            </p>
                        </div>
                    </div>
                    <div>
                        <div class="mb-2 flex flex-wrap items-center justify-between gap-4">
                            <div class="flex flex-wrap items-end gap-6">
                                <div>
                                    <span class="text-sm font-medium text-slate-700">{{ __('Armas') }}</span>
                                    <p id="revista-weapons-filter-count" class="text-xs text-slate-500"></p>
                                </div>
                                <div>
                                    <span class="text-sm font-medium text-slate-700">{{ __('Seleccionadas') }}</span>
                                    <p id="revista-weapons-selected-count" class="text-sm font-semibold tabular-nums text-[#0b6fb6]">0</p>
                                </div>
                            </div>
                            <label class="flex items-center gap-2 text-xs text-slate-600 shrink-0">
                                <input type="checkbox" id="revista-select-all-weapons" class="rounded border-slate-300">
                                {{ __('Seleccionar todas visibles') }}
                            </label>
                        </div>
                        <div id="revista-weapons-table-wrap" class="overflow-hidden rounded-lg border border-slate-200">
                            <div class="overflow-x-auto border-b border-slate-200 bg-slate-50">
                                <table class="min-w-full table-fixed text-sm">
                                    <colgroup>
                                        <col style="width: 2.5rem">
                                        <col style="width: 34%">
                                        <col style="width: 28%">
                                        <col style="width: 20%">
                                        <col style="width: 18%">
                                    </colgroup>
                                    <thead>
                                        <tr class="text-left text-xs font-semibold uppercase tracking-wide text-slate-600">
                                            <th class="px-2 py-2" scope="col"><span class="sr-only">{{ __('Seleccionar') }}</span></th>
                                            <th class="px-3 py-2" scope="col">{{ __('Cliente') }}</th>
                                            <th class="px-3 py-2" scope="col">{{ __('Serie') }}</th>
                                            <th class="px-3 py-2" scope="col">{{ __('Tipo') }}</th>
                                            <th class="px-3 py-2" scope="col">{{ __('Estado') }}</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <div id="revista-weapons-list" class="max-h-52 overflow-y-auto overflow-x-auto overscroll-contain bg-white">
                                <table class="min-w-full table-fixed divide-y divide-slate-100 text-sm">
                                    <colgroup>
                                        <col style="width: 2.5rem">
                                        <col style="width: 34%">
                                        <col style="width: 28%">
                                        <col style="width: 20%">
                                        <col style="width: 18%">
                                    </colgroup>
                                    <tbody id="revista-weapons-tbody">
                                        <tr id="revista-weapons-loading-row">
                                            <td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500">
                                                {{ __('Seleccione un usuario temporal para cargar armas…') }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            <div id="revista-weapons-selected-inputs" class="hidden" aria-hidden="true"></div>
                        </div>
                        <p id="revista-weapons-filter-empty" class="mt-2 hidden text-center text-sm text-slate-500">
                            {{ __('Ningún arma coincide con la búsqueda.') }}
                        </p>
                    </div>
                </div>
                <div class="flex flex-wrap justify-end gap-2 border-t px-4 py-3">
                    <button type="button" data-revista-assign-cancel class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</button>
                    <button
                        type="button"
                        id="revista-renew-access"
                        class="sj-ui-btn sj-ui-btn--ghost disabled:cursor-not-allowed disabled:opacity-40"
                        disabled
                    >{{ __('Renovar último acceso') }}</button>
                    <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Enviar') }}</button>
                </div>
            </form>
            <form id="revista-renew-form" method="POST" action="{{ route('revista-armas.access.renew') }}" class="hidden">
                @csrf
                <input type="hidden" name="temporary_photo_user_id" id="revista-renew-temp-user" value="">
            </form>
        </div>
    </div>

    {{-- Éxito acceso --}}
    @if (session('revista_access_success'))
        @php($ok = session('revista_access_success'))
        <div id="revista-success-modal" class="fixed inset-0 z-[1060] flex items-center justify-center bg-black/40 p-4">
            <div class="w-full max-w-lg rounded-xl bg-white p-5 shadow-xl">
                <h3 class="sj-type-section text-slate-900">
                    @if (! empty($ok['renewed']))
                        {{ __('Acceso renovado') }}
                    @elseif (! empty($ok['appended']))
                        {{ __('Armas agregadas al acceso vigente') }}
                    @else
                        {{ __('Acceso creado') }}
                    @endif
                </h3>
                <p class="mt-2 text-sm text-slate-600">
                    @if (! empty($ok['renewed']))
                        {{ __('Se generó un nuevo código con las mismas armas del último acceso. Las fotos en revisión se conservan.') }}
                    @elseif (! empty($ok['appended']))
                        {{ __('Las armas seleccionadas se sumaron al acceso activo. El colaborador debe usar el mismo código enviado anteriormente.') }}
                    @else
                        {{ __('Copie y envíe estos datos al colaborador. También se envió un correo si el servidor de correo está configurado.') }}
                    @endif
                </p>
                <textarea id="revista-success-copy" readonly rows="6" class="sj-ui-field__control mt-3 w-full">@foreach (array_filter([
                    __('Enlace') . ': ' . $ok['login_url'],
                    __('Correo') . ': ' . $ok['email'],
                    ! empty($ok['code']) ? __('Código') . ': ' . $ok['code'] : null,
                    __('Válido hasta') . ': ' . $ok['expires_at'],
                ]) as $line){{ $line }}
@endforeach</textarea>
                <div class="mt-4 flex justify-end gap-2">
                    <button type="button" id="revista-copy-success" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Copiar') }}</button>
                    <button type="button" onclick="document.getElementById('revista-success-modal').remove()" class="sj-ui-btn sj-ui-btn--primary">{{ __('Cerrar') }}</button>
                </div>
            </div>
        </div>
    @endif

    {{-- Alerta (fotos incompletas u otros avisos) --}}
    <div id="revista-alert-modal" class="fixed inset-0 z-[1070] hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl" role="alertdialog" aria-modal="true" aria-labelledby="revista-alert-title">
            <h3 id="revista-alert-title" class="sj-type-section text-slate-900">{{ __('Aviso') }}</h3>
            <p id="revista-alert-message" class="mt-3 text-sm text-slate-600"></p>
            <div class="mt-5 flex justify-end">
                <button type="button" id="revista-alert-ok" class="sj-ui-btn sj-ui-btn--primary">{{ __('Entendido') }}</button>
            </div>
        </div>
    </div>

    {{-- Confirmación --}}
    <div id="revista-confirm-modal" class="fixed inset-0 z-[1070] hidden items-center justify-center bg-black/40 p-4">
        <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl" role="dialog" aria-modal="true" aria-labelledby="revista-confirm-title">
            <h3 id="revista-confirm-title" class="sj-type-section text-slate-900">{{ __('Confirmar') }}</h3>
            <p id="revista-confirm-message" class="mt-3 text-sm text-slate-600"></p>
            <div class="mt-5 flex justify-end gap-2">
                <button type="button" id="revista-confirm-cancel" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</button>
                <button type="button" id="revista-confirm-accept" class="sj-ui-btn sj-ui-btn--primary">{{ __('Aceptar') }}</button>
            </div>
        </div>
    </div>

    {{-- Revisión --}}
    <div id="revista-review-modal" class="fixed inset-0 z-[1050] hidden items-center justify-center bg-black/40 p-4">
        <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-xl">
            <div class="border-b px-4 py-3">
                <h3 class="sj-type-section text-slate-900">{{ __('Revisión de fotos') }} — <span id="revista-review-serial"></span></h3>
            </div>
            <div id="revista-review-grid" class="grid grid-cols-2 gap-3 overflow-y-auto p-4"></div>
            <div class="flex justify-end gap-2 border-t px-4 py-3">
                <button type="button" id="revista-review-reject" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--danger">{{ __('Rechazar') }}</button>
                <button type="button" id="revista-review-approve" class="sj-ui-btn sj-ui-btn--primary">{{ __('Actualizar') }}</button>
                <button type="button" data-revista-review-close class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cerrar') }}</button>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        (() => {
            const tableFilter = document.getElementById('revista-table-filter');
            const tableFilterCount = document.getElementById('revista-table-filter-count');
            const tableFilterEmpty = document.getElementById('revista-table-filter-empty');
            const tableBody = document.getElementById('revista-table-body');
            const tableRows = () => Array.from(tableBody?.querySelectorAll('.revista-table-row') ?? []);

            const applyTableFilter = () => {
                const term = (tableFilter?.value ?? '').trim().toLowerCase();
                const rows = tableRows();
                let visible = 0;

                rows.forEach((row) => {
                    const haystack = row.dataset.search ?? '';
                    const matches = term === '' || haystack.includes(term);
                    row.style.display = matches ? '' : 'none';
                    if (matches) {
                        visible += 1;
                    }
                });

                const total = rows.length;
                if (tableFilterCount) {
                    if (total === 0) {
                        tableFilterCount.textContent = '';
                    } else if (term === '') {
                        tableFilterCount.textContent = `{{ __('Total:') }} ${total}`;
                    } else {
                        tableFilterCount.textContent = `{{ __('Mostrando') }} ${visible} / ${total}`;
                    }
                }

                if (tableFilterEmpty) {
                    tableFilterEmpty.classList.toggle('hidden', visible > 0 || total === 0);
                }
            };

            tableFilter?.addEventListener('input', applyTableFilter);
            applyTableFilter();
        })();
    </script>
    <script>
        (() => {
            const requiredPhotoCount = @json(\App\Support\RevistaWeaponPhotoSlots::requiredCount());
            const assignSearchUrl = @json($assignWeaponsSearchUrl);
            const assignmentContextUrlTemplate = @json($assignmentContextUrlTemplate);

            const assignModal = document.getElementById('revista-assign-modal');
            const tempUserSelect = document.getElementById('revista-assign-temp-user');
            const assignHint = document.getElementById('revista-assign-hint');
            const renewBtn = document.getElementById('revista-renew-access');
            const renewForm = document.getElementById('revista-renew-form');
            const renewTempUserInput = document.getElementById('revista-renew-temp-user');
            const weaponsFilter = document.getElementById('revista-weapons-filter');
            const weaponsTableWrap = document.getElementById('revista-weapons-table-wrap');
            const weaponsList = document.getElementById('revista-weapons-tbody');
            const weaponsFilterCount = document.getElementById('revista-weapons-filter-count');
            const weaponsSelectedCount = document.getElementById('revista-weapons-selected-count');
            const weaponsFilterEmpty = document.getElementById('revista-weapons-filter-empty');
            const selectedInputsHost = document.getElementById('revista-weapons-selected-inputs');
            const selectAllWeapons = document.getElementById('revista-select-all-weapons');

            /** @type {Map<number, object>} */
            const selectedWeapons = new Map();
            /** @type {Set<number>} */
            let lockedWeaponIds = new Set();
            let canRenew = false;
            let searchTimer = null;
            let searchSeq = 0;
            let visibleItems = [];

            const escapeHtml = (value) => String(value ?? '')
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;');

            const selectedTempUserId = () => Number(tempUserSelect?.value || 0) || null;

            const syncSelectedInputs = () => {
                if (!selectedInputsHost) {
                    return;
                }
                selectedInputsHost.innerHTML = '';
                selectedWeapons.forEach((weapon) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'weapon_ids[]';
                    input.value = String(weapon.id);
                    selectedInputsHost.appendChild(input);
                });
            };

            const updateSelectedCount = () => {
                if (weaponsSelectedCount) {
                    weaponsSelectedCount.textContent = String(selectedWeapons.size);
                }
                syncSelectedInputs();
            };

            const updateRenewButton = () => {
                if (!renewBtn) {
                    return;
                }
                renewBtn.disabled = !canRenew || !selectedTempUserId();
            };

            const setHint = (message) => {
                if (!assignHint) {
                    return;
                }
                if (!message) {
                    assignHint.classList.add('hidden');
                    assignHint.textContent = '';
                    return;
                }
                assignHint.textContent = message;
                assignHint.classList.remove('hidden');
            };

            const statusLabel = (item) => {
                if (item.blocked_by_other) {
                    return `<span class="text-xs font-semibold text-rose-700" title="${escapeHtml(item.blocked_by_other)}">{{ __('Bloqueada') }}</span>`;
                }
                if (item.locked || item.has_staging) {
                    return `<span class="text-xs font-semibold text-amber-700">{{ __('Con fotos') }}</span>`;
                }
                return `<span class="text-xs text-slate-400">—</span>`;
            };

            const updateSelectAllState = () => {
                if (!selectAllWeapons) {
                    return;
                }
                const selectable = visibleItems.filter((item) => !item.blocked_by_other);
                if (selectable.length === 0) {
                    selectAllWeapons.checked = false;
                    selectAllWeapons.indeterminate = false;
                    return;
                }
                const selectedVisible = selectable.filter((item) => selectedWeapons.has(item.id)).length;
                selectAllWeapons.checked = selectedVisible === selectable.length;
                selectAllWeapons.indeterminate = selectedVisible > 0 && selectedVisible < selectable.length;
            };

            const renderWeaponRows = (items) => {
                visibleItems = items;
                if (!weaponsList) {
                    return;
                }

                if (items.length === 0) {
                    weaponsList.innerHTML = `<tr><td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500">${escapeHtml(@json(__('Ningún arma coincide con la búsqueda.')))}</td></tr>`;
                } else {
                    weaponsList.innerHTML = items.map((item) => {
                        const locked = Boolean(item.locked || lockedWeaponIds.has(item.id));
                        const blocked = Boolean(item.blocked_by_other);
                        const checked = selectedWeapons.has(item.id) || locked ? 'checked' : '';
                        const disabled = locked || blocked ? 'disabled' : '';
                        const rowClass = blocked
                            ? 'bg-rose-50/60'
                            : (locked ? 'bg-amber-50/70' : 'hover:bg-slate-50 cursor-pointer');

                        if (locked && !blocked) {
                            selectedWeapons.set(item.id, { ...item, locked: true });
                        }

                        return `
                            <tr class="revista-weapon-row ${rowClass}" data-weapon-id="${item.id}" data-locked="${locked ? '1' : '0'}" data-blocked="${blocked ? '1' : '0'}">
                                <td class="px-3 py-2 align-middle">
                                    <input type="checkbox" value="${item.id}" class="revista-weapon-cb rounded border-slate-300" ${checked} ${disabled}>
                                </td>
                                <td class="px-3 py-2 align-middle text-slate-700">${escapeHtml(item.client_name)}</td>
                                <td class="px-3 py-2 align-middle font-medium text-slate-900">${escapeHtml(item.serial_number)}</td>
                                <td class="px-3 py-2 align-middle text-slate-700">${escapeHtml(item.weapon_type || '—')}</td>
                                <td class="px-3 py-2 align-middle">${statusLabel({ ...item, locked })}</td>
                            </tr>
                        `;
                    }).join('');
                }

                weaponsList.querySelectorAll('.revista-weapon-row').forEach((row) => {
                    const id = Number(row.dataset.weaponId);
                    const item = items.find((entry) => entry.id === id);
                    const cb = row.querySelector('.revista-weapon-cb');
                    const locked = row.dataset.locked === '1';
                    const blocked = row.dataset.blocked === '1';

                    const toggle = (force) => {
                        if (!item || !cb || locked || blocked) {
                            return;
                        }
                        const next = typeof force === 'boolean' ? force : !selectedWeapons.has(id);
                        cb.checked = next;
                        if (next) {
                            selectedWeapons.set(id, item);
                        } else {
                            selectedWeapons.delete(id);
                        }
                        updateSelectedCount();
                        updateSelectAllState();
                    };

                    cb?.addEventListener('change', () => {
                        if (locked || blocked) {
                            cb.checked = locked;
                            return;
                        }
                        toggle(cb.checked);
                    });
                    row.addEventListener('click', (event) => {
                        if (event.target.closest('input') || locked || blocked) {
                            return;
                        }
                        toggle();
                    });
                });

                if (weaponsFilterCount) {
                    weaponsFilterCount.textContent = items.length > 0
                        ? `{{ __('Mostrando') }} ${items.length}`
                        : '';
                }

                if (weaponsFilterEmpty) {
                    weaponsFilterEmpty.classList.toggle('hidden', items.length > 0);
                }

                if (weaponsTableWrap) {
                    weaponsTableWrap.classList.toggle('hidden', items.length === 0 && (weaponsFilter?.value ?? '').trim() !== '');
                }

                updateSelectAllState();
                updateSelectedCount();
            };

            const fetchAssignableWeapons = async (term) => {
                const tempId = selectedTempUserId();
                const seq = ++searchSeq;
                if (weaponsList) {
                    weaponsList.innerHTML = `<tr><td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500">${escapeHtml(@json(__('Buscando…')))}</td></tr>`;
                }

                const url = new URL(assignSearchUrl, window.location.origin);
                if (term) {
                    url.searchParams.set('q', term);
                }
                if (tempId) {
                    url.searchParams.set('temporary_photo_user_id', String(tempId));
                }

                try {
                    const res = await fetch(url.toString(), { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) {
                        throw new Error('search failed');
                    }
                    const data = await res.json();
                    if (seq !== searchSeq) {
                        return;
                    }
                    renderWeaponRows(Array.isArray(data.items) ? data.items : []);
                } catch (_) {
                    if (seq !== searchSeq) {
                        return;
                    }
                    weaponsList.innerHTML = `<tr><td colspan="5" class="px-3 py-6 text-center text-sm text-red-600">${escapeHtml(@json(__('No se pudo cargar el listado de armas.')))}</td></tr>`;
                }
            };

            const loadAssignmentContext = async () => {
                const tempId = selectedTempUserId();
                lockedWeaponIds = new Set();
                canRenew = false;
                updateRenewButton();
                setHint('');

                if (!tempId) {
                    selectedWeapons.clear();
                    updateSelectedCount();
                    if (weaponsList) {
                        weaponsList.innerHTML = `<tr><td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500">${escapeHtml(@json(__('Seleccione un usuario temporal para cargar armas…')))}</td></tr>`;
                    }
                    return;
                }

                const url = assignmentContextUrlTemplate.replace('__ID__', String(tempId));
                try {
                    const res = await fetch(url, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) {
                        throw new Error('context failed');
                    }
                    const data = await res.json();
                    lockedWeaponIds = new Set((data.locked_weapon_ids || []).map(Number));
                    canRenew = Boolean(data.can_renew);
                    updateRenewButton();

                    selectedWeapons.clear();
                    (data.staging_weapons || []).forEach((weapon) => {
                        selectedWeapons.set(Number(weapon.id), {
                            id: Number(weapon.id),
                            serial_number: weapon.serial_number,
                            weapon_type: '',
                            client_name: '—',
                            locked: true,
                            has_staging: true,
                        });
                    });
                    (data.latest_weapon_ids || []).forEach((id) => {
                        const weaponId = Number(id);
                        if (!selectedWeapons.has(weaponId)) {
                            selectedWeapons.set(weaponId, {
                                id: weaponId,
                                serial_number: '',
                                weapon_type: '',
                                client_name: '—',
                                locked: lockedWeaponIds.has(weaponId),
                                has_staging: lockedWeaponIds.has(weaponId),
                            });
                        }
                    });

                    const stagingCount = (data.staging_weapons || []).length;
                    if (stagingCount > 0) {
                        const serials = (data.staging_weapons || [])
                            .map((weapon) => weapon.serial_number)
                            .filter(Boolean)
                            .join(', ');
                        const base = @json(__('Hay :count arma(s) con fotos pendientes: quedan bloqueadas en la selección. Actualice o elimine esas fotos si necesita sacarlas.'));
                        setHint(base.replace(':count', String(stagingCount)) + (serials ? ` (${serials})` : ''));
                    }

                    updateSelectedCount();
                    await fetchAssignableWeapons((weaponsFilter?.value ?? '').trim());
                } catch (_) {
                    setHint(@json(__('No se pudo cargar el contexto del usuario temporal.')));
                }
            };

            const scheduleSearch = () => {
                clearTimeout(searchTimer);
                searchTimer = setTimeout(() => {
                    fetchAssignableWeapons((weaponsFilter?.value ?? '').trim());
                }, 280);
            };

            const resetAssignModalFilters = () => {
                selectedWeapons.clear();
                lockedWeaponIds = new Set();
                canRenew = false;
                if (weaponsFilter) {
                    weaponsFilter.value = '';
                }
                if (selectAllWeapons) {
                    selectAllWeapons.checked = false;
                    selectAllWeapons.indeterminate = false;
                }
                setHint('');
                updateRenewButton();
                updateSelectedCount();
            };

            document.getElementById('revista-open-assign')?.addEventListener('click', () => {
                resetAssignModalFilters();
                assignModal?.classList.remove('hidden');
                assignModal?.classList.add('flex');
                loadAssignmentContext();
            });

            const closeAssignModal = () => {
                assignModal?.classList.add('hidden');
                assignModal?.classList.remove('flex');
                resetAssignModalFilters();
                if (weaponsList) {
                    weaponsList.innerHTML = `<tr><td colspan="5" class="px-3 py-6 text-center text-sm text-slate-500">${escapeHtml(@json(__('Seleccione un usuario temporal para cargar armas…')))}</td></tr>`;
                }
            };

            document.querySelector('[data-revista-assign-cancel]')?.addEventListener('click', closeAssignModal);
            tempUserSelect?.addEventListener('change', () => {
                selectedWeapons.clear();
                loadAssignmentContext();
            });
            weaponsFilter?.addEventListener('input', scheduleSearch);

            selectAllWeapons?.addEventListener('change', (e) => {
                const checked = e.target.checked;
                visibleItems.forEach((item) => {
                    if (item.blocked_by_other) {
                        return;
                    }
                    if (item.locked || lockedWeaponIds.has(item.id)) {
                        selectedWeapons.set(item.id, item);
                        return;
                    }
                    if (checked) {
                        selectedWeapons.set(item.id, item);
                    } else {
                        selectedWeapons.delete(item.id);
                    }
                });
                weaponsList?.querySelectorAll('.revista-weapon-row').forEach((row) => {
                    const cb = row.querySelector('.revista-weapon-cb');
                    if (!cb || cb.disabled) {
                        return;
                    }
                    cb.checked = checked;
                });
                selectAllWeapons.indeterminate = false;
                updateSelectedCount();
            });

            document.getElementById('revista-copy-success')?.addEventListener('click', () => {
                const ta = document.getElementById('revista-success-copy');
                ta?.select();
                navigator.clipboard?.writeText(ta.value);
            });

            @if ($errors->has('weapon_ids') || $errors->has('access'))
                assignModal?.classList.remove('hidden');
                assignModal?.classList.add('flex');
                loadAssignmentContext();
            @endif

            const reviewModal = document.getElementById('revista-review-modal');
            const reviewGrid = document.getElementById('revista-review-grid');
            const alertModal = document.getElementById('revista-alert-modal');
            const alertMessage = document.getElementById('revista-alert-message');
            const confirmModal = document.getElementById('revista-confirm-modal');
            const confirmMessage = document.getElementById('revista-confirm-message');
            const confirmAccept = document.getElementById('revista-confirm-accept');
            const confirmCancel = document.getElementById('revista-confirm-cancel');

            let approveUrl = '';
            let rejectUrl = '';
            let reviewIsComplete = false;
            let reviewPendingCount = requiredPhotoCount;
            let confirmOnAccept = null;

            const openOverlay = (el) => {
                el?.classList.remove('hidden');
                el?.classList.add('flex');
            };
            const closeOverlay = (el) => {
                el?.classList.add('hidden');
                el?.classList.remove('flex');
            };

            const showAlert = (message) => {
                if (alertMessage) alertMessage.textContent = message;
                openOverlay(alertModal);
            };

            const showConfirm = (message, onAccept) => {
                confirmOnAccept = onAccept;
                if (confirmMessage) confirmMessage.textContent = message;
                openOverlay(confirmModal);
            };

            renewBtn?.addEventListener('click', () => {
                const tempId = selectedTempUserId();
                if (!tempId || !canRenew || !renewForm || !renewTempUserInput) {
                    showAlert(@json(__('Seleccione un usuario temporal con un acceso previo para renovar.')));
                    return;
                }
                renewTempUserInput.value = String(tempId);
                showConfirm(
                    @json(__('¿Renovar el último acceso con las mismas armas y un código nuevo? Las fotos en revisión se conservan.')),
                    () => renewForm.submit(),
                );
            });

            document.getElementById('revista-assign-form')?.addEventListener('submit', (event) => {
                lockedWeaponIds.forEach((id) => {
                    if (!selectedWeapons.has(id)) {
                        selectedWeapons.set(id, { id, locked: true, has_staging: true });
                    }
                });
                syncSelectedInputs();

                if (selectedWeapons.size === 0) {
                    event.preventDefault();
                    showAlert(@json(__('Seleccione al menos un arma.')));
                }
            });

            document.getElementById('revista-alert-ok')?.addEventListener('click', () => closeOverlay(alertModal));

            confirmCancel?.addEventListener('click', () => {
                confirmOnAccept = null;
                closeOverlay(confirmModal);
            });

            confirmAccept?.addEventListener('click', async () => {
                const action = confirmOnAccept;
                confirmOnAccept = null;
                closeOverlay(confirmModal);
                if (typeof action === 'function') {
                    await action();
                }
            });

            const closeReview = () => closeOverlay(reviewModal);
            document.querySelectorAll('[data-revista-review-close]').forEach((b) => b.addEventListener('click', closeReview));

            const postAction = async (url) => {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': @json(csrf_token()),
                        'Accept': 'application/json',
                    },
                });

                if (!res.ok) {
                    let message = @json(__('No se pudo completar la acción.'));
                    try {
                        const payload = await res.json();
                        if (payload?.message) message = payload.message;
                        if (payload?.errors?.photos?.[0]) message = payload.errors.photos[0];
                    } catch (_) {}
                    showAlert(message);
                    return;
                }

                window.location.reload();
            };

            document.querySelectorAll('.revista-review-btn').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    approveUrl = btn.dataset.approveUrl;
                    rejectUrl = btn.dataset.rejectUrl;
                    document.getElementById('revista-review-serial').textContent = btn.dataset.serial || '';
                    const res = await fetch(btn.dataset.reviewUrl, { headers: { 'Accept': 'application/json' } });
                    if (!res.ok) {
                        showAlert(@json(__('No se pudo cargar la revisión de fotos.')));
                        return;
                    }
                    const data = await res.json();
                    reviewIsComplete = Boolean(data.is_complete);
                    reviewPendingCount = Number(data.pending_count ?? (requiredPhotoCount - (data.uploaded_count ?? 0)));
                    reviewGrid.innerHTML = '';
                    (data.slots || []).forEach((slot) => {
                        const cell = document.createElement('div');
                        cell.className = 'rounded-lg border border-slate-200 p-2';
                        cell.innerHTML = slot.url
                            ? `<img src="${slot.url}" alt="" class="h-36 w-full rounded object-contain bg-slate-50"><div class="mt-1 text-xs font-medium text-slate-600">${slot.label}</div>`
                            : `<div class="flex h-36 items-center justify-center rounded border border-dashed border-slate-300 text-xs text-slate-400">${slot.label}<br>{{ __('Sin imagen') }}</div>`;
                        reviewGrid.appendChild(cell);
                    });
                    openOverlay(reviewModal);
                });
            });

            document.getElementById('revista-review-approve')?.addEventListener('click', () => {
                if (!approveUrl) return;

                if (!reviewIsComplete) {
                    const pending = Math.max(1, reviewPendingCount);
                    showAlert(@json(__('No se pueden actualizar las imágenes oficiales porque faltan :count foto(s) pendiente(s).')).replace(':count', String(pending)));
                    return;
                }

                showConfirm(
                    @json(__('¿Actualizar las imágenes oficiales del arma con estas fotos?')),
                    () => postAction(approveUrl),
                );
            });

            document.getElementById('revista-review-reject')?.addEventListener('click', () => {
                if (!rejectUrl) return;

                showConfirm(
                    @json(__('¿Rechazar y eliminar las fotos en revisión?')),
                    () => postAction(rejectUrl),
                );
            });
        })();
    </script>
    @endpush
</x-app-layout>
