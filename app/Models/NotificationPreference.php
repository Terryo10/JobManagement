<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id', 'notification_type',
        'channel_database', 'channel_mail', 'channel_sms',
    ];

    protected function casts(): array
    {
        return [
            'channel_database' => 'boolean',
            'channel_mail' => 'boolean',
            'channel_sms' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
