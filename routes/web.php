<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\InvoiceSignatureController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/invoices/{invoice}/sign', [InvoiceSignatureController::class, 'show'])
    ->name('invoices.sign.show');

Route::post('/invoices/{invoice}/sign', [InvoiceSignatureController::class, 'sign'])
    ->name('invoices.sign.store');

Route::get('/invoices/{invoice}/download', [InvoiceSignatureController::class, 'download'])
    ->name('invoices.client.download');

