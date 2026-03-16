<?php

namespace App\Observers;

use App\Models\StockLevel;
use App\Notifications\NotificationEvent;
use App\Services\NotificationRouter;

class StockLevelObserver
{
    public function updated(StockLevel $stockLevel): void
    {
        $material = $stockLevel->material;

        if (! $material || ! $material->minimum_stock_level) {
            return;
        }

        if ($stockLevel->current_quantity > $material->minimum_stock_level) {
            return;
        }

        app(NotificationRouter::class)->dispatch(new NotificationEvent(
            type:           'stock.low',
            title:          '⚠️ Low Stock Alert',
            body:           "{$material->name} is at {$stockLevel->current_quantity} {$material->unit} (minimum: {$material->minimum_stock_level})",
            icon:           'heroicon-o-exclamation-triangle',
            color:          'danger',
            recipientRoles: ['super_admin', 'manager'],
            subjectType:    StockLevel::class,
            subjectId:      $stockLevel->id,
            priority:       'high',
        ));
    }
}
