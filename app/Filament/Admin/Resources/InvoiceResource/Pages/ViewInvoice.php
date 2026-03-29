<?php

namespace App\Filament\Admin\Resources\InvoiceResource\Pages;

use App\Filament\Admin\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('email')
                ->label('Email Invoice')
                ->icon('heroicon-o-paper-airplane')
                ->color('info')
                ->form([
                    \Filament\Forms\Components\TextInput::make('email')
                        ->label('Recipient Email')
                        ->email()
                        ->required()
                        ->default(fn ($record) => $record->client?->email),
                ])
                ->action(function ($record, array $data) {
                    $email = $data['email'];
                    $record->update([
                        'status' => 'sent',
                        'issued_at' => $record->issued_at ?? now(),
                    ]);
                    app(\App\Services\InvoiceMailService::class)->sendInvoiceToClient($record, $email);
                    $clientUser = \App\Models\User::where('email', $record->client?->email)->first();
                    if ($clientUser) {
                        $signedUrl = route('invoices.sign.show', ['invoice' => $record->id]);
                        \Filament\Notifications\Notification::make()
                            ->title('New Invoice Received')
                            ->body("Invoice {$record->invoice_number} for \${$record->total} has been sent to you. Click below to review and sign.")
                            ->icon('heroicon-o-document-currency-dollar')
                            ->info()
                            ->actions([\Filament\Notifications\Actions\Action::make('sign')->label('Review & Sign')->url($signedUrl)->openUrlInNewTab()->button()])
                            ->sendToDatabase($clientUser);
                    }
                    \Filament\Notifications\Notification::make()->title('Invoice emailed successfully.')->success()->send();
                }),
            Actions\EditAction::make(),
        ];
    }
}
