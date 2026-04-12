<?php

namespace App\Filament\Admin\Pages;

use App\Jobs\SendNotificationJob;
use App\Models\NotificationLog;
use App\Models\User;
use App\Notifications\NotificationEvent;
use Filament\Actions\Action;
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

    protected function getHeaderActions(): array
    {
        return [
            Action::make('viewHistory')
                ->label('Message History')
                ->icon('heroicon-o-clock')
                ->color('gray')
                ->slideOver()
                ->modalHeading('Sent Message History')
                ->modalDescription('All messages sent via the Compose Message page, ordered by most recent.')
                ->modalContent(function () {
                    $logs = NotificationLog::query()
                        ->where('event_type', 'admin.broadcast')
                        ->with('notifiable')
                        ->orderByDesc('created_at')
                        ->limit(100)
                        ->get();

                    return view('filament.admin.pages.partials.message-history', compact('logs'));
                })
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Close'),
        ];
    }

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

                Select::make('whatsapp_template')
                    ->label('WhatsApp Template')
                    ->options([
                        'financial_approval_request' => 'Financial Approval Request',
                        'invoice_alert' => 'Invoice Alert',
                        'work_order_assigned' => 'Work Order Assigned',
                        'quotation_status_update' => 'Quotation Status Update',
                        'company_announcement' => 'Company Announcement',
                        'action_required_alert' => 'Action Required Alert',
                        'completion_success' => 'Completion Success',
                        'welcome_onboarding' => 'Welcome Onboarding',
                    ])
                    ->live()
                    ->required(fn (Get $get) => in_array('whatsapp', (array) $get('channels')))
                    ->visible(fn (Get $get) => in_array('whatsapp', (array) $get('channels'))),

                TextInput::make('variable_1')
                    ->label('Variable 1')
                    ->required(fn (Get $get) => in_array('whatsapp', (array) $get('channels')))
                    ->visible(fn (Get $get) => in_array('whatsapp', (array) $get('channels')) && in_array($get('whatsapp_template'), [
                        'financial_approval_request', 'invoice_alert', 'work_order_assigned', 'quotation_status_update', 
                        'company_announcement', 'action_required_alert', 'completion_success', 'welcome_onboarding'
                    ]))
                    ->helperText('First placeholder (e.g. name, ID, or amount)'),

                TextInput::make('variable_2')
                    ->label('Variable 2')
                    ->required(fn (Get $get) => in_array('whatsapp', (array) $get('channels')) && in_array($get('whatsapp_template'), [
                        'financial_approval_request', 'invoice_alert', 'work_order_assigned', 'quotation_status_update', 'action_required_alert'
                    ]))
                    ->visible(fn (Get $get) => in_array('whatsapp', (array) $get('channels')) && in_array($get('whatsapp_template'), [
                        'financial_approval_request', 'invoice_alert', 'work_order_assigned', 'quotation_status_update', 'action_required_alert'
                    ]))
                    ->helperText('Second placeholder (e.g. status or date)'),

                TextInput::make('variable_3')
                    ->label('Variable 3')
                    ->required(fn (Get $get) => in_array('whatsapp', (array) $get('channels')) && in_array($get('whatsapp_template'), [
                        'financial_approval_request', 'invoice_alert'
                    ]))
                    ->visible(fn (Get $get) => in_array('whatsapp', (array) $get('channels')) && in_array($get('whatsapp_template'), [
                        'financial_approval_request', 'invoice_alert'
                    ]))
                    ->helperText('Third placeholder (e.g. link or amount)'),

                Textarea::make('message')
                    ->label('Message')
                    ->placeholder('Type your message here…')
                    ->rows(5)
                    ->required(fn (Get $get) => !in_array('whatsapp', (array) $get('channels')))
                    ->hidden(fn (Get $get) => in_array('whatsapp', (array) $get('channels'))),

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

        $recipientIds      = $data['recipient_ids'] ?? [];
        $channels          = (array) ($data['channels'] ?? ['mail']);
        $subject           = $data['subject'] ?? ($data['message'] ?? '');
        $body              = $data['message'] ?? '';
        $actionUrl         = $data['action_url'] ?? null;
        $actionText        = filled($actionUrl) ? ($data['action_text'] ?? 'View') : null;

        $whatsappTemplate  = $data['whatsapp_template'] ?? null;
        $whatsappVariables = [];

        if (in_array('whatsapp', $channels) && $whatsappTemplate) {
            $varsCount = match ($whatsappTemplate) {
                'financial_approval_request', 'invoice_alert' => 3,
                'work_order_assigned', 'quotation_status_update', 'action_required_alert' => 2,
                default => 1,
            };
            for ($i = 1; $i <= $varsCount; $i++) {
                $whatsappVariables[] = $data["variable_{$i}"] ?? '';
            }
        }

        $extraData = [];
        if ($whatsappTemplate) {
            $extraData['whatsapp_template'] = $whatsappTemplate;
            $extraData['whatsapp_variables'] = $whatsappVariables;
        }

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
                extraData:       $extraData,
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
