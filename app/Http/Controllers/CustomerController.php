<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $query = Customer::latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('reference_name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('whatsapp', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('gst_number', 'like', "%{$search}%")
                  ->orWhere('pan_number', 'like', "%{$search}%")
                  ->orWhere('gst_type', 'like', "%{$search}%")
                  ->orWhere('billing_address', 'like', "%{$search}%")
                  ->orWhere('shipping_address', 'like', "%{$search}%")
                  ->orWhere('state', 'like', "%{$search}%")
                  ->orWhere('country', 'like', "%{$search}%")
                  ->orWhere('notes', 'like', "%{$search}%")
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        $customers = $query->get();

        if ($request->ajax()) {
            return view('customers.partials.table', compact('customers'))->render();
        }

        return view('customers.index', compact('customers'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'reference_name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'gst_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
            'gst_type' => 'nullable|string|in:inter_state,intra_state',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'opening_balance' => 'nullable|numeric',
            'credit_limit' => 'nullable|numeric',
            'status' => 'required|string',
        ]);

        Customer::create($validated);

        return redirect()->route('customers.index')->with('success', 'Customer added successfully.');
    }

    public function edit(Customer $customer)
    {
        return view('customers.edit', compact('customer'));
    }

    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'reference_name' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:255',
            'whatsapp' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'gst_number' => 'nullable|string|max:20',
            'pan_number' => 'nullable|string|max:20',
            'gst_type' => 'nullable|string|in:inter_state,intra_state',
            'billing_address' => 'nullable|string',
            'shipping_address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'opening_balance' => 'nullable|numeric',
            'credit_limit' => 'nullable|numeric',
            'status' => 'required|string',
        ]);

        $customer->update($validated);

        return redirect()->route('customers.index')->with('success', 'Customer updated successfully.');
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Customer deleted successfully.');
    }

    public function search(Request $request)
    {
        $search = $request->input('q');
        $query = Customer::latest();
        
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('company_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }
        
        $customers = $query->limit(20)->get(['id', 'name', 'company_name', 'state', 'gst_type']);
        return response()->json($customers);
    }

    public function history($id)
    {
        $invoices = \App\Models\Invoice::where('customer_id', $id)
            ->with(['company'])
            ->latest('invoice_date')
            ->get();

        $performaInvoices = \App\Models\PerformaInvoice::where('customer_id', $id)
            ->with(['company'])
            ->latest('invoice_date')
            ->get();

        $payments = \App\Models\Payment::where('customer_id', $id)
            ->with(['invoice'])
            ->latest('payment_date')
            ->get();

        return response()->json([
            'invoices' => $invoices,
            'performa_invoices' => $performaInvoices,
            'payments' => $payments
        ]);
    }
}
