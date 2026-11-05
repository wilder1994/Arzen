<?php

namespace App\Services\Geo;

use Illuminate\Support\Facades\Http;
use Throwable;

final class ColombiaGeocoder
{
    /**
     * @return array{latitude: float, longitude: float}|null
     */
    public function locate(?string $address, ?string $city, ?string $department): ?array
    {
        $address = trim((string) $address);
        $city = trim((string) $city);
        $department = trim((string) $department);
        $key = config('services.google.maps_key');

        if (mb_strlen($address) < 5 || $city === '' || $department === '' || ! is_string($key) || $key === '') {
            return null;
        }

        try {
            $response = Http::timeout(8)->get('https://maps.googleapis.com/maps/api/geocode/json', [
                'address' => $address.', '.$city.', '.$department.', Colombia',
                'components' => 'country:CO',
                'region' => 'co',
                'key' => $key,
            ]);
        } catch (Throwable) {
            return null;
        }

        $location = $response->json('results.0.geometry.location');

        if (! $response->ok() || ! is_array($location) || ! isset($location['lat'], $location['lng'])) {
            return null;
        }

        return [
            'latitude' => (float) $location['lat'],
            'longitude' => (float) $location['lng'],
        ];
    }
}
