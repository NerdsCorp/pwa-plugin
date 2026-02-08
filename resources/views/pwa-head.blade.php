{{-- PWA Meta Tags --}}
<meta name="application-name" content="{{ config('app.name', 'Pelican Panel') }}">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
<meta name="apple-mobile-web-app-title" content="{{ config('app.name', 'Pelican Panel') }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="theme-color" content="{{ app(\PwaPlugin\Services\PwaSettingsRepository::class)->get('theme_color', config('pwa.theme_color', '#0ea5e9')) }}">

{{-- PWA Manifest --}}
<link rel="manifest" href="{{ route('pwa.manifest') }}">

{{-- Apple Touch Icons --}}
@php
    $pwaSettings = app(\PwaPlugin\Services\PwaSettingsRepository::class);
    $appleDefault = $pwaSettings->get('apple_touch_icon', config('pwa.apple_touch_icon', '/pelican.svg'));
    $apple152 = $pwaSettings->get('apple_touch_icon_152', config('pwa.apple_touch_icon_152', $appleDefault));
    $apple167 = $pwaSettings->get('apple_touch_icon_167', config('pwa.apple_touch_icon_167', $appleDefault));
    $apple180 = $pwaSettings->get('apple_touch_icon_180', config('pwa.apple_touch_icon_180', $appleDefault));
@endphp
<link rel="apple-touch-icon" href="{{ asset(ltrim($appleDefault, '/')) }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ asset(ltrim($apple152, '/')) }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ asset(ltrim($apple180, '/')) }}">
<link rel="apple-touch-icon" sizes="167x167" href="{{ asset(ltrim($apple167, '/')) }}">

{{-- Service Worker Registration & Notifications --}}
<script>
window.pwaConfig = {
    vapidPublicKey: @json(app(\PwaPlugin\Services\PwaSettingsRepository::class)->get('vapid_public_key', config('pwa.vapid_public_key'))),
    pushEnabled: @json(app(\PwaPlugin\Services\PwaSettingsRepository::class)->get('push_enabled', config('pwa.push_enabled', false))),
    routes: {
        subscribe: @json(route('pwa.subscribe')),
        unsubscribe: @json(route('pwa.unsubscribe')),
        test: @json(route('pwa.test')),
    },
};

function pwaCsrfToken() {
    const token = document.querySelector('meta[name="csrf-token"]');
    return token ? token.getAttribute('content') : '';
}

window.pwaRequestNotifications = function() {
    if (!('Notification' in window)) {
        console.error('PWA: Notifications not supported');
        return Promise.resolve('unsupported');
    }

    return Notification.requestPermission().then(function(permission) {
        console.log('PWA: Notification permission:', permission);
        return permission;
    });
};

function pwaUrlBase64ToUint8Array(base64String) {
    const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
    const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
    const rawData = window.atob(base64);
    const outputArray = new Uint8Array(rawData.length);

    for (let i = 0; i < rawData.length; ++i) {
        outputArray[i] = rawData.charCodeAt(i);
    }

    return outputArray;
}

window.pwaRegisterPush = async function() {
    if (!window.pwaConfig?.pushEnabled) {
        console.warn('PWA: Push is disabled in settings');
        return null;
    }

    if (!window.pwaConfig?.vapidPublicKey) {
        console.error('PWA: Missing VAPID public key');
        return null;
    }

    const subscription = await window.pwaSubscribePush(window.pwaConfig.vapidPublicKey);
    if (!subscription) {
        return null;
    }

    const response = await fetch(window.pwaConfig.routes.subscribe, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': pwaCsrfToken(),
        },
        body: JSON.stringify(subscription),
    });

    if (!response.ok) {
        console.error('PWA: Failed to store subscription');
        return null;
    }

    console.log('PWA: Subscription stored');
    return subscription;
};

window.pwaUnregisterPush = async function() {
    const registration = await navigator.serviceWorker.ready;
    const subscription = await registration.pushManager.getSubscription();

    if (!subscription) {
        return false;
    }

    await subscription.unsubscribe();

    await fetch(window.pwaConfig.routes.unsubscribe, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': pwaCsrfToken(),
        },
        body: JSON.stringify({ endpoint: subscription.endpoint }),
    });

    console.log('PWA: Subscription removed');
    return true;
};

window.pwaSendTestPush = async function() {
    const response = await fetch(window.pwaConfig.routes.test, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': pwaCsrfToken(),
        },
    });

    const payload = await response.json().catch(() => ({}));
    console.log('PWA: Test push', payload.message || response.status);
};

if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('{{ route('pwa.sw') }}')
            .then(function(registration) {
                console.log('PWA: Service Worker registered', registration.scope);
                
                // Check for updates
                registration.addEventListener('updatefound', function() {
                    const newWorker = registration.installing;
                    newWorker.addEventListener('statechange', function() {
                        if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                            if (confirm('A new version is available. Reload to update?')) {
                                window.location.reload();
                            }
                        }
                    });
                });
            })
            .catch(function(error) {
                console.log('PWA: Service Worker registration failed:', error);
            });
    });
}

// Install prompt handling
let deferredPrompt;
window.addEventListener('beforeinstallprompt', function(e) {
    e.preventDefault();
    deferredPrompt = e;
    console.log('PWA: Install prompt available');
    
    // Make install function available globally
    window.pwaInstall = function() {
        if (deferredPrompt) {
            deferredPrompt.prompt();
            deferredPrompt.userChoice.then(function(choiceResult) {
                console.log('PWA: User', choiceResult.outcome);
                deferredPrompt = null;
            });
        }
    };
});

window.addEventListener('appinstalled', function() {
    console.log('PWA: App installed successfully');
    deferredPrompt = null;
});

// Helper function to subscribe to push notifications
window.pwaSubscribePush = async function(publicKey) {
    if (!('serviceWorker' in navigator) || !('PushManager' in window)) {
        console.error('PWA: Push notifications not supported');
        return null;
    }

    try {
        const registration = await navigator.serviceWorker.ready;
        
        // Request notification permission
        const permission = await window.pwaRequestNotifications();
        if (permission !== 'granted') {
            console.log('PWA: Notification permission denied');
            return null;
        }

        const applicationServerKey = publicKey instanceof Uint8Array
            ? publicKey
            : pwaUrlBase64ToUint8Array(publicKey);

        // Subscribe to push notifications
        const subscription = await registration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: applicationServerKey
        });

        console.log('PWA: Push subscription created', subscription);
        return subscription;
    } catch (error) {
        console.error('PWA: Push subscription failed:', error);
        return null;
    }
};

// Helper function to unsubscribe from push notifications
window.pwaUnsubscribePush = async function() {
    try {
        const registration = await navigator.serviceWorker.ready;
        const subscription = await registration.pushManager.getSubscription();
        
        if (subscription) {
            await subscription.unsubscribe();
            console.log('PWA: Unsubscribed from push notifications');
            return true;
        }
        return false;
    } catch (error) {
        console.error('PWA: Unsubscribe failed:', error);
        return false;
    }
};
</script>
