<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Revista armas') }}</h2>
                <p class="sj-section-header__subtitle">{{ __('Fotos de las armas y envío a campo.') }}</p>
            </div>
            <div class="sj-section-header__actions">
                <button type="button" class="sj-ui-btn sj-ui-btn--primary" data-revista-open-assign>{{ __('Enviar a campo') }}</button>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell sj-page-shell--wide space-y-6">
            @if (session('status'))
                <div class="rounded bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            <div class="sj-ui-card overflow-hidden">
                <div class="sj-table-wrap">
                    <table class="sj-table sj-table--align-left sj-table--compact">
                        <thead>
                            <tr>
                                <th>{{ __('Cliente') }}</th>
                                <th>{{ __('Puesto o trabajador') }}</th>
                                <th>{{ __('Tipo') }}</th>
                                <th>{{ __('Marca') }}</th>
                                <th>{{ __('Serie') }}</th>
                                <th>{{ __('Calibre') }}</th>
                                <th>{{ __('Estado') }}</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($rows as $row)
                                @php($weapon = $row['weapon'])
                                <tr>
                                    <td>{{ $weapon->activeClientAssignment?->client?->name ?? '—' }}</td>
                                    <td>
                                        @if ($weapon->activePostAssignment?->post)
                                            <div>{{ __('Puesto') }}: {{ $weapon->activePostAssignment->post->name }}</div>
                                        @endif
                                        @if ($weapon->activeWorkerAssignment?->worker)
                                            <div>{{ __('Trabajador') }}: {{ $weapon->activeWorkerAssignment->worker->name }}</div>
                                        @endif
                                        @if (! $weapon->activePostAssignment?->post && ! $weapon->activeWorkerAssignment?->worker)
                                            —
                                        @endif
                                    </td>
                                    <td>{{ $weapon->weapon_type ?? '—' }}</td>
                                    <td>{{ $weapon->brand ?? '—' }}</td>
                                    <td>{{ $weapon->serial_number ?? '—' }}</td>
                                    <td>{{ $weapon->caliber ?? '—' }}</td>
                                    <td>{{ $row['status'] }}</td>
                                    <td class="text-right">
                                        <button type="button" class="sj-ui-link" data-revista-photos="{{ route('revista-armas.photos', $weapon) }}">{{ __('Fotos') }}</button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-3 py-8 text-center text-slate-500">{{ __('No hay armas en esta revista.') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-4">{{ $weapons->links() }}</div>
            </div>
        </div>
    </div>

    <div id="revista-assign" class="fixed inset-0 z-[1050] hidden items-center justify-center bg-black/40 p-4">
        <form method="POST" action="{{ route('revista-armas.assign') }}" class="flex max-h-[92vh] w-full max-w-5xl flex-col overflow-hidden rounded-xl bg-white shadow-xl">
            @csrf
            <div class="border-b px-4 py-3 font-semibold">{{ __('Enviar a campo') }}</div>
            <div class="space-y-3 overflow-y-auto p-4">
                <div>
                    <label for="field_user_id" class="sj-ui-field__label">{{ __('Usuario de campo') }}</label>
                    <select id="field_user_id" name="field_user_id" class="sj-ui-field__control mt-1 w-full" required>
                        <option value="">{{ __('Seleccione') }}</option>
                        @foreach ($fieldUsers as $fieldUser)
                            <option value="{{ $fieldUser->id }}">{{ $fieldUser->name }} · {{ $fieldUser->email }}</option>
                        @endforeach
                    </select>
                    @if ($fieldUsers->isEmpty())
                        <p class="mt-1 text-xs text-slate-500">{{ __('Cree el usuario de campo en Usuarios.') }}</p>
                    @endif
                </div>
                <div>
                    <label for="revista-weapon-search" class="sj-ui-field__label">{{ __('Armas') }}</label>
                    <input id="revista-weapon-search" type="search" class="sj-ui-field__control mt-1 w-full" placeholder="{{ __('Serie, marca, cliente, puesto o trabajador') }}">
                </div>
                <div id="revista-weapon-results" class="sj-table-wrap max-h-80 overflow-y-auto"></div>
            </div>
            <div class="flex justify-end gap-2 border-t px-4 py-3">
                <button type="button" class="sj-ui-btn sj-ui-btn--ghost" data-revista-close>{{ __('Cancelar') }}</button>
                <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Enviar') }}</button>
            </div>
        </form>
    </div>

    <div id="revista-photos" class="fixed inset-0 z-[1050] hidden items-center justify-center bg-black/40 p-4">
        <div class="flex max-h-[92vh] w-full max-w-3xl flex-col overflow-hidden rounded-xl bg-white shadow-xl">
            <div class="border-b px-4 py-3 font-semibold">{{ __('Fotos') }} — <span id="revista-photos-serial"></span></div>
            <div id="revista-photos-body" class="space-y-4 overflow-y-auto p-4"></div>
            <div class="flex justify-end border-t px-4 py-3">
                <button type="button" class="sj-ui-btn sj-ui-btn--ghost" data-revista-close>{{ __('Cancelar') }}</button>
            </div>
        </div>
    </div>

    @if ($assignmentNotice)
        <div id="revista-notice" class="fixed inset-0 z-[1100] flex items-center justify-center bg-black/40 p-4">
            <div class="relative w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                <button type="button" class="absolute right-3 top-3 text-lg" data-revista-close aria-label="{{ __('Cerrar') }}">×</button>
                <p class="pr-6 text-sm text-slate-800">
                    {{ __('Sus armas han sido asignadas correctamente. Informe al usuario de campo :field que actualice la página y revise. El usuario y la contraseña son los mismos que le compartió :owner.', [
                        'field' => $assignmentNotice['field_name'],
                        'owner' => $assignmentNotice['owner_name'],
                    ]) }}
                </p>
            </div>
        </div>
    @endif

    @push('styles')
        <style>
            .revista-photo-slot__frame { display: flex; align-items: center; justify-content: center; height: 9rem; border-radius: 0.75rem; background: #e8eef5; overflow: hidden; }
            .revista-photo-slot__frame img { width: 100%; height: 100%; object-fit: contain; }
            .revista-photo-slot__empty { font-size: 0.75rem; color: #64748b; }
        </style>
    @endpush

    @push('scripts')
        <script>
            (() => {
                const token = document.querySelector('meta[name="csrf-token"]')?.content || '';
                const searchUrl = @json(route('revista-armas.weapons.search'));
                const approveUrl = @json(route('revista-armas.review.approve', ['weapon' => '__W__', 'fieldUser' => '__U__']));
                const rejectUrl = @json(route('revista-armas.review.reject', ['weapon' => '__W__', 'fieldUser' => '__U__']));
                const results = document.getElementById('revista-weapon-results');
                const search = document.getElementById('revista-weapon-search');
                let photoUrl = '';

                const open = (id) => {
                    const el = document.getElementById(id);
                    el.classList.remove('hidden');
                    el.classList.add('flex');
                };
                const close = (el) => {
                    el.classList.add('hidden');
                    el.classList.remove('flex');
                };
                document.querySelector('[data-revista-open-assign]')?.addEventListener('click', () => open('revista-assign'));
                document.querySelectorAll('[data-revista-close]').forEach((button) => {
                    button.addEventListener('click', () => close(button.closest('.fixed')));
                });

                const escapeHtml = (value) => String(value ?? '')
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;');

                const renderWeapons = (items) => {
                    if (!items.length) {
                        results.innerHTML = '<p class="p-3 text-sm text-slate-500">{{ __('Sin resultados.') }}</p>';
                        return;
                    }

                    const rows = items.map((item) => {
                        const assignment = (item.assignment || [])
                            .map((line) => `<div>${escapeHtml(line)}</div>`)
                            .join('') || '—';

                        return `
                            <tr>
                                <td>
                                    <input type="checkbox" name="weapon_ids[]" value="${escapeHtml(item.id)}" class="sj-ui-check" aria-label="${escapeHtml(item.serial_number || '')}">
                                </td>
                                <td>${escapeHtml(item.client_name || '—')}</td>
                                <td>${assignment}</td>
                                <td>${escapeHtml(item.weapon_type || '—')}</td>
                                <td>${escapeHtml(item.serial_number || '—')}</td>
                                <td>${escapeHtml(item.brand || '—')}</td>
                            </tr>
                        `;
                    }).join('');

                    results.innerHTML = `
                        <table class="sj-table sj-table--align-left sj-table--compact">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>{{ __('Cliente') }}</th>
                                    <th>{{ __('Puesto o trabajador') }}</th>
                                    <th>{{ __('Tipo') }}</th>
                                    <th>{{ __('Serie') }}</th>
                                    <th>{{ __('Marca') }}</th>
                                </tr>
                            </thead>
                            <tbody>${rows}</tbody>
                        </table>
                    `;
                };

                let timer = null;
                const loadWeapons = () => {
                    const params = new URLSearchParams({ q: search.value || '' });
                    fetch(`${searchUrl}?${params}`, { headers: { 'Accept': 'application/json' } })
                        .then((response) => response.json())
                        .then((data) => renderWeapons(data.items || []));
                };
                search?.addEventListener('input', () => {
                    clearTimeout(timer);
                    timer = setTimeout(loadWeapons, 250);
                });
                document.querySelector('[data-revista-open-assign]')?.addEventListener('click', loadWeapons);

                const slotCard = (slot) => `
                    <div class="revista-photo-slot rounded-lg border border-slate-200 p-2">
                        <div class="revista-photo-slot__frame">${slot.url ? `<img src="${slot.url}" alt="">` : '<span class="revista-photo-slot__empty">{{ __('Sin foto') }}</span>'}</div>
                        <p class="mt-1 text-xs font-semibold">${slot.label}</p>
                    </div>
                `;

                const paintPhotos = (data) => {
                    document.getElementById('revista-photos-serial').textContent = data.weapon.serial_number || '';
                    const official = data.slots.map((slot) => slotCard(slot)).join('');
                    const reviews = (data.reviews || []).map((review) => `
                        <div class="rounded border border-amber-200 p-3">
                            <p class="mb-2 text-sm font-semibold">${review.name}</p>
                            <div class="grid grid-cols-2 gap-2">${review.slots.map((slot) => slotCard(slot)).join('')}</div>
                            <div class="mt-3 flex justify-end gap-2">
                                <button type="button" class="sj-ui-btn sj-ui-btn--ghost" data-review="reject" data-user="${review.field_user_id}">{{ __('Rechazar') }}</button>
                                <button type="button" class="sj-ui-btn sj-ui-btn--primary" data-review="approve" data-user="${review.field_user_id}">{{ __('Actualizar') }}</button>
                            </div>
                        </div>
                    `).join('');
                    const body = document.getElementById('revista-photos-body');
                    body.innerHTML = `<div class="grid grid-cols-2 gap-2">${official}</div>${reviews}`;
                    body.querySelectorAll('[data-review]').forEach((button) => {
                        button.addEventListener('click', () => {
                            const template = button.dataset.review === 'approve' ? approveUrl : rejectUrl;
                            const url = template.replace('__W__', data.weapon.id).replace('__U__', button.dataset.user);
                            fetch(url, { method: 'POST', headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' } })
                                .then(() => window.location.reload());
                        });
                    });
                };

                document.querySelectorAll('[data-revista-photos]').forEach((button) => {
                    button.addEventListener('click', () => {
                        photoUrl = button.dataset.revistaPhotos;
                        fetch(photoUrl, { headers: { 'Accept': 'application/json' } })
                            .then((response) => response.json())
                            .then((data) => {
                                paintPhotos(data);
                                open('revista-photos');
                            });
                    });
                });
            })();
        </script>
    @endpush
</x-app-layout>
