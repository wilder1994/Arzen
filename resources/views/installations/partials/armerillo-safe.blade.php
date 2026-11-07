@php
    $ready = $post->assignments->filter(fn ($assignment) => $assignment->weapon);
    $maintenance = ($maintenanceAssignments ?? collect())->filter(fn ($assignment) => $assignment->weapon);
    $ammoRows = $ready->concat($maintenance)
        ->groupBy(fn ($assignment) => implode('|', [
            $assignment->weapon->weapon_type ?: '—',
            $assignment->weapon->brand ?: '—',
            $assignment->weapon->caliber ?: '—',
        ]))
        ->map(function ($rows) {
            $weapon = $rows->first()->weapon;

            return [
                'type' => $weapon->weapon_type ?: '—',
                'brand' => $weapon->brand ?: '—',
                'caliber' => $weapon->caliber ?: '—',
                'quantity' => $rows->sum(fn ($assignment) => (int) ($assignment->ammo_count ?? 0)),
            ];
        })
        ->sortBy(['type', 'brand'])
        ->values();
@endphp

<article id="armerillo-{{ $post->id }}" class="armerillo-safe" data-armerillo-safe data-safe-name="{{ $post->name }}">
    <div class="armerillo-safe__row">
        <div class="armerillo-safe__stage">
            <svg class="armerillo-safe__sheet" viewBox="-200 0 608 488" data-safe-body aria-hidden="true"></svg>
            <div class="armerillo-safe__shelves" data-safe-shelves>
                <button type="button" data-shelf="listas">{{ __('Listas para asignar') }} · {{ $ready->count() }}</button>
                <button type="button" data-shelf="mantenimiento">{{ __('Para mantenimiento') }} · {{ $maintenance->count() }}</button>
                <button type="button" data-shelf="municion">{{ __('Munición') }} · {{ $ammoRows->count() }}</button>
            </div>
        </div>

        <div class="armerillo-safe__detail">
            @can('delete', $post)
                <form method="POST" action="{{ route('posts.destroy', $post) }}" onsubmit="return confirm(@js(__('Si archiva este armerillo, las armas quedan sin puesto y siguen con el cliente.')))">
                    @csrf
                    @method('DELETE')
                    <input type="hidden" name="return" value="installation">
                    <button type="submit" class="sj-ui-link sj-ui-link--danger">{{ __('Archivar') }}</button>
                </form>
            @endcan
            <div data-safe-detail hidden>
        <div data-panel="listas" hidden>
            <h4>{{ __('Listas para asignar') }}</h4>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Serie') }}</th>
                        <th>{{ __('Tipo') }}</th>
                        <th>{{ __('Marca') }}</th>
                        <th>{{ __('Calibre') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ready as $assignment)
                        <tr>
                            <td>{{ $assignment->weapon->serial_number ?: '—' }}</td>
                            <td>{{ $assignment->weapon->weapon_type ?: '—' }}</td>
                            <td>{{ $assignment->weapon->brand ?: '—' }}</td>
                            <td>{{ $assignment->weapon->caliber ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">{{ __('Sin armas listas.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-panel="mantenimiento" hidden>
            <h4>{{ __('Para mantenimiento') }}</h4>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Serie') }}</th>
                        <th>{{ __('Tipo') }}</th>
                        <th>{{ __('Marca') }}</th>
                        <th>{{ __('Calibre') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($maintenance as $assignment)
                        <tr>
                            <td>{{ $assignment->weapon->serial_number ?: '—' }}</td>
                            <td>{{ $assignment->weapon->weapon_type ?: '—' }}</td>
                            <td>{{ $assignment->weapon->brand ?: '—' }}</td>
                            <td>{{ $assignment->weapon->caliber ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">{{ __('Sin armas para mantenimiento.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div data-panel="municion" hidden>
            <h4>{{ __('Munición') }}</h4>
            <p>{{ __('Cantidad por tipo, marca y calibre. El lote todavía no se guarda en el inventario.') }}</p>
            <table>
                <thead>
                    <tr>
                        <th>{{ __('Tipo') }}</th>
                        <th>{{ __('Marca') }}</th>
                        <th>{{ __('Calibre') }}</th>
                        <th>{{ __('Cantidad') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($ammoRows as $row)
                        <tr>
                            <td>{{ $row['type'] }}</td>
                            <td>{{ $row['brand'] }}</td>
                            <td>{{ $row['caliber'] }}</td>
                            <td>{{ $row['quantity'] > 0 ? $row['quantity'] : '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">{{ __('Sin munición en esta caja.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
            </div>
        </div>
    </div>
</article>

@once
    @push('styles')
        <style>
            .armerillo-safe { margin: 0; padding: 0.35rem 0.5rem; background: #fff; border: 1px solid #e2e8f0; border-radius: 0.9rem; }
            .armerillo-safe__row { display: flex; align-items: stretch; gap: 0.75rem; }
            .armerillo-safe__stage { position: relative; width: min(100%, 403px); flex: 0 0 min(100%, 403px); }
            .armerillo-safe__sheet { display: block; width: 100%; height: auto; }
            .armerillo-safe__shelves { position: absolute; left: 41%; top: 14%; width: 52%; height: 68%; display: none; flex-direction: column; gap: 6px; }
            .armerillo-safe.is-open .armerillo-safe__shelves { display: flex; }
            .armerillo-safe__shelves button { flex: 1; text-align: left; padding: 8px 10px; border: 1px solid #9aa8b2; border-radius: 4px; background: #f4f7f8; cursor: pointer; font-weight: 600; font-size: 0.8rem; }
            .armerillo-safe__shelves button.is-selected { border-color: #0b6f8a; }
            .armerillo-safe__detail { flex: 1; min-width: 0; max-height: var(--safe-h, 320px); display: flex; flex-direction: column; }
            .armerillo-safe__detail [data-safe-detail] { overflow: auto; min-height: 0; }
            .armerillo-safe__detail table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
            .armerillo-safe__detail th, .armerillo-safe__detail td { text-align: left; padding: 0.4rem 0.5rem; border-bottom: 1px solid #e2e8ee; }
            .armerillo-safe__detail h4 { margin: 0 0 0.5rem; }
        </style>
    @endpush
    @push('scripts')
        <script>
            (() => {
                const hingeX = 36;
                const doorRight = 388;
                const doorTop = 34;
                const doorBottom = 432;
                const openFreeX = -176;
                const openFreeTop = 4;
                const openFreeBottom = 470;
                const mix = (a, b, t) => a + (b - a) * t;
                const point = (u, v, progress) => {
                    const freeTop = { x: mix(doorRight, openFreeX, progress), y: mix(doorTop, openFreeTop, progress) };
                    const freeBottom = { x: mix(doorRight, openFreeX, progress), y: mix(doorBottom, openFreeBottom, progress) };
                    const top = { x: mix(hingeX, freeTop.x, u), y: mix(doorTop, freeTop.y, u) };
                    const bottom = { x: mix(hingeX, freeBottom.x, u), y: mix(doorBottom, freeBottom.y, u) };
                    return { x: mix(top.x, bottom.x, v), y: mix(top.y, bottom.y, v) };
                };
                const poly = (pairs) => pairs.map(([u, v, progress]) => {
                    const spot = point(u, v, progress);
                    return `${spot.x},${spot.y}`;
                }).join(' ');

                const paint = (svg, progress, open) => {
                    const steel = '#e7eef1';
                    const line = '#5d6b73';
                    const recess = '#d5e0e4';
                    const freeTop = point(1, 0, progress);
                    const freeBottom = point(1, 1, progress);
                    const edgeX = freeBottom.x - freeTop.x;
                    const edgeY = freeBottom.y - freeTop.y;
                    const edgeLength = Math.hypot(edgeX, edgeY) || 1;
                    let normalX = (-edgeY / edgeLength) * 20 * progress;
                    let normalY = (edgeX / edgeLength) * 20 * progress;
                    const towardHingeX = hingeX - (freeTop.x + freeBottom.x) / 2;
                    const towardHingeY = (doorTop + doorBottom) / 2 - (freeTop.y + freeBottom.y) / 2;
                    if (normalX * towardHingeX + normalY * towardHingeY > 0) {
                        normalX = -normalX;
                        normalY = -normalY;
                    }
                    const dial = point(0.4, 0.5, progress);
                    const handle = point(0.74, 0.38, progress);
                    const escape = (value) => String(value).replace(/[&<>"']/g, (char) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
                    const face = 'font-family="Figtree, ui-sans-serif, sans-serif" style="text-rendering:optimizeLegibility"';
                    const closedLine = (text, v, size, weight, color) => {
                        if (progress >= 0.02) {
                            return '';
                        }
                        const spot = point(0.5, v, 0);
                        return `<text x="${spot.x}" y="${spot.y}" text-anchor="middle" fill="${color}" font-size="${size}" font-weight="${weight}" ${face}>${escape(text)}</text>`;
                    };
                    const span = Math.hypot(point(1, 0.5, progress).x - point(0, 0.5, progress).x, point(1, 0.5, progress).y - point(0, 0.5, progress).y);
                    const scale = span / (doorRight - hingeX);
                    const frame = poly([[0.05, 0.045, progress], [0.95, 0.045, progress], [0.95, 0.955, progress], [0.05, 0.955, progress]]);
                    const frameInner = poly([[0.08, 0.075, progress], [0.92, 0.075, progress], [0.92, 0.925, progress], [0.08, 0.925, progress]]);
                    const bolts = [[0.09, 0.07], [0.91, 0.07], [0.09, 0.93], [0.91, 0.93]].map(([u, v]) => {
                        const spot = point(u, v, progress);
                        return `<circle cx="${spot.x}" cy="${spot.y}" r="3.5" fill="${recess}" stroke="${line}"></circle>`;
                    }).join('');
                    const pins = [0.34, 0.5, 0.66].map((v) => {
                        const spot = point(0.93, v, progress);
                        return `<rect x="${spot.x - 6}" y="${spot.y - 8}" width="12" height="16" rx="2" fill="${recess}" stroke="${line}"></rect>`;
                    }).join('');
                    const ticks = Array.from({ length: 12 }, (_, index) => {
                        const angle = (index / 12) * Math.PI * 2;
                        const inner = index % 3 === 0 ? 40 : 46;
                        return `<line x1="${Math.sin(angle) * inner}" y1="${-Math.cos(angle) * inner}" x2="${Math.sin(angle) * 54}" y2="${-Math.cos(angle) * 54}" stroke="${line}" stroke-width="${index % 3 === 0 ? 2 : 1}"></line>`;
                    }).join('');
                    const hinges = [78, 196, 314].map((y) => `
                        <rect x="2" y="${y}" width="36" height="58" rx="4" fill="#f7fbfc" stroke="${line}" stroke-width="1.5"></rect>
                        <rect x="14" y="${y + 6}" width="12" height="46" rx="6" fill="${recess}" stroke="${line}"></rect>
                        <line x1="20" y1="${y + 10}" x2="20" y2="${y + 48}" stroke="${line}"></line>
                    `).join('');
                    const name = svg.dataset.safeName || '';
                    const dialMarkup = progress < 0.4 ? `
                        <g transform="translate(${dial.x} ${dial.y}) scale(${scale})" data-safe-toggle="chapa" style="cursor:pointer">
                            <circle r="66" fill="${steel}" stroke="${line}" stroke-width="3"></circle>
                            <circle r="58" fill="${recess}" stroke="${line}"></circle>
                            ${ticks}
                            <circle r="28" fill="${steel}" stroke="${line}" stroke-width="2"></circle>
                            <line y1="-20" y2="20" stroke="${line}" stroke-width="3" stroke-linecap="round"></line>
                            <line x1="-17" y1="10" x2="17" y2="-10" stroke="${line}" stroke-width="3" stroke-linecap="round"></line>
                            <line x1="-17" y1="-10" x2="17" y2="10" stroke="${line}" stroke-width="3" stroke-linecap="round"></line>
                            <circle r="5" fill="#0b6f8a"></circle>
                            <circle cy="-62" r="3" fill="${line}"></circle>
                        </g>
                    ` : '';
                    svg.innerHTML = `
                        <rect x="16" y="16" width="388" height="452" rx="6" fill="#f7fbfc" stroke="${line}" stroke-width="3"></rect>
                        <rect x="${hingeX}" y="${doorTop}" width="${doorRight - hingeX}" height="${doorBottom - doorTop}" fill="${recess}"></rect>
                        <rect x="58" y="468" width="46" height="14" rx="2" fill="#f7fbfc" stroke="${line}" stroke-width="1.5"></rect>
                        <rect x="316" y="468" width="46" height="14" rx="2" fill="#f7fbfc" stroke="${line}" stroke-width="1.5"></rect>
                        <polygon points="${freeTop.x},${freeTop.y} ${freeTop.x + normalX},${freeTop.y + normalY} ${freeBottom.x + normalX},${freeBottom.y + normalY} ${freeBottom.x},${freeBottom.y}" fill="#f7fbfc" stroke="${line}"></polygon>
                        <polygon points="${poly([[0, 0, progress], [1, 0, progress], [1, 1, progress], [0, 1, progress]])}" fill="${steel}" stroke="${line}" stroke-width="1.5"></polygon>
                        <polygon points="${frame}" fill="none" stroke="${line}" stroke-width="1"></polygon>
                        <polygon points="${frameInner}" fill="none" stroke="${line}" stroke-width="1"></polygon>
                        ${bolts}
                        ${pins}
                        ${closedLine(name, 0.15, 26, 650, '#1c2a30')}
                        ${closedLine('Clic para abrir', 0.76, 17, 600, '#1c2a30')}
                        ${closedLine('Chapa o manija', 0.825, 15, 600, '#3a4a52')}
                        ${dialMarkup}
                        ${progress > 0.55 ? `<text x="${(handle.x + 22 * scale).toFixed(2)}" y="${(handle.y + 58 * scale).toFixed(2)}" text-anchor="start" fill="#1c2a30" font-size="18" font-weight="650" data-safe-toggle="manija" style="cursor:pointer;text-rendering:optimizeLegibility" font-family="Figtree, ui-sans-serif, sans-serif">Cerrar</text>` : ''}
                        <g transform="translate(${handle.x} ${handle.y}) scale(${scale})" data-safe-toggle="manija" style="cursor:pointer">
                            <rect x="-22" y="-16" width="44" height="16" rx="3" fill="${steel}" stroke="${line}" stroke-width="1.5"></rect>
                            <rect x="-16" y="0" width="32" height="92" rx="16" fill="${recess}" stroke="${line}" stroke-width="2"></rect>
                            <rect x="-8" y="10" width="16" height="72" rx="8" fill="${steel}" stroke="${line}"></rect>
                            <circle cy="108" r="18" fill="${steel}" stroke="${line}" stroke-width="2.5"></circle>
                            <circle cy="108" r="7" fill="${recess}" stroke="${line}"></circle>
                        </g>
                        ${hinges}
                    `;
                };

                document.querySelectorAll('[data-armerillo-safe]').forEach((safe) => {
                    const svg = safe.querySelector('[data-safe-body]');
                    const detail = safe.querySelector('[data-safe-detail]');
                    let progress = 0;
                    let open = false;
                    let frame = 0;
                    const render = () => {
                        paint(svg, progress, open);
                        const height = svg.getBoundingClientRect().height;
                        if (height) {
                            safe.style.setProperty('--safe-h', `${Math.round(height)}px`);
                        }
                    };
                    svg.dataset.safeName = safe.dataset.safeName || '';
                    const swing = (next) => {
                        const from = progress;
                        const started = performance.now();
                        cancelAnimationFrame(frame);
                        const step = (now) => {
                            const t = Math.min(1, (now - started) / 560);
                            const eased = 1 - (1 - t) ** 3;
                            progress = from + (next - from) * eased;
                            render();
                            if (t < 1) {
                                frame = requestAnimationFrame(step);
                                return;
                            }
                            safe.classList.toggle('is-open', open);
                            if (!open && detail) {
                                detail.hidden = true;
                            }
                        };
                        frame = requestAnimationFrame(step);
                    };
                    safe.addEventListener('click', (event) => {
                        const toggle = event.target.closest('[data-safe-toggle]');
                        if (toggle) {
                            open = !open;
                            if (!open) {
                                safe.classList.remove('is-open');
                            }
                            swing(open ? 1 : 0);
                            return;
                        }
                        const shelf = event.target.closest('[data-shelf]');
                        if (!shelf || !detail) {
                            return;
                        }
                        safe.querySelectorAll('[data-shelf]').forEach((button) => button.classList.toggle('is-selected', button === shelf));
                        detail.hidden = false;
                        detail.querySelectorAll('[data-panel]').forEach((panel) => {
                            panel.hidden = panel.dataset.panel !== shelf.dataset.shelf;
                        });
                    });
                    render();
                });
            })();
        </script>
    @endpush
@endonce
