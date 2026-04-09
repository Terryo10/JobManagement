<?php

namespace App\Filament\Shared\Actions;

use App\Models\ActivityLog;
use App\Models\DeletionRequest;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

/**
 * Replaces Filament's DeleteAction.
 *
 * - In the Admin panel: deletes immediately (the LogsActivity trait logs it).
 * - In non-admin panels: creates a DeletionRequest for admin approval.
 */
class RequestDeletionAction extends Action
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
                ? 'Are you sure you want to delete this record? This action cannot be undone.'
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

                    return redirect($this->getResource()::getUrl('index'));
                }

                // Non-admin: create a deletion request
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

    protected function getResource(): ?string
    {
        $livewire = $this->getLivewire();

        if (method_exists($livewire, 'getResource')) {
            return $livewire->getResource();
        }

        return null;
    }
}
