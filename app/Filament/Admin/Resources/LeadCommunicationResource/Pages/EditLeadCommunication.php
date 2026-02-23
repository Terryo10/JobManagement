<?php

namespace App\Filament\Admin\Resources\LeadCommunicationResource\Pages;

use App\Filament\Admin\Resources\LeadCommunicationResource;
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
