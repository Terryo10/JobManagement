<!DOCTYPE html>
<html>
<head>
    <title>Invoice Signed</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eaeaea; border-radius: 8px;">
        <h2 style="color: #2c3e50;">Invoice #{{ $invoice->invoice_number }} Signed</h2>
        <p>Hello{{ $invoice->client ? ' ' . $invoice->client->company_name : '' }},</p>
        <p>Thank you for signing the invoice. You can download your finalized PDF copy using the link below.</p>
        <br>
        @isset($downloadUrl)
        <div style="text-align: center;">
            <a href="{{ $downloadUrl }}"
               style="background-color: #27ae60; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold;">
                Download Signed Invoice
            </a>
        </div>
        <br>
        @endisset
        <p>Thank you,<br>Household Media Team</p>
    </div>
</body>
</html>
