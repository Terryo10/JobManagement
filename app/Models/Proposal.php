<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Proposal extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'title', 'client_id', 'lead_id', 'prepared_by',
        'type', 'status', 'value', 'currency',
        'submitted_at', 'valid_until', 'content', 'notes',
    ];

    protected function casts(): array
    {
        return [
            'submitted_at' => 'date',
            'valid_until' => 'date',
            'value' => 'decimal:2',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function preparedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'prepared_by');
    }
}
