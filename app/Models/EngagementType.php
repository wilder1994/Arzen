<?php

namespace App\Models;

use App\Models\Concerns\ResolvesCatalogName;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EngagementType extends Model
{
    use ResolvesCatalogName;

    protected $fillable = [
        'name',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

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
}
