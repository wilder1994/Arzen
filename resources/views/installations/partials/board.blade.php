@php
    $hasPin = \App\Support\MapCoordinates::isFilled($installation->latitude, $installation->longitude);
    $activePosts = $installation->posts->reject(fn ($post) => $post->isArchived());
    $armerillos = $activePosts->filter(fn ($post) => $post->custody_role === \App\Support\PostCustodyRole::ARMERILLO);
    $maintenanceByOwner = $activePosts
        ->filter(fn ($post) => $post->custody_role === \App\Support\PostCustodyRole::ARMERILLO_PARA_MANTENIMIENTO)
        ->groupBy(fn ($post) => (string) ($post->owner_responsible_user_id ?? ''));
    $fieldPosts = $installation->posts->filter(fn ($post) => ! filled($post->custody_role));
    $activeFieldPosts = $fieldPosts->reject(fn ($post) => $post->isArchived());
    $postCount = $activeFieldPosts->count();
    $workerCount = $activeFieldPosts->sum(fn ($post) => $post->workers->count());
    $weaponCount = $activePosts->sum(fn ($post) => $post->assignments->count());
    $blank = '—';
@endphp

<div
    class="sj-site-board"
    x-data="{
        historyOpen: false,
        open: {{ $errors->has('post_name') || ($errors->has('name') && old('editing_post')) ? 'true' : 'false' }},
        mode: '{{ old('editing_post') ? 'edit' : (old('armerillo') ? 'armerillo' : 'create') }}',
        action: '{{ old('editing_post') ? route('installations.posts.update', [$installation, old('editing_post')]) : (old('armerillo') ? route('installations.armerillos.store', $installation) : route('installations.posts.store', $installation)) }}',
        postId: '{{ old('editing_post') }}',
        postName: '{{ old('editing_post') ? old('name') : old('post_name') }}',
        openCreate() {
            this.mode = 'create';
            this.action = @js(route('installations.posts.store', $installation));
            this.postId = '';
            this.postName = '';
            this.open = true;
        },
        openArmerillo() {
            this.mode = 'armerillo';
            this.action = @js(route('installations.armerillos.store', $installation));
            this.postId = '';
            this.postName = '';
            this.open = true;
        },
        openEdit(action, id, name) {
            this.mode = 'edit';
            this.action = action;
            this.postId = id;
            this.postName = name;
            this.open = true;
        },
    }"
    @keydown.escape.window="open = false"
