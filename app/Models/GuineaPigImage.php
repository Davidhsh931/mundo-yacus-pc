<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GuineaPigImage extends Model
{

    protected $fillable = [
        'guinea_pig_id',
        'image_path'
    ];

    public function guineaPig()
    {
        return $this->belongsTo(GuineaPig::class);
    }

    /**
     * Get the public URL for the image.
     * Images are stored in storage/app/public/images/ and served via the
     * storage symlink at public/storage, so the correct URL is /storage/images/filename.jpg.
     */
    public function getImageUrlAttribute(): string
    {
        if (!$this->image_path) {
            return '/images/cobaya-fondo-blanco.jpg';
        }

        // If already a full URL, return as-is
        if (str_starts_with($this->image_path, 'http')) {
            return $this->image_path;
        }

        // Strip any leading /storage/ or storage/ prefix to avoid doubling
        $path = ltrim($this->image_path, '/');
        $path = preg_replace('#^storage/#', '', $path);

        return '/storage/' . $path;
    }
}