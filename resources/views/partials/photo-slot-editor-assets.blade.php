<div id="photo_source_modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-sm rounded bg-white shadow-lg">
        <div class="border-b px-4 py-3 text-sm font-semibold text-gray-800">{{ __('Agregar imagen') }}</div>
        <div class="p-4 space-y-2 text-sm text-gray-700">
            <button id="photo_source_camera" type="button" class="sj-ui-btn sj-ui-btn--ghost w-full">{{ __('Tomar foto') }}</button>
            <button id="photo_source_gallery" type="button" class="sj-ui-btn sj-ui-btn--ghost w-full">{{ __('Elegir de galería') }}</button>
        </div>
        <div class="flex justify-end border-t px-4 py-2">
            <button id="photo_source_cancel" type="button" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancelar') }}</button>
        </div>
    </div>
</div>

<div id="photo_action_modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
    <div class="w-full max-w-sm rounded bg-white shadow-lg">
        <div class="border-b px-4 py-3 text-sm font-semibold text-gray-800">{{ __('Editar imagen') }}</div>
        <div class="p-4 space-y-2 text-sm text-gray-700">
            <button id="photo_action_crop" type="button" class="sj-ui-btn sj-ui-btn--ghost w-full">{{ __('Recortar o mover') }}</button>
            <button id="photo_action_change" type="button" class="sj-ui-btn sj-ui-btn--ghost w-full">{{ __('Cambiar imagen') }}</button>
        </div>
        <div class="flex justify-end border-t px-4 py-2">
            <button id="photo_action_cancel" type="button" class="text-sm text-gray-600 hover:text-gray-900">{{ __('Cancelar') }}</button>
        </div>
    </div>
</div>

<div id="image_editor_modal" class="fixed inset-0 z-50 hidden items-center justify-center overflow-hidden bg-black/50 p-2 sm:p-4">
    <div class="sj-image-editor-panel flex max-h-[calc(100dvh-0.5rem)] w-full max-w-3xl flex-col overflow-hidden rounded-lg bg-white shadow-lg sm:max-h-[calc(100dvh-2rem)]">
        <div class="flex shrink-0 items-center justify-between border-b px-4 py-3">
            <h3 class="sj-type-section text-gray-800">{{ __('Editar imagen') }}</h3>
            <button id="image_editor_close" type="button" class="text-sm text-gray-500 hover:text-gray-700">{{ __('Cerrar') }}</button>
        </div>
        <div class="min-h-0 flex-1 overflow-y-auto overscroll-contain p-3 sm:p-4">
            <div class="sj-image-editor-canvas w-full overflow-auto rounded bg-gray-50">
                <img id="image_editor_image" alt="Editor" class="mx-auto w-full max-w-full object-contain" />
            </div>
        </div>
        <div class="shrink-0 border-t bg-white pb-[max(0.75rem,env(safe-area-inset-bottom))]">
            <div class="flex flex-col gap-3 px-3 py-3 sm:px-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                    <div class="flex flex-wrap gap-2">
                        <button id="image_editor_rotate_left" type="button" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm">{{ __('Girar izquierda') }}</button>
                        <button id="image_editor_rotate_right" type="button" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm">{{ __('Girar derecha') }}</button>
                    </div>
                    <div class="flex w-full flex-col gap-2 sm:flex-1 sm:flex-row sm:flex-wrap sm:items-center">
                        <span class="text-xs font-medium text-gray-600">{{ __('Ajuste fino') }}</span>
                        <input id="image_editor_rotate_fine" type="range" min="-10" max="10" step="0.1" value="0" class="h-2 w-full min-w-0 flex-1 cursor-pointer accent-indigo-600 sm:min-w-[8rem]">
                        <span id="image_editor_rotate_value" class="text-xs font-medium text-gray-600 sm:w-14 sm:text-right">0.0°</span>
                        <button id="image_editor_rotate_reset" type="button" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--sm">{{ __('Restablecer') }}</button>
                    </div>
                </div>
                <div class="flex w-full gap-2 border-t border-gray-100 pt-3">
                    <button id="image_editor_cancel" type="button" class="min-h-11 flex-1 rounded-md border border-gray-300 px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50 sm:min-h-0 sm:flex-none sm:border-0 sm:bg-transparent sm:py-1">{{ __('Cancelar') }}</button>
                    <button id="image_editor_crop" type="button" class="min-h-11 flex-1 rounded-md bg-indigo-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700 disabled:cursor-wait disabled:opacity-60 sm:min-h-0 sm:flex-none sm:py-1">{{ __('Guardar') }}</button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="weapon-photo-toast" class="pointer-events-none fixed inset-x-4 bottom-[max(1rem,env(safe-area-inset-bottom))] z-[60] mx-auto hidden max-w-md rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-center text-sm font-semibold text-emerald-800 shadow-lg sm:inset-x-auto sm:right-4 sm:mx-0" role="status" aria-live="polite"></div>

