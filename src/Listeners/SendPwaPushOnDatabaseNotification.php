<?php

namespace PwaPlugin\Listeners;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PwaPlugin\Jobs\SendPwaPush;
use PwaPlugin\Models\PwaPushSubscription;
use PwaPlugin\Services\PwaPushService;
use PwaPlugin\Services\PwaSettingsRepository;

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

        if (!$this->settings->get('push_enabled', config('pwa-plugin.push_enabled', false))) {
            return;
        }

        if (
            $event->channel === 'database'
            && !$this->settings->get('push_send_on_database_notifications', config('pwa-plugin.push_send_on_database_notifications', true))
        ) {
            return;
        }

        if (
            $event->channel === 'mail'
            && !$this->settings->get('push_send_on_mail_notifications', config('pwa-plugin.push_send_on_mail_notifications', false))
        ) {
            return;
        }

        $notifiable = $event->notifiable;
        if (!$notifiable || !method_exists($notifiable, 'getMorphClass')) {
            return;
        }

        $vapid = [
            'subject' => $this->settings->get('vapid_subject', config('pwa-plugin.vapid_subject', '')),
            'publicKey' => $this->settings->get('vapid_public_key', config('pwa-plugin.vapid_public_key', '')),
            'privateKey' => $this->settings->get('vapid_private_key', config('pwa-plugin.vapid_private_key', '')),
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

        $subscriptionIds = PwaPushSubscription::query()
            ->where('notifiable_type', $notifiable->getMorphClass())
            ->where('notifiable_id', $notifiable->getKey())
            ->pluck('id');

        foreach ($subscriptionIds as $subscriptionId) {
            SendPwaPush::dispatch($subscriptionId, $payload)->onQueue('push');
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
            return $this->normalizePayload($data, $event->notifiable);
        }

        if ($event->channel === 'mail' && method_exists($event->notification, 'toMail')) {
            $mail = $event->notification->toMail($event->notifiable);
            if ($mail instanceof MailMessage) {
                return $this->normalizePayload($this->payloadFromMailMessage($mail), $event->notifiable);
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

        $title = $subject ?: ($greeting ?: config('app.name', 'Pelican'));
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

    private function normalizePayload(array $data, mixed $notifiable = null): array
    {
        $defaultTitle = config('app.name', 'Pelican');
        $defaultBody = trans('pwa-plugin::pwa-plugin.messages.new_notification');

        $title = $data['title'] ?? $data['subject'] ?? $defaultTitle;
        $body = $data['body'] ?? $data['message'] ?? $defaultBody;
        $url = $data['url'] ?? $data['action_url'] ?? url('/app');

        $icon = $this->assetOrUrl($this->settings->get('default_notification_icon', config('pwa-plugin.default_notification_icon', '/pelican.svg')));
        $badge = $this->assetOrUrl($this->settings->get('default_notification_badge', config('pwa-plugin.default_notification_badge', '/pelican.svg')));

        return [
            'title' => $title,
            'body' => $body,
            'icon' => $data['icon'] ?? $icon,
            'badge' => $data['badge'] ?? $badge,
            'url' => $url,
            'tag' => $data['tag'] ?? null,
            'requireInteraction' => $data['require_interaction'] ?? false,
            'actions' => $data['actions'] ?? [],
        ];
    }

    private function assetOrUrl(string $value): string
    {
        if ($value === '') {
            return asset('pelican.svg');
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        if (!str_starts_with($value, '/') && Storage::disk('public')->exists($value)) {
            return Storage::disk('public')->url($value);
        }

        return asset(ltrim($value, '/'));
    }

}

