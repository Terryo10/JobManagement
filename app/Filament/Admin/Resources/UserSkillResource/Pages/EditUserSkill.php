<?php

namespace App\Filament\Admin\Resources\UserSkillResource\Pages;

use App\Filament\Admin\Resources\UserSkillResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditUserSkill extends EditRecord
{
    protected static string $resource = UserSkillResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()];
    }
}
