<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReportLog extends Model
{
    protected $fillable = [
        'scheduled_report_id', 'report_type', 'generated_by',
        'filters_used', 'file_path', 'status', 'error_message', 'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'filters_used' => 'array',
            'generated_at' => 'datetime',
        ];
    }

    public function scheduledReport(): BelongsTo
    {
        return $this->belongsTo(ScheduledReport::class);
    }

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }
}
