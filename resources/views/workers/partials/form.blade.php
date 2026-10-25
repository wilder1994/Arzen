@php
    $worker = $worker ?? null;
    $selectedClient = (int) old('client_id', $worker?->client_id);
    $selectedPost = (int) old('post_id', $worker?->post_id);
    $postOptions = $posts->map(fn ($post) => [
        'id' => $post->id,
        'client_id' => $post->client_id,
        'label' => $post->name.($post->installation ? ' — '.$post->installation->name : ''),
    ])->values();
@endphp

<div x-data="{ clientId: @js($selectedClient ?: ''), postId: @js($selectedPost ?: ''), posts: @js($postOptions), get clientPosts() { return this.posts.filter((post) => String(post.client_id) === String(this.clientId)); } }">
    <h3 class="sj-form-section__title">{{ __('Identificación') }}</h3>
    <div class="sj-company__grid">
        <div class="sj-ui-field">
            <label for="worker-name" class="sj-ui-field__label">{{ __('Nombre completo') }}</label>
            <input id="worker-name" name="name" type="text" maxlength="255" value="{{ old('name', $worker?->name) }}" class="sj-ui-field__control" required>
            <x-input-error :messages="$errors->get('name')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-document_type" class="sj-ui-field__label">{{ __('Tipo documento') }}</label>
            <select id="worker-document_type" name="document_type" class="sj-ui-field__control" required>
                @foreach (\App\Models\Client::documentTypeLabels() as $value => $label)
                    @continue($value === 'NIT')
                    <option value="{{ $value }}" @selected(old('document_type', $worker?->document_type ?? 'CC') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('document_type')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-document" class="sj-ui-field__label">{{ __('Número documento') }}</label>
            <input id="worker-document" name="document" type="text" maxlength="50" value="{{ old('document', $worker?->document) }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('document')" />
        </div>
        <div class="sj-ui-field">
            <div class="sj-ui-field__label-row">
                <label for="worker-position" class="sj-ui-field__label">{{ __('Cargo') }}</label>
                <x-help-tip :text="__('Los cargos se crean en Ajustes → Cargos (escolta, guarda, mensajero, recaudador...).')" />
            </div>
            <select id="worker-position" name="position_id" class="sj-ui-field__control">
                <option value="">{{ __('Sin cargo') }}</option>
                @foreach ($positions as $position)
                    <option value="{{ $position->id }}" @selected((int) old('position_id', $worker?->position_id) === $position->id)>{{ $position->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('position_id')" />
        </div>
    </div>

    <hr class="sj-ui-divider">

    <h3 class="sj-form-section__title">{{ __('Asignación') }}</h3>
    <div class="sj-company__grid">
        <div class="sj-ui-field">
            <label for="worker-client" class="sj-ui-field__label">{{ __('Cliente') }}</label>
            <select id="worker-client" name="client_id" class="sj-ui-field__control" x-model="clientId" @change="postId = ''" required>
                <option value="">{{ __('Seleccione') }}</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">{{ $client->name }}{{ $client->is_own_company ? ' · '.__('Mi empresa') : '' }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('client_id')" />
        </div>
        <div class="sj-ui-field">
            <div class="sj-ui-field__label-row">
                <label for="worker-post" class="sj-ui-field__label">{{ __('Puesto') }}</label>
                <x-help-tip :text="__('Opcional. El trabajador queda en un solo puesto a la vez; cada cambio queda en su historial.')" />
            </div>
            <select id="worker-post" name="post_id" class="sj-ui-field__control" x-model="postId">
                <option value="">{{ __('Sin puesto') }}</option>
                <template x-for="post in clientPosts" :key="post.id">
                    <option :value="post.id" x-text="post.label" :selected="String(post.id) === String(postId)"></option>
                </template>
            </select>
            <x-input-error :messages="$errors->get('post_id')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-responsible" class="sj-ui-field__label">{{ __('Responsable') }}</label>
            @if (! empty($lockResponsible))
                <input type="hidden" name="responsible_user_id" value="{{ auth()->id() }}">
                <input id="worker-responsible" type="text" value="{{ auth()->user()->name }}" class="sj-ui-field__control" readonly>
            @else
                <select id="worker-responsible" name="responsible_user_id" class="sj-ui-field__control">
                    <option value="">{{ __('Sin responsable') }}</option>
                    @foreach ($responsibles as $responsible)
                        <option value="{{ $responsible->id }}" @selected((int) old('responsible_user_id', $worker?->responsible_user_id) === $responsible->id)>{{ $responsible->name }}</option>
                    @endforeach
                </select>
            @endif
            <x-input-error :messages="$errors->get('responsible_user_id')" />
        </div>
        <div class="sj-ui-field sj-location__address">
            <label for="worker-notes" class="sj-ui-field__label">{{ __('Notas') }}</label>
            <textarea id="worker-notes" name="notes" rows="2" class="sj-ui-field__control">{{ old('notes', $worker?->notes) }}</textarea>
            <x-input-error :messages="$errors->get('notes')" />
        </div>
    </div>

    @if ($worker)
        <hr class="sj-ui-divider">

        <div class="sj-form-section__title sj-ui-field__label-row">
            <h3>{{ __('Nota del cambio') }}</h3>
            <x-help-tip :text="__('Obligatoria. Se guarda en el historial del trabajador.')" />
        </div>
        <div class="sj-ui-field">
            <textarea id="change_note" name="change_note" rows="2" class="sj-ui-field__control" required aria-label="{{ __('Nota del cambio') }}">{{ old('change_note') }}</textarea>
            <x-input-error :messages="$errors->get('change_note')" />
        </div>
    @endif
</div>
