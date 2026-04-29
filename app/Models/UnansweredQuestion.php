<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UnansweredQuestion extends Model
{
    protected $fillable = [
        'question',
        'cleaned_question',
        'times_asked',
        'resolved',
    ];

    protected $casts = [
        'times_asked' => 'integer',
        'resolved' => 'boolean',
    ];
}
