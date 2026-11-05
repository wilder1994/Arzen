<?php

namespace App\Support\Workers;

use App\Models\Worker;
use Carbon\Carbon;
use Throwable;

final class WorkerProfile
{
    /**
     * @return list<string>
     */
    public static function textColumns(): array
    {
        return [
            'first_names', 'last_name_paternal', 'last_name_maternal', 'sex',
            'birth_department', 'birth_city', 'nationality', 'blood_group',
            'document_issue_department', 'document_issue_city', 'has_disability',
            'phone', 'email', 'address', 'residence_city', 'emergency_phone', 'emergency_contact',
            'collaborator_type', 'sector', 'same_cost_center',
            'contributor_type',
            'education', 'marital_status',
            'eps_code', 'eps_name', 'afp_code', 'afp_name', 'compensation_fund', 'arl_name', 'arl_risk_level',
        ];
    }

    /**
     * @return list<string>
     */
    public static function dateColumns(): array
    {
        return ['birth_date', 'document_issued_at', 'hired_on', 'labor_contract_ends_on', 'left_on'];
    }

    /**
     * @return list<string>
     */
    public static function integerColumns(): array
    {
        return ['age', 'children_count'];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function rules(): array
    {
        $rules = [];

        foreach (self::textColumns() as $column) {
            if (in_array($column, ['first_names', 'last_name_paternal', 'last_name_maternal'], true)) {
                continue;
            }

            $rules[$column] = $column === 'email'
                ? ['nullable', 'email', 'max:255']
                : ['nullable', 'string', 'max:255'];
        }

        foreach (self::dateColumns() as $column) {
            $rules[$column] = ['nullable', 'date'];
        }

        $rules['age'] = ['nullable', 'integer', 'min:0', 'max:120'];
        $rules['children_count'] = ['nullable', 'integer', 'min:0', 'max:30'];
        $rules['first_names'] = ['required', 'string', 'max:255'];
        $rules['last_name_paternal'] = ['nullable', 'string', 'max:255'];
        $rules['last_name_maternal'] = ['nullable', 'string', 'max:255'];

        return $rules;
    }

    public static function composeName(?string $firstNames, ?string $paternal, ?string $maternal): string
    {
        return trim(implode(' ', array_filter([
            trim((string) $firstNames),
            trim((string) $paternal),
            trim((string) $maternal),
        ], fn (string $part) => $part !== '')));
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    public static function attributesFromRaw(array $raw): array
    {
        $attributes = [];
        $notes = [];
        $value = fn (string $field) => ($trimmed = trim((string) ($raw[$field] ?? ''))) === '' ? null : $trimmed;

        foreach (self::textColumns() as $column) {
            $attributes[$column] = $value($column);
        }

        foreach (self::dateColumns() as $column) {
            [$date, $valid] = self::date($value($column));
            $attributes[$column] = $date;

            if (! $valid) {
                $notes[] = 'La fecha "'.$value($column).'" no se pudo leer y quedó vacía.';
            }
        }

        foreach (self::integerColumns() as $column) {
            $rawValue = $value($column);

            if ($rawValue === null) {
                $attributes[$column] = null;
                continue;
            }

            if (! is_numeric($rawValue)) {
                $attributes[$column] = null;
                $notes[] = 'El valor "'.$rawValue.'" no es un número y quedó vacío.';
                continue;
            }

            $attributes[$column] = (int) $rawValue;
        }

        return [$attributes, $notes];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function differs(Worker $worker, array $payload): bool
    {
        foreach (array_merge(self::textColumns(), self::dateColumns(), self::integerColumns()) as $column) {
            if (! array_key_exists($column, $payload) || $payload[$column] === null || $payload[$column] === '') {
                continue;
            }

            $current = $worker->{$column};

            if (in_array($column, self::dateColumns(), true)) {
                $current = $current instanceof Carbon ? $current->format('Y-m-d') : ($current ? (string) $current : null);
            }

            if ((string) $current !== (string) $payload[$column]) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public static function filledAttributes(array $payload): array
    {
        $attributes = [];

        foreach (array_merge(self::textColumns(), self::dateColumns(), self::integerColumns()) as $column) {
            if (! array_key_exists($column, $payload) || $payload[$column] === null || $payload[$column] === '') {
                continue;
            }

            $attributes[$column] = $payload[$column];
        }

        return $attributes;
    }

    /**
     * @return array{0: ?string, 1: bool}
     */
    private static function date(?string $value): array
    {
        if ($value === null || $value === '') {
            return [null, true];
        }

        if (is_numeric($value)) {
            return [Carbon::create(1899, 12, 30)->addDays((int) floor((float) $value))->format('Y-m-d'), true];
        }

        foreach (['d/m/Y', 'j/n/Y', 'd-m-Y', 'j-n-Y', 'Y-m-d', 'Y/m/d', 'd.m.Y'] as $format) {
            try {
                return [Carbon::createFromFormat($format, $value)->format('Y-m-d'), true];
            } catch (Throwable) {
            }
        }

        try {
            return [Carbon::parse($value)->format('Y-m-d'), true];
        } catch (Throwable) {
            return [null, false];
        }
    }
};
