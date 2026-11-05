@php
    $partyLabels = \App\Models\Client::partyTypeLabels();
    $documentLabels = \App\Models\Client::documentTypeLabels();
    $address = collect([$client->address, $client->neighborhood])->filter()->implode(' / ');
    $hasPin = \App\Support\MapCoordinates::isFilled($client->latitude, $client->longitude);
    $blank = '—';
@endphp

<article class="sj-client-ficha">
    <section class="sj-client-ficha__section">
        <h4 class="sj-client-ficha__title">{{ __('Identificación') }}</h4>
        <dl class="sj-client-ficha__grid">
            <div>
                <dt>{{ __('Nombre comercial') }}</dt>
                <dd>{{ $client->name ?: $blank }}</dd>
            </div>
            <div>
                <dt>{{ __('Razón social') }}</dt>
                <dd>{{ $client->legal_name ?: $client->name ?: $blank }}</dd>
            </div>
            <div>
                <dt>{{ __('Tipo de cliente') }}</dt>
                <dd>{{ $partyLabels[$client->party_type] ?? $blank }}</dd>
            </div>
            <div>
                <dt>{{ __('Tipo de estructura') }}</dt>
                <dd>{{ $client->structureType?->name ?: $blank }}</dd>
            </div>
            <div>
                <dt>{{ __('Documento') }}</dt>
                <dd>{{ trim(($documentLabels[$client->document_type] ?? $client->document_type).' '.($client->nit ?: '')) ?: $blank }}</dd>
            </div>
        </dl>
    </section>

    <section class="sj-client-ficha__section">
        <h4 class="sj-client-ficha__title">{{ __('Contacto') }}</h4>
        <dl class="sj-client-ficha__grid">
            <div>
                <dt>{{ __('Contacto') }}</dt>
                <dd>{{ $client->contact_name ?: $blank }}</dd>
            </div>
            <div>
                <dt>{{ __('Correo') }}</dt>
                <dd>{{ $client->email ?: $blank }}</dd>
            </div>
            <div>
                <dt>{{ __('Teléfono') }}</dt>
                <dd>{{ $client->phone ?: $blank }}</dd>
            </div>
            <div>
                <dt>{{ __('Representante legal') }}</dt>
                <dd>{{ $client->legal_representative ?: $blank }}</dd>
            </div>
            <div class="sj-client-ficha__item--wide">
                <dt>{{ __('Correo del representante') }}</dt>
                <dd>{{ $client->representative_email ?: $blank }}</dd>
            </div>
        </dl>
    </section>

    <section class="sj-client-ficha__section">
        <h4 class="sj-client-ficha__title">{{ __('Sede') }}</h4>
        <dl class="sj-client-ficha__grid">
            <div class="sj-client-ficha__item--wide">
                <dt>{{ __('Dirección') }}</dt>
                <dd>{{ $address ?: $blank }}</dd>
            </div>
            <div>
                <dt>{{ __('Ciudad') }}</dt>
                <dd>{{ $client->city ?: $blank }}</dd>
            </div>
            <div>
                <dt>{{ __('Departamento') }}</dt>
                <dd>{{ $client->department ?: $blank }}</dd>
            </div>
            @if ($hasPin)
                <iframe
                    class="sj-client-ficha__map"
                    title="{{ __('Ubicación') }}"
                    src="https://maps.google.com/maps?q={{ $client->latitude }},{{ $client->longitude }}&z=16&output=embed"
                ></iframe>
            @else
                <div class="sj-client-ficha__item--wide">
                    <dt>{{ __('Ubicación') }}</dt>
                    <dd>{{ __('Ubicación pendiente') }}</dd>
                </div>
            @endif
        </dl>
    </section>
</article>
