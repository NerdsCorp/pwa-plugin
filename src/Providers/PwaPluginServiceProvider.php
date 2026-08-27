<?php

namespace PwaPlugin\Providers;

use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;
use PwaPlugin\Listeners\SendPwaPushOnDatabaseNotification;

class PwaPluginServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        Event::listen(NotificationSent::class, SendPwaPushOnDatabaseNotification::class);
    }
}
