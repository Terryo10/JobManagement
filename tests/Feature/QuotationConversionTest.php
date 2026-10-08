<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Quotation;
use App\Models\User;
use App\Models\WorkOrder;
use App\Services\QuotationConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_quotation_conversion_creates_one_work_order_with_every_item_and_no_sales_order(): void
    {
        [$user, $quotation] = $this->quotationFixture();
        $service = app(QuotationConversionService::class);

        $first = $service->toWorkOrder($quotation, $user, [
            'category' => 'civil_works',
            'priority' => 'high',
            'deadline' => '2026-11-30',
        ]);
        $second = $service->toWorkOrder($quotation->fresh(), $user);

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('work_orders', 1);
        $this->assertDatabaseCount('sales_orders', 0);
        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'work_order_id' => $first->id,
            'status' => 'converted',
        ]);
        $this->assertSame('civil_works', $first->category);
        $this->assertSame('high', $first->priority);
        $this->assertSame('2026-11-30', $first->deadline->toDateString());
        $this->assertSame('200.00', $first->budget);
        $this->assertSame($quotation->quotation_number, $first->details['source_quotation_number']);
        $this->assertSame('0771234567', $first->details['source_phone']);
        $this->assertSame('2026-12-31', $first->details['source_valid_until']);
        $this->assertSame('Install at the client site.', $first->details['source_notes']);
        $this->assertSame('200.00', $first->details['source_subtotal']);
        $this->assertCount(2, $first->details['source_items']);
        $this->assertSame('Printed banners', $first->details['source_items'][0]['description']);
        $this->assertSame('Installation', $first->details['source_items'][1]['description']);
    }

    public function test_conversion_does_not_inherit_details_from_an_unrelated_linked_job(): void
    {
        [$user, $quotation] = $this->quotationFixture();
        $workOrder = WorkOrder::create([
            'client_id' => Client::create([
                'company_name' => 'Other Client',
                'contact_person' => 'Other Contact',
                'created_by' => $user->id,
            ])->id,
            'title' => 'Existing job',
            'category' => 'media',
            'status' => 'pending',
            'priority' => 'normal',
            'created_by' => $user->id,
            'details' => ['logistics' => 'Use company van'],
        ]);
        $quotation->update(['work_order_id' => $workOrder->id]);

        $converted = app(QuotationConversionService::class)->toWorkOrder($quotation, $user);

        $this->assertFalse($workOrder->is($converted));
        $this->assertDatabaseCount('work_orders', 2);
        $this->assertDatabaseCount('sales_orders', 0);
        $this->assertSame('Use company van', $workOrder->fresh()->details['logistics']);
        $this->assertArrayNotHasKey('logistics', $converted->details);
        $this->assertNull($converted->category);
        $this->assertNull($converted->priority);
        $this->assertNull($converted->deadline);
        $this->assertNull($converted->actual_cost);
        $this->assertNull($converted->budget_alert_threshold);
        $this->assertArrayNotHasKey('date_order_received', $converted->details);
        $this->assertSame($quotation->quotation_number, $converted->details['source_quotation_number']);
        $this->assertSame('converted', $quotation->fresh()->status);
        $this->assertSame($converted->id, $quotation->fresh()->work_order_id);
    }

    public function test_conversion_reuses_only_the_job_created_from_that_quotation(): void
    {
        [$user, $quotation] = $this->quotationFixture();
        $service = app(QuotationConversionService::class);
        $first = $service->toWorkOrder($quotation, $user);
        $first->update(['title' => 'Edited after conversion']);

        $second = $service->toWorkOrder($quotation->fresh(), $user);

        $this->assertTrue($first->is($second));
        $this->assertSame('Edited after conversion', $second->title);
        $this->assertDatabaseCount('work_orders', 1);
    }

    public function test_an_unset_quotation_total_does_not_become_a_work_order_budget(): void
    {
        [$user, $quotation] = $this->quotationFixture();
        $quotation->update(['subtotal' => 0, 'total' => 0]);

        $workOrder = app(QuotationConversionService::class)->toWorkOrder($quotation, $user);

        $this->assertNull($workOrder->budget);
        $this->assertNull($workOrder->details['source_total']);
    }

    private function quotationFixture(): array
    {
        $user = User::factory()->create();
        $client = Client::create([
            'company_name' => 'Example Client',
            'contact_person' => 'Jane Client',
            'created_by' => $user->id,
        ]);
        $quotation = Quotation::create([
            'client_id' => $client->id,
            'created_by' => $user->id,
            'status' => 'accepted',
            'currency' => 'USD',
            'phone' => '0771234567',
            'valid_until' => '2026-12-31',
            'subtotal' => 200,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total' => 200,
            'notes' => 'Install at the client site.',
        ]);
        $quotation->items()->createMany([
            [
                'description' => 'Printed banners',
                'quantity' => 3,
                'unit' => 'each',
                'unit_price' => 50,
                'total' => 150,
            ],
            [
                'description' => 'Installation',
                'quantity' => 2,
                'unit' => 'hours',
                'unit_price' => 25,
                'total' => 50,
            ],
        ]);

        return [$user, $quotation];
    }
}