<div id="weapon-photo-alert-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl" role="alertdialog" aria-modal="true">
        <h3 class="sj-type-section text-slate-900">{{ __('Aviso') }}</h3>
        <p id="weapon-photo-alert-message" class="mt-3 text-sm text-slate-600"></p>
        <div class="mt-5 flex justify-end">
            <button type="button" id="weapon-photo-alert-ok" class="sj-ui-btn sj-ui-btn--primary">{{ __('Entendido') }}</button>
        </div>
    </div>
</div>

<div id="weapon-photo-confirm-modal" class="fixed inset-0 z-[60] hidden items-center justify-center bg-black/40 p-4">
    <div class="w-full max-w-md rounded-xl bg-white p-5 shadow-xl" role="alertdialog" aria-modal="true" aria-labelledby="weapon-photo-confirm-title">
        <h3 id="weapon-photo-confirm-title" class="sj-type-section text-slate-900">{{ __('Atención') }}</h3>
        <p id="weapon-photo-confirm-message" class="mt-3 text-sm text-slate-600"></p>
        <div class="mt-5 flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
            <button type="button" id="weapon-photo-confirm-cancel" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</button>
            <button type="button" id="weapon-photo-confirm-discard" class="sj-ui-btn sj-ui-btn--ghost sj-ui-btn--danger">{{ __('Salir sin guardar') }}</button>
            <button type="button" id="weapon-photo-confirm-save" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar cambios') }}</button>
        </div>
    </div>
</div>

<input id="photo_pick_gallery" type="file" accept="image/jpeg,image/png,image/webp,image/*" class="hidden">
<input id="photo_pick_camera" type="file" accept="image/jpeg,image/png,image/webp,image/*" capture="environment" class="hidden">

