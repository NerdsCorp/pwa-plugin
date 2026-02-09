<?php

namespace PwaPlugin\Services;

use PwaPlugin\Models\PwaPushSubscription;

class PwaPushService
{
    public function canSend(): bool
    {
        return class_exists(\Minishlink\WebPush\WebPush::class)
            && class_exists(\Minishlink\WebPush\Subscription::class);
    }

    public function sendToSubscription(PwaPushSubscription $subscription, array $payload, array $vapid): bool
    {
        if (!$this->canSend()) {
            return false;
        }

        $webPush = new \Minishlink\WebPush\WebPush([
            'VAPID' => $vapid,
        ]);

        $webPush->queueNotification(
            \Minishlink\WebPush\Subscription::create([
                'endpoint' => $subscription->endpoint,
                'keys' => [
                    'p256dh' => $subscription->public_key,
                    'auth' => $subscription->auth_token,
                ],
            ]),
            json_encode($payload, JSON_UNESCAPED_SLASHES)
        );

        foreach ($webPush->flush() as $report) {
            if (!$report->isSuccess()) {
                return false;
            }
        }

        return true;
    }
}
 