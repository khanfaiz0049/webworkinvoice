<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use App\Models\Company;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function index()
    {
        $expenses = Expense::with('company')
            ->orderBy('date', 'desc')
            ->get();
            
        return view('expenses.index', compact('expenses'));
    }

    public function create()
    {
        return view('expenses.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
            'date' => 'required|date',
            'payment_method' => 'required|string',
            'notes' => 'nullable|string',
        ]);

        $validated['company_id'] = $this->resolveActiveCompanyId();

        Expense::create($validated);

        return redirect()->route('expenses.index')->with('success', 'Expense recorded successfully.');
    }

    private function resolveActiveCompanyId(): int
    {
        return session('active_company_id') 
            ?: optional(auth()->user())->active_company_id 
            ?: optional(Company::first())->id;
    }

    public function destroy(Expense $expense)
    {
        $expense->delete();
        return redirect()->route('expenses.index')->with('success', 'Expense removed successfully.');
    }
}
