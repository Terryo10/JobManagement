<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Notifications\Notifiable;

/**
 * FieldWorker represents an internal or external worker who can be
 * assigned to tasks and receive notifications. They are NOT system
 * users and cannot log in to any Filament panel.
 */
class FieldWorker extends Model
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'type',
        'email',
        'phone_number',
    ];

    /**
     * Route notifications for the mail channel.
     * Required by Notifiable to send email notifications.
     */
    public function routeNotificationForMail(): string|null
    {
        return $this->email;
    }

    /**
     * Tasks this field worker is currently assigned to.
     */
    public function tasks(): BelongsToMany
    {
        return $this->belongsToMany(Task::class, 'field_worker_task')
                    ->withPivot('assigned_by', 'assigned_at', 'notes')
                    ->withTimestamps();
    }
}
