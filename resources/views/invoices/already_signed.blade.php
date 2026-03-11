<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice #{{ $invoice->invoice_number }} Already Signed</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased py-20">

<div class="max-w-xl mx-auto bg-white shadow-xl rounded-lg overflow-hidden text-center p-10">
    <div class="mb-6">
        <svg class="w-16 h-16 text-green-500 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
    </div>
    <h1 class="text-3xl font-bold text-gray-800 mb-2">Already Signed</h1>
    <p class="text-gray-600 mb-8">This invoice has already been signed or paid and requires no further action from you.</p>
    <p class="text-sm text-gray-500 mb-6">Invoice: #{{ $invoice->invoice_number }}</p>

    <a href="{{ route('invoices.client.download', ['invoice' => $invoice->id]) }}" 
       class="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 md:text-lg">
        <svg class="-ml-1 mr-2 h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
        Download PDF Record
    </a>
</div>

</body>
</html>
