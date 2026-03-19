<?php

namespace App\Filament\Admin\Resources\WorkOrderResource\RelationManagers;

use App\Filament\Shared\RelationManagers\BaseCommentsRelationManager;

class CommentsRelationManager extends BaseCommentsRelationManager
{
    protected bool $showVisibilityToggle = true;
    protected bool $canManageAllComments = true;
}
