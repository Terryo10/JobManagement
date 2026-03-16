<?php

namespace App\Observers;

use App\Models\StaffAvailability;
use App\Notifications\NotificationEvent;
use App\Services\NotificationRouter;

class StaffAvailabilityObserver
{
    public function created(StaffAvailability $availability): void
    {
        $this->notifyManagers($availability, 'New Leave Request Submitted', 'leave.submitted');
    }

    public function updated(StaffAvailability $availability): void
    {
        if ($availability->isDirty('status') && in_array($availability->status, ['approved', 'denied'])) {
            $this->notifyStaff($availability);
            return;
        }

        $staffFields = ['unavailable_from', 'unavailable_to', 'reason', 'notes'];
        if ($availability->isDirty($staffFields)) {
            $this->notifyManagers($availability, 'Leave Request Updated', 'leave.updated');
        }
    }

    private function notifyManagers(StaffAvailability $availability, string $title, string $type): void
    {
        $staffName = $availability->user?->name ?? 'A staff member';
        $from      = $availability->unavailable_from?->format('d M Y') ?? '—';
        $to        = $availability->unavailable_to?->format('d M Y') ?? '—';
        $reason    = $this->formatReason($availability->reason);

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:           $type,
            title:          $title,
            body:           "{$staffName} is unavailable from {$from} to {$to} ({$reason}). Please review and approve or deny.",
            icon:           'heroicon-o-calendar-days',
            color:          'warning',
            actionUrl:      route('filament.admin.resources.staff-availabilities.index'),
            actionText:     'Review Requests',
            recipientRoles: ['manager', 'super_admin'],
            subjectType:    StaffAvailability::class,
            subjectId:      $availability->id,
        ));
    }

    private function notifyStaff(StaffAvailability $availability): void
    {
        $staff = $availability->user;
        if (! $staff) {
            return;
        }

        $from       = $availability->unavailable_from?->format('d M Y') ?? '—';
        $to         = $availability->unavailable_to?->format('d M Y') ?? '—';
        $reason     = $this->formatReason($availability->reason);
        $isApproved = $availability->status === 'approved';
        $adminName  = $availability->approvedBy?->name ?? 'Management';

        $body = $isApproved
            ? "Your {$reason} request ({$from} – {$to}) has been approved by {$adminName}."
            : "Your {$reason} request ({$from} – {$to}) has been denied by {$adminName}.";

        if ($availability->admin_note) {
            $body .= " Note: \"{$availability->admin_note}\"";
        }

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:             $isApproved ? 'leave.approved' : 'leave.denied',
            title:            $isApproved ? 'Leave Request Approved' : 'Leave Request Denied',
            body:             $body,
            icon:             $isApproved ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle',
            color:            $isApproved ? 'success' : 'danger',
            recipientUserIds: [$staff->id],
            subjectType:      StaffAvailability::class,
            subjectId:        $availability->id,
            priority:         'high',
        ));
    }

    private function formatReason(?string $reason): string
    {
        return match ($reason) {
            'leave'            => 'Annual Leave',
            'sick'             => 'Sick Leave',
            'field_deployment' => 'Field Deployment',
            'training'         => 'Training',
            default            => ucfirst($reason ?? 'Other'),
        };
    }
}
