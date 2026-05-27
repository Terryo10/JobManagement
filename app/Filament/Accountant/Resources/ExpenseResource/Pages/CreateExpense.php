<?php

namespace App\Filament\Accountant\Resources\ExpenseResource\Pages;

use App\Filament\Accountant\Resources\ExpenseResource;
use Filament\Resources\Pages\CreateRecord;

class CreateExpense extends CreateRecord
{
    protected static string $resource = ExpenseResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Default approval status for new expenses created via the accountant panel.
        $data['approval_status'] = $data['approval_status'] ?? 'pending';
        return $data;
    }
}
