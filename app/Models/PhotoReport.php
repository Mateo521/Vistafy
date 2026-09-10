<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'photo_id',
        'email',
        'reason',
        'message',
        'status',
    ];


    public function photo()
    {
        return $this->belongsTo(Photo::class);
    }

 
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}