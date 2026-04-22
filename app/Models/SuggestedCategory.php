<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SuggestedCategory extends Model
{
    protected $fillable = ['name', 'description', 'product_ids', 'status'];

    protected $casts = [
        'product_ids' => 'array',
    ];
}
