<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponClientAssignment;
use App\Models\WeaponIncident;
use App\Models\WeaponIncidentUpdate;
use Database\Seeders\IncidentModalitySeeder;
use Database\Seeders\IncidentTypeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeaponIncidentAuthorizationTest extends TestCase
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

    public function test_responsible_can_view_incident_dashboard_within_scope(): void
    {
        $responsible = User::factory()->create(['role' => 'RESPONSABLE']);
        [$weapon] = $this->createWeaponContext($responsible);

        $type = \App\Models\IncidentType::query()->where('code', 'perdida')->firstOrFail();

        WeaponIncident::create([
            'weapon_id' => $weapon->id,
            'incident_type_id' => $type->id,
            'status' => WeaponIncident::STATUS_OPEN,
            'observation' => 'Perdida en desplazamiento',
            'event_at' => now()->subHours(3),
            'reported_at' => now()->subHours(3),
            'reported_by' => $responsible->id,
        ]);

        $this->actingAs($responsible)
            ->get(route('reports.weapon-incidents.index'))
            ->assertOk()
            ->assertSee($weapon->serial_number)
            ->assertSee('Perdida en desplazamiento');
    }

    public function test_responsible_cannot_create_incident_from_module(): void
    {
        $responsible = User::factory()->create(['role' => 'RESPONSABLE']);
        [$weapon] = $this->createWeaponContext($responsible);
        $type = \App\Models\IncidentType::query()->where('code', 'perdida')->firstOrFail();

        $this->actingAs($responsible)
            ->post(route('weapon-incidents.store'), [
                'weapon_id' => $weapon->id,
                'incident_type_id' => $type->id,
                'status' => WeaponIncident::STATUS_OPEN,
                'observation' => 'Intento no autorizado',
                'event_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertForbidden();
    }

    public function test_responsible_cannot_add_update_or_reopen_incident(): void
    {
        $responsible = User::factory()->create(['role' => 'RESPONSABLE']);
        [$weapon] = $this->createWeaponContext($responsible);
        $type = \App\Models\IncidentType::query()->where('code', 'perdida')->firstOrFail();

        $incident = WeaponIncident::query()->create([
            'weapon_id' => $weapon->id,
            'incident_type_id' => $type->id,
            'status' => WeaponIncident::STATUS_RESOLVED,
            'observation' => 'Caso cerrado',
            'event_at' => now()->subHours(3),
            'reported_at' => now()->subHours(3),
            'reported_by' => $responsible->id,
        ]);

        $this->actingAs($responsible)
            ->post(route('weapon-incidents.updates.store', $incident), [
                'event_type' => WeaponIncidentUpdate::EVENT_NOTE,
                'note' => 'Intento no autorizado',
                'happened_at' => now()->format('Y-m-d H:i:s'),
            ])
            ->assertForbidden();

        $this->actingAs($responsible)
            ->patch(route('weapon-incidents.reopen', $incident), [
                'status' => WeaponIncident::STATUS_OPEN,
            ])
            ->assertForbidden();
    }

    public function test_responsible_weapon_search_is_limited_to_scope(): void
    {
        $responsible = User::factory()->create(['role' => 'RESPONSABLE']);
        [$visibleWeapon] = $this->createWeaponContext($responsible);

        $otherResponsible = User::factory()->create(['role' => 'RESPONSABLE']);
        $otherClient = Client::query()->create([
            'name' => 'Cliente Externo',
            'nit' => '900300400-9',
        ]);
        $otherResponsible->clients()->attach($otherClient->id);

        $hiddenWeapon = Weapon::query()->create([
            'internal_code' => 'ARM-3999',
            'serial_number' => 'SER-3999',
            'weapon_type' => 'Pistola',
            'caliber' => '9MM',
            'brand' => 'Bersa',
            'capacity' => '15',
            'ownership_type' => 'company_owned',
            'permit_type' => 'porte',
        ]);

        WeaponClientAssignment::query()->create([
            'weapon_id' => $hiddenWeapon->id,
            'client_id' => $otherClient->id,
            'responsible_user_id' => $otherResponsible->id,
            'start_at' => now()->toDateString(),
            'is_active' => true,
            'assigned_by' => $otherResponsible->id,
        ]);

        $this->actingAs($responsible)
            ->getJson(route('reports.weapon-incidents.weapons.search', ['q' => 'SER-']))
            ->assertOk()
            ->assertJsonFragment(['serial' => $visibleWeapon->serial_number])
            ->assertJsonMissing(['serial' => $hiddenWeapon->serial_number]);
    }

    private function createWeaponContext(User $responsible): array
    {
        $client = Client::query()->create([
            'name' => 'Cliente Alcance',
            'nit' => '900300400-1',
        ]);

        $responsible->clients()->attach($client->id);

        $weapon = Weapon::query()->create([
            'internal_code' => 'ARM-3001',
            'serial_number' => 'SER-3001',
            'weapon_type' => 'Pistola',
            'caliber' => '9MM',
            'brand' => 'Glock',
            'capacity' => '15',
            'ownership_type' => 'company_owned',
            'permit_type' => 'porte',
        ]);

        WeaponClientAssignment::query()->create([
            'weapon_id' => $weapon->id,
            'client_id' => $client->id,
            'responsible_user_id' => $responsible->id,
            'start_at' => now()->toDateString(),
            'is_active' => true,
            'assigned_by' => $responsible->id,
        ]);

        return [$weapon, $client];
    }
}
