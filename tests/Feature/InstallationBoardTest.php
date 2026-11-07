<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\Client;
use App\Models\Installation;
use App\Models\Post;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponClientAssignment;
use App\Models\WeaponPostAssignment;
use App\Models\WeaponWorkerAssignment;
use App\Models\Worker;
use App\Support\PostCustodyRole;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InstallationBoardTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_list_opens_the_installation_instead_of_the_client(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::query()->create(['name' => 'Palmas', 'nit' => '900111222-3']);
        $installation = $client->mainInstallation()->firstOrFail();
        Post::query()->create([
            'client_id' => $client->id,
            'installation_id' => $installation->id,
            'name' => 'Portería',
        ]);

        $this->actingAs($admin)
            ->get(route('installations.index'))
            ->assertOk()
            ->assertSee('>Editar</a>', false)
            ->assertDontSee('Editar cliente');

        $this->actingAs($admin)
            ->get(route('installations.edit', $installation))
            ->assertOk()
            ->assertSee('Palmas')
            ->assertSee('Principal')
            ->assertSee('Puesto: Portería')
            ->assertSee('Agregar puesto')
            ->assertSee('Personas')
            ->assertSee('Armas')
            ->assertSee('Editar instalación')
            ->assertSee('Historial')
            ->assertDontSee('Ajustar datos')
            ->assertSee('Buscar trabajador por nombre o documento');
    }

    public function test_a_post_can_be_created_and_renamed_on_its_installation(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::query()->create(['name' => 'Palmas', 'nit' => '900111222-3']);
        $installation = $client->mainInstallation()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('installations.posts.store', $installation), ['post_name' => 'Garita'])
            ->assertRedirect();

        $post = Post::query()->where('name', 'Garita')->firstOrFail();
        $this->assertSame($installation->id, $post->installation_id);

        $this->actingAs($admin)
            ->patch(route('installations.posts.update', [$installation, $post]), ['name' => 'Garita norte'])
            ->assertRedirect();

        $this->assertSame('Garita norte', $post->fresh()->name);
    }

    public function test_assigning_a_worker_sets_the_post_and_the_installation_client(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::query()->create(['name' => 'Palmas', 'nit' => '900111222-3']);
        $other = Client::query()->create(['name' => 'Norte', 'nit' => '900111333-4']);
        $installation = $client->mainInstallation()->firstOrFail();
        $post = Post::query()->create([
            'client_id' => $client->id,
            'installation_id' => $installation->id,
            'name' => 'Portería',
        ]);
        $otherPost = Post::query()->create([
            'client_id' => $other->id,
            'installation_id' => $other->mainInstallation()->firstOrFail()->id,
            'name' => 'Caseta',
        ]);
        $free = Worker::query()->create(['name' => 'Luis Gómez', 'document' => '1098001']);
        $placed = Worker::query()->create([
            'name' => 'Ana Pérez',
            'document' => '1098002',
            'client_id' => $other->id,
            'post_id' => $otherPost->id,
        ]);

        $this->actingAs($admin)
            ->post(route('installations.posts.workers.store', [$installation, $post]), ['worker_id' => $free->id])
            ->assertRedirect();

        $free->refresh();
        $this->assertSame($post->id, $free->post_id);
        $this->assertSame($client->id, $free->client_id);

        $this->actingAs($admin)
            ->post(route('installations.posts.workers.store', [$installation, $post]), ['worker_id' => $placed->id])
            ->assertSessionHasErrors('worker');

        $this->assertSame($otherPost->id, $placed->fresh()->post_id);

        $this->actingAs($admin)
            ->post(route('installations.posts.workers.store', [$installation, $post]), [
                'worker_id' => $placed->id,
                'confirm' => 1,
            ])
            ->assertRedirect();

        $placed->refresh();
        $this->assertSame($post->id, $placed->post_id);
        $this->assertSame($client->id, $placed->client_id);

        $this->actingAs($admin)
            ->delete(route('installations.posts.workers.destroy', [$installation, $post, $placed]))
            ->assertRedirect();

        $placed->refresh();
        $this->assertNull($placed->post_id);
        $this->assertSame($client->id, $placed->client_id);
    }

    public function test_a_weapon_moves_to_the_post_and_keeps_its_responsible(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::query()->create(['name' => 'Palmas', 'nit' => '900111222-3', 'latitude' => 3.45, 'longitude' => -76.53]);
        $installation = $client->mainInstallation()->firstOrFail();
        $installation->update(['latitude' => 3.45, 'longitude' => -76.53]);
        $post = Post::query()->create([
            'client_id' => $client->id,
            'installation_id' => $installation->id,
            'name' => 'Portería',
        ]);
        $other = Post::query()->create([
            'client_id' => $client->id,
            'installation_id' => $installation->id,
            'name' => 'Garita',
        ]);
        $worker = Worker::query()->create(['name' => 'Ana Pérez', 'client_id' => $client->id, 'post_id' => $other->id]);
        $weapon = Weapon::query()->create([
            'internal_code' => 'ARM-100',
            'serial_number' => 'SER-100',
            'weapon_type' => 'Pistola',
            'caliber' => '9mm',
            'brand' => 'Test',
            'capacity' => '15',
            'ownership_type' => 'company_owned',
            'permit_type' => 'porte',
            'permit_number' => 'PERM-9',
        ]);
        WeaponClientAssignment::query()->create([
            'weapon_id' => $weapon->id,
            'client_id' => $client->id,
            'responsible_user_id' => $admin->id,
            'start_at' => now()->toDateString(),
            'is_active' => true,
            'assigned_by' => $admin->id,
        ]);
        WeaponPostAssignment::query()->create([
            'weapon_id' => $weapon->id,
            'post_id' => $other->id,
            'start_at' => now()->toDateString(),
            'is_active' => true,
            'assigned_by' => $admin->id,
        ]);
        WeaponWorkerAssignment::query()->create([
            'weapon_id' => $weapon->id,
            'worker_id' => $worker->id,
            'start_at' => now()->toDateString(),
            'is_active' => true,
            'assigned_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('installations.posts.weapons.store', [$installation, $post]), ['weapon_id' => $weapon->id])
            ->assertSessionHasErrors('weapon');

        $this->assertSame($other->id, $weapon->activePostAssignment()->first()->post_id);

        $this->actingAs($admin)
            ->post(route('installations.posts.weapons.store', [$installation, $post]), [
                'weapon_id' => $weapon->id,
                'confirm' => 1,
            ])
            ->assertRedirect();

        $this->assertSame($post->id, $weapon->activePostAssignment()->first()->post_id);
        $this->assertSame($worker->id, $weapon->activeWorkerAssignment()->first()->worker_id);

        $this->actingAs($admin)
            ->getJson(route('installations.weapons.search', $installation).'?q=SER&post='.$post->id)
            ->assertOk()
            ->assertJsonCount(0, 'weapons');
    }

    public function test_the_principal_installation_is_edited_on_the_client_and_returns(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::query()->create([
            'name' => 'Palmas',
            'nit' => '900111222-3',
            'city' => 'Cali',
            'department' => 'Valle del Cauca',
        ]);
        $installation = $client->mainInstallation()->firstOrFail();
        Installation::query()->create([
            'client_id' => $client->id,
            'name' => 'Bodega',
            'is_client_site' => false,
            'city' => 'Cali',
            'department' => 'Valle del Cauca',
        ]);

        $this->actingAs($admin)
            ->get(route('installations.form', $installation))
            ->assertRedirect(route('clients.edit', [$client, 'return_installation' => $installation->id]));

        $this->actingAs($admin)
            ->get(route('clients.edit', [$client, 'return_installation' => $installation->id]))
            ->assertOk()
            ->assertSee('Nota del cambio');

        $bodega = Installation::query()->where('name', 'Bodega')->firstOrFail();
        $this->actingAs($admin)
            ->get(route('installations.form', $bodega))
            ->assertOk()
            ->assertSee('Nota del cambio')
            ->assertDontSee('Ajustar datos');

        $this->actingAs($admin)
            ->put(route('installations.update', $bodega), [
                'name' => 'Bodega norte',
                'city' => 'Cali',
                'department' => 'Valle del Cauca',
                'change_note' => 'Se corrigió el nombre de la sede.',
            ])
            ->assertRedirect(route('installations.edit', $bodega));

        $this->assertSame('Se corrigió el nombre de la sede.', AuditLog::query()->where('auditable_id', $bodega->id)->where('action', 'installation_updated')->first()->after['note']);

        $this->actingAs($admin)
            ->put(route('clients.update', $client), [
                'party_type' => 'legal_entity',
                'name' => 'Palmas del Ingenio',
                'document_type' => 'NIT',
                'nit' => '900111222-3',
                'city' => 'Cali',
                'department' => 'Valle del Cauca',
                'return_installation' => $installation->id,
                'change_note' => 'Nombre comercial ajustado.',
            ])
            ->assertRedirect(route('installations.edit', $installation));

        $this->assertSame('Palmas del Ingenio', $installation->fresh()->name);
        $this->assertSame('Nombre comercial ajustado.', AuditLog::query()->where('auditable_id', $installation->id)->latest('id')->first()->after['note']);
    }

    public function test_post_movements_stay_on_the_installation_history_and_archive_releases_the_post(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::query()->create([
            'name' => 'Palmas',
            'nit' => '900111222-3',
            'latitude' => 3.45,
            'longitude' => -76.53,
        ]);
        $installation = $client->mainInstallation()->firstOrFail();
        $installation->update(['latitude' => 3.45, 'longitude' => -76.53]);
        $worker = Worker::query()->create(['name' => 'Luis Gómez', 'document' => '1098001', 'client_id' => $client->id]);
        $weapon = Weapon::query()->create([
            'internal_code' => 'ARM-200',
            'serial_number' => 'SER-200',
            'weapon_type' => 'Pistola',
            'caliber' => '9mm',
            'brand' => 'Test',
            'capacity' => '15',
            'ownership_type' => 'company_owned',
            'permit_type' => 'porte',
            'permit_number' => 'PERM-200',
        ]);
        WeaponClientAssignment::query()->create([
            'weapon_id' => $weapon->id,
            'client_id' => $client->id,
            'responsible_user_id' => $admin->id,
            'start_at' => now()->toDateString(),
            'is_active' => true,
            'assigned_by' => $admin->id,
        ]);
        WeaponWorkerAssignment::query()->create([
            'weapon_id' => $weapon->id,
            'worker_id' => $worker->id,
            'start_at' => now()->toDateString(),
            'is_active' => true,
            'assigned_by' => $admin->id,
        ]);

        $this->actingAs($admin)
            ->post(route('installations.posts.store', $installation), ['post_name' => 'Garita'])
            ->assertRedirect();

        $post = Post::query()->where('name', 'Garita')->firstOrFail();
        $this->assertSame('Puesto creado: Garita.', AuditLog::query()->where('auditable_id', $installation->id)->where('action', 'installation_post_movement')->latest('id')->first()->after['note']);

        $this->actingAs($admin)
            ->post(route('installations.posts.workers.store', [$installation, $post]), ['worker_id' => $worker->id])
            ->assertRedirect();

        $this->actingAs($admin)
            ->post(route('installations.posts.weapons.store', [$installation, $post]), ['weapon_id' => $weapon->id])
            ->assertRedirect();

        $this->actingAs($admin)
            ->delete(route('posts.destroy', $post), ['return' => 'installation'])
            ->assertRedirect(route('installations.edit', $installation).'#puesto-'.$post->id);

        $worker->refresh();
        $this->assertNull($worker->post_id);
        $this->assertSame($client->id, $worker->client_id);
        $this->assertNotNull($post->fresh()->archived_at);
        $this->assertNull(WeaponPostAssignment::query()->where('weapon_id', $weapon->id)->where('is_active', true)->first());
        $this->assertNotNull($weapon->fresh()->activeWorkerAssignment);

        $this->actingAs($admin)
            ->get(route('installations.edit', $installation))
            ->assertOk()
            ->assertSee('Puesto: Garita')
            ->assertSee('Archivado')
            ->assertSee('Reactivar')
            ->assertSee('Puesto archivado: Garita. Las personas y las armas quedaron sin puesto.')
            ->assertSee('Persona asignada al puesto Garita: Luis Gómez.')
            ->assertSee('Arma asignada al puesto Garita: Pistola · SER-200 · 9mm.');

        $site = Installation::query()->create([
            'client_id' => $client->id,
            'name' => 'Bodega',
            'is_client_site' => false,
            'city' => 'Cali',
            'department' => 'Valle del Cauca',
        ]);
        Post::query()->create([
            'client_id' => $client->id,
            'installation_id' => $site->id,
            'name' => 'Andén',
        ]);

        $this->actingAs($admin)
            ->patch(route('installations.archive', $site))
            ->assertSessionHasErrors('installation');

        $site->posts()->update(['archived_at' => now()]);

        $this->actingAs($admin)
            ->patch(route('installations.archive', $site))
            ->assertRedirect();

        $this->actingAs($admin)
            ->get(route('installations.index'))
            ->assertOk()
            ->assertSee('Bodega')
            ->assertSee('sj-archived-row', false)
            ->assertSee('Reactivar');
    }

    public function test_an_armerillo_is_a_safe_on_the_installation_and_not_a_field_post(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);
        $client = Client::query()->create(['name' => 'Palmas', 'nit' => '900111222-3']);
        $installation = $client->mainInstallation()->firstOrFail();

        $this->actingAs($admin)
            ->post(route('installations.armerillos.store', $installation), [
                'post_name' => 'Caja norte',
                'armerillo' => '1',
            ])
            ->assertRedirect();

        $post = Post::query()->where('name', 'Caja norte')->firstOrFail();
        $this->assertSame(PostCustodyRole::ARMERILLO, $post->custody_role);
        $this->assertSame($installation->id, $post->installation_id);

        $this->actingAs($admin)
            ->get(route('installations.edit', $installation))
            ->assertOk()
            ->assertSee('Caja norte')
            ->assertSee('Clic para abrir')
            ->assertDontSee('Armerillo: Caja norte')
            ->assertSee('Listas para asignar')
            ->assertDontSee('Puesto: Caja norte');
    }
}
