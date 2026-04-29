<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatSession extends Model
{
    protected $fillable = [
        'user_id',
        'session_id',
        'messages',
        'intent',
        'context',
        'message_count',
        'last_activity',
        'active',
    ];

    protected $casts = [
        'messages' => 'array',
        'context' => 'array',
        'last_activity' => 'datetime',
        'active' => 'boolean',
    ];
}
