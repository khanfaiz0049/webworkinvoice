<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Customer;
use App\Models\Company;
use App\Models\HsnMaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Validation\Rule;

class InvoiceController extends Controller
{
    private const DEFAULT_HSN_SAC = '9983';

    public function index(Request $request)
    {
        $perPage = $request->query('per_page', 10);
        
        $query = Invoice::with(['customer', 'company'])
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

        if ($request->ajax()) {
            return view('invoices.partials.table', compact('invoices'))->render();
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

        return view('invoices.create', compact('customers', 'companies', 'activeCompany', 'hsnMasters', 'hsnOptions', 'nextInvoiceNumber'));
    }

    public function store(Request $request)
    {
        $activeCompany = $this->resolveActiveCompany();
        $activeCompanyId = optional($activeCompany)->id;

        if (! $activeCompanyId) {
            return back()->withInput()->with('error', 'Please select a billing company before creating an invoice.');
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
                Rule::unique('invoices', 'invoice_number')->where('company_id', $activeCompanyId),
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
                $company->update([
                    'invoice_starting_number' => ((int) $nextInvoiceNumber) + 1,
                ]);
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

        return view('invoices.edit', compact('invoice', 'customers', 'activeCompany', 'items', 'hsnMasters', 'hsnOptions'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_number' => [
                'required',
                'string',
                'max:255',
                Rule::unique('invoices', 'invoice_number')
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
            return redirect()->route('invoices.index')->with('success', 'Invoice updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating invoice: ' . $e->getMessage());
        }
    }

    public function destroy(Invoice $invoice)
    {
        try {
            DB::beginTransaction();

            $company = $invoice->company()->withTrashed()->lockForUpdate()->first();

            // Permanently remove dependent payments so the invoice number can be reused.
            $invoice->payments()->forceDelete();

            // Store the invoice number before permanently deleting the invoice itself.
            $deletedNumber = $invoice->invoice_number;

            // Permanently remove the invoice itself.
            $invoice->forceDelete();

            if ($company && is_numeric($deletedNumber)) {
                $deletedNumVal = (int) $deletedNumber;
                if ($deletedNumVal < $company->invoice_starting_number) {
                    $company->update([
                        'invoice_starting_number' => $deletedNumVal,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('invoices.index')->with('success', 'Invoice undone and deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error undoing invoice: ' . $e->getMessage());
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
