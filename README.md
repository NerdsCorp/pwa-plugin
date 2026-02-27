# PWA Plugin for Pelican Panel

Turn Pelican Panel into an installable Progressive Web App with push notifications, background sync support, and admin tooling.

## Features

- Installable PWA (desktop and mobile)
- Generated `manifest.json` and `service-worker.js`
- Push notifications (test push + routed notifications)
- Admin broadcast push page for sending to all subscribed PWA users
- Admin Sync Diagnostics block (overall status, usage, activity, queue/push readiness)
- VAPID key management via settings (with env fallback)
- Localized UI strings (depends on panel locale)

## Requirements

- Recent Pelican Panel version
- HTTPS enabled (required for service workers/push in normal environments)
- Browser with PWA support
- `minishlink/web-push` available in your Pelican install

## Installation

### Via Panel

1. Import and install the plugin from **Admin -> Plugins**.
2. Open **Admin -> PWA** and configure settings.

### Manual

```bash
cd /var/www/pelican/plugins
# place plugin as: pwa-plugin
```

If needed:

```bash
cd /var/www/pelican
composer require minishlink/web-push
```

## Admin Pages

### `Admin -> PWA`

Configure:

- Theme/start URL
- Manifest and Apple icons
- Cache/service worker behavior
- Push enablement and VAPID keys
- Default notification icon/badge

Also includes **Sync Diagnostics** with:

- Overall status
- PWA users
- Active subscriptions
- Subscriptions per user
- Last push sent
- Last sync (server)
- Last subscription refresh (server)
- Queue readiness
- Push stack readiness

### `Admin -> Broadcast to all PWA users`

Send a manual push to all active subscriptions.

Fields:

- Title and body
- Click URL
- Optional icon and badge overrides

## User Profile PWA Actions

Users get a **PWA** section in profile with quick actions:

- Install PWA
- Request notifications permission
- Subscribe push
- Unsubscribe push
- Send test push

## Push Setup

1. Enable push in **Admin -> PWA**.
2. Set `vapid_subject`, `vapid_public_key`, `vapid_private_key`.
3. On a user device, allow notifications and subscribe.
4. Use **Send Test Push** to validate.

## Notes on Delivery

- Test push sends directly to that users current subscriptions.
- Broadcast currently sends directly from the panel path.
- If subscriptions are stale, users may need to re-subscribe.

## Troubleshooting

### PWA install issues

- Confirm HTTPS
- Confirm `/manifest.json` and `/service-worker.js` are reachable
- Confirm icon paths are valid

### Push issues

- Verify browser permission is granted
- Verify VAPID keys are set
- Verify `minishlink/web-push` is installed
- Re-subscribe a device and run test push

## License

GNU General Public License v3.0
