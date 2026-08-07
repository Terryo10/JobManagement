<?php

namespace App\Services;

use App\Mail\InvoiceSentToClient;
use App\Mail\InvoiceSigned;
use App\Models\Invoice;
use Illuminate\Support\Facades\Mail;

class InvoiceMailService
{
    /**
     * Send the "invoice sent to client" email.
     */
    public function sendInvoiceToClient(Invoice $invoice, string $toEmail): void
    {
        Mail::to($toEmail)->send(new InvoiceSentToClient($invoice));
    }

    /**
     * Send the "invoice signed" confirmation email.
     * Includes a download link to the PDF instead of attaching it.
     */
    public function sendInvoiceSigned(Invoice $invoice, string $toEmail): void
    {
        Mail::to($toEmail)->send(new InvoiceSigned($invoice));
    }
}
