<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AnnouncementComment extends Model
{
    protected $fillable = [
        'announcement_id',
        'user_id',
        'body',
    ];

    protected static function booted(): void
    {
        static::creating(function (AnnouncementComment $comment) {
            if (empty($comment->user_id)) {
                $comment->user_id = auth()->id();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function announcement(): BelongsTo
    {
        return $this->belongsTo(Announcement::class);
    }
}
