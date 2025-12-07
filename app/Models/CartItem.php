<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CartItem extends Model
{
    protected $fillable = ['cart_id', 'photo_id', 'price'];

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function photo()
    {
        return $this->belongsTo(Photo::class);
    }
}
