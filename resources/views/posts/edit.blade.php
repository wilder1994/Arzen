<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <div class="sj-section-header__title-row">
                    <h2 class="sj-section-header__title">{{ __('Editar puesto') }}</h2>
                    <x-help-drawer :title="__('Puestos')">
                        @include('posts.partials.help')
                    </x-help-drawer>
                </div>
                <p class="sj-section-header__subtitle">{{ $post->name }} · {{ $post->installation?->name }}</p>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('posts.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al listado') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="sj-page-shell sj-page-shell--wide">
            <form method="POST" action="{{ route('posts.update', $post) }}">
                @csrf
                @method('PUT')
                <section class="sj-ui-card">
                    <div class="sj-ui-card__body p-6">
                        @include('posts.partials.form', ['post' => $post])

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('posts.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar cambios') }}</button>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
</x-app-layout>
