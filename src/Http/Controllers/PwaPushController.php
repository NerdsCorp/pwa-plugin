<?php

namespace PwaPlugin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Carbon;
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

        $endpoint = $request->string('endpoint')->toString();
        $endpointHash = hash('sha256', $endpoint);

        $subscription = PwaPushSubscription::query()->updateOrCreate(
            [
                'endpoint' => $endpoint,
                'notifiable_type' => $user->getMorphClass(),
                'notifiable_id' => $user->getKey(),
            ],
            [
                'endpoint_hash' => $endpointHash,
                'public_key' => $request->input('keys.p256dh'),
                'auth_token' => $request->input('keys.auth'),
                'content_encoding' => $request->input('contentEncoding') ?? 'aesgcm',
                'user_agent' => $request->userAgent(),
                'last_synced_at' => now(),
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

        $endpoint = $request->string('endpoint')->toString();
        $endpointHash = hash('sha256', $endpoint);

        PwaPushSubscription::query()
            ->where('endpoint_hash', $endpointHash)
            ->where('endpoint', $endpoint)
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

    public function sync(Request $request, PwaSettingsRepository $settings): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.unauthorized'),
            ], 401);
        }

        if (!method_exists($user, 'notifications')) {
            return response()->json([
                'notifications' => [],
                'count' => 0,
                'server_time' => now()->toISOString(),
            ]);
        }

        if (Schema::hasTable('pwa_push_subscriptions') && method_exists($user, 'getMorphClass')) {
            PwaPushSubscription::query()
                ->where('notifiable_type', $user->getMorphClass())
                ->where('notifiable_id', $user->getKey())
                ->update(['last_synced_at' => now()]);
        }

        $since = trim((string) $request->query('since', ''));
        $limit = max(1, min((int) $request->query('limit', 20), 50));

        $query = $user->notifications()
            ->select(['id', 'data', 'created_at'])
            ->orderBy('created_at', 'desc');

        if ($since !== '') {
            try {
                $query->where('created_at', '>', Carbon::parse($since));
            } catch (\Throwable) {
                // Ignore invalid timestamps and continue with latest notifications.
            }
        }

        $icon = $this->assetOrUrl($settings->get('default_notification_icon', config('pwa-plugin.default_notification_icon', '/pelican.svg')));
        $badge = $this->assetOrUrl($settings->get('default_notification_badge', config('pwa-plugin.default_notification_badge', '/pelican.svg')));
        $defaultTitle = config('app.name', 'Pelican');
        $defaultBody = trans('pwa-plugin::pwa-plugin.messages.new_notification');

        $notifications = $query
            ->limit($limit)
            ->get()
            ->reverse()
            ->map(function ($notification) use ($defaultTitle, $defaultBody, $icon, $badge): array {
                $data = is_array($notification->data) ? $notification->data : [];

                return [
                    'id' => (string) $notification->id,
                    'title' => $data['title'] ?? $data['subject'] ?? $defaultTitle,
                    'body' => $data['body'] ?? $data['message'] ?? $defaultBody,
                    'url' => $data['url'] ?? $data['action_url'] ?? url('/'),
                    'icon' => $data['icon'] ?? $icon,
                    'badge' => $data['badge'] ?? $badge,
                    'tag' => $data['tag'] ?? ('sync-' . $notification->id),
                    'requireInteraction' => (bool) ($data['require_interaction'] ?? false),
                    'actions' => is_array($data['actions'] ?? null) ? $data['actions'] : [],
                    'created_at' => optional($notification->created_at)?->toISOString(),
                ];
            })
            ->values()
            ->all();

        $syncPoint = now()->toISOString();
        if (!empty($notifications)) {
            $last = $notifications[array_key_last($notifications)] ?? null;
            if (is_array($last) && !empty($last['created_at'])) {
                $syncPoint = (string) $last['created_at'];
            }
        }

        return response()->json([
            'notifications' => $notifications,
            'count' => count($notifications),
            'server_time' => now()->toISOString(),
            'sync_point' => $syncPoint,
        ]);
    }

    public function diagnostics(Request $request, PwaSettingsRepository $settings, PwaPushService $push): JsonResponse
    {
        $user = $this->resolveUser($request);
        if (!$user) {
            return response()->json([
                'message' => trans('pwa-plugin::pwa-plugin.errors.unauthorized'),
            ], 401);
        }

        $queueConnection = (string) config('queue.default', 'sync');
        $queueConfigured = is_array(config("queue.connections.{$queueConnection}"));
        $queueBackground = !in_array($queueConnection, ['sync', 'null'], true);

        $vapid = [
            'subject' => (string) $settings->get('vapid_subject', config('pwa-plugin.vapid_subject', '')),
            'public' => (string) $settings->get('vapid_public_key', config('pwa-plugin.vapid_public_key', '')),
            'private' => (string) $settings->get('vapid_private_key', config('pwa-plugin.vapid_private_key', '')),
        ];

        $pushEnabled = (bool) $settings->get('push_enabled', config('pwa-plugin.push_enabled', false));
        $pushLibraryAvailable = $push->canSend();
        $vapidConfigured = $vapid['subject'] !== '' && $vapid['public'] !== '' && $vapid['private'] !== '';

        $globalSubscriptions = 0;
        $globalUsers = 0;
        $lastPushAt = null;
        $lastSyncAt = null;
        $lastSubscriptionRefreshAt = null;

        if (Schema::hasTable('pwa_push_subscriptions')) {
            $globalSubscriptions = PwaPushSubscription::query()->count();
            $globalUsers = PwaPushSubscription::query()
                ->select(['notifiable_type', 'notifiable_id'])
                ->distinct()
                ->get()
                ->count();

            if (Schema::hasColumn('pwa_push_subscriptions', 'last_push_sent_at')) {
                $lastPushAt = $this->isoFromDatabaseValue(
                    PwaPushSubscription::query()->max('last_push_sent_at')
                );
            }

            if (Schema::hasColumn('pwa_push_subscriptions', 'last_synced_at')) {
                $lastSyncAt = $this->isoFromDatabaseValue(
                    PwaPushSubscription::query()->max('last_synced_at')
                );
            }

            $lastSubscriptionRefreshAt = $this->isoFromDatabaseValue(
                PwaPushSubscription::query()->max('updated_at')
            );
        }

        return response()->json([
            'queue' => [
                'connection' => $queueConnection,
                'configured' => $queueConfigured,
                'background' => $queueBackground,
                'ready' => $queueConfigured,
            ],
            'push' => [
                'enabled' => $pushEnabled,
                'library_available' => $pushLibraryAvailable,
                'vapid_configured' => $vapidConfigured,
                'ready' => $pushEnabled && $pushLibraryAvailable && $vapidConfigured,
            ],
            'usage' => [
                'pwa_users' => $globalUsers,
                'subscriptions' => $globalSubscriptions,
            ],
            'activity' => [
                'last_push_sent_at' => $lastPushAt,
                'last_sync_at' => $lastSyncAt,
                'last_subscription_refresh_at' => $lastSubscriptionRefreshAt,
            ],
            'server_time' => now()->toISOString(),
        ]);
    }

    private function isoFromDatabaseValue(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->toISOString();
        } catch (\Throwable) {
            return null;
        }
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
