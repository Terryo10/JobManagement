<?php
namespace App\Filament\Marketing\Resources\LeadCommunicationResource\Pages;
use App\Filament\Shared\Actions\RequestDeletionAction;
use App\Filament\Marketing\Resources\LeadCommunicationResource;
use Filament\Resources\Pages\EditRecord;
class EditLeadCommunication extends EditRecord
{
    protected static string $resource = LeadCommunicationResource::class;
    protected function getHeaderActions(): array
    {
        return [RequestDeletionAction::make()];
    }
}
