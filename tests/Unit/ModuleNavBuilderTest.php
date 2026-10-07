<?php

namespace Tests\Unit;

use App\Models\ResponsibilityLevel;
use App\Models\User;
use App\Support\Navigation\ModuleNavBuilder;
use Database\Seeders\ResponsibilityLevelSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class ModuleNavBuilderTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ResponsibilityLevelSeeder::class);
        $this->app->instance('request', Request::create('/dashboard', 'GET'));
        $this->app['request']->setRouteResolver(function () {
            $route = new \Illuminate\Routing\Route('GET', '/dashboard', fn () => null);
            $route->name('dashboard');

            return $route;
        });
    }

    public function test_admin_sees_the_three_modules_and_admin_only_items(): void
    {
        $nav = $this->builder()->forUser($this->user(User::ROLE_ADMIN));
        $keys = $this->itemKeys($nav);

        $this->assertSame(['armamento', 'dotacion', 'plataforma'], $this->moduleKeys($nav));
        $this->assertContains('usuarios', $keys);
        $this->assertContains('asignaciones', $keys);
        $this->assertContains('cargas', $keys);
        $this->assertContains('formatos', $keys);
        $this->assertContains('mapa', $keys);
        $this->assertNotContains('patrulla', $keys);
        $this->assertNotContains('subir-armas', $keys);
    }

    public function test_responsible_keeps_authorized_items_and_hides_admin_tools(): void
    {
        $levelTwo = ResponsibilityLevel::query()->where('level', 2)->firstOrFail();
        $nav = $this->builder()->forUser($this->user(User::ROLE_RESPONSABLE, $levelTwo->id));
        $keys = $this->itemKeys($nav);

        $this->assertContains('inicio', $keys);
        $this->assertContains('armas', $keys);
        $this->assertContains('formatos', $keys);
        $this->assertContains('mapa', $keys);
        $this->assertNotContains('usuarios', $keys);
        $this->assertNotContains('asignaciones', $keys);
        $this->assertNotContains('cargas', $keys);
        $this->assertNotContains('revista', $keys);
        $this->assertNotContains('reportes', $keys);
    }

    public function test_responsible_level_one_sees_revista_and_vest_import(): void
    {
        $levelOne = ResponsibilityLevel::query()->where('level', 1)->firstOrFail();
        $nav = $this->builder()->forUser($this->user(User::ROLE_RESPONSABLE, $levelOne->id));
        $keys = $this->itemKeys($nav);

        $this->assertContains('revista', $keys);
        $this->assertContains('cargas', $keys);
        $this->assertContains('formatos', $keys);
        $this->assertNotContains('usuarios', $keys);
    }

    public function test_almacen_only_sees_dotacion_and_vest_platform_tools(): void
    {
        $nav = $this->builder()->forUser($this->user(User::ROLE_ALMACEN));
        $keys = $this->itemKeys($nav);

        $this->assertSame(['dotacion', 'plataforma'], $this->moduleKeys($nav));
        $this->assertContains('chalecos', $keys);
        $this->assertContains('cargas', $keys);
        $this->assertNotContains('formatos', $keys);
        $this->assertNotContains('inicio', $keys);
        $this->assertNotContains('armas', $keys);
        $this->assertNotContains('usuarios', $keys);
        $this->assertNotContains('mapa', $keys);
    }

    private function builder(): ModuleNavBuilder
    {
        return new ModuleNavBuilder();
    }

    private function user(string $role, ?int $responsibilityLevelId = null): User
    {
        return User::factory()->create([
            'role' => $role,
            'responsibility_level_id' => $responsibilityLevelId,
        ]);
    }

    /**
     * @param  array<string, mixed>  $nav
     * @return list<string>
     */
    private function moduleKeys(array $nav): array
    {
        return collect($nav['modules'])->pluck('key')->all();
    }

    /**
     * @param  array<string, mixed>  $nav
     * @return list<string>
     */
    private function itemKeys(array $nav): array
    {
        $keys = [];

        foreach ($nav['modules'] as $module) {
            foreach ($module['items'] as $item) {
                $keys[] = $item['key'];
                foreach ($item['children'] ?? [] as $child) {
                    $keys[] = $child['key'];
                }
            }
        }

        return $keys;
    }
}
