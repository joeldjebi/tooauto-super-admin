<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificationCampaign extends Model
{
    use HasFactory;

    public const STATUS_DRAFT = 'draft';
    public const STATUS_SCHEDULED = 'scheduled';
    public const STATUS_SENDING = 'sending';
    public const STATUS_SENT = 'sent';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public const AUDIENCE_ALL_USERS = 'all_users';
    public const AUDIENCE_SELECTED_USERS = 'selected_users';
    public const AUDIENCE_ALERT_EXPIRATION = 'alert_expiration';

    protected $fillable = [
        'title',
        'body',
        'image_url',
        'action_url',
        'audience_type',
        'audience_filters',
        'scheduled_at',
        'sent_at',
        'status',
        'total_targets',
        'success_count',
        'failure_count',
        'created_by',
        'created_by_type',
        'last_error',
    ];

    protected $casts = [
        'audience_filters' => 'array',
        'scheduled_at' => 'datetime',
        'sent_at' => 'datetime',
    ];

    public function logs()
    {
        return $this->hasMany(NotificationCampaignLog::class);
    }

    public function scopeDue($query)
    {
        return $query->where('status', self::STATUS_SCHEDULED)
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '<=', now());
    }
}