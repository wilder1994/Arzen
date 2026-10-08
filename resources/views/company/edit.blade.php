<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <h2 class="sj-section-header__title">{{ __('Datos de la empresa') }}</h2>
                <p class="sj-section-header__subtitle">{{ __('Esta información aparece en las cartas de revalidación, los formatos y el logo del sistema.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-8">
        <div class="sj-page-shell space-y-6">
            @if (session('status'))
                <div class="rounded bg-green-50 p-3 text-sm text-green-700">{{ session('status') }}</div>
            @endif

            @if ($missingLetterFields !== [])
                <div class="rounded bg-amber-50 p-3 text-sm text-amber-800" role="status">
                    {{ __('Para generar cartas de revalidación faltan: :fields.', ['fields' => implode(', ', $missingLetterFields)]) }}
                </div>
            @endif

            @if ($errors->any())
                <div class="rounded bg-red-50 p-3 text-sm text-red-700" role="alert">
                    <ul class="list-disc pl-5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form method="POST" action="{{ route('company.update') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                <section class="sj-ui-card">
                    <div class="sj-ui-card__body space-y-4 p-6">
                        <h3 class="sj-company__section-title">{{ __('Empresa') }}</h3>
                        <div class="sj-company__grid">
                            @foreach ([
                                'legal_name' => __('Razón social'),
                                'nit' => __('NIT'),
                                'address' => __('Dirección'),
                                'city' => __('Ciudad'),
                                'phone' => __('Teléfono'),
                                'email' => __('Correo'),
                                'website' => __('Sitio web'),
                            ] as $field => $label)
                                <div class="sj-ui-field">
                                    <label for="company-{{ $field }}" class="sj-ui-field__label">{{ $label }}</label>
                                    <input id="company-{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" value="{{ old($field, $company->{$field}) }}" class="sj-ui-field__control">
                                </div>
                            @endforeach
                        </div>
                        <p class="sj-company__hint">{{ __('La ciudad se usa en la fecha de las cartas (por ejemplo: "Santiago de Cali, 7 de octubre de 2026").') }}</p>
                    </div>
                </section>

                <section class="sj-ui-card">
                    <div class="sj-ui-card__body space-y-4 p-6">
                        <h3 class="sj-company__section-title">{{ __('Representante legal') }}</h3>
                        <div class="sj-company__grid">
                            <div class="sj-ui-field">
                                <label for="company-legal_rep_name" class="sj-ui-field__label">{{ __('Nombre completo') }}</label>
                                <input id="company-legal_rep_name" name="legal_rep_name" type="text" value="{{ old('legal_rep_name', $company->legal_rep_name) }}" class="sj-ui-field__control">
                            </div>
                            <div class="sj-ui-field">
                                <label for="company-legal_rep_document" class="sj-ui-field__label">{{ __('Cédula') }}</label>
                                <input id="company-legal_rep_document" name="legal_rep_document" type="text" value="{{ old('legal_rep_document', $company->legal_rep_document) }}" class="sj-ui-field__control">
                            </div>
                            <div class="sj-ui-field">
                                <label for="company-legal_rep_document_city" class="sj-ui-field__label">{{ __('Ciudad de expedición') }}</label>
                                <input id="company-legal_rep_document_city" name="legal_rep_document_city" type="text" value="{{ old('legal_rep_document_city', $company->legal_rep_document_city) }}" class="sj-ui-field__control">
                            </div>
                        </div>

                        <h3 class="sj-company__section-title pt-2">{{ __('Persona autorizada para trámites') }}</h3>
                        <p class="sj-company__hint">{{ __('Opcional. Si se deja vacío, la carta autoriza al mismo representante legal.') }}</p>
                        <div class="sj-company__grid">
                            <div class="sj-ui-field">
                                <label for="company-agent_name" class="sj-ui-field__label">{{ __('Nombre completo') }}</label>
                                <input id="company-agent_name" name="agent_name" type="text" value="{{ old('agent_name', $company->agent_name) }}" class="sj-ui-field__control">
                            </div>
                            <div class="sj-ui-field">
                                <label for="company-agent_document" class="sj-ui-field__label">{{ __('Cédula') }}</label>
                                <input id="company-agent_document" name="agent_document" type="text" value="{{ old('agent_document', $company->agent_document) }}" class="sj-ui-field__control">
                            </div>
                            <div class="sj-ui-field">
                                <label for="company-agent_document_city" class="sj-ui-field__label">{{ __('Ciudad de expedición') }}</label>
                                <input id="company-agent_document_city" name="agent_document_city" type="text" value="{{ old('agent_document_city', $company->agent_document_city) }}" class="sj-ui-field__control">
                            </div>
                        </div>
                    </div>
                </section>

                <section class="sj-ui-card">
                    <div class="sj-ui-card__body space-y-4 p-6">
                        <h3 class="sj-company__section-title">{{ __('Logo y membrete') }}</h3>
                        <div class="sj-company__media">
                            <div class="space-y-3">
                                <p class="sj-ui-field__label">{{ __('Logo') }}</p>
                                <div class="sj-company__preview sj-company__preview--logo">
                                    <img src="{{ $company->logoUrl() }}" alt="{{ __('Logo') }}">
                                </div>
                                <input name="logo" type="file" accept=".png,.jpg,.jpeg,.webp" class="sj-company__file">
                                <p class="sj-company__hint">{{ __('PNG, JPG o WEBP, máximo 2 MB. Se muestra en el sidebar, el inicio de sesión y la revista mensual.') }}</p>
                                @if ($company->logo_file_id)
                                    <label class="sj-company__check"><input type="checkbox" name="remove_logo" value="1" class="sj-ui-check rounded"> {{ __('Quitar logo y volver al predeterminado') }}</label>
                                @endif
                            </div>

                            <div class="space-y-3">
                                <p class="sj-ui-field__label">{{ __('Membrete (Word)') }}</p>
                                <div class="sj-company__preview sj-company__preview--letterhead">
                                    @if ($hasLetterheadHeader)
                                        <img src="{{ route('company.letterhead.image', ['part' => 'header', 'v' => $company->letterhead_file_id]) }}" alt="{{ __('Encabezado') }}">
                                    @endif
                                    <div class="sj-company__preview-page">
                                        {{ $company->letterhead_file_id ? __('Cuerpo de la carta') : __('Sin membrete cargado') }}
                                    </div>
                                    @if ($hasLetterheadFooter)
                                        <img src="{{ route('company.letterhead.image', ['part' => 'footer', 'v' => $company->letterhead_file_id]) }}" alt="{{ __('Pie de página') }}">
                                    @endif
                                </div>
                                @if ($company->letterheadFile)
                                    <p class="sj-company__hint">{{ __('Archivo actual: :name', ['name' => $company->letterheadFile->original_name]) }}</p>
                                @endif
                                <input name="letterhead" type="file" accept=".docx" class="sj-company__file">
                                <p class="sj-company__hint">{{ __('Documento .docx con el membrete como imagen en el encabezado y en el pie. Se aplica a todas las páginas de las cartas de revalidación.') }}</p>
                                @if ($company->letterhead_file_id)
                                    <label class="sj-company__check"><input type="checkbox" name="remove_letterhead" value="1" class="sj-ui-check rounded"> {{ __('Quitar membrete') }}</label>
                                @endif
                            </div>
                        </div>
                    </div>
                </section>

                <section class="sj-ui-card">
                    <div class="sj-ui-card__body space-y-4 p-6">
                        <h3 class="sj-company__section-title">{{ __('Códigos internos de armas') }}</h3>
                        <div class="sj-company__grid">
                            <div class="sj-ui-field">
                                <label for="company-internal_code_prefix" class="sj-ui-field__label">{{ __('Prefijo') }}</label>
                                <input id="company-internal_code_prefix" name="internal_code_prefix" type="text" maxlength="10" value="{{ old('internal_code_prefix', $company->internal_code_prefix) }}" class="sj-ui-field__control">
                            </div>
                        </div>
                        <p class="sj-company__hint">{{ __('Las armas nuevas reciben el prefijo seguido de un consecutivo, por ejemplo :example. Cambiarlo no modifica los códigos ya asignados.', ['example' => ($company->internal_code_prefix ?: '').'0001']) }}</p>
                    </div>
                </section>

                <div class="flex justify-end">
                    <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar cambios') }}</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
