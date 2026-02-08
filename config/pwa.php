<?php

return [

    'cache_version' => env('PWA_PLUGIN_CACHE_VERSION', 1),
    'cache_name' => env('PWA_PLUGIN_CACHE_NAME', 'pelican-pwa-v1'),
    
    'theme_color' => env('PWA_PLUGIN_THEME_COLOR', '#0ea5e9'),
    'background_color' => env('PWA_PLUGIN_BACKGROUND_COLOR', '#0f172a'),
    'start_url' => env('PWA_PLUGIN_START_URL', '/'),

    'manifest_icon_192' => env('PWA_PLUGIN_MANIFEST_ICON_192', '/pelican.svg'),
    'manifest_icon_512' => env('PWA_PLUGIN_MANIFEST_ICON_512', '/pelican.svg'),
    'apple_touch_icon' => env('PWA_PLUGIN_APPLE_TOUCH_ICON', '/pelican.svg'),
    'apple_touch_icon_152' => env('PWA_PLUGIN_APPLE_TOUCH_ICON_152', '/pelican.svg'),
    'apple_touch_icon_167' => env('PWA_PLUGIN_APPLE_TOUCH_ICON_167', '/pelican.svg'),
    'apple_touch_icon_180' => env('PWA_PLUGIN_APPLE_TOUCH_ICON_180', '/pelican.svg'),

    'push_enabled' => env('PWA_PLUGIN_PUSH_ENABLED', false),
    'push_send_on_database_notifications' => env('PWA_PLUGIN_PUSH_SEND_ON_DATABASE_NOTIFICATIONS', true),
    'push_send_on_mail_notifications' => env('PWA_PLUGIN_PUSH_SEND_ON_MAIL_NOTIFICATIONS', false),
    'vapid_public_key' => env('PWA_PLUGIN_VAPID_PUBLIC_KEY', ''),
    'vapid_private_key' => env('PWA_PLUGIN_VAPID_PRIVATE_KEY', ''),
    'vapid_subject' => env('PWA_PLUGIN_VAPID_SUBJECT', ''),
    'default_notification_icon' => env('PWA_PLUGIN_NOTIFICATION_ICON', '/pelican.svg'),
    'default_notification_badge' => env('PWA_PLUGIN_NOTIFICATION_BADGE', '/pelican.svg'),
];
