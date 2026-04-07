<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    protected $fillable = ['user_id', 'guinea_pig_id', 'content', 'rating', 'is_active'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function guineaPig() {
        return $this->belongsTo(GuineaPig::class);
    }
}