@once
    @push('styles')
        <link rel="stylesheet" href="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.css">
        <style>
            .weapon-photo-surface-wrap > .sj-paste-proxy {
                position: absolute;
                inset: 0;
                z-index: 20;
                background: transparent;
                border: 0;
                color: transparent;
                caret-color: transparent;
                opacity: 0;
                font-size: 1px;
                line-height: 1;
                padding: 0;
                margin: 0;
                outline: none;
                user-select: none;
                -webkit-user-select: none;
                white-space: pre-wrap;
                word-break: break-word;
            }

            .weapon-photo-surface-wrap > .sj-paste-proxy::selection {
                background: transparent;
            }

            .sj-image-editor-canvas {
                max-height: min(42dvh, 380px);
            }

            .sj-image-editor-canvas .cropper-container,
            .sj-image-editor-canvas .cropper-canvas,
            .sj-image-editor-canvas .cropper-wrap-box,
            .sj-image-editor-canvas img {
                max-height: min(42dvh, 380px) !important;
            }

            @media (min-width: 640px) {
                .sj-image-editor-canvas {
                    max-height: 55vh;
                }

                .sj-image-editor-canvas .cropper-container,
                .sj-image-editor-canvas .cropper-canvas,
                .sj-image-editor-canvas .cropper-wrap-box,
                .sj-image-editor-canvas img {
                    max-height: 55vh !important;
                }
            }

            .sj-toggle--photo-mode {
                display: inline-flex;
                align-items: center;
                cursor: pointer;
                user-select: none;
            }

            .sj-toggle--photo-mode .sj-toggle-input {
                position: absolute;
                width: 1px;
                height: 1px;
                padding: 0;
                margin: -1px;
                overflow: hidden;
                clip: rect(0, 0, 0, 0);
                white-space: nowrap;
                border: 0;
            }

            .sj-toggle--photo-mode .sj-toggle-track {
                position: relative;
                display: inline-flex;
                align-items: center;
                width: 4.875rem;
                height: 1.375rem;
                border-radius: 9999px;
                background: #fff1f2;
                border: 1px solid #fda4af;
                box-shadow:
                    inset 0 1px 2px rgba(255, 255, 255, 0.7),
                    0 0 0 1px rgba(251, 113, 133, 0.35),
                    0 0 10px rgba(244, 63, 94, 0.28);
                transition: background 0.22s ease, border-color 0.22s ease, box-shadow 0.22s ease;
                flex-shrink: 0;
                overflow: hidden;
            }

            .sj-toggle--photo-mode .sj-toggle-track__text {
                position: absolute;
                top: 50%;
                transform: translateY(-50%);
                font-size: 0.625rem;
                font-weight: 600;
                letter-spacing: 0.02em;
                line-height: 1;
                pointer-events: none;
                transition: opacity 0.18s ease, color 0.22s ease;
                white-space: nowrap;
            }

            .sj-toggle--photo-mode .sj-toggle-track__text--idle {
                right: 0.4rem;
                color: #be123c;
                opacity: 1;
            }

            .sj-toggle--photo-mode .sj-toggle-track__text--active {
                left: 0.4rem;
                color: #15803d;
                opacity: 0;
            }

            .sj-toggle--photo-mode .sj-toggle-knob {
                position: absolute;
                top: 2px;
                left: 2px;
                width: 1rem;
                height: 1rem;
                border-radius: 9999px;
                background: #ffffff;
                border: 1px solid #fecdd3;
                box-shadow:
                    0 1px 2px rgba(190, 18, 60, 0.12),
                    0 0 6px rgba(244, 63, 94, 0.2);
                transition: transform 0.2s cubic-bezier(0.4, 0, 0.2, 1), border-color 0.22s ease, box-shadow 0.22s ease;
            }

            .sj-toggle--photo-mode .sj-toggle-input:checked + .sj-toggle-track {
                background: #ecfdf5;
                border-color: #6ee7b7;
                box-shadow:
                    inset 0 1px 2px rgba(255, 255, 255, 0.75),
                    0 0 0 1px rgba(52, 211, 153, 0.4),
                    0 0 10px rgba(16, 185, 129, 0.3);
            }

            .sj-toggle--photo-mode .sj-toggle-input:checked + .sj-toggle-track .sj-toggle-knob {
                transform: translateX(3.625rem);
                border-color: #a7f3d0;
                box-shadow:
                    0 1px 2px rgba(21, 128, 61, 0.12),
                    0 0 6px rgba(16, 185, 129, 0.22);
            }

            .sj-toggle--photo-mode .sj-toggle-input:checked + .sj-toggle-track .sj-toggle-track__text--idle {
                opacity: 0;
            }

            .sj-toggle--photo-mode .sj-toggle-input:checked + .sj-toggle-track .sj-toggle-track__text--active {
                opacity: 1;
            }

            .sj-toggle--photo-mode .sj-toggle-input:focus-visible + .sj-toggle-track {
                outline: 2px solid #6366f1;
                outline-offset: 2px;
            }

            .sj-toggle--photo-mode .sj-toggle-input:disabled + .sj-toggle-track {
                opacity: 0.55;
                cursor: not-allowed;
            }
        </style>
    @endpush

    @push('scripts')
        <script src="https://unpkg.com/cropperjs@1.6.2/dist/cropper.min.js"></script>
        @vite('resources/js/weapon-photo-editor.js')
    @endpush
@endonce
