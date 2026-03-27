<?php

namespace App\Filament\Staff\Resources;

use App\Filament\Staff\Resources\PersonalFileResource\Pages;
use App\Filament\Shared\Resources\BasePersonalFileResource;
use App\Filament\Shared\Concerns\EnforcesAdminDelete;

class PersonalFileResource extends BasePersonalFileResource
{
    protected static ?int $navigationSort = 10;

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPersonalFiles::route('/'),
            'create' => Pages\CreatePersonalFile::route('/create'),
            'edit'   => Pages\EditPersonalFile::route('/{record}/edit'),
        ];
    }
}
