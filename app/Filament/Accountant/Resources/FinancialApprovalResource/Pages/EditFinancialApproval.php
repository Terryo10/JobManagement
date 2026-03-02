<?php

namespace App\Filament\Accountant\Resources\FinancialApprovalResource\Pages;

use App\Filament\Accountant\Resources\FinancialApprovalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFinancialApproval extends EditRecord
{
    protected static string $resource = FinancialApprovalResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
