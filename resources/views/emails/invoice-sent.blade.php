<!DOCTYPE html>
<html>
<head>
    <title>New Invoice from Household Media</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eaeaea; border-radius: 8px;">
        <h2 style="color: #2c3e50;">Invoice #{{ $invoice->invoice_number }}</h2>
        <p>Hello{{ $invoice->client ? ' ' . $invoice->client->company_name : '' }},</p>
        <p>A new invoice has been generated for your recent project. The total amount due is <strong>${{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</strong>.</p>
        <p>You can review and cryptographically sign the invoice by clicking the secure link below:</p>
        <br>
        <div style="text-align: center;">
            <a href="{{ route('invoices.sign.show', ['invoice' => $invoice->id]) }}" 
               style="background-color: #3498db; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Review & Sign Invoice
            </a>
        </div>
        <br>
        <p>If you have any questions, feel free to reply to this email.</p>
        <p>Thank you,<br>Household Media Team</p>
    </div>
</body>
</html>
