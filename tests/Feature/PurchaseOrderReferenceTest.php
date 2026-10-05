<?php

namespace Tests\Feature;

use App\Models\PurchaseOrder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchaseOrderReferenceTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_requisitions_continue_after_existing_references_without_reusing_a_number(): void
    {
        $user = User::factory()->create();
        $year = now()->year;

        PurchaseOrder::create([
            'title' => 'Existing requisition',
            'reference_number' => "REQ-{$year}-0701",
            'ordered_by' => $user->id,
        ]);
        PurchaseOrder::create([
            'title' => 'Existing five-digit requisition',
            'reference_number' => "REQ-{$year}-10000",
            'ordered_by' => $user->id,
        ]);

        $first = PurchaseOrder::create([
            'title' => 'First new requisition',
            'ordered_by' => $user->id,
        ]);
        $second = PurchaseOrder::create([
            'title' => 'Second new requisition',
            'ordered_by' => $user->id,
        ]);

        $this->assertSame("REQ-{$year}-10001", $first->reference_number);
        $this->assertSame("REQ-{$year}-10002", $second->reference_number);
    }
}
