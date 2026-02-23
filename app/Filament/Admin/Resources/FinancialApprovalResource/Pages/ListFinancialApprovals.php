<?php

namespace App\Filament\Admin\Resources\FinancialApprovalResource\Pages;

use App\Filament\Admin\Resources\FinancialApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFinancialApprovals extends ListRecords
{
    protected static string $resource = FinancialApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
