<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationCampaignLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'notification_campaign_id',
        'user_id',
        'alert_id',
        'type_alert_id',
        'fcm_token',
        'status',
        'error_message',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function campaign()
    {
        return $this->belongsTo(NotificationCampaign::class, 'notification_campaign_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function alert()
    {
        return $this->belongsTo(Alert::class);
    }

    public function typeAlert()
    {
        return $this->belongsTo(Type_alert::class, 'type_alert_id');
    }
}