<?php

namespace PwaPlugin\Services;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Group;
use Illuminate\Support\Str;

class PwaActions
{
    public static function make(): Group
    {
        return Group::make()
            ->columns(['default' => 1, 'lg' => 5])
            ->extraAttributes(['class' => 'gap-4'])
            ->schema([
                SchemaActions::make([
                    Action::make('exclude_install')
                        ->label(trans('pwa-plugin::pwa-plugin.actions.install'))
                        ->icon('heroicon-o-arrow-down-tray')
                        ->color('success')
                        ->action(function () {
                            $ua = request()->header('User-Agent', '');
                            $isIos = Str::contains($ua, ['iPhone', 'iPad', 'iPod']);
                            $isAndroid = Str::contains($ua, ['Android']);

                            Notification::make()
                                ->title($isIos
                                    ? trans('pwa-plugin::pwa-plugin.errors.install_ios_title')
                                    : ($isAndroid ? trans('pwa-plugin::pwa-plugin.errors.install_android_title') : trans('pwa-plugin::pwa-plugin.errors.unsupported')))
                                ->body($isIos
                                    ? trans('pwa-plugin::pwa-plugin.errors.install_ios_body')
                                    : ($isAndroid ? trans('pwa-plugin::pwa-plugin.errors.install_android_body') : null))
                                ->warning()
                                ->send();
                        })
                        ->extraAttributes([
                            'onclick' => "if(!window.triggerPwaInstall?.()){ \$wire.call('mountAction', 'exclude_install'); } return false;",
                        ]),
                ])->fullWidth(),

                SchemaActions::make([
                    Action::make('exclude_notifications')
                        ->label(trans('pwa-plugin::pwa-plugin.actions.request_notifications'))
                        ->icon('heroicon-o-bell-snooze')
                        ->color('info')
                        ->extraAttributes(['onclick' => 'window.pwaRequestNotifications?.(); return false;']),
                ])->fullWidth(),

                SchemaActions::make([
                    Action::make('exclude_subscribe')
                        ->label(trans('pwa-plugin::pwa-plugin.actions.subscribe'))
                        ->icon('heroicon-o-check-circle')
                        ->color('primary')
                        ->extraAttributes(['onclick' => 'window.pwaRegisterPush?.(); return false;']),
                ])->fullWidth(),

                SchemaActions::make([
                    Action::make('exclude_unsubscribe')
                        ->label(trans('pwa-plugin::pwa-plugin.actions.unsubscribe'))
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->extraAttributes(['onclick' => 'window.pwaUnregisterPush?.(); return false;']),
                ])->fullWidth(),

                SchemaActions::make([
                    Action::make('exclude_test')
                        ->label(trans('pwa-plugin::pwa-plugin.actions.test_push'))
                        ->icon('heroicon-o-paper-airplane')
                        ->color('warning')
                        ->visible(fn () => app(PwaSettingsRepository::class)->get('push_enabled', config('pwa-plugin.push_enabled', false)) ?? false)
                        ->action(fn () => Notification::make()->title(trans('pwa-plugin::pwa-plugin.notifications.test_sent'))->success()->send())
                        ->extraAttributes(['onclick' => <<<'JS'
                            const btn = event.currentTarget;
                            btn.disabled = true;
                            fetch(window.pwaConfig.routes.test, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': pwaCsrfToken(),
                                    'Accept': 'application/json'
                                }
                            }).then(res => {
                                btn.disabled = false;
                                if (res.ok) {
                                    $wire.call('mountAction', 'test');
                                }
                            }).catch(() => {
                                btn.disabled = false;
                            });
                            return false;
                        JS]),
                ])->fullWidth(),
            ]);
    }
}
