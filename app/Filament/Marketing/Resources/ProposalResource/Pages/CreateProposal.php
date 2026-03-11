<?php

namespace App\Filament\Marketing\Resources\ProposalResource\Pages;

use App\Filament\Marketing\Resources\ProposalResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProposal extends CreateRecord
{
    protected static string $resource = ProposalResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['prepared_by'] = auth()->id();
        return $data;
    }
}
