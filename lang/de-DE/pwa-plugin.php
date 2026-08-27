<?php

return [
    'navigation' => [
        'label' => 'PWA',
        'group' => 'Erweitert',
    ],
    'settings' => [
        'title' => 'PWA Einstellungen',
    ],
    'broadcast' => [
        'title' => 'An alle PWA-Benutzer senden',
        'navigation_label' => 'An alle PWA-Benutzer senden',
        'section_title' => 'An alle PWA-Benutzer senden',
        'section_description' => 'Senden Sie eine Push-Benachrichtigung an alle aktiven PWA-Abonnements.',
    ],
    'tabs' => [
        'manifest' => 'Manifest',
        'push' => 'Push-Benachrichtigungen',
        'broadcast' => 'An alle PWA-Benutzer senden',
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
        'cache_enabled' => [
            'label' => 'Offline-Cache aktivieren',
            'helper' => 'URLs vorab speichern und bei Offline-Nutzung aus dem Cache bedienen.',
        ],
        'cache_precache_urls' => [
            'label' => 'Vorgecachete URLs',
            'helper' => 'Kommagetrennte oder zeilenweise URLs zum Vercachen (z. B. /, /).',
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
        'push_notification_include_classes' => [
            'label' => 'Benachrichtigungsklassen einschließen',
            'helper' => 'Optional. Eine Klasse oder ein Wildcard-Muster pro Zeile/Komma. Wenn gesetzt, werden nur passende Klassen gesendet.',
        ],
        'push_notification_exclude_classes' => [
            'label' => 'Benachrichtigungsklassen ausschließen',
            'helper' => 'Optional. Eine Klasse oder ein Wildcard-Muster pro Zeile/Komma. Ausschlüsse haben immer Vorrang.',
        ],
        'broadcast_title' => [
            'label' => 'Broadcast-Titel',
        ],
        'broadcast_body' => [
            'label' => 'Broadcast-Nachricht',
        ],
        'broadcast_url' => [
            'label' => 'Klick-URL',
            'helper' => 'Zielseite, wenn ein Benutzer auf die Push-Benachrichtigung klickt.',
        ],
        'broadcast_icon' => [
            'label' => 'Icon überschreiben (optional)',
            'helper' => 'Optionales Icon nur für diesen Broadcast.',
        ],
        'broadcast_badge' => [
            'label' => 'Badge überschreiben (optional)',
            'helper' => 'Optionales Badge nur für diesen Broadcast.',
        ],
        'broadcast_require_interaction' => [
            'label' => 'Interaktion erforderlich',
            'helper' => 'Wenn aktiv, bleibt die Benachrichtigung sichtbar bis zur Interaktion.',
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
        'send_broadcast' => 'Broadcast an alle senden',
        'save' => 'Speichern',
    ],
    'notifications' => [
        'saved' => 'PWA-Einstellungen gespeichert.',
        'subscribed' => 'Push-Benachrichtigungen erfolgreich abonniert.',
        'unsubscribed' => 'Erfolgreich abgemeldet.',
        'test_sent' => 'Test-Benachrichtigung wurde gesendet.',
        'broadcast_queued' => 'Broadcast für :count Abonnement(s) in die Queue gestellt.',
        'broadcast_sent' => 'Broadcast an :sent von :total Abonnement(s) gesendet.',
    ],
    'errors' => [
        'table_missing' => 'Datenbanktabelle für Push-Abonnements fehlt.',
        'unauthorized' => 'Nicht autorisierter Zugriff.',
        'library_missing' => 'Web-Push Bibliothek nicht gefunden.',
        'vapid_missing' => 'VAPID-Schlüssel oder Betreff fehlen.',
        'no_subscription' => 'Kein Abonnement für diesen Browser gefunden.',
        'send_failed' => 'Senden der Benachrichtigung fehlgeschlagen.',
        'push_disabled' => 'Push-Benachrichtigungen sind in den Einstellungen deaktiviert.',
        'broadcast_required' => 'Broadcast-Titel und Nachricht sind erforderlich.',
        'unsupported' => 'Installation momentan nicht möglich. Die App ist eventuell bereits installiert oder Ihr Browser erfüllt die Anforderungen nicht.',
        'install_android_title' => 'Installation auf Android',
        'install_android_body' => 'Öffne das Browser-Menü und tippe auf „App installieren“ oder „Zum Startbildschirm hinzufügen“.',
        'install_already' => 'Die App ist bereits installiert.',
        'install_ios_title' => 'Installation auf iOS',
        'install_ios_body' => 'Öffne diese Seite in Safari, tippe auf Teilen und dann auf "Zum Home-Bildschirm".',
        'png_required' => 'PNG-Icons sind für Android und Benachrichtigungen erforderlich.',
    ],
    'profile' => [
        'tab_label' => 'PWA',
        'section_heading' => 'PWA Aktionen',
        'section_description' => 'Verwalten Sie Ihre Geräteverbindung und Benachrichtigungen.',
    ],
    'preferences' => [
        'section_title' => 'Benachrichtigungseinstellungen',
        'section_description' => 'Wählen Sie die Benachrichtigungskategorien aus, die Sie erhalten möchten und wie oft.',
        'channels_label' => 'Benachrichtigungskategorien',
        'digest_mode_label' => 'Zustellungsmodus',
        'digest_mode_instant' => 'Sofort',
        'digest_mode_daily' => 'Tägliche Zusammenfassung',
        'quiet_hours_label' => 'Bitte beachten Sie die Ruhezeiten',
        'quiet_hours_start_label' => 'Beginn der Ruhezeiten',
        'quiet_hours_end_label' => 'Ende der Ruhezeiten',
        'max_per_day_label' => 'Max. Benachrichtigungen pro Tag',
        'save_button' => 'Einstellungen speichern',
        'saved' => 'Benachrichtigungseinstellungen gespeichert.',
    ],
    'diagnostics' => [
        'title' => 'Sync Diagnostics',
        'refresh' => 'Diagnose aktualisieren',
        'unavailable' => 'nicht verfügbar',
        'labels' => [
            'overall_status' => 'Gesamtstatus',
            'pwa_users' => 'PWA-Benutzer',
            'active_subscriptions' => 'Aktive Abonnements',
            'subscriptions_per_user' => 'Abonnements pro Benutzer',
            'last_push_sent' => 'Letzter gesendeter Push',
            'last_sync_server' => 'Letzte Synchronisierung (Server)',
            'last_subscription_refresh_server' => 'Letzte Abo-Aktualisierung (Server)',
            'queue_readiness' => 'Queue-Bereitschaft',
            'push_stack' => 'Push-Stack',
            'connection' => 'Verbindung',
            'background' => 'Hintergrund',
            'enabled' => 'aktiviert',
            'library' => 'Bibliothek',
            'vapid' => 'vapid',
            'queue' => 'queue',
            'push' => 'push',
            'subscribers' => 'Abonnenten',
            'activity' => 'Aktivität',
        ],
        'status' => [
            'healthy' => 'gesund',
            'needs_attention' => 'benötigt Aufmerksamkeit',
            'ready' => 'bereit',
            'not_ready' => 'nicht bereit',
            'incomplete' => 'unvollständig',
            'ok' => 'ok',
            'issue' => 'Problem',
            'none' => 'keine',
            'yes' => 'ja',
            'no' => 'nein',
            'unknown' => 'unbekannt',
        ],
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
