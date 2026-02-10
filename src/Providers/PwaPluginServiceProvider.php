<?php

namespace PwaPlugin\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\ServiceProvider;
use PwaPlugin\Listeners\SendPwaPushOnDatabaseNotification;

class PwaPluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
        $this->loadViewsFrom(__DIR__ . '/../../resources/views', 'pwa-plugin');

        Event::listen(NotificationSent::class, SendPwaPushOnDatabaseNotification::class);
    }
}
