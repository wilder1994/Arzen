@php
    $photoEditorConfig = [
        'csrfToken' => csrf_token(),
        'storeUrl' => route('vests.photos.store', $vest),
        'updateUrlBase' => route('vests.photos.update', [$vest, 0]),
        'photoSlotTotal' => count(\App\Models\VestPhoto::DESCRIPTIONS),
        'photoCountSelector' => '[data-vest-photo-count]',
        'txtSaving' => __('Guardando…'),
        'txtSave' => __('Guardar'),
        'txtSaved' => __('Imagen guardada'),
        'txtDeleted' => __('Foto eliminada.'),
        'txtGenericError' => __('No se pudo actualizar la foto.'),
        'txtCanvasError' => __('No se pudo procesar la imagen. Intente otra foto o reduzca el zoom.'),
        'txtNetworkError' => __('Sin conexión o la subida tardó demasiado. Espere y vuelva a intentar una sola vez.'),
        'txtSessionExpired' => __('Su sesión expiró. Recargue la página e intente de nuevo.'),
        'txtImagesOnly' => __('Solo puede usar archivos de imagen.'),
        'txtPendingPhoto' => __('Foto pendiente'),
        'txtPending' => __('Pendiente'),
        'txtDelete' => __('Eliminar'),
        'txtDeleteConfirmTitle' => __('Eliminar foto'),
        'txtDeleteConfirm' => __('¿Eliminar foto?'),
        'txtUploadInProgress' => __('Espere a que termine la subida en curso.'),
        'txtUnsavedEditor' => __('Tiene una imagen con cambios sin guardar en el editor. ¿Desea guardarla antes de continuar?'),
        'txtUnsavedChanges' => __('Realizó cambios en las fotos que aún no se han guardado. ¿Qué desea hacer?'),
        'txtEditSessionActive' => __('Sigue en modo edición de fotos. Si sale ahora, deberá activarlo de nuevo para seguir cargando imágenes.'),
        'txtToggleStartEdit' => __('Activar edición de fotos'),
        'txtToggleFinishEdit' => __('Finalizar edición de fotos'),
    ];
    $photoDescriptions = \App\Models\VestPhoto::DESCRIPTIONS;
    $photoSurfaceClass = 'h-36';
    $photoCardPadding = 'p-2';
    $photoMetaClass = 'text-xs';
@endphp

<div
    class="sj-ui-card sj-weapon-detail-photos p-4"
    @can('updatePhotos', $vest)
        data-photo-slot-editor
        data-photo-slot-editor-config='@json($photoEditorConfig)'
    @endcan
>
    <div class="sj-weapon-detail-section__head mb-4">
        <div>
            <h3 class="sj-type-section text-gray-900">{{ __('Fotografías del chaleco') }}</h3>
            <p class="sj-weapon-detail-section__hint mt-1 mb-0">{{ __('4 fotos: 2 vistas completas y 2 placas de serie. Clic, arrastre o pegue para cargar.') }}</p>
        </div>
        <div class="flex shrink-0 items-center gap-3">
            <span class="text-sm font-semibold text-gray-600" data-vest-photo-count>{{ $photosByDescription->count() }}/{{ count($photoDescriptions) }}</span>
            @can('updatePhotos', $vest)
                <label class="sj-toggle sj-toggle--photo-mode shrink-0" title="{{ __('Modo edición de fotos') }}">
                    <input id="photo_edit_toggle" type="checkbox" class="sj-toggle-input" aria-label="{{ __('Activar edición de fotos') }}">
                    <span class="sj-toggle-track" aria-hidden="true">
                        <span class="sj-toggle-track__text sj-toggle-track__text--idle">{{ __('Editar') }}</span>
                        <span class="sj-toggle-track__text sj-toggle-track__text--active">{{ __('Guardar') }}</span>
                        <span class="sj-toggle-knob"></span>
                    </span>
                </label>
            @endcan
        </div>
    </div>

    @if ($errors->has('photo'))
        <div class="mb-3 text-sm text-red-600">{{ $errors->first('photo') }}</div>
    @endif

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4" id="vest-photo-grid" data-photo-grid>
        @foreach ($photoDescriptions as $description => $label)
            @php
                $photo = $photosByDescription->get($description);
                $photoUrl = $photo?->file ? Storage::disk($photo->file->disk)->url($photo->file->path) : null;
            @endphp
            <div
                class="relative rounded-lg border {{ $photoCardPadding }} weapon-photo-card"
                data-photo-type="vest"
                data-photo-id="{{ $photo?->id }}"
                data-photo-description="{{ $description }}"
                data-photo-label="{{ $label }}"
                data-photo-src="{{ $photoUrl ?? '' }}"
                data-photo-empty="{{ $photo ? '0' : '1' }}"
                @can('updatePhotos', $vest)
                    data-photo-editable="1"
                    data-drop-zone
                    tabindex="0"
                @endcan
                title="{{ __('Haz clic para tomar foto o elegir de galería; también arrastra o pega') }}"
            >
                <div class="weapon-photo-surface-wrap relative">
                    @can('updatePhotos', $vest)
                        <div class="sj-paste-proxy" data-paste-proxy contenteditable="true" spellcheck="false"></div>
                    @endcan
                    <div data-photo-surface-host>
                        @if ($photoUrl)
                            <img src="{{ $photoUrl }}" alt="{{ $label }}" class="{{ $photoSurfaceClass }} w-full rounded object-contain bg-gray-50" data-drop-surface>
                        @else
                            <div class="flex {{ $photoSurfaceClass }} w-full items-center justify-center rounded border border-dashed border-gray-300 bg-gray-50 text-center text-sm text-gray-400 transition" data-drop-surface>
                                <div>
                                    <div class="font-medium">{{ __('Foto pendiente') }}</div>
                                    <div class="mt-1 text-xs text-gray-400">{{ $label }}</div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="mt-2 flex items-center justify-between {{ $photoMetaClass }}">
                    <div class="min-w-0 text-gray-600">
                        <div class="flex flex-col gap-0.5">
                            <span class="truncate">{{ $label }}</span>
                            <span class="text-xs text-gray-500" data-photo-date>{{ $photo?->created_at?->format('Y-m-d') ?? __('Pendiente') }}</span>
                        </div>
                    </div>

                    @can('updatePhotos', $vest)
                        <div data-photo-actions class="hidden shrink-0">
                            @if ($photo)
                                <button
                                    type="button"
                                    class="cursor-pointer px-1 py-0.5 text-red-600 hover:text-red-900 hover:underline"
                                    data-photo-delete
                                    data-destroy-url="{{ route('vests.photos.destroy', [$vest, $photo]) }}"
                                >
                                    {{ __('Eliminar') }}
                                </button>
                            @endif
                        </div>
                    @endcan
                </div>
            </div>
        @endforeach
    </div>

    @can('updatePhotos', $vest)
        @include('partials.photo-slot-editor-assets')
    @endcan
</div>
