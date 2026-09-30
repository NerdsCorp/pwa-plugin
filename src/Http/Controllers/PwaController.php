<?php

namespace PwaPlugin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use PwaPlugin\Services\PwaSettingsRepository;

class PwaController extends Controller
{
    public function manifest(): JsonResponse
    {
        $appName = config('app.name', 'Pelican Panel');
        $themeColor = $this->setting('theme_color', '#0ea5e9');
        $backgroundColor = $this->setting('background_color', '#0f172a');
        $startUrl = $this->startUrl();

        $icon192 = $this->setting('manifest_icon_192', '/pelican.svg');
        $icon512 = $this->setting('manifest_icon_512', '/pelican.svg');

        $manifest = [
            'name' => $appName,
            'short_name' => $appName,
            'description' => trans('pwa-plugin::pwa-plugin.manifest.description'),
            'start_url' => $startUrl,
            'scope' => '/',
            'display' => 'standalone',
            'background_color' => $backgroundColor,
            'theme_color' => $themeColor,
            'orientation' => 'portrait-primary',
            'icons' => [
                [
                    'src' => $this->assetOrUrl($icon192),
                    'sizes' => '192x192',
                    'type' => $this->iconMime($icon192),
                    'purpose' => 'any maskable',
                ],
                [
                    'src' => $this->assetOrUrl($icon512),
                    'sizes' => '512x512',
                    'type' => $this->iconMime($icon512),
                    'purpose' => 'any maskable',
                ],
            ],
            'categories' => ['utilities', 'productivity'],
            'shortcuts' => [
                [
                    'name' => trans('pwa-plugin::pwa-plugin.manifest.shortcuts.dashboard_name'),
                    'short_name' => trans('pwa-plugin::pwa-plugin.manifest.shortcuts.dashboard_short'),
                    'description' => trans('pwa-plugin::pwa-plugin.manifest.shortcuts.dashboard_description'),
                    'url' => url('/'),
                    'icons' => [
                        [
                            'src' => $this->assetOrUrl($icon192),
                            'sizes' => '192x192',
                        ],
                    ],
                ],
            ],
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }

    public function serviceWorker(): Response
    {
        $cacheName = (string) $this->setting('cache_name', 'pelican-pwa-v1');
        $cacheVersion = (int) $this->setting('cache_version', 1);
        $cacheEnabled = (bool) $this->setting('cache_enabled', false);
        $precacheUrls = $this->parsePrecacheUrls((string) $this->setting('cache_precache_urls', ''));

        // Dynamische Texte für den SW (Hardcoded Texte ersetzt)
        $swDefaultTitle = config('app.name', 'Pelican Panel');
        $swDefaultBody = trans('pwa-plugin::pwa-plugin.messages.new_notification');
        $swDefaultIcon = $this->setting('default_notification_icon', '/pelican.svg');
        $swSyncRoute = '/pwa/sync';

        $serviceWorker = <<<'JS'
const CACHE_NAME = '__CACHE_NAME__';
const CACHE_VERSION = __CACHE_VERSION__;
const CACHE_SCHEMA_VERSION = 2;
const CACHE_ENABLED = __CACHE_ENABLED__;
const PRECACHE_URLS = __PRECACHE_URLS__;
const DEFAULT_TITLE = '__DEFAULT_TITLE__';
const DEFAULT_BODY = '__DEFAULT_BODY__';
const DEFAULT_ICON = '__DEFAULT_ICON__';
const SYNC_ROUTE = '__SYNC_ROUTE__';
const SYNC_STATE_CACHE = `${CACHE_NAME}:sync-state`;
const SYNC_STATE_KEY = '/__pwa_sync_state__';
const PAGE_CACHE = `${CACHE_NAME}:${CACHE_VERSION}:${CACHE_SCHEMA_VERSION}`;

// Install event - minimal setup
self.addEventListener('install', (event) => {
    console.log('PWA: Service Worker installing');
    event.waitUntil((async () => {
        if (CACHE_ENABLED && Array.isArray(PRECACHE_URLS) && PRECACHE_URLS.length > 0) {
            const cache = await caches.open(PAGE_CACHE);
            for (const resourceUrl of PRECACHE_URLS) {
                try {
                    const request = new Request(resourceUrl, { credentials: 'omit' });
                    const response = await fetch(request);
                    const contentType = response.headers.get('Content-Type') || '';
                    const cacheControl = response.headers.get('Cache-Control') || '';
                    if (response.ok
                        && /^(text\/css|application\/javascript|text\/javascript|image\/)/i.test(contentType)
                        && !/private|no-store/i.test(cacheControl)
                        && !response.headers.has('Set-Cookie')) {
                        await cache.put(request, response);
                    }
                } catch (_e) {
                    // Skip resources that cannot be safely cached.
                }
            }
        }
        self.skipWaiting();
    })());
});

// Activate event
self.addEventListener('activate', (event) => {
    console.log('PWA: Service Worker activating');
    event.waitUntil((async () => {
        const keys = await caches.keys();
        await Promise.all(keys
            .filter(key => key.startsWith(CACHE_NAME)
                && key !== SYNC_STATE_CACHE
                && (!CACHE_ENABLED || key !== PAGE_CACHE))
            .map(key => caches.delete(key)));
        await self.clients.claim();
    })());
});

// Push notification handler
self.addEventListener('push', (event) => {
    let data = {};

    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = { title: DEFAULT_TITLE, body: event.data.text() };
        }
    }

    const title = data.title || DEFAULT_TITLE;
    const options = {
        body: data.body || DEFAULT_BODY,
        icon: data.icon || DEFAULT_ICON,
        badge: data.badge || DEFAULT_ICON,
        vibrate: [200, 100, 200],
        data: data.url || '/',
        actions: data.actions || [],
        tag: data.tag || 'default',
        requireInteraction: data.requireInteraction || false
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    const urlToOpen = event.notification.data || '/';
    const targetUrl = new URL(urlToOpen, self.location.origin).href;

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true })
            .then((windowClients) => {
                // Check if there's already a window open
                for (let client of windowClients) {
                    if (client.url === targetUrl && 'focus' in client) {
                        return client.focus();
                    }
                }
                // Otherwise open new window
                if (clients.openWindow) {
                    return clients.openWindow(targetUrl);
                }
            })
    );
});

