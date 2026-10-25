<?php

namespace App\Models;

use App\Support\PostCustodyRole;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'installation_id',
        'custody_role',
        'owner_responsible_user_id',
        'name',
        'address',
        'city',
        'department',
        'latitude',
        'longitude',
        'notes',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (Post $post) {
            if (! $post->installation_id && $post->client_id) {
                $post->installation_id = Installation::mainFor(Client::query()->findOrFail($post->client_id))->id;
            }

            if ($post->installation_id && ($post->isDirty('installation_id') || ! $post->exists)) {
                $installation = Installation::query()->find($post->installation_id);

                if ($installation) {
                    $post->client_id = $installation->client_id;

                    $keepOwnAddress = $post->custody_role === PostCustodyRole::ARMERO && filled($post->address);

                    if (! $keepOwnAddress) {
                        $post->address = $installation->address;
                    }

                    $post->city = $installation->city;
                    $post->department = $installation->department;
                    $post->latitude = $installation->latitude;
                    $post->longitude = $installation->longitude;
                }
            }
        });
    }

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function installation()
    {
        return $this->belongsTo(Installation::class);
    }

    public function workers()
    {
        return $this->hasMany(Worker::class);
    }

    public function ownerResponsible()
    {
        return $this->belongsTo(User::class, 'owner_responsible_user_id');
    }

    public function isCustodyPost(): bool
    {
        return filled($this->custody_role);
    }

    public function isNonOperationalCustody(): bool
    {
        return in_array($this->custody_role, PostCustodyRole::nonOperational(), true);
    }

    public function scopeSelectableForInternalAssignment(Builder $query): Builder
    {
        return $query->where(function (Builder $inner) {
            $inner->whereNull('custody_role')
                ->orWhere('custody_role', PostCustodyRole::ARMERO);
        });
    }

    public function scopeOperationalCustody(Builder $query): Builder
    {
        return $query->where(function (Builder $inner) {
            $inner->whereNull('custody_role')
                ->orWhere('custody_role', PostCustodyRole::ARMERILLO);
        });
    }

    public function scopeNonOperationalCustody(Builder $query): Builder
    {
        return $query->whereIn('custody_role', PostCustodyRole::nonOperational());
    }

    public function scopeForResponsibleCustody(Builder $query, int $responsibleUserId): Builder
    {
        return $query->where('owner_responsible_user_id', $responsibleUserId);
    }

    public function assignments()
    {
        return $this->hasMany(WeaponPostAssignment::class);
    }

    public function histories()
    {
        return $this->hasMany(PostHistory::class);
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function scopeArchived(Builder $query): Builder
    {
        return $query->whereNotNull('archived_at');
    }
}

