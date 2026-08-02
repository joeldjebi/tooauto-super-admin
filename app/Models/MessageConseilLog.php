<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MessageConseilLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'message_conseil_id',
        'user_id',
        'fcm_token',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function messageConseil()
    {
        return $this->belongsTo(MessageConseil::class);
    }
}
