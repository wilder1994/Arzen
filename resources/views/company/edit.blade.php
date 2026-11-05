<x-app-layout>
    <x-slot name="header">
        <div class="sj-section-header">
            <div class="sj-section-header__main">
                <div class="sj-section-header__title-row">
                    <h2 class="sj-section-header__title">{{ __('Datos de la empresa') }}</h2>
                    <x-help-drawer :title="__('Datos de la empresa')">
                        @include('company.partials.help')
                    </x-help-drawer>
                </div>
                <p class="sj-section-header__subtitle">{{ __('Esta información aparece en las cartas de revalidación, los formatos y el logo del sistema.') }}</p>
            </div>
        </div>
    </x-slot>

    <div class="py-4">
        <div class="sj-page-shell space-y-3">
            @if (session('status'))
                <x-ui-alert type="success">{{ session('status') }}</x-ui-alert>
            @endif

            @if ($missingLetterFields !== [])
                <x-ui-alert type="warning">
                    {{ __('Para generar cartas de revalidación faltan: :fields.', ['fields' => implode(', ', $missingLetterFields)]) }}
                </x-ui-alert>
            @endif

            @if ($errors->any())
                <x-ui-alert type="danger">
                    <ul class="list-disc pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </x-ui-alert>
            @endif

            <form method="POST" action="{{ route('company.update') }}" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                @if ($returnInstallation)
                    <input type="hidden" name="return_installation" value="{{ $returnInstallation->id }}">
                @endif

                <section class="sj-ui-card">
                    <div class="sj-ui-card__body p-6">
                        <h3 class="sj-form-section__title">{{ __('Empresa') }}</h3>
                        <div class="sj-company__grid">
                            @foreach ([
                                'legal_name' => __('Razón social'),
                                'nit' => __('NIT'),
                                'phone' => __('Teléfono'),
                                'email' => __('Correo'),
                                'website' => __('Sitio web'),
                            ] as $field => $label)
                                <div class="sj-ui-field">
                                    <label for="company-{{ $field }}" class="sj-ui-field__label">{{ $label }}</label>
                                    <input id="company-{{ $field }}" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" value="{{ old($field, $company->{$field}) }}" class="sj-ui-field__control">
                                </div>
                            @endforeach
                            <div class="sj-ui-field">
                                <div class="sj-ui-field__label-row">
                                    <label for="company-internal_code_prefix" class="sj-ui-field__label">{{ __('Prefijo de códigos internos') }}</label>
                                    <x-help-tip :text="__('Las armas nuevas reciben el prefijo seguido de un consecutivo, por ejemplo :example. Cambiarlo no modifica los códigos ya asignados.', ['example' => ($company->internal_code_prefix ?: '').'0001'])" />
                                </div>
                                <input id="company-internal_code_prefix" name="internal_code_prefix" type="text" maxlength="10" value="{{ old('internal_code_prefix', $company->internal_code_prefix) }}" class="sj-ui-field__control">
                            </div>
                        </div>

                        <hr class="sj-ui-divider">

                        <div class="sj-form-section__title sj-ui-field__label-row">
                            <h3>{{ __('Ubicación') }}</h3>
                            <x-help-tip :text="__('Sede principal de la empresa. Es la ubicación de su armerillo e instalación principal en el mapa.')" />
                        </div>
                        <x-location-fields :model="$company" :required="false" letter-city />

                        <hr class="sj-ui-divider">

                        <h3 class="sj-form-section__title">{{ __('Representante legal') }}</h3>
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

                        <hr class="sj-ui-divider">

                        <div class="sj-form-section__title sj-ui-field__label-row">
                            <h3>{{ __('Persona autorizada para trámites') }} <span class="sj-form-section__optional">{{ __('(opcional)') }}</span></h3>
                            <x-help-tip :text="__('Si se deja vacío, la carta autoriza al representante legal. Si escribe un nombre, la cédula y la ciudad de expedición son obligatorias.')" />
                        </div>
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

                        <hr class="sj-ui-divider">

                        <h3 class="sj-form-section__title">{{ __('Logo y membrete') }}</h3>
                        <div class="sj-company__media">
                            <div class="sj-ui-field">
                                <div class="sj-ui-field__label-row">
                                    <span class="sj-ui-field__label">{{ __('Logo') }}</span>
                                    <x-help-tip :text="__('PNG, JPG o WEBP de hasta 2 MB. Se muestra en el menú lateral, el inicio de sesión y la revista mensual.')" />
                                </div>
                                <x-file-drop
                                    name="logo"
                                    accept=".png,.jpg,.jpeg,.webp"
                                    :label="__('Logo')"
                                    :image-url="$company->logoUrl()"
                                    :invalid-message="__('El logo debe ser una imagen PNG, JPG o WEBP.')"
                                >
                                    <x-slot name="meta">
                                        <span class="sj-file-drop__meta-name">{{ $company->logo_file_id ? __('Logo de la empresa') : __('Logo predeterminado de Arzen') }}</span>
                                        @if ($company->logo_file_id)
                                            <label class="sj-company__check"><input type="checkbox" name="remove_logo" value="1" class="sj-ui-check rounded" data-file-drop-remove> {{ __('Quitar logo') }}</label>
                                        @endif
                                    </x-slot>
                                </x-file-drop>
                            </div>

                            <div class="sj-ui-field">
                                <div class="sj-ui-field__label-row">
                                    <span class="sj-ui-field__label">{{ __('Membrete (Word)') }}</span>
                                    <x-help-tip :text="__('Archivo .docx de hasta 10 MB con el membrete como imagen en el encabezado y, si aplica, en el pie. Se aplica a todas las páginas de las cartas.')" />
                                </div>
                                <x-file-drop
                                    name="letterhead"
                                    accept=".docx"
                                    kind="document"
                                    :label="__('Membrete (Word)')"
                                    :invalid-message="__('El membrete debe ser un documento de Word (.docx).')"
                                >
                                    @if ($company->letterhead_file_id)
                                        <div class="sj-company__letterhead-page">
                                            @if ($hasLetterheadHeader)
                                                <img src="{{ route('company.letterhead.image', ['part' => 'header', 'v' => $company->letterhead_file_id]) }}" alt="{{ __('Encabezado') }}">
                                            @endif
                                            <div class="sj-company__letterhead-body">{{ __('Cuerpo de la carta') }}</div>
                                            @if ($hasLetterheadFooter)
                                                <img src="{{ route('company.letterhead.image', ['part' => 'footer', 'v' => $company->letterhead_file_id]) }}" alt="{{ __('Pie de página') }}">
                                            @endif
                                        </div>
                                    @else
                                        <span class="sj-file-drop__empty">{{ __('Sin membrete cargado') }}</span>
                                    @endif

                                    <x-slot name="meta">
                                        <span class="sj-file-drop__meta-name">{{ $company->letterheadFile?->original_name ?? __('Ningún archivo') }}</span>
                                        @if ($company->letterhead_file_id)
                                            <label class="sj-company__check"><input type="checkbox" name="remove_letterhead" value="1" class="sj-ui-check rounded" data-file-drop-remove> {{ __('Quitar membrete') }}</label>
                                        @endif
                                    </x-slot>
                                </x-file-drop>
                            </div>
                        </div>

                        @if ($returnInstallation)
                            <div class="sj-ui-field mt-4">
                                <label for="company-change-note" class="sj-ui-field__label">{{ __('Nota del cambio') }}</label>
                                <textarea id="company-change-note" name="change_note" rows="2" maxlength="2000" class="sj-ui-field__control" placeholder="{{ __('Opcional. Queda en el historial de la instalación.') }}">{{ old('change_note') }}</textarea>
                            </div>
                        @endif

                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="sj-ui-btn sj-ui-btn--primary">{{ __('Guardar cambios') }}</button>
                        </div>
                    </div>
                </section>
            </form>
        </div>
    </div>
</x-app-layout>
