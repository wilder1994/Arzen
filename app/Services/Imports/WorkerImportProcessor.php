<?php

namespace App\Services\Imports;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\ContractType;
use App\Models\EngagementType;
use App\Models\Installation;
use App\Models\Position;
use App\Models\Post;
use App\Models\User;
use App\Models\WeaponImportBatch;
use App\Models\WeaponImportRow;
use App\Models\Worker;
use App\Models\WorkerHistory;
use App\Services\Imports\Contracts\ImportBatchProcessor;
use App\Support\Imports\ZentraClientSchema;
use App\Support\Workers\WorkerProfile;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;

/**
 * Carga de trabajadores con la plantilla de empleados de Zentra (solo las columnas que usa Arzen).
 */
class WorkerImportProcessor implements ImportBatchProcessor
{
    /**
     * @var array<string, string>
     */
    private const HEADER_ALIASES = [
        'tipo documento de identidad' => 'document_type',
        'tipo documento' => 'document_type',
        'tipo de documento' => 'document_type',
        'nro documento de identidad' => 'document',
        'numero documento de identidad' => 'document',
        'numero documento' => 'document',
        'documento' => 'document',
        'cedula' => 'document',
        'ap paterno' => 'last_name_paternal',
        'apellido paterno' => 'last_name_paternal',
        'ap materno' => 'last_name_maternal',
        'apellido materno' => 'last_name_maternal',
        'nombres' => 'first_names',
        'nombre' => 'full_name',
        'nombre completo' => 'full_name',
        'razon social cliente final' => 'client',
        'cliente' => 'client',
        'instalaciones' => 'installation',
        'instalacion' => 'installation',
        'puesto' => 'post',
        'cargo' => 'position',
        'sexo' => 'sex',
        'edad' => 'age',
        'tipo colaborador' => 'collaborator_type',
        'sector ciudad' => 'sector',
        'mismo cc origen' => 'same_cost_center',
        'fecha nacimiento' => 'birth_date',
        'lugar de nacimiento departamento' => 'birth_department',
        'lugar de nacimiento ciudad' => 'birth_city',
        'telefono emergencia' => 'emergency_phone',
        'contacto emergencia' => 'emergency_contact',
        'nacionalidad' => 'nationality',
        'discapacidad' => 'has_disability',
        'email ficha' => 'email',
        'lugar de expedicion documento de identificacion' => 'document_issue_department',
        'lugar de expedicion documento de identificacion ciudad' => 'document_issue_city',
        'fecha de expedicion documento de identificacion' => 'document_issued_at',
        'g sanguineo' => 'blood_group',
        'telefono' => 'phone',
        'lugar de residencia' => 'residence_city',
        'direccion' => 'address',
        'escolaridad' => 'education',
        'estado civil' => 'marital_status',
        'numero de hijos' => 'children_count',
        'tipo de vinculacion' => 'engagement_type',
        'tipo de cotizante' => 'contributor_type',
        'tipo de contrato' => 'labor_contract_type',
        'fecha de ingreso' => 'hired_on',
        'vencimiento de contrato' => 'labor_contract_ends_on',
        'fecha de retiro' => 'left_on',
        'codigo eps' => 'eps_code',
        'eps' => 'eps_name',
        'codigo afp' => 'afp_code',
        'pension' => 'afp_name',
        'caja de compensacion' => 'compensation_fund',
        'arl' => 'arl_name',
        'nivel de riesgo arl' => 'arl_risk_level',
    ];

    public function type(): string
    {
        return WeaponImportBatch::TYPE_WORKER;
    }

