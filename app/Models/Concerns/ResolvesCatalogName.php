<?php

namespace App\Models\Concerns;

trait ResolvesCatalogName
{
    public static function resolveByName(?string $name): ?static
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        return static::findByName($name) ?? static::query()->create([
            'name' => mb_convert_case(mb_strtolower($name), MB_CASE_TITLE, 'UTF-8'),
            'sort_order' => ((int) static::query()->max('sort_order')) + 10,
        ]);
    }

    public static function findByName(string $name): ?static
    {
        $key = static::catalogKey($name);

        return static::query()->get()->first(
            fn (self $item): bool => static::catalogKey($item->name) === $key
        );
    }

    public static function catalogKey(string $value): string
    {
        $value = mb_strtolower(trim($value));

        return strtr($value, [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
        ]);
    }
}
