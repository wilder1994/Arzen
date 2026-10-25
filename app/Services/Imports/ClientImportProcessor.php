<?php

namespace App\Services\Imports;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\User;
use App\Models\WeaponImportBatch;
use App\Models\WeaponImportRow;
use App\Services\Imports\Contracts\ImportBatchProcessor;
use App\Support\Imports\ZentraClientSchema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Carga de clientes con el mismo Excel que exporta e importa Zentra.
 */
class ClientImportProcessor implements ImportBatchProcessor
{
    private const REQUIRED_COLUMNS = ['name', 'nit'];

    private const IMPORTABLE_FIELDS = [
        'party_type',
        'name',
        'legal_name',
        'document_type',
        'nit',
        'email',
        'phone',
        'legal_representative',
        'representative_email',
        'address',
        'city',
        'department',
    ];

    private const FIELD_LABELS = [
        'party_type' => 'Tipo de cliente',
        'name' => 'Nombre comercial',
        'legal_name' => 'Razón social',
        'document_type' => 'Tipo documento',
        'nit' => 'Número documento',
        'email' => 'Email',
        'phone' => 'Teléfono',
        'legal_representative' => 'Representante',
        'representative_email' => 'Email representante',
        'address' => 'Dirección',
        'city' => 'Ciudad',
        'department' => 'Departamento',
    ];

    public function type(): string
    {
        return WeaponImportBatch::TYPE_CLIENT;
    }

