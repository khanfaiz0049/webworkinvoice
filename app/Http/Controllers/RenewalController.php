<?php

namespace App\Http\Controllers;

use App\Models\Renewal;
use App\Models\Customer;
use App\Models\Company;
use Illuminate\Http\Request;

class RenewalController extends Controller
{
    public function index()
    {
        $renewals = Renewal::with(['customer', 'company'])
            ->orderBy('due_date', 'asc')
            ->get();
            
        return view('renewals.index', compact('renewals'));
    }

    public function create()
    {
        $customers = Customer::all();
        $activeCompanyId = session('active_company_id');
        return view('renewals.create', compact('customers', 'activeCompanyId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'due_date' => 'required|date',
            'frequency' => 'required|string',
            'status' => 'required|string',
        ]);

        $validated['company_id'] = session('active_company_id');

        Renewal::create($validated);

        return redirect()->route('renewals.index')->with('success', 'Renewal scheduled successfully.');
    }

    public function edit(Renewal $renewal)
    {
        $customers = Customer::all();
        return view('renewals.edit', compact('renewal', 'customers'));
    }

    public function update(Request $request, Renewal $renewal)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'name' => 'required|string|max:255',
            'due_date' => 'required|date',
            'frequency' => 'required|string',
            'status' => 'required|string',
        ]);

        $renewal->update($validated);

        return redirect()->route('renewals.index')->with('success', 'Renewal updated successfully.');
    }

    public function destroy(Renewal $renewal)
    {
        $renewal->delete();
        return redirect()->route('renewals.index')->with('success', 'Renewal removed successfully.');
    }
}
