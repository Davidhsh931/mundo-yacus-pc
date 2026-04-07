<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class GuineaPig extends Model
{
    use SoftDeletes;
    protected $fillable = [
    'user_id', 
    'name', 
    'breed',          // <--- AÑADIR BREED
    'average_weight',  // <--- AÑADIR AVERAGE_WEIGHT
    'category_id',    // <--- AÑADIR CATEGORY_ID
    'species', 
    'price', 
    'stock',          // <--- ASEGÚRATE DE QUE ESTÉ AQUÍ
    'specifications',   // <--- ASEGÚRATE DE QUE ESTÉ AQUÍ
    'ia_verification', 
    'active'
];

    protected $casts = [
        'specifications' => 'array', 
        'ia_verification' => 'array',
        'active' => 'boolean',  // ← AÑADIR ESTE CAST
    ];

    // El Habitante que vende el animal
    public function seller()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(GuineaPigImage::class)->orderBy('position');
    }

    public function getMainImageAttribute()
    {
        $mainImage = $this->images()->where('position', 0)->first();
        if ($mainImage) {
            return [
                'url' => asset('storage/' . $mainImage->image_path),
                'webp_url' => asset('storage/' . str_replace('.jpg', '.webp', $mainImage->image_path)),
                'thumb_url' => asset('storage/' . str_replace('.jpg', '_thumb.jpg', $mainImage->image_path)),
                'alt' => $this->name
            ];
        }
        return null;
    }

    public function getAllImagesAttribute()
    {
        return $this->images()->map(function($image) {
            return [
                'url' => asset('storage/' . $image->image_path),
                'webp_url' => asset('storage/' . str_replace('.jpg', '.webp', $image->image_path)),
                'thumb_url' => asset('storage/' . str_replace('.jpg', '_thumb.jpg', $image->image_path)),
                'alt' => $this->name,
                'position' => $image->position
            ];
        });
    }

    public function comments() {
        return $this->hasMany(Comment::class)->with('user')->latest();
    }
}