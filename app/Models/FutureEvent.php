<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
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
        'latitude',        // 
        'longitude',       // 
        'event_date',
        'expiry_date',
        'cover_image',
        'status',
        'converted_event_id',
    ];

    protected $casts = [
        'event_date' => 'datetime',
        'expiry_date' => 'datetime',
        'latitude' => 'decimal:7',   // 
        'longitude' => 'decimal:7',  // 
    ];

    protected $appends = ['cover_image_url', 'formatted_date', 'days_until'];



 
    public function photographer()
    {
        return $this->belongsTo(Photographer::class);
    }


    public function convertedEvent()
    {
        return $this->belongsTo(Event::class, 'converted_event_id');
    }



 
    public function scopeUpcoming($query)
    {
        return $query->where('status', 'upcoming')
            ->where('event_date', '>', now());
    }


    public function scopeReadyToConvert($query)
    {
        return $query->where('status', 'upcoming')
            ->where('event_date', '<=', now());
    }

 
    public function scopeByPhotographer($query, $photographerId)
    {
        return $query->where('photographer_id', $photographerId);
    }

 
    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'LIKE', "%{$location}%");
    }


  
public function getCoverImageUrlAttribute()
    {
        if (!$this->cover_image) return null;

        if (\Illuminate\Support\Str::startsWith($this->cover_image, ['http://', 'https://'])) {
            return $this->cover_image;
        }

        /** @var \Illuminate\Filesystem\FilesystemAdapter $disk */
        $disk = \Illuminate\Support\Facades\Storage::disk('b2');

        return $disk->url($this->cover_image);
    }


    public function collaborators()
    {
        return $this->belongsToMany(
            \App\Models\Photographer::class, 
            'future_event_photographer',  
            'future_event_id',            
            'photographer_id'             
        )->withPivot('status')->withTimestamps();
    }


  
    public function getFormattedDateAttribute()
    {
        return $this->event_date->locale('es')->isoFormat('dddd D [de] MMMM [de] YYYY [a las] HH:mm');
    }

   
    public function daysUntil()
    {
        return now()->diffInDays($this->event_date, false); // false = puede ser negativo
    }

  
    public function isPast()
    {
        return $this->event_date->isPast();
    }

 
    public function isToday()
    {
        return $this->event_date->isToday();
    }


    public function isThisWeek()
    {
        return $this->event_date->isCurrentWeek();
    }

  
    public function getStatusBadgeAttribute()
    {
        return match ($this->status) {
            'upcoming' => ['text' => 'Próximamente', 'color' => 'blue'],
            'converted' => ['text' => 'Convertido', 'color' => 'green'],
            'cancelled' => ['text' => 'Cancelado', 'color' => 'red'],
            'expired' => ['text' => 'Expirado', 'color' => 'gray'],
            default => ['text' => 'Desconocido', 'color' => 'gray'],
        };
    }

 
   
    public static function generateUniqueSlug($title)
    {
        $slug = Str::slug($title);
        $count = static::where('title', 'LIKE', "{$slug}%")->count();

        return $count ? "{$slug}-{$count}" : $slug;
    }

  
    public function isExpired()
    {
        if (!$this->expiry_date) {
            return false;
        }

        return now()->isAfter($this->expiry_date);
    }

  
    public function markAsConverted($eventId)
    {
        $this->update([
            'status' => 'converted',
            'converted_event_id' => $eventId,
        ]);
    }

  
    public function cancel()
    {
        $this->update(['status' => 'cancelled']);
    }
}
