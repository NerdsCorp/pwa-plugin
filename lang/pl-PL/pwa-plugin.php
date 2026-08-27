<?php

return [
    'navigation' => [
        'label' => 'PWA',
        'group' => 'Zaawansowane',
    ],
    'settings' => [
        'title' => 'Ustawienia PWA',
    ],
    'broadcast' => [
        'title' => 'Wyślij do wszystkich użytkowników PWA',
        'navigation_label' => 'Wyślij do wszystkich użytkowników PWA',
        'section_title' => 'Wyślij do wszystkich użytkowników PWA',
        'section_description' => 'Wyślij powiadomienie push do wszystkich aktywnych subskrypcji PWA.',
    ],
    'tabs' => [
        'manifest' => 'Manifest',
        'push' => 'Powiadomienia Push',
        'broadcast' => 'Wyślij do wszystkich użytkowników PWA',
        'actions' => 'Akcje',
    ],
    'fields' => [
        'theme_color' => [
            'label' => 'Kolor motywu',
            'helper' => 'Używane przez manifest oraz interfejsu przeglądarki.',
        ],
        'background_color' => [
            'label' => 'Kolor tła',
            'helper' => 'Kolor tła ekranu powitalnego.',
        ],
        'start_url' => [
            'label' => 'Początkowy adres URL',
            'helper' => 'Względny adres URL dla uruchomionej aplikacji PWA.',
        ],
        'cache_name' => [
            'label' => 'Nazwa Cache',
            'helper' => 'Wykorzystywane w pamięci Cache Service Worker.',
        ],
        'cache_version' => [
            'label' => 'Wersja Cache',
        ],
        'cache_enabled' => [
            'label' => 'Włącz offline Cache',
            'helper' => 'Wstępne zapisywanie adresów URL w pamięci cache i dostarczanie ich w trybie offline.',
        ],
        'cache_precache_urls' => [
            'label' => 'URL zapisane w pamięci cache',
            'helper' => 'Rozdzielona przecinkami lub nową linią lista adresów URL do zapisania w pamięci cache (np. /, /).',
        ],
        'manifest_icon_192' => [
            'label' => 'Ikona manifestu (192x192)',
            'helper' => 'System Android wymaga tutaj formatu PNG do ikony instalacji.',
        ],
        'manifest_icon_512' => [
            'label' => 'Ikona manifestu (512x512)',
            'helper' => 'System Android wymaga tutaj formatu PNG do ikony instalacji.',
        ],
        'apple_touch_icon' => [
            'label' => 'Ikona Apple Touch (domyślna)',
        ],
        'apple_touch_icon_152' => [
            'label' => 'Ikona Apple Touch (152x152)',
        ],
        'apple_touch_icon_167' => [
            'label' => 'Ikona Apple Touch (167x167)',
        ],
        'apple_touch_icon_180' => [
            'label' => 'Ikona Apple Touch (180x180)',
        ],
        'push_enabled' => [
            'label' => 'Włącz powiadomienia Push',
            'helper' => 'Wymaga kluczy VAPID i biblioteki Web Push.',
        ],
        'push_send_on_db' => [
            'label' => 'Wysyłaj powiadomienia Push dla powiadomień panelu',
            'helper' => 'Wysyła powiadomienie Push kiedy powiadomienie z panelu jest w bazie danych.',
        ],
        'push_send_on_mail' => [
            'label' => 'Wysyłaj powiadomienia Push dla powiadomień email',
            'helper' => 'Wysyłaj powiadomienia Push dla powiadomień, które tylko używają głównego kanału email',
        ],
        'push_notification_include_classes' => [
            'label' => 'Uwzględnij klasy powiadomień',
            'helper' => 'Opcjonalnie. Jedna klasa lub wzorzec wildcard na linię/przecinek. Gdy ustawione, wysyłane są tylko pasujące klasy.',
        ],
        'push_notification_exclude_classes' => [
            'label' => 'Wyklucz klasy powiadomień',
            'helper' => 'Opcjonalnie. Jedna klasa lub wzorzec wildcard na linię/przecinek. Wykluczenia zawsze mają pierwszeństwo.',
        ],
        'broadcast_title' => [
            'label' => 'Tytuł broadcastu',
        ],
        'broadcast_body' => [
            'label' => 'Treść broadcastu',
        ],
        'broadcast_url' => [
            'label' => 'URL po kliknięciu',
            'helper' => 'Dokąd użytkownik zostanie przeniesiony po kliknięciu powiadomienia.',
        ],
        'broadcast_icon' => [
            'label' => 'Nadpisz ikonę (opcjonalnie)',
            'helper' => 'Opcjonalna ikona tylko dla tego broadcastu.',
        ],
        'broadcast_badge' => [
            'label' => 'Nadpisz plakietkę (opcjonalnie)',
            'helper' => 'Opcjonalna plakietka tylko dla tego broadcastu.',
        ],
        'broadcast_require_interaction' => [
            'label' => 'Wymagaj interakcji',
            'helper' => 'Po włączeniu powiadomienie pozostaje widoczne aż do interakcji użytkownika.',
        ],
        'vapid_subject' => [
            'label' => 'Podmiot VAPID',
            'helper' => 'Zazwyczaj mailto: albo https: URL, np. mailto:admin@example.com',
        ],
        'vapid_public_key' => [
            'label' => 'Publiczny klucz VAPID',
        ],
        'vapid_private_key' => [
            'label' => 'Prywatny klucz VAPID',
        ],
        'default_notification_icon' => [
            'label' => 'Domyślna ikona powiadomień',
            'helper' => 'Domyślna ikona dla powiadomień Push.',
        ],
        'default_notification_badge' => [
            'label' => 'Domyślna plakietka powiadomień',
            'helper' => 'Domyślna plakietka dla powiadomień Push.',
        ],
    ],
    'actions' => [
        'install' => 'Zainstaluj PWA',
        'request_notifications' => 'Proś o powiadomienia',
        'subscribe' => 'Subskrybuj Push',
        'unsubscribe' => 'Odsubskrybuj',
        'test_push' => 'Wyślij testowego Pusha',
        'send_broadcast' => 'Wyślij broadcast do wszystkich',
        'save' => 'Zapisz',
    ],
    'notifications' => [
        'saved' => 'Zapisano ustawienia PWA.',
        'subscribed' => 'Pomyślnie zasubskrybowano powiadomienia Push.',
        'unsubscribed' => 'Pomyślnie odsubskrybowano.',
        'test_sent' => 'Testowe powiadomienie zostało wysłane.',
        'broadcast_queued' => 'Broadcast dodany do kolejki dla :count subskrypcji.',
        'broadcast_sent' => 'Broadcast wysłany do :sent z :total subskrypcji.',
    ],
    'errors' => [
        'table_missing' => 'Nie znaleziono tabeli subskrypcji Push',
        'unauthorized' => 'Nieautoryzowany dostęp.',
        'library_missing' => 'Nie znaleziono biblioteki Web Push.',
        'vapid_missing' => 'Nie znaleziono kluczy lub podmiotu VAPID.',
        'no_subscription' => 'Nie znaleziono subskrypcji dla tej przeglądarki.',
        'send_failed' => 'Nie udało się wysłać powiadomienia.',
        'push_disabled' => 'Powiadomienia push są wyłączone w ustawieniach.',
        'broadcast_required' => 'Tytuł i treść broadcastu są wymagane.',
        'unsupported' => 'Instalacja nie jest obecnie możliwa. Aplikacja może być już zainstalowana lub Twoja przeglądarka nie spełnia wymagań.',
        'install_android_title' => 'Zainstaluj na Androida',
        'install_android_body' => 'Otwórz menu przeglądarki i wybierz opcję „Zainstaluj aplikację” lub „Dodaj do ekranu głównego”.',
        'install_already' => 'Aplikacja jest już zainstalowana.',
        'install_ios_title' => 'Zainstaluj na iOS',
        'install_ios_body' => 'Otwórz tę stronę w przeglądarce Safari, wybierz opcję „Udostępnij”, a następnie „Dodaj do ekranu głównego”.',
        'png_required' => 'Ikony PNG są wymagane dla systemu Android i powiadomień.',
    ],
    'profile' => [
        'tab_label' => 'PWA',
        'section_heading' => 'Akcje PWA',
        'section_description' => 'Zarządzaj swoimi połączonymi urządzeniami oraz powiadomieniami.',
    ],
    'preferences' => [
        'section_title' => 'Preferencje powiadomień',
        'section_description' => 'Wybierz, jakie kategorie powiadomień chcesz otrzymywać i jak często.',
        'channels_label' => 'Kategorie powiadomień',
        'digest_mode_label' => 'Tryb dostarczania',
        'digest_mode_instant' => 'Natychmiast',
        'digest_mode_daily' => 'Dzienny podsumowanie',
        'quiet_hours_label' => 'Włącz godziny ciszy',
        'quiet_hours_start_label' => 'Początek godzin ciszy',
        'quiet_hours_end_label' => 'Koniec godzin ciszy',
        'max_per_day_label' => 'Maks. powiadomień dziennie',
        'save_button' => 'Zapisz preferencje',
        'saved' => 'Preferencje powiadomień zapisane.',
    ],
    'diagnostics' => [
        'title' => 'Sync Diagnostics',
        'refresh' => 'Odśwież diagnostykę',
        'unavailable' => 'niedostępne',
        'labels' => [
            'overall_status' => 'Stan ogólny',
            'pwa_users' => 'Użytkownicy PWA',
            'active_subscriptions' => 'Aktywne subskrypcje',
            'subscriptions_per_user' => 'Subskrypcje na użytkownika',
            'last_push_sent' => 'Ostatni wysłany push',
            'last_sync_server' => 'Ostatnia synchronizacja (serwer)',
            'last_subscription_refresh_server' => 'Ostatnie odświeżenie subskrypcji (serwer)',
            'queue_readiness' => 'Gotowość kolejki',
            'push_stack' => 'Stos push',
            'connection' => 'połączenie',
            'background' => 'tło',
            'enabled' => 'włączone',
            'library' => 'biblioteka',
            'vapid' => 'vapid',
            'queue' => 'queue',
            'push' => 'push',
            'subscribers' => 'subskrybenci',
            'activity' => 'aktywność',
        ],
        'status' => [
            'healthy' => 'zdrowy',
            'needs_attention' => 'wymaga uwagi',
            'ready' => 'gotowe',
            'not_ready' => 'niegotowe',
            'incomplete' => 'niekompletne',
            'ok' => 'ok',
            'issue' => 'problem',
            'none' => 'brak',
            'yes' => 'tak',
            'no' => 'nie',
            'unknown' => 'nieznane',
        ],
    ],
    'messages' => [
        'update_available' => 'Nowa wersja jest dostępna. Przeładować teraz?',
        'test_notification_body' => 'To jest testowe powiadomienie z twojego PWA.',
        'new_notification' => 'Masz nowe powiadomienie.',
    ],
    'manifest' => [
        'description' => 'Oficjalna aplikacja dla naszego panelu.',
        'shortcuts' => [
            'dashboard_name' => 'Panel',
            'dashboard_short' => 'Panel',
            'dashboard_description' => 'Zobacz swoje serwery',
        ],
    ],
];
