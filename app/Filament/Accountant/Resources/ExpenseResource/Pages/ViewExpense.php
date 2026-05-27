<?php

namespace App\Filament\Accountant\Resources\ExpenseResource\Pages;

use App\Filament\Accountant\Resources\ExpenseResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewExpense extends ViewRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }
}
