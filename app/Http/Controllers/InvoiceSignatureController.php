<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Invoice;
use App\Services\InvoiceMailService;
use Barryvdh\DomPDF\Facade\Pdf;

class InvoiceSignatureController extends Controller
{
    public function show(Invoice $invoice)
    {
        if ($invoice->status === 'signed' || $invoice->status === 'paid') {
            return view('invoices.already_signed', compact('invoice'));
        }

        return view('invoices.sign', compact('invoice'));
    }

    public function sign(Request $request, Invoice $invoice)
    {
        $request->validate([
            'signature' => 'required|string',
        ]);

        if ($invoice->status === 'signed' || $invoice->status === 'paid') {
            return redirect()->back()->with('error', 'Invoice is already signed or paid.');
        }

        $invoice->update([
            'client_signature' => $request->signature,
            'client_signature_date' => now(),
            'client_ip' => $request->ip(),
            'status' => 'signed',
        ]);

        if ($invoice->client && $invoice->client->email) {
            app(InvoiceMailService::class)->sendInvoiceSigned($invoice, $invoice->client->email);
        }

        return redirect()->route('invoices.sign.show', $invoice)->with('success', 'Thank you! Your invoice has been signed successfully.');
    }

    public function download(Invoice $invoice)
    {
        if ($invoice->status !== 'signed' && $invoice->status !== 'paid') {
            abort(403, 'Invoice is not available for download yet.');
        }

        $pdf = Pdf::loadView('pdf.invoice', ['invoice' => $invoice]);
        return response()->streamDownload(
            fn () => print($pdf->output()),
            "invoice-{$invoice->invoice_number}.pdf"
        );
    }
}