    /**
     * @return array<int, string>
     */
    public static function templateHeaders(): array
    {
        return ZentraClientSchema::headers();
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function templateInstructions(): array
    {
        return [
            ['Tipo de cliente', 'No', 'Texto', 'Jurídica o Natural. Vacío = Jurídica.'],
            ['Nombre comercial', 'Sí', 'Texto', 'Nombre que se ve en listados, mapa y reportes.'],
            ['Razón social', 'No', 'Texto', 'Nombre legal del RUT. Vacío = el nombre comercial.'],
            ['Tipo documento', 'No', 'Texto', 'NIT, CC, CE, PA o PPT. Vacío = NIT.'],
            ['Número documento', 'Sí', 'Texto', 'Clave del cliente: si ya existe se actualiza, si no se crea.'],
            ['Email', 'No', 'Correo', 'Correo de contacto del cliente.'],
            ['Teléfono', 'No', 'Texto', 'Teléfono de contacto.'],
            ['Representante', 'No', 'Texto', 'Representante legal.'],
            ['Email representante', 'No', 'Correo', 'Correo del representante legal.'],
            ['Tipo de estructura', 'No', 'Texto', 'Solo lo usa Zentra; Arzen lo ignora.'],
            ['Dirección', 'No', 'Texto', 'Dirección principal. Crea o actualiza la instalación principal.'],
            ['Ciudad', 'No', 'Texto', 'Municipio.'],
            ['Departamento', 'No', 'Texto', 'Departamento.'],
            ['Accesos', 'No', 'SI / NO', 'Solo lo usa Zentra; Arzen lo ignora.'],
            ['Supervisión', 'No', 'SI / NO', 'Solo lo usa Zentra; Arzen lo ignora.'],
        ];
    }

    public function prepareRows(array $headers, array $rows, ?User $user = null): array
    {
        $columnMap = $this->resolveColumnMap($headers);
        $nitFrequency = [];

        foreach ($rows as $row) {
            $normalizedNit = $this->normalizeNitCompareValue($this->extractCell($row['cells'], $columnMap, 'nit'));

            if ($normalizedNit !== '') {
                $nitFrequency[$normalizedNit] = ($nitFrequency[$normalizedNit] ?? 0) + 1;
            }
        }

        $existingClientsByNit = Client::query()
            ->whereNotNull('nit')
            ->get()
            ->groupBy(fn (Client $client) => $this->normalizeNitCompareValue($client->nit));

        $preparedRows = [];
        $counts = [
            WeaponImportRow::ACTION_CREATE => 0,
            WeaponImportRow::ACTION_UPDATE => 0,
            WeaponImportRow::ACTION_NO_CHANGE => 0,
            WeaponImportRow::ACTION_ERROR => 0,
        ];

        foreach ($rows as $row) {
            $rawPayload = $this->buildRawPayload($row['cells'], $columnMap);
            [$normalizedPayload, $errors] = $this->normalizePayload($rawPayload);

            $normalizedNit = $this->normalizeNitCompareValue((string) ($normalizedPayload['nit'] ?? ''));
            if ($normalizedNit !== '' && ($nitFrequency[$normalizedNit] ?? 0) > 1) {
                $errors[] = 'El número de documento está repetido dentro del archivo.';
            }

            $matchingClients = $normalizedNit !== '' ? ($existingClientsByNit->get($normalizedNit) ?? collect()) : collect();
            $client = $matchingClients->count() === 1 ? $matchingClients->first() : null;
            $beforePayload = $client ? $this->clientSnapshot($client) : null;

            if ($matchingClients->count() > 1) {
                $errors[] = 'Ya existen varios clientes con el mismo número de documento.';
            }

            if ($client?->is_own_company) {
                $errors[] = 'Ese documento es el de su propia empresa; edítela en Mi empresa.';
            }

            if ($errors !== []) {
                $action = WeaponImportRow::ACTION_ERROR;
                $summary = implode(' ', array_unique($errors));
            } elseif (! $client) {
                $action = WeaponImportRow::ACTION_CREATE;
                $summary = 'Documento nuevo. Se creará el cliente con su instalación principal.';
            } else {
                $changedFields = $this->detectChangedFields($client, $normalizedPayload);

                if ($changedFields === []) {
                    $action = WeaponImportRow::ACTION_NO_CHANGE;
                    $summary = 'La información importable ya coincide con el sistema.';
                } else {
                    $action = WeaponImportRow::ACTION_UPDATE;
                    $summary = 'Actualiza: '.implode(', ', array_map(
                        fn (string $field) => self::FIELD_LABELS[$field] ?? $field,
                        $changedFields
                    ));
                }
            }

            $counts[$action]++;

            $preparedRows[] = [
                'client_id' => $client?->id,
                'row_number' => $row['row_number'],
                'action' => $action,
                'summary' => $summary,
                'raw_payload' => $rawPayload,
                'normalized_payload' => $normalizedPayload,
                'before_payload' => $beforePayload,
                'after_payload' => null,
                'errors' => array_values(array_unique($errors)),
            ];
        }

        return [$preparedRows, $counts];
    }

    public function executeRow(WeaponImportRow $row, User $user): void
    {
        DB::transaction(function () use ($row, $user) {
            $row->refresh();
            $row->loadMissing('client');

            if ($row->execution_status === WeaponImportRow::EXECUTION_COMPLETED) {
                return;
            }

            $row->update([
                'execution_status' => WeaponImportRow::EXECUTION_PROCESSING,
                'execution_error' => null,
            ]);

            $payload = $row->normalized_payload ?? [];

            if ($row->action === WeaponImportRow::ACTION_NO_CHANGE) {
                $client = $row->client ?: $this->findClientByNit($payload['nit'] ?? null);

                $row->update([
                    'client_id' => $client?->id,
                    'after_payload' => $client ? $this->clientSnapshot($client) : null,
                    'execution_status' => WeaponImportRow::EXECUTION_COMPLETED,
                    'processed_at' => now(),
                ]);

                return;
            }

            if ($row->action === WeaponImportRow::ACTION_CREATE) {
                $client = Client::create($this->buildCreatePayload($payload));
                $after = $this->clientSnapshot($client->fresh());

                $row->update([
                    'client_id' => $client->id,
                    'after_payload' => $after,
                    'execution_status' => WeaponImportRow::EXECUTION_COMPLETED,
                    'processed_at' => now(),
                ]);

                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'client_import_created',
                    'auditable_type' => Client::class,
                    'auditable_id' => $client->id,
                    'before' => null,
                    'after' => $after,
                ]);

                return;
            }

            $client = $row->client ?: $this->findClientByNit($payload['nit'] ?? null);

            if (! $client) {
                throw new RuntimeException(sprintf(
                    'No se encontró el cliente de la fila %d al momento de ejecutar el lote.',
                    $row->row_number
                ));
            }

            $before = $this->clientSnapshot($client);
            $client->fill($this->buildUpdatePayload($payload));
            $client->save();

            $after = $this->clientSnapshot($client->fresh());

            $row->update([
                'client_id' => $client->id,
                'before_payload' => $before,
                'after_payload' => $after,
                'execution_status' => WeaponImportRow::EXECUTION_COMPLETED,
                'processed_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'client_import_updated',
                'auditable_type' => Client::class,
                'auditable_id' => $client->id,
                'before' => $before,
                'after' => $after,
            ]);
        });
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<string, int>
     */
    private function resolveColumnMap(array $headers): array
    {
        $normalizedHeaders = [];
        foreach ($headers as $index => $header) {
            $normalized = $this->normalizeHeader((string) $header);
            if ($normalized !== '' && ! isset($normalizedHeaders[$normalized])) {
                $normalizedHeaders[$normalized] = $index;
            }
        }

        $hasCommercialName = isset($normalizedHeaders['nombre comercial']);
        $columnMap = [];

        foreach (ZentraClientSchema::aliases($hasCommercialName) as $alias => $field) {
            if (isset($normalizedHeaders[$alias]) && ! isset($columnMap[$field])) {
                $columnMap[$field] = $normalizedHeaders[$alias];
            }
        }

        $missingColumns = array_values(array_diff(self::REQUIRED_COLUMNS, array_keys($columnMap)));
        if ($missingColumns !== []) {
            throw ValidationException::withMessages([
                'document' => 'Faltan columnas obligatorias: '.implode(', ', array_map(
                    fn (string $field) => self::FIELD_LABELS[$field] ?? $field,
                    $missingColumns
                )).'. Use el Excel de clientes de Zentra o la plantilla de Arzen.',
            ]);
        }

        return $columnMap;
    }

