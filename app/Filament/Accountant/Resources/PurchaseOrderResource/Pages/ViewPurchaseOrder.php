<?php
namespace App\Filament\Accountant\Resources\PurchaseOrderResource\Pages;
use App\Filament\Accountant\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn ($record) => $record->status === 'pending_finance_approval'),
        ];
    }
}
