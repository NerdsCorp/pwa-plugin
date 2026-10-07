<?php

namespace PwaPlugin\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use PwaPlugin\Http\Controllers\PwaApplicationPushController;
use PwaPlugin\Http\Controllers\PwaController;
use PwaPlugin\Http\Controllers\PwaPushController;

class PwaPluginRouteServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->routes(function (): void {
            if (!Route::has('pwa.manifest')) {
                Route::middleware('web')->group(function () {
                    Route::get('/manifest.json', [PwaController::class, 'manifest'])->name('pwa.manifest');
                });
            }

            if (!Route::has('pwa.sw')) {
                Route::middleware('web')->group(function () {
                    Route::get('/service-worker.js', [PwaController::class, 'serviceWorker'])->name('pwa.sw');
                });
            }

            if (!Route::has('pwa.subscribe')) {
                Route::middleware(['web', 'auth', 'throttle:60,1'])->group(function () {
                    Route::post('/pwa/subscribe', [PwaPushController::class, 'subscribe'])->name('pwa.subscribe');
                });
            }

            if (!Route::has('pwa.unsubscribe')) {
                Route::middleware(['web', 'auth', 'throttle:60,1'])->group(function () {
                    Route::post('/pwa/unsubscribe', [PwaPushController::class, 'unsubscribe'])->name('pwa.unsubscribe');
                });
            }

            if (!Route::has('pwa.test')) {
                Route::middleware(['web', 'auth', 'throttle:10,1'])->group(function () {
                    Route::post('/pwa/test', [PwaPushController::class, 'test'])->name('pwa.test');
                });
            }

            if (!Route::has('pwa.sync')) {
                Route::middleware(['web', 'auth', 'throttle:30,1'])->group(function () {
                    Route::get('/pwa/sync', [PwaPushController::class, 'sync'])->name('pwa.sync');
                });
            }

            if (!Route::has('pwa.diagnostics')) {
                Route::middleware(['web', 'auth', 'throttle:30,1'])->group(function () {
                    Route::get('/pwa/diagnostics', [PwaPushController::class, 'diagnostics'])->name('pwa.diagnostics');
                });
            }

            Route::middleware(['web', 'auth', 'throttle:60,1'])->group(function () {
                if (!Route::has('pwa.devices.index')) {
                    Route::get('/pwa/devices', [PwaPushController::class, 'devices'])->name('pwa.devices.index');
                }
                if (!Route::has('pwa.devices.rename')) {
                    Route::patch('/pwa/devices/{subscription}', [PwaPushController::class, 'renameDevice'])->name('pwa.devices.rename');
                }
                if (!Route::has('pwa.devices.remove')) {
                    Route::delete('/pwa/devices/{subscription}', [PwaPushController::class, 'removeDevice'])->name('pwa.devices.remove');
                }
            });

            Route::middleware(['api', 'application-api', 'throttle:api.application'])
                ->prefix('/api/application/pwa')
                ->scopeBindings()
                ->group(function (): void {
                    if (!Route::has('pwa.application.push.user')) {
                        Route::post('/users/{user:id}/notifications', [PwaApplicationPushController::class, 'sendToUser'])
                            ->name('pwa.application.push.user');
                    }

                    if (!Route::has('pwa.application.push.broadcast')) {
                        Route::post('/notifications/broadcast', [PwaApplicationPushController::class, 'broadcast'])
                            ->name('pwa.application.push.broadcast');
                    }
                });
        });
    }
}
