<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\CompanyController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\PerformaInvoiceController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RenewalController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HsnMasterController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::resource('companies', CompanyController::class)->except(['show']);
    Route::post('companies/switch', [CompanyController::class, 'switch'])->name('companies.switch');
    Route::get('api/customers/search', [CustomerController::class, 'search'])->name('api.customers.search');
    Route::get('api/customers/{id}/history', [CustomerController::class, 'history'])->name('api.customers.history');
    Route::resource('customers', CustomerController::class)->except(['show']);
    Route::resource('hsn-masters', HsnMasterController::class)->except(['show']);
    
    Route::get('invoices/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
    Route::resource('invoices', InvoiceController::class);

    Route::get('performa-invoices/{performa_invoice}/download', [PerformaInvoiceController::class, 'download'])->name('performa-invoices.download');
    Route::post('performa-invoices/{performa_invoice}/convert', [PerformaInvoiceController::class, 'convertToInvoice'])->name('performa-invoices.convert');
    Route::resource('performa-invoices', PerformaInvoiceController::class);

    Route::resource('payments', PaymentController::class)->only(['index', 'create', 'store', 'destroy']);
    Route::resource('renewals', RenewalController::class)->except(['show']);
    Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store', 'destroy']);
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
