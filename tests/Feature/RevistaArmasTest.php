<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\User;
use App\Models\Weapon;
use App\Models\WeaponClientAssignment;
use App\Support\Access\ModuleAccess;
use App\Support\RevistaWeaponPhotoSlots;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RevistaArmasTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    public function test_revista_lists_weapons_on_entry_and_uses_five_photo_slots(): void
    {
        [$responsible, $weapon] = $this->createResponsibleWithWeapon();
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('revista-armas.index'))
            ->assertOk()
            ->assertSee($weapon->serial_number)
            ->assertSee(__('Puesto o trabajador'))
            ->assertDontSee('revista_camera_modal')
            ->assertDontSee('Seleccionar archivo');

        $this->actingAs($responsible)
            ->get(route('revista-armas.index'))
            ->assertOk()
            ->assertSee($weapon->serial_number);

        $this->assertContains('impronta', RevistaWeaponPhotoSlots::keys());
        $this->assertSame(5, RevistaWeaponPhotoSlots::requiredCount());

        $this->actingAs($admin)
            ->getJson(route('revista-armas.photos', $weapon))
            ->assertOk()
            ->assertJsonFragment(['description' => 'impronta']);
    }

    public function test_field_user_is_created_in_users_and_second_assignment_shows_notice(): void
    {
        [$owner, $weapon] = $this->createResponsibleWithWeapon('REV-A');
        [$other, $otherWeapon] = $this->createResponsibleWithWeapon('REV-B');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->post(route('users.store'), [
            'account_kind' => User::KIND_FIELD,
            'email' => 'campo@arzen.test',
            'zone' => 'Norte',
            'field_owner_id' => $owner->id,
            'field_shared' => '1',
            'field_responsible_ids' => [$other->id],
            'field_day_start' => 1,
            'field_day_end' => 5,
            'is_active' => '1',
        ])->assertRedirect(route('users.index'));

        $fieldUser = User::query()->where('email', 'campo@arzen.test')->firstOrFail();
        $this->assertTrue($fieldUser->isField());
        $this->assertSame('Norte', $fieldUser->name);
        $this->assertNull($fieldUser->document);
        $this->assertFalse($fieldUser->isResponsible());
        $this->assertTrue($fieldUser->fieldSharedResponsibles()->whereKey($other->id)->exists());

        $this->actingAs($owner)->post(route('revista-armas.assign'), [
            'field_user_id' => $fieldUser->id,
            'weapon_ids' => [$weapon->id],
        ])->assertRedirect()->assertSessionMissing('revista_assignment_notice');

        $this->actingAs($other)->post(route('revista-armas.assign'), [
            'field_user_id' => $fieldUser->id,
            'weapon_ids' => [$otherWeapon->id],
        ])->assertRedirect()->assertSessionHas('revista_assignment_notice');

        $this->assertEqualsCanonicalizing(
            [$weapon->id, $otherWeapon->id],
            $fieldUser->fieldWeapons()->pluck('weapons.id')->all()
        );
    }

    public function test_a_single_field_user_is_visible_only_to_its_owner(): void
    {
        [$owner] = $this->createResponsibleWithWeapon('REV-C');
        [$other] = $this->createResponsibleWithWeapon('REV-D');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->post(route('users.store'), [
            'account_kind' => User::KIND_FIELD,
            'email' => 'turno@arzen.test',
            'zone' => 'Turno norte',
            'field_owner_id' => $owner->id,
            'field_day_start' => 1,
            'field_day_end' => 5,
            'is_active' => '1',
        ])->assertRedirect(route('users.index'));

        $this->actingAs($owner)->get(route('revista-armas.index'))->assertOk()->assertSee('turno@arzen.test');
        $this->actingAs($other)->get(route('revista-armas.index'))->assertOk()->assertDontSee('turno@arzen.test');
        $this->actingAs($admin)->get(route('revista-armas.index'))->assertOk()->assertSee('turno@arzen.test');
    }

    public function test_field_login_uses_password_and_the_monthly_window(): void
    {
        $fieldUser = User::factory()->create([
            'account_kind' => User::KIND_FIELD,
            'role' => User::ROLE_RESPONSABLE,
            'password' => 'campo-secreta',
            'field_day_start' => 1,
            'field_day_end' => 5,
            'must_change_password' => false,
        ]);

        $this->post(route('login'), [
            'email' => $fieldUser->email,
            'password' => 'campo-secreta',
        ])->assertSessionHasErrors('email');
        $this->assertGuest();

        $this->travelTo(now()->startOfMonth()->addDays(2));
        $this->post(route('revista-armas.guest.login.store'), [
            'email' => $fieldUser->email,
            'password' => 'campo-secreta',
        ])->assertRedirect(route('revista-armas.guest.weapons.index'));

        $this->get(route('revista-armas.guest.weapons.index'))
            ->assertOk()
            ->assertSee(__('Girar izquierda'))
            ->assertSee(__('Ajuste fino'))
            ->assertDontSee(__('Elegir de galería'))
            ->assertDontSee('Seleccionar archivo');

        $this->post(route('revista-armas.guest.logout'));
        $this->travelTo(now()->startOfMonth()->addDays(10));
        $this->post(route('revista-armas.guest.login.store'), [
            'email' => $fieldUser->email,
            'password' => 'campo-secreta',
        ])->assertSessionHasErrors('email');
    }

    /**
     * @return array{0: User, 1: Weapon}
     */
    private function createResponsibleWithWeapon(string $serial = 'REV-001'): array
    {
        $responsible = User::factory()->create([
            'role' => User::ROLE_RESPONSABLE,
            'account_kind' => User::KIND_PLATFORM,
        ]);
        $responsible->syncModuleLevels([
            'weapons' => ModuleAccess::MANAGE,
            'revista' => ModuleAccess::MANAGE,
        ]);

        $client = Client::create([
            'name' => 'Cliente '.$serial,
            'nit' => '900'.$serial,
        ]);

        $weapon = Weapon::create([
            'internal_code' => $serial,
            'serial_number' => $serial,
            'weapon_type' => 'Pistola',
            'caliber' => '9MM',
            'brand' => 'Glock',
            'capacity' => '15',
            'permit_type' => 'porte',
            'permit_number' => 'P-'.$serial,
            'permit_expires_at' => now()->addYear(),
        ]);

        WeaponClientAssignment::create([
            'weapon_id' => $weapon->id,
            'client_id' => $client->id,
            'responsible_user_id' => $responsible->id,
            'start_at' => now()->toDateString(),
            'is_active' => true,
            'assigned_by' => $responsible->id,
        ]);

        return [$responsible, $weapon];
    }
}
