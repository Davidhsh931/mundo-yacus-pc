<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'total',
        'status',
        'shipping_address',
        'payment_method',
        'status_updated_at',
        'status_notes',
        'delivery_type',
        'shipping_cost'
    ];

    protected $casts = [
        'status_updated_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function trackings()
    {
        return $this->hasMany(OrderTracking::class)->orderBy('status_updated_at', 'desc');
    }
}
