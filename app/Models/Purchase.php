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
        // 'photo_id',  <-- YA NO VA (está en purchase_items)
        // 'event_id',  <-- Opcional, si no lo pusiste en la migración nueva, quítalo.
        'buyer_email',
        'buyer_name',
        'total_amount',      // <--- CAMBIADO (antes amount)
        'currency',
        'status',
        'mp_preference_id',
        'mp_payment_id',
        'mp_merchant_order_id',
        'payment_details',
        'metadata',
        'rejection_reason',
        // 'downloaded_at',   <-- Si los moviste a items, quítalos. Si están en purchases, déjalos.
        // 'download_count', 
        'order_token',       // <--- CAMBIADO (antes download_token)
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
            // Generamos el token de la ORDEN
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

    // Helpers (Actualizados)
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}