<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\ContractType;
use App\Models\EngagementType;
use App\Models\Position;
use App\Models\User;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_labor_catalogs_are_seeded_and_a_worker_can_be_created_without_a_client(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $position = Position::query()->create(['name' => 'Escolta', 'sort_order' => 10]);
        $engagement = EngagementType::query()->where('name', 'Dependiente')->firstOrFail();
        $contract = ContractType::query()->where('name', 'Término fijo')->firstOrFail();

        $this->assertSame(4, EngagementType::query()->count());
        $this->assertSame(5, ContractType::query()->count());

        $this->actingAs($admin)
            ->get(route('workers.create'))
            ->assertOk()
            ->assertSee('Departamento de expedición')
            ->assertSee('Tipo de vinculación')
            ->assertSee('Colombiana')
            ->assertDontSee('name="client_id"', false)
            ->assertDontSee('Código EPS');

        $this->actingAs($admin)
            ->post(route('workers.store'), [
                'document_type' => 'CC',
                'document' => '1098000333',
                'first_names' => 'Ana',
                'last_name_paternal' => 'Pérez',
                'nationality' => 'Colombiana',
                'blood_group' => 'O+',
                'education' => 'Bachillerato',
                'position_id' => $position->id,
                'engagement_type_id' => $engagement->id,
                'contract_type_id' => $contract->id,
                'arl_risk_level' => 'III',
            ])
            ->assertRedirect(route('workers.index'));

        $worker = Worker::query()->where('document', '1098000333')->firstOrFail();
        $this->assertNull($worker->client_id);
        $this->assertSame('Ana Pérez', $worker->name);
        $this->assertSame($position->id, $worker->position_id);
        $this->assertSame('Dependiente', $worker->engagementType->name);
        $this->assertSame('Término fijo', $worker->contractType->name);
        $this->assertSame('O+', $worker->blood_group);
    }

    public function test_editing_the_ficha_keeps_the_client_and_imported_codes(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::query()->create(['name' => 'Palmas', 'nit' => '900111222-3']);
        $worker = Worker::query()->create([
            'client_id' => $client->id,
            'name' => 'Ana Pérez',
            'first_names' => 'Ana',
            'document_type' => 'CC',
            'document' => '1098000444',
            'eps_code' => 'EPS001',
            'eps_name' => 'SURA',
            'afp_code' => 'AFP01',
            'contributor_type' => 'Dependiente',
        ]);

        $this->actingAs($admin)
            ->put(route('workers.update', $worker), [
                'document_type' => 'CC',
                'document' => '1098000444',
                'first_names' => 'Ana',
                'last_name_paternal' => 'Pérez',
                'nationality' => 'Colombiana',
                'eps_name' => 'SURA',
                'change_note' => 'Corrige la ficha.',
            ])
            ->assertRedirect(route('workers.index'));

        $worker->refresh();
        $this->assertSame($client->id, $worker->client_id);
        $this->assertSame('EPS001', $worker->eps_code);
        $this->assertSame('AFP01', $worker->afp_code);
        $this->assertSame('Dependiente', $worker->contributor_type);
        $this->assertSame('SURA', $worker->eps_name);
    }

    public function test_an_engagement_type_in_use_cannot_be_deleted(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $engagement = EngagementType::query()->where('name', 'Aprendiz')->firstOrFail();
        Worker::query()->create([
            'name' => 'Luis Ruiz',
            'first_names' => 'Luis',
            'document_type' => 'CC',
            'engagement_type_id' => $engagement->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('catalogs.engagement-types.destroy', $engagement))
            ->assertRedirect(route('catalogs.index', ['tab' => 'vinculacion']))
            ->assertSessionHas('error');

        $this->assertModelExists($engagement);

        $this->actingAs($admin)
            ->get(route('catalogs.index', ['tab' => 'contratos']))
            ->assertOk()
            ->assertSee('Término indefinido')
            ->assertSee('Prestación de servicios');
    }
}
