# PWA Plugin for Pelican Panel

Transform your Pelican Panel into a full-fledged Progressive Web App. Users can install it like a native app and receive push notifications for all the important stuff.

## Screenshots

### Settings
<img width="300" alt="image" src="https://github.com/user-attachments/assets/7f39e570-365f-4934-aff3-4a9ecb016a8f" />

### Sync Diagnostics
<img width="300" alt="image" src="https://github.com/user-attachments/assets/2c2c09ec-08f4-495b-9fbd-c174ac0be3b5" />

### Broadcast Page
<img width="300" alt="image" src="https://github.com/user-attachments/assets/1f95993a-e635-444d-9de6-d74b899d56d9" />

### User Profile Settings
<img width="300" alt="image" src="https://github.com/user-attachments/assets/dce9a77a-d496-46df-b47a-f4f01acef09e" />

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
composer require minishlink/web-push
```

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

## License

GNU General Public License v3.0
