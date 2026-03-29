<?php

namespace App\Observers;

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

        $excerpt = $announcement->excerpt;

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:             'announcement.published',
            title:            $announcement->title,
            body:             $excerpt,
            icon:             'heroicon-o-megaphone',
            color:            'info',
            recipientUserIds: $recipientIds,
            subjectType:      Announcement::class,
            subjectId:        $announcement->id,
            priority:         'normal',
            idempotencyKey:   'announcement.published.' . $announcement->id,
        ));
    }
}
