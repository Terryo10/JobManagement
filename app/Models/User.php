<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone_number', 'whatsapp_number',
        'notification_quiet_hours', 'department_id', 'is_active', 'saved_signature',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at'          => 'datetime',
            'password'                   => 'hashed',
            'is_active'                  => 'boolean',
            'notification_quiet_hours'   => 'array',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'admin'      => $this->hasRole(['super_admin', 'manager']),
            'staff'      => $this->hasRole(['super_admin', 'manager', 'dept_head', 'staff']),
            'accountant' => $this->hasRole(['super_admin', 'accountant']),
            'client'     => $this->hasRole('client'),
            'marketing'  => $this->hasRole(['super_admin', 'manager', 'marketing']),
            default      => false,
        };
    }

    public function department(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function skills(): HasMany
    {
        return $this->hasMany(UserSkill::class);
    }

    public function availability(): HasMany
    {
        return $this->hasMany(StaffAvailability::class);
    }

    public function workOrders(): BelongsToMany
    {
        return $this->belongsToMany(WorkOrder::class, 'work_order_collaborators')
            ->withPivot('role', 'added_at');
    }

    /** All per-type channel preferences for this user. */
    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    /** Convenience: get the preference row for a specific event type. */
    public function notificationPreferenceFor(string $type): ?NotificationPreference
    {
        return $this->notificationPreferences->firstWhere('notification_type', $type);
    }
}
