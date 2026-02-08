<?php

namespace PwaPlugin;

use Filament\Contracts\Plugin as PluginContract;
use Filament\Facades\Filament;
use Filament\Panel;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use App\Enums\TabPosition;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\View as SchemaView;
use PwaPlugin\Filament\Pages\PwaSettings;
use PwaPlugin\Http\Controllers\PwaController;
use PwaPlugin\Listeners\SendPwaPushOnDatabaseNotification;
use Illuminate\Notifications\Events\NotificationSent;

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

        $hooks = [
            'panels::profile.tabs.customization.after',
            'panels::profile.tab.customization.after',
            'panels::profile.customization.after',
            'panels::profile.after',
        ];

        foreach ($hooks as $hook) {
            $panel->renderHook($hook, fn () => view('pwa-plugin::pwa-quick-actions'));
        }

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
            Route::get('/manifest.json', [PwaController::class, 'manifest'])
                ->name('pwa.manifest');

            Route::get('/service-worker.js', [PwaController::class, 'serviceWorker'])
                ->name('pwa.sw');

            Route::post('/pwa/subscribe', [\PwaPlugin\Http\Controllers\PwaPushController::class, 'subscribe'])
                ->name('pwa.subscribe');

            Route::post('/pwa/unsubscribe', [\PwaPlugin\Http\Controllers\PwaPushController::class, 'unsubscribe'])
                ->name('pwa.unsubscribe');

            Route::post('/pwa/test', [\PwaPlugin\Http\Controllers\PwaPushController::class, 'test'])
                ->name('pwa.test');
        });
    }

    private function registerHeadHook(Panel $panel): void
    {
        $panel->renderHook(
            'panels::head.end',
            fn () => view('pwa-plugin::pwa-head')
        );
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
                ->label('PWA')
                ->schema([
                    Section::make('PWA')
                        ->schema([
                            SchemaView::make('pwa-plugin::pwa-quick-actions'),
                        ]),
                ])
        );
    }
}
