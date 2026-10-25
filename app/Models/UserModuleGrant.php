<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserModuleGrant extends Model
{
    protected $fillable = [
        'user_id',
        'module',
        'level',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
