@once
    <style>
        .sj-developer-mark {
            margin: 0;
            padding: 0.7rem 1rem;
            text-align: center;
            font-size: var(--sj-fs-xs);
            line-height: 1.4;
            letter-spacing: 0.01em;
            color: #64748b;
            background: rgba(255, 255, 255, 0.86);
            border-top: 1px solid rgba(148, 163, 184, 0.35);
        }

        .sj-developer-mark--overlay {
            position: absolute;
            left: 0;
            right: 0;
            bottom: 0;
            z-index: 3;
            color: rgba(226, 232, 240, 0.92);
            background: rgba(2, 6, 23, 0.55);
            border-top: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(6px);
        }
    </style>
@endonce
<footer class="sj-developer-mark {{ !empty($overlay) ? 'sj-developer-mark--overlay' : '' }}">
    {{ config('app.name', 'Arzen') }} &copy; {{ date('Y') }} &middot; WCodex &middot; Innovative Software Solutions
</footer>
