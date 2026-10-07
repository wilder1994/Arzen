<?php

namespace App\Support;

final class MapCoordinates
{
    /**
     * Whether latitude and longitude are set and numeric (map can place a marker).
     */
    public static function isFilled(mixed $latitude, mixed $longitude): bool
    {
        if ($latitude === null || $longitude === null) {
            return false;
        }

        $lat = trim((string) $latitude);
        $lng = trim((string) $longitude);

        if ($lat === '' || $lng === '') {
            return false;
        }

        return is_numeric($lat) && is_numeric($lng);
    }

    /**
     * Keep coordinates already resolved in the browser and drop the form flag.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function apply(array $data): array
    {
        if (self::isFilled($data['latitude'] ?? null, $data['longitude'] ?? null)) {
            $data['latitude'] = (float) $data['latitude'];
            $data['longitude'] = (float) $data['longitude'];
        } else {
            $data['latitude'] = null;
            $data['longitude'] = null;
        }

        unset($data['coords_source']);

        return $data;
    }
}
