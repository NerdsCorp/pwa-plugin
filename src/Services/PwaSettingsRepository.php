<?php

namespace PwaPlugin\Services;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;
use Throwable;
use PwaPlugin\Models\PwaSetting;

class PwaSettingsRepository
{
    private const STRING_KEYS_WITH_FALLBACK = [
        'theme_color',
        'background_color',
        'start_url',
        'cache_name',
        'vapid_public_key',
        'vapid_private_key',
        'vapid_subject',
        'default_notification_icon',
        'default_notification_badge',
    ];

    public function get(string $key, mixed $default = null): mixed
    {
        if (!Schema::hasTable('pwa_settings')) {
            return $default;
        }

        $record = PwaSetting::query()->where('key', $key)->first();

        if (!$record) {
            return $default;
        }

        $value = $record->value ?? $default;

        if (in_array($key, self::STRING_KEYS_WITH_FALLBACK, true) && is_string($value) && $value === '') {
            return $default;
        }

        if (in_array($key, ['vapid_private_key'], true) && is_string($value) && $value !== '') {
            try {
                return Crypt::decryptString($value);
            } catch (Throwable) {
                return $value;
            }
        }

        return $value;
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
            ['value' => $value]
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
}
 