<?php

namespace PwaPlugin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use PwaPlugin\Models\PwaPushSubscription;
use PwaPlugin\Services\PwaSettingsRepository;
use PwaPlugin\Services\PwaPushService;

class PwaPushController extends Controller
{
    public function subscribe(Request $request): JsonResponse
    {
        if (!Schema::hasTable('pwa_push_subscriptions')) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.table_missing')
            ], 500);
        }

        $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'contentEncoding' => ['nullable', 'string'],
        ]);

        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.unauthorized')
            ], 401);
        }

        $subscription = PwaPushSubscription::query()->updateOrCreate(
            ['endpoint' => $request->string('endpoint')->toString()],
            [
                'notifiable_type' => $user->getMorphClass(),
                'notifiable_id' => $user->getKey(),
                'public_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
                'content_encoding' => $request->input('contentEncoding', 'aesgcm'),
                'user_agent' => $request->userAgent(),
            ]
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
                'message' => trans('pwa-plugin::pwa-plugin.errors.table_missing')
            ], 500);
        }

        $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.unauthorized')
            ], 401);
        }

        PwaPushSubscription::query()
            ->where('endpoint', $request->string('endpoint')->toString())
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())
            ->delete();

        return response()->json([
            'message' => trans('pwa-plugin::pwa-plugin.notifications.unsubscribed')
        ]);
    }

    public function test(Request $request, PwaSettingsRepository $settings, PwaPushService $push): JsonResponse
    {
        if (!Schema::hasTable('pwa_push_subscriptions')) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.table_missing')
            ], 500);
        }

        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.unauthorized')
            ], 401);
        }

        $vapid = [
            'subject' => $settings->get('vapid_subject', config('pwa.vapid_subject', '')),
            'publicKey' => $settings->get('vapid_public_key', config('pwa.vapid_public_key', '')),
            'privateKey' => $settings->get('vapid_private_key', config('pwa.vapid_private_key', '')),
        ];

        if (!$push->canSend()) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.library_missing')
            ], 400);
        }

        if (!$vapid['publicKey'] || !$vapid['privateKey'] || !$vapid['subject']) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.vapid_missing')
            ], 400);
        }

        $subscription = PwaPushSubscription::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())
            ->latest('id')
            ->first();

        if (!$subscription) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.no_subscription')
            ], 404);
        }

        $appName = config('app.name', 'Pelican');
        $icon = asset(ltrim($settings->get('default_notification_icon', config('pwa.default_notification_icon', '/pelican.svg')), '/'));
        $badge = asset(ltrim($settings->get('default_notification_badge', config('pwa.default_notification_badge', '/pelican.svg')), '/'));

        $payload = [
            'title' => $appName,
            'body' => trans('pwa-plugin::pwa-plugin.messages.test_notification_body'),
            'icon' => $icon,
            'badge' => $badge,
            'url' => url('/'),
            'tag' => 'pwa-test',
        ];

        $ok = $push->sendToSubscription($subscription, $payload, $vapid);

        return response()->json([
            'message' => $ok 
                ? trans('pwa-plugin::pwa-plugin.notifications.test_sent') 
                : trans('pwa-plugin::pwa-plugin.errors.send_failed'),
        ], $ok ? 200 : 500);
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

        foreach (array_keys(config('auth.guards', [])) as $guard) {
            $user = Auth::guard($guard)->user();
            if ($user) {
                return $user;
            }
        }

        return null;
    }
}