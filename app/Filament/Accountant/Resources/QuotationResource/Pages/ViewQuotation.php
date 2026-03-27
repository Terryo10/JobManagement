<?php

namespace App\Filament\Accountant\Resources\QuotationResource\Pages;

use App\Filament\Accountant\Resources\QuotationResource;
use Filament\Resources\Pages\ViewRecord;

class ViewQuotation extends ViewRecord
{
    protected static string $resource = QuotationResource::class;

    protected function getHeaderActions(): array
    {
        return [\Filament\Actions\EditAction::make()];
    }
}
