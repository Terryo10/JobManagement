<?php

namespace App\Filament\Accountant\Resources\MaterialResource\Pages;

use App\Filament\Accountant\Resources\MaterialResource;
use Filament\Resources\Pages\CreateRecord;

class CreateMaterial extends CreateRecord
{
    protected static string $resource = MaterialResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
