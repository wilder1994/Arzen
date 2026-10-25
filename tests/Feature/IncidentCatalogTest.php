<?php

namespace Tests\Feature;

use App\Models\IncidentModality;
use App\Models\IncidentType;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponIncident;
use App\Services\WeaponIncidentService;
use Database\Seeders\IncidentModalitySeeder;
use Database\Seeders\IncidentTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use InvalidArgumentException;
use Tests\TestCase;

class IncidentCatalogTest extends TestCase
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

    public function test_catalog_hides_custody_types_and_lists_dar_de_baja(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('catalogs.index', ['tab' => 'novedades']))
            ->assertOk()
            ->assertSee('Dar de Baja')
            ->assertSee('Hurtada')
            ->assertDontSee('En Mantenimiento')
            ->assertDontSee('Para Mantenimiento')
            ->assertDontSee('En Armerillo')
            ->assertDontSee('Horas de atención')
            ->assertDontSee('Exige adjunto')
            ->assertDontSee('Exige nota de cierre');
    }

    public function test_admin_creates_a_type_and_a_modality_used_by_reports(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('catalogs.incident-types.store'), [
                'kind' => 'type',
                'name' => 'Robo en vía',
                'color' => '#112233',
            ])
            ->assertRedirect(route('catalogs.index', ['tab' => 'novedades']));

        $type = IncidentType::query()->where('name', 'Robo en vía')->sole();
        $this->assertTrue($type->is_reportable);
        $this->assertTrue($type->is_active);
        $this->assertTrue($type->requires_resolution_note);
        $this->assertFalse($type->requires_attachment);

        $this->actingAs($admin)
            ->post(route('catalogs.incident-types.store'), [
                'kind' => 'modality',
                'incident_type_id' => $type->id,
                'name' => 'En desplazamiento',
            ])
            ->assertRedirect(route('catalogs.index', ['tab' => 'novedades']));

        $type->refresh();
        $this->assertTrue($type->requires_modality);
        $this->assertTrue(
            IncidentType::query()->catalog()->where('is_active', true)->where('is_reportable', true)->whereKey($type->id)->exists()
        );
        $this->assertTrue(
            IncidentModality::query()->where('incident_type_id', $type->id)->where('name', 'En desplazamiento')->where('is_active', true)->exists()
        );
    }

    public function test_used_type_and_modality_can_be_deactivated_but_not_deleted(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $type = IncidentType::query()->where('code', 'hurtada')->firstOrFail();
        $modality = $type->modalities()->firstOrFail();
        $weapon = Weapon::query()->create([
            'internal_code' => 'ARM-CAT-1',
            'serial_number' => 'SER-CAT-1',
            'weapon_type' => 'Pistola',
            'caliber' => '9MM',
            'brand' => 'Test',
            'ownership_type' => 'company_owned',
        ]);

        WeaponIncident::query()->create([
            'weapon_id' => $weapon->id,
            'incident_type_id' => $type->id,
            'incident_modality_id' => $modality->id,
            'status' => WeaponIncident::STATUS_OPEN,
            'observation' => 'Caso vigente',
            'event_at' => now(),
            'reported_at' => now(),
            'reported_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->delete(route('catalogs.incident-types.destroy', $type))
            ->assertSessionHas('error');
        $this->assertModelExists($type);

        $this->actingAs($admin)
            ->delete(route('catalogs.modalities.destroy', $modality))
            ->assertSessionHas('error');
        $this->assertModelExists($modality);

        $this->actingAs($admin)
            ->put(route('catalogs.incident-types.update', $type), [
                'name' => 'Hurtada',
                'color' => '#be123c',
                'is_active' => '0',
            ])
            ->assertRedirect(route('catalogs.index', ['tab' => 'novedades']));

        $this->assertFalse($type->refresh()->is_active);

        $this->actingAs($admin)
            ->post(route('weapon-incidents.store'), [
                'weapon_id' => $weapon->id,
                'incident_type_id' => $type->id,
                'incident_modality_id' => $modality->id,
                'observation' => 'No debe crearse',
                'event_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertSessionHasErrors('incident_type_id');
    }

    public function test_closing_always_requires_a_note(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $type = IncidentType::query()->where('code', 'en_mantenimiento')->firstOrFail();
        $this->assertFalse($type->requires_resolution_note);

        $weapon = Weapon::query()->create([
            'internal_code' => 'ARM-CAT-2',
            'serial_number' => 'SER-CAT-2',
            'weapon_type' => 'Pistola',
            'caliber' => '9MM',
            'brand' => 'Test',
            'ownership_type' => 'company_owned',
        ]);

        $incident = WeaponIncident::query()->create([
            'weapon_id' => $weapon->id,
            'incident_type_id' => $type->id,
            'status' => WeaponIncident::STATUS_OPEN,
            'observation' => 'Mantenimiento',
            'event_at' => now(),
            'reported_at' => now(),
            'reported_by' => $admin->id,
        ]);

        $this->expectException(InvalidArgumentException::class);

        app(WeaponIncidentService::class)->close($incident, [
            'status' => WeaponIncident::STATUS_RESOLVED,
            'closure_outcome' => WeaponIncident::OUTCOME_ADMINISTRATIVE_CLOSURE,
            'resolution_note' => '   ',
        ], $admin);
    }
}
