<?php

namespace App\Filament\Shared\Concerns;

/**
 * Apply this trait to any non-Admin Resource to prevent delete operations.
 * Only the Admin panel retains delete capability.
 */
trait EnforcesAdminDelete
{
    public static function canDelete($record): bool
    {
        return false;
    }

    public static function canDeleteAny(): bool
    {
        return false;
    }
}
