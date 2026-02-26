<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockLevel extends Model
{
    public $timestamps = false;

    protected static function booted(): void
    {
        static::updated(function (StockLevel $stockLevel) {
            $material = $stockLevel->material;
            if ($material && $material->minimum_stock_level && $stockLevel->current_quantity <= $material->minimum_stock_level) {
                $admins = User::role(['super_admin', 'manager'])->get();
                foreach ($admins as $admin) {
                    $admin->notify(new \App\Notifications\DatabaseAlert(
                        title: '⚠️ Low Stock Alert',
                        body: "{$material->name} is at {$stockLevel->current_quantity} {$material->unit} (minimum: {$material->minimum_stock_level})",
                        icon: 'heroicon-o-exclamation-triangle',
                        color: 'danger',
                    ));
                }
            }
        });
    }

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
