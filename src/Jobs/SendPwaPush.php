<?php

namespace PwaPlugin\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Schema;
use PwaPlugin\Models\PwaPushSubscription;
use PwaPlugin\Services\PwaPushService;
use PwaPlugin\Services\PwaSettingsRepository;

class SendPwaPush implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public string $queue = 'push';

    /** @param array<string, mixed> $payload */
    public function __construct(
        private readonly int $subscriptionId,
        private readonly array $payload,
    ) {
    }

    public function handle(PwaPushService $push, PwaSettingsRepository $settings): void
    {
        if (!$push->canSend()) {
            return;
        }

        if (!Schema::hasTable('pwa_push_subscriptions')) {
            return;
        }

        $subscription = PwaPushSubscription::query()->find($this->subscriptionId);
        if (!$subscription) {
            return;
        }

        $vapid = [
            'subject' => $settings->get('vapid_subject', config('pwa-plugin.vapid_subject', '')),
            'publicKey' => $settings->get('vapid_public_key', config('pwa-plugin.vapid_public_key', '')),
            'privateKey' => $settings->get('vapid_private_key', config('pwa-plugin.vapid_private_key', '')),
        ];

        if (!$vapid['publicKey'] || !$vapid['privateKey'] || !$vapid['subject']) {
            return;
        }

        $push->sendToSubscription($subscription, $this->payload, $vapid);
    }
}