    /**
     * Subconjunto de la plantilla de empleados de Zentra, con los mismos nombres de columna.
     *
     * @return array<int, string>
     */
    public static function templateHeaders(): array
    {
        return [
            'Tipo Documento de Identidad',
            'Nro. Documento de Identidad',
            'Ap. Paterno',
            'Ap. Materno',
            'Nombres',
            'Sexo',
            'Fecha Nacimiento',
            'Lugar de Nacimiento (Departamento)',
            'Lugar de Nacimiento (Ciudad)',
            'Nacionalidad',
            'G.Sanguíneo',
            'Lugar de Expedición Documento de Identificación',
            'Lugar de Expedición Documento de Identificación (Ciudad)',
            'Fecha de Expedición Documento de Identificación',
            'Discapacidad',
            'Escolaridad',
            'Estado civil',
            'Número de hijos',
            'Teléfono',
            'Email Ficha',
            'Dirección',
            'Lugar de residencia',
            'Contacto Emergencia',
            'Teléfono Emergencia',
            'Cargo',
            'Tipo de vinculación',
            'Tipo de contrato',
            'Fecha de ingreso',
            'Vencimiento de contrato',
            'Fecha de retiro',
            'EPS',
            'Pensión',
            'Caja de compensación',
            'ARL',
            'Nivel de riesgo ARL',
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    public static function templateInstructions(): array
    {
        return [
            ['Tipo Documento de Identidad', 'No', 'Texto', 'CC, CE, PA o PPT. Vacío = CC.'],
            ['Nro. Documento de Identidad', 'Sí', 'Texto', 'Si el documento ya existe sin cliente, se actualiza. Si no existe, se crea.'],
            ['Ap. Paterno', 'Sí*', 'Texto', 'Primer apellido. (*) Basta nombres o un apellido.'],
            ['Ap. Materno', 'No', 'Texto', 'Segundo apellido.'],
            ['Nombres', 'Sí*', 'Texto', 'Nombres del trabajador.'],
            ['Sexo', 'No', 'Texto', 'Hombre o Mujer.'],
            ['Fecha Nacimiento', 'No', 'Fecha dd/mm/aaaa', 'También acepta fecha de Excel.'],
            ['Lugar de Nacimiento (Departamento)', 'No', 'Texto', 'Departamento de Colombia, por ejemplo ANTIOQUIA.'],
            ['Lugar de Nacimiento (Ciudad)', 'No', 'Texto', 'Municipio de ese departamento.'],
            ['Nacionalidad', 'No', 'Texto', 'Vacío = sin dato. En el formulario nuevo queda Colombiana.'],
            ['G.Sanguíneo', 'No', 'Texto', 'O+, O-, A+, A-, B+, B-, AB+, AB- o Pendiente.'],
            ['Lugar de Expedición Documento de Identificación', 'No', 'Texto', 'Departamento donde se expidió el documento.'],
            ['Lugar de Expedición Documento de Identificación (Ciudad)', 'No', 'Texto', 'Ciudad de ese departamento.'],
            ['Fecha de Expedición Documento de Identificación', 'No', 'Fecha dd/mm/aaaa', 'También acepta fecha de Excel.'],
            ['Discapacidad', 'No', 'Sí o No', 'Vacío = sin dato.'],
            ['Escolaridad', 'No', 'Texto', 'Primaria, Bachillerato, Educación superior u Otro.'],
            ['Estado civil', 'No', 'Texto', 'Texto libre.'],
            ['Número de hijos', 'No', 'Número', 'De 0 a 30.'],
            ['Teléfono', 'No', 'Texto', 'Teléfono de contacto.'],
            ['Email Ficha', 'No', 'Correo', 'Correo del trabajador.'],
            ['Dirección', 'No', 'Texto', 'Dirección de residencia.'],
            ['Lugar de residencia', 'No', 'Texto', 'Ciudad donde vive.'],
            ['Contacto Emergencia', 'No', 'Texto', 'Nombre de la persona de emergencia.'],
            ['Teléfono Emergencia', 'No', 'Texto', 'Teléfono de esa persona.'],
            ['Cargo', 'No', 'Texto', 'Catálogo de Ajustes → Cargos. Si no existe, se crea.'],
            ['Tipo de vinculación', 'No', 'Texto', 'Catálogo de Ajustes → Tipos de vinculación. Si no existe, se crea.'],
            ['Tipo de contrato', 'No', 'Texto', 'Catálogo de Ajustes → Tipos de contrato. Si no existe, se crea.'],
            ['Fecha de ingreso', 'No', 'Fecha dd/mm/aaaa', 'También acepta fecha de Excel.'],
            ['Vencimiento de contrato', 'No', 'Fecha dd/mm/aaaa', 'También acepta fecha de Excel.'],
            ['Fecha de retiro', 'No', 'Fecha dd/mm/aaaa', 'También acepta fecha de Excel.'],
            ['EPS', 'No', 'Texto', 'Nombre de la EPS.'],
            ['Pensión', 'No', 'Texto', 'Fondo de pensión (AFP).'],
            ['Caja de compensación', 'No', 'Texto', 'Nombre de la caja.'],
            ['ARL', 'No', 'Texto', 'Aseguradora de riesgos laborales.'],
            ['Nivel de riesgo ARL', 'No', 'Texto', 'I, II, III, IV o V.'],
            ['Cliente / Instalación / Puesto', 'No', 'Texto', 'No van en esta plantilla. Si el archivo trae Razón Social Cliente Final, Instalaciones o Puesto, se aplican. Si vienen vacíos, el trabajador queda sin cliente ni puesto.'],
        ];
    }

    public function prepareRows(array $headers, array $rows, ?User $user = null): array
    {
        $columnMap = $this->resolveColumnMap($headers);
        $clients = Client::query()->get();
        $documentFrequency = [];

        foreach ($rows as $row) {
            $key = $this->documentKey($this->cell($row['cells'], $columnMap, 'document'));
            if ($key !== '') {
                $documentFrequency[$key] = ($documentFrequency[$key] ?? 0) + 1;
            }
        }

        $preparedRows = [];
        $counts = [
            WeaponImportRow::ACTION_CREATE => 0,
            WeaponImportRow::ACTION_UPDATE => 0,
            WeaponImportRow::ACTION_NO_CHANGE => 0,
            WeaponImportRow::ACTION_ERROR => 0,
        ];

        foreach ($rows as $row) {
            $raw = [];
            foreach (array_unique(array_values(self::HEADER_ALIASES)) as $field) {
                $raw[$field] = $this->cell($row['cells'], $columnMap, $field);
            }

            [$payload, $errors, $notes] = $this->normalize($raw, $clients);

            if (($documentFrequency[$this->documentKey((string) $payload['document'])] ?? 0) > 1) {
                $errors[] = 'El documento está repetido dentro del archivo.';
            }

            $client = $payload['client_id'] ? $clients->firstWhere('id', $payload['client_id']) : null;
            $worker = null;

            if ($payload['document']) {
                $workerQuery = Worker::query()->with(['position', 'post.installation', 'engagementType', 'contractType']);

                if ($client) {
                    $otherClient = Worker::query()
                        ->where('document', $payload['document'])
                        ->where('client_id', '!=', $client->id)
                        ->exists();

                    if ($otherClient) {
                        $errors[] = 'El documento pertenece a un trabajador de otro cliente.';
                    }

                    $worker = (clone $workerQuery)
                        ->where('client_id', $client->id)
                        ->where('document', $payload['document'])
                        ->first();
                } else {
                    if (Worker::query()->where('document', $payload['document'])->whereNotNull('client_id')->exists()) {
                        $errors[] = 'El documento pertenece a un trabajador ya asignado a un cliente.';
                    }

                    $worker = (clone $workerQuery)
                        ->whereNull('client_id')
                        ->where('document', $payload['document'])
                        ->first();
                }
            }

            if ($errors !== []) {
                $action = WeaponImportRow::ACTION_ERROR;
                $summary = implode(' ', array_unique($errors));
            } elseif (! $worker) {
                $action = WeaponImportRow::ACTION_CREATE;
                $summary = trim('Se creará el trabajador. '.implode(' ', $notes));
            } else {
                $changes = $this->detectChanges($worker, $payload);

                if ($changes === []) {
                    $action = WeaponImportRow::ACTION_NO_CHANGE;
                    $summary = 'El trabajador ya coincide con el sistema.';
                } else {
                    $action = WeaponImportRow::ACTION_UPDATE;
                    $summary = trim('Actualiza: '.implode(', ', $changes).'. '.implode(' ', $notes));
                }
            }

            $counts[$action]++;

            $preparedRows[] = [
                'client_id' => $client?->id,
                'row_number' => $row['row_number'],
                'action' => $action,
                'summary' => $summary,
                'raw_payload' => $raw,
                'normalized_payload' => $payload,
                'before_payload' => $worker ? $this->snapshot($worker) : null,
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

            $payload = $row->normalized_payload ?? [];
            $client = isset($payload['client_id']) ? Client::query()->find($payload['client_id']) : null;

            if (($payload['client_id'] ?? null) && ! $client) {
                throw new RuntimeException(sprintf('No se encontró el cliente de la fila %d.', $row->row_number));
            }

            $worker = Worker::query()
                ->when($client, fn ($query) => $query->where('client_id', $client->id), fn ($query) => $query->whereNull('client_id'))
                ->where('document', $payload['document'] ?? null)
                ->first();

            if ($row->action === WeaponImportRow::ACTION_NO_CHANGE) {
                $row->update([
                    'after_payload' => $worker ? $this->snapshot($worker) : null,
                    'execution_status' => WeaponImportRow::EXECUTION_COMPLETED,
                    'processed_at' => now(),
                ]);

                return;
            }

            $post = null;
            if ($client) {
                $installation = $this->resolveInstallation($client, $payload['installation'] ?? null);
                $post = $this->resolvePost($installation, $payload['post'] ?? null);
            }
            $position = Position::resolveByName($payload['position'] ?? null);
            $engagement = EngagementType::resolveByName($payload['engagement_type'] ?? null);
            $contract = ContractType::resolveByName($payload['labor_contract_type'] ?? null);
            $before = $worker ? $this->snapshot($worker) : null;
            $previousPostId = $worker?->post_id;

            $attributes = [
                'name' => $payload['name'],
                'document_type' => $payload['document_type'],
                'document' => $payload['document'],
            ];

            if ($client) {
                $attributes['client_id'] = $client->id;
            }

            if ($position) {
                $attributes['position_id'] = $position->id;
            }

            if ($engagement) {
                $attributes['engagement_type_id'] = $engagement->id;
            }

            if ($contract) {
                $attributes['contract_type_id'] = $contract->id;
            }

            if ($post) {
                $attributes['post_id'] = $post->id;
            }

            $worker = $worker ?? new Worker(['archived_at' => null]);
            $worker->fill($attributes + WorkerProfile::filledAttributes($payload));
            $worker->save();

            $historyLines = [$before ? __('Actualizado por carga masiva.') : __('Registro inicial por carga masiva.')];
            if ($post && $previousPostId !== $post->id) {
                $historyLines[] = __('Puesto: :from → :to', [
                    'from' => $previousPostId ? (Post::query()->find($previousPostId)?->name ?? '—') : __('sin puesto'),
                    'to' => $post->name,
                ]);
            }

            WorkerHistory::create([
                'worker_id' => $worker->id,
                'user_id' => $user->id,
                'body' => implode("\n", $historyLines),
            ]);

            $after = $this->snapshot($worker->fresh(['position', 'post.installation']));

            $row->update([
                'client_id' => $client?->id,
                'before_payload' => $before,
                'after_payload' => $after,
                'execution_status' => WeaponImportRow::EXECUTION_COMPLETED,
                'processed_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => $before ? 'worker_import_updated' : 'worker_import_created',
                'auditable_type' => Worker::class,
                'auditable_id' => $worker->id,
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
        $map = [];

        foreach ($headers as $index => $header) {
            $field = self::HEADER_ALIASES[ZentraClientSchema::normalizeHeader((string) $header)] ?? null;

            if ($field && ! isset($map[$field])) {
                $map[$field] = $index;
            }
        }

        $hasName = isset($map['full_name']) || isset($map['first_names']) || isset($map['last_name_paternal']);
        $missing = array_filter([
            isset($map['document']) ? null : 'Nro. Documento de Identidad',
            $hasName ? null : 'Nombres / Ap. Paterno',
        ]);

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'document' => 'Faltan columnas obligatorias: '.implode(', ', $missing).'. Use la plantilla de empleados de Zentra o la de Arzen.',
            ]);
        }

        return $map;
    }

    /**
     * @param  array<string, string>  $raw
     * @return array{0: array<string, mixed>, 1: list<string>, 2: list<string>}
     */
    private function normalize(array $raw, Collection $clients): array
    {
        $errors = [];
        $notes = [];
        $value = fn (string $field) => ($trimmed = trim((string) ($raw[$field] ?? ''))) === '' ? null : $trimmed;

        $name = $value('full_name') ?? trim(implode(' ', array_filter([
            $value('first_names'),
            $value('last_name_paternal'),
            $value('last_name_maternal'),
        ])));

        $documentType = ZentraClientSchema::documentType($raw['document_type'] ?? '');
        if (($raw['document_type'] ?? '') === '') {
            $documentType = 'CC';
        } elseif ($documentType === null || $documentType === 'NIT') {
            $errors[] = 'Tipo de documento no válido (CC, CE, PA o PPT).';
        }

        $document = $value('document');
        if ($document === null) {
            $errors[] = 'El número de documento es obligatorio.';
        }

        if ($name === '') {
            $errors[] = 'El nombre del trabajador es obligatorio.';
        }

        $client = null;
        $clientName = $value('client');
        if ($clientName !== null) {
            $key = $this->textKey($clientName);
            $client = $clients->first(fn (Client $candidate) => $this->textKey($candidate->name) === $key || $this->textKey((string) $candidate->legal_name) === $key);

            if (! $client) {
                $errors[] = 'El cliente "'.$clientName.'" no existe. Cárguelo primero en Clientes.';
            }
        }

        if ($client) {
            $installationName = $value('installation');
            $installation = $installationName ? $this->findInstallation($client, $installationName) : null;

            if ($installationName && ! $installation) {
                $notes[] = 'Se creará la instalación "'.$installationName.'".';
            }

            $postName = $value('post');
            if ($postName && ! ($installation && $this->findPost($installation, $postName))) {
                $notes[] = 'Se creará el puesto "'.$postName.'".';
            }
        }

        [$profile, $profileNotes] = WorkerProfile::attributesFromRaw($raw);
        $notes = array_merge($notes, $profileNotes);

        $engagementName = $value('engagement_type');
        if ($engagementName && ! EngagementType::findByName($engagementName)) {
            $notes[] = 'Se creará el tipo de vinculación "'.$engagementName.'".';
        }

        $contractName = $value('labor_contract_type');
        if ($contractName && ! ContractType::findByName($contractName)) {
            $notes[] = 'Se creará el tipo de contrato "'.$contractName.'".';
        }

        return [array_merge($profile, [
            'client_id' => $client?->id,
            'client_name' => $client?->name ?? $clientName,
            'name' => $name,
            'document_type' => $documentType ?? 'CC',
            'document' => $document,
            'installation' => $value('installation'),
            'post' => $value('post'),
            'position' => $value('position'),
            'engagement_type' => $engagementName,
            'labor_contract_type' => $contractName,
        ]), $errors, $notes];
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return list<string>
     */
    private function detectChanges(Worker $worker, array $payload): array
    {
        $changes = [];

        if ($this->textKey($worker->name) !== $this->textKey((string) $payload['name'])) {
            $changes[] = 'nombre';
        }

        if ($payload['position'] && $this->textKey((string) $worker->position?->name) !== $this->textKey($payload['position'])) {
            $changes[] = 'cargo';
        }

        if ($payload['post'] && $this->textKey((string) $worker->post?->name) !== $this->textKey($payload['post'])) {
            $changes[] = 'puesto';
        }

        if (($payload['engagement_type'] ?? null) && $this->textKey((string) $worker->engagementType?->name) !== $this->textKey($payload['engagement_type'])) {
            $changes[] = 'vinculación';
        }

        if (($payload['labor_contract_type'] ?? null) && $this->textKey((string) $worker->contractType?->name) !== $this->textKey($payload['labor_contract_type'])) {
            $changes[] = 'contrato';
        }

        if (WorkerProfile::differs($worker, $payload)) {
            $changes[] = 'ficha';
        }

        return $changes;
    }

    private function resolveInstallation(Client $client, ?string $name): Installation
    {
        if (! $name) {
            return Installation::mainFor($client);
        }

        return $this->findInstallation($client, $name) ?? Installation::query()->create(array_merge(
            $client->only(Installation::LOCATION_FIELDS),
            ['client_id' => $client->id, 'name' => $name],
        ));
    }

    private function resolvePost(Installation $installation, ?string $name): ?Post
    {
        if (! $name) {
            return null;
        }

        return $this->findPost($installation, $name) ?? Post::query()->create([
            'installation_id' => $installation->id,
            'client_id' => $installation->client_id,
            'name' => $name,
        ]);
    }

    private function findInstallation(Client $client, string $name): ?Installation
    {
        $key = $this->textKey($name);

        return Installation::query()
            ->where('client_id', $client->id)
            ->active()
            ->get()
            ->first(fn (Installation $installation) => $this->textKey($installation->name) === $key);
    }

    private function findPost(Installation $installation, string $name): ?Post
    {
        $key = $this->textKey($name);

        return Post::query()
            ->where('installation_id', $installation->id)
            ->active()
            ->get()
            ->first(fn (Post $post) => $this->textKey($post->name) === $key);
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Worker $worker): array
    {
        $worker->loadMissing(['position', 'post.installation']);

        return [
            'name' => $worker->name,
            'document_type' => $worker->document_type,
            'document' => $worker->document,
            'position' => $worker->position?->name,
            'installation' => $worker->post?->installation?->name,
            'post' => $worker->post?->name,
        ];
    }

    /**
     * @param  array<int, string>  $cells
     * @param  array<string, int>  $map
     */
    private function cell(array $cells, array $map, string $field): string
    {
        $index = $map[$field] ?? null;

        return $index === null ? '' : trim((string) ($cells[$index] ?? ''));
    }

    private function textKey(?string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', mb_strtolower(Str::ascii((string) $value))) ?? '');
    }

    private function documentKey(string $value): string
    {
        return preg_replace('/[^A-Z0-9]/', '', strtoupper(Str::ascii($value))) ?? '';
    }
}
