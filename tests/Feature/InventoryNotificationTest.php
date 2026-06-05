<?php

namespace Tests\Feature;

use App\Models\InventoryRequisition;
use App\Models\Material;
use App\Models\User;
use App\Services\InventoryService;
use App\Services\NotificationRouter;
use App\Notifications\NotificationEvent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_approve_inventory_requisition_sends_notification()
    {
        $requester = User::factory()->create();
        $approver = User::factory()->create();
        
        $material = Material::create([
            'name' => 'Wire 2.5mm',
            'sku' => 'WIRE-2.5',
            'unit' => 'm',
        ]);

        $requisition = InventoryRequisition::create([
            'type' => 'inventory',
            'material_id' => $material->id,
            'quantity_requested' => 10,
            'requested_by' => $requester->id,
            'status' => 'pending',
        ]);

        $mock = $this->mock(NotificationRouter::class);
        $mock->shouldReceive('dispatch')
            ->once()
            ->withArgs(function (NotificationEvent $event) use ($requester, $requisition) {
                return $event->type === 'inventory_requisition.approved'
                    && $event->recipientUserIds === [$requester->id]
                    && $event->subjectId === $requisition->id;
            });

        app(InventoryService::class)->approveInventoryRequisition($requisition, $approver);
    }

    public function test_issue_from_stock_sends_notification()
    {
        $requester = User::factory()->create();
        $issuedBy = User::factory()->create();
        
        $material = Material::create([
            'name' => 'Wire 2.5mm',
            'sku' => 'WIRE-2.5',
            'unit' => 'm',
        ]);

        $requisition = InventoryRequisition::create([
            'type' => 'inventory',
            'material_id' => $material->id,
            'quantity_requested' => 10,
            'requested_by' => $requester->id,
            'status' => 'approved',
        ]);

        // We need to ensure stock exists so that deduct doesn't fail
        $stockLevel = app(InventoryService::class)->ensureStockLevel($material);
        $stockLevel->add(20, $issuedBy, $requisition);

        $mock = $this->mock(NotificationRouter::class);
        $mock->shouldReceive('dispatch')
            ->once()
            ->withArgs(function (NotificationEvent $event) use ($requester, $requisition) {
                return $event->type === 'inventory_requisition.issued'
                    && $event->recipientUserIds === [$requester->id]
                    && $event->subjectId === $requisition->id;
            });

        app(InventoryService::class)->issueFromStock($requisition, $issuedBy);
    }

    public function test_reject_inventory_requisition_sends_notification()
    {
        $requester = User::factory()->create();
        $rejectedBy = User::factory()->create();
        
        $material = Material::create([
            'name' => 'Wire 2.5mm',
            'sku' => 'WIRE-2.5',
            'unit' => 'm',
        ]);

        $requisition = InventoryRequisition::create([
            'type' => 'inventory',
            'material_id' => $material->id,
            'quantity_requested' => 10,
            'requested_by' => $requester->id,
            'status' => 'pending',
        ]);

        $mock = $this->mock(NotificationRouter::class);
        $mock->shouldReceive('dispatch')
            ->once()
            ->withArgs(function (NotificationEvent $event) use ($requester, $requisition) {
                return $event->type === 'inventory_requisition.rejected'
                    && $event->recipientUserIds === [$requester->id]
                    && $event->subjectId === $requisition->id;
            });

        app(InventoryService::class)->reject($requisition, $rejectedBy, 'No stock available');
    }
}
