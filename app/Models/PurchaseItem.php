<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'purchase_payment_id',
        'photo_id',
        'unit_price',     
        //   title y description  
        //   quantity  
        
      
        'download_token', 
        'download_count', 
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'download_count' => 'integer',
    ];

   
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->download_token)) {
                $item->download_token = Str::random(64);
            }
        });
    }

     
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(PurchasePayment::class, 'purchase_payment_id');
    }
}