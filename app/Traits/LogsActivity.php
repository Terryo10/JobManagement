<?php

namespace App\Traits;

use App\Models\ActivityLog;

/**
 * Add this trait to any Eloquent model that should have its
 * deletions (and restorations) recorded in the activity_logs table.
 *
 * The model can optionally define a getActivityLogLabel() method
 * to provide a human-readable label. Falls back to class + id.
 */
trait LogsActivity
{
    public static function bootLogsActivity(): void
    {
        static::deleted(function ($model) {
            ActivityLog::create([
                'user_id'       => auth()->id(),
                'action'        => 'deleted',
                'subject_type'  => $model->getMorphClass(),
                'subject_id'    => $model->getKey(),
                'subject_label' => $model->getActivityLogLabel(),
            ]);
        });

        // Only register the 'restored' listener if the model uses SoftDeletes
        if (method_exists(static::class, 'restoring')) {
            static::restored(function ($model) {
                ActivityLog::create([
                    'user_id'       => auth()->id(),
                    'action'        => 'restored',
                    'subject_type'  => $model->getMorphClass(),
                    'subject_id'    => $model->getKey(),
                    'subject_label' => $model->getActivityLogLabel(),
                ]);
            });
        }
    }

    /**
     * Override this in your model to provide a better label.
     * e.g. "Work Order #WO-2026-001"
     */
    public function getActivityLogLabel(): string
    {
        // Try common columns for a meaningful label
        if (isset($this->reference_number)) {
            return class_basename($this) . ' #' . $this->reference_number;
        }
        if (isset($this->title)) {
            return class_basename($this) . ': ' . \Illuminate\Support\Str::limit($this->title, 50);
        }
        if (isset($this->name)) {
            return class_basename($this) . ': ' . \Illuminate\Support\Str::limit($this->name, 50);
        }
        if (isset($this->company_name)) {
            return class_basename($this) . ': ' . \Illuminate\Support\Str::limit($this->company_name, 50);
        }
        if (isset($this->contact_name)) {
            return class_basename($this) . ': ' . \Illuminate\Support\Str::limit($this->contact_name, 50);
        }

        return class_basename($this) . ' #' . $this->getKey();
    }
}
