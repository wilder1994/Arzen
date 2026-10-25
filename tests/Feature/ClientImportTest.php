<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\WeaponImportBatch;
use App\Models\WeaponImportRow;
use App\Services\Imports\ClientImportProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientImportTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_import_processor_prepares_preview_rows_for_create_update_and_no_change(): void
    {
        $existingClient = Client::query()->create([
            'name' => 'Cliente Antiguo',
            'nit' => '900123456-7',
            'legal_representative' => 'Ana Perez',
            'address' => 'Cra 1 #2-3',
            'city' => 'Bogota',
            'department' => 'Cundinamarca',
        ]);

        $processor = app(ClientImportProcessor::class);

        [$rows, $counts] = $processor->prepareRows([
            'NIT./CC',
            'RAZON SOCIAL',
            'NOMBRE REP. LEGAL',
            'DIRECCION PRINCIPAL',
            'CIUDAD',
        ], [
            [
                'row_number' => 2,
                'cells' => ['900123456-7', 'Cliente Antiguo', 'Ana Perez', 'Cra 1 #2-3', 'Bogota'],
            ],
            [
                'row_number' => 3,
                'cells' => ['900987654-3', 'Cliente Nuevo', 'Pedro Gomez', 'Cra 4 #5-6', 'Cali'],
            ],
        ]);

        $this->assertSame(1, $counts[WeaponImportRow::ACTION_NO_CHANGE]);
        $this->assertSame(1, $counts[WeaponImportRow::ACTION_CREATE]);
        $this->assertSame(0, $counts[WeaponImportRow::ACTION_ERROR]);

        $this->assertSame(WeaponImportRow::ACTION_NO_CHANGE, $rows[0]['action']);
        $this->assertSame($existingClient->id, $rows[0]['client_id']);

        $this->assertSame(WeaponImportRow::ACTION_CREATE, $rows[1]['action']);
        $this->assertNull($rows[1]['client_id']);
    }

    public function test_client_import_processor_executes_create_and_update_rows(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);
        $processor = app(ClientImportProcessor::class);

        $batch = WeaponImportBatch::query()->create([
            'status' => 'draft',
            'type' => WeaponImportBatch::TYPE_CLIENT,
            'source_name' => 'clientes.csv',
            'total_rows' => 2,
            'create_count' => 1,
            'update_count' => 1,
            'no_change_count' => 0,
            'error_count' => 0,
        ]);

        $existingClient = Client::query()->create([
            'name' => 'Cliente Base',
            'nit' => '900111222-3',
            'legal_representative' => 'Maria Lopez',
            'address' => 'Cra 10 #11-12',
            'city' => 'Bogota',
            'contact_name' => 'Contacto Base',
            'email' => 'base@example.com',
        ]);

        $updateRow = WeaponImportRow::query()->create([
            'batch_id' => $batch->id,
            'client_id' => $existingClient->id,
            'row_number' => 2,
            'action' => WeaponImportRow::ACTION_UPDATE,
            'summary' => 'Actualiza cliente base',
            'raw_payload' => [
                'nit' => '900111222-3',
                'name' => 'Cliente Base Nuevo',
                'legal_representative' => 'Maria Lopez',
                'address' => 'Cra 20 #21-22',
                'city' => 'Bogota',
            ],
            'normalized_payload' => [
                'nit' => '900111222-3',
                'name' => 'Cliente Base Nuevo',
                'legal_representative' => 'Maria Lopez',
                'address' => 'Cra 20 #21-22',
                'city' => 'Bogota',
            ],
            'before_payload' => null,
            'after_payload' => null,
            'errors' => [],
        ]);

        $processor->executeRow($updateRow, $user);

        $existingClient->refresh();
        $updateRow->refresh();

        $this->assertSame('Cliente Base Nuevo', $existingClient->name);
        $this->assertSame('Contacto Base', $existingClient->contact_name);
        $this->assertSame('base@example.com', $existingClient->email);
        $this->assertSame(WeaponImportRow::EXECUTION_COMPLETED, $updateRow->execution_status);
        $this->assertSame($existingClient->id, $updateRow->client_id);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'client_import_updated',
            'auditable_type' => Client::class,
            'auditable_id' => $existingClient->id,
        ]);

        $createRow = WeaponImportRow::query()->create([
            'batch_id' => $batch->id,
            'row_number' => 3,
            'action' => WeaponImportRow::ACTION_CREATE,
            'summary' => 'Crear cliente nuevo',
            'raw_payload' => [
                'nit' => '900333444-5',
                'name' => 'Cliente Nuevo',
                'legal_representative' => 'Carlos Ruiz',
                'address' => 'Cra 30 #31-32',
                'city' => 'Cali',
            ],
            'normalized_payload' => [
                'nit' => '900333444-5',
                'name' => 'Cliente Nuevo',
                'legal_representative' => 'Carlos Ruiz',
                'address' => 'Cra 30 #31-32',
                'city' => 'Cali',
            ],
            'before_payload' => null,
            'after_payload' => null,
            'errors' => [],
        ]);

        $processor->executeRow($createRow, $user);

        $createRow->refresh();

        $this->assertDatabaseHas('clients', [
            'nit' => '900333444-5',
            'name' => 'Cliente Nuevo',
            'city' => 'Cali',
            'contact_name' => null,
            'email' => null,
        ]);

        $this->assertSame(WeaponImportRow::EXECUTION_COMPLETED, $createRow->execution_status);
        $this->assertNotNull($createRow->client_id);
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'client_import_created',
            'auditable_type' => Client::class,
            'auditable_id' => $createRow->client_id,
        ]);
    }

    public function test_client_import_processor_marks_error_when_multiple_clients_share_same_nit(): void
    {
        Client::query()->create([
            'name' => 'Cliente A',
            'nit' => '900123456-7',
        ]);

        Client::query()->create([
            'name' => 'Cliente B',
            'nit' => '9001234567',
        ]);

        $processor = app(ClientImportProcessor::class);

        [$rows, $counts] = $processor->prepareRows([
            'NIT./CC',
            'RAZON SOCIAL',
            'NOMBRE REP. LEGAL',
            'DIRECCION PRINCIPAL',
            'CIUDAD',
        ], [
            [
                'row_number' => 2,
                'cells' => ['9001234567', 'Cliente Duplicado', 'Ana Perez', 'Cra 1 #2-3', 'Bogota'],
            ],
        ]);

        $this->assertSame(1, $counts[WeaponImportRow::ACTION_ERROR]);
        $this->assertSame(WeaponImportRow::ACTION_ERROR, $rows[0]['action']);
        $this->assertContains('Ya existen varios clientes con el mismo número de documento.', $rows[0]['errors']);
    }

    public function test_client_import_accepts_the_zentra_client_export(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);
        $processor = app(ClientImportProcessor::class);

        [$rows, $counts] = $processor->prepareRows(\App\Support\Imports\ZentraClientSchema::headers(), [
            [
                'row_number' => 2,
                'cells' => ['Jurídica', 'Residencial Norte', 'Residencial Norte PH', 'NIT', '901888777-6', 'norte@cliente.test', '3000000000', 'Ana Import', 'ana@cliente.test', 'ph', 'Calle 1 # 2-3', 'Cali', 'Valle del Cauca', 'NO', 'SI'],
            ],
            [
                'row_number' => 3,
                'cells' => ['Natural', 'Pedro Gómez', '', 'CC', '1098000111', '', '', '', '', 'casa', '', 'Bogotá', 'Bogotá D.C.', 'NO', 'NO'],
            ],
        ]);

        $this->assertSame(2, $counts[WeaponImportRow::ACTION_CREATE]);
        $this->assertSame('Residencial Norte PH', $rows[0]['normalized_payload']['legal_name']);
        $this->assertSame(Client::PARTY_NATURAL, $rows[1]['normalized_payload']['party_type']);
        $this->assertSame('CC', $rows[1]['normalized_payload']['document_type']);

        $batch = WeaponImportBatch::query()->create([
            'status' => 'draft',
            'type' => WeaponImportBatch::TYPE_CLIENT,
            'source_name' => 'clientes-zentra.xlsx',
            'total_rows' => 1,
            'create_count' => 1,
            'update_count' => 0,
            'no_change_count' => 0,
            'error_count' => 0,
        ]);
        $row = WeaponImportRow::query()->create(array_merge($rows[0], ['batch_id' => $batch->id]));

        $processor->executeRow($row, $user);

        $client = Client::query()->where('nit', '901888777-6')->firstOrFail();
        $this->assertSame('Residencial Norte', $client->name);
        $this->assertSame('ana@cliente.test', $client->representative_email);
        $this->assertSame('Residencial Norte', $client->mainInstallation->name);
        $this->assertSame('Calle 1 # 2-3', $client->mainInstallation->address);
    }

    public function test_worker_import_builds_installation_post_and_position_from_zentra_employees(): void
    {
        $user = User::factory()->create(['role' => 'ADMIN']);
        $client = Client::query()->create([
            'name' => 'Palmas',
            'legal_name' => 'Palmas del Ingenio PH',
            'nit' => '900555666-1',
            'city' => 'CALI',
            'department' => 'VALLE DEL CAUCA',
        ]);
        $processor = app(\App\Services\Imports\WorkerImportProcessor::class);
        $zentraHeaders = ['Tipo Documento de Identidad', 'Nro. Documento de Identidad', 'Ap. Paterno', 'Ap. Materno', 'Nombres', 'Sexo', 'Edad', 'Tipo Colaborador', 'Razón Social Cliente Final', 'Instalaciones', 'Sector (Ciudad)', 'Puesto', 'Cargo', 'EPS'];

        [$rows, $counts] = $processor->prepareRows($zentraHeaders, [
            [
                'row_number' => 2,
                'cells' => ['CC', '1098000111', 'Pérez', 'Gómez', 'Ana', 'Mujer', '34', 'OPERATIVO', 'Palmas del Ingenio PH', 'Torre norte', 'Cali', 'Portería', 'Escolta', 'SURA'],
            ],
            [
                'row_number' => 3,
                'cells' => ['CC', '1098000222', 'Ruiz', '', 'Luis', 'Hombre', '40', 'OPERATIVO', 'Cliente inexistente', '', '', '', 'Guarda', ''],
            ],
        ]);

        $this->assertSame(1, $counts[WeaponImportRow::ACTION_CREATE]);
        $this->assertSame(1, $counts[WeaponImportRow::ACTION_ERROR]);
        $this->assertSame($client->id, $rows[0]['client_id']);

        $batch = WeaponImportBatch::query()->create([
            'status' => 'draft',
            'type' => WeaponImportBatch::TYPE_WORKER,
            'source_name' => 'empleados-zentra.xlsx',
            'total_rows' => 2,
            'create_count' => 1,
            'update_count' => 0,
            'no_change_count' => 0,
            'error_count' => 1,
        ]);
        $row = WeaponImportRow::query()->create(array_merge($rows[0], ['batch_id' => $batch->id]));

        $processor->executeRow($row, $user);

        $worker = \App\Models\Worker::query()->where('document', '1098000111')->firstOrFail();
        $this->assertSame('Ana Pérez Gómez', $worker->name);
        $this->assertSame('Escolta', $worker->position->name);
        $this->assertSame('Portería', $worker->post->name);
        $this->assertSame('Torre norte', $worker->post->installation->name);
        $this->assertSame($client->id, $worker->post->installation->client_id);
        $this->assertSame('CALI', $worker->post->city);
    }
}

