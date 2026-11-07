<?php

namespace Database\Seeders;

use App\Models\WeaponBrand;
use App\Models\WeaponCaliber;
use Illuminate\Database\Seeder;

class WeaponCatalogSeeder extends Seeder
{
    public function run(): void
    {
        $brands = [
            'Glock',
            'Beretta',
            'Sig Sauer',
            'Taurus',
            'Smith & Wesson',
            'CZ',
            'Heckler & Koch',
            'Walther',
            'Colt',
            'Remington',
            'Mossberg',
            'Benelli',
        ];

        $calibers = [
            '9 mm',
            '.380',
            '.40',
            '.45',
            '.38',
            '.32',
            '.22',
            '12',
            '16',
            '20',
            '5.56 mm',
        ];

        foreach ($brands as $index => $name) {
            WeaponBrand::query()->firstOrCreate(
                ['name' => $name],
                ['sort_order' => ($index + 1) * 10, 'is_active' => true]
            );
        }

        foreach ($calibers as $index => $name) {
            WeaponCaliber::query()->firstOrCreate(
                ['name' => $name],
                ['sort_order' => ($index + 1) * 10, 'is_active' => true]
            );
        }
    }
}
