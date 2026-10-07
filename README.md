[![Stars](https://img.shields.io/github/stars/NerdsCorp/pwa-plugin?style=flat-square)](https://github.com/NerdsCorp/pwa-plugin/stargazers)
[![Forks](https://img.shields.io/github/forks/NerdsCorp/pwa-plugin?style=flat-square)](https://github.com/NerdsCorp/pwa-plugin/network/members)
[![Watchers](https://img.shields.io/github/watchers/NerdsCorp/pwa-plugin?style=flat-square)](https://github.com/NerdsCorp/pwa-plugin/watchers)
[![Issues](https://img.shields.io/github/issues/NerdsCorp/pwa-plugin?style=flat-square)](https://github.com/NerdsCorp/pwa-plugin/issues)
[![Pull Requests](https://img.shields.io/github/issues-pr/NerdsCorp/pwa-plugin?style=flat-square)](https://github.com/NerdsCorp/pwa-plugin/pulls)
[![Contributors](https://img.shields.io/github/contributors/NerdsCorp/pwa-plugin?style=flat-square)](https://github.com/NerdsCorp/pwa-plugin/graphs/contributors)
[![Last Commit](https://img.shields.io/github/last-commit/NerdsCorp/pwa-plugin?style=flat-square)](https://github.com/NerdsCorp/pwa-plugin/commits/main)
[![Commit Activity](https://img.shields.io/github/commit-activity/m/NerdsCorp/pwa-plugin?style=flat-square)](https://github.com/NerdsCorp/pwa-plugin/commits/main)
[![Release](https://img.shields.io/github/v/release/NerdsCorp/pwa-plugin?style=flat-square)](https://github.com/NerdsCorp/pwa-plugin/releases)
[![Downloads](https://img.shields.io/github/downloads/NerdsCorp/pwa-plugin/total?style=flat-square)](https://github.com/NerdsCorp/pwa-plugin/releases)
[![Repo Size](https://img.shields.io/github/repo-size/NerdsCorp/pwa-plugin?style=flat-square)](https://github.com/NerdsCorp/pwa-plugin)
[![License](https://img.shields.io/github/license/NerdsCorp/pwa-plugin?style=flat-square)](https://github.com/NerdsCorp/pwa-plugin/blob/main/LICENSE)

[![Panel Integration (main)](https://github.com/NerdsCorp/pwa-plugin/actions/workflows/panel-integration-main.yml/badge.svg)](https://github.com/NerdsCorp/pwa-plugin/actions/workflows/panel-integration-main.yml)
[![Panel Integration (latest release)](https://github.com/NerdsCorp/pwa-plugin/actions/workflows/panel-integration-latest.yml/badge.svg)](https://github.com/NerdsCorp/pwa-plugin/actions/workflows/panel-integration-latest.yml)

# PWA Plugin for Pelican Panel

Transform your Pelican Panel into a full-fledged Progressive Web App. Users can install it like a native app and receive push notifications for all the important stuff.

## Screenshots

### Settings
<img width="300" alt="image" src="https://github.com/user-attachments/assets/7f39e570-365f-4934-aff3-4a9ecb016a8f" />

### Sync Diagnostics
<img width="300" alt="image" src="https://github.com/user-attachments/assets/23d100f6-66b1-4668-a7a5-4e5d2bac7f91" />

### Broadcast Page
<img width="300" alt="image" src="https://github.com/user-attachments/assets/1f95993a-e635-444d-9de6-d74b899d56d9" />

### User Profile Settings
<img width="300" alt="image" src="https://github.com/user-attachments/assets/29705ac7-cd45-42d4-a6d6-6f1f5905826a" />
<img width="300" alt="image" src="https://github.com/user-attachments/assets/dff04e35-decb-4029-8086-51e4f6ad2c3f" />


### Android Notification
<img width="300" alt="image" src="https://github.com/user-attachments/assets/76e9430c-82f8-4592-afae-a1e2f0f3426f" />

### Android PWA with Notification
<img width="300" alt="image" src="https://github.com/user-attachments/assets/704d2ebc-5270-423f-8a7c-5b1c869148fd" />

### Apple Notification
<img width="600" alt="image" src="https://github.com/user-attachments/assets/05f0c479-7ed2-41c4-9cab-620ac4350810" />

### Apple in the PWA
<img width="300" alt="image" src="https://github.com/user-attachments/assets/6491f3ec-7ff8-474b-ae47-7cda42b0b65f" />

### Apple PWA
<img width="300" alt="image" src="https://github.com/user-attachments/assets/ff9778af-3da0-48f1-8fbc-da5341a523be" />

## Features

- Installable PWA on desktop and mobile
- Generated `manifest.json` and `service-worker.js`
- Service worker that prompts users when updates are available
- Push notifications (test push, routed notifications, and admin broadcast to all subscribers)
- Admin Sync Diagnostics block (overall status, usage, activity, queue/push readiness)
- VAPID key management via settings (with `.env` fallback)
- Localized UI strings (depends on panel locale)
- Complete admin settings page for everything PWA-related

## Requirements

- Recent Pelican Panel version
- HTTPS enabled (required for service workers and push)
- Browser with PWA support
- PNG icons for Android (SVG and ICO are not reliably supported for install icons or notifications)
- `minishlink/web-push` available in your Pelican install

## Installation

### Via Panel

1. Download the plugin zip file
2. Go to **Admin → Plugins**, click **Import**, and install
3. Open **Admin → PWA** and configure settings

### Manual

```bash
cd /var/www/pelican/plugins
# Upload and extract pwa-plugin here
```

If `minishlink/web-push` wasn't installed automatically:

```bash
cd /var/www/pelican
composer require minishlink/web-push:^11.0.0 -W
```

Push subscription endpoints must use HTTPS and resolve to public IP addresses.

## Application API

Use these endpoints when another backend service needs to trigger a PWA push. They use Pelican's Application API authentication and rate limit.

### Before you start

1. Enable push notifications in **Admin > PWA** and configure valid VAPID keys.
2. Make sure users have subscribed to push in their profile. A push can only be sent to a user's registered browser subscriptions.
3. Create a Pelican Application API key with **Users: Write** access. Send it as a bearer token in the `Authorization` header. Keep this key secret and make requests over HTTPS.

Use your panel's public origin in place of `https://panel.example.com`. The user-specific endpoint takes the user's numeric panel ID.

### Send to one user

```bash
curl --request POST 'https://panel.example.com/api/application/pwa/users/123/notifications' \
  --header 'Authorization: Bearer YOUR_APPLICATION_API_KEY' \
  --header 'Accept: application/json' \
  --header 'Content-Type: application/json' \
  --data '{
    "title": "Server ready",
    "body": "Your server has finished installing.",
    "url": "/",
    "tag": "server-ready"
  }'
```

### Broadcast to all subscriptions

Use the same headers and JSON body as the user-specific request, but call:

```bash
curl --request POST 'https://panel.example.com/api/application/pwa/notifications/broadcast' \
  --header 'Authorization: Bearer YOUR_APPLICATION_API_KEY' \
  --header 'Accept: application/json' \
  --header 'Content-Type: application/json' \
  --data '{
    "title": "Scheduled maintenance",
    "body": "The panel will be unavailable at 02:00 UTC.",
    "url": "/",
    "tag": "maintenance"
  }'
```

### Request fields

| Field | Type | Required | Meaning |
| --- | --- | --- | --- |
| `title` | string, up to 120 characters | Yes | Notification heading. |
| `body` | string, up to 300 characters | Yes | Notification text. |
| `url` | panel-relative path | No | Page opened when the notification is clicked. Must start with one `/`; defaults to `/`. |
| `icon` | string | No | Icon URL/path; defaults to the icon configured in **Admin > PWA**. |
| `badge` | string | No | Badge URL/path; defaults to the badge configured in **Admin > PWA**. |
| `tag` | string, up to 255 characters | No | Browser notification tag; defaults to `pwa-api`. |
| `require_interaction` | boolean | No | Ask the browser to keep the notification visible until the user interacts; defaults to `false`. |

### Response and errors

On success, the response reports provider send results:

```json
{
  "message": "Push notification sent.",
  "sent": 2,
  "failed": 0,
  "total": 2
}
```

`sent` means the push service accepted the message. Browser and operating-system settings still determine whether it is displayed. The broadcast sends to all registered subscriptions and, like the existing admin broadcast, does not filter by notification preference channels.

| HTTP status | Meaning |
| --- | --- |
| `401` / `403` | Missing or unauthorized Application API key, or insufficient **Users: Write** access. |
| `404` | The target user or their subscriptions were not found; broadcast has no subscriptions. |
| `409` | Push notifications are disabled in plugin settings. |
| `422` | Invalid request fields. |
| `502` | Subscriptions existed, but no push service accepted the message. Check the `failed` count. |
| `503` | Plugin subscription migrations, Web Push library, or VAPID configuration are unavailable. |

## Admin Pages

### Admin → PWA

Configure:

- Theme and background colors
- Start URL
- Cache settings (name and version)
- Manifest icons (192px and 512px PNG recommended)
- Apple touch icons for iOS (152px, 167px, 180px)
- Default icons for notifications and badges
- Push notification enablement and VAPID keys

All settings save to the database and fall back to your `.env` file if nothing is set.

Also includes a **Sync Diagnostics** showing:

- Overall status
- PWA users and active subscriptions
- Subscriptions per user
- Last push sent
- Last sync and last subscription refresh (server)
- Queue readiness and push stack readiness

### Admin → Broadcast to All PWA Users

Send a manual push to all active subscriptions. Fields include:

- Title and body
- Click URL
- Optional icon and badge overrides

## User Profile — PWA Tab

Users get a **PWA** section in their profile with quick actions:

- Install PWA
- Request notification permissions
- Subscribe to push
- Unsubscribe from push
- Send test push

The profile also lists subscribed devices. Users can give each device a name, see its last push or subscription sync activity, and remove individual devices. Removing the current device also unsubscribes its browser from push notifications.

## Icon Setup

Android requires PNG icons — SVG and ICO files won't work reliably for app installation or notifications.

### Recommended Files

Place these in a publicly accessible location (e.g., `public/`):

- `/favicon-192.png` — app icon
- `/favicon-512.png` — higher-res version
- `/favicon-96.png` — notification badge
- Apple touch icons at 152px, 167px, and 180px (optional, for custom iOS icons)

Then enter the paths in **Admin → PWA**.

### Generating Icons

Using [realfavicongenerator.net](https://realfavicongenerator.net/) is the quickest option. Or with ImageMagick:

```bash
cd /var/www/pelican/public/
convert logo.png -resize 192x192 favicon-192.png
convert logo.png -resize 512x512 favicon-512.png
convert logo.png -resize 96x96 favicon-96.png
```

## Push Notification Setup

1. Generate your VAPID keys
2. Add `vapid_subject`, `vapid_public_key`, and `vapid_private_key` in **Admin → PWA** (or in `.env`)
3. Enable push notifications in **Admin → PWA**
4. On a user device, allow notifications and click **Subscribe to Push**
5. Use **Send Test Push** to verify everything is working

### Notes on Delivery

- Test push sends directly to the current user's active subscriptions
- Broadcast sends directly from the panel to all active subscriptions
- If subscriptions are stale, users may need to re-subscribe

## How Users Install the App

### Desktop (Chrome, Edge, Brave)

1. Open the panel in a browser
2. Click the install icon in the address bar (⊕ or monitor icon), or go to the three-dot menu → **Install [App Name]**
3. Confirm by clicking **Install**

The app will appear on your desktop, in the Start menu, or Applications folder.

### Android (Chrome, Samsung Internet, Edge)

1. Open the panel in a mobile browser
2. Wait for the install banner, or tap the three-dot menu and choose **Install app** or **Add to Home screen**
   - On Samsung Internet: **Add page to → Home screen**
3. Tap **Install** when prompted

The app will launch in full-screen mode like a native app.

### iOS (Safari only)

1. Open the panel in Safari
2. Tap the Share button → **Add to Home Screen**
3. Optionally rename, then tap **Add**

> **Note:** iOS has limitations with PWAs. Push notifications and background sync are not supported, and installation must be done through Safari.

### Uninstalling

- **Desktop:** Right-click the app icon → Uninstall
- **Android:** Long-press → Uninstall (or via App info)
- **iOS:** Long-press → Remove App

## Troubleshooting

### Plugin Logs

PWA server-side errors are written through Laravel's standard logger, so they go to the same log destination configured for Pelican. Search the panel log for messages beginning with `PWA` to find failed push deliveries, missing push configuration, subscription problems, or settings diagnostics errors. Push delivery entries include the subscription and user IDs, provider status, and a short sanitized reason; they do not include the push endpoint or notification contents.

Browser and service-worker errors happen on the user's device and are not sent to the panel log. Check that browser's developer console for client-side issues.

### PWA Won't Install

- Confirm HTTPS is enabled
- Confirm `/manifest.json` and `/service-worker.js` are accessible
- Confirm icon paths are valid and point to PNG files
- Try clearing the browser cache

### Android Icons Missing

Android does not support SVG or ICO for app icons or notification badges. Switch to PNG.

### Push Notifications Not Working

- Verify browser notification permissions are granted
- Verify VAPID keys are configured correctly
- Verify `minishlink/web-push` is installed
- Re-subscribe the device and run a test push

![Alt](https://repobeats.axiom.co/api/embed/80311c1baa59a0ba31dfa51b712ad70187a0da16.svg "Repobeats analytics image")

## License

GNU General Public License v3.0
