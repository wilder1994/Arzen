<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    public const PARTY_LEGAL = 'legal_entity';

    public const PARTY_NATURAL = 'natural_person';

    protected $fillable = [
        'party_type',
        'name',
        'legal_name',
        'document_type',
        'nit',
        'legal_representative',
        'representative_email',
        'contact_name',
        'email',
        'phone',
        'is_own_company',
        'address',
        'neighborhood',
        'city',
        'department',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'is_own_company' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::created(fn (Client $client) => Installation::mainFor($client));

        static::updated(function (Client $client) {
            if (! $client->wasChanged(array_merge(['name'], Installation::LOCATION_FIELDS))) {
                return;
            }

            $main = Installation::mainFor($client);
            $main->copyFromClient($client);
            $main->save();
        });
    }

    /**
     * @return array<string, string>
     */
    public static function partyTypeLabels(): array
    {
        return [
            self::PARTY_LEGAL => __('Persona jurídica'),
            self::PARTY_NATURAL => __('Persona natural'),
        ];
    }

    /**
     * Mismos códigos que el catálogo de documentos de Zentra.
     *
     * @return array<string, string>
     */
    public static function documentTypeLabels(): array
    {
        return [
            'NIT' => __('NIT'),
            'CC' => __('Cédula de ciudadanía'),
            'CE' => __('Cédula de extranjería'),
            'PA' => __('Pasaporte'),
            'PPT' => __('Permiso por protección temporal'),
        ];
    }

    public static function ownCompany(): ?self
    {
        return static::query()->where('is_own_company', true)->first();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_clients')->withTimestamps();
    }

    public function installations()
    {
        return $this->hasMany(Installation::class);
    }

    public function mainInstallation()
    {
        return $this->hasOne(Installation::class)->where('is_client_site', true);
    }

    public function posts()
    {
        return $this->hasMany(Post::class);
    }

    public function workers()
    {
        return $this->hasMany(Worker::class);
    }

    public function scopeExternal(Builder $query): Builder
    {
        return $query->where('is_own_company', false);
    }

    public function displayLegalName(): string
    {
        return $this->legal_name ?: $this->name;
    }
}
