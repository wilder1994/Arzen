<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Position extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function workers()
    {
        return $this->hasMany(Worker::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /**
     * Busca un cargo por nombre sin importar mayúsculas ni tildes, o lo crea.
     */
    public static function resolveByName(?string $name): ?self
    {
        $name = trim((string) $name);

        if ($name === '') {
            return null;
        }

        $key = static::normalize($name);
        $match = static::query()->get()->first(fn (self $position) => static::normalize($position->name) === $key);

        return $match ?? static::query()->create(['name' => mb_convert_case(mb_strtolower($name), MB_CASE_TITLE, 'UTF-8')]);
    }

    private static function normalize(string $value): string
    {
        $value = mb_strtolower(trim($value));

        return strtr($value, ['á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n']);
    }
}
