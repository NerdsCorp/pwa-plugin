<?php

namespace PwaPlugin\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Schema;
use PwaPlugin\Models\PwaPushSubscription;
use PwaPlugin\Services\PwaSettingsRepository;
use PwaPlugin\Services\PwaPushService;

class SendPwaPushOnDatabaseNotification
{
    public function __construct(
        private PwaSettingsRepository $settings,
        private PwaPushService $push,
    ) {
    }

    public function handle(NotificationSent $event): void
    {
        if (!in_array($event->channel, ['database', 'mail'], true)) {
            return;
        }

        if (!$this->settings->get('push_enabled', config('pwa.push_enabled', false))) {
            return;
        }

        if ($event->channel === 'database'
            && !$this->settings->get('push_send_on_database_notifications', config('pwa.push_send_on_database_notifications', true))) {
            return;
        }

        if ($event->channel === 'mail'
            && !$this->settings->get('push_send_on_mail_notifications', config('pwa.push_send_on_mail_notifications', false))) {
            return;
        }

        $notifiable = $event->notifiable;
        if (!$notifiable || !method_exists($notifiable, 'getMorphClass')) {
            return;
        }

        $vapid = [
            'subject' => $this->settings->get('vapid_subject', config('pwa.vapid_subject', '')),
            'publicKey' => $this->settings->get('vapid_public_key', config('pwa.vapid_public_key', '')),
            'privateKey' => $this->settings->get('vapid_private_key', config('pwa.vapid_private_key', '')),
        ];

        if (!$this->push->canSend() || !$vapid['publicKey'] || !$vapid['privateKey'] || !$vapid['subject']) {
            return;
        }

        if (!Schema::hasTable('pwa_push_subscriptions')) {
            return;
        }

        $payload = $this->buildPayload($event);
        if (!$payload) {
            return;
        }

        $subscriptions = PwaPushSubscription::query()
            ->where('notifiable_type', $notifiable->getMorphClass())
            ->where('notifiable_id', $notifiable->getKey())
            ->get();

        foreach ($subscriptions as $subscription) {
            $this->push->sendToSubscription($subscription, $payload, $vapid);
        }
    }

    private function buildPayload(NotificationSent $event): ?array
    {
        if (method_exists($event->notification, 'toPwaPush')) {
            $custom = $event->notification->toPwaPush($event->notifiable);
            if (is_array($custom)) {
                return $this->normalizePayload($custom);
            }
        }

        $data = [];

        if (is_array($event->notification)) {
            $data = $event->notification;
        } elseif (method_exists($event->notification, 'toArray')) {
            $data = (array) $event->notification->toArray($event->notifiable);
        }

        if (!empty($data)) {
            return $this->normalizePayload($data);
        }

        if ($event->channel === 'mail' && method_exists($event->notification, 'toMail')) {
            $mail = $event->notification->toMail($event->notifiable);
            if ($mail instanceof MailMessage) {
                return $this->normalizePayload($this->payloadFromMailMessage($mail));
            }
        }

        return null;
    }

    private function payloadFromMailMessage(MailMessage $mail): array
    {
        $greeting = $mail->greeting;
        $introLines = $mail->introLines ?? [];
        $outroLines = $mail->outroLines ?? [];
        $subject = $mail->subject;

        $title = $subject ?: ($greeting ?: 'Pelican Panel');
        $body = '';

        if (!empty($introLines)) {
            $body = implode(' ', $introLines);
        } elseif (!empty($outroLines)) {
            $body = implode(' ', $outroLines);
        } elseif ($greeting) {
            $body = $greeting;
        }

        $payload = [
            'title' => $title,
            'body' => $body,
            'url' => $mail->actionUrl ?: url('/app'),
        ];

        if ($mail->actionText && $mail->actionUrl) {
            $payload['actions'] = [
                [
                    'action' => 'open',
                    'title' => $mail->actionText,
                ],
            ];
        }

        return $payload;
    }

    private function normalizePayload(array $data): array
    {
        $title = $data['title'] ?? $data['subject'] ?? 'Pelican Panel';
        $body = $data['body'] ?? $data['message'] ?? 'You have a new notification.';
        $url = $data['url'] ?? $data['action_url'] ?? url('/app');

        return [
            'title' => $title,
            'body' => $body,
            'icon' => $data['icon'] ?? config('pwa.default_notification_icon', '/pelican.svg'),
            'badge' => $data['badge'] ?? config('pwa.default_notification_badge', '/pelican.svg'),
            'url' => $url,
            'tag' => $data['tag'] ?? null,
            'requireInteraction' => $data['require_interaction'] ?? false,
            'actions' => $data['actions'] ?? [],
        ];
    }
}
