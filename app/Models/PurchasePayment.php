<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PurchasePayment extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id',
        'photographer_id',
        'amount',
        'platform_fee',
        'currency',
        'status',
        'mp_preference_id',
        'mp_payment_id',
        'mp_payment_status',
        'init_point',
        'sandbox_init_point',
        'payment_details',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'platform_fee' => 'decimal:2',
        'payment_details' => 'array',
    ];

    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    public function photographer(): BelongsTo
    {
        return $this->belongsTo(Photographer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }
}
