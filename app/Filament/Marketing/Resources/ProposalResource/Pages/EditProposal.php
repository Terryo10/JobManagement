<?php
namespace App\Filament\Marketing\Resources\ProposalResource\Pages;
use App\Filament\Shared\Actions\RequestDeletionAction;
use App\Filament\Marketing\Resources\ProposalResource;
use Filament\Resources\Pages\EditRecord;
class EditProposal extends EditRecord
{
    protected static string $resource = ProposalResource::class;
    protected function getHeaderActions(): array
    {
        return [RequestDeletionAction::make()];
    }
}
