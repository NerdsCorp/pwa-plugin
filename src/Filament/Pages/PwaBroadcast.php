<?php

namespace PwaPlugin\Filament\Pages;

use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions as SchemaActions;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Contracts\HasSchemas;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use PwaPlugin\Models\PwaPushSubscription;
use PwaPlugin\Services\PwaPushService;
use PwaPlugin\Services\PwaSettingsRepository;

class PwaBroadcast extends Page implements HasSchemas
{
    use InteractsWithForms;

    protected string $view = 'filament.pages.settings';

    protected static ?string $slug = 'pwa-broadcast';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-megaphone';

    protected static ?int $navigationSort = 91;

    public ?array $data = [];

    public function getTitle(): string
    {
        return trans('pwa-plugin::pwa-plugin.broadcast.title');
    }

    public static function getNavigationLabel(): string
    {
        return trans('pwa-plugin::pwa-plugin.broadcast.navigation_label');
    }

    public static function getNavigationGroup(): string|\UnitEnum|null
    {
        return trans('pwa-plugin::pwa-plugin.navigation.group');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return Filament::getCurrentPanel()?->getId() === 'admin';
    }

    protected function getFormStatePath(): ?string
    {
        return 'data';
    }

    public function mount(PwaSettingsRepository $settings): void
    {
        $this->data = [
            'broadcast_title' => '',
            'broadcast_body' => '',
            'broadcast_url' => '/',
            'broadcast_icon' => $settings->get('default_notification_icon', config('pwa-plugin.default_notification_icon', '/pelican.svg')),
            'broadcast_badge' => $settings->get('default_notification_badge', config('pwa-plugin.default_notification_badge', '/pelican.svg')),
        ];

        $this->form->fill($this->data);
    }

    protected function getFormSchema(): array
    {
        return [
            Section::make(trans('pwa-plugin::pwa-plugin.broadcast.section_title'))
                ->description(trans('pwa-plugin::pwa-plugin.broadcast.section_description'))
                ->schema([
                    TextInput::make('broadcast_title')
                        ->label(trans('pwa-plugin::pwa-plugin.fields.broadcast_title.label'))
                        ->required()
                        ->maxLength(120),
                    Textarea::make('broadcast_body')
                        ->label(trans('pwa-plugin::pwa-plugin.fields.broadcast_body.label'))
                        ->required()
                        ->rows(4)
                        ->maxLength(300),
                    TextInput::make('broadcast_url')
                        ->label(trans('pwa-plugin::pwa-plugin.fields.broadcast_url.label'))
                        ->helperText(trans('pwa-plugin::pwa-plugin.fields.broadcast_url.helper'))
                        ->maxLength(255),
                    Group::make()->columns(2)->schema([
                        TextInput::make('broadcast_icon')
                            ->label(trans('pwa-plugin::pwa-plugin.fields.broadcast_icon.label'))
                            ->helperText(trans('pwa-plugin::pwa-plugin.fields.broadcast_icon.helper'))
                            ->maxLength(255),
                        TextInput::make('broadcast_badge')
                            ->label(trans('pwa-plugin::pwa-plugin.fields.broadcast_badge.label'))
                            ->helperText(trans('pwa-plugin::pwa-plugin.fields.broadcast_badge.helper'))
                            ->maxLength(255),
                    ]),
                    SchemaActions::make([
                        Action::make('broadcast_send')
                            ->label(trans('pwa-plugin::pwa-plugin.actions.send_broadcast'))
                            ->icon('heroicon-o-paper-airplane')
                            ->color('warning')
                            ->action('sendBroadcast')
                            ->authorize(fn () => user()?->can('update settings')),
                    ])->fullWidth(),
                ]),
        ];
    }

    public function sendBroadcast(PwaSettingsRepository $settings, PwaPushService $push): void
    {
        if (!Schema::hasTable('pwa_push_subscriptions')) {
            Log::error('PWA broadcast could not run because the subscriptions table is missing.');
            Notification::make()->title(trans('pwa-plugin::pwa-plugin.errors.table_missing'))->danger()->send();

            return;
        }

        if (!$push->canSend()) {
            Log::error('PWA broadcast could not run because the Web Push library is unavailable.');
            Notification::make()->title(trans('pwa-plugin::pwa-plugin.errors.library_missing'))->danger()->send();

            return;
        }

        if (!(bool) $settings->get('push_enabled', config('pwa-plugin.push_enabled', false))) {
            Log::warning('PWA broadcast was requested while push notifications are disabled in plugin settings.');
            Notification::make()->title(trans('pwa-plugin::pwa-plugin.errors.push_disabled'))->warning()->send();

            return;
        }

        $vapidSubject = (string) $settings->get('vapid_subject', config('pwa-plugin.vapid_subject', ''));
        $vapidPublic = (string) $settings->get('vapid_public_key', config('pwa-plugin.vapid_public_key', ''));
        $vapidPrivate = (string) $settings->get('vapid_private_key', config('pwa-plugin.vapid_private_key', ''));
        if ($vapidSubject === '' || $vapidPublic === '' || $vapidPrivate === '') {
            Log::error('PWA broadcast could not run because VAPID settings are incomplete.');
            Notification::make()->title(trans('pwa-plugin::pwa-plugin.errors.vapid_missing'))->danger()->send();

            return;
        }

        $vapid = [
            'subject' => $vapidSubject,
            'publicKey' => $vapidPublic,
            'privateKey' => $vapidPrivate,
        ];

        $title = trim((string) ($this->data['broadcast_title'] ?? ''));
        $body = trim((string) ($this->data['broadcast_body'] ?? ''));

        if ($title === '' || $body === '') {
            Notification::make()->title(trans('pwa-plugin::pwa-plugin.errors.broadcast_required'))->warning()->send();

            return;
        }

        $icon = trim((string) ($this->data['broadcast_icon'] ?? ''));
        $badge = trim((string) ($this->data['broadcast_badge'] ?? ''));
        $url = trim((string) ($this->data['broadcast_url'] ?? ''));

        $payload = [
            'title' => $title,
            'body' => $body,
            'url' => $url !== '' ? $url : url('/'),
            'icon' => $icon !== '' ? $icon : $settings->get('default_notification_icon', config('pwa-plugin.default_notification_icon', '/pelican.svg')),
            'badge' => $badge !== '' ? $badge : $settings->get('default_notification_badge', config('pwa-plugin.default_notification_badge', '/pelican.svg')),
            'tag' => 'admin-broadcast',
        ];

        $total = 0;
        $sent = 0;
        PwaPushSubscription::query()
            ->orderBy('id')
            ->chunkById(200, function ($rows) use (&$total, &$sent, $payload, $push, $vapid): void {
                foreach ($rows as $row) {
                    $total++;
                    if ($push->sendToSubscription($row, $payload, $vapid)) {
                        $sent++;
                    }
                }
            });

        if ($total === 0) {
            Log::notice('PWA broadcast was requested, but no push subscriptions are registered.');
            Notification::make()
                ->title(trans('pwa-plugin::pwa-plugin.errors.no_subscription'))
                ->warning()
                ->send();

            return;
        }

        if ($sent < $total) {
            Log::warning('PWA broadcast completed with failed deliveries.', [
                'sent' => $sent,
                'failed' => $total - $sent,
                'total' => $total,
            ]);
        }

        Notification::make()
            ->title(trans('pwa-plugin::pwa-plugin.notifications.broadcast_sent', [
                'sent' => $sent,
                'total' => $total,
            ]))
            ->status($sent > 0 ? 'success' : 'danger')
            ->send();
    }
}
