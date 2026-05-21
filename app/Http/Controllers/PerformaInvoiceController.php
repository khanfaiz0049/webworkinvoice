<?php

namespace App\Http\Controllers;

use App\Models\PerformaInvoice;
use App\Models\PerformaInvoiceItem;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Company;
use App\Models\HsnMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;

class PerformaInvoiceController extends Controller
{
    private const DEFAULT_HSN_SAC = '9983';

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        
        $query = PerformaInvoice::with(['customer', 'company'])
            ->latest();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', function($cQ) use ($search) {
                      $cQ->where('name', 'like', "%{$search}%")
                         ->orWhere('company_name', 'like', "%{$search}%");
                  })
                  ->orWhereHas('company', function($coQ) use ($search) {
                      $coQ->where('name', 'like', "%{$search}%");
                  })
                  ->orWhere('status', 'like', "%{$search}%");
            });
        }

        if ($perPage === 'all') {
            $invoices = $query->get();
        } else {
            $perPage = max(1, (int)$perPage);
            $invoices = $query->paginate($perPage)->withQueryString();
        }

        // Compute next invoice number for the active company
        $activeCompany = $this->resolveActiveCompany();
        $nextInvoiceNumber = $activeCompany ? $this->resolveNextInvNumber($activeCompany) : null;

        if ($request->ajax()) {
            return view('performa_invoices.partials.table', compact('invoices', 'nextInvoiceNumber'))->render();
        }

        return view('performa_invoices.index', compact('invoices', 'perPage', 'nextInvoiceNumber'));
    }

    public function show(PerformaInvoice $performaInvoice)
    {
        $invoice = $performaInvoice;
        $invoice->load(['customer', 'company', 'items']);
        return view('performa_invoices.show', compact('invoice'));
    }

    public function download(PerformaInvoice $performaInvoice)
    {
        $invoice = $performaInvoice;
        $invoice->load(['customer', 'company', 'items']);
        $pdf = Pdf::loadView('performa_invoices.pdf', compact('invoice'));
        $safeFilename = 'performa-invoice-' . str_replace(['/', '\\'], '-', $invoice->invoice_number) . '.pdf';
        return $pdf->download($safeFilename);
    }

    public function create()
    {
        $customers = Customer::all();
        $companies = Company::all();
        $hsnMasters = HsnMaster::orderBy('service_name')->get();
        $hsnOptions = $hsnMasters->map(fn ($hsn) => [
            'id' => (string) $hsn->id,
            'service_name' => $hsn->service_name,
            'hsn_code' => (string) $hsn->hsn_code,
        ])->values()->all();

        $hasDefault = false;
        foreach ($hsnOptions as $option) {
            if ($option['hsn_code'] === self::DEFAULT_HSN_SAC) {
                $hasDefault = true;
                break;
            }
        }

        if (!$hasDefault) {
            array_unshift($hsnOptions, [
                'id' => 'default',
                'service_name' => 'Default (Technical Services)',
                'hsn_code' => self::DEFAULT_HSN_SAC,
            ]);
        }

        $activeCompany = $this->resolveActiveCompany();
        $nextInvoiceNumber = $activeCompany ? $this->resolveNextInvoiceNumber($activeCompany) : '';

        return view('performa_invoices.create', compact('customers', 'companies', 'activeCompany', 'hsnMasters', 'hsnOptions', 'nextInvoiceNumber'));
    }

    public function store(Request $request)
    {
        $activeCompany = $this->resolveActiveCompany();
        $activeCompanyId = optional($activeCompany)->id;

        if (! $activeCompanyId) {
            return back()->withInput()->with('error', 'Please select a billing company before creating a proforma invoice.');
        }

        $nextInvoiceNumber = $this->resolveNextInvoiceNumber($activeCompany);
        $request->merge([
            'invoice_number' => $nextInvoiceNumber,
        ]);

        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('performa_invoices', 'invoice_number')->where('company_id', $activeCompanyId),
            ],
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
                $hsn_sac    = $gstEnabled ? (!empty($item['hsn_sac']) ? $item['hsn_sac'] : self::DEFAULT_HSN_SAC) : null;

                $itemSubtotal = $qty * $rate;
                $itemGst      = round(($itemSubtotal * $gstPercent) / 100, 2);
                $itemTotal    = $itemSubtotal + $itemGst;

                $cgst = $sgst = $igst = 0;
                if ($gstType === 'inter_state') {
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

            $invoice = PerformaInvoice::create([
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
                $itemData['performa_invoice_id'] = $invoice->id;
                PerformaInvoiceItem::create($itemData);
            }

            // Increment the company's starting performa invoice number
            $company = Company::find($activeCompanyId);
            if ($company) {
                $company->update([
                    'performa_invoice_starting_number' => ((int) $nextInvoiceNumber) + 1,
                ]);
            }

            DB::commit();
            return redirect()->route('performa-invoices.index')->with('success', 'Performa Invoice generated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error generating proforma invoice: ' . $e->getMessage());
        }
    }

    public function edit(PerformaInvoice $performaInvoice)
    {
        $invoice = $performaInvoice;
        $invoice->load(['items', 'customer']);
        $customers = Customer::all();
        $hsnMasters = HsnMaster::orderBy('service_name')->get();
        $hsnOptions = $hsnMasters->map(fn ($hsn) => [
            'id' => (string) $hsn->id,
            'service_name' => $hsn->service_name,
            'hsn_code' => (string) $hsn->hsn_code,
        ])->values()->all();

        $hasDefault = false;
        foreach ($hsnOptions as $option) {
            if ($option['hsn_code'] === self::DEFAULT_HSN_SAC) {
                $hasDefault = true;
                break;
            }
        }

        if (!$hasDefault) {
            array_unshift($hsnOptions, [
                'id' => 'default',
                'service_name' => 'Default (Technical Services)',
                'hsn_code' => self::DEFAULT_HSN_SAC,
            ]);
        }

        // Add legacy codes
        foreach ($invoice->items as $item) {
            if ($item->hsn_sac) {
                $hasHsn = false;
                foreach ($hsnOptions as $option) {
                    if ($option['hsn_code'] === (string) $item->hsn_sac) {
                        $hasHsn = true;
                        break;
                    }
                }
                if (!$hasHsn) {
                    $hsnOptions[] = [
                        'id' => 'legacy-' . $item->hsn_sac,
                        'service_name' => 'Current selection',
                        'hsn_code' => (string) $item->hsn_sac,
                    ];
                }
            }
        }

        $activeCompany = $invoice->company;
        
        // Map items to Alpine structure
        $items = $invoice->items->map(function($item) use ($hsnMasters) {
            $matchedHsn = $hsnMasters->firstWhere('hsn_code', $item->hsn_sac);

            return [
                'description' => $item->description,
                'amount' => $item->rate,
                'gst' => $item->gst_percentage,
                'hsn' => $item->hsn_sac ?: self::DEFAULT_HSN_SAC,
                'service_id' => $matchedHsn?->id ? (string) $matchedHsn->id : '',
            ];
        });

        return view('performa_invoices.edit', compact('invoice', 'customers', 'activeCompany', 'items', 'hsnMasters', 'hsnOptions'));
    }

    public function update(Request $request, PerformaInvoice $performaInvoice)
    {
        $invoice = $performaInvoice;
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('performa_invoices', 'invoice_number')
                    ->where('company_id', $invoice->company_id)
                    ->ignore($invoice->id),
            ],
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
                $hsn_sac    = $gstEnabled ? (!empty($item['hsn_sac']) ? $item['hsn_sac'] : self::DEFAULT_HSN_SAC) : null;

                $itemSubtotal = $qty * $rate;
                $itemGst      = round(($itemSubtotal * $gstPercent) / 100, 2);
                $itemTotal    = $itemSubtotal + $itemGst;

                $cgst = $sgst = $igst = 0;
                if ($gstType === 'inter_state') {
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
            return redirect()->route('performa-invoices.index')->with('success', 'Performa Invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating proforma invoice: ' . $e->getMessage());
        }
    }

    public function destroy(PerformaInvoice $performaInvoice)
    {
        $invoice = $performaInvoice;
        try {
            DB::beginTransaction();

            $company = $invoice->company()->withTrashed()->lockForUpdate()->first();

            // Store the invoice number before permanently deleting the invoice itself.
            $deletedNumber = $invoice->invoice_number;

            // Permanently remove the invoice itself.
            $invoice->forceDelete();

            if ($company && is_numeric($deletedNumber)) {
                $deletedNumVal = (int) $deletedNumber;
                if ($deletedNumVal < $company->performa_invoice_starting_number) {
                    $company->update([
                        'performa_invoice_starting_number' => $deletedNumVal,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('performa-invoices.index')->with('success', 'Performa Invoice undone and deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error undoing proforma invoice: ' . $e->getMessage());
        }
    }

    /**
     * Convert a Proforma Invoice into a real Invoice.
     */
    public function convertToInvoice(PerformaInvoice $performaInvoice)
    {
        $proforma = $performaInvoice;
        $proforma->load(['items', 'customer', 'company']);

        $company = $proforma->company;
        if (! $company) {
            return back()->with('error', 'Company not found for this proforma invoice.');
        }

        try {
            DB::beginTransaction();

            // Get next invoice number for the INVOICE series (not proforma)
            $nextInvNumber = $this->resolveNextInvNumber($company);

            // Create the Invoice
            $invoice = Invoice::create([
                'company_id'         => $company->id,
                'customer_id'        => $proforma->customer_id,
                'invoice_number'     => $nextInvNumber,
                'invoice_date'       => $proforma->invoice_date,
                'renewal_date'       => $proforma->renewal_date,
                'renewal_text'       => $proforma->renewal_text,
                'status'             => 'pending',
                'subtotal'           => $proforma->subtotal,
                'taxable_amount'     => $proforma->taxable_amount,
                'gst_enabled'        => $proforma->gst_enabled,
                'cgst'               => $proforma->cgst,
                'sgst'               => $proforma->sgst,
                'igst'               => $proforma->igst,
                'total_gst'          => $proforma->total_gst,
                'grand_total'        => $proforma->grand_total,
                'outstanding_amount' => $proforma->grand_total,
                'paid_amount'        => 0,
            ]);

            // Copy items
            foreach ($proforma->items as $item) {
                InvoiceItem::create([
                    'invoice_id'     => $invoice->id,
                    'name'           => $item->name,
                    'description'    => $item->description,
                    'hsn_sac'        => $item->hsn_sac,
                    'quantity'       => $item->quantity,
                    'rate'           => $item->rate,
                    'discount'       => $item->discount ?? 0,
                    'gst_percentage' => $item->gst_percentage,
                    'cgst'           => $item->cgst,
                    'sgst'           => $item->sgst,
                    'igst'           => $item->igst,
                    'total'          => $item->total,
                ]);
            }

            // Advance the company invoice counter
            $company->update([
                'invoice_starting_number' => ((int) $nextInvNumber) + 1,
            ]);

            // Mark proforma as converted
            $proforma->update(['status' => 'converted']);

            DB::commit();
            return redirect()->route('invoices.show', $invoice)
                ->with('success', "Proforma #{$proforma->invoice_number} converted to Invoice #{$nextInvNumber} successfully.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error converting proforma to invoice: ' . $e->getMessage());
        }
    }

    private function resolveActiveCompany(): ?Company
    {
        $activeCompanyId = session('active_company_id') ?: optional(auth()->user())->active_company_id;

        if ($activeCompanyId) {
            return Company::find($activeCompanyId);
        }

        return Company::query()->first();
    }

    private function resolveNextInvoiceNumber(Company $company): string
    {
        $candidate = max(1, (int) $company->performa_invoice_starting_number);

        // Fetch all numeric proforma invoice numbers for this company in a single query
        $existingNumbers = PerformaInvoice::withTrashed()
            ->where('company_id', $company->id)
            ->pluck('invoice_number')
            ->filter(fn($num) => is_numeric($num))
            ->map(fn($num) => (int)$num)
            ->toArray();

        // Convert to a hash lookup map for O(1) checks
        $existingMap = array_flip($existingNumbers);

        while (isset($existingMap[$candidate])) {
            $candidate++;
        }

        return (string) $candidate;
    }

    /**
     * Resolve the next available INVOICE (not proforma) number for a company.
     */
    private function resolveNextInvNumber(Company $company): string
    {
        $candidate = max(1, (int) $company->invoice_starting_number);

        // Fetch all numeric invoice numbers for this company in a single query
        $existingNumbers = Invoice::withTrashed()
            ->where('company_id', $company->id)
            ->pluck('invoice_number')
            ->filter(fn($num) => is_numeric($num))
            ->map(fn($num) => (int)$num)
            ->toArray();

        // Convert to a hash lookup map for O(1) checks
        $existingMap = array_flip($existingNumbers);

        while (isset($existingMap[$candidate])) {
            $candidate++;
        }

        return (string) $candidate;
    }
}
