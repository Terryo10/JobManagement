<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MarketResearch extends Model
{
    protected $table = 'market_research';

    protected $fillable = [
        'title', 'category', 'summary', 'source',
        'findings', 'researched_by', 'research_date',
    ];

    protected function casts(): array
    {
        return [
            'research_date' => 'date',
        ];
    }

    public function researchedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'researched_by');
    }
}
