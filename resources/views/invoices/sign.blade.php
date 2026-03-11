<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Invoice #{{ $invoice->invoice_number }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    <style>
        .signature-pad {
            border: 2px dashed #cbd5e1;
            border-radius: 0.5rem;
            cursor: crosshair;
            background-color: white;
            touch-action: none;
        }
    </style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased py-10">

<div class="max-w-3xl mx-auto bg-white shadow-xl rounded-lg overflow-hidden">
    <!-- Header -->
    <div class="bg-slate-900 border-b p-6 text-white flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">Household Media</h1>
            <p class="text-sm opacity-80">Invoice Review & Sign</p>
        </div>
        <div class="text-right">
            <h2 class="text-xl font-semibold">#{{ $invoice->invoice_number }}</h2>
            <p class="text-gray-300">Total: ${{ number_format($invoice->total, 2) }} {{ $invoice->currency }}</p>
        </div>
    </div>

    <!-- Alert -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 m-6" role="alert">
            <p>{{ session('success') }}</p>
        </div>
    @endif
    
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 m-6" role="alert">
            <p>{{ session('error') }}</p>
        </div>
    @endif

    <!-- Content -->
    <div class="p-8">
        <h3 class="text-lg font-semibold mb-4 border-b pb-2">Invoice Details</h3>
        <div class="grid grid-cols-2 gap-4 mb-8">
            <div>
                <p class="text-sm text-gray-500">Client</p>
                <p class="font-medium">{{ $invoice->client ? $invoice->client->company_name : 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Date Issued</p>
                <p class="font-medium">{{ $invoice->issued_at ? $invoice->issued_at->format('M d, Y') : 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Subtotal</p>
                <p class="font-medium">${{ number_format($invoice->subtotal, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-500">Tax</p>
                <p class="font-medium">${{ number_format($invoice->tax_amount, 2) }}</p>
            </div>
        </div>

        @if($invoice->status !== 'signed' && $invoice->status !== 'paid' && !session('success'))
        <form id="signature-form" action="{{ route('invoices.sign.store', $invoice) }}" method="POST">
            @csrf
            <h3 class="text-lg font-semibold mb-4 border-b pb-2">Digital Signature</h3>
            <p class="text-sm text-gray-600 mb-4">Please sign within the box below to accept the terms and total amount of this invoice.</p>
            
            <div class="mb-4">
                <canvas id="signature-pad" class="signature-pad w-full h-48"></canvas>
                <input type="hidden" name="signature" id="signature-input" required>
            </div>
            
            <div class="flex justify-between items-center">
                <button type="button" id="clear-btn" class="text-sm text-red-600 hover:text-red-800 font-medium">
                    Clear Signature
                </button>
                <button type="submit" id="submit-btn" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-6 rounded shadow">
                    Submit Signature
                </button>
            </div>
        </form>
        @endif
    </div>
</div>

@if($invoice->status !== 'signed' && $invoice->status !== 'paid' && !session('success'))
<script>
    document.addEventListener("DOMContentLoaded", function() {
        var canvas = document.getElementById('signature-pad');
        
        // Make it visually fill the parent
        canvas.style.width = '100%';
        canvas.style.height = '100%';
        
        // set aspect ratio based on parent width
        canvas.width  = canvas.offsetWidth;
        canvas.height = canvas.offsetHeight;
        
        var signaturePad = new SignaturePad(canvas, {
            backgroundColor: 'rgb(255, 255, 255)'
        });

        document.getElementById('clear-btn').addEventListener('click', function () {
            signaturePad.clear();
        });

        document.getElementById('signature-form').addEventListener('submit', function (e) {
            if (signaturePad.isEmpty()) {
                e.preventDefault();
                alert("Please provide a signature first.");
            } else {
                var dataUrl = signaturePad.toDataURL('image/png');
                document.getElementById('signature-input').value = dataUrl;
            }
        });
    });
</script>
@endif

</body>
</html>
