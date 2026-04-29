<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatAnalytics extends Model
{
    protected $fillable = [
        'session_id',
        'user_id',
        'message_type',
        'message',
        'response',
        'intent',
        'response_time_ms',
        'resolved',
        'satisfaction_rating',
        'metadata',
    ];

    protected $casts = [
        'metadata' => 'array',
        'resolved' => 'boolean',
        'response_time_ms' => 'integer',
        'satisfaction_rating' => 'integer',
    ];

    public function session()
    {
        return $this->belongsTo(ChatSession::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
