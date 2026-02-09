<?php

namespace PwaPlugin\Filament\Pages;

use Filament\Facades\Filament;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use PwaPlugin\Services\PwaSettingsRepository;
use PwaPlugin\Services\PwaActions;
use Filament\Actions\Action;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Enums\IconSize;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Components\ColorPicker;

class PwaSettings extends Page implements HasSchemas
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.settings';

    protected static ?string $slug = 'pwa';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-device-phone-mobile';
    
    protected static \UnitEnum|string|null $navigationGroup = 'Advanced';
    
    protected static ?int $navigationSort = 90;

    public ?array $data = [];

    public function hasLogo(): bool { return false; }
    public function getLogo(): ?string { return null; }
    
    public function getTitle(): string 
    { 
        return trans('pwa-plugin::pwa-plugin.settings.title'); 
    }

    public static function getNavigationLabel(): string 
    { 
        return trans('pwa-plugin::pwa-plugin.navigation.label'); 
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    protected function getFormStatePath(): ?string 
    {
        return 'data';
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

    protected function getFormSchema(): array
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
                        ]),
                ])
                ->persistTabInQueryString()
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label(trans('pwa-plugin::pwa-plugin.actions.save'))
                ->iconButton()->iconSize(IconSize::ExtraLarge)
                ->icon('tabler-device-floppy')
                ->action('save')
                ->authorize(fn () => user()?->can('update settings'))
                ->keyBindings(['mod+s']),
        ];
    }

    public function save(PwaSettingsRepository $settings): void
    {
        $state = $this->form->getState();
        $settings->setMany($state);
        Notification::make()->title(trans('pwa-plugin::pwa-plugin.notifications.saved'))->success()->send();
    }

    private function defaultFromEnv(string $key, string $envKey, string $fallback): string
    {
        $value = (string) config('pwa.' . $key, $fallback);
        return $value ?: (string) env($envKey, $fallback);
    }
} 