<?php

namespace PwaPlugin\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Forms\Contracts\HasForms;
use PwaPlugin\Services\PwaSettingsRepository;

class PwaSettings extends Page implements HasForms
{
    use InteractsWithForms;

    protected static \BackedEnum|string|null $navigationIcon = 'heroicon-o-device-phone-mobile';
    protected static ?string $navigationLabel = 'PWA';
    protected static \UnitEnum|string|null $navigationGroup = 'Advanced';
    protected static ?int $navigationSort = 90;

    protected string $view = 'pwa-plugin::pages.pwa-settings';
    protected static ?string $slug = 'pwa';

    public ?array $data = [];

    public static function shouldRegisterNavigation(): bool
    {
        $panel = Filament::getCurrentPanel();

        return $panel?->getId() === 'admin';
    }

    public function mount(PwaSettingsRepository $settings): void
    {
        $defaults = [
            'theme_color' => $this->defaultFromEnv('theme_color', 'PWA_PLUGIN_THEME_COLOR', '#0ea5e9'),
            'background_color' => $this->defaultFromEnv('background_color', 'PWA_PLUGIN_BACKGROUND_COLOR', '#0f172a'),
            'start_url' => $this->defaultFromEnv('start_url', 'PWA_PLUGIN_START_URL', '/'),
            'cache_name' => $this->defaultFromEnv('cache_name', 'PWA_PLUGIN_CACHE_NAME', 'pelican-pwa-v1'),
            'cache_version' => (int) config('pwa.cache_version', 1),
            'manifest_icon_192' => $this->defaultFromEnv('manifest_icon_192', 'PWA_PLUGIN_MANIFEST_ICON_192', '/pelican.svg'),
            'manifest_icon_512' => $this->defaultFromEnv('manifest_icon_512', 'PWA_PLUGIN_MANIFEST_ICON_512', '/pelican.svg'),
            'apple_touch_icon' => $this->defaultFromEnv('apple_touch_icon', 'PWA_PLUGIN_APPLE_TOUCH_ICON', '/pelican.svg'),
            'apple_touch_icon_152' => $this->defaultFromEnv('apple_touch_icon_152', 'PWA_PLUGIN_APPLE_TOUCH_ICON_152', '/pelican.svg'),
            'apple_touch_icon_167' => $this->defaultFromEnv('apple_touch_icon_167', 'PWA_PLUGIN_APPLE_TOUCH_ICON_167', '/pelican.svg'),
            'apple_touch_icon_180' => $this->defaultFromEnv('apple_touch_icon_180', 'PWA_PLUGIN_APPLE_TOUCH_ICON_180', '/pelican.svg'),
            'push_enabled' => (bool) config('pwa.push_enabled', false),
            'push_send_on_database_notifications' => (bool) config('pwa.push_send_on_database_notifications', true),
            'push_send_on_mail_notifications' => (bool) config('pwa.push_send_on_mail_notifications', false),
            'vapid_public_key' => $this->defaultFromEnv('vapid_public_key', 'PWA_PLUGIN_VAPID_PUBLIC_KEY', ''),
            'vapid_private_key' => $this->defaultFromEnv('vapid_private_key', 'PWA_PLUGIN_VAPID_PRIVATE_KEY', ''),
            'vapid_subject' => $this->defaultFromEnv('vapid_subject', 'PWA_PLUGIN_VAPID_SUBJECT', ''),
            'default_notification_icon' => $this->defaultFromEnv('default_notification_icon', 'PWA_PLUGIN_NOTIFICATION_ICON', '/pelican.svg'),
            'default_notification_badge' => $this->defaultFromEnv('default_notification_badge', 'PWA_PLUGIN_NOTIFICATION_BADGE', '/pelican.svg'),
        ];

        $values = $settings->allWithDefaults($defaults);
        $this->data = $values;
        $this->form->fill($values);
    }

    public function form($form)
    {
        return $form
            ->schema([
                TextInput::make('theme_color')
                    ->label('Theme color')
                    ->helperText('Used by the manifest and browser UI.')
                    ->required()
                    ->maxLength(32),
                TextInput::make('background_color')
                    ->label('Background color')
                    ->helperText('Splash screen background color.')
                    ->required()
                    ->maxLength(32),
                TextInput::make('start_url')
                    ->label('Start URL')
                    ->helperText('Relative URL for the PWA start.')
                    ->required()
                    ->maxLength(255),
                TextInput::make('cache_name')
                    ->label('Cache name')
                    ->helperText('Used in the service worker cache.')
                    ->required()
                    ->maxLength(255),
                TextInput::make('cache_version')
                    ->label('Cache version')
                    ->numeric()
                    ->required(),
                TextInput::make('manifest_icon_192')
                    ->label('Manifest icon (192x192)')
                    ->helperText('Android requires PNG here for install icon.')
                    ->required()
                    ->maxLength(255),
                TextInput::make('manifest_icon_512')
                    ->label('Manifest icon (512x512)')
                    ->helperText('Android requires PNG here for install icon.')
                    ->required()
                    ->maxLength(255),
                TextInput::make('apple_touch_icon')
                    ->label('Apple touch icon (default)')
                    ->required()
                    ->maxLength(255),
                TextInput::make('apple_touch_icon_152')
                    ->label('Apple touch icon (152x152)')
                    ->required()
                    ->maxLength(255),
                TextInput::make('apple_touch_icon_167')
                    ->label('Apple touch icon (167x167)')
                    ->required()
                    ->maxLength(255),
                TextInput::make('apple_touch_icon_180')
                    ->label('Apple touch icon (180x180)')
                    ->required()
                    ->maxLength(255),
                Toggle::make('push_enabled')
                    ->label('Enable push notifications')
                    ->helperText('Requires VAPID keys and the Web Push library.'),
                Toggle::make('push_send_on_database_notifications')
                    ->label('Send push for panel notifications')
                    ->helperText('Sends push when a notification is stored in the database.'),
                Toggle::make('push_send_on_mail_notifications')
                    ->label('Send push for mail notifications')
                    ->helperText('Sends push for notifications that only use the mail channel.'),
                TextInput::make('vapid_subject')
                    ->label('VAPID subject')
                    ->helperText('Usually a mailto: or https: URL, e.g. mailto:admin@example.com')
                    ->required()
                    ->maxLength(255),
                TextInput::make('vapid_public_key')
                    ->label('VAPID public key')
                    ->required()
                    ->maxLength(255),
                TextInput::make('vapid_private_key')
                    ->label('VAPID private key')
                    ->password()
                    ->required()
                    ->maxLength(255),
                TextInput::make('default_notification_icon')
                    ->label('Default notification icon')
                    ->helperText('Default icon for push notifications.')
                    ->required()
                    ->maxLength(255),
                TextInput::make('default_notification_badge')
                    ->label('Default notification badge')
                    ->helperText('Default badge for push notifications.')
                    ->required()
                    ->maxLength(255),
            ])
            ->statePath('data');
    }

    public function save(PwaSettingsRepository $settings): void
    {
        $state = $this->form->getState();
        $settings->setMany($state);

        session()->flash('status', 'PWA settings saved.');
    }

    private function defaultFromEnv(string $key, string $envKey, string $fallback): string
    {
        $value = (string) config('pwa.' . $key, $fallback);

        if ($value === '') {
            $value = (string) env($envKey, $fallback);
        }

        return $value;
    }
}
