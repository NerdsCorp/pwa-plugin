<?php

namespace PwaPlugin;

use Filament\Contracts\Plugin as PluginContract;
use Filament\Facades\Filament;
use Filament\Panel;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\HtmlString;
use Illuminate\Notifications\Events\NotificationSent;
use App\Enums\TabPosition;
use PwaPlugin\Filament\Pages\PwaSettings;
use PwaPlugin\Http\Controllers\PwaController;
use PwaPlugin\Http\Controllers\PwaPushController;
use PwaPlugin\Listeners\SendPwaPushOnDatabaseNotification;
use PwaPlugin\Services\PwaSettingsRepository;
use PwaPlugin\Services\PwaActions;

class PwaPlugin implements PluginContract
{
    private static bool $routesRegistered = false;

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
        $this->registerRoutes();
        Event::listen(NotificationSent::class, SendPwaPushOnDatabaseNotification::class);

        $this->registerProfileCustomizationTab();
        $this->registerHeadHookForAllPanels();
    }

    private function registerRoutes(): void
    {
        if (self::$routesRegistered || Route::has('pwa.manifest')) {
            return;
        }

        self::$routesRegistered = true;

        Route::middleware('web')->group(function () {
            Route::get('/manifest.json', [PwaController::class, 'manifest'])->name('pwa.manifest');
            Route::get('/service-worker.js', [PwaController::class, 'serviceWorker'])->name('pwa.sw');
            Route::post('/pwa/subscribe', [PwaPushController::class, 'subscribe'])->name('pwa.subscribe');
            Route::post('/pwa/unsubscribe', [PwaPushController::class, 'unsubscribe'])->name('pwa.unsubscribe');
            Route::post('/pwa/test', [PwaPushController::class, 'test'])->name('pwa.test');
        });
    }

    private function registerHeadHook(Panel $panel): void
    {
        $panel->renderHook(
            'panels::head.end',
            fn (): HtmlString => $this->getPwaHeadHtml()
        );
    }

    private function getPwaHeadHtml(): HtmlString
    {
        $settings = app(PwaSettingsRepository::class);
        $appName = config('app.name', 'Pelican Panel');
        
        $themeColor = $settings->get('theme_color', config('pwa.theme_color', '#0ea5e9'));
        
        $appleDefault = asset(ltrim($settings->get('apple_touch_icon', config('pwa.apple_touch_icon', '/pelican.svg')), '/'));
        $apple152 = asset(ltrim($settings->get('apple_touch_icon_152', config('pwa.apple_touch_icon_152', $appleDefault)), '/'));
        $apple167 = asset(ltrim($settings->get('apple_touch_icon_167', config('pwa.apple_touch_icon_167', $appleDefault)), '/'));
        $apple180 = asset(ltrim($settings->get('apple_touch_icon_180', config('pwa.apple_touch_icon_180', $appleDefault)), '/'));

        $vapidPublicKey = json_encode($settings->get('vapid_public_key', config('pwa.vapid_public_key')));
        $pushEnabled = json_encode($settings->get('push_enabled', config('pwa.push_enabled', false)));
        
        $langUpdateAvailable = json_encode(trans('pwa-plugin::pwa-plugin.messages.update_available'));

        $html = <<<HTML
        <meta name="application-name" content="{$appName}">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
        <meta name="apple-mobile-web-app-title" content="{$appName}">
        <meta name="mobile-web-app-capable" content="yes">
        <meta name="theme-color" content="{$themeColor}">

        <link rel="manifest" href="/manifest.json">
        <link rel="apple-touch-icon" href="{$appleDefault}">
        <link rel="apple-touch-icon" sizes="152x152" href="{$apple152}">
        <link rel="apple-touch-icon" sizes="180x180" href="{$apple180}">
        <link rel="apple-touch-icon" sizes="167x167" href="{$apple167}">

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
                updateAvailable: {$langUpdateAvailable}
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
        });

        window.triggerPwaInstall = () => {
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

    private function registerHeadHookForAllPanels(): void
    {
        if (!class_exists(Filament::class) || !method_exists(Filament::class, 'getPanels')) {
            return;
        }

        foreach (Filament::getPanels() as $panel) {
            $this->registerHeadHook($panel);
        }
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
                ])
        );
    }
}