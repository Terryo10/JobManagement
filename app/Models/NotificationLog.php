<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class NotificationLog extends Model
{
    protected $table = 'notification_log';

    protected $fillable = [
        'event_type',
        'notifiable_type',
        'notifiable_id',
        'subject_type',
        'subject_id',
        'channel',
        'status',
        'provider_message_id',
        'payload',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'payload' => 'array',
        ];
    }

    public function notifiable(): MorphTo
    {
        return $this->morphTo();
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
