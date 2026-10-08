<?php

namespace App\Filament\Shared\Concerns;

use App\Models\Department;
use App\Models\Quotation;
use App\Services\QuotationConversionService;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Tables;

trait HasQuotationConversionActions
{
    protected static function quotationConversionActions(): array
    {
        return [
            Tables\Actions\Action::make('convertToWorkOrder')
                ->label('Convert to Work Order')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('primary')
                ->requiresConfirmation()
                ->modalHeading('Convert Quotation to Work Order')
                ->modalDescription('Creates a work order from this quotation. Leave unknown job details empty and add them later.')
                ->form([
                    Forms\Components\Select::make('category')
                        ->options([
                            'media' => 'Media', 'civil_works' => 'Civil Works',
                            'energy' => 'Energy', 'warehouse' => 'Warehouse',
                        ])->placeholder('Choose when known'),
                    Forms\Components\Select::make('priority')
                        ->options([
                            'low' => 'Low', 'normal' => 'Normal',
                            'high' => 'High', 'urgent' => 'Urgent',
                        ])->placeholder('Choose when known'),
                    Forms\Components\Select::make('assigned_department_id')
                        ->label('Department')
                        ->options(fn () => Department::query()->orderBy('name')->pluck('name', 'id'))
                        ->searchable()->preload(),
                    Forms\Components\DatePicker::make('deadline'),
                ])
                ->visible(fn (Quotation $record) => in_array($record->status, ['draft', 'sent', 'accepted', 'converted'], true)
                    && static::needsWorkOrderConversion($record))
                ->action(function ($record, array $data) {
                    $workOrder = app(QuotationConversionService::class)
                        ->toWorkOrder($record, auth()->user(), $data);

                    Notification::make()
                        ->title("Work order {$workOrder->reference_number} ready")
                        ->success()
                        ->send();
                }),
            Tables\Actions\Action::make('openWorkOrder')
                ->label('Open Work Order')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->color('primary')
                ->visible(fn (Quotation $record) => $record->status === 'converted' && ! static::needsWorkOrderConversion($record))
                ->url(fn (Quotation $record) => static::workOrderResource()::getUrl('view', [
                    'record' => $record->work_order_id,
                ])),
        ];
    }

    private static function workOrderResource(): string
    {
        return match (\Filament\Facades\Filament::getCurrentPanel()->getId()) {
            'admin' => \App\Filament\Admin\Resources\WorkOrderResource::class,
            'accountant' => \App\Filament\Accountant\Resources\WorkOrderResource::class,
            'marketing' => \App\Filament\Marketing\Resources\WorkOrderResource::class,
        };
    }

    private static function needsWorkOrderConversion(Quotation $record): bool
    {
        if ($record->status !== 'converted' || ! $record->work_order_id) {
            return true;
        }

        $workOrder = $record->workOrder;

        return ! $workOrder
            || $workOrder->client_id !== $record->client_id
            || (int) ($workOrder->details['source_quotation_id'] ?? 0) !== $record->id;
    }
}
