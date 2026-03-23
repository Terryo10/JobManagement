<?php

namespace App\Filament\Pages;

use App\Models\NotificationPreference;
use Filament\Forms\Components\Fieldset;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

class MyNotificationPreferences extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-bell-alert';
    protected static ?string $navigationLabel = 'Notification Preferences';
    protected static ?string $title = 'My Notification Settings';
    protected static bool $shouldRegisterNavigation = false;
    protected static string $view = 'filament.pages.my-notification-preferences';

    public ?array $data = [];

    public function mount(): void
    {
        /** @var \App\Models\User|null $user */
        $user = auth()->user();
        if (!$user) return;
        
        $preferences = $user->notificationPreferences->keyBy('notification_type');
        $initialData = [];
        
        foreach (config('notifications', []) as $type => $settings) {
            $pref = $preferences->get($type);
            $key = str_replace('.', '___', $type);
            
            $initialData[$key] = [
                'channel_database' => $pref ? $pref->channel_database : ($settings['channel_database'] ?? true),
                'channel_mail'     => $pref ? $pref->channel_mail     : ($settings['channel_mail'] ?? true),
                'channel_sms'      => $pref ? $pref->channel_sms      : ($settings['channel_sms'] ?? false),
                'channel_whatsapp' => $pref ? $pref->channel_whatsapp : ($settings['channel_whatsapp'] ?? false),
            ];
        }

        $this->form->fill($initialData);
    }

    public function form(Form $form): Form
    {
        $schema = [];
        
        foreach (config('notifications', []) as $type => $settings) {
            $key = str_replace('.', '___', $type);
            $schema[] = Fieldset::make($settings['label'] ?? Str::title(str_replace(['.', '_'], ' ', $type)))
                ->statePath($key)
                ->schema([
                    Toggle::make('channel_database')->label('In-App Alert')->inline(false),
                    Toggle::make('channel_mail')->label('Email')->inline(false),
                    Toggle::make('channel_sms')->label('SMS')->inline(false),
                    Toggle::make('channel_whatsapp')->label('WhatsApp')->inline(false),
                ])
                ->columns(4);
        }

        return $form->schema($schema)->statePath('data');
    }

    public function save(): void
    {
        $userId = auth()->id();
        $state = $this->form->getState();

        // Validate that at least one channel is selected for every event type
        foreach ($state as $key => $channels) {
            $hasActiveChannel = !empty($channels['channel_database'])
                             || !empty($channels['channel_mail'])
                             || !empty($channels['channel_sms'])
                             || !empty($channels['channel_whatsapp']);

            if (!$hasActiveChannel) {
                $type = str_replace('___', '.', $key);
                $label = config("notifications.{$type}.label") ?? Str::title(str_replace(['.', '_'], ' ', $type));
                
                Notification::make()
                    ->title("Configuration Error")
                    ->body("You must enable at least one notification method (In-App, Email, SMS, or WhatsApp) for '{$label}'.")
                    ->danger()
                    ->send();
                    
                return;
            }
        }

        // Save preferences if validation passes
        foreach ($state as $key => $channels) {
            $type = str_replace('___', '.', $key);
            NotificationPreference::updateOrCreate(
                ['user_id' => $userId, 'notification_type' => $type],
                [
                    'channel_database' => $channels['channel_database'] ?? false,
                    'channel_mail'     => $channels['channel_mail'] ?? false,
                    'channel_sms'      => $channels['channel_sms'] ?? false,
                    'channel_whatsapp' => $channels['channel_whatsapp'] ?? false,
                ]
            );
        }

        Notification::make()
            ->title('Notification Preferences saved successfully.')
            ->success()
            ->send();
    }
}
