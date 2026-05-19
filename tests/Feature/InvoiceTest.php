<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;

function invoiceTestUser(): User
{
    return User::factory()->create([
        'email_verified_at' => now(),
    ]);
}

function invoiceTestCompany(array $attributes = []): Company
{
    return Company::create(array_merge([
        'name' => 'Web Work',
        'legal_name' => 'Web Work Pvt Ltd',
        'gst_number' => '27CELPS7220B1ZI',
        'address' => 'Mumbai',
        'invoice_prefix' => 'INV',
        'invoice_starting_number' => 1,
    ], $attributes));
}

function invoiceTestCustomer(array $attributes = []): Customer
{
    return Customer::create(array_merge([
        'name' => 'Test Customer',
        'company_name' => 'Client Co',
        'billing_address' => 'Client Address',
        'gst_type' => 'intra_state',
        'state' => 'Maharashtra',
        'status' => 'active',
    ], $attributes));
}

test('invoice create page renders with fallback active company', function () {
    $user = invoiceTestUser();
    $company = invoiceTestCompany();

    $response = $this
        ->actingAs($user)
        ->get(route('invoices.create'));

    $response->assertOk();
    $response->assertSee($company->name);
});

test('invoice show page renders company and customer details safely', function () {
    $user = invoiceTestUser();
    $company = invoiceTestCompany([
        'bank_name' => 'Axis Bank',
        'account_number' => '1234567890',
        'ifsc_code' => 'UTIB0000001',
    ]);
    $customer = invoiceTestCustomer();

    $invoice = Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-1001',
        'invoice_date' => now()->toDateString(),
        'status' => 'pending',
        'subtotal' => 1000,
        'taxable_amount' => 1000,
        'gst_enabled' => true,
        'cgst' => 90,
        'sgst' => 90,
        'igst' => 0,
        'total_gst' => 180,
        'grand_total' => 1180,
        'outstanding_amount' => 1180,
    ]);

    $invoice->items()->create([
        'name' => 'Design Work',
        'description' => 'Landing page design',
        'quantity' => 1,
        'rate' => 1000,
        'gst_percentage' => 18,
        'cgst' => 90,
        'sgst' => 90,
        'igst' => 0,
        'total' => 1180,
    ]);

    $response = $this
        ->actingAs($user)
        ->get(route('invoices.show', $invoice));

    $response->assertOk();
    $response->assertSee($company->legal_name);
    $response->assertSee($customer->company_name);
    $response->assertSee('Rs. 1,180.00');
});

test('invoice update accepts the existing invoice number', function () {
    $user = invoiceTestUser();
    $company = invoiceTestCompany();
    $customer = invoiceTestCustomer();

    $invoice = Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'invoice_number' => 'INV-1002',
        'invoice_date' => now()->toDateString(),
        'status' => 'pending',
        'subtotal' => 500,
        'taxable_amount' => 500,
        'gst_enabled' => true,
        'cgst' => 45,
        'sgst' => 45,
        'igst' => 0,
        'total_gst' => 90,
        'grand_total' => 590,
        'outstanding_amount' => 590,
    ]);

    $invoice->items()->create([
        'name' => 'SEO Audit',
        'description' => 'SEO Audit',
        'quantity' => 1,
        'rate' => 500,
        'gst_percentage' => 18,
        'cgst' => 45,
        'sgst' => 45,
        'igst' => 0,
        'total' => 590,
    ]);

    $response = $this
        ->actingAs($user)
        ->patch(route('invoices.update', $invoice), [
            'customer_id' => $customer->id,
            'invoice_number' => 'INV-1002',
            'invoice_date' => now()->toDateString(),
            'gst_enabled' => 1,
            'items' => [
                [
                    'description' => 'SEO Audit Updated',
                    'price' => 500,
                ],
            ],
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('invoices.index'));

    expect($invoice->fresh()->invoice_number)->toBe('INV-1002');
});
