<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;

/**
 * Evita que una acción falle cuando el servidor de tiempo real (Reverb/Pusher) no está encendido.
 */
final class Realtime
{
    private const CACHE_KEY = 'realtime.reachable';

    private const CACHE_SECONDS = 30;

    public static function available(): bool
    {
        if (! (bool) config('broadcasting.enabled', true)) {
            return false;
        }

        $connection = (string) config('broadcasting.default');

        if (! in_array($connection, ['reverb', 'pusher'], true)) {
            return true;
        }

        $host = (string) config("broadcasting.connections.{$connection}.options.host");
        $port = (int) config("broadcasting.connections.{$connection}.options.port", 443);

        if ($host === '' || app()->runningUnitTests()) {
            return true;
        }

        return Cache::remember(self::CACHE_KEY, self::CACHE_SECONDS, function () use ($host, $port): bool {
            $socket = @fsockopen($host, $port, $errorCode, $errorMessage, 0.5);

            if ($socket === false) {
                return false;
            }

            fclose($socket);

            return true;
        });
    }
}
