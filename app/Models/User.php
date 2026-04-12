<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',      // 'client', 'photographer'
        'is_admin',  //  : true/false para admin
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_admin' => 'boolean', //  
    ];

    //  Relaciones
    public function photographer()
    {
        return $this->hasOne(Photographer::class);
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
    }

    //  Helpers de roles
    public function isPhotographer(): bool
    {
        return $this->role === 'photographer';
    }

    public function isClient(): bool
    {
        return $this->role === 'client';
    }

    public function isAdmin(): bool
    {
        return $this->is_admin === true;  
    }

   
    public function isApprovedPhotographer(): bool
    {
        if (!$this->isPhotographer()) {
            return false;
        }

        return $this->photographer?->status === 'approved';
    }
}
