<?php
namespace App\Filament\Marketing\Resources\PurchaseOrderResource\Pages;
use App\Filament\Shared\Actions\RequestDeletionAction;
use App\Filament\Marketing\Resources\PurchaseOrderResource;
use Filament\Resources\Pages\EditRecord;
class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;
    protected function getHeaderActions(): array
    {
        return [RequestDeletionAction::make()];
    }
}
