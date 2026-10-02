<?php

namespace App\Filament\Shared\Concerns;

use App\Models\Department;
use App\Services\QuotationConversionService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;

trait HasQuotationConversionActions
{
    protected static function quotationConversionActions(): array
    {
        return [
            Tables\Actions\Action::make('convertToJobCard')
                ->label('Create Job Card')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Create Job Card from Quotation')
                ->modalDescription('A pending job card will be created with the client, line-item description, and quotation total. You can complete the production details from Job Cards.')
                ->form([
                    Forms\Components\Select::make('category')
                        ->options([
                            'media' => 'Media',
                            'civil_works' => 'Civil Works',
                            'energy' => 'Energy',
                            'warehouse' => 'Warehouse',
                        ])->default('media')->required(),
                    Forms\Components\Select::make('priority')
                        ->options([
                            'low' => 'Low', 'normal' => 'Normal',
                            'high' => 'High', 'urgent' => 'Urgent',
                        ])->default('normal')->required(),
                    Forms\Components\Select::make('assigned_department_id')
                        ->label('Department')
                        ->options(fn () => Department::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()->preload(),
                    Forms\Components\DatePicker::make('deadline'),
                ])
                ->visible(fn ($record) => in_array($record->status, ['sent', 'accepted', 'converted'], true)
                    && ! $record->work_order_id)
                ->action(function ($record, array $data) {
                    $workOrder = app(QuotationConversionService::class)
                        ->toWorkOrder($record, auth()->user(), $data);

                    Notification::make()
                        ->title("Job card {$workOrder->reference_number} created")
                        ->body('The quotation is now linked to the new job card.')
                        ->success()
                        ->send();
                }),

            Tables\Actions\Action::make('convertToSalesOrder')
                ->label('Create Sales Order')
                ->icon('heroicon-o-shopping-bag')
                ->color('warning')
                ->requiresConfirmation()
                ->modalHeading('Create Sales Order from Quotation')
                ->modalDescription('The client, currency, totals, notes, and all quotation line items will be copied to a new draft sales order.')
                ->visible(fn ($record) => in_array($record->status, ['sent', 'accepted', 'converted'], true)
                    && $record->salesOrder()->doesntExist())
                ->action(function ($record) {
                    $order = app(QuotationConversionService::class)
                        ->toSalesOrder($record, auth()->user());

                    Notification::make()
                        ->title("Sales order {$order->order_number} created")
                        ->success()
                        ->send();
                }),
        ];
    }
}
