<?php

namespace Tests\Feature;

use App\Models\BankAccount;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Quotation;
use App\Models\User;
use App\Services\QuotationConversionService;
use App\Services\QuotationInvoiceConversionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class QuotationInvoiceConversionTest extends TestCase
{
    use RefreshDatabase;

    public function test_conversion_creates_one_draft_invoice_with_calculated_items_and_valid_work_order_link(): void
    {
        [$user, $quotation, $bankAccount] = $this->fixture();
        $workOrder = app(QuotationConversionService::class)->toWorkOrder($quotation, $user);
        $service = app(QuotationInvoiceConversionService::class);

        $first = $service->convert($quotation->fresh(), $user);
        $second = $service->convert($quotation->fresh(), $user);

        $this->assertTrue($first->is($second));
        $this->assertDatabaseCount('invoices', 1);
        $this->assertDatabaseCount('invoice_items', 2);
        $this->assertSame($quotation->id, $first->quotation_id);
        $this->assertSame($workOrder->id, $first->work_order_id);
        $this->assertSame($quotation->client_id, $first->client_id);
        $this->assertSame($bankAccount->id, $first->bank_account_id);
        $this->assertSame('draft', $first->status);
        $this->assertNull($first->issued_at);
        $this->assertNull($first->due_at);
        $this->assertSame('USD', $first->currency);
        $this->assertSame('200.00', $first->subtotal);
        $this->assertSame('10.00', $first->tax_rate);
        $this->assertSame('20.00', $first->tax_amount);
        $this->assertSame('220.00', $first->total);
        $this->assertSame('Install at client site', $first->notes);
        $this->assertSame('converted', $quotation->fresh()->status);
        $this->assertDatabaseHas('invoice_items', [
            'invoice_id' => $first->id,
            'description' => 'Printed banners',
            'quantity' => 3,
            'unit_price' => 50,
            'total' => 150,
        ]);
    }

    public function test_invoice_number_follows_existing_numbers_including_deleted_invoices(): void
    {
        [$user, $quotation] = $this->fixture();
        $year = now()->year;
        $existing = Invoice::create([
            'invoice_number' => "INV-{$year}-0088",
            'client_id' => $quotation->client_id,
            'status' => 'draft',
        ]);
        $existing->delete();

        $invoice = app(QuotationInvoiceConversionService::class)->convert($quotation, $user);

        $this->assertSame("INV-{$year}-0089", $invoice->invoice_number);
    }

    public function test_empty_quotation_does_not_create_an_invoice_or_change_status(): void
    {
        [$user, $quotation] = $this->fixture();
        $quotation->items()->delete();

        try {
            app(QuotationInvoiceConversionService::class)->convert($quotation, $user);
            $this->fail('Expected validation to reject an empty quotation.');
        } catch (ValidationException) {
            $this->assertDatabaseCount('invoices', 0);
            $this->assertSame('accepted', $quotation->fresh()->status);
        }
    }

    public function test_draft_quotation_cannot_be_invoiced(): void
    {
        [$user, $quotation] = $this->fixture();
        $quotation->update(['status' => 'draft']);

        $this->expectException(ValidationException::class);
        app(QuotationInvoiceConversionService::class)->convert($quotation, $user);
    }

    private function fixture(): array
    {
        $user = User::factory()->create();
        $client = Client::create([
            'company_name' => 'Example Client',
            'contact_person' => 'Jane Client',
            'created_by' => $user->id,
        ]);
        $bankAccount = BankAccount::create([
            'account_name' => 'Operating Account',
            'bank_name' => 'Example Bank',
            'account_number' => '12345678',
        ]);
        $quotation = Quotation::create([
            'client_id' => $client->id,
            'created_by' => $user->id,
            'bank_account_id' => $bankAccount->id,
            'status' => 'accepted',
            'currency' => 'USD',
            'subtotal' => 0,
            'tax_rate' => 10,
            'tax_amount' => 0,
            'total' => 0,
            'notes' => 'Install at client site',
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

        return [$user, $quotation, $bankAccount];
    }
}
