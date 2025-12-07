<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PurchaseItem extends Model
{
    protected $fillable = [
        'purchase_id',
        'photo_id',
        'unit_price',     
        // Quitamos title y description (se obtienen de la relación Photo)
        // Quitamos quantity (por ahora es siempre 1 en tu lógica)
        
        // Agregamos estos para controlar la descarga individual
        'download_token', 
        'download_count', 
    ];

    protected $casts = [
        'unit_price' => 'decimal:2',
        'download_count' => 'integer',
    ];

    /**
     * Boot: Generar token de descarga único automáticamente
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($item) {
            if (empty($item->download_token)) {
                $item->download_token = Str::random(64);
            }
        });
    }

    /**
     * Compra asociada (Cabecera)
     */
    public function purchase(): BelongsTo
    {
        return $this->belongsTo(Purchase::class);
    }

    /**
     * Foto asociada (Producto)
     */
    public function photo(): BelongsTo
    {
        return $this->belongsTo(Photo::class);
    }
}