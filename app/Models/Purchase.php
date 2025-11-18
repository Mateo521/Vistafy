<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Purchase extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'photo_id',
        'event_id',
        'buyer_email',
        'buyer_name',
        'mp_preference_id',
        'mp_payment_id',
        'mp_merchant_order_id',
        'status',
        'amount',
        'currency',
        'payment_details',
        'rejection_reason',
        'downloaded_at',
        'download_count',
        'download_token',
    ];

    protected $casts = [
        'payment_details' => 'array',
        'amount' => 'decimal:2',
          'metadata' => 'array', //
        'downloaded_at' => 'datetime',
        'download_count' => 'integer',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($purchase) {
            if (empty($purchase->download_token)) {
                $purchase->download_token = Str::random(64);
            }
        });
    }

    /**
     * Usuario comprador
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Foto comprada
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }

    /**
     * Evento
     */
    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * Items de la compra
     */
    public function items(): HasMany
    {
        return $this->hasMany(PurchaseItem::class);
    }

    /**
     * Verificar si la compra está aprobada
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Verificar si la compra está pendiente
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Marcar como aprobada
     */
    public function markAsApproved(): void
    {
        $this->update(['status' => 'approved']);
    }

    /**
     * Marcar como rechazada
     */
    public function markAsRejected(string $reason = null): void
    {
        $this->update([
            'status' => 'rejected',
            'rejection_reason' => $reason,
        ]);
    }

    /**
     * Incrementar contador de descargas
     */
    public function incrementDownloadCount(): void
    {
        $this->increment('download_count');
        
        if ($this->download_count === 1) {
            $this->update(['downloaded_at' => now()]);
        }
    }

    /**
     * Scope para compras aprobadas
     */
    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    /**
     * Scope para compras pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }
}
