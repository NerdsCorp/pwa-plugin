<?php

namespace PwaPlugin\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class PwaPushSubscription extends Model
{
    protected $table = 'pwa_push_subscriptions';

    protected $fillable = [
        'notifiable_type',
        'notifiable_id',
        'endpoint',
        'endpoint_hash',
        'public_key',
        'auth_token',
        'content_encoding',
        'user_agent',
        'device_name',
        'last_synced_at',
        'last_push_sent_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
        'last_push_sent_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $subscription): void {
            if ($subscription->endpoint !== null && $subscription->endpoint !== '') {
                $subscription->endpoint_hash = hash('sha256', $subscription->endpoint);
            }
        });
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }
}