// Basic runtime caching (optional)
self.addEventListener('fetch', (event) => {
    if (!CACHE_ENABLED) return;
    if (event.request.method !== 'GET') return;

    const url = new URL(event.request.url);
    if (url.origin !== self.location.origin) return;
    if (url.pathname.startsWith('/pwa/')) return;
    if (url.pathname.startsWith('/api/')) return;

    const accept = event.request.headers.get('accept') || '';
    if (accept.includes('text/html')) return;
    if (!accept.includes('text/css')
        && !accept.includes('application/javascript')
        && !accept.includes('image/')) {
        return;
    }

    event.respondWith((async () => {
        const cache = await caches.open(PAGE_CACHE);
        const cached = await cache.match(event.request);
        if (cached) return cached;

        const response = await fetch(event.request);
        const cacheControl = response?.headers.get('Cache-Control') || '';
        const contentType = response?.headers.get('Content-Type') || '';
        if (response
            && response.status === 200
            && /^(text\/css|application\/javascript|text\/javascript|image\/)/i.test(contentType)
            && !/private|no-store/i.test(cacheControl)
            && !response.headers.has('Set-Cookie')) {
            cache.put(event.request, response.clone());
        }
        return response;
    })());
});

self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-notifications') {
        event.waitUntil(syncNotifications());
    }
});

// Periodic background sync (where supported)
self.addEventListener('periodicsync', (event) => {
    if (event.tag === 'sync-notifications') {
        event.waitUntil(syncNotifications());
    }
});

