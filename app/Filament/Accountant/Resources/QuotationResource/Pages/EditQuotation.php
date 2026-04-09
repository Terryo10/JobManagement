<?php
namespace App\Filament\Accountant\Resources\QuotationResource\Pages;
use App\Filament\Shared\Actions\RequestDeletionAction;
use App\Filament\Accountant\Resources\QuotationResource;
use Filament\Resources\Pages\EditRecord;
class EditQuotation extends EditRecord
{
    protected static string $resource = QuotationResource::class;
    protected function getHeaderActions(): array
    {
        return [RequestDeletionAction::make()];
    }
}
