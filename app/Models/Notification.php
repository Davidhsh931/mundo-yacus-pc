<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Notification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'message',
        'type',
        'read',
        'data'
    ];

    protected $casts = [
        'read' => 'boolean',
        'data' => 'array'
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para notificaciones no leídas
     */
    public function scopeUnread($query)
    {
        return $query->where('read', false);
    }

    /**
     * Scope para notificaciones por tipo
     */
    public function scopeOfType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Obtener IDs adicionales desde el campo data
     */
    public function getDataAttribute($value)
    {
        return json_decode($value, true) ?? [];
    }

    /**
     * Obtener order_id desde data
     */
    public function getOrderIdAttribute()
    {
        return $this->data['order_id'] ?? null;
    }

    /**
     * Obtener product_id desde data
     */
    public function getProductIdAttribute()
    {
        return $this->data['product_id'] ?? null;
    }

    /**
     * Obtener comment_id desde data
     */
    public function getCommentIdAttribute()
    {
        return $this->data['comment_id'] ?? null;
    }
}
