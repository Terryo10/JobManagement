<?php

namespace App\Filament\Admin\Resources\UserSkillResource\Pages;

use App\Filament\Admin\Resources\UserSkillResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUserSkills extends ListRecords
{
    protected static string $resource = UserSkillResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\CreateAction::make()];
    }
}
