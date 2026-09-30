<?php

declare(strict_types=1);

namespace PwaPlugin\Services;

use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;
use PwaPlugin\Models\PwaPushSubscription;

class PwaPushService
{
    public static function isAllowedEndpoint(string $endpoint): bool
    {
        $parts = parse_url($endpoint);
        if (!is_array($parts)
            || strtolower((string) ($parts['scheme'] ?? '')) !== 'https'
            || empty($parts['host'])
            || isset($parts['user'])
            || isset($parts['pass'])
            || (isset($parts['port']) && (int) $parts['port'] !== 443)
        ) {
            return false;
        }

        $host = strtolower(rtrim((string) $parts['host'], '.'));
        foreach ((array) config('pwa-plugin.push_endpoint_hosts', []) as $allowedHost) {
            $allowedHost = strtolower(trim((string) $allowedHost));
            $allowedHost = ltrim($allowedHost, '*.');
            $allowedHost = rtrim($allowedHost, '.');
            if ($host === $allowedHost || str_ends_with($host, '.' . $allowedHost)) {
                return true;
            }
        }

        return false;
    }

    public function canSend(): bool
    {
        return class_exists(WebPush::class)
            && class_exists(Subscription::class);
    }

    public function sendToSubscription(PwaPushSubscription $subscription, array $payload, array $vapid): bool
    {
        if (!$this->canSend() || !self::isAllowedEndpoint((string) $subscription->endpoint)) {
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
