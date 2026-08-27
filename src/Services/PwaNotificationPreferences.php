<?php

namespace PwaPlugin\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use PwaPlugin\Models\PwaNotificationPreference;

class PwaNotificationPreferences
{
    /**
     * @return array<string, array{label: string, default_enabled: bool}>
     */
    public static function channelDefinitions(): array
    {
        return [
            'account' => [
                'label' => 'Account',
                'default_enabled' => true,
            ],
            'server' => [
                'label' => 'Server',
                'default_enabled' => true,
            ],
            'backup' => [
                'label' => 'Backup',
                'default_enabled' => true,
            ],
            'mail' => [
                'label' => 'Mail',
                'default_enabled' => true,
            ],
            'other' => [
                'label' => 'Other',
                'default_enabled' => true,
            ],
        ];
    }

    public static function channelOptions(): array
    {
        return collect(self::channelDefinitions())
            ->map(fn (array $definition): string => $definition['label'])
            ->all();
    }

    public static function defaultPreferences(): array
    {
        $defaults = [];

        foreach (self::channelDefinitions() as $channel => $definition) {
            $defaults[$channel] = [
                'enabled' => (bool) $definition['default_enabled'],
                'digest_mode' => 'instant',
                'quiet_hours_enabled' => false,
                'quiet_hours_start' => '22:00',
                'quiet_hours_end' => '07:00',
                'max_per_day' => 10,
                'sent_count_24h' => 0,
                'last_sent_at' => null,
                'last_digest_sent_at' => null,
            ];
        }

        return $defaults;
    }

    public static function settingsForUser(mixed $user): array
    {
        $defaults = self::defaultPreferences();

        if (!$user || !method_exists($user, 'getMorphClass') || !method_exists($user, 'getKey')) {
            return $defaults;
        }

        $rows = PwaNotificationPreference::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())
            ->get();

        foreach ($rows as $row) {
            $channel = self::normalizeChannel((string) $row->channel);
            if (!isset($defaults[$channel])) {
                continue;
            }

            $defaults[$channel] = array_replace($defaults[$channel], [
                'enabled' => (bool) $row->enabled,
                'digest_mode' => in_array((string) $row->digest_mode, ['instant', 'daily'], true)
                    ? (string) $row->digest_mode
                    : 'instant',
                'quiet_hours_enabled' => (bool) $row->quiet_hours_enabled,
                'quiet_hours_start' => (string) ($row->quiet_hours_start ?: '22:00'),
                'quiet_hours_end' => (string) ($row->quiet_hours_end ?: '07:00'),
                'max_per_day' => max(0, (int) ($row->max_per_day ?? 10)),
                'sent_count_24h' => max(0, (int) ($row->sent_count_24h ?? 0)),
                'last_sent_at' => $row->last_sent_at ? $row->last_sent_at->toISOString() : null,
                'last_digest_sent_at' => $row->last_digest_sent_at ? $row->last_digest_sent_at->toISOString() : null,
            ]);
        }

