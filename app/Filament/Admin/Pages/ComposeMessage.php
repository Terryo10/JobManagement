<?php

namespace App\Filament\Admin\Pages;

use App\Jobs\SendNotificationJob;
use App\Models\User;
use App\Notifications\NotificationEvent;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class ComposeMessage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon  = 'heroicon-o-paper-airplane';
    protected static ?string $navigationLabel = 'Compose Message';
    protected static ?string $navigationGroup = 'Messaging';
    protected static ?int    $navigationSort  = 1;
    protected static string  $view            = 'filament.admin.pages.compose-message';

    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Select::make('recipient_ids')
                    ->label('Recipients')
                    ->options(
                        User::query()
                            ->where('is_active', true)
                            ->orderBy('name')
                            ->pluck('name', 'id')
                    )
                    ->multiple()
                    ->searchable()
                    ->required()
                    ->helperText('Select one or more staff members to message.'),

                CheckboxList::make('channels')
                    ->label('Send via')
                    ->options([
                        'mail'     => 'Email',
                        'whatsapp' => 'WhatsApp',
                        'sms'      => 'SMS',
                    ])
                    ->default(['mail'])
                    ->live()
                    ->required(),

                TextInput::make('subject')
                    ->label('Subject')
                    ->placeholder('Message subject…')
                    ->required()
                    ->visible(fn (Get $get) => in_array('mail', (array) $get('channels'))),

                Textarea::make('message')
                    ->label('Message')
                    ->placeholder('Type your message here…')
                    ->rows(5)
                    ->required(),

                TextInput::make('action_url')
                    ->label('Link URL (optional)')
                    ->url()
                    ->placeholder('https://…')
                    ->helperText('If provided, an action button will be included in the email.'),

                TextInput::make('action_text')
                    ->label('Link Button Label')
                    ->placeholder('e.g. View Details')
                    ->visible(fn (Get $get) => filled($get('action_url'))),
            ])
            ->statePath('data');
    }

    public function send(): void
    {
        $data = $this->form->getState();

        $recipientIds = $data['recipient_ids'] ?? [];
        $channels     = (array) ($data['channels'] ?? ['mail']);
        $subject      = $data['subject'] ?? $data['message'];
        $body         = $data['message'];
        $actionUrl    = $data['action_url'] ?? null;
        $actionText   = filled($actionUrl) ? ($data['action_text'] ?? 'View') : null;

        if (empty($recipientIds)) {
            Notification::make()->title('Please select at least one recipient.')->warning()->send();
            return;
        }

        $baseKey = 'compose.' . now()->timestamp;
        $errors  = [];

        foreach ($recipientIds as $userId) {
            $event = new NotificationEvent(
                type:            'admin.broadcast',
                title:           $subject,
                body:            $body,
                actionUrl:       $actionUrl,
                actionText:      $actionText,
                recipientUserIds: [$userId],
                idempotencyKey:  $baseKey . '.' . $userId,
            );

            foreach ($channels as $channel) {
                try {
                    (new SendNotificationJob($userId, $event, $channel))->handle(
                        app(\App\Notifications\Channels\InfobipEmailChannel::class),
                        app(\App\Notifications\Channels\InfobipSmsChannel::class),
                        app(\App\Notifications\Channels\InfobipWhatsAppChannel::class),
                    );
                } catch (\Throwable $e) {
                    $errors[] = "User #{$userId} via " . strtoupper($channel) . ': ' . $e->getMessage();
                }
            }
        }

        if (! empty($errors)) {
            Notification::make()
                ->title('Some messages failed to send.')
                ->body(implode("\n", array_slice($errors, 0, 3)))
                ->danger()
                ->persistent()
                ->send();
            return;
        }

        $recipientCount = count($recipientIds);
        $channelList    = implode(', ', $channels);

        Notification::make()
            ->title("Message sent to {$recipientCount} recipient(s) via {$channelList}.")
            ->success()
            ->send();

        $this->form->fill();
    }
}
