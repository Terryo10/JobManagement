<?php

namespace App\Filament\Admin\Resources\FieldWorkerResource\Pages;

use App\Filament\Admin\Resources\FieldWorkerResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFieldWorker extends EditRecord
{
    protected static string $resource = FieldWorkerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
            Actions\DeleteAction::make(),
        ];
    }

    /** Split stored phone_number back into prefix + local when the form loads. */
    protected function mutateFormDataBeforeFill(array $data): array
    {
        return FieldWorkerResource::splitPhoneNumber($data);
    }

    /** Merge prefix + local back into phone_number before saving. */
    protected function mutateFormDataBeforeSave(array $data): array
    {
        return FieldWorkerResource::mergePhoneNumber($data);
    }
}
