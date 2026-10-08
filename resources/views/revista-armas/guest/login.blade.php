<x-revista-guest-layout :title="__('Ingreso')">
    <div class="mx-auto max-w-md rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h1 class="sj-type-page text-slate-900">{{ __('Ingreso temporal') }}</h1>
        <p class="mt-1 text-sm text-slate-600">{{ __('Use el correo y el código que le envió su responsable.') }}</p>

        <form method="POST" action="{{ route('revista-armas.guest.login.store') }}" class="mt-6 space-y-4">
            @csrf
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700">{{ __('Correo') }}</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                    class="sj-ui-field__control mt-1 w-full">
            </div>
            <div>
                <label for="access_code" class="block text-sm font-medium text-slate-700">{{ __('Código temporal') }}</label>
                <input id="access_code" name="access_code" type="text" required autocomplete="one-time-code"
                    class="sj-ui-field__control mt-1 w-full uppercase" placeholder="XXXXXXXX">
            </div>
            <button type="submit" class="sj-ui-btn sj-ui-btn--primary w-full">
                {{ __('Entrar') }}
            </button>
        </form>
    </div>
</x-revista-guest-layout>
