<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ContactMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'subject',
        'message',
        'is_read'
    ];

    protected $casts = [
        'is_read' => 'boolean',
    ];

    // Scope para mensajes no leídos
    public function scopeUnread($query)
    {
        return $query->where('is_read', false);
    }

    // Scope para mensajes leídos
    public function scopeRead($query)
    {
        return $query->where('is_read', true);
    }
}
