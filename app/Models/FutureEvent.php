<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Carbon\Carbon;

class FutureEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'photographer_id',
        'title',
        'description',
        'location',
        'event_date',
        'expiry_date',
        'cover_image',
        'status',
        'converted_event_id',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'expiry_date' => 'datetime',
    ];

    // ============================================
    //  RELACIONES
    // ============================================

    /**
     * Relación: Fotógrafo del evento futuro
     */
    public function photographer()
    {
        return $this->belongsTo(Photographer::class);
    }

    /**
     * Relación: Evento normal creado tras conversión
     */
    public function convertedEvent()
    {
        return $this->belongsTo(Event::class, 'converted_event_id');
    }

    // ============================================
    //  SCOPES
    // ============================================

    /**
     * Scope: Solo eventos upcoming (futuro y activos)
     */
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')
                     ->where('event_date', '>', now());
    }

    /**
     * Scope: Eventos listos para convertir (ya pasaron pero aún no convertidos)
     */
    public function scopeReadyToConvert($query)
    {
        return $query->where('status', 'upcoming')
                     ->where('event_date', '<=', now());
    }

    /**
     * Scope: Por fotógrafo
     */
    public function scopeByPhotographer($query, $photographerId)
    {
        return $query->where('photographer_id', $photographerId);
    }

    /**
     * Scope: Por ubicación
     */
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'LIKE', "%{$location}%");
    }

    // ============================================
    //  ACCESSORS & MUTATORS
    // ============================================

    /**
     * Accessor: URL de cover image
     */
    public function getCoverImageUrlAttribute()
    {
        if ($this->cover_image) {
            // Si es URL completa
            if (filter_var($this->cover_image, FILTER_VALIDATE_URL)) {
                return $this->cover_image;
            }
            
            // Si es path de storage
            if (Str::startsWith($this->cover_image, 'future_events/')) {
                return asset('storage/' . $this->cover_image);
            }
            
            // Fallback
            return $this->cover_image;
        }
        
        // Placeholder si no hay imagen
        return 'https://picsum.photos/1280/720?random=' . $this->id;
    }

    /**
     * Accessor: Fecha formateada para humanos
     */
    public function getFormattedDateAttribute()
    {
        return $this->event_date->locale('es')->isoFormat('D [de] MMMM [de] YYYY');
    }

    /**
     * Accessor: Días hasta el evento
     */
    public function daysUntil()
    {
        return now()->diffInDays($this->event_date, false); // false = puede ser negativo
    }

    /**
     * Accessor: ¿Ya pasó?
     */
    public function isPast()
    {
        return $this->event_date->isPast();
    }

    /**
     * Accessor: ¿Es hoy?
     */
    public function isToday()
    {
        return $this->event_date->isToday();
    }

    /**
     * Accessor: ¿Es esta semana?
     */
    public function isThisWeek()
    {
        return $this->event_date->isCurrentWeek();
    }

    /**
     * Accessor: Badge de estado
     */
    public function getStatusBadgeAttribute()
    {
        return match($this->status) {
            'upcoming' => ['text' => 'Próximamente', 'color' => 'blue'],
            'converted' => ['text' => 'Convertido', 'color' => 'green'],
            'cancelled' => ['text' => 'Cancelado', 'color' => 'red'],
            'expired' => ['text' => 'Expirado', 'color' => 'gray'],
            default => ['text' => 'Desconocido', 'color' => 'gray'],
        };
    }

    // ============================================
    //  MÉTODOS HELPER
    // ============================================

    /**
     * Generar slug único para el título
     */
    public static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $count = static::where('title', 'LIKE', "{$slug}%")->count();
        
        return $count ? "{$slug}-{$count}" : $slug;
    }

    /**
     * Verificar si está expirado
     */
    public function isExpired()
    {
        if (!$this->expiry_date) {
            return false;
        }
        
        return now()->isAfter($this->expiry_date);
    }

    /**
     * Marcar como convertido
     */
    public function markAsConverted($eventId)
    {
        $this->update([
            'status' => 'converted',
            'converted_event_id' => $eventId,
        ]);
    }

    /**
     * Cancelar evento
     */
    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }
}
