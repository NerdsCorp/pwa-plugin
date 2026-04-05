<?php

namespace PwaPlugin\Filament\Pages;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Support\Enums\IconSize;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use PwaPlugin\Models\PwaPushSubscription;
use PwaPlugin\Services\PwaActions;
use PwaPlugin\Services\PwaPushService;
use PwaPlugin\Services\PwaSettingsRepository;

class PwaSettings extends Page implements HasSchemas
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.settings';

    protected static ?string $slug = 'pwa';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-device-phone-mobile';

    protected static ?int $navigationSort = 90;

    public ?array $data = [];
    public array $syncDiagnostics = [];

    public function hasLogo(): bool
    {
        return false;
    }

    public function getLogo(): ?string
    {
        return null;
    }

    public function getTitle(): string
    {
        return trans('pwa-plugin::pwa-plugin.settings.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('pwa-plugin::pwa-plugin.navigation.label');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return trans('pwa-plugin::pwa-plugin.navigation.group');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    public static function loadPluginSettings(PwaSettingsRepository $settings): array
    {
        $settings->ensureVapidKeys();

        return $settings->allWithDefaults(self::defaultSettings());
    }

    public static function getPluginSettingsForm(): array
    {
        return self::pluginSettingsSchema(
            self::buildSyncDiagnostics(
                app(PwaSettingsRepository::class),
                app(PwaPushService::class),
            ),
        );
    }

    public static function savePluginSettings(array $state, PwaSettingsRepository $settings): void
    {
        self::applyUploads($state);

        $invalidPngFields = self::validatePngFields($state);

        if (!empty($invalidPngFields)) {
            Notification::make()
                ->title(trans('pwa-plugin::pwa-plugin.errors.png_required'))
                ->body(implode(', ', $invalidPngFields))
                ->warning()
                ->send();
        }

        $settings->setMany($state);

        Notification::make()
            ->title(trans('pwa-plugin::pwa-plugin.notifications.saved'))
            ->success()
            ->send();
    }

    private static function defaultSettings(): array
    {
        return [
            'theme_color' => self::defaultFromEnv('theme_color', 'PWA_PLUGIN_THEME_COLOR', '#0ea5e9'),
            'background_color' => self::defaultFromEnv('background_color', 'PWA_PLUGIN_BACKGROUND_COLOR', '#0f172a'),
            'start_url' => self::defaultFromEnv('start_url', 'PWA_PLUGIN_START_URL', '/'),
            'cache_name' => self::defaultFromEnv('cache_name', 'PWA_PLUGIN_CACHE_NAME', 'pelican-pwa-v1'),
            'cache_version' => (int) config('pwa-plugin.cache_version', 1),
            'cache_enabled' => (bool) config('pwa-plugin.cache_enabled', false),
            'cache_precache_urls' => self::defaultFromEnv('cache_precache_urls', 'PWA_PLUGIN_CACHE_PRECACHE_URLS', ''),
            'manifest_icon_192' => self::defaultFromEnv('manifest_icon_192', 'PWA_PLUGIN_MANIFEST_ICON_192', '/pelican.svg'),
            'manifest_icon_512' => self::defaultFromEnv('manifest_icon_512', 'PWA_PLUGIN_MANIFEST_ICON_512', '/pelican.svg'),
            'apple_touch_icon' => self::defaultFromEnv('apple_touch_icon', 'PWA_PLUGIN_APPLE_TOUCH_ICON', '/pelican.svg'),
            'apple_touch_icon_152' => self::defaultFromEnv('apple_touch_icon_152', 'PWA_PLUGIN_APPLE_TOUCH_ICON_152', '/pelican.svg'),
            'apple_touch_icon_167' => self::defaultFromEnv('apple_touch_icon_167', 'PWA_PLUGIN_APPLE_TOUCH_ICON_167', '/pelican.svg'),
            'apple_touch_icon_180' => self::defaultFromEnv('apple_touch_icon_180', 'PWA_PLUGIN_APPLE_TOUCH_ICON_180', '/pelican.svg'),
            'push_enabled' => (bool) config('pwa-plugin.push_enabled', false),
            'push_send_on_database_notifications' => (bool) config('pwa-plugin.push_send_on_database_notifications', true),
            'push_send_on_mail_notifications' => (bool) config('pwa-plugin.push_send_on_mail_notifications', false),
            'vapid_public_key' => self::defaultFromEnv('vapid_public_key', 'PWA_PLUGIN_VAPID_PUBLIC_KEY', ''),
            'vapid_private_key' => self::defaultFromEnv('vapid_private_key', 'PWA_PLUGIN_VAPID_PRIVATE_KEY', ''),
            'vapid_subject' => self::defaultFromEnv('vapid_subject', 'PWA_PLUGIN_VAPID_SUBJECT', ''),
            'default_notification_icon' => self::defaultFromEnv('default_notification_icon', 'PWA_PLUGIN_NOTIFICATION_ICON', '/pelican.svg'),
            'default_notification_badge' => self::defaultFromEnv('default_notification_badge', 'PWA_PLUGIN_NOTIFICATION_BADGE', '/pelican.svg'),
        ];
    }

    private static function pluginSettingsSchema(array $syncDiagnostics): array
    {
        return [
            Tabs::make('Settings')
                ->tabs([
                    Tab::make('Manifest')
                        ->label(trans('pwa-plugin::pwa-plugin.tabs.manifest'))
                        ->icon('heroicon-o-device-phone-mobile')
                        ->schema([
                            Group::make()->columns(2)->schema([
                                ColorPicker::make('theme_color')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.theme_color.label'))
                                    ->helperText(trans('pwa-plugin::pwa-plugin.fields.theme_color.helper'))
                                    ->required(),
                                ColorPicker::make('background_color')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.background_color.label'))
                                    ->helperText(trans('pwa-plugin::pwa-plugin.fields.background_color.helper'))
                                    ->required(),
                                TextInput::make('start_url')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.start_url.label'))
                                    ->helperText(trans('pwa-plugin::pwa-plugin.fields.start_url.helper'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('cache_name')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.cache_name.label'))
                                    ->helperText(trans('pwa-plugin::pwa-plugin.fields.cache_name.helper'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                            TextInput::make('cache_version')
                                ->label(trans('pwa-plugin::pwa-plugin.fields.cache_version.label'))
                                ->numeric()
                                ->required(),
                            Toggle::make('cache_enabled')
                                ->label(trans('pwa-plugin::pwa-plugin.fields.cache_enabled.label'))
                                ->helperText(trans('pwa-plugin::pwa-plugin.fields.cache_enabled.helper')),
                            Textarea::make('cache_precache_urls')
                                ->label(trans('pwa-plugin::pwa-plugin.fields.cache_precache_urls.label'))
                                ->helperText(trans('pwa-plugin::pwa-plugin.fields.cache_precache_urls.helper'))
                                ->rows(4)
                                ->visible(fn ($get) => $get('cache_enabled')),
                            Group::make()->columns(2)->schema([
                                TextInput::make('manifest_icon_192')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.manifest_icon_192.label'))
                                    ->helperText(trans('pwa-plugin::pwa-plugin.fields.manifest_icon_192.helper'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('manifest_icon_512')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.manifest_icon_512.label'))
                                    ->helperText(trans('pwa-plugin::pwa-plugin.fields.manifest_icon_512.helper'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                            Group::make()->columns(2)->schema([
                                TextInput::make('apple_touch_icon')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.apple_touch_icon.label'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('apple_touch_icon_152')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.apple_touch_icon_152.label'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('apple_touch_icon_167')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.apple_touch_icon_167.label'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('apple_touch_icon_180')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.apple_touch_icon_180.label'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        ]),
                    Tab::make('Push Notifications')
                        ->label(trans('pwa-plugin::pwa-plugin.tabs.push'))
                        ->icon('heroicon-o-bell')
                        ->schema([
                            Toggle::make('push_enabled')
                                ->label(trans('pwa-plugin::pwa-plugin.fields.push_enabled.label'))
                                ->helperText(trans('pwa-plugin::pwa-plugin.fields.push_enabled.helper'))
                                ->reactive(),
                            Toggle::make('push_send_on_database_notifications')
                                ->label(trans('pwa-plugin::pwa-plugin.fields.push_send_on_db.label'))
                                ->helperText(trans('pwa-plugin::pwa-plugin.fields.push_send_on_db.helper')),
                            Toggle::make('push_send_on_mail_notifications')
                                ->label(trans('pwa-plugin::pwa-plugin.fields.push_send_on_mail.label'))
                                ->helperText(trans('pwa-plugin::pwa-plugin.fields.push_send_on_mail.helper')),
                            TextInput::make('vapid_subject')
                                ->label(trans('pwa-plugin::pwa-plugin.fields.vapid_subject.label'))
                                ->helperText(trans('pwa-plugin::pwa-plugin.fields.vapid_subject.helper'))
                                ->required()
                                ->maxLength(255)
                                ->visible(fn ($get) => $get('push_enabled')),
                            Group::make()->columns(2)->schema([
                                TextInput::make('vapid_public_key')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.vapid_public_key.label'))
                                    ->required()
                                    ->maxLength(255)
                                    ->visible(fn ($get) => $get('push_enabled')),
                                TextInput::make('vapid_private_key')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.vapid_private_key.label'))
                                    ->password()
                                    ->required()
                                    ->maxLength(255)
                                    ->visible(fn ($get) => $get('push_enabled')),
                            ]),
                            Group::make()->columns(2)->schema([
                                TextInput::make('default_notification_icon')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.default_notification_icon.label'))
                                    ->helperText(trans('pwa-plugin::pwa-plugin.fields.default_notification_icon.helper'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('default_notification_badge')
                                    ->label(trans('pwa-plugin::pwa-plugin.fields.default_notification_badge.label'))
                                    ->helperText(trans('pwa-plugin::pwa-plugin.fields.default_notification_badge.helper'))
                                    ->required()
                                    ->maxLength(255),
                            ]),
                        ]),
                    Tab::make('Actions')
                        ->label(trans('pwa-plugin::pwa-plugin.tabs.actions'))
                        ->icon('heroicon-o-command-line')
                        ->schema([
                            PwaActions::make(),
                            Section::make(trans('pwa-plugin::pwa-plugin.diagnostics.title'))
                                ->schema([
                                    Placeholder::make('diag_overall_status')->label(trans('pwa-plugin::pwa-plugin.diagnostics.labels.overall_status'))->content(fn (): string => (string) ($syncDiagnostics['overall_status'] ?? trans('pwa-plugin::pwa-plugin.diagnostics.unavailable'))),
                                    Placeholder::make('diag_pwa_users')->label(trans('pwa-plugin::pwa-plugin.diagnostics.labels.pwa_users'))->content(fn (): string => (string) ($syncDiagnostics['pwa_users'] ?? trans('pwa-plugin::pwa-plugin.diagnostics.unavailable'))),
                                    Placeholder::make('diag_active_subscriptions')->label(trans('pwa-plugin::pwa-plugin.diagnostics.labels.active_subscriptions'))->content(fn (): string => (string) ($syncDiagnostics['active_subscriptions'] ?? trans('pwa-plugin::pwa-plugin.diagnostics.unavailable'))),
                                    Placeholder::make('diag_subscriptions_per_user')->label(trans('pwa-plugin::pwa-plugin.diagnostics.labels.subscriptions_per_user'))->content(fn (): string => (string) ($syncDiagnostics['subscriptions_per_user'] ?? trans('pwa-plugin::pwa-plugin.diagnostics.unavailable'))),
                                    Placeholder::make('diag_last_push_sent')->label(trans('pwa-plugin::pwa-plugin.diagnostics.labels.last_push_sent'))->content(fn (): string => (string) ($syncDiagnostics['last_push_sent'] ?? trans('pwa-plugin::pwa-plugin.diagnostics.unavailable'))),
                                    Placeholder::make('diag_last_sync_server')->label(trans('pwa-plugin::pwa-plugin.diagnostics.labels.last_sync_server'))->content(fn (): string => (string) ($syncDiagnostics['last_sync_server'] ?? trans('pwa-plugin::pwa-plugin.diagnostics.unavailable'))),
                                    Placeholder::make('diag_last_subscription_refresh_server')->label(trans('pwa-plugin::pwa-plugin.diagnostics.labels.last_subscription_refresh_server'))->content(fn (): string => (string) ($syncDiagnostics['last_subscription_refresh_server'] ?? trans('pwa-plugin::pwa-plugin.diagnostics.unavailable'))),
                                    Placeholder::make('diag_queue_readiness')->label(trans('pwa-plugin::pwa-plugin.diagnostics.labels.queue_readiness'))->content(fn (): string => (string) ($syncDiagnostics['queue_readiness'] ?? trans('pwa-plugin::pwa-plugin.diagnostics.unavailable'))),
                                    Placeholder::make('diag_push_stack')->label(trans('pwa-plugin::pwa-plugin.diagnostics.labels.push_stack'))->content(fn (): string => (string) ($syncDiagnostics['push_stack'] ?? trans('pwa-plugin::pwa-plugin.diagnostics.unavailable'))),
                                ]),
                        ]),
                ])
                ->persistTabInQueryString(),
        ];
    }

    public function mount(PwaSettingsRepository $settings, PwaPushService $push): void
    {
        $values = self::loadPluginSettings($settings);
        $this->data = $values;
        $this->form->fill($values);
        $this->refreshSyncDiagnostics($settings, $push);
    }

    protected function getFormSchema(): array
    {
        return self::pluginSettingsSchema($this->syncDiagnostics);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('pwa-plugin::pwa-plugin.actions.save'))
                ->iconButton()
                ->iconSize(IconSize::ExtraLarge)
                ->icon('tabler-device-floppy')
                ->action('save')
                ->authorize(fn () => user()?->can('update settings'))
                ->keyBindings(['mod+s']),
            Action::make('refresh_sync_diagnostics')
                ->label(trans('pwa-plugin::pwa-plugin.diagnostics.refresh'))
                ->icon('heroicon-o-arrow-path')
                ->color('gray')
                ->action('refreshSyncDiagnostics')
                ->authorize(fn () => user()?->can('update settings')),
        ];
    }

    public function refreshSyncDiagnostics(PwaSettingsRepository $settings, PwaPushService $push): void
    {
        $this->syncDiagnostics = self::buildSyncDiagnostics($settings, $push);
    }

    public function save(PwaSettingsRepository $settings): void
    {
        self::savePluginSettings($this->form->getState(), $settings);
    }

    private static function defaultFromEnv(string $key, string $envKey, string $fallback): string
    {
        $value = (string) config('pwa-plugin.' . $key, $fallback);

        return $value ?: (string) env($envKey, $fallback);
    }

    /** @param array<string, mixed> $state */
    private static function validatePngFields(array $state): array
    {
        $fields = [
            'manifest_icon_192' => trans('pwa-plugin::pwa-plugin.fields.manifest_icon_192.label'),
            'manifest_icon_512' => trans('pwa-plugin::pwa-plugin.fields.manifest_icon_512.label'),
            'default_notification_icon' => trans('pwa-plugin::pwa-plugin.fields.default_notification_icon.label'),
            'default_notification_badge' => trans('pwa-plugin::pwa-plugin.fields.default_notification_badge.label'),
        ];

        $invalid = [];
        foreach ($fields as $key => $label) {
            $value = trim((string) ($state[$key] ?? ''));
            if ($value === '') {
                continue;
            }

            $path = parse_url($value, PHP_URL_PATH) ?: $value;
            if (!str_ends_with(strtolower($path), '.png')) {
                $invalid[] = $label;
            }
        }

        return $invalid;
    }

    /** @param array<string, mixed> $state */
    private static function applyUploads(array &$state): void
    {
        // No-op: uploads removed.
    }

    public static function buildSyncDiagnostics(PwaSettingsRepository $settings, PwaPushService $push): array
    {
        $unavailable = trans('pwa-plugin::pwa-plugin.diagnostics.unavailable');

        try {
            $queueConnection = (string) config('queue.default', 'sync');
            $queueConfigured = is_array(config("queue.connections.{$queueConnection}"));
            $queueBackground = !in_array($queueConnection, ['sync', 'null'], true);

            $vapidSubject = (string) $settings->get('vapid_subject', config('pwa-plugin.vapid_subject', ''));
            $vapidPublic = (string) $settings->get('vapid_public_key', config('pwa-plugin.vapid_public_key', ''));
            $vapidPrivate = (string) $settings->get('vapid_private_key', config('pwa-plugin.vapid_private_key', ''));

            $pushEnabled = (bool) $settings->get('push_enabled', config('pwa-plugin.push_enabled', false));
            $pushLibraryAvailable = $push->canSend();
            $vapidConfigured = $vapidSubject !== '' && $vapidPublic !== '' && $vapidPrivate !== '';
            $pushReady = $pushEnabled && $pushLibraryAvailable && $vapidConfigured;

            $globalSubscriptions = 0;
            $globalUsers = 0;
            $lastPushAt = null;
            $lastSyncAt = null;
            $lastSubscriptionRefreshAt = null;

            if (Schema::hasTable('pwa_push_subscriptions')) {
                $globalSubscriptions = PwaPushSubscription::query()->count();
                $globalUsers = PwaPushSubscription::query()
                    ->select(['notifiable_type', 'notifiable_id'])
                    ->distinct()
                    ->get()
                    ->count();

                if (Schema::hasColumn('pwa_push_subscriptions', 'last_push_sent_at')) {
                    $lastPushAt = self::formatDiagnosticsDate(
                        PwaPushSubscription::query()->max('last_push_sent_at')
                    );
                }

                if (Schema::hasColumn('pwa_push_subscriptions', 'last_synced_at')) {
                    $lastSyncAt = self::formatDiagnosticsDate(
                        PwaPushSubscription::query()->max('last_synced_at')
                    );
                }

                $lastSubscriptionRefreshAt = self::formatDiagnosticsDate(
                    PwaPushSubscription::query()->max('updated_at')
                );
            }

            $subscriptionsPerUser = $globalUsers > 0 ? number_format($globalSubscriptions / $globalUsers, 2) : '0.00';

            $queueStatus = $queueConfigured
                ? trans('pwa-plugin::pwa-plugin.diagnostics.status.ready')
                : trans('pwa-plugin::pwa-plugin.diagnostics.status.not_ready');

            $pushStatus = $pushReady
                ? trans('pwa-plugin::pwa-plugin.diagnostics.status.ready')
                : trans('pwa-plugin::pwa-plugin.diagnostics.status.incomplete');

            $hasRecentActivity = $lastPushAt !== null || $lastSyncAt !== null;
            $hasSubscribers = $globalSubscriptions > 0;
            $healthy = $queueConfigured && $pushReady && $hasSubscribers && $hasRecentActivity;

            return [
                'overall_status' => $healthy
                    ? trans('pwa-plugin::pwa-plugin.diagnostics.status.healthy')
                    : trans('pwa-plugin::pwa-plugin.diagnostics.status.needs_attention'),
                'pwa_users' => (string) $globalUsers,
                'active_subscriptions' => (string) $globalSubscriptions,
                'subscriptions_per_user' => $subscriptionsPerUser,
                'last_push_sent' => $lastPushAt ?? $unavailable,
                'last_sync_server' => $lastSyncAt ?? $unavailable,
                'last_subscription_refresh_server' => $lastSubscriptionRefreshAt ?? $unavailable,
                'queue_readiness' => sprintf(
                    '%s (%s: %s, %s: %s)',
                    $queueStatus,
                    trans('pwa-plugin::pwa-plugin.diagnostics.labels.connection'),
                    $queueConnection,
                    trans('pwa-plugin::pwa-plugin.diagnostics.labels.background'),
                    $queueBackground ? trans('pwa-plugin::pwa-plugin.diagnostics.status.yes') : trans('pwa-plugin::pwa-plugin.diagnostics.status.no'),
                ),
                'push_stack' => sprintf(
                    '%s (%s: %s, %s: %s, %s: %s)',
                    $pushStatus,
                    trans('pwa-plugin::pwa-plugin.diagnostics.labels.enabled'),
                    $pushEnabled ? trans('pwa-plugin::pwa-plugin.diagnostics.status.yes') : trans('pwa-plugin::pwa-plugin.diagnostics.status.no'),
                    trans('pwa-plugin::pwa-plugin.diagnostics.labels.library'),
                    $pushLibraryAvailable ? trans('pwa-plugin::pwa-plugin.diagnostics.status.yes') : trans('pwa-plugin::pwa-plugin.diagnostics.status.no'),
                    trans('pwa-plugin::pwa-plugin.diagnostics.labels.vapid'),
                    $vapidConfigured ? trans('pwa-plugin::pwa-plugin.diagnostics.status.yes') : trans('pwa-plugin::pwa-plugin.diagnostics.status.no'),
                ),
            ];
        } catch (\Throwable) {
            return [
                'overall_status' => $unavailable,
                'pwa_users' => $unavailable,
                'active_subscriptions' => $unavailable,
                'subscriptions_per_user' => $unavailable,
                'last_push_sent' => $unavailable,
                'last_sync_server' => $unavailable,
                'last_subscription_refresh_server' => $unavailable,
                'queue_readiness' => $unavailable,
                'push_stack' => $unavailable,
            ];
        }
    }

    private static function formatDiagnosticsDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->toDateTimeString();
        } catch (\Throwable) {
            return null;
        }
    }
}