    /**
     * @param  array<int, string>  $cells
     * @param  array<string, int>  $columnMap
     * @return array<string, string>
     */
    private function buildRawPayload(array $cells, array $columnMap): array
    {
        $payload = [];

        foreach (self::IMPORTABLE_FIELDS as $field) {
            $payload[$field] = $this->extractCell($cells, $columnMap, $field);
        }

        return $payload;
    }

    /**
     * @param  array<string, string>  $raw
     * @return array{0: array<string, mixed>, 1: list<string>}
     */
    private function normalizePayload(array $raw): array
    {
        $errors = [];
        $value = fn (string $field) => ($trimmed = trim((string) ($raw[$field] ?? ''))) === '' ? null : $trimmed;

        $partyType = ZentraClientSchema::partyType($raw['party_type'] ?? '');
        if ($partyType === null) {
            $errors[] = 'Tipo de cliente: jurídica o natural.';
        }

        $documentType = ZentraClientSchema::documentType($raw['document_type'] ?? '');
        if ($documentType === null) {
            $errors[] = 'Tipo de documento no válido (NIT, CC, CE, PA o PPT).';
        }

        $payload = [
            'party_type' => $partyType ?? Client::PARTY_LEGAL,
            'name' => $value('name'),
            'legal_name' => $value('legal_name'),
            'document_type' => $documentType ?? 'NIT',
            'nit' => $value('nit'),
            'email' => $value('email'),
            'phone' => $value('phone'),
            'legal_representative' => $value('legal_representative'),
            'representative_email' => $value('representative_email'),
            'address' => $value('address'),
            'city' => $value('city'),
            'department' => $value('department'),
        ];

        if ($payload['nit'] === null) {
            $errors[] = 'El número de documento es obligatorio.';
        }

        if ($payload['name'] === null) {
            $errors[] = 'El nombre comercial es obligatorio.';
        }

        foreach (['email' => 'Email', 'representative_email' => 'Email representante'] as $field => $label) {
            if ($payload[$field] !== null && ! filter_var($payload[$field], FILTER_VALIDATE_EMAIL)) {
                $errors[] = $label.' no es un correo válido.';
            }
        }

        return [$payload, $errors];
    }

    /**
     * @param  array<int, string>  $cells
     * @param  array<string, int>  $columnMap
     */
    private function extractCell(array $cells, array $columnMap, string $field): string
    {
        $index = $columnMap[$field] ?? null;

        return $index === null ? '' : trim((string) ($cells[$index] ?? ''));
    }

    /**
     * @param  array<string, mixed>  $normalizedPayload
     * @return array<int, string>
     */
    private function detectChangedFields(Client $client, array $normalizedPayload): array
    {
        $changedFields = [];

        if (! $this->valuesMatch($client->name, $normalizedPayload['name'] ?? null)) {
            $changedFields[] = 'name';
        }

        foreach (array_diff(self::IMPORTABLE_FIELDS, ['name', 'nit']) as $field) {
            $incomingValue = $normalizedPayload[$field] ?? null;

            if ($incomingValue !== null && ! $this->valuesMatch($client->{$field}, $incomingValue)) {
                $changedFields[] = $field;
            }
        }

        return $changedFields;
    }

    /**
     * @return array<string, mixed>
     */
    private function clientSnapshot(Client $client): array
    {
        return $client->only(array_merge(self::IMPORTABLE_FIELDS, ['contact_name', 'neighborhood', 'latitude', 'longitude']));
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function buildCreatePayload(array $payload): array
    {
        $attributes = array_merge(
            array_fill_keys(self::IMPORTABLE_FIELDS, null),
            collect($payload)->only(self::IMPORTABLE_FIELDS)->all(),
        );
        $attributes['party_type'] ??= Client::PARTY_LEGAL;
        $attributes['document_type'] ??= 'NIT';

        return $attributes;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function buildUpdatePayload(array $payload): array
    {
        $updatePayload = ['name' => $payload['name'] ?? null];

        foreach (array_diff(self::IMPORTABLE_FIELDS, ['name', 'nit']) as $field) {
            if (($payload[$field] ?? null) !== null) {
                $updatePayload[$field] = $payload[$field];
            }
        }

        return $updatePayload;
    }

    private function findClientByNit(mixed $nit): ?Client
    {
        $target = $this->normalizeNitCompareValue((string) ($nit ?? ''));

        if ($target === '') {
            return null;
        }

        return Client::query()->get()->first(fn (Client $client) => $this->normalizeNitCompareValue($client->nit) === $target);
    }

    private function valuesMatch(mixed $currentValue, mixed $incomingValue): bool
    {
        if ($currentValue === null || $currentValue === '') {
            return $incomingValue === null || $incomingValue === '';
        }

        return trim((string) $currentValue) === trim((string) $incomingValue);
    }

    private function normalizeHeader(string $value): string
    {
        return ZentraClientSchema::normalizeHeader($value);
    }

    private function normalizeNitCompareValue(?string $value): string
    {
        $value = strtoupper(Str::ascii((string) $value));

        return preg_replace('/[^A-Z0-9]+/', '', $value) ?? '';
    }
}
