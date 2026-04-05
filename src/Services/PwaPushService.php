<?php

declare(strict_types=1);

namespace PwaPlugin\Services;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use PwaPlugin\Models\PwaPushSubscription;

class PwaPushService
{
    public function canSend(): bool
    {
        return class_exists(WebPush::class)
            && class_exists(Subscription::class);
    }

    public function sendToSubscription(PwaPushSubscription $subscription, array $payload, array $vapid): bool
    {
        if (!$this->canSend()) {
            return false;
        }

        $webPush = new WebPush([
            'VAPID' => $vapid,
        ]);

        $webPush->queueNotification(
            Subscription::create([
                'endpoint' => $subscription->endpoint,
                'keys' => [
                    'p256dh' => $subscription->public_key,
                    'auth' => $subscription->auth_token,
                ],
            ]),
            json_encode($payload, JSON_UNESCAPED_SLASHES),
        );

        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                if ($report->isSubscriptionExpired()) {
                    $subscription->delete();
                }

                return false;
            }
        }

        $subscription->forceFill([
            'last_push_sent_at' => now(),
        ])->saveQuietly();

        return true;
    }
}
