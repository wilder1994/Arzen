<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_id',
        'post_id',
        'name',
        'document_type',
        'document',
        'position_id',
        'responsible_user_id',
        'notes',
        'archived_at',
    ];

    protected $casts = [
        'archived_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function post()
    {
        return $this->belongsTo(Post::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function responsible()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function assignments()
    {
        return $this->hasMany(WeaponWorkerAssignment::class);
    }

    public function histories()
    {
        return $this->hasMany(WorkerHistory::class);
    }

    public function positionLabel(): string
    {
        return $this->position?->name ?? __('Sin cargo');
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
