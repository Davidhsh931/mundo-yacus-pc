<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
{
    use SoftDeletes; // Activa el borrado lógico
    
    protected $fillable = [
        'name',
        'training_data',
        'id'
    ];

    public function guineaPigs()
    {
        return $this->hasMany(GuineaPig::class);
    }
}
