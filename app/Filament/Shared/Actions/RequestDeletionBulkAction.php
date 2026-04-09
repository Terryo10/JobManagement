<?php

namespace App\Filament\Shared\Actions;

use App\Models\ActivityLog;
use App\Models\DeletionRequest;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\BulkAction;

/**
 * Replaces Filament's DeleteBulkAction.
 *
 * - In the Admin panel: deletes selected records immediately.
 * - In non-admin panels: creates DeletionRequests for each selected record.
 */
class RequestDeletionBulkAction extends BulkAction
{
    public static function getDefaultName(): ?string
    {
        return 'requestDeletionBulk';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn () => $this->isAdminPanel() ? 'Delete selected' : 'Request deletion')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->deselectRecordsAfterCompletion()
            ->modalHeading(fn () => $this->isAdminPanel()
                ? 'Delete selected records'
                : 'Request deletion of selected records')
            ->modalDescription(fn () => $this->isAdminPanel()
                ? 'Are you sure you want to delete the selected records?'
                : 'This will send deletion requests to an administrator for approval.')
            ->action(function ($records) {
                if ($this->isAdminPanel()) {
                    $records->each->delete();

                    Notification::make()
                        ->title(count($records) . ' record(s) deleted')
                        ->success()
                        ->send();

                    return;
                }

                $count = 0;
                foreach ($records as $record) {
                    $label = method_exists($record, 'getActivityLogLabel')
                        ? $record->getActivityLogLabel()
                        : class_basename($record) . ' #' . $record->getKey();

                    DeletionRequest::create([
                        'requested_by'  => auth()->id(),
                        'subject_type'  => $record->getMorphClass(),
                        'subject_id'    => $record->getKey(),
                        'subject_label' => $label,
                        'reason'        => null,
                        'status'        => 'pending',
                    ]);

                    ActivityLog::create([
                        'user_id'       => auth()->id(),
                        'action'        => 'deletion_requested',
                        'subject_type'  => $record->getMorphClass(),
                        'subject_id'    => $record->getKey(),
                        'subject_label' => $label,
                    ]);

                    $count++;
                }

                Notification::make()
                    ->title("{$count} deletion request(s) submitted")
                    ->body('An administrator will review your requests.')
                    ->success()
                    ->send();
            });
    }

    protected function isAdminPanel(): bool
    {
        try {
            return filament()->getCurrentPanel()?->getId() === 'admin';
        } catch (\Throwable) {
            return false;
        }
    }
}
