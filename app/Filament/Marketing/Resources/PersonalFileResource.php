<?php

namespace App\Filament\Marketing\Resources;

use App\Filament\Marketing\Resources\PersonalFileResource\Pages;
use App\Filament\Shared\Resources\BasePersonalFileResource;

class PersonalFileResource extends BasePersonalFileResource
{
    protected static ?string $navigationGroup = 'Operations';
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
