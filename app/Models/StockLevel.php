<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLevel extends Model
{
    public $timestamps = false;

    protected $fillable = ['material_id', 'current_quantity', 'last_updated', 'last_updated_by'];

    protected function casts(): array
    {
        return [
            'last_updated' => 'datetime',
            'current_quantity' => 'decimal:2',
        ];
    }

    public function material(): BelongsTo
    {
        return $this->belongsTo(Material::class);
    }

    public function lastUpdatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'last_updated_by');
    }
}
