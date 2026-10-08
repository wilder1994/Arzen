<?php

namespace App\Support;

use App\Models\CompanySetting;
use App\Models\Weapon;

class WeaponInternalCode
{
    public static function next(bool $lockForUpdate = false): string
    {
        $prefix = CompanySetting::current()->codePrefix();
        $escapedPrefix = addcslashes($prefix, '\\%_');

        $query = Weapon::query()->where('internal_code', 'like', $escapedPrefix.'%');
        if ($lockForUpdate) {
            $query->lockForUpdate();
        }

        $lastNumber = 0;
        foreach ($query->pluck('internal_code') as $code) {
            $suffix = substr((string) $code, strlen($prefix));
            if ($suffix !== '' && ctype_digit($suffix)) {
                $lastNumber = max($lastNumber, (int) $suffix);
            }
        }

        return sprintf('%s%04d', $prefix, $lastNumber + 1);
    }
}
