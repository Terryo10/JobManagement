<?php

namespace App\Filament\Staff\Resources\ExpenseResource\Pages;

use App\Filament\Staff\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExpense extends ViewRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make()
                ->visible(fn () => $this->record->approval_status === 'pending'),
        ];
    }
}
