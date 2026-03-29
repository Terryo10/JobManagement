<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\View;

class InvoiceMailService
{
    public function __construct(private InfobipClient $client) {}

    /**
     * Send the "invoice sent to client" email via Infobip REST API.
     */
    public function sendInvoiceToClient(Invoice $invoice, string $toEmail): void
    {
        $html = View::make('emails.invoice-sent', ['invoice' => $invoice])->render();

        $this->client->sendEmail(
            to: $toEmail,
            subject: 'New Invoice - ' . $invoice->invoice_number,
            htmlBody: $html,
        );
    }

    /**
     * Send the "invoice signed" confirmation email via Infobip REST API.
     * Includes a download link to the PDF instead of attaching it.
     */
    public function sendInvoiceSigned(Invoice $invoice, string $toEmail): void
    {
        $downloadUrl = route('invoices.client.download', ['invoice' => $invoice->id]);

        $html = View::make('emails.invoice-signed', [
            'invoice'     => $invoice,
            'downloadUrl' => $downloadUrl,
        ])->render();

        $this->client->sendEmail(
            to: $toEmail,
            subject: 'Signed Invoice - ' . $invoice->invoice_number,
            htmlBody: $html,
        );
    }
}
