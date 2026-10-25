<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Post;
use App\Models\User;
use App\Models\Worker;
use App\Models\WeaponImportRow;
use App\Services\Imports\VestImportProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VestImportValidationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_preview_marks_error_when_client_does_not_exist(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $processor = app(VestImportProcessor::class);

        [$rows, $counts] = $processor->prepareRows(
            $this->headers(),
            [$this->row(2, ['Cliente Inexistente', '', '', '', '', 'VEST-001'])],
            $admin,
        );

        $this->assertSame(1, $counts[WeaponImportRow::ACTION_ERROR]);
        $this->assertStringContainsString('Cliente no encontrado', $rows[0]['summary']);
    }

    public function test_preview_marks_error_when_post_does_not_exist(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $client = Client::query()->create(['name' => 'Cliente Alpha', 'nit' => '900100200-1']);
        $processor = app(VestImportProcessor::class);

        [$rows] = $processor->prepareRows(
            $this->headers(),
            [$this->row(2, [$client->name, 'Puesto Fantasma', '', '', '', 'VEST-002'])],
            $admin,
        );

        $this->assertSame(WeaponImportRow::ACTION_ERROR, $rows[0]['action']);
        $this->assertStringContainsString('Puesto no encontrado', $rows[0]['summary']);
    }

    public function test_preview_marks_error_when_document_belongs_to_other_client(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $clientA = Client::query()->create(['name' => 'Cliente A', 'nit' => '900100201-1']);
        $clientB = Client::query()->create(['name' => 'Cliente B', 'nit' => '900100202-1']);

        Worker::query()->create([
            'client_id' => $clientB->id,
            'name' => 'Juan Remoto',
            'document' => '1234567890',
            'position_id' => \App\Models\Position::resolveByName('Guarda')->id,
        ]);

        $processor = app(VestImportProcessor::class);

        [$rows] = $processor->prepareRows(
            $this->headers(),
            [$this->row(2, [$clientA->name, '', '1234567890', 'Juan Remoto', 'Guarda', 'VEST-003'])],
            $admin,
        );

        $this->assertSame(WeaponImportRow::ACTION_ERROR, $rows[0]['action']);
        $this->assertStringContainsString('pertenece a otro cliente', $rows[0]['summary']);
    }

    public function test_preview_accepts_existing_worker_by_document(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $client = Client::query()->create(['name' => 'Cliente Gamma', 'nit' => '900100203-1']);

        Worker::query()->create([
            'client_id' => $client->id,
            'name' => 'Pedro Local',
            'document' => '99887766',
            'position_id' => \App\Models\Position::resolveByName('Guarda')->id,
        ]);

        $processor = app(VestImportProcessor::class);

        [$rows, $counts] = $processor->prepareRows(
            $this->headers(),
            [$this->row(2, [$client->name, '', '99887766', 'Pedro Local', 'Guarda', 'VEST-004'])],
            $admin,
        );

        $this->assertSame(1, $counts[WeaponImportRow::ACTION_CREATE]);
        $this->assertSame(WeaponImportRow::ACTION_CREATE, $rows[0]['action']);
        $this->assertStringContainsString('Trabajador existente', $rows[0]['summary']);
    }

    public function test_execute_does_not_create_missing_post(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $client = Client::query()->create(['name' => 'Cliente Delta', 'nit' => '900100204-1']);
        $processor = app(VestImportProcessor::class);

        $batch = \App\Models\WeaponImportBatch::query()->create([
            'status' => 'draft',
            'type' => \App\Models\WeaponImportBatch::TYPE_VEST,
            'source_name' => 'vests.csv',
            'total_rows' => 1,
            'create_count' => 1,
            'update_count' => 0,
            'no_change_count' => 0,
            'error_count' => 0,
        ]);

        $row = \App\Models\WeaponImportRow::query()->create([
            'batch_id' => $batch->id,
            'row_number' => 2,
            'action' => WeaponImportRow::ACTION_CREATE,
            'summary' => 'test',
            'normalized_payload' => [
                'serial_number' => 'VEST-EXEC-POST',
                'client_name' => $client->name,
                'post_name' => 'Puesto Inexistente',
            ],
        ]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Puesto no encontrado');

        $processor->executeRow($row, $admin);

        $this->assertSame(0, Post::query()->where('name', 'Puesto Inexistente')->count());
    }

    public function test_admin_can_download_vest_import_template(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($admin)
            ->get(route('vest-imports.templates.vest'))
            ->assertOk()
            ->assertHeader(
                'content-type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            );
    }

    public function test_responsible_n1_can_download_vest_import_template(): void
    {
        $user = User::factory()->create([
            'role' => 'RESPONSABLE',
        ]);

        $this->actingAs($user)
            ->get(route('vest-imports.templates.vest'))
            ->assertOk();
    }

    public function test_auditor_cannot_download_vest_import_template(): void
    {
        $user = User::factory()->create(['role' => 'AUDITOR']);

        $this->actingAs($user)
            ->get(route('vest-imports.templates.vest'))
            ->assertForbidden();
    }

    /**
     * @return array<int, string>
     */
    private function headers(): array
    {
        return [
            'Cliente',
            'Puesto',
            'Cedula del empleado',
            'Nombres y apellidos',
            'Cargo',
            'No. serie o codigo',
        ];
    }

    /**
     * @param  array<int, string>  $cells
     * @return array{row_number: int, cells: array<int, string>}
     */
    private function row(int $rowNumber, array $cells): array
    {
        return [
            'row_number' => $rowNumber,
            'cells' => $cells,
        ];
    }
}
