<?php

namespace App\Filament\Admin\Actions;

use App\Jobs\SendNotificationJob;
use App\Models\User;
use App\Notifications\NotificationEvent;
use Filament\Actions\Action;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class SendMessageAction extends Action
{
    /**
     * The URL for the "View" link included in the message.
     * Set via ->withRecordUrl(fn($record) => ...) after make().
     */
    public ?\Closure $recordUrlResolver = null;

    public static function make(?string $name = 'send_message'): static
    {
        return parent::make($name)
            ->label('Send Message')
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->color('info')
            ->modalHeading('Send a Message')
            ->modalWidth('lg')
            ->form(fn (Action $action) => [

                Radio::make('recipient_type')
                    ->label('Send to')
                    ->options([
                        'assigned' => 'Assigned user',
                        'custom'   => 'Choose someone else',
                    ])
                    ->default('assigned')
                    ->live()
                    ->required(),

                Select::make('recipient_user_id')
                    ->label('Recipient')
                    ->options(User::query()->where('is_active', true)->orderBy('name')->pluck('name', 'id'))
                    ->searchable()
                    ->required()
                    ->visible(fn (Get $get) => $get('recipient_type') === 'custom'),

                CheckboxList::make('channels')
                    ->label('Send via')
                    ->options([
                        'mail'      => 'Email',
                        'whatsapp'  => 'WhatsApp',
                        'sms'       => 'SMS',
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
                    ->rows(4)
                    ->required(),
            ])
            ->action(function (array $data, Model $record, Action $action) {
                $recipientUserId = $data['recipient_type'] === 'assigned'
                    ? ($record->assigned_to ?? $record->claimed_by ?? null)
                    : ($data['recipient_user_id'] ?? null);

                if (! $recipientUserId) {
                    Notification::make()
                        ->title('No recipient found. The record has no assigned user.')
                        ->warning()
                        ->send();
                    return;
                }

                $channels  = (array) ($data['channels'] ?? ['mail']);
                $subject   = $data['subject'] ?? $data['message'];
                $body      = $data['message'];

                // Build the record URL for the action link
                $actionUrl  = null;
                $actionText = null;
                if ($action->recordUrlResolver) {
                    $actionUrl  = ($action->recordUrlResolver)($record);
                    $actionText = 'View ' . class_basename($record);
                }

                $idempotencyKey = 'send_message.' . $record->getKey() . '.' . $recipientUserId . '.' . now()->timestamp;

                $event = new NotificationEvent(
                    type:            'manual.message',
                    title:           $subject,
                    body:            $body,
                    actionUrl:       $actionUrl,
                    actionText:      $actionText,
                    recipientUserIds: [$recipientUserId],
                    subjectType:     get_class($record),
                    subjectId:       $record->getKey(),
                    idempotencyKey:  $idempotencyKey,
                );

                $errors = [];
                foreach ($channels as $channel) {
                    try {
                        // Run synchronously so errors surface immediately in the UI
                        (new SendNotificationJob($recipientUserId, $event, $channel))->handle(
                            app(\App\Notifications\Channels\InfobipEmailChannel::class),
                            app(\App\Notifications\Channels\InfobipSmsChannel::class),
                            app(\App\Notifications\Channels\InfobipWhatsAppChannel::class),
                        );
                    } catch (\Throwable $e) {
                        $errors[] = strtoupper($channel) . ': ' . $e->getMessage();
                    }
                }

                if (! empty($errors)) {
                    Notification::make()
                        ->title('Message failed to send.')
                        ->body(implode("\n", $errors))
                        ->danger()
                        ->persistent()
                        ->send();
                    return;
                }

                Notification::make()
                    ->title('Message sent successfully.')
                    ->success()
                    ->send();
            });
    }

    /**
     * Provide a closure that resolves the record's URL for the action link in the message.
     * Example: ->withRecordUrl(fn($record) => route('filament.admin.resources.work-orders.view', $record))
     */
    public function withRecordUrl(\Closure $resolver): static
    {
        $this->recordUrlResolver = $resolver;
        return $this;
    }
}
