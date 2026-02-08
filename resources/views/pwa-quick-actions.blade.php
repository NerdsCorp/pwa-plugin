<div class="mt-4 rounded-lg border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-gray-900">
    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">PWA Quick Actions</h3>
    <div class="mt-3 flex flex-wrap gap-2">
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
</div>
