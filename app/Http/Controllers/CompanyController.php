<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CompanyController extends Controller
{
    public function index()
    {
        $companies = Company::all();
        return view('companies.index', compact('companies'));
    }

    public function create()
    {
        return view('companies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gst_number' => 'nullable|string|max:20',
            'hsn_code' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:255',
            'upi_id' => 'nullable|string|max:255',
            'qr_code' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
            'invoice_starting_number' => 'required|integer|min:0',
            'performa_invoice_starting_number' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('qr_code')) {
            $validated['qr_code'] = $request->file('qr_code')->store('company_qr', 'public');
        }

        if ($request->hasFile('logo')) {
            $validated['logo'] = $request->file('logo')->store('company_logo', 'public');
        }

        $validated['invoice_prefix'] = '';

        $company = Company::create($validated);

        if (Company::count() === 1) {
            session(['active_company_id' => $company->id]);
        }

        return redirect()->route('companies.index')->with('success', 'Company created successfully.');
    }

    public function edit(Company $company)
    {
        return view('companies.edit', compact('company'));
    }

    public function update(Request $request, Company $company)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'gst_number' => 'nullable|string|max:20',
            'hsn_code' => 'nullable|string|max:50',
            'pan_number' => 'nullable|string|max:20',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'state' => 'nullable|string|max:100',
            'pincode' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'bank_name' => 'nullable|string|max:255',
            'account_name' => 'nullable|string|max:255',
            'account_number' => 'nullable|string|max:255',
            'ifsc_code' => 'nullable|string|max:20',
            'swift_code' => 'nullable|string|max:255',
            'upi_id' => 'nullable|string|max:255',
            'qr_code' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:5120',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp,svg|max:5120',
            'invoice_starting_number' => 'required|integer|min:0',
            'performa_invoice_starting_number' => 'required|integer|min:0',
        ]);

        if ($request->hasFile('qr_code')) {
            if ($company->qr_code) {
                Storage::disk('public')->delete($company->qr_code);
            }
            $validated['qr_code'] = $request->file('qr_code')->store('company_qr', 'public');
        }

        if ($request->hasFile('logo')) {
            if ($company->logo && $company->logo !== 'logo.png') {
                Storage::disk('public')->delete($company->logo);
            }
            $validated['logo'] = $request->file('logo')->store('company_logo', 'public');
        }

        $validated['invoice_prefix'] = '';

        $company->update($validated);

        return redirect()->route('companies.index')->with('success', 'Company updated successfully.');
    }

    public function destroy(Company $company)
    {
        if ($company->qr_code) {
            Storage::disk('public')->delete($company->qr_code);
        }
        $company->delete();
        return redirect()->route('companies.index')->with('success', 'Company deleted successfully.');
    }

    public function switch(Request $request)
    {
        $request->validate(['company_id' => 'required|exists:companies,id']);
        session(['active_company_id' => $request->company_id]);
        return back()->with('success', 'Switched to ' . Company::find($request->company_id)->name);
    }
}
