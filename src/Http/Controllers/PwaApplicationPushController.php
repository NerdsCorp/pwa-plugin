<?php

declare(strict_types=1);

namespace PwaPlugin\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Schema;
use PwaPlugin\Http\Requests\SendPwaNotificationRequest;
use PwaPlugin\Models\PwaPushSubscription;
use PwaPlugin\Services\PwaPushService;
use PwaPlugin\Services\PwaSettingsRepository;

class PwaApplicationPushController extends Controller
{
    public function sendToUser(
        SendPwaNotificationRequest $request,
        User $user,
        PwaSettingsRepository $settings,
        PwaPushService $push,
    ): JsonResponse {
        $unavailable = $this->unavailableResponse($settings, $push);
        if ($unavailable) {
            return $unavailable;
        }

        $subscriptions = PwaPushSubscription::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())
            ->orderBy('id')
            ->get();

        if ($subscriptions->isEmpty()) {
            return response()->json([
                'message' => 'The user has no push subscriptions.',
                'sent' => 0,
                'failed' => 0,
                'total' => 0,
            ], 404);
        }

        $payload = $this->payload($request, $settings);
        $vapid = $this->vapid($settings);
        $sent = 0;
        $failed = 0;

        foreach ($subscriptions as $subscription) {
            try {
                if ($push->sendToSubscription($subscription, $payload, $vapid)) {
                    $sent++;
                } else {
                    $failed++;
                }
            } catch (\Throwable $exception) {
                report($exception);
                $failed++;
            }
        }

        return $this->sendResult($sent, $failed);
    }

    public function broadcast(
        SendPwaNotificationRequest $request,
        PwaSettingsRepository $settings,
        PwaPushService $push,
    ): JsonResponse {
        $unavailable = $this->unavailableResponse($settings, $push);
        if ($unavailable) {
            return $unavailable;
        }

        $payload = $this->payload($request, $settings);
        $vapid = $this->vapid($settings);
        $sent = 0;
        $failed = 0;
        $total = 0;

        PwaPushSubscription::query()
            ->orderBy('id')
            ->chunkById(200, function ($subscriptions) use ($push, $payload, $vapid, &$sent, &$failed, &$total): void {
                foreach ($subscriptions as $subscription) {
                    $total++;

                    try {
                        if ($push->sendToSubscription($subscription, $payload, $vapid)) {
                            $sent++;
                        } else {
                            $failed++;
                        }
                    } catch (\Throwable $exception) {
                        report($exception);
                        $failed++;
                    }
                }
            });

        return $this->sendResult($sent, $failed, $total);
    }

    private function unavailableResponse(PwaSettingsRepository $settings, PwaPushService $push): ?JsonResponse
    {
        if (!Schema::hasTable('pwa_push_subscriptions')) {
            return response()->json(['message' => 'Push subscriptions are unavailable until the plugin migrations have run.'], 503);
        }

        if (!(bool) $settings->get('push_enabled', config('pwa-plugin.push_enabled', false))) {
            return response()->json(['message' => 'PWA push notifications are disabled.'], 409);
        }

        if (!$push->canSend()) {
            return response()->json(['message' => 'The Web Push library is unavailable.'], 503);
        }

        $vapid = $this->vapid($settings);
        if (!$vapid['publicKey'] || !$vapid['privateKey'] || !$vapid['subject']) {
            return response()->json(['message' => 'VAPID keys are not configured.'], 503);
        }

        return null;
    }

    private function payload(SendPwaNotificationRequest $request, PwaSettingsRepository $settings): array
    {
        $data = $request->validated();

        return [
            'title' => trim($data['title']),
            'body' => trim($data['body']),
            'url' => $data['url'] ?? url('/'),
            'icon' => $data['icon'] ?? $settings->get('default_notification_icon', config('pwa-plugin.default_notification_icon', '/pelican.svg')),
            'badge' => $data['badge'] ?? $settings->get('default_notification_badge', config('pwa-plugin.default_notification_badge', '/pelican.svg')),
            'tag' => $data['tag'] ?? 'pwa-api',
            'requireInteraction' => (bool) ($data['require_interaction'] ?? false),
        ];
    }

    private function vapid(PwaSettingsRepository $settings): array
    {
        return [
            'subject' => $settings->get('vapid_subject', config('pwa-plugin.vapid_subject', '')),
            'publicKey' => $settings->get('vapid_public_key', config('pwa-plugin.vapid_public_key', '')),
            'privateKey' => $settings->get('vapid_private_key', config('pwa-plugin.vapid_private_key', '')),
        ];
    }

    private function sendResult(int $sent, int $failed, ?int $total = null): JsonResponse
    {
        $total ??= $sent + $failed;

        if ($total === 0) {
            return response()->json([
                'message' => 'No push subscriptions were found.',
                'sent' => 0,
                'failed' => 0,
                'total' => 0,
            ], 404);
        }

        return response()->json([
            'message' => $sent > 0 ? 'Push notification sent.' : 'No push notifications were accepted by push services.',
            'sent' => $sent,
            'failed' => $failed,
            'total' => $total,
        ], $sent > 0 ? 200 : 502);
    }
}
