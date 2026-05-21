<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Renewal;
use App\Models\Customer;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Combined data across ALL companies
        $totalRevenue = Invoice::whereIn('status', ['paid', 'partial'])->sum('paid_amount');
        $totalOutstanding = Invoice::sum('outstanding_amount');
        $pendingInvoices = Invoice::where('status', 'pending')->count();
        $overdueInvoices = Invoice::where('invoice_date', '<', now())->where('status', '!=', 'paid')->count();
        $renewalsDue = Renewal::where('due_date', '<=', now()->addDays(30))->count();
        
        // Recent Payments (all companies)
        $recentPayments = Payment::with(['customer', 'invoice.company'])
            ->latest()
            ->limit(5)
            ->get();

        // Monthly Revenue Graph Data (all companies)
        $monthlyRevenueRaw = Invoice::select(
                DB::raw('SUM(paid_amount) as total'), 
                DB::raw("DATE_FORMAT(invoice_date, '%Y-%m') as invoice_year_month"),
                DB::raw("DATE_FORMAT(invoice_date, '%b') as month_name")
            )
            ->groupBy(DB::raw("DATE_FORMAT(invoice_date, '%Y-%m')"), DB::raw("DATE_FORMAT(invoice_date, '%b')"))
            ->orderBy('invoice_year_month')
            ->get();

        $monthlyRevenue = [];
        foreach ($monthlyRevenueRaw as $row) {
            $monthlyRevenue[$row->month_name] = (float) $row->total;
        }

        // Total companies count for the subtitle
        $totalCompanies = Company::count();

        return view('dashboard', compact(
            'totalRevenue', 'totalOutstanding', 'pendingInvoices', 
            'overdueInvoices', 'renewalsDue', 'recentPayments', 'monthlyRevenue',
            'totalCompanies'
        ));
    }
}
