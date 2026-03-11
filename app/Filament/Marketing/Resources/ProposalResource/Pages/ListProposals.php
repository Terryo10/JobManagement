<?php

namespace App\Filament\Marketing\Resources\ProposalResource\Pages;

use App\Filament\Marketing\Resources\ProposalResource;
use Filament\Resources\Pages\ListRecords;

class ListProposals extends ListRecords
{
    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
