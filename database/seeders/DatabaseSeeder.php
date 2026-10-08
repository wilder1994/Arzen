<?php

namespace Database\Seeders;

use App\Models\CompanySetting;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Installs only what the system needs to run: its own catalogs, the empty company record and the first administrator.
     */
    public function run(): void
    {
        $this->call([
            ResponsibilityLevelSeeder::class,
            IncidentTypeSeeder::class,
            IncidentModalitySeeder::class,
            AdminUserSeeder::class,
        ]);

        CompanySetting::firstOrCreateSingleton();
    }
}
