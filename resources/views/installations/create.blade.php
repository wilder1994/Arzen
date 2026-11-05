<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Nueva instalación') }}</h2>
            </div>
            <div class="sj-section-header__actions">
                <a href="{{ route('installations.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Volver al listado') }}</a>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="sj-page-shell sj-page-shell--wide">
            <form
                method="POST"
                action="{{ route('installations.store') }}"
                x-data="{
                    clientId: @js((int) old('client_id', $client?->id ?? 0)),
                    query: @js(old('client_query', $client?->name ?? '')),
                    open: false,
                    sameAsClient: @js(filter_var(old('same_as_client', false), FILTER_VALIDATE_BOOLEAN)),
                    clients: @js($clients->map(fn ($item) => [
                        'id' => $item->id,
                        'name' => $item->name,
                        'place' => collect([$item->address, $item->city, $item->department])->filter()->implode(' · '),
                    ])->values()),
                    get matches() {
                        const q = this.query.trim().toLowerCase();
                        const rows = q === '' ? this.clients : this.clients.filter((client) => client.name.toLowerCase().includes(q));
                        return rows.slice(0, 8);
                    },
                    get selected() {
                        return this.clients.find((client) => client.id === Number(this.clientId)) || null;
                    },
                    pick(client) {
                        this.clientId = client.id;
                        this.query = client.name;
                        this.open = false;
                    }
                }"
            >
                @csrf
                <section class="sj-ui-card">
                    <div class="sj-ui-card__body p-6">
                        <div class="sj-company__grid">
                            <div class="sj-ui-field">
                                <label for="installation-client" class="sj-ui-field__label">{{ __('Cliente') }}</label>
                                <input type="hidden" name="client_id" :value="clientId || ''">
                                <input
                                    id="installation-client"
                                    name="client_query"
                                    type="search"
                                    class="sj-ui-field__control"
                                    placeholder="{{ __('Buscar cliente') }}"
                                    x-model="query"
                                    @focus="open = true"
                                    @input="open = true; clientId = 0"
                                    autocomplete="off"
                                    required
                                >
                                <div class="mt-1 rounded border border-gray-200 bg-white" x-show="open && matches.length" x-cloak @click.outside="open = false">
                                    <template x-for="client in matches" :key="client.id">
                                        <button type="button" class="block w-full px-3 py-2 text-left text-sm hover:bg-gray-50" @click="pick(client)" x-text="client.name"></button>
                                    </template>
                                </div>
                                <x-input-error :messages="$errors->get('client_id')" />
                            </div>
                            <div class="sj-ui-field" x-show="clientId" x-cloak>
                                <label for="installation-name" class="sj-ui-field__label">{{ __('Nombre de la instalación') }}</label>
                                <input id="installation-name" name="name" type="text" maxlength="120" value="{{ old('name') }}" class="sj-ui-field__control" :required="clientId > 0">
                                <x-input-error :messages="$errors->get('name')" />
                            </div>
                        </div>

                        <div x-show="clientId" x-cloak>
                            <hr class="sj-ui-divider">
                            <label class="sj-company__check mb-3">
                                <input type="hidden" name="same_as_client" value="0">
                                <input type="checkbox" name="same_as_client" value="1" class="sj-ui-check rounded" x-model="sameAsClient">
                                {{ __('Usa la misma ubicación del cliente') }}
                            </label>
                            <p x-show="sameAsClient" class="sj-company__hint" x-text="selected && selected.place ? selected.place : @js(__('El cliente aún no tiene ubicación registrada.'))"></p>
                            <div x-show="!sameAsClient">
                                <x-location-fields :required="false" />
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-2">
                            <a href="{{ route('installations.index') }}" class="sj-ui-btn sj-ui-btn--ghost">{{ __('Cancelar') }}</a>
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar') }}</button>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
</x-app-layout>