// Manual trigger from controlled pages
self.addEventListener('message', (event) => {
    if (event && event.data && event.data.type === 'PWA_SYNC_NOTIFICATIONS') {
        if (event.waitUntil) {
            event.waitUntil(syncNotifications());
        } else {
            syncNotifications();
        }
    }
});

async function readSyncState() {
    const cache = await caches.open(SYNC_STATE_CACHE);
    const response = await cache.match(SYNC_STATE_KEY);
    if (!response) return {};

    try {
        return await response.json();
    } catch (_e) {
        return {};
    }
}

async function writeSyncState(state) {
    const cache = await caches.open(SYNC_STATE_CACHE);
    await cache.put(
        SYNC_STATE_KEY,
        new Response(JSON.stringify(state), {
            headers: { 'Content-Type': 'application/json' },
        })
    );
}

async function syncNotifications() {
    try {
        const state = await readSyncState();
        const hasLastSync = state && typeof state.lastSync === 'string' && state.lastSync !== '';
        const since = hasLastSync ? `?since=${encodeURIComponent(state.lastSync)}&limit=20` : '?limit=20';
        const response = await fetch(`${SYNC_ROUTE}${since}`, {
            method: 'GET',
            credentials: 'same-origin',
            headers: { 'Accept': 'application/json' },
        });

        if (!response.ok) return;

        const payload = await response.json();
        const notifications = payload && Array.isArray(payload.notifications) ? payload.notifications : [];

        for (const item of notifications) {
            const title = item && item.title ? item.title : DEFAULT_TITLE;
            const options = {
                body: item && item.body ? item.body : DEFAULT_BODY,
                icon: item && item.icon ? item.icon : DEFAULT_ICON,
                badge: item && item.badge ? item.badge : DEFAULT_ICON,
                data: item && item.url ? item.url : '/',
                tag: item && item.tag ? item.tag : `sync-${(item && item.id) ? item.id : Date.now()}`,
                requireInteraction: Boolean(item && item.requireInteraction),
                actions: item && Array.isArray(item.actions) ? item.actions : [],
            };

            await self.registration.showNotification(title, options);
        }

        await writeSyncState({
            lastSync: (payload && (payload.sync_point || payload.server_time)) ? (payload.sync_point || payload.server_time) : new Date().toISOString(),
        });
    } catch (_e) {
        // Ignore sync failures and retry on next background trigger.
    }
}
JS;

        $serviceWorker = str_replace(
            ['__CACHE_NAME__', '__CACHE_VERSION__', '__CACHE_ENABLED__', '__PRECACHE_URLS__', '__DEFAULT_TITLE__', '__DEFAULT_BODY__', '__DEFAULT_ICON__', '__SYNC_ROUTE__'],
            [
                addslashes($cacheName),
                (string) $cacheVersion,
                $cacheEnabled ? 'true' : 'false',
                json_encode($precacheUrls, JSON_UNESCAPED_SLASHES),
                addslashes($swDefaultTitle),
                addslashes($swDefaultBody),
                addslashes($swDefaultIcon),
                addslashes($swSyncRoute),
            ],
            $serviceWorker
        );

        return response($serviceWorker)
            ->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', '/');
    }

    private function setting(string $key, mixed $default = null): mixed
    {
        return app(PwaSettingsRepository::class)->get($key, config('pwa-plugin.' . $key, $default));
    }

    private function startUrl(): string
    {
        $value = (string) $this->setting('start_url', '/');
        if ($value === '') {
            return url('/');
        }

        if (str_starts_with($value, 'http://') || str_starts_with($value, 'https://')) {
            return $value;
        }

        return url($value);
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

    private function iconMime(string $value): string
    {
        $lower = strtolower($value);
        if (str_ends_with($lower, '.svg')) {
            return 'image/svg+xml';
        }

        return 'image/png';
    }

    /** @return string[] */
    private function parsePrecacheUrls(string $raw): array
    {
        $parts = preg_split('/[\r\n,]+/', $raw) ?: [];
        $urls = array_filter(array_map('trim', $parts), fn ($value) => $value !== '');

        return array_values(array_unique($urls));
    }
}
