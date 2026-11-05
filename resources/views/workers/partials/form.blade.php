@php
    use App\Support\Workers\WorkerFieldOptions;

    $worker = $worker ?? null;
    $date = fn (string $field) => old($field, $worker?->{$field}?->format('Y-m-d'));
    $text = fn (string $field) => old($field, $worker?->{$field});
    $sex = old('sex', $worker?->sex);
    $disability = old('has_disability', $worker?->has_disability);
    $nationality = old('nationality', $worker?->nationality ?? ($worker ? '' : WorkerFieldOptions::DEFAULT_NATIONALITY));
    $choices = fn (string $field, array $options) => WorkerFieldOptions::including(old($field, $worker?->{$field}), $options);
    $selectedId = fn (string $field) => (string) old($field, $worker?->{$field});
    $catalogOptions = function ($items, $current) {
        $options = $items->values();
        if ($current && $options->doesntContain('id', $current->id)) {
            $options->push($current);
        }

        return $options;
    };
@endphp

<div data-worker-places>
    <h3 class="sj-form-section__title">{{ __('Identificación') }}</h3>
    <div class="sj-company__grid">
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
            <input id="worker-document" name="document" type="text" maxlength="50" value="{{ $text('document') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('document')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-first-names" class="sj-ui-field__label">{{ __('Nombres') }}</label>
            <input id="worker-first-names" name="first_names" type="text" maxlength="255" value="{{ old('first_names', $worker?->first_names ?? $worker?->name) }}" class="sj-ui-field__control" required>
            <x-input-error :messages="$errors->get('first_names')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-last-paternal" class="sj-ui-field__label">{{ __('Apellido paterno') }}</label>
            <input id="worker-last-paternal" name="last_name_paternal" type="text" maxlength="255" value="{{ $text('last_name_paternal') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('last_name_paternal')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-last-maternal" class="sj-ui-field__label">{{ __('Apellido materno') }}</label>
            <input id="worker-last-maternal" name="last_name_maternal" type="text" maxlength="255" value="{{ $text('last_name_maternal') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('last_name_maternal')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-sex" class="sj-ui-field__label">{{ __('Sexo') }}</label>
            <select id="worker-sex" name="sex" class="sj-ui-field__control">
                <option value="">{{ __('Sin definir') }}</option>
                @foreach (['Hombre', 'Mujer'] as $option)
                    <option value="{{ $option }}" @selected($sex === $option)>{{ $option }}</option>
                @endforeach
                @if (filled($sex) && ! in_array($sex, ['Hombre', 'Mujer'], true))
                    <option value="{{ $sex }}" selected>{{ $sex }}</option>
                @endif
            </select>
            <x-input-error :messages="$errors->get('sex')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-birth-date" class="sj-ui-field__label">{{ __('Fecha de nacimiento') }}</label>
            <input id="worker-birth-date" name="birth_date" type="date" value="{{ $date('birth_date') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('birth_date')" />
        </div>
        <div class="contents" data-place-pair>
            <div class="sj-ui-field">
                <label for="worker-birth-department" class="sj-ui-field__label">{{ __('Departamento de nacimiento') }}</label>
                <select id="worker-birth-department" name="birth_department" class="sj-ui-field__control" data-place-department data-selected="{{ $text('birth_department') }}">
                    <option value="">{{ __('Seleccione') }}</option>
                </select>
                <x-input-error :messages="$errors->get('birth_department')" />
            </div>
            <div class="sj-ui-field">
                <label for="worker-birth-city" class="sj-ui-field__label">{{ __('Ciudad de nacimiento') }}</label>
                <select id="worker-birth-city" name="birth_city" class="sj-ui-field__control" data-place-city data-selected="{{ $text('birth_city') }}">
                    <option value="">{{ __('Seleccione') }}</option>
                </select>
                <x-input-error :messages="$errors->get('birth_city')" />
            </div>
        </div>
        <div class="sj-ui-field">
            <label for="worker-nationality" class="sj-ui-field__label">{{ __('Nacionalidad') }}</label>
            <select id="worker-nationality" name="nationality" class="sj-ui-field__control">
                <option value="">{{ __('Sin definir') }}</option>
                @foreach (WorkerFieldOptions::including($nationality, WorkerFieldOptions::nationalities()) as $option)
                    <option value="{{ $option }}" @selected($nationality === $option)>{{ $option }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('nationality')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-blood" class="sj-ui-field__label">{{ __('Grupo sanguíneo') }}</label>
            <select id="worker-blood" name="blood_group" class="sj-ui-field__control">
                <option value="">{{ __('Sin definir') }}</option>
                @foreach ($choices('blood_group', WorkerFieldOptions::BLOOD_GROUPS) as $option)
                    <option value="{{ $option }}" @selected($text('blood_group') === $option)>{{ $option }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('blood_group')" />
        </div>
        <div class="contents" data-place-pair>
            <div class="sj-ui-field">
                <label for="worker-issue-department" class="sj-ui-field__label">{{ __('Departamento de expedición') }}</label>
                <select id="worker-issue-department" name="document_issue_department" class="sj-ui-field__control" data-place-department data-selected="{{ $text('document_issue_department') }}">
                    <option value="">{{ __('Seleccione') }}</option>
                </select>
                <x-input-error :messages="$errors->get('document_issue_department')" />
            </div>
            <div class="sj-ui-field">
                <label for="worker-issue-city" class="sj-ui-field__label">{{ __('Ciudad de expedición') }}</label>
                <select id="worker-issue-city" name="document_issue_city" class="sj-ui-field__control" data-place-city data-selected="{{ $text('document_issue_city') }}">
                    <option value="">{{ __('Seleccione') }}</option>
                </select>
                <x-input-error :messages="$errors->get('document_issue_city')" />
            </div>
        </div>
        <div class="sj-ui-field">
            <label for="worker-issued-at" class="sj-ui-field__label">{{ __('Fecha de expedición') }}</label>
            <input id="worker-issued-at" name="document_issued_at" type="date" value="{{ $date('document_issued_at') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('document_issued_at')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-disability" class="sj-ui-field__label">{{ __('Discapacidad') }}</label>
            <select id="worker-disability" name="has_disability" class="sj-ui-field__control">
                <option value="">{{ __('Sin definir') }}</option>
                @foreach (['Sí', 'No'] as $option)
                    <option value="{{ $option }}" @selected($disability === $option)>{{ $option }}</option>
                @endforeach
                @if (filled($disability) && ! in_array($disability, ['Sí', 'No'], true))
                    <option value="{{ $disability }}" selected>{{ $disability }}</option>
                @endif
            </select>
            <x-input-error :messages="$errors->get('has_disability')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-education" class="sj-ui-field__label">{{ __('Escolaridad') }}</label>
            <select id="worker-education" name="education" class="sj-ui-field__control">
                <option value="">{{ __('Sin definir') }}</option>
                @foreach ($choices('education', WorkerFieldOptions::EDUCATION) as $option)
                    <option value="{{ $option }}" @selected($text('education') === $option)>{{ $option }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('education')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-marital" class="sj-ui-field__label">{{ __('Estado civil') }}</label>
            <input id="worker-marital" name="marital_status" type="text" maxlength="80" value="{{ $text('marital_status') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('marital_status')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-children" class="sj-ui-field__label">{{ __('Número de hijos') }}</label>
            <input id="worker-children" name="children_count" type="number" min="0" max="30" value="{{ $text('children_count') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('children_count')" />
        </div>
    </div>

    <hr class="sj-ui-divider">

    <h3 class="sj-form-section__title">{{ __('Contacto') }}</h3>
    <div class="sj-company__grid">
        <div class="sj-ui-field">
            <label for="worker-phone" class="sj-ui-field__label">{{ __('Teléfono') }}</label>
            <input id="worker-phone" name="phone" type="text" maxlength="40" value="{{ $text('phone') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('phone')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-email" class="sj-ui-field__label">{{ __('Correo') }}</label>
            <input id="worker-email" name="email" type="email" maxlength="255" value="{{ $text('email') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('email')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-address" class="sj-ui-field__label">{{ __('Dirección') }}</label>
            <input id="worker-address" name="address" type="text" maxlength="255" value="{{ $text('address') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('address')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-residence" class="sj-ui-field__label">{{ __('Lugar de residencia') }}</label>
            <input id="worker-residence" name="residence_city" type="text" maxlength="255" value="{{ $text('residence_city') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('residence_city')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-emergency-contact" class="sj-ui-field__label">{{ __('Contacto de emergencia') }}</label>
            <input id="worker-emergency-contact" name="emergency_contact" type="text" maxlength="255" value="{{ $text('emergency_contact') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('emergency_contact')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-emergency-phone" class="sj-ui-field__label">{{ __('Teléfono de emergencia') }}</label>
            <input id="worker-emergency-phone" name="emergency_phone" type="text" maxlength="40" value="{{ $text('emergency_phone') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('emergency_phone')" />
        </div>
    </div>

    <hr class="sj-ui-divider">

    <h3 class="sj-form-section__title">{{ __('Vinculación') }}</h3>
    <div class="sj-company__grid">
        <div class="sj-ui-field">
            <div class="sj-ui-field__label-row">
                <label for="worker-position" class="sj-ui-field__label">{{ __('Cargo') }}</label>
                <x-help-tip :text="__('Los cargos se crean en Ajustes → Cargos.')" />
            </div>
            <select id="worker-position" name="position_id" class="sj-ui-field__control">
                <option value="">{{ __('Sin cargo') }}</option>
                @foreach ($catalogOptions($positions, $worker?->position) as $position)
                    <option value="{{ $position->id }}" @selected($selectedId('position_id') === (string) $position->id)>{{ $position->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('position_id')" />
        </div>
        <div class="sj-ui-field">
            <div class="sj-ui-field__label-row">
                <label for="worker-engagement" class="sj-ui-field__label">{{ __('Tipo de vinculación') }}</label>
                <x-help-tip :text="__('Se administra en Ajustes → Tipos de vinculación.')" />
            </div>
            <select id="worker-engagement" name="engagement_type_id" class="sj-ui-field__control">
                <option value="">{{ __('Sin definir') }}</option>
                @foreach ($catalogOptions($engagementTypes, $worker?->engagementType) as $type)
                    <option value="{{ $type->id }}" @selected($selectedId('engagement_type_id') === (string) $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('engagement_type_id')" />
        </div>
        <div class="sj-ui-field">
            <div class="sj-ui-field__label-row">
                <label for="worker-contract" class="sj-ui-field__label">{{ __('Tipo de contrato') }}</label>
                <x-help-tip :text="__('Se administra en Ajustes → Tipos de contrato.')" />
            </div>
            <select id="worker-contract" name="contract_type_id" class="sj-ui-field__control">
                <option value="">{{ __('Sin definir') }}</option>
                @foreach ($catalogOptions($contractTypes, $worker?->contractType) as $type)
                    <option value="{{ $type->id }}" @selected($selectedId('contract_type_id') === (string) $type->id)>{{ $type->name }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('contract_type_id')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-hired" class="sj-ui-field__label">{{ __('Fecha de ingreso') }}</label>
            <input id="worker-hired" name="hired_on" type="date" value="{{ $date('hired_on') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('hired_on')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-contract-end" class="sj-ui-field__label">{{ __('Vencimiento de contrato') }}</label>
            <input id="worker-contract-end" name="labor_contract_ends_on" type="date" value="{{ $date('labor_contract_ends_on') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('labor_contract_ends_on')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-left" class="sj-ui-field__label">{{ __('Fecha de retiro') }}</label>
            <input id="worker-left" name="left_on" type="date" value="{{ $date('left_on') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('left_on')" />
        </div>
    </div>

    <hr class="sj-ui-divider">

    <h3 class="sj-form-section__title">{{ __('Seguridad social') }}</h3>
    <div class="sj-company__grid">
        <div class="sj-ui-field">
            <label for="worker-eps" class="sj-ui-field__label">{{ __('EPS') }}</label>
            <input id="worker-eps" name="eps_name" type="text" maxlength="255" value="{{ $text('eps_name') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('eps_name')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-afp" class="sj-ui-field__label">{{ __('Pensión') }}</label>
            <input id="worker-afp" name="afp_name" type="text" maxlength="255" value="{{ $text('afp_name') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('afp_name')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-fund" class="sj-ui-field__label">{{ __('Caja de compensación') }}</label>
            <input id="worker-fund" name="compensation_fund" type="text" maxlength="255" value="{{ $text('compensation_fund') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('compensation_fund')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-arl" class="sj-ui-field__label">{{ __('ARL') }}</label>
            <input id="worker-arl" name="arl_name" type="text" maxlength="255" value="{{ $text('arl_name') }}" class="sj-ui-field__control">
            <x-input-error :messages="$errors->get('arl_name')" />
        </div>
        <div class="sj-ui-field">
            <label for="worker-arl-risk" class="sj-ui-field__label">{{ __('Nivel de riesgo') }}</label>
            <select id="worker-arl-risk" name="arl_risk_level" class="sj-ui-field__control">
                <option value="">{{ __('Sin definir') }}</option>
                @foreach ($choices('arl_risk_level', WorkerFieldOptions::RISK_LEVELS) as $option)
                    <option value="{{ $option }}" @selected($text('arl_risk_level') === $option)>{{ $option }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('arl_risk_level')" />
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
