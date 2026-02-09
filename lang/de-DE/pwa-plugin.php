<?php

return [
    'navigation' => [
        'label' => 'PWA',
    ],
    'settings' => [
        'title' => 'PWA Einstellungen',
    ],
    'tabs' => [
        'manifest' => 'Manifest',
        'push' => 'Push-Benachrichtigungen',
        'actions' => 'Aktionen',
    ],
    'fields' => [
        'theme_color' => [
            'label' => 'Designfarbe',
            'helper' => 'Wird vom Manifest und der Browser-UI genutzt.',
        ],
        'background_color' => [
            'label' => 'Hintergrundfarbe',
            'helper' => 'Hintergrundfarbe des Startbildschirms.',
        ],
        'start_url' => [
            'label' => 'Start-URL',
            'helper' => 'Relative URL für den PWA-Start.',
        ],
        'cache_name' => [
            'label' => 'Cache-Name',
            'helper' => 'Wird im Service-Worker-Cache genutzt.',
        ],
        'cache_version' => [
            'label' => 'Cache-Version',
        ],
        'manifest_icon_192' => [
            'label' => 'Manifest-Icon (192x192)',
            'helper' => 'Android benötigt hier ein PNG für das Installations-Icon.',
        ],
        'manifest_icon_512' => [
            'label' => 'Manifest-Icon (512x512)',
            'helper' => 'Android benötigt hier ein PNG für das Installations-Icon.',
        ],
        'apple_touch_icon' => [
            'label' => 'Apple Touch Icon (Standard)',
        ],
        'apple_touch_icon_152' => [
            'label' => 'Apple Touch Icon (152x152)',
        ],
        'apple_touch_icon_167' => [
            'label' => 'Apple Touch Icon (167x167)',
        ],
        'apple_touch_icon_180' => [
            'label' => 'Apple Touch Icon (180x180)',
        ],
        'push_enabled' => [
            'label' => 'Push-Benachrichtigungen aktivieren',
            'helper' => 'Erfordert VAPID-Schlüssel und die Web-Push-Bibliothek.',
        ],
        'push_send_on_db' => [
            'label' => 'Push für Panel-Benachrichtigungen senden',
            'helper' => 'Sendet Push, wenn eine Benachrichtigung in der Datenbank gespeichert wird.',
        ],
        'push_send_on_mail' => [
            'label' => 'Push für E-Mail-Benachrichtigungen senden',
            'helper' => 'Sendet Push für Benachrichtigungen, die nur den E-Mail-Kanal nutzen.',
        ],
        'vapid_subject' => [
            'label' => 'VAPID-Betreff',
            'helper' => 'Normalerweise eine mailto: oder https: URL, z. B. mailto:admin@beispiel.de',
        ],
        'vapid_public_key' => [
            'label' => 'Öffentlicher VAPID-Schlüssel',
        ],
        'vapid_private_key' => [
            'label' => 'Privater VAPID-Schlüssel',
        ],
        'default_notification_icon' => [
            'label' => 'Standard-Benachrichtigungs-Icon',
            'helper' => 'Standard-Icon für Push-Benachrichtigungen.',
        ],
        'default_notification_badge' => [
            'label' => 'Standard-Benachrichtigungs-Badge',
            'helper' => 'Standard-Badge für Push-Benachrichtigungen.',
        ],
    ],
    'actions' => [
        'install' => 'PWA installieren',
        'request_notifications' => 'Benachrichtigungen anfordern',
        'subscribe' => 'Push abonnieren',
        'unsubscribe' => 'Abmelden',
        'test_push' => 'Test-Push senden',
        'save' => 'Speichern',
    ],
    'notifications' => [
        'saved' => 'PWA-Einstellungen gespeichert.',
        'subscribed' => 'Push-Benachrichtigungen erfolgreich abonniert.',
        'unsubscribed' => 'Erfolgreich abgemeldet.',
        'test_sent' => 'Test-Benachrichtigung wurde gesendet.',
    ],
    'errors' => [
        'table_missing' => 'Datenbanktabelle für Push-Abonnements fehlt.',
        'unauthorized' => 'Nicht autorisierter Zugriff.',
        'library_missing' => 'Web-Push Bibliothek nicht gefunden.',
        'vapid_missing' => 'VAPID-Schlüssel oder Betreff fehlen.',
        'no_subscription' => 'Kein Abonnement für diesen Browser gefunden.',
        'send_failed' => 'Senden der Benachrichtigung fehlgeschlagen.',
        'unsupported' => 'Installation momentan nicht möglich. Die App ist eventuell bereits installiert oder Ihr Browser erfüllt die Anforderungen nicht.',
    ],
    'profile' => [
        'tab_label' => 'PWA',
        'section_heading' => 'PWA Aktionen',
        'section_description' => 'Verwalten Sie Ihre Geräteverbindung und Benachrichtigungen.',
    ],
    'messages' => [
        'update_available' => 'Eine neue Version ist verfügbar. Jetzt neu laden?',
        'test_notification_body' => 'Dies ist eine Test-Benachrichtigung deiner PWA.',
        'new_notification' => 'Du hast eine neue Benachrichtigung.',
    ],
    'manifest' => [
        'description' => 'Die offizielle App für unser Panel.',
        'shortcuts' => [
            'dashboard_name' => 'Dashboard',
            'dashboard_short' => 'Dashboard',
            'dashboard_description' => 'Deine Server ansehen',
        ],
    ],
]; 