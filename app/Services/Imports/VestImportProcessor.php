<?php

namespace App\Services\Imports;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Position;
use App\Models\Post;
use App\Models\User;
use App\Models\Vest;
use App\Models\WeaponImportBatch;
use App\Models\WeaponImportRow;
use App\Models\Worker;
use App\Services\Imports\Contracts\ImportBatchProcessor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class VestImportProcessor implements ImportBatchProcessor
{
    private const REQUIRED_COLUMNS = [
        'serial_number',
    ];

    private const IMPORTABLE_VEST_FIELDS = [
        'brand',
        'batch',
        'size',
        'manufactured_at',
        'expires_at',
        'device_responsible',
    ];

    private const FIELD_LABELS = [
        'serial_number' => 'No. serie o código',
        'worker_document' => 'Cédula del empleado',
        'worker_name' => 'Nombres y apellidos',
        'worker_role' => 'Cargo',
        'client_name' => 'Cliente',
        'client_legal_name' => 'Razón social cliente',
        'department' => 'Departamento',
        'city' => 'Ciudad',
        'post_name' => 'Puesto',
        'brand' => 'Marca chaleco',
        'batch' => 'Lote',
        'size' => 'Talla',
        'manufactured_at' => 'Fecha de fabricación',
        'expires_at' => 'Fecha de vencimiento',
        'device_responsible' => 'Responsable dispositivo',
    ];

    /**
     * @var array<string, string>
     */
    private const HEADER_ALIASES = [
        'cedula del empleado' => 'worker_document',
        'cedula empleado' => 'worker_document',
        'documento empleado' => 'worker_document',
        'nombres y apellidos' => 'worker_name',
        'nombre empleado' => 'worker_name',
        'cargo' => 'worker_role',
        'razon social cliente' => 'client_legal_name',
        'razon social' => 'client_legal_name',
        'cliente' => 'client_name',
        'regional' => 'client_name',
        'departamento' => 'department',
        'ciudad' => 'city',
        'responsable dispositivo' => 'device_responsible',
        'centro de costos' => 'post_name',
        'centro costos' => 'post_name',
        'puesto' => 'post_name',
        'marca chaleco' => 'brand',
        'marca' => 'brand',
        'lote' => 'batch',
        'no serie o codigo' => 'serial_number',
        'numero serie o codigo' => 'serial_number',
        'no serie' => 'serial_number',
        'numero serie' => 'serial_number',
        'serie' => 'serial_number',
        'fecha de fabricacion' => 'manufactured_at',
        'fecha fabricacion' => 'manufactured_at',
        'fecha de vencimiento' => 'expires_at',
        'fecha vencimiento' => 'expires_at',
        'vence' => 'expires_at',
        'talla' => 'size',
    ];

    public function type(): string
    {
        return WeaponImportBatch::TYPE_VEST;
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function templateInstructions(): array
    {
        return [
            ['No. serie o código', 'Sí', 'Texto', 'Clave principal del lote. Si la serie ya existe, se actualiza; si no, se crea el chaleco.'],
            ['Cédula del empleado', 'No', 'Texto', 'Debe existir en el sistema y pertenecer al cliente de la fila.'],
            ['Nombres y apellidos', 'No', 'Texto', 'Referencia informativa; la validación usa la cédula.'],
            ['Cargo', 'No', 'Texto', 'Valores aceptados: Escolta, Supervisor, Guarda, Motorizado, Guarda infraestructura.'],
            ['Razón social cliente', 'Condicional', 'Texto', 'ADMIN: obligatorio en cada fila. RESPONSABLE N1: opcional (se infiere de cartera). Debe existir en el sistema; no se crea en la carga.'],
            ['Puesto', 'Condicional', 'Texto', 'Si se informa, debe existir previamente para el cliente. No se crea en la carga.'],
            ['Marca chaleco', 'No', 'Texto', 'Marca del chaleco.'],
            ['Lote', 'No', 'Texto', 'Lote del fabricante.'],
            ['Fecha de fabricación', 'No', 'Fecha dd/mm/aaaa', ''],
            ['Fecha de vencimiento', 'No', 'Fecha dd/mm/aaaa', 'Usada para la semaforización del inventario.'],
            ['Talla', 'No', 'Texto', ''],
            ['Responsable dispositivo', 'No', 'Texto', 'Opcional; si se omite puede resolverse desde el cliente.'],
        ];
    }

    public function prepareRows(array $headers, array $rows, ?User $user = null): array
    {
        $columnMap = $this->resolveColumnMap($headers);
        $serialFrequency = [];
        $serialCandidates = [];

        foreach ($rows as $row) {
            $serialValue = $this->extractCell($row['cells'], $columnMap, 'serial_number');
            $normalizedSerial = $this->normalizeCompareValue($serialValue);

            if ($normalizedSerial !== '') {
                $serialFrequency[$normalizedSerial] = ($serialFrequency[$normalizedSerial] ?? 0) + 1;
                $serialCandidates[] = $serialValue;
            }
        }

        $existingVests = Vest::query()
            ->whereIn('serial_number', array_values(array_unique($serialCandidates)))
            ->get()
            ->keyBy(fn (Vest $vest) => $this->normalizeCompareValue($vest->serial_number));

        $preparedRows = [];
        $counts = [
            WeaponImportRow::ACTION_CREATE => 0,
            WeaponImportRow::ACTION_UPDATE => 0,
            WeaponImportRow::ACTION_NO_CHANGE => 0,
            WeaponImportRow::ACTION_ERROR => 0,
        ];

        foreach ($rows as $row) {
            $rawPayload = $this->buildRawPayload($row['cells'], $columnMap);
            $normalizedPayload = $this->normalizePayload($rawPayload);
            $errors = $normalizedPayload['errors'];
            unset($normalizedPayload['errors']);

            $normalizedSerial = $this->normalizeCompareValue((string) ($normalizedPayload['serial_number'] ?? ''));
            if ($normalizedSerial !== '' && ($serialFrequency[$normalizedSerial] ?? 0) > 1) {
                $errors[] = 'La serie está repetida dentro del archivo.';
            }

            $vest = $normalizedSerial !== '' ? $existingVests->get($normalizedSerial) : null;
            $beforePayload = $vest ? $this->vestSnapshot($vest) : null;
            $relationNotes = [];

            if ($errors !== []) {
                $action = WeaponImportRow::ACTION_ERROR;
                $summary = implode(' ', array_unique($errors));
            } elseif (! $vest) {
                $action = WeaponImportRow::ACTION_CREATE;
                $summary = 'Serie nueva. Se creará el chaleco.';
            } else {
                $changedFields = $this->detectChangedFields($vest, $normalizedPayload);

                if ($changedFields === []) {
                    $action = WeaponImportRow::ACTION_NO_CHANGE;
                    $summary = 'La información ya coincide con el sistema.';
                } else {
                    $action = WeaponImportRow::ACTION_UPDATE;
                    $summary = 'Actualiza: ' . implode(', ', array_map(
                        fn (string $field) => self::FIELD_LABELS[$field] ?? $field,
                        $changedFields
                    ));
                }
            }

            if ($action !== WeaponImportRow::ACTION_ERROR && $user !== null) {
                [$relationErrors, $relationNotes] = $this->validateRowRelations($normalizedPayload, $user);
                if ($relationErrors !== []) {
                    $action = WeaponImportRow::ACTION_ERROR;
                    $errors = array_values(array_unique([...$errors, ...$relationErrors]));
                    $summary = implode(' ', $errors);
                } elseif ($relationNotes !== []) {
                    $summary = trim($summary.' '.implode(' ', $relationNotes));
                }
            }

            $counts[$action]++;

            $preparedRows[] = [
                'vest_id' => $vest?->id,
                'row_number' => $row['row_number'],
                'action' => $action,
                'summary' => trim($summary),
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

            if ($row->execution_status === WeaponImportRow::EXECUTION_COMPLETED) {
                return;
            }

            $row->update([
                'execution_status' => WeaponImportRow::EXECUTION_PROCESSING,
                'execution_error' => null,
            ]);

            if ($row->action === WeaponImportRow::ACTION_NO_CHANGE) {
                $vest = $row->vest_id ? Vest::find($row->vest_id) : null;
                $row->update([
                    'vest_id' => $vest?->id,
                    'after_payload' => $vest ? $this->vestSnapshot($vest) : null,
                    'execution_status' => WeaponImportRow::EXECUTION_COMPLETED,
                    'processed_at' => now(),
                ]);

                return;
            }

            if ($row->action === WeaponImportRow::ACTION_ERROR) {
                throw new RuntimeException($row->summary ?: 'Fila con errores.');
            }

            $payload = $row->normalized_payload ?? [];
            [$client, $clientErrors] = $this->resolveClientStrict($payload, $user);
            if ($clientErrors !== []) {
                throw new RuntimeException(implode(' ', $clientErrors));
            }

            if (! $client) {
                throw new RuntimeException('No se pudo resolver el cliente para esta fila.');
            }

            $worker = $this->resolveWorker($payload, $client, $user);
            $post = $this->resolvePost($payload, $client);

            $vestPayload = $this->buildVestPayload($payload, $client->id, $worker?->id, $post?->id);

            if ($row->action === WeaponImportRow::ACTION_CREATE) {
                $vest = Vest::create($vestPayload);
                $after = $this->vestSnapshot($vest->fresh(['client', 'worker', 'post']));

                $row->update([
                    'vest_id' => $vest->id,
                    'client_id' => $client->id,
                    'after_payload' => $after,
                    'execution_status' => WeaponImportRow::EXECUTION_COMPLETED,
                    'processed_at' => now(),
                ]);

                AuditLog::create([
                    'user_id' => $user->id,
                    'action' => 'vest_import_created',
                    'auditable_type' => Vest::class,
                    'auditable_id' => $vest->id,
                    'before' => null,
                    'after' => $after,
                ]);

                return;
            }

            $vest = $row->vest_id ? Vest::find($row->vest_id) : Vest::query()->where('serial_number', $payload['serial_number'] ?? null)->first();
            if (! $vest) {
                throw new RuntimeException('No se encontró el chaleco a actualizar.');
            }

            $before = $this->vestSnapshot($vest);
            $vest->update($vestPayload);
            $after = $this->vestSnapshot($vest->fresh(['client', 'worker', 'post']));

            $row->update([
                'vest_id' => $vest->id,
                'client_id' => $client->id,
                'after_payload' => $after,
                'execution_status' => WeaponImportRow::EXECUTION_COMPLETED,
                'processed_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'vest_import_updated',
                'auditable_type' => Vest::class,
                'auditable_id' => $vest->id,
                'before' => $before,
                'after' => $after,
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function validateRowRelations(array $payload, User $user): array
    {
        [$client, $clientErrors] = $this->resolveClientStrict($payload, $user);
        if ($clientErrors !== []) {
            return [$clientErrors, []];
        }

        [$postErrors, $postNotes] = $this->validatePost($client, $payload);
        [$workerErrors, $workerNotes] = $this->validateWorker($client, $payload);

        return [
            array_values(array_unique([...$postErrors, ...$workerErrors])),
            array_values(array_filter([...$postNotes, ...$workerNotes])),
        ];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{0: ?Client, 1: array<int, string>}
     */
    private function resolveClientStrict(array $payload, User $user): array
    {
        $clientLabel = trim((string) ($payload['client_name'] ?? ''));
        if ($clientLabel === '') {
            $clientLabel = trim((string) ($payload['client_legal_name'] ?? ''));
        }

        if ($user->canManageAllVests()) {
            if ($clientLabel === '') {
                return [null, ['El cliente es obligatorio en cada fila.']];
            }

            $client = $this->findClientByName($clientLabel);
            if (! $client) {
                return [null, ['Cliente no encontrado: '.$clientLabel.'.']];
            }

            return [$client, []];
        }

        $portfolioIds = $user->clients()->pluck('clients.id');

        if ($clientLabel !== '') {
            $client = Client::query()
                ->whereIn('id', $portfolioIds)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($clientLabel)])
                ->first();

            if ($client) {
                return [$client, []];
            }

            if ($this->findClientByName($clientLabel)) {
                return [null, ['El cliente no pertenece a su cartera.']];
            }

            return [null, ['Cliente no encontrado: '.$clientLabel.'.']];
        }

        if ($portfolioIds->count() === 1) {
            return [Client::find($portfolioIds->first()), []];
        }

        $document = trim((string) ($payload['worker_document'] ?? ''));
        if ($document !== '') {
            $worker = Worker::query()
                ->where('document', $document)
                ->whereIn('client_id', $portfolioIds)
                ->first();

            if ($worker) {
                return [$worker->client, []];
            }
        }

        return [null, ['Debe indicar un cliente existente de su cartera.']];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function validatePost(?Client $client, array $payload): array
    {
        $postName = trim((string) ($payload['post_name'] ?? ''));
        if ($postName === '' || $client === null) {
            return [[], []];
        }

        $post = $this->findPostForClient($client, $postName);
        if (! $post) {
            return [['Puesto no encontrado para el cliente. Debe crearlo antes en el sistema.'], []];
        }

        return [[], ['Puesto: '.$post->name.'.']];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array{0: array<int, string>, 1: array<int, string>}
     */
    private function validateWorker(?Client $client, array $payload): array
    {
        if ($client === null) {
            return [[], []];
        }

        $name = trim((string) ($payload['worker_name'] ?? ''));
        $document = trim((string) ($payload['worker_document'] ?? ''));

        if ($this->isUnassignedWorker($name, $document)) {
            return [[], ['Trabajador: sin asignar.']];
        }

        if ($document === '' && $name === '') {
            return [[], []];
        }

        if ($document !== '') {
            $workerOnOtherClient = Worker::query()
                ->where('document', $document)
                ->where('client_id', '!=', $client->id)
                ->with('client')
                ->first();

            if ($workerOnOtherClient) {
                $otherClientName = $workerOnOtherClient->client?->name ?? 'otro cliente';

                return [['La cédula pertenece a otro cliente ('.$otherClientName.').'], []];
            }

            $worker = Worker::query()
                ->where('client_id', $client->id)
                ->where('document', $document)
                ->first();

            if ($worker) {
                return [[], ['Trabajador existente (cédula '.$document.'). Se asignará el chaleco.']];
            }

            $role = $this->normalizeWorkerRole((string) ($payload['worker_role'] ?? ''));
            if ($name === '' || $role === null) {
                return [['Para crear el trabajador se requieren nombre y cargo válido.'], []];
            }

            return [[], ['Se creará trabajador (cédula '.$document.').']];
        }

        $worker = Worker::query()
            ->where('client_id', $client->id)
            ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
            ->first();

        if ($worker) {
            return [[], ['Trabajador existente ('.$name.'). Se asignará el chaleco.']];
        }

        $role = $this->normalizeWorkerRole((string) ($payload['worker_role'] ?? ''));
        if ($role === null) {
            return [['Para crear el trabajador se requieren nombre y cargo válido.'], []];
        }

        return [[], ['Se creará trabajador ('.$name.').']];
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveWorker(array $payload, Client $client, User $user): ?Worker
    {
        $name = trim((string) ($payload['worker_name'] ?? ''));
        $document = trim((string) ($payload['worker_document'] ?? ''));

        if ($this->isUnassignedWorker($name, $document)) {
            return null;
        }

        if ($document === '' && $name === '') {
            return null;
        }

        $role = $this->normalizeWorkerRole((string) ($payload['worker_role'] ?? ''));

        $worker = null;
        if ($document !== '') {
            $workerOnOtherClient = Worker::query()
                ->where('document', $document)
                ->where('client_id', '!=', $client->id)
                ->first();

            if ($workerOnOtherClient) {
                throw new RuntimeException('La cédula pertenece a otro cliente.');
            }

            $worker = Worker::query()
                ->where('client_id', $client->id)
                ->where('document', $document)
                ->first();
        }

        if (! $worker && $name !== '') {
            $worker = Worker::query()
                ->where('client_id', $client->id)
                ->whereRaw('LOWER(name) = ?', [mb_strtolower($name)])
                ->first();
        }

        $responsibleId = $user->canManageAllVests()
            ? ($worker?->responsible_user_id)
            : $user->id;

        if (! $worker) {
            if ($name === '' || $role === null) {
                throw new RuntimeException('Para crear el trabajador se requieren nombre y cargo válido.');
            }

            $worker = Worker::create([
                'client_id' => $client->id,
                'name' => $name,
                'document' => $document !== '' ? $document : null,
                'position_id' => Position::resolveByName($role)?->id,
                'responsible_user_id' => $responsibleId,
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'worker_created',
                'auditable_type' => Worker::class,
                'auditable_id' => $worker->id,
                'before' => null,
                'after' => ['source' => 'vest_import', 'name' => $worker->name],
            ]);

            return $worker;
        }

        return $worker;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolvePost(array $payload, Client $client): ?Post
    {
        $postName = trim((string) ($payload['post_name'] ?? ''));
        if ($postName === '') {
            return null;
        }

        $post = $this->findPostForClient($client, $postName);

        if (! $post) {
            throw new RuntimeException('Puesto no encontrado para el cliente. Debe crearlo antes en el sistema.');
        }

        return $post;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    private function buildVestPayload(array $payload, int $clientId, ?int $workerId, ?int $postId): array
    {
        return [
            'client_id' => $clientId,
            'worker_id' => $workerId,
            'post_id' => $postId,
            'serial_number' => $payload['serial_number'],
            'brand' => $payload['brand'] ?? null,
            'batch' => $payload['batch'] ?? null,
            'size' => $payload['size'] ?? null,
            'manufactured_at' => $payload['manufactured_at'] ?? null,
            'expires_at' => $payload['expires_at'] ?? null,
            'device_responsible' => $payload['device_responsible'] ?? null,
        ];
    }

    /**
     * @param  array<string, string>  $rawPayload
     * @return array<string, mixed>
     */
    private function normalizePayload(array $rawPayload): array
    {
        $errors = [];
        $serial = trim((string) ($rawPayload['serial_number'] ?? ''));
        if ($serial === '') {
            $errors[] = 'La serie es obligatoria.';
        }

        [$manufacturedAt, $manufacturedErrors] = $this->normalizeDate((string) ($rawPayload['manufactured_at'] ?? ''), false);
        [$expiresAt, $expiresErrors] = $this->normalizeDate((string) ($rawPayload['expires_at'] ?? ''), false);

        return [
            'serial_number' => $serial !== '' ? $serial : null,
            'worker_document' => $this->optionalString($rawPayload['worker_document'] ?? ''),
            'worker_name' => $this->optionalString($rawPayload['worker_name'] ?? ''),
            'worker_role' => $this->optionalString($rawPayload['worker_role'] ?? ''),
            'client_name' => $this->optionalString($rawPayload['client_name'] ?? ''),
            'client_legal_name' => $this->optionalString($rawPayload['client_legal_name'] ?? ''),
            'department' => $this->optionalString($rawPayload['department'] ?? ''),
            'city' => $this->optionalString($rawPayload['city'] ?? ''),
            'post_name' => $this->optionalString($rawPayload['post_name'] ?? ''),
            'brand' => $this->optionalString($rawPayload['brand'] ?? ''),
            'batch' => $this->optionalString($rawPayload['batch'] ?? ''),
            'size' => $this->optionalString($rawPayload['size'] ?? ''),
            'manufactured_at' => $manufacturedAt,
            'expires_at' => $expiresAt,
            'device_responsible' => $this->optionalString($rawPayload['device_responsible'] ?? ''),
            'errors' => [...$errors, ...$manufacturedErrors, ...$expiresErrors],
        ];
    }

    /**
     * @return array<int, string>
     */
    private function detectChangedFields(Vest $vest, array $normalizedPayload): array
    {
        $changed = [];

        foreach (['serial_number', ...self::IMPORTABLE_VEST_FIELDS] as $field) {
            $current = $vest->{$field};
            if ($current instanceof Carbon) {
                $current = $current->format('Y-m-d');
            }
            $incoming = $normalizedPayload[$field] ?? null;

            if (! $this->valuesMatch($current, $incoming)) {
                $changed[] = $field;
            }
        }

        return $changed;
    }

    /**
     * @return array<string, mixed>
     */
    private function vestSnapshot(Vest $vest): array
    {
        return [
            'serial_number' => $vest->serial_number,
            'brand' => $vest->brand,
            'batch' => $vest->batch,
            'size' => $vest->size,
            'manufactured_at' => $vest->manufactured_at?->format('Y-m-d'),
            'expires_at' => $vest->expires_at?->format('Y-m-d'),
            'device_responsible' => $vest->device_responsible,
            'client_id' => $vest->client_id,
            'client_name' => $vest->client?->name,
            'worker_id' => $vest->worker_id,
            'worker_name' => $vest->worker?->name,
            'post_id' => $vest->post_id,
            'post_name' => $vest->post?->name,
        ];
    }

    /**
     * @param  array<int, string>  $headers
     * @return array<string, int>
     */
    private function resolveColumnMap(array $headers): array
    {
        $columnMap = [];

        foreach ($headers as $index => $header) {
            $normalizedHeader = $this->normalizeHeader($header);
            if ($normalizedHeader === '') {
                continue;
            }

            $field = self::HEADER_ALIASES[$normalizedHeader] ?? null;
            if (! $field || isset($columnMap[$field])) {
                continue;
            }

            $columnMap[$field] = $index;
        }

        $missingColumns = array_values(array_diff(self::REQUIRED_COLUMNS, array_keys($columnMap)));
        if ($missingColumns !== []) {
            throw ValidationException::withMessages([
                'document' => 'Faltan columnas obligatorias: ' . implode(', ', array_map(
                    fn (string $field) => self::FIELD_LABELS[$field] ?? $field,
                    $missingColumns
                )) . '.',
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
        $fields = array_unique(array_merge(self::REQUIRED_COLUMNS, array_values(self::HEADER_ALIASES)));

        foreach ($fields as $field) {
            if (isset($columnMap[$field])) {
                $payload[$field] = $this->extractCell($cells, $columnMap, $field);
            }
        }

        return $payload;
    }

    /**
     * @param  array<int, string>  $cells
     * @param  array<string, int>  $columnMap
     */
    private function extractCell(array $cells, array $columnMap, string $field): string
    {
        $index = $columnMap[$field] ?? null;
        if ($index === null) {
            return '';
        }

        return trim((string) ($cells[$index] ?? ''));
    }

    private function findClientByName(string $name): ?Client
    {
        return Client::query()
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($name))])
            ->first();
    }

    private function findPostForClient(Client $client, string $postName): ?Post
    {
        return Post::query()
            ->where('client_id', $client->id)
            ->whereNull('archived_at')
            ->whereRaw('LOWER(name) = ?', [mb_strtolower(trim($postName))])
            ->first();
    }

    private function normalizeWorkerRole(string $value): ?string
    {
        $value = trim($value);

        return $value !== '' ? $value : null;
    }

    private function isUnassignedWorker(?string $name, ?string $document): bool
    {
        $nameKey = $this->normalizeHeader((string) $name);

        return in_array($nameKey, ['sin asignar', 'no asignado', 'na'], true)
            && trim((string) $document) === '';
    }

    /**
     * @return array{0: ?string, 1: array<int, string>}
     */
    private function normalizeDate(string $value, bool $required): array
    {
        $value = trim($value);
        if ($value === '') {
            return [null, $required ? ['Fecha obligatoria.'] : []];
        }

        if (is_numeric($value)) {
            $date = Carbon::create(1899, 12, 30)->addDays((int) floor((float) $value));

            return [$date->format('Y-m-d'), []];
        }

        foreach (['d/m/Y', 'j/n/Y', 'd-m-Y', 'M-y', 'm-y', 'Y-m-d'] as $format) {
            try {
                return [Carbon::createFromFormat($format, $value)->format('Y-m-d'), []];
            } catch (Throwable) {
            }
        }

        try {
            return [Carbon::parse($value)->format('Y-m-d'), []];
        } catch (Throwable) {
            return [null, ['Fecha inválida: '.$value]];
        }
    }

    private function optionalString(string $value): ?string
    {
        $value = trim($value);

        return $value === '' ? null : $value;
    }

    private function valuesMatch(mixed $currentValue, mixed $incomingValue): bool
    {
        if ($incomingValue === null || $incomingValue === '') {
            return true;
        }

        if ($currentValue === null || $currentValue === '') {
            return false;
        }

        return trim((string) $currentValue) === trim((string) $incomingValue);
    }

    private function normalizeHeader(string $value): string
    {
        $value = Str::ascii($value);
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '';

        return trim(preg_replace('/\s+/', ' ', $value) ?? '');
    }

    private function normalizeCompareValue(string $value): string
    {
        return strtoupper(preg_replace('/\s+/', '', trim($value)) ?? '');
    }
}
