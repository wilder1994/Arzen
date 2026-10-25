<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\Vest;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlmacenRoleTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_almacen_user_is_redirected_to_vests_after_login(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_ALMACEN,
            'must_change_password' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect('/vests');
    }

    public function test_almacen_can_access_vest_index(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ALMACEN]);

        $response = $this->actingAs($user)->get(route('vests.index'));

        $response->assertOk();
    }

    public function test_almacen_can_access_vest_import_center(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ALMACEN]);

        $response = $this->actingAs($user)->get(route('vest-imports.index'));

        $response->assertOk();
    }

    public function test_almacen_is_redirected_away_from_dashboard(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ALMACEN]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertRedirect(route('vests.index'));
    }

    public function test_almacen_is_redirected_away_from_weapons(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ALMACEN]);

        $response = $this->actingAs($user)->get(route('weapons.index'));

        $response->assertRedirect(route('vests.index'));
    }

    public function test_almacen_cannot_access_user_management(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ALMACEN]);

        $response = $this->actingAs($user)->get(route('users.index'));

        $response->assertRedirect(route('vests.index'));
    }

    public function test_almacen_sees_all_clients_in_vest_scope(): void
    {
        $user = User::factory()->create(['role' => User::ROLE_ALMACEN]);
        $clientA = Client::query()->create(['name' => 'Cliente A', 'nit' => '900100201-1']);
        $clientB = Client::query()->create(['name' => 'Cliente B', 'nit' => '900100202-1']);

        Vest::query()->create([
            'client_id' => $clientA->id,
            'serial_number' => 'VEST-A-001',
        ]);
        Vest::query()->create([
            'client_id' => $clientB->id,
            'serial_number' => 'VEST-B-001',
        ]);

        $response = $this->actingAs($user)->get(route('vests.index'));

        $response->assertOk();
        $response->assertSee('VEST-A-001');
        $response->assertSee('VEST-B-001');
    }

    public function test_admin_can_create_almacen_user(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $response = $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Personal Almacén',
            'email' => 'almacen@example.com',
            'role' => User::ROLE_ALMACEN,
            'is_active' => true,
        ]);

        $response->assertRedirect(route('users.index'));
        $this->assertDatabaseHas('users', [
            'email' => 'almacen@example.com',
            'role' => User::ROLE_ALMACEN,
        ]);
    }

    public function test_home_for_almacen_returns_vests_path(): void
    {
        $user = User::factory()->make(['role' => User::ROLE_ALMACEN]);

        $this->assertSame('/vests', RouteServiceProvider::homeFor($user));
    }

    public function test_responsible_still_redirects_to_dashboard_after_login(): void
    {
        $user = User::factory()->create([
            'role' => User::ROLE_RESPONSABLE,
            'must_change_password' => false,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(RouteServiceProvider::HOME);
    }
}
