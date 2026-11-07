<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WeaponPhotoStaging extends Model
{
    protected $table = 'weapon_photo_staging';

    protected $fillable = [
        'user_id',
        'weapon_id',
        'description',
        'file_id',
    ];

    public function fieldUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function weapon(): BelongsTo
    {
        return $this->belongsTo(Weapon::class);
    }

    public function file(): BelongsTo
    {
        return $this->belongsTo(File::class);
    }
}
