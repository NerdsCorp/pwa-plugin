<?php

namespace PwaPlugin;

use App\Enums\TabPosition;
use Filament\Contracts\Plugin as PluginContract;
use Filament\Panel;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use PwaPlugin\Filament\Pages\PwaSettings;
use PwaPlugin\Services\PwaActions;
use PwaPlugin\Services\PwaSettingsRepository;

class PwaPlugin implements PluginContract
{
    public function getId(): string
    {
        return 'pwa-plugin';
    }

    public function register(Panel $panel): void
    {
        View::addNamespace('pwa-plugin', __DIR__ . '/../resources/views');

        $this->registerHeadHook($panel);

        $panel->pages([
            PwaSettings::class,
        ]);
    }

    public function boot(Panel $panel): void
    {
        $this->registerProfileCustomizationTab();
    }

    private function registerHeadHook(Panel $panel): void
    {
        $panel->renderHook(
            'panels::head.end',
            fn (): HtmlString => $this->getPwaHeadHtml(),
        );
    }

    private function getPwaHeadHtml(): HtmlString
    {
        $settings = app(PwaSettingsRepository::class);
        $settings->ensureVapidKeys();
        $appName = config('app.name', 'Pelican Panel');

        $themeColor = $settings->get('theme_color', config('pwa-plugin.theme_color', '#0ea5e9'));

        $appleDefault = $this->assetOrUrl($settings->get('apple_touch_icon', config('pwa-plugin.apple_touch_icon', '/pelican.svg')));
        $apple152 = $this->assetOrUrl($settings->get('apple_touch_icon_152', config('pwa-plugin.apple_touch_icon_152', $appleDefault)));
        $apple167 = $this->assetOrUrl($settings->get('apple_touch_icon_167', config('pwa-plugin.apple_touch_icon_167', $appleDefault)));
        $apple180 = $this->assetOrUrl($settings->get('apple_touch_icon_180', config('pwa-plugin.apple_touch_icon_180', $appleDefault)));

        $appNameEsc = e($appName);
        $themeColorEsc = e($themeColor);
        $appleDefaultEsc = e($appleDefault);
        $apple152Esc = e($apple152);
        $apple167Esc = e($apple167);
        $apple180Esc = e($apple180);

        $vapidPublicKey = json_encode($settings->get('vapid_public_key', config('pwa-plugin.vapid_public_key')));
        $pushEnabled = json_encode($settings->get('push_enabled', config('pwa-plugin.push_enabled', false)));

        $langUpdateAvailable = json_encode(trans('pwa-plugin::pwa-plugin.messages.update_available'));
        $langInstallAlready = json_encode(trans('pwa-plugin::pwa-plugin.errors.install_already'));

        $html = <<<HTML
        <meta name="application-name" content="{$appNameEsc}">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="{$appNameEsc}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="theme-color" content="{$themeColorEsc}">

        <link rel="manifest" href="/manifest.json">
        <link rel="apple-touch-icon" href="{$appleDefaultEsc}">
        <link rel="apple-touch-icon" sizes="152x152" href="{$apple152Esc}">
        <link rel="apple-touch-icon" sizes="180x180" href="{$apple180Esc}">
        <link rel="apple-touch-icon" sizes="167x167" href="{$apple167Esc}">

        <script>
        window.pwaConfig = {
            vapidPublicKey: {$vapidPublicKey},
            pushEnabled: {$pushEnabled},
            routes: {
                subscribe: "/pwa/subscribe",
                unsubscribe: "/pwa/unsubscribe",
                test: "/pwa/test",
            },
            lang: {
                updateAvailable: {$langUpdateAvailable},
                installAlready: {$langInstallAlready}
            }
        };

        function pwaCsrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
        }

        window.pwaRequestNotifications = function() {
            if (!('Notification' in window)) return Promise.resolve('unsupported');
            return Notification.requestPermission();
        };

        function pwaUrlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            const outputArray = new Uint8Array(rawData.length);
            for (let i = 0; i < rawData.length; ++i) outputArray[i] = rawData.charCodeAt(i);
            return outputArray;
        }

        window.pwaRegisterPush = async function() {
            if (!window.pwaConfig?.pushEnabled || !window.pwaConfig?.vapidPublicKey) return null;
            const subscription = await window.pwaSubscribePush(window.pwaConfig.vapidPublicKey);
            if (!subscription) return null;

            const response = await fetch(window.pwaConfig.routes.subscribe, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': pwaCsrfToken() },
                body: JSON.stringify(subscription),
            });
            return response.ok ? subscription : null;
        };

        window.pwaUnregisterPush = async function() {
            const reg = await navigator.serviceWorker.ready;
            const sub = await reg.pushManager.getSubscription();
            if (!sub) return false;
            await sub.unsubscribe();
            await fetch(window.pwaConfig.routes.unsubscribe, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': pwaCsrfToken() },
                body: JSON.stringify({ endpoint: sub.endpoint }),
            });
            return true;
        };

        window.pwaSendTestPush = () => {
            return fetch(window.pwaConfig.routes.test, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': pwaCsrfToken() }
            });
        };

        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/service-worker.js').then(reg => {
                    reg.addEventListener('updatefound', () => {
                        const newWorker = reg.installing;
                        newWorker.addEventListener('statechange', () => {
                            if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                if (confirm(window.pwaConfig.lang.updateAvailable)) window.location.reload();
                            }
                        });
                    });
                });
            });
        }


        let deferredPrompt;
        window.addEventListener('beforeinstallprompt', e => {
            e.preventDefault();
            deferredPrompt = e;
            window.pwaCanInstall = true;
        });

        window.addEventListener('appinstalled', () => {
            deferredPrompt = null;
            window.pwaCanInstall = false;
        });

        window.triggerPwaInstall = () => {
            const isStandalone = window.matchMedia?.('(display-mode: standalone)')?.matches || navigator.standalone;
            if (isStandalone) {
                alert(window.pwaConfig?.lang?.installAlready || 'The app is already installed.');
                return true;
            }
            if (deferredPrompt) {
                deferredPrompt.prompt();
                deferredPrompt.userChoice.then(() => { deferredPrompt = null; });
                return true;
            }
            return false;
        };

        window.pwaSubscribePush = async function(publicKey) {
            if (!('serviceWorker' in navigator) || !('PushManager' in window)) return null;
            const reg = await navigator.serviceWorker.ready;
            const permission = await window.pwaRequestNotifications();
            if (permission !== 'granted') return null;
            return await reg.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: pwaUrlBase64ToUint8Array(publicKey)
            });
        };
        </script>
HTML;

        return new HtmlString($html);
    }

    private function registerProfileCustomizationTab(): void
    {
        if (!class_exists(\App\Filament\Pages\Auth\EditProfile::class)) {
            return;
        }

        if (!enum_exists(TabPosition::class) || !class_exists(Tab::class) || !class_exists(Section::class)) {
            return;
        }

        \App\Filament\Pages\Auth\EditProfile::registerCustomTabs(
            TabPosition::After,
            Tab::make('pwa')
                ->label(trans('pwa-plugin::pwa-plugin.profile.tab_label'))
                ->icon('heroicon-o-device-phone-mobile')
                ->schema([
                    Section::make(trans('pwa-plugin::pwa-plugin.profile.section_heading'))
                        ->description(trans('pwa-plugin::pwa-plugin.profile.section_description'))
                        ->schema([
                            PwaActions::make(),
                        ]),
                ]),
        );
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


