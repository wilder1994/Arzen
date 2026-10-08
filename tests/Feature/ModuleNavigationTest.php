<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleNavigationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_renders_module_sidebar(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Armamento')
            ->assertSee('Dotación')
            ->assertDontSee('Supervisión')
            ->assertSee('Plataforma')
            ->assertSee('Mi empresa')
            ->assertSee('Mapa')
            ->assertDontSee('Cargas masivas')
            ->assertDontSee('Subir armas');
    }

    public function test_responsible_does_not_see_admin_import_links(): void
    {
        $responsible = User::factory()->create(['role' => User::ROLE_RESPONSABLE]);

        $this->actingAs($responsible)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Armamento')
            ->assertSee('Mapa')
            ->assertDontSee('Subir armas')
            ->assertDontSee('Mi empresa')
            ->assertDontSee('Usuarios');
    }
}
