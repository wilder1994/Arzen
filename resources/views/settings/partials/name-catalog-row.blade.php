@php
    $formId = $formPrefix.'-'.$item->id;
    $usageCount = (int) ($item->workers_count ?? $item->clients_count ?? $item->weapons_count ?? 0);
@endphp
<div class="sj-position-row">
    <form id="{{ $formId }}" method="POST" action="{{ route($updateRoute, $item) }}" class="contents">
        @csrf
        @method('PUT')
        <input name="name" type="text" required maxlength="255" value="{{ $item->name }}" class="sj-ui-field__control" aria-label="{{ $label }}">
    </form>
    <div class="sj-catalog__order">
        <form method="POST" action="{{ route($moveRoute, $item) }}">
            @csrf
            <input type="hidden" name="direction" value="up">
            <button type="submit" class="sj-catalog__arrow" aria-label="{{ __('Subir') }}">↑</button>
        </form>
        <form method="POST" action="{{ route($moveRoute, $item) }}">
            @csrf
            <input type="hidden" name="direction" value="down">
            <button type="submit" class="sj-catalog__arrow" aria-label="{{ __('Bajar') }}">↓</button>
        </form>
    </div>
    <label class="sj-catalog__state">
        <input type="hidden" name="is_active" value="0" form="{{ $formId }}">
        <input type="checkbox" name="is_active" value="1" form="{{ $formId }}" class="sj-ui-check rounded" @checked($item->is_active)>
        {{ __('activo') }}
    </label>
    <div class="sj-catalog__actions">
        <button type="submit" form="{{ $formId }}" class="sj-ui-btn sj-ui-btn--success sj-ui-btn--sm">{{ __('Guardar') }}</button>
        <form method="POST" action="{{ route($destroyRoute, $item) }}" onsubmit="return confirm(@js($deleteConfirm))">
            @csrf
            @method('DELETE')
            <button type="submit" class="sj-ui-btn sj-ui-btn--danger sj-ui-btn--sm" @disabled($usageCount > 0) title="{{ $usageCount > 0 ? __('En uso: desactívelo.') : __('Eliminar') }}">{{ __('Eliminar') }}</button>
        </form>
    </div>
</div>
