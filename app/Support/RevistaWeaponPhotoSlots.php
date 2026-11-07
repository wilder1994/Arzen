<?php

namespace App\Support;

use App\Models\WeaponPhoto;

class RevistaWeaponPhotoSlots
{
    /**
     * @return array<string, string>
     */
    public static function descriptions(): array
    {
        return WeaponPhoto::DESCRIPTIONS;
    }

    /**
     * @return list<string>
     */
    public static function keys(): array
    {
        return array_keys(WeaponPhoto::DESCRIPTIONS);
    }

    public static function requiredCount(): int
    {
        return count(WeaponPhoto::DESCRIPTIONS);
    }
}
