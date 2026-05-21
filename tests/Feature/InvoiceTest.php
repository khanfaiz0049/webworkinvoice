<?php

use App\Models\Company;
use App\Models\Customer;
use App\Models\HsnMaster;
use App\Models\Invoice;
use App\Models\Payment;
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

test('invoice store clears hsn code when gst is disabled', function () {
    $user = invoiceTestUser();
    $company = invoiceTestCompany([
        'invoice_starting_number' => 1003,
    ]);
    $customer = invoiceTestCustomer();

    $response = $this
        ->actingAs($user)
        ->post(route('invoices.store'), [
            'customer_id' => $customer->id,
            'invoice_number' => (string) $company->invoice_starting_number,
            'invoice_date' => now()->toDateString(),
            'gst_enabled' => 0,
            'items' => [
                [
                    'description' => 'Hosting Renewal',
                    'quantity' => 1,
                    'price' => 1200,
                    'discount' => 0,
                    'gst_percentage' => 0,
                    'hsn_sac' => '998314',
                ],
            ],
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('invoices.index'));

    $invoice = Invoice::latest('id')->first();

    expect($invoice->gst_enabled)->toBeFalse();
    expect($invoice->items()->first()->hsn_sac)->toBeNull();
});

test('invoice store defaults hsn code to 9983 when gst is enabled and no code is selected', function () {
    $user = invoiceTestUser();
    $company = invoiceTestCompany([
        'invoice_starting_number' => 1004,
    ]);
    $customer = invoiceTestCustomer();

    HsnMaster::create([
        'service_name' => 'Default Service',
        'hsn_code' => '9983',
    ]);

    $response = $this
        ->actingAs($user)
        ->post(route('invoices.store'), [
            'customer_id' => $customer->id,
            'invoice_number' => (string) $company->invoice_starting_number,
            'invoice_date' => now()->toDateString(),
            'gst_enabled' => 1,
            'items' => [
                [
                    'description' => 'Website Maintenance',
                    'quantity' => 1,
                    'price' => 2500,
                    'discount' => 0,
                    'gst_percentage' => 18,
                    'hsn_sac' => '',
                ],
            ],
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('invoices.index'));

    $invoice = Invoice::latest('id')->first();

    expect($invoice->gst_enabled)->toBeTrue();
    expect($invoice->items()->first()->hsn_sac)->toBe('9983');
});

test('invoice store uses the next available invoice number when company counter is stale', function () {
    $user = invoiceTestUser();
    $company = invoiceTestCompany([
        'invoice_starting_number' => 1,
    ]);
    $customer = invoiceTestCustomer();

    Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'invoice_number' => '1',
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

    $response = $this
        ->actingAs($user)
        ->post(route('invoices.store'), [
            'customer_id' => $customer->id,
            'invoice_number' => '1',
            'invoice_date' => now()->toDateString(),
            'gst_enabled' => 1,
            'items' => [
                [
                    'description' => 'Support Retainer',
                    'quantity' => 1,
                    'price' => 1000,
                    'discount' => 0,
                    'gst_percentage' => 18,
                    'hsn_sac' => '9983',
                ],
            ],
        ]);

    $response->assertSessionHasNoErrors();
    $response->assertRedirect(route('invoices.index'));

    $invoice = Invoice::latest('id')->first();

    expect($invoice->invoice_number)->toBe('2');
    expect($company->fresh()->invoice_starting_number)->toBe(3);
});

test('invoice undo permanently deletes the latest invoice and rolls back the company counter', function () {
    $user = invoiceTestUser();
    $company = invoiceTestCompany([
        'invoice_starting_number' => 5,
    ]);
    $customer = invoiceTestCustomer();

    $invoice = Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'invoice_number' => '4',
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

    Payment::create([
        'customer_id' => $customer->id,
        'invoice_id' => $invoice->id,
        'amount' => 500,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'UPI',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('invoices.destroy', $invoice));

    $response->assertRedirect(route('invoices.index'));

    expect(Invoice::withTrashed()->find($invoice->id))->toBeNull();
    expect(Payment::withTrashed()->count())->toBe(0);
    expect($company->fresh()->invoice_starting_number)->toBe(4);
});

test('invoice undo rolls back the company counter to an older invoice number if it is lower', function () {
    $user = invoiceTestUser();
    $company = invoiceTestCompany([
        'invoice_starting_number' => 5,
    ]);
    $customer = invoiceTestCustomer();

    $invoice = Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'invoice_number' => '2',
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

    $response = $this
        ->actingAs($user)
        ->delete(route('invoices.destroy', $invoice));

    $response->assertRedirect(route('invoices.index'));

    expect($company->fresh()->invoice_starting_number)->toBe(2);
});

test('payment undo permanently deletes the payment and restores invoice balances', function () {
    $user = invoiceTestUser();
    $company = invoiceTestCompany();
    $customer = invoiceTestCustomer();

    $invoice = Invoice::create([
        'company_id' => $company->id,
        'customer_id' => $customer->id,
        'invoice_number' => '10',
        'invoice_date' => now()->toDateString(),
        'status' => 'partial',
        'subtotal' => 1000,
        'taxable_amount' => 1000,
        'gst_enabled' => true,
        'cgst' => 90,
        'sgst' => 90,
        'igst' => 0,
        'total_gst' => 180,
        'grand_total' => 1180,
        'paid_amount' => 500,
        'outstanding_amount' => 680,
    ]);

    $payment = Payment::create([
        'customer_id' => $customer->id,
        'invoice_id' => $invoice->id,
        'amount' => 500,
        'payment_date' => now()->toDateString(),
        'payment_method' => 'UPI',
    ]);

    $response = $this
        ->actingAs($user)
        ->delete(route('payments.destroy', $payment));

    $response->assertRedirect(route('payments.index'));

    $invoice->refresh();

    expect(Payment::withTrashed()->find($payment->id))->toBeNull();
    expect((float) $invoice->paid_amount)->toBe(0.0);
    expect((float) $invoice->outstanding_amount)->toBe(1180.0);
    expect($invoice->status)->toBe('pending');
});
