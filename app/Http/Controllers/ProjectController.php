<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Customer;
use App\Models\Company;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    /* ── Index ──────────────────────────────────────── */

    public function index(Request $request)
    {
        $query = Project::with(['customer', 'company'])
            ->orderBy('renewal_date', 'asc');

        // Optional status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Optional search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($c) => $c->where('name', 'like', "%{$search}%"));
            });
        }

        $projects = $query->get();

        return view('projects.index', compact('projects'));
    }

    /* ── Create ─────────────────────────────────────── */

    public function create()
    {
        $customers = Customer::orderBy('name')->get();
        $activeCompanyId = $this->resolveActiveCompanyId();

        return view('projects.create', compact('customers', 'activeCompanyId'));
    }

    /* ── Store ──────────────────────────────────────── */

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'customer_id'    => 'required|exists:customers,id',
            'services'       => 'required|array|min:1',
            'services.*'     => 'string',
            'amount'         => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'renewal_period' => 'required|string|in:none,1_month,3_months,6_months,yearly',
            'status'         => 'required|string|in:open,closed',
            'notes'          => 'nullable|string|max:1000',
        ]);

        $validated['company_id']   = $this->resolveActiveCompanyId();
        $validated['renewal_date'] = $this->calculateRenewalDate(
            $validated['start_date'],
            $validated['renewal_period']
        );

        Project::create($validated);

        return redirect()->route('projects.index')->with('success', 'Project created successfully.');
    }

    /* ── Edit ───────────────────────────────────────── */

    public function edit(Project $project)
    {
        $customers = Customer::orderBy('name')->get();

        return view('projects.edit', compact('project', 'customers'));
    }

    /* ── Update ─────────────────────────────────────── */

    public function update(Request $request, Project $project)
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'customer_id'    => 'required|exists:customers,id',
            'services'       => 'required|array|min:1',
            'services.*'     => 'string',
            'amount'         => 'required|numeric|min:0',
            'start_date'     => 'required|date',
            'renewal_date'   => 'nullable|date',
            'renewal_period' => 'required|string|in:none,1_month,3_months,6_months,yearly',
            'status'         => 'required|string|in:open,closed',
            'notes'          => 'nullable|string|max:1000',
        ]);

        if ($validated['renewal_period'] === 'none') {
            $validated['renewal_date'] = null;
        }

        $project->update($validated);

        return redirect()->route('projects.index')->with('success', 'Project updated successfully.');
    }

    /* ── Delete ─────────────────────────────────────── */

    public function destroy(Project $project)
    {
        $project->delete();

        return redirect()->route('projects.index')->with('success', 'Project deleted successfully.');
    }

    /* ── Helpers ────────────────────────────────────── */

    private function resolveActiveCompanyId(): int
    {
        return session('active_company_id')
            ?: optional(auth()->user())->active_company_id
            ?: optional(Company::first())->id;
    }

    private function calculateRenewalDate(string $startDate, string $period): ?string
    {
        if ($period === 'none') {
            return null;
        }

        $date = Carbon::parse($startDate);

        return match ($period) {
            '1_month'  => $date->addMonth()->toDateString(),
            '3_months' => $date->addMonths(3)->toDateString(),
            '6_months' => $date->addMonths(6)->toDateString(),
            'yearly'   => $date->addYear()->toDateString(),
            default    => $date->addYear()->toDateString(),
        };
    }
}
