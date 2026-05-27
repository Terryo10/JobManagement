<?php

namespace App\Filament\Admin\Resources\StockLevelResource\Pages;

use App\Filament\Admin\Resources\StockLevelResource;
use Filament\Resources\Pages\CreateRecord;

class CreateStockLevel extends CreateRecord
{
    protected static string $resource = StockLevelResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['last_updated'] = now();
        $data['last_updated_by'] = auth()->id();
        return $data;
    }

    protected function afterCreate(): void
    {
        $record = $this->record;
        if ((float) $record->current_quantity > 0) {
            \App\Models\InventoryTransaction::record(
                material:      $record->material,
                type:          'addition',
                qty:           (float) $record->current_quantity,
                balanceBefore: 0.0,
                performedBy:   auth()->user(),
                reference:     $record,
                notes:         'Initial stock recorded on creation.',
            );
        }
    }
}
