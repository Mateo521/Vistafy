<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', // 'customer', 'photographer', 'admin'
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    // Relaciones
    public function photographer()
    {
        return $this->hasOne(Photographer::class);
    }

    public function isPhotographer()
    {
        return $this->role === 'photographer';
    }

    public function isAdmin()
    {
        return $this->role === 'admin';
    }
}