>
    <section class="sj-site-top">
        <div class="sj-site-map">
            @if ($hasPin)
                <iframe
                    title="{{ $installation->name }}"
                    src="https://maps.google.com/maps?q={{ $installation->latitude }},{{ $installation->longitude }}&z=16&output=embed"
                ></iframe>
            @else
                <p>{{ __('Ubicación pendiente') }}</p>
            @endif
        </div>

        <div class="sj-site-facts">
            <dl>
                <div>
                    <dt>{{ __('Cliente') }}</dt>
                    <dd>{{ $client->name }}</dd>
                </div>
                <div>
                    <dt>{{ __('Instalación') }}</dt>
                    <dd>{{ $installation->name }}@if ($installation->is_client_site) · {{ __('Principal') }}@endif</dd>
                </div>
                <div>
                    <dt>{{ __('Dirección') }}</dt>
                    <dd>{{ $installation->address ?: $blank }}</dd>
                </div>
                <div>
                    <dt>{{ __('Ciudad') }}</dt>
                    <dd>{{ $installation->city ?: $blank }}</dd>
                </div>
                <div>
                    <dt>{{ __('Departamento') }}</dt>
                    <dd>{{ $installation->department ?: $blank }}</dd>
                </div>
                <div>
                    <dt>{{ __('Puestos') }}</dt>
                    <dd>{{ $postCount }}</dd>
                </div>
                <div>
                    <dt>{{ __('Armas') }}</dt>
                    <dd>{{ $weaponCount }}</dd>
                </div>
                <div>
                    <dt>{{ __('Trabajadores') }}</dt>
                    <dd>{{ $workerCount }}</dd>
                </div>
            </dl>
            <div class="sj-site-facts__actions">
                <a
                    href="{{ $installation->is_client_site ? \App\Http\Controllers\InstallationController::clientEditorUrl($installation) : route('installations.form', $installation) }}"
                    class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm"
                    @if ($installation->is_client_site) onclick="return confirm(@js(__('Esta instalación solo se edita desde el cliente. ¿Desea ir allá?')))" @endif
                >{{ __('Editar instalación') }}</a>
                @can('create', App\Models\Post::class)
                    <button type="button" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm" @click="openCreate()">{{ __('Agregar puesto') }}</button>
                    <button type="button" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm" @click="openArmerillo()">{{ __('Agregar armerillo') }}</button>
                @endcan
                <button type="button" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm" @click="historyOpen = true">{{ __('Historial') }}</button>
            </div>
        </div>
    </section>

    @foreach ($armerillos as $armerillo)
        @include('installations.partials.armerillo-safe', [
            'post' => $armerillo,
            'maintenanceAssignments' => ($maintenanceByOwner[(string) ($armerillo->owner_responsible_user_id ?? '')] ?? collect())
                ->flatMap(fn ($maintenancePost) => $maintenancePost->assignments),
        ])
    @endforeach

    <div class="sj-site-posts">
        @forelse ($fieldPosts as $post)
            <article id="puesto-{{ $post->id }}" @class(['sj-site-post', 'is-archived' => $post->isArchived()])>
                <header class="sj-site-post__head">
                    <h3 class="sj-site-post__title">
                        {{ __('Puesto') }}: {{ $post->name }}
                        @if ($post->isArchived())
                            <span class="sj-chip">{{ __('Archivado') }}</span>
                        @endif
                    </h3>
                    <div class="sj-site-post__tools">
                        @if ($post->isArchived())
                            @can('restore', $post)
                                <form method="POST" action="{{ route('posts.restore', $post) }}" onsubmit="return confirm(@js(__('¿Reactivar este puesto?')))">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="return" value="installation">
                                    <button type="submit" class="sj-ui-link">{{ __('Reactivar') }}</button>
                                </form>
                            @endcan
                        @else
                            @can('update', $post)
                                <button type="button" class="sj-ui-link" @click="openEdit(@js(route('installations.posts.update', [$installation, $post])), '{{ $post->id }}', @js($post->name))">{{ __('Editar') }}</button>
                            @endcan
                            @can('delete', $post)
                                <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm(@js(__('Si archiva este puesto, las armas y los trabajadores quedan sin asignación, pendientes de una nueva asignación.')))">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="return" value="installation">
                                    <button type="submit" class="sj-ui-link sj-ui-link--danger">{{ __('Archivar') }}</button>
                                </form>
                            @endcan
                        @endif
                    </div>
                </header>

                @if ($post->isArchived())
                    <p class="sj-site-empty">{{ __('Archivado. Las personas y las armas quedaron sin puesto.') }}</p>
                @else
                <div class="sj-site-post__split">
                    <section>
                        <h4>{{ __('Personas') }}</h4>
                        <form method="POST" action="{{ route('installations.posts.workers.store', [$installation, $post]) }}" class="sj-site-finder" data-finder data-search-url="{{ route('installations.workers.search', $installation) }}" data-post="{{ $post->id }}" data-key="workers">
                            @csrf
                            <input type="search" class="sj-ui-field__control" placeholder="{{ __('Nombre o cédula') }}" aria-label="{{ __('Buscar trabajador por nombre o documento') }}" autocomplete="off">
                            <input type="hidden" name="worker_id" data-finder-id>
                            <input type="hidden" name="confirm" value="0" data-finder-confirm>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm" data-finder-add disabled>{{ __('Agregar') }}</button>
                            <div class="sj-site-finder__results" data-finder-results hidden></div>
                        </form>
                        @forelse ($post->workers as $worker)
                            <div class="sj-site-line">
                                <span>{{ $worker->name }}@if ($worker->document) · {{ $worker->document }}@endif</span>
                                <form method="POST" action="{{ route('installations.posts.workers.destroy', [$installation, $post, $worker]) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="sj-ui-link">{{ __('Quitar') }}</button>
                                </form>
                            </div>
                        @empty
                            <p class="sj-site-empty">{{ __('Sin personas.') }}</p>
                        @endforelse
                    </section>

                    <section>
                        <h4>{{ __('Armas') }}</h4>
                        <form method="POST" action="{{ route('installations.posts.weapons.store', [$installation, $post]) }}" class="sj-site-finder" data-finder data-search-url="{{ route('installations.weapons.search', $installation) }}" data-post="{{ $post->id }}" data-key="weapons" data-field="weapon_id">
                            @csrf
                            <input type="search" class="sj-ui-field__control" placeholder="{{ __('Serie, permiso, tipo o calibre') }}" aria-label="{{ __('Buscar arma') }}" autocomplete="off">
                            <input type="hidden" name="weapon_id" data-finder-id>
                            <input type="hidden" name="confirm" value="0" data-finder-confirm>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm" data-finder-add disabled>{{ __('Agregar') }}</button>
                            <div class="sj-site-finder__results" data-finder-results hidden></div>
                        </form>
                        @forelse ($post->assignments as $assignment)
                            @php $weapon = $assignment->weapon; @endphp
                            @if ($weapon)
                                <div class="sj-site-line">
                                    <span>
                                        {{ collect([$weapon->weapon_type, $weapon->serial_number, $weapon->caliber])->filter()->implode(' · ') }}
                                        @if ($weapon->activeWorkerAssignment?->worker)
                                            · {{ $weapon->activeWorkerAssignment->worker->name }}
                                        @endif
                                    </span>
                                    <form method="POST" action="{{ route('installations.posts.weapons.destroy', [$installation, $post, $weapon]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="sj-ui-link">{{ __('Quitar') }}</button>
                                    </form>
                                </div>
                            @endif
                        @empty
                            <p class="sj-site-empty">{{ __('Sin armas.') }}</p>
                        @endforelse
                    </section>
                </div>
                @endif
            </article>
        @empty
            @if ($armerillos->isEmpty())
                <p class="sj-company__hint">{{ __('Esta instalación aún no tiene puestos.') }}</p>
            @endif
        @endforelse
    </div>

    <div class="sj-site-modal" x-show="historyOpen" x-cloak @keydown.escape.window="historyOpen = false">
        <div class="sj-site-modal__backdrop" @click="historyOpen = false"></div>
        <div class="sj-site-modal__panel sj-site-modal__panel--history" @click.stop>
            <div class="sj-site-post__head">
                <h3 class="sj-form-section__title">{{ __('Historial') }}</h3>
                <button type="button" class="sj-ui-link" @click="historyOpen = false">{{ __('Cerrar') }}</button>
            </div>
            @forelse ($histories as $log)
                <article class="sj-site-history">
                    <p class="sj-site-history__meta">{{ $log->created_at?->timezone(config('app.timezone'))->format('d/m/Y H:i') }}@if ($log->user) · {{ $log->user->name }}@endif</p>
                    @if (! empty($log->after['note']))
                        <p>{{ $log->after['note'] }}</p>
                    @endif
                    @foreach (['name' => __('Nombre'), 'address' => __('Dirección'), 'city' => __('Ciudad'), 'department' => __('Departamento')] as $key => $label)
                        @if (is_array($log->before) && array_key_exists($key, $log->before) && ($log->before[$key] ?? null) !== ($log->after[$key] ?? null))
                            <p>{{ $label }}: {{ $log->before[$key] ?? '—' }} → {{ $log->after[$key] ?? '—' }}</p>
                        @endif
                    @endforeach
                </article>
            @empty
                <p class="sj-site-empty">{{ __('No hay entradas en el historial.') }}</p>
            @endforelse
        </div>
    </div>

    <div class="sj-site-modal" x-show="open" x-cloak>
        <div class="sj-site-modal__backdrop" @click="open = false"></div>
        <form method="POST" :action="action" class="sj-site-modal__panel" @click.stop>
            @csrf
            <input type="hidden" name="_method" value="PATCH" :disabled="mode !== 'edit'">
            <input type="hidden" name="editing_post" :value="postId" :disabled="mode !== 'edit'">
            <h3 class="sj-form-section__title" x-text="mode === 'edit' ? @js(__('Editar puesto')) : (mode === 'armerillo' ? @js(__('Agregar armerillo')) : @js(__('Agregar puesto')))"></h3>
            <div class="sj-ui-field">
                <label class="sj-ui-field__label" x-text="mode === 'armerillo' ? @js(__('Nombre del armerillo')) : @js(__('Nombre del puesto'))">{{ __('Nombre del puesto') }}</label>
                <input type="hidden" name="armerillo" value="1" :disabled="mode !== 'armerillo'">
                <input name="post_name" type="text" maxlength="255" class="sj-ui-field__control" x-show="mode !== 'edit'" x-model="postName" :disabled="mode === 'edit'" :required="mode !== 'edit'">
                <input name="name" type="text" maxlength="255" class="sj-ui-field__control" x-show="mode === 'edit'" x-model="postName" :disabled="mode !== 'edit'" :required="mode === 'edit'">
                <x-input-error :messages="$errors->get('post_name')" />
                <x-input-error :messages="$errors->get('name')" />
            </div>
            <div class="sj-site-modal__actions">
                <button type="button" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm" @click="open = false">{{ __('Cancelar') }}</button>
                <button type="submit" class="sj-ui-btn sj-ui-btn--primary sj-ui-btn--sm" x-text="mode === 'edit' ? @js(__('Guardar')) : @js(__('Crear'))">{{ __('Crear') }}</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        (() => {
            const escapeHtml = (value) => String(value ?? '').replace(/[&<>"']/g, (char) => ({
                '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;',
            }[char]));

            document.querySelectorAll('[data-finder]').forEach((finder) => {
                const input = finder.querySelector('input[type="search"]');
                const results = finder.querySelector('[data-finder-results]');
                const idInput = finder.querySelector('[data-finder-id]');
                const confirmInput = finder.querySelector('[data-finder-confirm]');
                const addButton = finder.querySelector('[data-finder-add]');
                if (!input || !results || !idInput || !addButton) {
                    return;
                }

                const clearSelection = () => {
                    idInput.value = '';
                    if (confirmInput) {
                        confirmInput.value = '0';
                    }
                    addButton.disabled = true;
                    finder.dataset.confirmText = '';
                };

                let timer = null;
                input.addEventListener('input', () => {
                    clearSelection();
                    clearTimeout(timer);
                    const term = input.value.trim();
                    if (term.length < 2) {
                        results.hidden = true;
                        results.innerHTML = '';
                        return;
                    }
                    timer = setTimeout(async () => {
                        const url = new URL(finder.dataset.searchUrl, window.location.origin);
                        url.searchParams.set('q', term);
                        url.searchParams.set('post', finder.dataset.post || '');
                        const response = await fetch(url, { headers: { 'Accept': 'application/json' } });
                        if (!response.ok) {
                            return;
                        }
                        const data = await response.json();
                        const items = data[finder.dataset.key || 'workers'] || [];
                        if (items.length === 0) {
                            results.hidden = false;
                            results.innerHTML = '<p class="sj-site-empty">{{ __('Sin coincidencias.') }}</p>';
                            return;
                        }
                        results.hidden = false;
                        results.innerHTML = items.map((item) => `
                            <button type="button" class="sj-site-finder__option" data-id="${escapeHtml(item.id)}" data-needs-confirm="${item.needs_confirm ? '1' : '0'}" data-confirm="${escapeHtml(item.confirm)}">
                                <strong>${escapeHtml(item.label)}</strong>
                                <small>${escapeHtml(item.hint)}</small>
                            </button>
                        `).join('');
                    }, 200);
                });

                results.addEventListener('click', (event) => {
                    const option = event.target.closest('[data-id]');
                    if (!option) {
                        return;
                    }
                    results.querySelectorAll('.is-selected').forEach((node) => node.classList.remove('is-selected'));
                    option.classList.add('is-selected');
                    idInput.value = option.dataset.id || '';
                    finder.dataset.confirmText = option.dataset.needsConfirm === '1' ? (option.dataset.confirm || '') : '';
                    if (confirmInput) {
                        confirmInput.value = option.dataset.needsConfirm === '1' ? '1' : '0';
                    }
                    addButton.disabled = idInput.value === '';
                });

                finder.addEventListener('submit', (event) => {
                    if (idInput.value === '') {
                        event.preventDefault();
                        return;
                    }
                    if (finder.dataset.confirmText && !window.confirm(finder.dataset.confirmText)) {
                        event.preventDefault();
                    }
                });
            });
        })();
    </script>
@endpush
