<?php

return [
    'navigation' => [
        'label' => 'PWA',
        'group' => 'Avancé',
    ],
    'settings' => [
        'title' => 'Paramètres PWA',
    ],
    'broadcast' => [
        'title' => 'Diffuser à tous les utilisateurs PWA',
        'navigation_label' => 'Notification PWA globale',
        'section_title' => 'Notification PWA globale',
        'section_description' => 'Envoyer une notification push à tous les abonnements PWA actifs.',
    ],
    'tabs' => [
        'manifest' => 'Manifest',
        'push' => 'Notifications Push',
        'broadcast' => 'Notification PWA globale',
        'actions' => 'Actions',
    ],
    'fields' => [
        'theme_color' => [
            'label' => 'Couleur du thème',
            'helper' => 'Utilisée par le manifest et l’interface utilisateur du navigateur.',
        ],
        'background_color' => [
            'label' => 'Couleur de fond',
            'helper' => 'Couleur d’arrière-plan de l’écran de chargement.',
        ],
        'start_url' => [
            'label' => 'Start-URL',
            'helper' => 'URL relative utilisée pour le démarrage de la PWA',
        ],
        'cache_name' => [
            'label' => 'Nom du cache',
            'helper' => 'Utilisé dans le cache du service worker.',
        ],
        'cache_version' => [
            'label' => 'Version du cache',
        ],
        'cache_enabled' => [
            'label' => 'Activer le cache hors ligne',
            'helper' => 'Précharger les URL dans le cache et utiliser les réponses mises en cache lorsque l’utilisateur est hors ligne.',
        ],
        'cache_precache_urls' => [
            'label' => 'URLs à précharger',
            'helper' => 'Liste d’URLs à précharger, séparées par des virgules ou des sauts de ligne (ex. /, /).',
        ],
        'manifest_icon_192' => [
            'label' => 'Manifest icon (192x192)',
            'helper' => 'Android nécessite un fichier PNG pour l’icône d’installation.',
        ],
        'manifest_icon_512' => [
            'label' => 'Manifest icon (512x512)',
            'helper' => 'Android nécessite un fichier PNG pour l’icône d’installation.',
        ],
        'apple_touch_icon' => [
            'label' => 'Apple touch icon (default)',
        ],
        'apple_touch_icon_152' => [
            'label' => 'Apple touch icon (152x152)',
        ],
        'apple_touch_icon_167' => [
            'label' => 'Apple touch icon (167x167)',
        ],
        'apple_touch_icon_180' => [
            'label' => 'Apple touch icon (180x180)',
        ],
        'push_enabled' => [
            'label' => 'Activer les notifications push',
            'helper' => 'Nécessite des clés VAPID et la bibliothèque Web Push.',
        ],
        'push_send_on_db' => [
            'label' => 'Envoyer les notifications push pour les notifications du panel',
            'helper' => 'Envoie une notification push quand une notification est stockée dans la base de données.',
        ],
        'push_send_on_mail' => [
            'label' => 'Envoyer les notifications push pour les notifications par email',
            'helper' => 'Envoie une notification push pour les notifications qui utilisent uniquement le canal email.',
        ],
        'broadcast_title' => [
            'label' => 'Titre de la notification',
        ],
        'broadcast_body' => [
            'label' => 'Corps du message',
        ],
        'broadcast_url' => [
            'label' => 'URL de clic',
            'helper' => 'Ouvre cette URL lorsque l’utilisateur clique sur la notification push.',
        ],
        'broadcast_icon' => [
            'label' => 'Icône de remplacement (facultatif)',
            'helper' => 'URL ou chemin de l’icône optionnelle pour cette diffusion uniquement.',
        ],
        'broadcast_badge' => [
            'label' => 'Remplacement du badge (facultatif)',
            'helper' => 'URL ou chemin du badge optionnel pour cette diffusion uniquement.',
        ],
        'broadcast_require_interaction' => [
            'label' => 'Nécessite une interaction',
            'helper' => 'Si activé, la notification reste visible jusqu’à l’interaction de l’utilisateur.',
        ],
        'vapid_subject' => [
            'label' => 'Sujet VAPID',
            'helper' => 'Il s’agit généralement d’une URL mailto: ou https:, par exemple mailto:admin@example.com',
        ],
        'vapid_public_key' => [
            'label' => 'Clé publique VAPID',
        ],
        'vapid_private_key' => [
            'label' => 'Clé privée VAPID',
        ],
        'default_notification_icon' => [
            'label' => 'Icône de notification par défaut',
            'helper' => 'Icône par défaut pour les notifications push.',
        ],
        'default_notification_badge' => [
            'label' => 'Badge de notification par défaut',
            'helper' => 'Badge par défaut pour les notifications push.',
        ],
    ],
    'actions' => [
        'install' => 'Installer PWA',
        'request_notifications' => 'Activer les notifications',
        'subscribe' => 'S’abonner',
        'unsubscribe' => 'Se désabonner',
        'test_push' => 'Envoyer une notification de test',
        'send_broadcast' => 'Envoyer une diffusion à tous',
        'save' => 'Sauvegarder',
    ],
    'notifications' => [
        'saved' => 'Paramètres PWA enregistrés.',
        'subscribed' => 'Abonné aux notifications push avec succès.',
        'unsubscribed' => 'Désabonné avec succès.',
        'test_sent' => 'Notification de test envoyée.',
        'broadcast_queued' => 'Diffusion mise en file d’attente pour :count abonnement(s).',
        'broadcast_sent' => 'Diffusion envoyée à :sent sur :total abonnement(s).',
    ],
    'errors' => [
        'table_missing' => 'La table des abonnements push est manquante.',
        'unauthorized' => 'Accès non autorisé.',
        'library_missing' => 'La bibliothèque Web Push est introuvable.',
        'vapid_missing' => 'Les clés VAPID ou le sujet sont manquants.',
        'no_subscription' => 'Aucun abonnement trouvé pour ce navigateur.',
        'send_failed' => 'Échec de l’envoi de la notification.',
        'push_disabled' => 'Les notifications push sont désactivées dans les paramètres.',
        'broadcast_required' => 'Le titre et le message de diffusion sont requis.',
        'unsupported' => 'L’installation est actuellement impossible. L’application est peut-être déjà installée ou votre navigateur ne répond pas aux exigences.',
        'install_android_title' => 'Installer sur Android',
        'install_android_body' => 'Ouvrez le menu du navigateur et appuyez sur « Installer l’application » ou « Ajouter à l’écran d’accueil ».',
        'install_already' => 'L’application est déjà installée.',
        'install_ios_title' => 'Installer sur iOS',
        'install_ios_body' => 'Ouvrez cette page dans Safari, appuyez sur Partager, puis « Ajouter à l’écran d’accueil ».',
        'png_required' => 'Les icônes PNG sont requises pour Android et les notifications.',
    ],
    'profile' => [
        'tab_label' => 'PWA',
        'section_heading' => 'Actions PWA',
        'section_description' => 'Gérez la connexion et les notifications de votre appareil.',
    ],
    'diagnostics' => [
        'title' => 'Diagnostic de synchronisation',
        'refresh' => 'Actualiser les diagnostics',
        'loading' => 'Chargement des diagnostics...',
        'checking' => 'vérification...',
        'unavailable' => 'non disponible',
        'labels' => [
            'overall_status' => 'État général',
            'pwa_users' => 'Utilisateurs PWA',
            'active_subscriptions' => 'Abonnements actifs',
            'subscriptions_per_user' => 'Abonnements par utilisateur',
            'last_push_sent' => 'Dernière notification envoyée',
            'last_sync_server' => 'Dernière synchronisation (serveur)',
            'last_subscription_refresh_server' => 'Dernière actualisation de l’abonnement (serveur)',
            'queue_readiness' => 'Préparation de la file d’attente',
            'push_stack' => 'Pile de notifications',
            'connection' => 'connexion',
            'background' => 'arrière-plan',
            'enabled' => 'activé',
            'library' => 'bibliothèque',
            'vapid' => 'vapid',
            'queue' => 'file d’attente',
            'push' => 'push',
            'subscribers' => 'abonnés',
            'activity' => 'activité',
        ],
        'status' => [
            'healthy' => 'en bonne santé',
            'needs_attention' => 'besoin d’attention',
            'ready' => 'prêt',
            'not_ready' => 'non prêt',
            'incomplete' => 'incomplet',
            'ok' => 'ok',
            'issue' => 'problème',
            'none' => 'aucun',
            'yes' => 'oui',
            'no' => 'non',
            'unknown' => 'inconnu',
        ],
    ],
    'messages' => [
        'update_available' => 'Une nouvelle version est disponible. Recharger maintenant ?',
        'test_notification_body' => 'Ceci est une notification de test de PWA.',
        'new_notification' => 'Vous avez une nouvelle notification.',
    ],
    'manifest' => [
        'description' => 'L’application officielle de notre panel.',
        'shortcuts' => [
            'dashboard_name' => 'Tableau de bord',
            'dashboard_short' => 'Tableau de bord',
            'dashboard_description' => 'Consultez vos serveurs',
        ],
    ],
];
