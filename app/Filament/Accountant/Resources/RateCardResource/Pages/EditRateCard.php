<?php
namespace App\Filament\Accountant\Resources\RateCardResource\Pages;
use App\Filament\Shared\Actions\RequestDeletionAction;
use App\Filament\Accountant\Resources\RateCardResource;
use Filament\Resources\Pages\EditRecord;
class EditRateCard extends EditRecord
{
    protected static string $resource = RateCardResource::class;
    protected function getHeaderActions(): array
    {
        return [RequestDeletionAction::make()];
    }
}
