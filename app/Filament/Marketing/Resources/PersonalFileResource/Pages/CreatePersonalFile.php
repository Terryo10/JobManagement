<?php

namespace App\Filament\Marketing\Resources\PersonalFileResource\Pages;

use App\Filament\Marketing\Resources\PersonalFileResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePersonalFile extends CreateRecord
{
    use \App\Filament\Shared\Resources\Traits\HandlesMultiplePersonalFiles;

    protected static string $resource = PersonalFileResource::class;
}
