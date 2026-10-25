<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use App\Services\OwnCompanyClientService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Installs only what the system needs to run: its own catalogs, the empty company record and the first administrator.
     */
    public function run(): void
    {
        $this->call([
            IncidentTypeSeeder::class,
            IncidentModalitySeeder::class,
            AdminUserSeeder::class,
        ]);

        app(OwnCompanyClientService::class)->sync(CompanySetting::firstOrCreateSingleton());
    }
}
