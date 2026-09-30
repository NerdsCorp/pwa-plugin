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
        if (filter_var($host, FILTER_VALIDATE_IP) !== false) {
            return false;
        }

        $records = @dns_get_record($host, DNS_A | DNS_AAAA);
        if (!is_array($records) || $records === []) {
            return false;
        }

        $addresses = [];
        foreach ($records as $record) {
            if (!empty($record['ip'])) {
                $addresses[] = $record['ip'];
            }
            if (!empty($record['ipv6'])) {
                $addresses[] = $record['ipv6'];
            }
        }

        if ($addresses === []) {
            return false;
        }

        foreach ($addresses as $address) {
            if (filter_var(
                $address,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) === false) {
                return false;
            }
        }

        return true;
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
