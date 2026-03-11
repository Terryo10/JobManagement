<?php

namespace App\Filament\Marketing\Resources\LeadCommunicationResource\Pages;

use App\Filament\Marketing\Resources\LeadCommunicationResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLeadCommunication extends EditRecord
{
    protected static string $resource = LeadCommunicationResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
