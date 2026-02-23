<?php

namespace App\Filament\Client\Resources\LeadCommunicationResource\Pages;

use App\Filament\Client\Resources\LeadCommunicationResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLeadCommunications extends ListRecords
{
    protected static string $resource = LeadCommunicationResource::class;

    protected function getHeaderActions(): array
    {
        return [];
    }
}
