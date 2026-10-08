<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponHistory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeaponHistoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_client_assignment_records_destination_with_reason(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $client = Client::query()->create([
            'name' => 'Cliente Historial',
            'nit' => '900200300-1',
        ]);
        $admin->clients()->attach($client->id);

        $weapon = Weapon::query()->create([
            'internal_code' => 'ARM-HIST-1',
            'serial_number' => 'SER-HIST-1',
            'weapon_type' => 'Pistola',
            'caliber' => '9MM',
            'brand' => 'Test',
            'capacity' => '15',
            'ownership_type' => 'company_owned',
            'permit_type' => 'porte',
        ]);

        $this->actingAs($admin)
            ->post(route('weapons.client_assignments.store', $weapon), [
                'client_id' => $client->id,
                'reason' => 'Entrega en custodia operativa',
            ])
            ->assertRedirect(route('weapons.show', $weapon));

        $entry = WeaponHistory::query()->where('weapon_id', $weapon->id)->first();
        $this->assertNotNull($entry);
        $this->assertSame(WeaponHistory::KIND_DESTINATION, $entry->kind);
        $this->assertStringContainsString('Cliente Historial', $entry->body);
        $this->assertStringContainsString('Entrega en custodia operativa', $entry->body);
    }

    public function test_weapon_update_records_field_diff_and_textarea_notes(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $weapon = Weapon::query()->create([
            'internal_code' => 'ARM-HIST-2',
            'serial_number' => 'SER-HIST-2',
            'weapon_type' => 'Pistola',
            'caliber' => '9MM',
            'brand' => 'Marca Vieja',
            'capacity' => '15',
            'ownership_type' => 'company_owned',
            'permit_type' => 'porte',
            'notes' => 'Nota base',
        ]);

        $this->actingAs($admin)
            ->put(route('weapons.update', $weapon), [
                'internal_code' => 'ARM-HIST-2',
                'serial_number' => 'SER-HIST-2',
                'weapon_type' => 'Pistola',
                'caliber' => '9MM',
                'brand' => 'Marca Nueva',
                'capacity' => '15',
                'ownership_type' => 'company_owned',
                'ownership_entity' => null,
                'permit_type' => 'porte',
                'permit_number' => null,
                'permit_expires_at' => null,
                'notes' => 'Nota base',
            ])
            ->assertRedirect(route('weapons.show', $weapon));

        $entry = WeaponHistory::query()
            ->where('weapon_id', $weapon->id)
            ->where('kind', WeaponHistory::KIND_UPDATE)
            ->first();

        $this->assertNotNull($entry);
        $this->assertStringContainsString('Marca Vieja', $entry->body);
        $this->assertStringContainsString('Marca Nueva', $entry->body);
        $this->assertStringContainsString('Nota base', $entry->body);
    }

    public function test_weapon_show_displays_history_entries(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);
        $weapon = Weapon::query()->create([
            'internal_code' => 'ARM-HIST-3',
            'serial_number' => 'SER-HIST-3',
            'weapon_type' => 'Pistola',
            'caliber' => '9MM',
            'brand' => 'Test',
            'capacity' => '15',
            'ownership_type' => 'company_owned',
            'permit_type' => 'porte',
        ]);

        WeaponHistory::query()->create([
            'weapon_id' => $weapon->id,
            'user_id' => $admin->id,
            'kind' => WeaponHistory::KIND_NOTE,
            'body' => 'Entrada visible en ficha',
        ]);

        $this->actingAs($admin)
            ->get(route('weapons.show', $weapon))
            ->assertOk()
            ->assertSee('Entrada visible en ficha')
            ->assertSee(__('Nota'));
    }

    public function test_weapon_create_records_initial_history(): void
    {
        $admin = User::factory()->create(['role' => 'ADMIN']);

        $this->actingAs($admin)
            ->post(route('weapons.store'), [
                'serial_number' => 'SER-HIST-NEW',
                'weapon_type' => 'Pistola',
                'caliber' => '9MM',
                'brand' => 'Nueva',
                'capacity' => '15',
                'ownership_type' => 'company_owned',
                'permit_type' => 'porte',
                'notes' => 'Alta con observación inicial',
                'permit_photo' => \Illuminate\Http\UploadedFile::fake()->image('permiso.jpg'),
            ]);

        $weapon = Weapon::query()->where('serial_number', 'SER-HIST-NEW')->firstOrFail();

        $this->assertDatabaseHas('weapon_histories', [
            'weapon_id' => $weapon->id,
            'kind' => WeaponHistory::KIND_CREATED,
        ]);

        $entry = WeaponHistory::query()->where('weapon_id', $weapon->id)->first();
        $this->assertStringContainsString('Alta con observación inicial', $entry->body);
    }
}
