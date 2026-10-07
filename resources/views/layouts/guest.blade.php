<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        @include('partials.theme-boot')
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="icon" href="{{ asset('favicon.ico') }}?v={{ time() }}" type="image/x-icon">
        <link rel="shortcut icon" href="{{ asset('favicon.ico') }}?v={{ time() }}" type="image/x-icon">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="sj-app min-h-screen font-sans text-gray-900 antialiased">
        <div class="sj-shell flex min-h-screen flex-col">
            <div class="flex flex-1 flex-col items-center pt-10 sm:pt-16">
            <div class="w-full max-w-5xl px-4">
                <div class="mb-4 flex items-center justify-end gap-2">
                @include('partials.theme-toggle', ['themeToggleClass' => 'sj-theme-toggle sj-theme-toggle--surface'])
                <form method="POST" action="{{ route('locale.switch') }}" class="flex justify-end">
                    @csrf
                    <label for="guest-locale-select" class="sr-only">{{ __('Idioma') }}</label>
                    <select id="guest-locale-select" name="locale" class="sj-ui-field__control text-sm" onchange="this.form.submit()">
                        <option value="es" @selected(app()->getLocale() === 'es')>{{ __('Español') }}</option>
                        <option value="en" @selected(app()->getLocale() === 'en')>{{ __('Inglés') }}</option>
                    </select>
                </form>
                </div>
            </div>
            <div class="text-center">
                <a href="/" class="inline-flex">
                    <x-application-logo class="mx-auto h-24 w-auto max-w-[18rem] rounded-xl" />
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 sj-ui-card overflow-hidden">
                {{ $slot }}
            </div>
            </div>
            @include('partials.developer-mark')
        </div>
    </body>
</html>




