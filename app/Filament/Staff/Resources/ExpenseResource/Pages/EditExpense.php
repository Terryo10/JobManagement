<?php

namespace App\Filament\Staff\Resources\ExpenseResource\Pages;

use App\Filament\Staff\Resources\ExpenseResource;
use Filament\Resources\Pages\EditRecord;

class EditExpense extends EditRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
