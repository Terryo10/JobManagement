<!DOCTYPE html>
<html>
<head>
    <title>Invoice Signed</title>
</head>
<body style="font-family: Arial, sans-serif; color: #333; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #eaeaea; border-radius: 8px;">
        <h2 style="color: #2c3e50;">Invoice #{{ $invoice->invoice_number }} Signed</h2>
        <p>Hello,</p>
        <p>Thank you for signing the invoice. A finalized PDF version with your digital signature has been attached to this email for your records.</p>
        <br>
        <p>Thank you,<br>Household Media Team</p>
    </div>
</body>
</html>
