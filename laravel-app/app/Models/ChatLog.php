<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatLog extends Model
{
    protected $fillable = [
        'session_id',
        'user_message',
        'ai_reply',
        'escalated',
        'contact',
        'resolved',
    ];
}