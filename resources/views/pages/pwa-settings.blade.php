<x-filament-panels::page>
    <div class="space-y-6">
        @if (session('status'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 dark:border-green-900 dark:bg-green-900/20 dark:text-green-200">
                {{ session('status') }}
            </div>
        @endif

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">PWA Settings</h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                This panel provides install and notification helpers for the Pelican Panel PWA.
            </p>
            @php
                $pushEnabled = app(\PwaPlugin\Services\PwaSettingsRepository::class)
                    ->get('push_enabled', config('pwa.push_enabled', false));
            @endphp
            @if ($pushEnabled && !class_exists(\Minishlink\WebPush\WebPush::class))
                <div class="mt-4 rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800 dark:border-amber-900 dark:bg-amber-900/20 dark:text-amber-200">
                    Web Push library not detected. Install `minishlink/web-push` in the panel to enable sending push notifications.
                </div>
            @endif
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <form wire:submit.prevent="save" class="space-y-6">
                {{ $this->form }}
                <div class="flex flex-wrap gap-3">
                    <x-filament::button color="success" type="submit">
                        Save Settings
                    </x-filament::button>
                </div>
            </form>
        </div>

        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">Quick Actions</h3>
            <div class="mt-4 flex flex-wrap gap-3">
                <x-filament::button color="primary" type="button" onclick="window.pwaInstall?.()">
                    Install App
                </x-filament::button>
                <x-filament::button color="info" type="button" onclick="window.pwaRequestNotifications?.()">
                    Request Notifications
                </x-filament::button>
                <x-filament::button color="success" type="button" onclick="window.pwaRegisterPush?.()">
                    Subscribe to Push
                </x-filament::button>
                <x-filament::button color="danger" type="button" onclick="window.pwaUnregisterPush?.()">
                    Unsubscribe Push
                </x-filament::button>
                <x-filament::button color="warning" type="button" onclick="window.pwaSendTestPush?.()">
                    Send Test Push
                </x-filament::button>
            </div>
            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                Install prompts and push permissions are only available on supported browsers and HTTPS.
            </p>
        </div>
    </div>
</x-filament-panels::page>
