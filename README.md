# PWA Plugin for Pelican Panel

Transform your Pelican Panel into a full-fledged Progressive Web App. Your users can install it like a native app and get push notifications for all the important stuff.


## Screenshots

### Settings
<img width="300" alt="image" src="https://github.com/user-attachments/assets/7f39e570-365f-4934-aff3-4a9ecb016a8f" />

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

## What's Included

- Install your panel as an app on any device (desktop or mobile)
- Service worker that prompts users when updates are available
- Push notifications that work through the database or email
- Complete admin settings page for everything PWA-related

## Before You Start

You'll need:
- Latest version of Pelican Panel
- HTTPS enabled (service workers won't run without it)
- A browser that supports PWAs
- PNG icons for Android (SVG doesn't cut it for install icons and notifications)

## Getting It Installed

### Through the Panel

1. Download the plugin zip file
2. Unzip it and remove any nested folders
3. Rename the folder to just "pwa-plugin"
4. Zip it back up
5. Head to Admin → Plugins
6. Click Import
7. Install the plugin
8. Configure your PWA settings

### Manual Installation

```bash
cd /var/www/pelican/plugins
# Upload and extract pwa-plugin here
```

## About the Composer Package

This plugin needs `minishlink/web-push` to send push notifications. It's listed in the plugin.json file so the panel should install it automatically.

If that doesn't work for some reason, you can install it yourself:

```bash
cd /var/www/pelican
composer require minishlink/web-push
```

## Configuring PWA Settings

Go to **Admin → PWA** and you'll find options for:

- Theme and background colors
- Start URL
- Cache settings (name and version)
- Manifest icons (you'll want 192px and 512px versions)
- Apple touch icons for iOS devices
- Default icons for notifications and badges
- Push notification settings including VAPID keys

Everything saves to the database and falls back to your `.env` file if nothing's set.

## Icon Setup (Read This!)

Here's the thing about Android: it needs PNG icons. Period. SVG and ICO files won't work reliably for either app installation or notifications.

### What You Need

Put these PNG files somewhere accessible (like `public/`):

- `/favicon-192.png` (for the app icon)
- `/favicon-512.png` (higher res version)
- `/favicon-96.png` (for the notification badge)
- Apple touch icons at 152px, 167px, and 180px if you want custom iOS icons

Then just enter those paths in **Admin → PWA**.

### Making the Icons

Try https://realfavicongenerator.net/ for a quick solution.

Or if you prefer command line, use ImageMagick:

```bash
cd /var/www/pelican/public/
convert logo.png -resize 192x192 favicon-192.png
convert logo.png -resize 512x512 favicon-512.png
convert logo.png -resize 96x96 favicon-96.png
```

## Setting Up Push Notifications

### How It Works

The plugin hooks into Laravel's `NotificationSent` events, so database notifications can automatically become push notifications.

### Getting Push Working

1. Generate your VAPID keys
2. Add them in **Admin → PWA** (or stick them in `.env`)
3. Enable the push notifications feature
4. Go to the PWA settings page and hit **Subscribe to Push**

### Testing It Out

There's a **Send Test Push** button on the PWA settings page. Click it to make sure everything's working.

## Quick Actions

You'll find quick action buttons in two places:
- The PWA settings page in admin
- The PWA tab on user profile pages

## How Users Install the App

### Desktop (Chrome, Edge, Brave)

1. Open your panel in the browser
2. Look for an install icon in the address bar (usually looks like a ⊕ or monitor icon)
3. Click it, or go to the three-dot menu and choose "Install [App Name]"
4. Confirm by clicking "Install"
5. The app will show up on your desktop, in the Start menu, or Applications folder

You can also do it through the menu: three-dot menu → "Save and Share" → "Install page as app"

### Android (Chrome, Samsung Internet, Edge)

1. Open the panel in your mobile browser
2. Either wait for the install banner at the bottom, or tap the three-dot menu
3. Choose "Install app" or "Add to Home screen"
   - On Samsung Internet it's "Add page to" → "Home screen"
4. Tap "Install" when prompted
5. The app icon appears on your home screen
6. Tap it to launch in full-screen mode

It'll look and feel just like a native app.

### iOS (Safari)

1. Open the panel in Safari (has to be Safari, not Chrome)
2. Tap the Share button at the bottom (the square with an arrow)
3. Scroll and find "Add to Home Screen"
4. Change the name if you want
5. Tap "Add" in the top right
6. Find the icon on your home screen and tap to open

Heads up: iOS has some limitations with PWAs. No background sync or push notifications, and it has to be installed through Safari specifically.

### General Tips

- PWAs update themselves automatically when you're online
- Most work offline once installed
- To uninstall:
  - **Desktop:** Right-click the app icon → Uninstall
  - **Android:** Long-press → Uninstall or go through App info
  - **iOS:** Long-press → Remove App

## Enabling Push on Your Device

1. Go to your profile
2. Switch to the PWA tab
3. Click "Request Notifications"
4. Click "Subscribe to Push"
5. Hit "Test Push" to verify
6. You're all set!

## When Things Don't Work

### PWA Won't Install

- Double-check that HTTPS is enabled
- Make sure `manifest.json` and `service-worker.js` are accessible
- Verify you're using PNG icons in the manifest
- Try clearing your browser cache

### Android Icons Missing

Android doesn't support SVG or ICO for app icons or notifications. Switch to PNG files.

### Push Notifications Not Working

- Check that notification permissions are granted
- Verify VAPID keys are configured correctly
- Confirm `minishlink/web-push` is installed
- Make sure the service worker registered successfully

## License

GNU General Public License v3.0
