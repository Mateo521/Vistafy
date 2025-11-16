<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Photographer extends Model
{
    protected $fillable = [
        'user_id',
        'business_name',
        'region',
        'bio',
        'phone',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relaciones
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function photos()
    {
        return $this->hasMany(Photo::class);
    }
}
