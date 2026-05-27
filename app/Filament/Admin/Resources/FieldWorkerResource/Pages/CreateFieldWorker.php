<?php

namespace App\Filament\Admin\Resources\FieldWorkerResource\Pages;

use App\Filament\Admin\Resources\FieldWorkerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateFieldWorker extends CreateRecord
{
    protected static string $resource = FieldWorkerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        return FieldWorkerResource::mergePhoneNumber($data);
    }
}
