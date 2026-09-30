<?php

declare(strict_types=1);

namespace PwaPlugin\Services;

use Illuminate\Support\Facades\Log;
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
        if (!$this->canSend()) {
            Log::error('PWA push could not be sent because the Web Push library is unavailable.', $this->subscriptionContext($subscription));

            return false;
        }

        if (!self::isAllowedEndpoint((string) $subscription->endpoint)) {
            Log::warning('PWA push was skipped because the subscription endpoint is not an allowed public HTTPS address.', $this->subscriptionContext($subscription));

            return false;
        }

        try {
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
                json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            );

            foreach ($webPush->flush() as $report) {
                if (!$report->isSuccess()) {
                    $expired = $report->isSubscriptionExpired();
                    if ($expired) {
                        $subscription->delete();
                    }

                    $response = $report->getResponse();
                    Log::warning('PWA push service rejected a notification.', array_merge(
                        $this->subscriptionContext($subscription),
                        [
                            'http_status' => $response?->getStatusCode(),
                            'reason' => $this->safeLogMessage($report->getReason()),
                            'subscription_expired' => $expired,
                        ],
                    ));

                    return false;
                }
            }
        } catch (\Throwable $exception) {
            Log::error('PWA push delivery failed before the push service accepted the notification.', array_merge(
                $this->subscriptionContext($subscription),
                [
                    'exception' => $exception::class,
                    'error' => $this->safeLogMessage($exception->getMessage()),
                ],
            ));

            return false;
        }

        $subscription->forceFill([
            'last_push_sent_at' => now(),
        ])->saveQuietly();

        return true;
    }

    private function subscriptionContext(PwaPushSubscription $subscription): array
    {
        return [
            'subscription_id' => $subscription->getKey(),
            'user_id' => $subscription->notifiable_id,
        ];
    }

    private function safeLogMessage(string $message): string
    {
        $message = preg_replace('~https?://[^\s"<>]+~i', '[redacted URL]', $message) ?? 'Unspecified push error.';
        $message = preg_replace('/[\r\n\t]+/', ' ', $message) ?? 'Unspecified push error.';

        return substr($message, 0, 300);
    }
}
