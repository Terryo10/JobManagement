<?php

namespace App\Observers;

use App\Filament\Admin\Resources\AnnouncementResource;
use App\Models\Announcement;
use App\Models\User;
use App\Notifications\NotificationEvent;
use App\Services\NotificationRouter;

class AnnouncementObserver
{
    public function created(Announcement $announcement): void
    {
        $recipientIds = User::where('is_active', true)
            ->where('id', '!=', $announcement->created_by)
            ->pluck('id')
            ->toArray();

        if (empty($recipientIds)) {
            return;
        }

        $author  = $announcement->createdBy?->name ?? 'Household Media';
        $excerpt = $announcement->excerpt;

        $actionUrl = rescue(
            fn () => AnnouncementResource::getUrl('view', ['record' => $announcement->id]),
            null,
            false
        );

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:             'announcement.published',
            title:            $announcement->title,
            body:             "Posted by {$author}: {$excerpt}",
            icon:             'heroicon-o-megaphone',
            color:            'info',
            actionUrl:        $actionUrl,
            actionText:       'Read Announcement',
            recipientUserIds: $recipientIds,
            subjectType:      Announcement::class,
            subjectId:        $announcement->id,
            priority:         'normal',
            idempotencyKey:   'announcement.published.' . $announcement->id,
            subject:          '[Announcement] ' . $announcement->title,
        ));
    }
}
