<?php

namespace App\Filament\Staff\Resources\PersonalFileResource\Pages;

use App\Filament\Staff\Resources\PersonalFileResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePersonalFile extends CreateRecord
{
    use \App\Filament\Shared\Resources\Traits\HandlesMultiplePersonalFiles;

    protected static string $resource = PersonalFileResource::class;
}
