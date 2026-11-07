@php
    $selected = old($field, $selected ?? '');
    $names = $options->pluck('name');
@endphp
<div>
    <x-input-label :for="$field" :value="$label" />
    <div
        class="relative mt-1"
        data-assignment-combobox
        data-empty-message="{{ $empty }}"
    >
        <select id="{{ $field }}" name="{{ $field }}" class="hidden" required data-combobox-select>
            <option value="">{{ __('Seleccione') }}</option>
            @foreach ($options as $option)
                <option value="{{ $option->name }}" data-label="{{ $option->name }}" @selected($selected === $option->name)>{{ $option->name }}</option>
            @endforeach
            @if ($selected !== '' && ! $names->contains($selected))
                <option value="{{ $selected }}" data-label="{{ $selected }}" selected>{{ $selected }}</option>
            @endif
        </select>
        <input
            type="text"
            data-combobox-search
            class="sj-ui-field__control block w-full pr-10"
            placeholder="{{ $placeholder }}"
            autocomplete="off"
            spellcheck="false"
            role="combobox"
            aria-expanded="false"
            aria-controls="{{ $field }}-options"
        >
        <button type="button" data-combobox-toggle class="absolute inset-y-0 right-0 flex items-center px-3 text-gray-500" aria-label="{{ __('Mostrar opciones') }}">
            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.51a.75.75 0 01-1.08 0l-4.25-4.51a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
            </svg>
        </button>
        <div id="{{ $field }}-options" data-combobox-panel class="absolute left-0 right-0 z-20 mt-2 hidden max-h-60 overflow-y-auto rounded-md border border-slate-200 bg-white py-1 shadow-xl" role="listbox"></div>
    </div>
    <x-input-error :messages="$errors->get($field)" class="mt-2" />
</div>
