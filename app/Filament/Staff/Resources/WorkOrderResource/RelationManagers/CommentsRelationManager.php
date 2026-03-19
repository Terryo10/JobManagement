<?php

namespace App\Filament\Staff\Resources\WorkOrderResource\RelationManagers;

use App\Filament\Shared\RelationManagers\BaseCommentsRelationManager;

class CommentsRelationManager extends BaseCommentsRelationManager
{
    protected bool $showVisibilityToggle = false;
}
