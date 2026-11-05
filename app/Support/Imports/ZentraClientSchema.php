<?php

namespace App\Support\Imports;

use App\Models\Client;
use Illuminate\Support\Str;

/**
 * Columnas del Excel de clientes de Zentra (App\Support\Client\ClientExcelSchema en Zentra).
 * Mantener el mismo orden y nombres para que el archivo sirva en ambos sistemas.
 */
final class ZentraClientSchema
{
    /**
     * @return list<string>
     */
    public static function headers(): array
    {
        return [
            'Tipo de cliente',
            'Nombre comercial',
            'Razón social',
            'Tipo documento',
            'Número documento',
            'Email',
            'Teléfono',
            'Representante',
            'Email representante',
            'Tipo de estructura',
            'Dirección',
            'Ciudad',
            'Departamento',
        ];
    }

    /**
     * Encabezados normalizados → campo de Arzen. Incluye la plantilla anterior de Arzen.
     *
     * @return array<string, string>
     */
    public static function aliases(bool $hasCommercialName): array
    {
        return [
            'tipo de cliente' => 'party_type',
            'nombre comercial' => 'name',
            'razon social' => $hasCommercialName ? 'legal_name' : 'name',
            'tipo documento' => 'document_type',
            'tipo de documento' => 'document_type',
            'numero documento' => 'nit',
            'numero de documento' => 'nit',
            'nit cc' => 'nit',
            'nit' => 'nit',
            'email' => 'email',
            'correo' => 'email',
            'telefono' => 'phone',
            'representante' => 'legal_representative',
            'representante legal' => 'legal_representative',
            'nombre rep legal' => 'legal_representative',
            'nombre representante legal' => 'legal_representative',
            'email representante' => 'representative_email',
            'direccion' => 'address',
            'direccion principal' => 'address',
            'ciudad' => 'city',
            'departamento' => 'department',
            'tipo de estructura' => 'structure_type',
        ];
    }

    public static function normalizeHeader(string $value): string
    {
        $value = preg_replace('/^\x{FEFF}/u', '', $value) ?? $value;
        $value = strtolower(Str::ascii($value));
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    /**
     * Mismos valores que acepta Zentra; vacío = persona jurídica.
     */
    public static function partyType(string $raw): ?string
    {
        $key = trim(strtolower(Str::ascii($raw)));

        return match ($key) {
            '', 'legal_entity', 'juridica', 'persona juridica', 'pj' => Client::PARTY_LEGAL,
            'natural_person', 'natural', 'persona natural', 'pn' => Client::PARTY_NATURAL,
            default => null,
        };
    }

    public static function partyTypeLabel(?string $partyType): string
    {
        return $partyType === Client::PARTY_NATURAL ? 'Natural' : 'Jurídica';
    }

    public static function documentType(string $raw): ?string
    {
        $key = strtoupper(trim(Str::ascii($raw)));
        $key = preg_replace('/[^A-Z]/', '', $key) ?? '';

        return match ($key) {
            '', 'NIT' => 'NIT',
            'CC', 'CEDULA', 'CEDULADECIUDADANIA' => 'CC',
            'CE', 'CEDULADEEXTRANJERIA' => 'CE',
            'PA', 'PAS', 'PASAPORTE' => 'PA',
            'PPT', 'PEP' => 'PPT',
            default => null,
        };
    }
}
