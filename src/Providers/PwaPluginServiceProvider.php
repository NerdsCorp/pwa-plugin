<?php

namespace PwaPlugin\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Notifications\Events\NotificationSent;
use PwaPlugin\Http\Controllers\PwaController;
use PwaPlugin\Http\Controllers\PwaPushController;
use PwaPlugin\Listeners\SendPwaPushOnDatabaseNotification;

class PwaPluginServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../../config/pwa.php', 'pwa');
    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'pwa-plugin');

        $this->registerRoutes();

        Event::listen(NotificationSent::class, SendPwaPushOnDatabaseNotification::class);
    }

    private function registerRoutes(): void
    {
        if (Route::has('pwa.manifest')) {
            return;
        }

        Route::middleware('web')->group(function () {
            Route::get('/manifest.json', [PwaController::class, 'manifest'])
                ->name('pwa.manifest');

            Route::get('/service-worker.js', [PwaController::class, 'serviceWorker'])
                ->name('pwa.sw');

            Route::post('/pwa/subscribe', [PwaPushController::class, 'subscribe'])
                ->name('pwa.subscribe');

            Route::post('/pwa/unsubscribe', [PwaPushController::class, 'unsubscribe'])
                ->name('pwa.unsubscribe');

            Route::post('/pwa/test', [PwaPushController::class, 'test'])
                ->name('pwa.test');
        });
    }
}
