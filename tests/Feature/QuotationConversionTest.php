<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Quotation;
use App\Models\User;
use App\Services\QuotationConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuotationConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_quotation_can_be_converted_to_a_linked_job_card_only_once(): void
    {
        [$user, $quotation] = $this->quotationFixture();
        $service = app(QuotationConversionService::class);

        $first = $service->toWorkOrder($quotation, $user);
        $second = $service->toWorkOrder($quotation->fresh(), $user);

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('work_orders', 1);
        $this->assertDatabaseHas('quotations', [
            'id' => $quotation->id,
            'work_order_id' => $first->id,
            'status' => 'converted',
        ]);
        $this->assertSame('media', $first->category);
        $this->assertSame('150.00', $first->budget);
        $this->assertSame($quotation->quotation_number, $first->details['source_quotation_number']);
    }

    public function test_a_quotation_can_be_converted_to_a_sales_order_with_its_items_only_once(): void
    {
        [$user, $quotation] = $this->quotationFixture();
        $service = app(QuotationConversionService::class);

        $first = $service->toSalesOrder($quotation, $user);
        $second = $service->toSalesOrder($quotation->fresh(), $user);
        $jobCard = $service->toWorkOrder($quotation->fresh(), $user);

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('sales_orders', 1);
        $this->assertDatabaseCount('sales_order_items', 1);
        $this->assertSame($quotation->id, $first->quotation_id);
        $this->assertSame('150.00', $first->total);
        $this->assertSame($jobCard->id, $first->fresh()->work_order_id);
        $this->assertDatabaseHas('sales_order_items', [
            'sales_order_id' => $first->id,
            'description' => 'Printed banners',
            'quantity' => 3,
            'unit_price' => 50,
            'total' => 150,
        ]);
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
            'subtotal' => 150,
            'tax_rate' => 0,
            'tax_amount' => 0,
            'total' => 150,
            'notes' => 'Install at the client site.',
        ]);
        $quotation->items()->create([
            'description' => 'Printed banners',
            'quantity' => 3,
            'unit' => 'each',
            'unit_price' => 50,
            'total' => 150,
        ]);

        return [$user, $quotation];
    }
}
