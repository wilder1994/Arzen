<?php

namespace Tests\Feature;

use App\Models\CompanySetting;
use App\Models\IncidentType;
use App\Models\Position;
use App\Models\User;
use App\Models\Weapon;
use App\Services\CompanyLetterheadService;
use App\Support\WeaponInternalCode;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;
use ZipArchive;

class CompanySettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_installation_seeds_only_the_administrator_and_system_catalogs(): void
    {
        $this->seed(DatabaseSeeder::class);

        $admin = User::query()->sole();
        $this->assertSame('admin@arzen.com', $admin->email);
        $this->assertTrue($admin->isAdmin());
        $this->assertTrue($admin->must_change_password);
        $this->assertTrue(Hash::check('ArzenAdmin.2026', $admin->password));

        $this->assertSame(0, Position::query()->count());
        $this->assertTrue(IncidentType::query()->where('code', 'hurtada')->exists());
        $this->assertSame(1, CompanySetting::query()->count());
    }

    public function test_only_admins_can_open_company_settings(): void
    {
        $responsible = User::factory()->create(['role' => User::ROLE_RESPONSABLE]);

        $this->actingAs($responsible)->get(route('company.edit'))->assertForbidden();
        $this->actingAs($responsible)->get(route('catalogs.index'))->assertForbidden();
    }

    public function test_admin_updates_company_data_logo_and_letterhead(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)->get(route('company.edit'))->assertOk()->assertSee('Datos de la empresa');

        $this->actingAs($admin)
            ->put(route('company.update'), [
                'legal_name' => 'Acme Seguridad LTDA',
                'nit' => '900.000.000-1',
                'city' => 'Bogotá',
                'legal_rep_name' => 'Ana Pérez',
                'legal_rep_document' => '1.000.000',
                'legal_rep_document_city' => 'Bogotá',
                'internal_code_prefix' => 'acme-',
                'logo' => UploadedFile::fake()->image('logo.png', 300, 120),
                'letterhead' => new UploadedFile($this->letterheadDocx(), 'membrete.docx', null, null, true),
            ])
            ->assertRedirect(route('company.edit'));

        $company = CompanySetting::current();
        $this->assertSame('Acme Seguridad LTDA', $company->legal_name);
        $this->assertSame('ACME-', $company->internal_code_prefix);
        $this->assertSame([], $company->missingLetterFields());
        $this->assertNotNull($company->logoFile);

        $images = app(CompanyLetterheadService::class)->imagesFor($company);
        $this->assertNotNull($images['header']);
        $this->assertNotNull($images['footer']);

        $this->get(route('company.logo'))->assertOk();
    }

    public function test_letterhead_without_header_image_is_rejected(): void
    {
        Storage::fake('local');
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->put(route('company.update'), [
                'letterhead' => new UploadedFile($this->letterheadDocx(withImages: false), 'vacio.docx', null, null, true),
            ])
            ->assertSessionHasErrors('letterhead');
    }

    public function test_internal_codes_use_the_company_prefix(): void
    {
        CompanySetting::query()->create(['internal_code_prefix' => 'ACME-']);
        foreach (['ACME-0009' => 'S-1', 'OTRO-0050' => 'S-2'] as $code => $serial) {
            Weapon::create([
                'internal_code' => $code,
                'serial_number' => $serial,
                'weapon_type' => 'Revólver',
                'caliber' => '38L',
                'brand' => 'LLAMA',
                'ownership_type' => 'company_owned',
            ]);
        }

        $this->assertSame('ACME-0010', WeaponInternalCode::next());
    }

    public function test_admin_manages_positions_and_cannot_delete_assigned_ones(): void
    {
        $admin = User::factory()->create(['role' => User::ROLE_ADMIN]);

        $this->actingAs($admin)
            ->post(route('catalogs.positions.store'), ['name' => 'Supervisor'])
            ->assertRedirect(route('catalogs.index', ['tab' => 'cargos']));

        $position = Position::query()->where('name', 'Supervisor')->sole();
        $admin->update(['position_id' => $position->id]);

        $this->actingAs($admin)
            ->delete(route('catalogs.positions.destroy', $position))
            ->assertSessionHas('error');

        $this->assertModelExists($position);
    }

    private function letterheadDocx(bool $withImages = true): string
    {
        $path = tempnam(sys_get_temp_dir(), 'letterhead').'.docx';
        $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/xcAAn8B9x2XWAAAAABJRU5ErkJggg==');

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"/>');
        $zip->addFromString('word/document.xml', '<?xml version="1.0"?><w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main"/>');

        if ($withImages) {
            $zip->addFromString('word/media/image1.png', $png);
            $zip->addFromString('word/media/image2.png', $png);
            $zip->addFromString('word/_rels/header1.xml.rels', '<Relationships><Relationship Id="rId1" Target="media/image1.png"/></Relationships>');
            $zip->addFromString('word/_rels/footer1.xml.rels', '<Relationships><Relationship Id="rId1" Target="media/image2.png"/></Relationships>');
        }

        $zip->close();

        return $path;
    }
}
