<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Position;
use App\Models\User;
use App\Support\Access\ModuleAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_responsible_without_saved_matrix_uses_role_defaults(): void
    {
        $responsible = User::factory()->create(['role' => User::ROLE_RESPONSABLE]);

        $this->assertSame(ModuleAccess::MANAGE, $responsible->moduleLevel('weapons'));
        $this->assertSame(1, $responsible->responsibilityLevelNumber());
        $this->assertSame(ModuleAccess::NONE, $responsible->moduleLevel('users'));
    }

    public function test_view_level_allows_reading_but_blocks_changes(): void
    {
        $client = Client::query()->create(['name' => 'Cliente Matriz', 'nit' => '900777888-1', 'city' => 'CALI', 'department' => 'VALLE DEL CAUCA']);
        $responsible = User::factory()->create(['role' => User::ROLE_RESPONSABLE]);
        $responsible->clients()->attach($client->id);
        $responsible->syncModuleLevels(['weapons' => ModuleAccess::MANAGE, 'posts' => ModuleAccess::VIEW]);

        $this->actingAs($responsible)->get(route('posts.index'))->assertOk();
        $this->actingAs($responsible)->get(route('posts.create'))->assertForbidden();
        $this->actingAs($responsible)->post(route('posts.store'), [
            'installation_id' => $client->mainInstallation->id,
            'name' => 'Portería',
        ])->assertForbidden();
    }

    public function test_none_level_hides_the_module(): void
    {
        $auditor = User::factory()->create(['role' => User::ROLE_AUDITOR]);
        $auditor->syncModuleLevels(['weapons' => ModuleAccess::VIEW]);

        $this->actingAs($auditor)->get(route('reports.index'))->assertForbidden();
        $this->actingAs($auditor)->get(route('weapons.index'))->assertOk();
    }

    public function test_admin_saves_role_position_clients_and_matrix(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::query()->create(['name' => 'Cliente Cartera', 'nit' => '900111999-2', 'city' => 'CALI', 'department' => 'VALLE DEL CAUCA']);
        $position = Position::query()->create(['name' => 'Escolta']);

        $this->actingAs($admin)->post(route('users.store'), [
            'name' => 'Laura Responsable',
            'email' => 'laura@arzen.test',
            'role' => User::ROLE_RESPONSABLE,
            'position_id' => $position->id,
            'is_active' => '1',
            'client_ids' => [$client->id],
            'grants' => ['weapons' => 'view', 'clients' => 'manage', 'reports' => 'none'],
        ])->assertRedirect(route('users.index'));

        $user = User::query()->where('email', 'laura@arzen.test')->firstOrFail();
        $this->assertSame($position->id, $user->position_id);
        $this->assertTrue($user->hasClientInPortfolio($client->id));
        $this->assertSame(ModuleAccess::VIEW, $user->moduleLevel('weapons'));
        $this->assertSame(ModuleAccess::MANAGE, $user->moduleLevel('clients'));
        $this->assertSame(ModuleAccess::NONE, $user->moduleLevel('posts'));
        $this->assertTrue($user->isResponsibleReadOnly());
    }

    public function test_only_super_admin_can_create_super_admins(): void
    {
        $manager = User::factory()->create(['role' => User::ROLE_AUDITOR]);
        $manager->syncModuleLevels(['users' => ModuleAccess::MANAGE]);

        $this->actingAs($manager)->post(route('users.store'), [
            'name' => 'Intruso',
            'email' => 'intruso@arzen.test',
            'role' => User::ROLE_ADMIN,
            'is_active' => '1',
        ])->assertForbidden();

        $this->assertDatabaseMissing('users', ['email' => 'intruso@arzen.test']);
    }

    public function test_only_super_admin_assigns_weapons_to_clients(): void
    {
        $responsible = User::factory()->create(['role' => User::ROLE_RESPONSABLE]);
        $weapon = \App\Models\Weapon::query()->create([
            'internal_code' => 'ARM-9001',
            'serial_number' => 'SER-9001',
            'weapon_type' => 'Pistola',
            'caliber' => '9MM',
            'brand' => 'JERICHO',
            'capacity' => '15',
            'ownership_type' => 'company_owned',
            'permit_type' => 'porte',
        ]);

        $this->assertFalse($responsible->can('assignToClient', $weapon));
        $this->assertTrue(User::factory()->create(['role' => User::ROLE_ADMIN])->can('assignToClient', $weapon));
    }
}
