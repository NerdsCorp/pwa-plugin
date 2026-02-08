<?php

namespace PwaPlugin\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

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
            'description' => 'Game server management panel',
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
                    'purpose' => 'any maskable'
                ],
                [
                    'src' => $this->assetOrUrl($icon512),
                    'sizes' => '512x512',
                    'type' => $this->iconMime($icon512),
                    'purpose' => 'any maskable'
                ]
            ],
            'categories' => ['utilities', 'productivity'],
            'shortcuts' => [
                [
                    'name' => 'Dashboard',
                    'short_name' => 'Dashboard',
                    'description' => 'View your servers',
                    'url' => url('/app'),
                    'icons' => [
                        [
                            'src' => $this->assetOrUrl($icon192),
                            'sizes' => '192x192'
                        ]
                    ]
                ]
            ]
        ];

        return response()->json($manifest)
            ->header('Content-Type', 'application/manifest+json');
    }

    public function serviceWorker(): Response
    {
        $cacheName = (string) $this->setting('cache_name', 'pelican-pwa-v1');
        $cacheVersion = (int) $this->setting('cache_version', 1);

        $serviceWorker = <<<'JS'
const CACHE_NAME = '__CACHE_NAME__';
const CACHE_VERSION = __CACHE_VERSION__;

// Install event - minimal setup
self.addEventListener('install', (event) => {
    console.log('PWA: Service Worker installing');
    self.skipWaiting();
});

// Activate event
self.addEventListener('activate', (event) => {
    console.log('PWA: Service Worker activating');
    event.waitUntil(self.clients.claim());
});

// Push notification handler
self.addEventListener('push', (event) => {
    let data = {};
    
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            data = { title: 'Notification', body: event.data.text() };
        }
    }

    const title = data.title || 'Pelican Panel';
    const options = {
        body: data.body || 'You have a new notification',
        icon: data.icon || '/pelican.svg',
        badge: data.badge || '/pelican.svg',
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

// Background sync (optional - for future use)
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-notifications') {
        event.waitUntil(syncNotifications());
    }
});

async function syncNotifications() {
    // Placeholder for future notification sync
    console.log('PWA: Background sync triggered');
}
JS;

        $serviceWorker = str_replace(
            ['__CACHE_NAME__', '__CACHE_VERSION__'],
            [addslashes($cacheName), (string) $cacheVersion],
            $serviceWorker
        );

        return response($serviceWorker)
            ->header('Content-Type', 'application/javascript')
            ->header('Service-Worker-Allowed', '/');
    }

    private function setting(string $key, mixed $default = null): mixed
    {
        return app(\PwaPlugin\Services\PwaSettingsRepository::class)->get($key, config('pwa.' . $key, $default));
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
}
