<?php

namespace PwaPlugin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PwaNotificationPreference extends Model
{
    protected $table = 'pwa_notification_preferences';

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'channel',
        'enabled',
        'digest_mode',
        'quiet_hours_enabled',
        'quiet_hours_start',
        'quiet_hours_end',
        'max_per_day',
        'sent_count_24h',
        'last_sent_at',
        'last_digest_sent_at',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'quiet_hours_enabled' => 'boolean',
        'max_per_day' => 'integer',
        'sent_count_24h' => 'integer',
        'last_sent_at' => 'datetime',
        'last_digest_sent_at' => 'datetime',
    ];

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
