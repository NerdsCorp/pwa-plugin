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
