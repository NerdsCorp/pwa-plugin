<?php

namespace PwaPlugin\Services;

use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class PwaActions
{
    public static function make(bool $includePreferences = true): Group
    {
        $user = auth()->user();
        $preferences = PwaNotificationPreferences::settingsForUser($user);

        $schema = [
            Group::make()
                ->columns(['default' => 1, 'lg' => 5])
                ->extraAttributes(['class' => 'gap-4'])
                ->schema([
                    SchemaActions::make([
                        Action::make('exclude_install')
                            ->label(fn (): string => trans('pwa-plugin::pwa-plugin.actions.install'))
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
                            ->label(fn (): string => trans('pwa-plugin::pwa-plugin.actions.request_notifications'))
                            ->icon('heroicon-o-bell-snooze')
                            ->color('info')
                            ->extraAttributes(['onclick' => 'window.pwaRequestNotifications?.(); return false;']),
                    ])->fullWidth(),

                    SchemaActions::make([
                        Action::make('exclude_subscribe')
                            ->label(fn (): string => trans('pwa-plugin::pwa-plugin.actions.subscribe'))
                            ->icon('heroicon-o-check-circle')
                            ->color('primary')
                            ->extraAttributes(['onclick' => 'window.pwaRegisterPush?.(); return false;']),
                    ])->fullWidth(),

                    SchemaActions::make([
                        Action::make('exclude_unsubscribe')
                            ->label(fn (): string => trans('pwa-plugin::pwa-plugin.actions.unsubscribe'))
                            ->icon('heroicon-o-x-circle')
                            ->color('danger')
                            ->extraAttributes(['onclick' => 'window.pwaUnregisterPush?.(); return false;']),
                    ])->fullWidth(),

                    SchemaActions::make([
                        Action::make('exclude_test')
                            ->label(fn (): string => trans('pwa-plugin::pwa-plugin.actions.test_push'))
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
                ]),
        ];

        if ($includePreferences) {
            $schema[] = Section::make(fn (): string => trans('pwa-plugin::pwa-plugin.preferences.section_title'))
                ->description(fn (): string => trans('pwa-plugin::pwa-plugin.preferences.section_description'))
                ->schema([
                    CheckboxList::make('pwa_notification_preferences.channels')
                        ->label(fn (): string => trans('pwa-plugin::pwa-plugin.preferences.channels_label'))
                        ->options(PwaNotificationPreferences::channelOptions())
                        ->default(array_values(array_keys(array_filter($preferences, fn (array $value): bool => (bool) ($value['enabled'] ?? false)))))
                        ->columns(2),

                    Group::make()->columns(2)->schema([
                        Select::make('pwa_notification_preferences.digest_mode')
                            ->label(fn (): string => trans('pwa-plugin::pwa-plugin.preferences.digest_mode_label'))
                            ->options([
                                'instant' => trans('pwa-plugin::pwa-plugin.preferences.digest_mode_instant'),
                                'daily' => trans('pwa-plugin::pwa-plugin.preferences.digest_mode_daily'),
                            ])
                            ->default(Arr::first($preferences)['digest_mode'] ?? 'instant'),
                        TextInput::make('pwa_notification_preferences.max_per_day')
                            ->label(fn (): string => trans('pwa-plugin::pwa-plugin.preferences.max_per_day_label'))
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->default((int) (Arr::first($preferences)['max_per_day'] ?? 10)),
                    ]),

                    Toggle::make('pwa_notification_preferences.quiet_hours_enabled')
                        ->label(fn (): string => trans('pwa-plugin::pwa-plugin.preferences.quiet_hours_label'))
                        ->default((bool) (Arr::first($preferences)['quiet_hours_enabled'] ?? false)),

                    Group::make()->columns(2)->schema([
                        TextInput::make('pwa_notification_preferences.quiet_hours_start')
                            ->label(fn (): string => trans('pwa-plugin::pwa-plugin.preferences.quiet_hours_start_label'))
                            ->placeholder('22:00')
                            ->default(Arr::first($preferences)['quiet_hours_start'] ?? '22:00'),
                        TextInput::make('pwa_notification_preferences.quiet_hours_end')
                            ->label(fn (): string => trans('pwa-plugin::pwa-plugin.preferences.quiet_hours_end_label'))
                            ->placeholder('07:00')
                            ->default(Arr::first($preferences)['quiet_hours_end'] ?? '07:00'),
                    ]),

                    SchemaActions::make([
                        Action::make('save_notification_preferences')
                            ->label(fn (): string => trans('pwa-plugin::pwa-plugin.preferences.save_button'))
                            ->icon('heroicon-o-check-circle')
                            ->color('primary')
                            ->action(function (?array $data = null): void {
                                $payload = $data ?? request()->all();
                                $preferences = $payload['pwa_notification_preferences'] ?? $payload;
                                PwaNotificationPreferences::saveForUser(auth()->user(), $preferences);

                                Notification::make()
                                    ->title(trans('pwa-plugin::pwa-plugin.preferences.saved'))
                                    ->success()
                                    ->send();
                            }),
                    ])->fullWidth(),
                ]);
        }

        return Group::make()
            ->columns(['default' => 1, 'lg' => 1])
            ->extraAttributes(['class' => 'gap-6'])
            ->schema($schema);
    }
}
