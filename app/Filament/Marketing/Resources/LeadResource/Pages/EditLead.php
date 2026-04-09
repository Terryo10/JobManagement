<?php
namespace App\Filament\Marketing\Resources\LeadResource\Pages;
use App\Filament\Shared\Actions\RequestDeletionAction;
use App\Filament\Marketing\Resources\LeadResource;
use Filament\Resources\Pages\EditRecord;
class EditLead extends EditRecord
{
    protected static string $resource = LeadResource::class;
    protected function getHeaderActions(): array
    {
        return [RequestDeletionAction::make()];
    }
}
