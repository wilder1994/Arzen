<?php

namespace App\Models;

use App\Support\PostCustodyRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Installation extends Model
{
    public const LOCATION_FIELDS = ['address', 'neighborhood', 'city', 'department', 'latitude', 'longitude'];

    protected $fillable = [
        'client_id',
        'name',
        'code',
        'is_client_site',
        'address',
        'neighborhood',
        'city',
        'department',
        'latitude',
        'longitude',
        'notes',
        'archived_at',
    ];

    protected $casts = [
        'is_client_site' => 'boolean',
        'archived_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saved(function (Installation $installation) {
            if ($installation->wasRecentlyCreated || $installation->wasChanged(self::LOCATION_FIELDS)) {
                $installation->syncPostLocations();
            }
        });
    }

    /**
     * Instalación que representa la sede del propio cliente; se crea si aún no existe.
     */
    public static function matchingPrincipal(int $id, int $clientId): ?self
    {
        return static::query()
            ->whereKey($id)
            ->where('client_id', $clientId)
            ->where('is_client_site', true)
            ->first();
    }

    public static function mainFor(Client $client): self
    {
        $existing = static::query()
            ->where('client_id', $client->id)
            ->where('is_client_site', true)
            ->first();

        if ($existing) {
            return $existing;
        }

        return static::query()->create(array_merge(
            $client->only(self::LOCATION_FIELDS),
            [
                'client_id' => $client->id,
                'name' => $client->name,
                'is_client_site' => true,
            ],
        ));
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * Copia los datos del cliente cuando la instalación es su misma sede.
     */
    public function copyFromClient(Client $client): void
    {
        $this->fill(array_merge($client->only(self::LOCATION_FIELDS), ['name' => $client->name]));
    }

    /**
     * Los puestos toman la ubicación de su instalación; los talleres de armero conservan la suya.
     */
    public function syncPostLocations(): void
    {
        $this->posts()
            ->where(function (Builder $query) {
                $query->whereNull('custody_role')->orWhere('custody_role', '!=', PostCustodyRole::ARMERO);
            })
            ->update([
                'address' => $this->address,
                'city' => $this->city,
                'department' => $this->department,
                'latitude' => $this->latitude,
                'longitude' => $this->longitude,
            ]);
    }
}
