<?php

namespace App;

use Closure;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CatalogCache
{
    public const TTL_SECONDS = 60;

    /** @param array<string, mixed> $parameters */
    public static function remember(string $resource, array $parameters, Closure $load): array
    {
        if (DB::transactionLevel() > 0) {
            return $load();
        }

        $version = Cache::get('catalog:version');
        if ($version === null) {
            Cache::add('catalog:version', (string) Str::uuid(), 31536000);
            $version = Cache::get('catalog:version');
        }
        ksort($parameters);
        $key = 'catalog:'.$version.':'.$resource.':'.hash('sha256', json_encode($parameters, JSON_THROW_ON_ERROR));

        return Cache::remember($key, self::TTL_SECONDS, $load);
    }

    public static function invalidate(): void
    {
        Cache::forever('catalog:version', (string) Str::uuid());
    }
}
