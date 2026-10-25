@props(['type' => 'warning'])

@php
    $role = $type === 'danger' ? 'alert' : 'status';
    $variant = match ($type) {
        'success' => 'sj-ui-alert--success',
        'danger' => 'sj-ui-alert--danger',
        default => 'sj-ui-alert--warning',
    };
@endphp

<div {{ $attributes->merge(['class' => 'sj-ui-alert '.$variant, 'role' => $role]) }}>
    <svg class="sj-ui-alert__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
        @if ($type === 'success')
            <path fill-rule="evenodd" d="M10 18a8 8 0 1 0 0-16 8 8 0 0 0 0 16Zm3.857-9.809a.75.75 0 0 0-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 1 0-1.06 1.061l2.5 2.5a.75.75 0 0 0 1.137-.089l4-5.5Z" clip-rule="evenodd" />
        @else
            <path fill-rule="evenodd" d="M8.485 2.495c.673-1.167 2.357-1.167 3.03 0l6.28 10.875c.673 1.167-.17 2.625-1.516 2.625H3.72c-1.347 0-2.189-1.458-1.515-2.625L8.485 2.495ZM10 5a.75.75 0 0 1 .75.75v3.5a.75.75 0 0 1-1.5 0v-3.5A.75.75 0 0 1 10 5Zm0 9a1 1 0 1 0 0-2 1 1 0 0 0 0 2Z" clip-rule="evenodd" />
        @endif
    </svg>
    <div class="min-w-0">{{ $slot }}</div>
</div>
