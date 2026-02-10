<?php

namespace PwaPlugin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use PwaPlugin\Models\PwaPushSubscription;
use PwaPlugin\Services\PwaPushService;
use PwaPlugin\Services\PwaSettingsRepository;

class PwaPushController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        if (!Schema::hasTable('pwa_push_subscriptions')) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.table_missing'),
            ], 500);
        }

        $request->validate([
            'endpoint' => ['required', 'string', 'url', 'max:2048'],
            'keys.p256dh' => ['required', 'string', 'max:255'],
            'keys.auth' => ['required', 'string', 'max:255'],
            'contentEncoding' => ['nullable', 'string', 'max:50'],
        ]);

        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.unauthorized'),
            ], 401);
        }

        $subscription = PwaPushSubscription::query()->updateOrCreate(
            [
                'endpoint' => $request->string('endpoint')->toString(),
                'notifiable_type' => $user->getMorphClass(),
                'notifiable_id' => $user->getKey(),
            ],
            [
                'public_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
                'content_encoding' => $request->input('contentEncoding', 'aesgcm'),
                'user_agent' => $request->userAgent(),
            ],
        );

        return response()->json([
            'message' => trans('pwa-plugin::pwa-plugin.notifications.subscribed'),
            'id' => $subscription->getKey(),
        ]);
    }

    public function unsubscribe(Request $request): JsonResponse
    {
        if (!Schema::hasTable('pwa_push_subscriptions')) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.table_missing'),
            ], 500);
        }

        $request->validate([
            'endpoint' => ['required', 'string', 'url', 'max:2048'],
        ]);

        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.unauthorized'),
            ], 401);
        }

        PwaPushSubscription::query()
            ->where('endpoint', $request->string('endpoint')->toString())
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())
            ->delete();

        return response()->json([
            'message' => trans('pwa-plugin::pwa-plugin.notifications.unsubscribed'),
        ]);
    }

    public function test(Request $request, PwaSettingsRepository $settings, PwaPushService $push): JsonResponse
    {
        if (!Schema::hasTable('pwa_push_subscriptions')) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.table_missing'),
            ], 500);
        }

        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.unauthorized'),
            ], 401);
        }

        $vapid = [
            'subject' => $settings->get('vapid_subject', config('pwa-plugin.vapid_subject', '')),
            'publicKey' => $settings->get('vapid_public_key', config('pwa-plugin.vapid_public_key', '')),
            'privateKey' => $settings->get('vapid_private_key', config('pwa-plugin.vapid_private_key', '')),
        ];

        if (!$push->canSend()) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.library_missing'),
            ], 400);
        }

        if (!$vapid['publicKey'] || !$vapid['privateKey'] || !$vapid['subject']) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.vapid_missing'),
            ], 400);
        }

        $subscriptions = PwaPushSubscription::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())
            ->get();

        if ($subscriptions->isEmpty()) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.no_subscription'),
            ], 404);
        }

        $appName = config('app.name', 'Pelican');
        $icon = $this->assetOrUrl($settings->get('default_notification_icon', config('pwa-plugin.default_notification_icon', '/pelican.svg')));
        $badge = $this->assetOrUrl($settings->get('default_notification_badge', config('pwa-plugin.default_notification_badge', '/pelican.svg')));

        $payload = [
            'title' => $appName,
            'body' => trans('pwa-plugin::pwa-plugin.messages.test_notification_body'),
            'icon' => $icon,
            'badge' => $badge,
            'url' => url('/'),
            'tag' => 'pwa-test',
        ];

        $sent = 0;
        foreach ($subscriptions as $subscription) {
            if ($push->sendToSubscription($subscription, $payload, $vapid)) {
                $sent++;
            }
        }

        return response()->json([
            'message' => $sent > 0
                ? trans('pwa-plugin::pwa-plugin.notifications.test_sent')
                : trans('pwa-plugin::pwa-plugin.errors.send_failed'),
            'sent' => $sent,
            'total' => $subscriptions->count(),
        ], $sent > 0 ? 200 : 500);
    }

    private function resolveUser(Request $request): mixed
    {
        $user = $request->user();
        if ($user) {
            return $user;
        }

        $defaultGuard = config('auth.defaults.guard');
        if ($defaultGuard) {
            $user = Auth::guard($defaultGuard)->user();
            if ($user) {
                return $user;
            }
        }

        return null;
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

