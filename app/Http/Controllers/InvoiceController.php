<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        
        $query = Invoice::with(['customer', 'company'])
            ->latest();

        if ($perPage === 'all') {
            $invoices = $query->get();
        } else {
            $perPage = max(1, (int)$perPage);
            $invoices = $query->paginate($perPage)->withQueryString();
        }

        return view('invoices.index', compact('invoices', 'perPage'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['customer', 'company', 'items']);
        return view('invoices.show', compact('invoice'));
    }

    public function download(Invoice $invoice)
    {
        $invoice->load(['customer', 'company', 'items']);
        $pdf = Pdf::loadView('invoices.pdf', compact('invoice'));
        $safeFilename = 'invoice-' . str_replace(['/', '\\'], '-', $invoice->invoice_number) . '.pdf';
        return $pdf->download($safeFilename);
    }

    public function create()
    {
        $customers = Customer::all();
        $companies = Company::all();
        $activeCompany = $this->resolveActiveCompany();
        return view('invoices.create', compact('customers', 'companies', 'activeCompany'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => 'required|string|max:255',
            'invoice_date' => 'required|date',
            'renewal_date' => 'nullable|date',
            'renewal_text' => 'nullable|array',
            'renewal_text.*' => 'string|max:255',
            'gst_enabled' => 'required|boolean',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|numeric|min:1',
            'items.*.price' => 'required|numeric|min:0',
            'items.*.gst_percentage' => 'nullable|numeric|min:0',
            'items.*.discount' => 'nullable|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $activeCompanyId = optional($this->resolveActiveCompany())->id;
            if (! $activeCompanyId) {
                return back()->withInput()->with('error', 'Please select a billing company before creating an invoice.');
            }

            $customer = Customer::findOrFail($request->customer_id);
            $gstType = $customer->gst_type; // 'Intra State' or 'Inter State'
            $gstEnabled = $request->boolean('gst_enabled');

            $subtotal  = 0;
            $totalGst  = 0;
            $totalCgst = 0;
            $totalSgst = 0;
            $totalIgst = 0;
            $itemsData = [];

            foreach ($request->items as $item) {
                $qty        = 1;
                $rate       = (float) $item['price'];
                $gstPercent = $gstEnabled ? 18 : 0;
                $hsn_sac    = $item['hsn_sac'] ?? null;

                $itemSubtotal = $qty * $rate;
                $itemGst      = round(($itemSubtotal * $gstPercent) / 100, 2);
                $itemTotal    = $itemSubtotal + $itemGst;

                $cgst = $sgst = $igst = 0;
                if ($gstType === 'intra_state') {
                    $igst = $itemGst;
                } else {
                    $cgst = round($itemGst / 2, 2);
                    $sgst = round($itemGst / 2, 2);
                }

                $itemsData[] = [
                    'name'           => $item['description'],
                    'description'    => $item['description'],
                    'hsn_sac'        => $hsn_sac,
                    'quantity'       => $qty,
                    'rate'           => $rate,
                    'discount'       => 0,
                    'gst_percentage' => $gstPercent,
                    'cgst'           => $cgst,
                    'sgst'           => $sgst,
                    'igst'           => $igst,
                    'total'          => $itemTotal,
                ];

                $subtotal  += $itemSubtotal;
                $totalGst  += $itemGst;
                $totalCgst += $cgst;
                $totalSgst += $sgst;
                $totalIgst += $igst;
            }

            $invoice = Invoice::create([
                'company_id'         => $activeCompanyId,
                'customer_id'        => $request->customer_id,
                'invoice_number'     => $request->invoice_number,
                'invoice_date'       => $request->invoice_date,
                'renewal_date'       => $request->renewal_date,
                'renewal_text'       => $request->has('renewal_text') && is_array($request->renewal_text) ? implode(', ', $request->renewal_text) : null,
                'status'             => 'pending',
                'subtotal'           => $subtotal,
                'taxable_amount'     => $subtotal,
                'gst_enabled'        => $gstEnabled,
                'cgst'               => $totalCgst,
                'sgst'               => $totalSgst,
                'igst'               => $totalIgst,
                'total_gst'          => $totalGst,
                'grand_total'        => $subtotal + $totalGst,
                'outstanding_amount' => $subtotal + $totalGst,
            ]);

            foreach ($itemsData as $itemData) {
                $itemData['invoice_id'] = $invoice->id;
                InvoiceItem::create($itemData);
            }

            // Increment the company's starting invoice number
            $company = Company::find($activeCompanyId);
            if ($company) {
                $company->increment('invoice_starting_number');
            }

            DB::commit();
            return redirect()->route('invoices.index')->with('success', 'Invoice generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error generating invoice: ' . $e->getMessage());
        }
    }
    public function edit(Invoice $invoice)
    {
        $invoice->load(['items', 'customer']);
        $customers = Customer::all();
        $activeCompany = $invoice->company;
        
        // Map items to Alpine structure
        $items = $invoice->items->map(function($item) {
            return [
                'description' => $item->description,
                'amount' => $item->rate,
                'gst' => $item->gst_percentage,
                'hsn' => $item->hsn_sac
            ];
        });

        return view('invoices.edit', compact('invoice', 'customers', 'activeCompany', 'items'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => ['required', 'string', 'max:255', Rule::unique('invoices', 'invoice_number')->ignore($invoice->id)],
            'invoice_date' => 'required|date',
            'renewal_date' => 'nullable|date',
            'renewal_text' => 'nullable|array',
            'renewal_text.*' => 'string|max:255',
            'gst_enabled' => 'required|boolean',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $customer = Customer::findOrFail($request->customer_id);
            $gstType  = $customer->gst_type; // 'Intra State' or 'Inter State'
            $gstEnabled = $request->boolean('gst_enabled');

            $subtotal  = 0;
            $totalGst  = 0;
            $totalCgst = 0;
            $totalSgst = 0;
            $totalIgst = 0;

            // Delete old items and recreate
            $invoice->items()->delete();

            foreach ($request->items as $item) {
                $qty        = 1;
                $rate       = (float) $item['price'];
                $gstPercent = $gstEnabled ? 18 : 0;
                $hsn_sac    = $item['hsn_sac'] ?? null;

                $itemSubtotal = $qty * $rate;
                $itemGst      = round(($itemSubtotal * $gstPercent) / 100, 2);
                $itemTotal    = $itemSubtotal + $itemGst;

                $cgst = $sgst = $igst = 0;
                if ($gstType === 'intra_state') {
                    $igst = $itemGst;
                } else {
                    $cgst = round($itemGst / 2, 2);
                    $sgst = round($itemGst / 2, 2);
                }

                $invoice->items()->create([
                    'name'           => $item['description'],
                    'description'    => $item['description'],
                    'hsn_sac'        => $hsn_sac,
                    'quantity'       => $qty,
                    'rate'           => $rate,
                    'gst_percentage' => $gstPercent,
                    'cgst'           => $cgst,
                    'sgst'           => $sgst,
                    'igst'           => $igst,
                    'total'          => $itemTotal,
                ]);

                $subtotal  += $itemSubtotal;
                $totalGst  += $itemGst;
                $totalCgst += $cgst;
                $totalSgst += $sgst;
                $totalIgst += $igst;
            }

            $invoice->update([
                'customer_id'        => $request->customer_id,
                'invoice_number'     => $request->invoice_number,
                'invoice_date'       => $request->invoice_date,
                'renewal_date'       => $request->renewal_date,
                'renewal_text'       => $request->has('renewal_text') && is_array($request->renewal_text) ? implode(', ', $request->renewal_text) : null,
                'subtotal'           => $subtotal,
                'taxable_amount'     => $subtotal,
                'gst_enabled'        => $gstEnabled,
                'cgst'               => $totalCgst,
                'sgst'               => $totalSgst,
                'igst'               => $totalIgst,
                'total_gst'          => $totalGst,
                'grand_total'        => $subtotal + $totalGst,
                'outstanding_amount' => ($subtotal + $totalGst) - $invoice->paid_amount,
            ]);

            DB::commit();
            return redirect()->route('invoices.index')->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating invoice: ' . $e->getMessage());
        }
    }

    public function destroy(Invoice $invoice)
    {
        $invoice->delete();
        return redirect()->route('invoices.index')->with('success', 'Invoice deleted successfully.');
    }

    private function resolveActiveCompany(): ?Company
    {
        $activeCompanyId = session('active_company_id') ?: optional(auth()->user())->active_company_id;

        if ($activeCompanyId) {
            return Company::find($activeCompanyId);
        }

        return Company::query()->first();
    }
}
