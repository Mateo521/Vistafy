<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Purchase extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        // 'photo_id',  <--  (está en purchase_items)
        // 'event_id',   
        'buyer_email',
        'buyer_name',
        'guest_email',
        'total_amount',      // <---  (antes amount)
        'currency',
        'status',
        'mp_preference_id',
        'mp_payment_id',
        'mp_payment_status',
        'mp_merchant_order_id',
        'payment_details',
        'metadata',
        'rejection_reason',
        // 'downloaded_at',   
        // 'download_count', 
        'order_token',       // <---   (antes download_token)
    ];

    protected $casts = [
        'payment_details' => 'array',
        'metadata' => 'array',
        'total_amount' => 'decimal:2',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {
        
            if (empty($purchase->order_token)) {
                $purchase->order_token = Str::random(64);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(PurchasePayment::class);
    }

    // Helpers (Actualizados)
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}