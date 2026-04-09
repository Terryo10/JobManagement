<?php

namespace App\Filament\Admin\Resources\PersonalFileResource\Pages;

use App\Filament\Admin\Resources\PersonalFileResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePersonalFile extends CreateRecord
{
    use \App\Filament\Shared\Resources\Traits\HandlesMultiplePersonalFiles;

    protected static string $resource = PersonalFileResource::class;
}
