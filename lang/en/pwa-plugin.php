<?php

return [
    'navigation' => [
        'label' => 'PWA',
        'group' => 'Advanced',
    ],
    'settings' => [
        'title' => 'PWA Settings',
    ],
    'broadcast' => [
        'title' => 'Broadcast to all PWA users',
        'navigation_label' => 'Broadcast Push',
        'section_title' => 'Broadcast Push',
        'section_description' => 'Send a push notification to all active PWA subscriptions.',
    ],
    'tabs' => [
        'manifest' => 'Manifest',
        'push' => 'Push Notifications',
        'broadcast' => 'Broadcast Push',
        'actions' => 'Actions',
    ],
    'fields' => [
        'theme_color' => [
            'label' => 'Theme color',
            'helper' => 'Used by the manifest and browser UI.',
        ],
        'background_color' => [
            'label' => 'Background color',
            'helper' => 'Splash screen background color.',
        ],
        'start_url' => [
            'label' => 'Start URL',
            'helper' => 'Relative URL for the PWA start.',
        ],
        'cache_name' => [
            'label' => 'Cache name',
            'helper' => 'Used in the service worker cache.',
        ],
        'cache_version' => [
            'label' => 'Cache version',
        ],
        'cache_enabled' => [
            'label' => 'Enable offline cache',
            'helper' => 'Precache URLs and serve cached responses when offline.',
        ],
        'cache_precache_urls' => [
            'label' => 'Precache URLs',
            'helper' => 'Comma or newline separated list of URLs to precache (e.g. /, /).',
        ],
        'manifest_icon_192' => [
            'label' => 'Manifest icon (192x192)',
            'helper' => 'Android requires PNG here for install icon.',
        ],
        'manifest_icon_512' => [
            'label' => 'Manifest icon (512x512)',
            'helper' => 'Android requires PNG here for install icon.',
        ],
        'apple_touch_icon' => [
            'label' => 'Apple touch icon (default)',
        ],
        'apple_touch_icon_152' => [
            'label' => 'Apple touch icon (152x152)',
        ],
        'apple_touch_icon_167' => [
            'label' => 'Apple touch icon (167x167)',
        ],
        'apple_touch_icon_180' => [
            'label' => 'Apple touch icon (180x180)',
        ],
        'push_enabled' => [
            'label' => 'Enable push notifications',
            'helper' => 'Requires VAPID keys and the Web Push library.',
        ],
        'push_send_on_db' => [
            'label' => 'Send push for panel notifications',
            'helper' => 'Sends push when a notification is stored in the database.',
        ],
        'push_send_on_mail' => [
            'label' => 'Send push for mail notifications',
            'helper' => 'Sends push for notifications that only use the mail channel.',
        ],
        'broadcast_title' => [
            'label' => 'Broadcast title',
        ],
        'broadcast_body' => [
            'label' => 'Broadcast message',
        ],
        'broadcast_url' => [
            'label' => 'Click URL',
            'helper' => 'Where users are opened when they click the push notification.',
        ],
        'broadcast_icon' => [
            'label' => 'Override icon (optional)',
            'helper' => 'Optional icon URL/path for this broadcast only.',
        ],
        'broadcast_badge' => [
            'label' => 'Override badge (optional)',
            'helper' => 'Optional badge URL/path for this broadcast only.',
        ],
        'broadcast_require_interaction' => [
            'label' => 'Require interaction',
            'helper' => 'If enabled, the notification stays visible until the user interacts.',
        ],
        'vapid_subject' => [
            'label' => 'VAPID subject',
            'helper' => 'Usually a mailto: or https: URL, e.g. mailto:admin@example.com',
        ],
        'vapid_public_key' => [
            'label' => 'VAPID public key',
        ],
        'vapid_private_key' => [
            'label' => 'VAPID private key',
        ],
        'default_notification_icon' => [
            'label' => 'Default notification icon',
            'helper' => 'Default icon for push notifications.',
        ],
        'default_notification_badge' => [
            'label' => 'Default notification badge',
            'helper' => 'Default badge for push notifications.',
        ],
    ],
    'actions' => [
        'install' => 'Install PWA',
        'request_notifications' => 'Request Notifications',
        'subscribe' => 'Subscribe Push',
        'unsubscribe' => 'Unsubscribe',
        'test_push' => 'Send Test Push',
        'send_broadcast' => 'Send Broadcast To All',
        'save' => 'Save',
    ],
    'notifications' => [
        'saved' => 'PWA settings saved.',
        'subscribed' => 'Successfully subscribed to push notifications.',
        'unsubscribed' => 'Successfully unsubscribed.',
        'test_sent' => 'Test notification has been sent.',
        'broadcast_queued' => 'Broadcast queued for :count subscription(s).',
        'broadcast_sent' => 'Broadcast sent to :sent of :total subscription(s).',
    ],
    'errors' => [
        'table_missing' => 'Push subscriptions table is missing.',
        'unauthorized' => 'Unauthorized access.',
        'library_missing' => 'Web Push library not found.',
        'vapid_missing' => 'VAPID keys or subject are missing.',
        'no_subscription' => 'No subscription found for this browser.',
        'send_failed' => 'Failed to send notification.',
        'push_disabled' => 'Push notifications are disabled in settings.',
        'broadcast_required' => 'Broadcast title and message are required.',
        'unsupported' => 'Installation is currently not possible. The app may already be installed or your browser does not meet the requirements.',
        'install_android_title' => 'Install on Android',
        'install_android_body' => 'Open the browser menu and tap "Install app" or "Add to Home screen".',
        'install_already' => 'The app is already installed.',
        'install_ios_title' => 'Install on iOS',
        'install_ios_body' => 'Open this page in Safari, tap Share, then "Add to Home Screen".',
        'png_required' => 'PNG icons are required for Android and notifications.',
    ],
    'profile' => [
        'tab_label' => 'PWA',
        'section_heading' => 'PWA Actions',
        'section_description' => 'Manage your device connection and notifications.',
    ],
    'diagnostics' => [
        'title' => 'Sync Diagnostics',
        'refresh' => 'Refresh Diagnostics',
        'loading' => 'Loading diagnostics...',
        'checking' => 'checking...',
        'unavailable' => 'unavailable',
        'labels' => [
            'overall_status' => 'Overall status',
            'pwa_users' => 'PWA users',
            'active_subscriptions' => 'Active subscriptions',
            'subscriptions_per_user' => 'Subscriptions per user',
            'last_push_sent' => 'Last push sent',
            'last_sync_server' => 'Last sync (server)',
            'last_subscription_refresh_server' => 'Last subscription refresh (server)',
            'queue_readiness' => 'Queue readiness',
            'push_stack' => 'Push stack',
            'connection' => 'connection',
            'background' => 'background',
            'enabled' => 'enabled',
            'library' => 'library',
            'vapid' => 'vapid',
            'queue' => 'queue',
            'push' => 'push',
            'subscribers' => 'subscribers',
            'activity' => 'activity',
        ],
        'status' => [
            'healthy' => 'healthy',
            'needs_attention' => 'needs attention',
            'ready' => 'ready',
            'not_ready' => 'not ready',
            'incomplete' => 'incomplete',
            'ok' => 'ok',
            'issue' => 'issue',
            'none' => 'none',
            'yes' => 'yes',
            'no' => 'no',
            'unknown' => 'unknown',
        ],
    ],
    'messages' => [
        'update_available' => 'A new version is available. Reload now?',
        'test_notification_body' => 'This is a test notification from your PWA.',
        'new_notification' => 'You have a new notification.',
    ],
    'manifest' => [
        'description' => 'The official app for our panel.',
        'shortcuts' => [
            'dashboard_name' => 'Dashboard',
            'dashboard_short' => 'Dashboard',
            'dashboard_description' => 'View your servers',
        ],
    ],
];
