<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with(['customer', 'invoice'])->get();
        return view('payments.index', compact('payments'));
    }

    public function create()
    {
        $customers = Customer::all();
        $invoices = Invoice::where('status', '!=', 'paid')->get();
        return view('payments.create', compact('customers', 'invoices'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_id' => 'nullable|exists:invoices,id',
            'received_in' => 'nullable|string',
            'amount' => 'required|numeric|min:0.01',
            'payment_date' => 'required|date',
            'payment_method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'reference_notes' => 'nullable|string',
        ]);

        Payment::create($validated);

        // Update invoice paid amount if linked
        if ($request->invoice_id) {
            $invoice = Invoice::find($request->invoice_id);
            $newPaidAmount = $invoice->paid_amount + $request->amount;
            $invoice->update([
                'paid_amount' => $newPaidAmount,
                'outstanding_amount' => $invoice->grand_total - $newPaidAmount,
                'status' => ($newPaidAmount >= $invoice->grand_total) ? 'paid' : 'partial'
            ]);
        }

        return redirect()->route('payments.index')->with('success', 'Payment recorded successfully.');
    }

    public function destroy(Payment $payment)
    {
        try {
            DB::beginTransaction();

            if ($payment->invoice_id) {
                $invoice = Invoice::withTrashed()->find($payment->invoice_id);
                if ($invoice) {
                    $newPaidAmount = max(0, $invoice->paid_amount - $payment->amount);
                    $invoice->update([
                        'paid_amount' => $newPaidAmount,
                        'outstanding_amount' => $invoice->grand_total - $newPaidAmount,
                        'status' => ($newPaidAmount >= $invoice->grand_total) 
                            ? 'paid' 
                            : ($newPaidAmount > 0 ? 'partial' : 'pending')
                    ]);
                }
            }

            $payment->delete();

            DB::commit();
            return redirect()->route('payments.index')->with('success', 'Payment undone successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error undoing payment: ' . $e->getMessage());
        }
    }
}
