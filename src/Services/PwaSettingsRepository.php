<?php

declare(strict_types=1);

namespace PwaPlugin\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Minishlink\WebPush\VAPID;
use PwaPlugin\Models\PwaSetting;

class PwaSettingsRepository
{
    private const ENV_KEYS = [
        'cache_version' => 'PWA_PLUGIN_CACHE_VERSION',
        'cache_name' => 'PWA_PLUGIN_CACHE_NAME',
        'cache_enabled' => 'PWA_PLUGIN_CACHE_ENABLED',
        'cache_precache_urls' => 'PWA_PLUGIN_CACHE_PRECACHE_URLS',
        'theme_color' => 'PWA_PLUGIN_THEME_COLOR',
        'background_color' => 'PWA_PLUGIN_BACKGROUND_COLOR',
        'start_url' => 'PWA_PLUGIN_START_URL',
        'manifest_icon_192' => 'PWA_PLUGIN_MANIFEST_ICON_192',
        'manifest_icon_512' => 'PWA_PLUGIN_MANIFEST_ICON_512',
        'apple_touch_icon' => 'PWA_PLUGIN_APPLE_TOUCH_ICON',
        'apple_touch_icon_152' => 'PWA_PLUGIN_APPLE_TOUCH_ICON_152',
        'apple_touch_icon_167' => 'PWA_PLUGIN_APPLE_TOUCH_ICON_167',
        'apple_touch_icon_180' => 'PWA_PLUGIN_APPLE_TOUCH_ICON_180',
        'push_enabled' => 'PWA_PLUGIN_PUSH_ENABLED',
        'push_send_on_database_notifications' => 'PWA_PLUGIN_PUSH_SEND_ON_DATABASE_NOTIFICATIONS',
        'push_send_on_mail_notifications' => 'PWA_PLUGIN_PUSH_SEND_ON_MAIL_NOTIFICATIONS',
        'vapid_public_key' => 'PWA_PLUGIN_VAPID_PUBLIC_KEY',
        'vapid_private_key' => 'PWA_PLUGIN_VAPID_PRIVATE_KEY',
        'vapid_subject' => 'PWA_PLUGIN_VAPID_SUBJECT',
        'default_notification_icon' => 'PWA_PLUGIN_NOTIFICATION_ICON',
        'default_notification_badge' => 'PWA_PLUGIN_NOTIFICATION_BADGE',
    ];

    private const STRING_KEYS_WITH_FALLBACK = [
        'theme_color',
        'background_color',
        'start_url',
        'cache_name',
        'cache_precache_urls',
        'vapid_public_key',
        'vapid_private_key',
        'vapid_subject',
        'default_notification_icon',
        'default_notification_badge',
    ];

    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->hasEnvironmentOverride($key)) {
            $configValue = config('pwa-plugin.' . $key);

            if ($configValue !== null) {
                return $configValue;
            }
        }

        if (!Schema::hasTable('pwa_settings')) {
            return $default;
        }

        $record = PwaSetting::query()->where('key', $key)->first();

        if ($record) {
            $value = $record->value ?? $default;

            if (in_array($key, self::STRING_KEYS_WITH_FALLBACK, true) && is_string($value) && $value === '') {
                return $default;
            }

            if (in_array($key, ['vapid_private_key'], true) && is_string($value) && $value !== '') {
                try {
                    return Crypt::decryptString($value);
                } catch (\Throwable) {
                    return $value;
                }
            }

            return $value;
        }

        $configValue = config('pwa-plugin.' . $key);

        if ($configValue !== null) {
            return $configValue;
        }

        return $default;
    }

    public function ensureVapidKeys(): void
    {
        if ($this->hasEnvironmentOverride('vapid_public_key') || $this->hasEnvironmentOverride('vapid_private_key')) {
            return;
        }

        if (!Schema::hasTable('pwa_settings')) {
            return;
        }

        if (!class_exists(VAPID::class)) {
            return;
        }

        $public = (string) $this->get('vapid_public_key', '');
        $private = (string) $this->get('vapid_private_key', '');

        if ($public !== '' && $private !== '') {
            return;
        }

        $keys = VAPID::createVapidKeys();

        $this->setMany([
            'vapid_public_key' => $keys['publicKey'] ?? '',
            'vapid_private_key' => $keys['privateKey'] ?? '',
        ]);
    }

    public function set(string $key, mixed $value): void
    {
        if (!Schema::hasTable('pwa_settings')) {
            return;
        }

        if (in_array($key, ['vapid_private_key'], true) && is_string($value) && $value !== '') {
            $value = Crypt::encryptString($value);
        }

        PwaSetting::query()->updateOrCreate(
            ['key' => $key],
            ['value' => $value],
        );
    }

    public function setMany(array $values): void
    {
        foreach ($values as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function allWithDefaults(array $defaults): array
    {
        $settings = [];

        foreach ($defaults as $key => $default) {
            $settings[$key] = $this->get($key, $default);
        }

        return $settings;
    }

    private function hasEnvironmentOverride(string $key): bool
    {
        $envKey = self::ENV_KEYS[$key] ?? null;

        if ($envKey === null) {
            return false;
        }

        return array_key_exists($envKey, $_ENV)
            || array_key_exists($envKey, $_SERVER)
            || getenv($envKey) !== false;
    }
}
