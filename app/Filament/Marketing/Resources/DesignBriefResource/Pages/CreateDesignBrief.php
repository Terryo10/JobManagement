<?php

namespace App\Filament\Marketing\Resources\DesignBriefResource\Pages;

use App\Filament\Marketing\Resources\DesignBriefResource;
use Filament\Resources\Pages\CreateRecord;

class CreateDesignBrief extends CreateRecord
{
    protected static string $resource = DesignBriefResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['created_by'] = auth()->id();
        return $data;
    }
}
