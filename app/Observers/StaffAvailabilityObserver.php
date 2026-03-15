<?php

namespace App\Observers;

use App\Models\StaffAvailability;
use App\Models\User;
use App\Notifications\DatabaseAlert;

class StaffAvailabilityObserver
{
    public function created(StaffAvailability $availability): void
    {
        $this->notifyManagers($availability, 'New Leave Request Submitted');
    }

    public function updated(StaffAvailability $availability): void
    {
        // If status changed to approved/denied, notify the staff member
        if ($availability->isDirty('status') && in_array($availability->status, ['approved', 'denied'])) {
            $this->notifyStaff($availability);
            return;
        }

        // If staff edited their own dates/reason/notes, re-notify managers
        $staffFields = ['unavailable_from', 'unavailable_to', 'reason', 'notes'];
        if ($availability->isDirty($staffFields)) {
            $this->notifyManagers($availability, 'Leave Request Updated');
        }
    }

    private function notifyManagers(StaffAvailability $availability, string $title): void
    {
        $staffName = $availability->user?->name ?? 'A staff member';
        $from      = $availability->unavailable_from?->format('d M Y') ?? '—';
        $to        = $availability->unavailable_to?->format('d M Y') ?? '—';
        $reason    = $this->formatReason($availability->reason);

        $managers = User::role(['manager', 'super_admin'])->get();
        foreach ($managers as $manager) {
            $manager->notify(new DatabaseAlert(
                title: $title,
                body: "{$staffName} is unavailable from {$from} to {$to} ({$reason}). Please review and approve or deny.",
                icon: 'heroicon-o-calendar-days',
                color: 'warning',
                actionUrl: route('filament.admin.resources.staff-availabilities.index'),
                actionText: 'Review Requests',
            ));
        }
    }

    private function notifyStaff(StaffAvailability $availability): void
    {
        $staff = $availability->user;
        if (! $staff) {
            return;
        }

        $from      = $availability->unavailable_from?->format('d M Y') ?? '—';
        $to        = $availability->unavailable_to?->format('d M Y') ?? '—';
        $reason    = $this->formatReason($availability->reason);
        $isApproved = $availability->status === 'approved';
        $adminName  = $availability->approvedBy?->name ?? 'Management';

        $title = $isApproved ? 'Leave Request Approved' : 'Leave Request Denied';
        $body  = $isApproved
            ? "Your {$reason} request ({$from} – {$to}) has been approved by {$adminName}."
            : "Your {$reason} request ({$from} – {$to}) has been denied by {$adminName}.";

        if ($availability->admin_note) {
            $body .= " Note: \"{$availability->admin_note}\"";
        }

        $staff->notify(new DatabaseAlert(
            title: $title,
            body: $body,
            icon: $isApproved ? 'heroicon-o-check-circle' : 'heroicon-o-x-circle',
            color: $isApproved ? 'success' : 'danger',
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
