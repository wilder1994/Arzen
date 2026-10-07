<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    @include('partials.theme-boot')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('Revista armas') }} — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-slate-100 text-slate-900 antialiased">
    <header class="border-b border-slate-200 bg-white shadow-sm">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3">
            <div>
                <div class="text-sm font-bold text-slate-900">{{ __('Revista armas') }}</div>
                <div class="text-xs text-slate-500">{{ __('Acceso temporal para fotografías') }}</div>
            </div>
            <div class="flex items-center gap-2">
                @include('partials.theme-toggle', ['themeToggleClass' => 'sj-theme-toggle sj-theme-toggle--surface'])
            @if (session('revista_grant_id'))
                <form method="POST" action="{{ route('revista-armas.guest.logout') }}">
                    @csrf
                    <button type="submit" class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        {{ __('Salir') }}
                    </button>
                </form>
            @endif
            </div>
        </div>
    </header>
    <main class="mx-auto max-w-6xl px-4 py-6">
        @if (session('status'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800">{{ session('status') }}</div>
        @endif
        @if ($errors->any())
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        {{ $slot ?? '' }}
    </main>
    @include('partials.developer-mark')
    @stack('scripts')
</body>
</html>
