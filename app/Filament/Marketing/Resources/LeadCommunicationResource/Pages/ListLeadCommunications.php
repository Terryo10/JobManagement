<?php

namespace App\Filament\Marketing\Resources\LeadCommunicationResource\Pages;

use App\Filament\Marketing\Resources\LeadCommunicationResource;
use Filament\Resources\Pages\ListRecords;

class ListLeadCommunications extends ListRecords
{
    protected static string $resource = LeadCommunicationResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
