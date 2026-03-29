<?php

namespace App\Observers;

use App\Models\AnnouncementComment;
use App\Notifications\NotificationEvent;
use App\Services\NotificationRouter;

class AnnouncementCommentObserver
{
    public function created(AnnouncementComment $comment): void
    {
        $announcement = $comment->announcement;

        if (! $announcement) {
            return;
        }

        // Collect people who should be notified:
        // 1. The announcement author (unless they wrote this comment)
        // 2. All distinct users who previously commented (unless they wrote this comment)
        $notifyIds = collect();

        if ($announcement->created_by && $announcement->created_by !== $comment->user_id) {
            $notifyIds->push($announcement->created_by);
        }

        $priorCommenterIds = $announcement->comments()
            ->where('id', '!=', $comment->id)
            ->whereNotNull('user_id')
            ->where('user_id', '!=', $comment->user_id)
            ->distinct()
            ->pluck('user_id');

        $notifyIds = $notifyIds->merge($priorCommenterIds)->unique()->values()->toArray();

        if (empty($notifyIds)) {
            return;
        }

        $commenterName = $comment->user?->name ?? 'Someone';
        $body          = "\"{$commenterName}\" commented: " . \Illuminate\Support\Str::limit($comment->body, 120);

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:             'announcement.comment',
            title:            'New comment on: ' . $announcement->title,
            body:             $body,
            icon:             'heroicon-o-chat-bubble-left-right',
            color:            'warning',
            recipientUserIds: $notifyIds,
            subjectType:      \App\Models\Announcement::class,
            subjectId:        $announcement->id,
            priority:         'normal',
            idempotencyKey:   'announcement.comment.' . $comment->id,
        ));
    }
}
