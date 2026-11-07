<x-revista-guest-layout :title="__('Mis armas')">
    <div class="mb-4 rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
        <p class="text-sm text-slate-700">{{ __('Hola :name', ['name' => $fieldUser->name]) }}</p>
        <p class="mt-1 text-xs text-slate-500">{{ __('Ingreso del día :start al día :end de cada mes.', ['start' => $fieldUser->field_day_start, 'end' => $fieldUser->field_day_end]) }}</p>
    </div>

    <div class="space-y-3">
        @forelse ($rows as $row)
            @php($weapon = $row['weapon'])
            <article data-revista-weapon-id="{{ $weapon->id }}" class="rounded-xl border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-base font-semibold text-slate-900">{{ $weapon->serial_number ?? '—' }}</p>
                        <p class="mt-1 text-sm text-slate-700">{{ __('Cliente') }}: {{ $weapon->activeClientAssignment?->client?->name ?? '—' }}</p>
                        @if ($weapon->activePostAssignment?->post)
                            <p class="text-sm text-slate-600">{{ __('Puesto') }}: {{ $weapon->activePostAssignment->post->name }}</p>
                        @endif
                        @if ($weapon->activeWorkerAssignment?->worker)
                            <p class="text-sm text-slate-600">{{ __('Trabajador') }}: {{ $weapon->activeWorkerAssignment->worker->name }}</p>
                        @endif
                        <p class="mt-1 text-sm text-slate-600">{{ $weapon->weapon_type ?? '—' }} · {{ $weapon->brand ?? '—' }} · {{ $weapon->caliber ?? '—' }}</p>
                    </div>
                    <span data-revista-guest-status>
                        @if ($row['is_complete'])
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-500">✓</span>
                        @else
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-red-500/15 text-red-500">✕</span>
                        @endif
                    </span>
                </div>
                <button
                    type="button"
                    class="revista-guest-upload mt-3 min-h-11 w-full rounded-lg bg-[#0b6fb6] text-sm font-bold text-white"
                    data-weapon-id="{{ $weapon->id }}"
                    data-state-url="{{ route('revista-armas.guest.weapons.staging-state', $weapon) }}"
                    data-store-url="{{ route('revista-armas.guest.weapons.photos.store', $weapon) }}"
                    data-serial="{{ $weapon->serial_number }}"
                >{{ __('Fotos') }}</button>
            </article>
        @empty
            <p class="rounded-xl border border-slate-200 bg-white p-6 text-center text-sm text-slate-500">{{ __('No hay armas asignadas.') }}</p>
        @endforelse
    </div>

    <div id="revista-guest-upload-modal" class="fixed inset-0 z-[1050] hidden items-center justify-center bg-black/40 p-4">
        <div class="relative flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-xl">
            <div class="border-b px-4 py-3 font-semibold">{{ __('Fotos del arma') }} — <span id="revista-guest-serial"></span></div>
            <p id="revista-guest-grid-hint" class="hidden border-b border-slate-100 bg-slate-50 px-4 py-2 text-center text-xs text-slate-500"></p>
            <div id="revista-guest-slot-grid" class="grid grid-cols-2 gap-3 overflow-y-auto p-4"></div>
            <div class="flex justify-end border-t px-4 py-3">
                <button type="button" data-revista-guest-close class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cerrar') }}</button>
            </div>
        </div>
    </div>

    @include('revista-armas.partials.photo-capture-kit')

    @push('scripts')
    <script>
        (() => {
            const uploadModal = document.getElementById('revista-guest-upload-modal');
            const slotGrid = document.getElementById('revista-guest-slot-grid');
            let stateUrl = '';
            let activeStoreUrl = '';
            let activeWeaponId = '';

            const updateRowStatus = (weaponId, isComplete) => {
                const row = document.querySelector(`[data-revista-weapon-id="${weaponId}"]`);
                const cell = row?.querySelector('[data-revista-guest-status]');
                if (!cell) return;
                cell.innerHTML = isComplete
                    ? '<span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-500 shadow-[0_0_12px_rgba(16,185,129,0.55)]">✓</span>'
                    : '<span class="inline-flex h-7 w-7 items-center justify-center rounded-full bg-red-500/15 text-red-500 shadow-[0_0_10px_rgba(239,68,68,0.45)]">✕</span>';
            };

            const gridHint = document.getElementById('revista-guest-grid-hint');
            const txtRefreshing = @json(__('Actualizando fotos…'));
            const txtGridError = @json(__('No se pudieron actualizar las miniaturas. Cierre y vuelva a abrir.'));

            const setGridBusy = (busy) => {
                slotGrid?.querySelectorAll('.revista-slot-card').forEach((cell) => {
                    cell.disabled = busy;
                });
            };

            const reloadGuestGrid = async () => {
                if (!stateUrl) return;
                setGridBusy(true);
                if (gridHint) {
                    gridHint.textContent = txtRefreshing;
                    gridHint.classList.remove('hidden');
                }
                try {
                    const res = await fetch(stateUrl, {
                        headers: { 'Accept': 'application/json' },
                        credentials: 'same-origin',
                    });
                    if (!res.ok) {
                        throw new Error('state failed');
                    }
                    const data = await res.json();
                    renderSlots(data);
                    if (activeWeaponId) {
                        updateRowStatus(activeWeaponId, data.is_complete);
                    }
                    if (gridHint) {
                        gridHint.classList.add('hidden');
                    }
                } catch (e) {
                    if (gridHint) {
                        gridHint.textContent = txtGridError;
                        gridHint.classList.remove('hidden');
                    }
                } finally {
                    setGridBusy(false);
                }
            };

            const capture = window.initRevistaPhotoCapture({
                csrfToken: @json(csrf_token()),
                onSuccess: reloadGuestGrid,
                onUploadingChange: (busy) => setGridBusy(busy),
            });

            const renderSlots = (data) => {
                if (!slotGrid) return;
                slotGrid.innerHTML = '';
                (data.slots || []).forEach((slot) => {
                    const cell = document.createElement('button');
                    cell.type = 'button';
                    cell.className = 'revista-slot-card rounded-lg border border-slate-200 p-2 text-left';
                    const label = slot.label || '';
                    const thumbUrl = slot.url
                        ? `${slot.url}${slot.url.includes('?') ? '&' : '?'}t=${Date.now()}`
                        : '';
                    const camera = '<span class="revista-slot-camera" aria-hidden="true"><svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><path d="M4 8h3l1.5-2h7L17 8h3a1 1 0 0 1 1 1v9a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V9a1 1 0 0 1 1-1z"/><circle cx="12" cy="13" r="3.2"/></svg></span>';
                    cell.innerHTML = `<div class="revista-slot-frame">${slot.url ? `<img src="${thumbUrl}" alt="">` : ''}${camera}</div><div class="mt-1 text-xs font-semibold">${label}</div>`;
                    cell.addEventListener('click', () => {
                        if (capture.isUploading?.()) return;
                        capture.openSlot(activeStoreUrl, slot.description);
                    });
                    slotGrid.appendChild(cell);
                });
            };

            const openModal = () => { uploadModal?.classList.remove('hidden'); uploadModal?.classList.add('flex'); };
            const closeModal = () => { uploadModal?.classList.add('hidden'); uploadModal?.classList.remove('flex'); };
            document.querySelector('[data-revista-guest-close]')?.addEventListener('click', closeModal);

            document.querySelectorAll('.revista-guest-upload').forEach((btn) => {
                btn.addEventListener('click', async () => {
                    stateUrl = btn.dataset.stateUrl;
                    activeStoreUrl = btn.dataset.storeUrl;
                    activeWeaponId = btn.dataset.weaponId || '';
                    document.getElementById('revista-guest-serial').textContent = btn.dataset.serial || '';
                    const res = await fetch(stateUrl, { headers: { 'Accept': 'application/json' } });
                    renderSlots(await res.json());
                    openModal();
                });
            });
        })();
    </script>
    @endpush
</x-revista-guest-layout>
