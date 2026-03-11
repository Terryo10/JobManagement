<?php

namespace App\Filament\Admin\Resources\ProposalResource\Pages;

use App\Filament\Admin\Resources\ProposalResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewProposal extends ViewRecord
{
    protected static string $resource = ProposalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('approve')
                ->label('Approve')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->requiresConfirmation()
                ->modalHeading('Approve Proposal')
                ->modalDescription('Are you sure you want to approve this proposal?')
                ->visible(fn () => $this->record->status === 'submitted')
                ->action(function () {
                    $this->record->update(['status' => 'approved']);
                    $this->refreshFormData(['status']);
                }),
            Actions\Action::make('reject')
                ->label('Reject')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->requiresConfirmation()
                ->modalHeading('Reject Proposal')
                ->modalDescription('Are you sure you want to reject this proposal?')
                ->visible(fn () => $this->record->status === 'submitted')
                ->action(function () {
                    $this->record->update(['status' => 'rejected']);
                    $this->refreshFormData(['status']);
                }),
        ];
    }
}
