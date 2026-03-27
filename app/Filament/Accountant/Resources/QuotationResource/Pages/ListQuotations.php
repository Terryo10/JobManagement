<?php

namespace App\Filament\Accountant\Resources\QuotationResource\Pages;

use App\Filament\Accountant\Resources\QuotationResource;
use Filament\Resources\Pages\ListRecords;

class ListQuotations extends ListRecords
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\CreateAction::make()];
    }
}
