<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #333; line-height: 1.5; font-size: 14px; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #2c3e50; padding-bottom: 15px; margin-bottom: 30px; }
        .header h1 { color: #2c3e50; margin: 0; font-size: 28px; }
        .company-details { float: left; width: 50%; }
        .invoice-details { float: right; width: 40%; text-align: right; }
        .clear { clear: both; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; }
        th, td { border-bottom: 1px solid #ddd; padding: 10px; text-align: left; }
        th { background-color: #f8f9fa; font-weight: bold; }
        .amounts { width: 40%; float: right; margin-bottom: 30px; }
        .amounts table th { text-align: right; background: none; }
        .amounts table td { text-align: right; }
        .total-row th, .total-row td { border-top: 2px solid #333; font-weight: bold; font-size: 16px; }
        .signature-box { margin-top: 50px; padding-top: 20px; border-top: 1px dashed #ccc; width: 50%; }
        .signature-img { max-height: 100px; display: block; margin-top: 10px; }
    </style>
</head>
<body>

<div class="header">
    <div class="company-details">
        <h1>Household Media</h1>
        <p>No. 8 Donald McDonald Road<br>Eastlea, Harare<br>billing@householdmedia.com</p>
    </div>
    <div class="invoice-details">
        <h2>INVOICE</h2>
        <p><strong>Invoice #:</strong> {{ $invoice->invoice_number }}<br>
        <strong>Date Issued:</strong> {{ $invoice->issued_at ? $invoice->issued_at->format('M d, Y') : 'N/A' }}<br>
        <strong>Due Date:</strong> {{ $invoice->due_at ? $invoice->due_at->format('M d, Y') : 'N/A' }}</p>
    </div>
    <div class="clear"></div>
</div>

<div style="margin-bottom: 30px;">
    <strong>Billed To:</strong><br>
    {{ $invoice->client ? $invoice->client->company_name : 'No Client Assigned' }}<br>
    {{ $invoice->client ? $invoice->client->address : '' }}<br>
    {{ $invoice->client ? $invoice->client->email : '' }}
</div>

<table>
    <thead>
        <tr>
            <th>Description</th>
            <th>Qty</th>
            <th>Unit Price</th>
            <th>Total</th>
        </tr>
    </thead>
    <tbody>
        @forelse($invoice->items as $item)
        <tr>
            <td>{{ $item->description }}</td>
            <td>{{ $item->quantity }}</td>
            <td>${{ number_format($item->unit_price, 2) }}</td>
            <td>${{ number_format($item->total, 2) }}</td>
        </tr>
        @empty
        <tr>
            <td colspan="4" style="text-align: center;">No line items found.</td>
        </tr>
        @endforelse
    </tbody>
</table>

<div class="amounts">
    <table>
        <tr>
            <th>Subtotal:</th>
            <td>${{ number_format($invoice->subtotal, 2) }}</td>
        </tr>
        <tr>
            <th>Tax ({{ $invoice->tax_rate }}%):</th>
            <td>${{ number_format($invoice->tax_amount, 2) }}</td>
        </tr>
        <tr class="total-row">
            <th>Total Due:</th>
            <td>${{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</td>
        </tr>
    </table>
</div>
<div class="clear"></div>

@if($invoice->client_signature)
<div class="signature-box">
    <strong>Authorized Signature:</strong>
    <img src="{{ $invoice->client_signature }}" class="signature-img" alt="Client Signature">
    <p style="font-size: 12px; color: #777;">
        Signed on: {{ $invoice->client_signature_date ? $invoice->client_signature_date->format('M d, Y h:i A') : 'N/A' }}<br>
        IP Address: {{ $invoice->client_ip }}
    </p>
</div>
@endif

</body>
</html>
