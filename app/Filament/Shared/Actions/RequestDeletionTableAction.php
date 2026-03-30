<?php

namespace App\Filament\Shared\Actions;

use App\Models\ActivityLog;
use App\Models\DeletionRequest;
use Filament\Notifications\Notification;
use Filament\Tables\Actions\Action;

/**
 * Replaces Filament's DeleteAction for table rows.
 *
 * - In the Admin panel: deletes immediately.
 * - In non-admin panels: creates a DeletionRequest for admin approval.
 */
class RequestDeletionTableAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'requestDeletion';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn () => $this->isAdminPanel() ? 'Delete' : 'Request Deletion')
            ->icon('heroicon-o-trash')
            ->color('danger')
            ->requiresConfirmation()
            ->modalHeading(fn () => $this->isAdminPanel()
                ? 'Delete record'
                : 'Request deletion')
            ->modalDescription(fn () => $this->isAdminPanel()
                ? 'Are you sure you want to delete this record?'
                : 'This will send a deletion request to an administrator for approval.')
            ->form(fn () => $this->isAdminPanel() ? [] : [
                \Filament\Forms\Components\Textarea::make('reason')
                    ->label('Reason for deletion (optional)')
                    ->rows(2)
                    ->maxLength(500),
            ])
            ->action(function (array $data, $record) {
                if ($this->isAdminPanel()) {
                    $record->delete();

                    Notification::make()
                        ->title('Record deleted')
                        ->success()
                        ->send();

                    return;
                }

                $label = method_exists($record, 'getActivityLogLabel')
                    ? $record->getActivityLogLabel()
                    : class_basename($record) . ' #' . $record->getKey();

                DeletionRequest::create([
                    'requested_by'  => auth()->id(),
                    'subject_type'  => $record->getMorphClass(),
                    'subject_id'    => $record->getKey(),
                    'subject_label' => $label,
                    'reason'        => $data['reason'] ?? null,
                    'status'        => 'pending',
                ]);

                ActivityLog::create([
                    'user_id'       => auth()->id(),
                    'action'        => 'deletion_requested',
                    'subject_type'  => $record->getMorphClass(),
                    'subject_id'    => $record->getKey(),
                    'subject_label' => $label,
                ]);

                Notification::make()
                    ->title('Deletion request submitted')
                    ->body('An administrator will review your request.')
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
