<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Installation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClientListTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_client_list_puts_installations_in_their_own_column(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::query()->create([
            'name' => 'Palmas',
            'nit' => '900111222-3',
            'city' => 'Cali',
            'department' => 'Valle del Cauca',
        ]);
        Installation::query()->create([
            'client_id' => $client->id,
            'name' => 'Bodega',
            'is_client_site' => false,
            'city' => 'Cali',
            'department' => 'Valle del Cauca',
        ]);

        $this->actingAs($admin)
            ->get(route('clients.index'))
            ->assertOk()
            ->assertSee('Instalaciones')
            ->assertSee('2 instalaciones')
            ->assertDontSee('Eliminar');

        $only = Client::query()->create([
            'name' => 'Norte',
            'nit' => '900111333-4',
            'city' => 'Cali',
            'department' => 'Valle del Cauca',
        ]);

        $this->actingAs($admin)
            ->get(route('clients.index'))
            ->assertOk()
            ->assertSee('1 misma ubicación');

        $this->actingAs($admin)
            ->delete(route('clients.destroy', $client))
            ->assertSessionHasErrors('client');

        $this->assertNull($client->fresh()->archived_at);

        $this->actingAs($admin)
            ->delete(route('clients.destroy', $only))
            ->assertRedirect(route('clients.index'));

        $this->assertNotNull($only->fresh()->archived_at);

        $this->actingAs($admin)
            ->get(route('clients.index'))
            ->assertOk()
            ->assertSee('sj-archived-row', false)
            ->assertSee('Reactivar')
            ->assertSee('Norte');

        $this->actingAs($admin)
            ->patch(route('clients.restore', $only))
            ->assertRedirect(route('clients.index'));

        $this->assertNull($only->fresh()->archived_at);
    }

    public function test_the_installation_list_leads_with_the_installation(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        Client::query()->create([
            'name' => 'Palmas',
            'nit' => '900111222-3',
            'city' => 'Cali',
            'department' => 'Valle del Cauca',
        ]);

        $html = $this->actingAs($admin)
            ->get(route('installations.index'))
            ->assertOk()
            ->getContent();

        $this->assertLessThan(
            strpos($html, '>Cliente<'),
            strpos($html, '>Instalación<')
        );
    }
}
