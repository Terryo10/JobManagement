<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationRule extends Model
{
    protected $fillable = [
        'rule_key', 'rule_type', 'label', 'value', 'trigger_days',
        'description', 'applies_to_role', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'trigger_days' => 'integer',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('rule_type', $type);
    }

    /**
     * Get the numeric value of this rule (e.g., hours, percentage).
     */
    public function getNumericValue(): float
    {
        return (float) $this->value;
    }
}
