<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\File;
use App\Models\IncidentType;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponClientAssignment;
use App\Models\WeaponDocument;
use App\Models\WeaponIncident;
use App\Services\DashboardMetricsService;
use Database\Seeders\IncidentModalitySeeder;
use Database\Seeders\IncidentTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeaponRevalidationDocumentMetricsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed([
            IncidentTypeSeeder::class,
            IncidentModalitySeeder::class,
        ]);
    }

    public function test_dashboard_expired_documents_exclude_non_revalidatable_weapons(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $responsible = User::factory()->create(['role' => 'RESPONSABLE']);
        $client = Client::query()->create(['name' => 'Cliente Revalidación', 'nit' => '900300400-1']);
        $responsible->clients()->attach($client->id);

        $operational = $this->createWeapon('SER-OK', $client, $responsible);
        $hurtada = $this->createWeapon('SER-HU', $client, $responsible);
        $incautadaOpen = $this->createWeapon('SER-IN-OP', $client, $responsible);
        $incautadaDefinitive = $this->createWeapon('SER-IN-DEF', $client, $responsible);

        $this->createIncident($hurtada, 'hurtada', WeaponIncident::STATUS_OPEN, $admin);
        $this->createIncident($incautadaOpen, 'incautada', WeaponIncident::STATUS_IN_PROGRESS, $admin);
        $this->createIncident(
            $incautadaDefinitive,
            'incautada',
            WeaponIncident::STATUS_RESOLVED,
            $admin,
            WeaponIncident::OUTCOME_SEIZURE_DEFINITIVE
        );

        $this->createExpiredRenewalDocument($operational);
        $this->createExpiredRenewalDocument($hurtada);
        $this->createExpiredRenewalDocument($incautadaOpen);
        $this->createExpiredRenewalDocument($incautadaDefinitive);

        $metrics = app(DashboardMetricsService::class)->forUser($admin);
        $kpis = collect($metrics['kpis'])->keyBy('key');

        $this->assertCount(6, $metrics['kpis']);
        $this->assertSame(4, $kpis['total']['value']);
        $this->assertSame('No operativas', $kpis['outside']['label']);
        $this->assertSame(2, $kpis['outside']['value']);
        $this->assertSame(2, $kpis['in_inventory']['value']);
        $this->assertSame(1, $kpis['seizure_open']['value']);
        $this->assertSame(2, $kpis['expired_docs']['value']);
        $this->assertStringContainsString('inventory_scope=non_operational', (string) $kpis['outside']['url']);
        $this->assertStringContainsString('inventory_scope=operational', (string) $kpis['in_inventory']['url']);
    }

    public function test_renewal_chart_segments_exclude_non_revalidatable_weapons(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $responsible = User::factory()->create(['role' => 'RESPONSABLE']);
        $client = Client::query()->create(['name' => 'Cliente Gráfico', 'nit' => '900300402-1']);
        $responsible->clients()->attach($client->id);

        $vigente = $this->createWeapon('SER-CH-VIG', $client, $responsible);
        $hurtada = $this->createWeapon('SER-CH-HU', $client, $responsible);
        $incautadaOpen = $this->createWeapon('SER-CH-IN', $client, $responsible);

        $this->createIncident($hurtada, 'hurtada', WeaponIncident::STATUS_OPEN, $admin);
        $this->createIncident($incautadaOpen, 'incautada', WeaponIncident::STATUS_IN_PROGRESS, $admin);

        $chartMonth = now()->startOfMonth()->addMonths(5)->format('Y-m');
        $chartYear = (int) now()->startOfMonth()->addMonths(5)->format('Y');
        $validUntil = now()->startOfMonth()->addMonths(5)->endOfMonth()->toDateString();

        $this->createRenewalDocument($vigente, $validUntil);
        $this->createRenewalDocument($hurtada, $validUntil);
        $this->createRenewalDocument($incautadaOpen, $validUntil);

        $metrics = app(DashboardMetricsService::class)->forUser($admin, $chartYear);
        $monthItem = collect($metrics['renewal_chart']['items'])->firstWhere('key', $chartMonth);

        $this->assertNotNull($monthItem);
        $this->assertSame(2, $monthItem['total']);
        $this->assertSame(1, $monthItem['vigente']);
        $this->assertSame(0, $monthItem['vencido']);
        $this->assertSame(1, $monthItem['incautada']);
        $this->assertSame(0, $monthItem['preventiva']);
        $this->assertSame(0, $monthItem['por_vencer']);
    }

    public function test_weapon_revalidation_exclusion_scope(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $responsible = User::factory()->create(['role' => 'RESPONSABLE']);
        $client = Client::query()->create(['name' => 'Cliente Scope', 'nit' => '900300401-1']);
        $responsible->clients()->attach($client->id);

        $hurtada = $this->createWeapon('SER-SCOPE-HU', $client, $responsible);
        $incautadaOpen = $this->createWeapon('SER-SCOPE-IN', $client, $responsible);
        $incautadaDefinitive = $this->createWeapon('SER-SCOPE-ID', $client, $responsible);

        $this->createIncident($hurtada, 'hurtada', WeaponIncident::STATUS_OPEN, $admin);
        $this->createIncident($incautadaOpen, 'incautada', WeaponIncident::STATUS_OPEN, $admin);
        $this->createIncident(
            $incautadaDefinitive,
            'incautada',
            WeaponIncident::STATUS_RESOLVED,
            $admin,
            WeaponIncident::OUTCOME_SEIZURE_DEFINITIVE
        );

        $hurtada->load('revalidationDocumentExcludingIncidents');
        $incautadaOpen->load('revalidationDocumentExcludingIncidents');
        $incautadaDefinitive->load('revalidationDocumentExcludingIncidents');

        $this->assertTrue($hurtada->isExcludedFromRevalidationDocuments());
        $this->assertFalse($incautadaOpen->isExcludedFromRevalidationDocuments());
        $this->assertTrue($incautadaDefinitive->isExcludedFromRevalidationDocuments());
    }

    private function createWeapon(string $serial, Client $client, User $responsible): Weapon
    {
        $weapon = Weapon::query()->create([
            'internal_code' => 'ARM-'.$serial,
            'serial_number' => $serial,
            'weapon_type' => 'Pistola',
            'caliber' => '9MM',
            'brand' => 'Test',
            'capacity' => '15',
            'ownership_type' => 'company_owned',
            'permit_type' => 'porte',
            'permit_expires_at' => now()->addYear(),
        ]);

        WeaponClientAssignment::query()->create([
            'weapon_id' => $weapon->id,
            'client_id' => $client->id,
            'responsible_user_id' => $responsible->id,
            'start_at' => now()->toDateString(),
            'is_active' => true,
            'assigned_by' => $responsible->id,
        ]);

        return $weapon;
    }

    private function createExpiredRenewalDocument(Weapon $weapon): WeaponDocument
    {
        return $this->createRenewalDocument($weapon, now()->subDays(10)->toDateString());
    }

    private function createRenewalDocument(Weapon $weapon, string $validUntil): WeaponDocument
    {
        $file = File::query()->create([
            'disk' => 'local',
            'path' => 'weapons/'.$weapon->id.'/documents/revalidacion.pdf',
            'original_name' => 'revalidacion.pdf',
            'mime_type' => 'application/pdf',
            'size' => 100,
        ]);

        return WeaponDocument::query()->create([
            'weapon_id' => $weapon->id,
            'file_id' => $file->id,
            'document_name' => 'Revalidación',
            'valid_until' => $validUntil,
            'status' => 'Sin novedad',
            'observations' => 'En Armerillo',
            'is_renewal' => true,
            'is_permit' => false,
        ]);
    }

    private function createIncident(
        Weapon $weapon,
        string $typeCode,
        string $status,
        User $admin,
        ?string $closureOutcome = null,
    ): WeaponIncident {
        $type = IncidentType::query()->where('code', $typeCode)->firstOrFail();

        return WeaponIncident::query()->create([
            'weapon_id' => $weapon->id,
            'incident_type_id' => $type->id,
            'status' => $status,
            'observation' => 'Caso de prueba',
            'event_at' => now()->subDay(),
            'reported_at' => now()->subDay(),
            'reported_by' => $admin->id,
            'resolved_at' => $status === WeaponIncident::STATUS_RESOLVED ? now() : null,
            'resolved_by' => $status === WeaponIncident::STATUS_RESOLVED ? $admin->id : null,
            'resolution_note' => $status === WeaponIncident::STATUS_RESOLVED ? 'Cierre de prueba' : null,
            'closure_outcome' => $status === WeaponIncident::STATUS_RESOLVED ? $closureOutcome : null,
        ]);
    }
}
