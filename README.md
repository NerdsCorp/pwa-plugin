# PWA Plugin for Pelican Panel

Turn Pelican Panel into a Progressive Web App (PWA) with install support and push notifications.

## Highlights

- Installable PWA (desktop + mobile)
- Service worker with update prompts
- Push notification support (database + optional mail channel)
- Admin settings page for all PWA configuration

## Requirements

- Pelican Panel (latest)
- HTTPS enabled (required for service workers and push)
- Modern browser with PWA support
- PNG icons for Android install + notifications (SVG is not sufficient on Android)

## Installation

### Via Panel UI

1. Download the plugin zip
2. Admin -> Plugins
3. Import
4. Install

### Manual

```bash
cd /var/www/pelican/plugins
# Upload and extract pwa-plugin here

cd /var/www/pelican
php artisan p:plugin:install
# Select: pwa-plugin
```

## Composer Dependency

This plugin requires `minishlink/web-push` to send push notifications. The plugin.json includes it under `composer_packages` so the panel can install it.

If your panel cannot auto-install packages, install manually:

```bash
cd /var/www/pelican
composer require minishlink/web-push
```

## PWA Settings (Admin)

Open **Admin -> PWA** to configure everything:

- Theme/background colors
- Start URL
- Cache name/version
- Manifest icons (192/512)
- Apple touch icons
- Default notification icon/badge
- Push options + VAPID keys

Settings are saved in the database and fall back to `.env` when empty.

## Icon Setup (Important)

Android **requires PNG** icons for:
- PWA install icons (manifest 192x192 and 512x512)
- Notification icons/badges

SVG/ICO will not work reliably on Android.

### Recommended files

Place PNGs in `public/` (or any URL path you choose):

- `/favicons/favicon-192.png`
- `/favicons/favicon-512.png`
- `/favicons/favicon-96.png` (badge)
- Apple touch icons (152/167/180) if you want custom ones

Then set those paths in **Admin ? PWA**.

### Generate icons

You can use:
- https://realfavicongenerator.net/

Or ImageMagick:

```bash
cd /var/www/pelican/public/favicons
convert logo.png -resize 192x192 favicon-192.png
convert logo.png -resize 512x512 favicon-512.png
convert logo.png -resize 96x96 favicon-96.png
```

## .env Defaults (Optional)

You can provide defaults in `.env`:

```env
PWA_THEME_COLOR=#0ea5e9
PWA_BACKGROUND_COLOR=#0f172a
PWA_START_URL=/
PWA_MANIFEST_ICON_192=/favicons/favicon-192.png
PWA_MANIFEST_ICON_512=/favicons/favicon-512.png
PWA_APPLE_TOUCH_ICON=/pelican.svg
PWA_APPLE_TOUCH_ICON_152=/favicons/favicon-152.png
PWA_APPLE_TOUCH_ICON_167=/favicons/favicon-167.png
PWA_APPLE_TOUCH_ICON_180=/favicons/favicon-180.png
PWA_NOTIFICATION_ICON=/favicons/favicon-192.png
PWA_NOTIFICATION_BADGE=/favicons/favicon-96.png
PWA_PUSH_ENABLED=false
PWA_PUSH_SEND_ON_DATABASE_NOTIFICATIONS=true
PWA_PUSH_SEND_ON_MAIL_NOTIFICATIONS=false
PWA_VAPID_PUBLIC_KEY=
PWA_VAPID_PRIVATE_KEY=
PWA_VAPID_SUBJECT=mailto:admin@example.com
```

## Push Notifications

### How it works

- The plugin listens for Laravel `NotificationSent` events.
- Database notifications can be pushed automatically.
- Mail-only notifications can also be pushed if enabled (e.g., `ServerInstalled`).

### Enable Push

1. Generate VAPID keys.
2. Set VAPID values in **Admin -> PWA** (or `.env`).
3. Enable **Push Notifications**.
4. On the PWA settings page, use **Subscribe to Push**.

### Test Push

Use **Send Test Push** on the PWA settings page.

## Quick Actions

Quick actions are available:
- In the PWA settings page
- In the Profile page (PWA tab)

## Troubleshooting

### PWA not installing

- Ensure HTTPS
- Check `manifest.json` and `service-worker.js` are reachable
- Verify PNG icons are set in the manifest
- Clear browser cache

### Android icons not showing

- Android ignores SVG/ICO for install and notifications
- Use PNG in manifest + notification icon/badge

### Push not working

- Ensure permission is granted
- Ensure VAPID keys are configured
- Ensure `minishlink/web-push` is installed
- Check service worker is registered

## License

GNU General Public License v3.0
