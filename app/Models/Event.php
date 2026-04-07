<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'image',
        'event_date',
        'is_active',
    ];

    protected $casts = [
        'event_date' => 'date',
        'is_active' => 'boolean',
    ];

    /**
     * Obtener solo eventos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Obtener eventos futuros
     */
    public function scopeUpcoming($query)
    {
        return $query->where('event_date', '>=', now());
    }

    /**
     * Formatear la fecha del evento
     */
    public function getFormattedDateAttribute()
    {
        return $this->event_date->format('d/m/Y');
    }

    /**
     * Obtener la URL de la imagen
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            return asset('storage/' . $this->image);
        }
        
        // Imagen por defecto si no hay imagen
        return 'https://picsum.photos/seed/event-' . $this->id . '/400/300.jpg';
    }
}