        return $defaults;
    }

    public static function saveForUser(mixed $user, array $data): void
    {
        if (!$user || !method_exists($user, 'getMorphClass') || !method_exists($user, 'getKey')) {
            return;
        }

        $rawChannels = $data['channels'] ?? array_keys(self::channelDefinitions());
        $rawChannels = is_array($rawChannels) ? $rawChannels : [$rawChannels];
        $selectedChannels = array_fill_keys(array_values($rawChannels), true);

        if (empty($selectedChannels) && !array_key_exists('channels', $data)) {
            $selectedChannels = array_fill_keys(array_keys(self::channelDefinitions()), true);
        }

        $quietHoursEnabled = (bool) ($data['quiet_hours_enabled'] ?? false);
        $quietHoursStart = (string) ($data['quiet_hours_start'] ?? '22:00');
        $quietHoursEnd = (string) ($data['quiet_hours_end'] ?? '07:00');
        $digestMode = in_array((string) ($data['digest_mode'] ?? 'instant'), ['instant', 'daily'], true)
            ? (string) ($data['digest_mode'] ?? 'instant')
            : 'instant';
        $maxPerDay = max(0, (int) ($data['max_per_day'] ?? 10));

        foreach (self::channelDefinitions() as $channel => $definition) {
            $enabled = array_key_exists($channel, $selectedChannels);

            PwaNotificationPreference::query()->updateOrCreate(
                [
                    'notifiable_type' => $user->getMorphClass(),
                    'notifiable_id' => $user->getKey(),
                    'channel' => $channel,
                ],
                [
                    'enabled' => $enabled,
                    'digest_mode' => $digestMode,
                    'quiet_hours_enabled' => $quietHoursEnabled,
                    'quiet_hours_start' => $quietHoursStart,
                    'quiet_hours_end' => $quietHoursEnd,
                    'max_per_day' => $maxPerDay,
                    'sent_count_24h' => 0,
                ]
            );
        }
    }

    public static function normalizeChannel(string $channel): string
    {
        $normalized = strtolower(trim($channel));
        $normalized = preg_replace('/[^a-z0-9]+/', '', $normalized) ?? '';

        $aliases = [
            'account' => 'account',
            'accountcreated' => 'account',
            'user' => 'account',
            'server' => 'server',
            'serverinstalled' => 'server',
            'addedtoserver' => 'server',
            'removedfromserver' => 'server',
            'backup' => 'backup',
            'backupcompleted' => 'backup',
            'mail' => 'mail',
            'mailtested' => 'mail',
            'other' => 'other',
            'misc' => 'other',
            'general' => 'other',
        ];

        return $aliases[$normalized] ?? 'other';
    }

    public static function shouldDeliver(mixed $user, ?string $channel = null): bool
    {
        $channel = self::normalizeChannel((string) ($channel ?: 'other'));
        $settings = self::settingsForUser($user);
        $preference = $settings[$channel] ?? self::defaultPreferences()[$channel] ?? self::defaultPreferences()['other'];

        if (empty($preference['enabled'])) {
            Log::debug('PWA notification blocked because the channel is disabled.', [
                'user' => $user?->getKey(),
                'channel' => $channel,
            ]);

            return false;
        }

        if (($preference['digest_mode'] ?? 'instant') === 'daily') {
            Log::debug('PWA notification blocked because digest mode is daily.', [
                'user' => $user?->getKey(),
                'channel' => $channel,
            ]);

            return false;
        }

        if (!empty($preference['quiet_hours_enabled'])) {
            $now = Carbon::now();
            $start = Carbon::parse($preference['quiet_hours_start'] ?? '22:00');
            $end = Carbon::parse($preference['quiet_hours_end'] ?? '07:00');
            $currentMinutes = ($now->hour * 60) + $now->minute;
            $startMinutes = ($start->hour * 60) + $start->minute;
            $endMinutes = ($end->hour * 60) + $end->minute;

            $withinQuietHours = $startMinutes <= $endMinutes
                ? $currentMinutes >= $startMinutes && $currentMinutes < $endMinutes
                : $currentMinutes >= $startMinutes || $currentMinutes < $endMinutes;

            if ($channel !== 'account' && $withinQuietHours) {
                Log::debug('PWA notification blocked by quiet hours.', [
                    'user' => $user?->getKey(),
                    'channel' => $channel,
                    'start' => $preference['quiet_hours_start'] ?? '22:00',
                    'end' => $preference['quiet_hours_end'] ?? '07:00',
                ]);

                return false;
            }
        }

        $maxPerDay = (int) ($preference['max_per_day'] ?? 0);
        if ($maxPerDay > 0) {
            $sentCount = (int) ($preference['sent_count_24h'] ?? 0);
            if ($sentCount >= $maxPerDay) {
                Log::debug('PWA notification blocked by daily limit.', [
                    'user' => $user?->getKey(),
                    'channel' => $channel,
                    'sent_count_24h' => $sentCount,
                    'max_per_day' => $maxPerDay,
                ]);

                return false;
            }
        }

        return true;
    }

    public static function markDelivered(mixed $user, ?string $channel = null): void
    {
        if (!$user || !method_exists($user, 'getMorphClass') || !method_exists($user, 'getKey')) {
            return;
        }

        $channel = self::normalizeChannel((string) ($channel ?: 'other'));
        $preference = PwaNotificationPreference::query()
            ->where('notifiable_type', $user->getMorphClass())
            ->where('notifiable_id', $user->getKey())
            ->where('channel', $channel)
            ->first();

        if (!$preference) {
            $preference = new PwaNotificationPreference();
            $preference->notifiable_type = $user->getMorphClass();
            $preference->notifiable_id = $user->getKey();
            $preference->channel = $channel;
            $preference->enabled = true;
            $preference->digest_mode = 'instant';
            $preference->quiet_hours_enabled = false;
            $preference->quiet_hours_start = '22:00';
            $preference->quiet_hours_end = '07:00';
            $preference->max_per_day = 10;
        }

        $preference->last_sent_at = now();
        $preference->sent_count_24h = (int) ($preference->sent_count_24h ?? 0) + 1;
        $preference->save();
    }
}
