<?php

return [
    'navigation' => [
        'label' => 'PWA',
        'group' => 'Geavanceerd',
    ],
    'settings' => [
        'title' => 'PWA-instellingen',
    ],
    'broadcast' => [
        'title' => 'Naar alle PWA-gebruikers verzenden',
        'navigation_label' => 'Naar alle PWA-gebruikers verzenden',
        'section_title' => 'Naar alle PWA-gebruikers verzenden',
        'section_description' => 'Stuur een pushmelding naar alle actieve PWA-abonnementen.',
    ],
    'tabs' => [
        'manifest' => 'Manifest',
        'push' => 'Pushmeldingen',
        'broadcast' => 'Naar alle PWA-gebruikers verzenden',
        'actions' => 'Acties',
    ],
    'fields' => [
        'theme_color' => [
            'label' => 'Themakleur',
            'helper' => 'Gebruikt door het manifest en de browserinterface.',
        ],
        'background_color' => [
            'label' => 'Achtergrondkleur',
            'helper' => 'Achtergrondkleur van het opstartscherm.',
        ],
        'start_url' => [
            'label' => 'Start-URL',
            'helper' => 'Relatieve URL waarmee de PWA wordt gestart.',
        ],
        'cache_name' => [
            'label' => 'Cache-naam',
            'helper' => 'Gebruikt in de Service Worker cache.',
        ],
        'cache_version' => [
            'label' => 'Cacheversie',
        ],
        'cache_enabled' => [
            'label' => 'Offline cache inschakelen',
            'helper' => 'Slaat URL’s vooraf op in de cache en levert ze offline aan.',
        ],
        'cache_precache_urls' => [
            'label' => 'URL’s voor caching',
            'helper' => 'Door komma’s of nieuwe regels gescheiden lijst met URL’s om in de cache op te slaan (bijv. /, /).',
        ],
        'manifest_icon_192' => [
            'label' => 'Manifest-icoon (192x192)',
            'helper' => 'Android vereist hier een PNG-bestand voor het installatie-icoon.',
        ],
        'manifest_icon_512' => [
            'label' => 'Manifest-icoon (512x512)',
            'helper' => 'Android vereist hier een PNG-bestand voor het installatie-icoon.',
        ],
        'apple_touch_icon' => [
            'label' => 'Apple Touch-icoon (standaard)',
        ],
        'apple_touch_icon_152' => [
            'label' => 'Apple Touch-icoon (152x152)',
        ],
        'apple_touch_icon_167' => [
            'label' => 'Apple Touch-icoon (167x167)',
        ],
        'apple_touch_icon_180' => [
            'label' => 'Apple Touch-icoon (180x180)',
        ],
        'push_enabled' => [
            'label' => 'Pushmeldingen inschakelen',
            'helper' => 'Vereist VAPID-sleutels en de Web Push-bibliotheek.',
        ],
        'push_send_on_db' => [
            'label' => 'Pushmeldingen verzenden bij paneelmeldingen',
            'helper' => 'Verstuurt een pushmelding wanneer een paneelmelding in de database wordt opgeslagen.',
        ],
        'push_send_on_mail' => [
            'label' => 'Pushmeldingen verzenden bij e-mailmeldingen',
            'helper' => 'Verstuurt pushmeldingen voor meldingen die alleen via e-mail worden verzonden.',
        ],
        'push_notification_include_classes' => [
            'label' => 'Meldingsklassen opnemen',
            'helper' => 'Optioneel. Een klasse of wildcard-patroon per regel/komma. Indien ingesteld, worden alleen overeenkomende klassen verzonden.',
        ],
        'push_notification_exclude_classes' => [
            'label' => 'Meldingsklassen uitsluiten',
            'helper' => 'Optioneel. Een klasse of wildcard-patroon per regel/komma. Uitsluitingen hebben altijd voorrang.',
        ],
        'broadcast_title' => [
            'label' => 'Broadcast-titel',
        ],
        'broadcast_body' => [
            'label' => 'Broadcast-bericht',
        ],
        'broadcast_url' => [
            'label' => 'Klik-URL',
            'helper' => 'Waar gebruikers heen gaan als ze op de pushmelding klikken.',
        ],
        'broadcast_icon' => [
            'label' => 'Icoon overschrijven (optioneel)',
            'helper' => 'Optioneel icoon alleen voor deze broadcast.',
        ],
        'broadcast_badge' => [
            'label' => 'Badge overschrijven (optioneel)',
            'helper' => 'Optionele badge alleen voor deze broadcast.',
        ],
        'broadcast_require_interaction' => [
            'label' => 'Interactie vereist',
            'helper' => 'Indien ingeschakeld blijft de melding zichtbaar tot de gebruiker reageert.',
        ],
        'vapid_subject' => [
            'label' => 'VAPID-onderwerp',
            'helper' => 'Meestal mailto: of een https:-URL, bijv. mailto:admin@example.com',
        ],
        'vapid_public_key' => [
            'label' => 'Publieke VAPID-sleutel',
        ],
        'vapid_private_key' => [
            'label' => 'Private VAPID-sleutel',
        ],
        'default_notification_icon' => [
            'label' => 'Standaard meldingsicoon',
            'helper' => 'Standaard icoon voor pushmeldingen.',
        ],
        'default_notification_badge' => [
            'label' => 'Standaard meldingsbadge',
            'helper' => 'Standaard badge voor pushmeldingen.',
        ],
    ],
    'actions' => [
        'install' => 'PWA installeren',
        'request_notifications' => 'Meldingen aanvragen',
        'subscribe' => 'Abonneren op push',
        'unsubscribe' => 'Afmelden',
        'test_push' => 'Test-push verzenden',
        'send_broadcast' => 'Broadcast naar iedereen verzenden',
        'save' => 'Opslaan',
    ],
    'notifications' => [
        'saved' => 'PWA-instellingen opgeslagen.',
        'subscribed' => 'Succesvol geabonneerd op pushmeldingen.',
        'unsubscribed' => 'Succesvol afgemeld.',
        'test_sent' => 'Testmelding is verzonden.',
        'broadcast_queued' => 'Broadcast in wachtrij geplaatst voor :count abonnement(en).',
        'broadcast_sent' => 'Broadcast verzonden naar :sent van :total abonnement(en).',
    ],
    'errors' => [
        'table_missing' => 'Push-abonnementstabel niet gevonden.',
        'unauthorized' => 'Ongeautoriseerde toegang.',
        'library_missing' => 'Web Push-bibliotheek niet gevonden.',
        'vapid_missing' => 'VAPID-sleutels of onderwerp niet gevonden.',
        'no_subscription' => 'Geen abonnement gevonden voor deze browser.',
        'send_failed' => 'Verzenden van melding mislukt.',
        'push_disabled' => 'Pushmeldingen zijn uitgeschakeld in de instellingen.',
        'broadcast_required' => 'Broadcast-titel en bericht zijn verplicht.',
        'unsupported' => 'Installatie is momenteel niet mogelijk. De app is mogelijk al geïnstalleerd of je browser voldoet niet aan de vereisten.',
        'install_android_title' => 'Installeren op Android',
        'install_android_body' => 'Open het browsermenu en kies “App installeren” of “Toevoegen aan startscherm”.',
        'install_already' => 'De app is al geïnstalleerd.',
        'install_ios_title' => 'Installeren op iOS',
        'install_ios_body' => 'Open deze pagina in Safari, kies “Delen” en vervolgens “Zet op beginscherm”.',
        'png_required' => 'PNG-iconen zijn vereist voor Android en pushmeldingen.',
    ],
    'profile' => [
        'tab_label' => 'PWA',
        'section_heading' => 'PWA-acties',
        'section_description' => 'Beheer je gekoppelde apparaten en meldingen.',
    ],
    'preferences' => [
        'section_title' => 'Meldingsvoorkeuren',
        'section_description' => 'Kies welke meldingscategorieën je wilt ontvangen en hoe vaak.',
        'channels_label' => 'Meldingscategorieën',
        'digest_mode_label' => 'Afleveringsmodus',
        'digest_mode_instant' => 'Direct',
        'digest_mode_daily' => 'Dagelijks overzicht',
        'quiet_hours_label' => 'Rusturen inschakelen',
        'quiet_hours_start_label' => 'Start rusturen',
        'quiet_hours_end_label' => 'Einde rusturen',
        'max_per_day_label' => 'Max. meldingen per dag',
        'save_button' => 'Voorkeuren opslaan',
        'saved' => 'Meldingsvoorkeuren opgeslagen.',
    ],
    'diagnostics' => [
        'title' => 'Sync Diagnostics',
        'refresh' => 'Diagnostiek vernieuwen',
        'unavailable' => 'niet beschikbaar',
        'labels' => [
            'overall_status' => 'Algemene status',
            'pwa_users' => 'PWA-gebruikers',
            'active_subscriptions' => 'Actieve abonnementen',
            'subscriptions_per_user' => 'Abonnementen per gebruiker',
            'last_push_sent' => 'Laatst verzonden push',
            'last_sync_server' => 'Laatste synchronisatie (server)',
            'last_subscription_refresh_server' => 'Laatste abonnementsverversing (server)',
            'queue_readiness' => 'Queue-gereedheid',
            'push_stack' => 'Push-stack',
            'connection' => 'verbinding',
            'background' => 'achtergrond',
            'enabled' => 'ingeschakeld',
            'library' => 'bibliotheek',
            'vapid' => 'vapid',
            'queue' => 'queue',
            'push' => 'push',
            'subscribers' => 'abonnees',
            'activity' => 'activiteit',
        ],
        'status' => [
            'healthy' => 'gezond',
            'needs_attention' => 'vereist aandacht',
            'ready' => 'klaar',
            'not_ready' => 'niet klaar',
            'incomplete' => 'onvolledig',
            'ok' => 'ok',
            'issue' => 'probleem',
            'none' => 'geen',
            'yes' => 'ja',
            'no' => 'nee',
            'unknown' => 'onbekend',
        ],
    ],
    'messages' => [
        'update_available' => 'Er is een nieuwe versie beschikbaar. Nu vernieuwen?',
        'test_notification_body' => 'Dit is een testmelding van jouw PWA.',
        'new_notification' => 'Je hebt een nieuwe melding.',
    ],
    'manifest' => [
        'description' => 'Officiële applicatie voor ons paneel.',
        'shortcuts' => [
            'dashboard_name' => 'Dashboard',
            'dashboard_short' => 'Dashboard',
            'dashboard_description' => 'Bekijk je servers',
        ],
    ],
];
