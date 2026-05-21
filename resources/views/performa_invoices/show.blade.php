<x-app-layout>
    <x-slot name="header">
        Proforma Invoice #{{ $invoice->invoice_number }}
    </x-slot>

    @php
        $company = $invoice->company;
        $customer = $invoice->customer;
        $companyName = $company?->legal_name ?: $company?->name ?: 'Company';
        $accountName = $company?->account_name ?: $companyName;
    @endphp

    <style>
        .pdf-like-view {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 11px;
            color: #1e293b;
            line-height: 1.4;
            background: #ffffff;
        }
        
        .pdf-like-view .invoice-container {
            border: none;
            padding: 0;
        }
        
        /* Header */
        .pdf-like-view .header {
            padding: 10px;
            position: relative;
            border-bottom: none;
        }
        .pdf-like-view .logo {
            max-height: 80px;
            margin-bottom: 15px;
            display: inline-block;
        }
        .pdf-like-view .meta-info {
            text-align: right;
            padding-right: 10px;
            margin-bottom: 15px;
        }
        .pdf-like-view .meta-item {
            margin-bottom: 5px;
            font-weight: bold;
            font-size: 13px;
        }

        /* To/From Section */
        .pdf-like-view .billing-section {
            width: 100%;
            border-bottom: 1px solid #000;
            border-collapse: collapse;
        }
        .pdf-like-view .billing-box {
            width: 50%;
            padding: 10px;
            vertical-align: top;
        }
        .pdf-like-view .billing-box:first-child {
            border-right: 1px solid #000;
        }
        .pdf-like-view .billing-label {
            font-weight: bold;
            display: block;
            margin-bottom: 5px;
            padding-bottom: 2px;
        }
        .pdf-like-view .billing-name {
            font-weight: bold;
            font-size: 12px;
        }
        .pdf-like-view .billing-address {
            margin-top: 5px;
            color: #000;
        }
        .pdf-like-view .gst-row {
            margin-top: 5px;
            font-weight: bold;
        }

        /* Items Table */
        .pdf-like-view .items-table {
            width: 100%;
            border-collapse: collapse;
        }
        .pdf-like-view .items-table th {
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
            padding: 6px;
            background: #f8fafc;
            font-weight: bold;
            text-align: center;
        }
        .pdf-like-view .items-table th:last-child {
            border-right: none;
        }
        .pdf-like-view .items-table td {
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
            padding: 8px;
            vertical-align: top;
        }
        .pdf-like-view .items-table td:last-child {
            border-right: none;
        }
        
        .pdf-like-view .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .pdf-like-view .totals-table td {
            padding: 8px;
            border-bottom: 1px solid #000;
        }
        .pdf-like-view .summary-label {
            width: 80%;
            border-right: 1px solid #000;
            text-align: right;
            font-weight: bold;
        }
        .pdf-like-view .summary-value {
            width: 20%;
            text-align: right;
            font-weight: bold;
        }
        
        /* Words */
        .pdf-like-view .words-section {
            padding: 8px;
            border-bottom: 1px solid #000;
            font-weight: bold;
        }

        /* Bank & Sign Section */
        .pdf-like-view .footer-section {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }
        .pdf-like-view .bank-details {
            width: 60%;
            padding: 10px;
            vertical-align: top;
        }
        .pdf-like-view .qr-code-area {
            width: 20%;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
        }
        .pdf-like-view .sign-area {
            width: 40%;
            padding: 10px;
            text-align: right;
            vertical-align: top;
        }
        .pdf-like-view .bank-title {
            font-weight: bold;
            text-decoration: underline;
            margin-bottom: 5px;
        }
        .pdf-like-view .sign-prop {
            margin-top: 40px;
            font-weight: bold;
        }

        /* Utils */
        .pdf-like-view .text-center { text-align: center; }
        .pdf-like-view .text-right { text-align: right; }
        .pdf-like-view .bold { font-weight: bold; }
        
        /* Screen preview improvements */
        @media screen {
            .pdf-like-view {
                font-size: 13px;
            }
            .pdf-like-view .billing-name {
                font-size: 14px;
            }
            .pdf-like-view .meta-item {
                font-size: 13px;
            }
            .pdf-like-view .totals-table td, .pdf-like-view .words-section, .pdf-like-view .bank-details, .pdf-like-view .sign-area {
                font-size: 13px;
            }
        }

        /* Print styles */
        @media print {
            body, html {
                background: #ffffff !important;
                color: #000000 !important;
                height: auto !important;
                overflow: visible !important;
            }
            /* Hide non-printable layout elements */
            aside,
            header,
            .no-print {
                display: none !important;
            }
            
            /* Reset container and body styles to print correctly */
            .flex.h-screen {
                display: block !important;
                height: auto !important;
                overflow: visible !important;
            }
            
            main {
                padding: 0 !important;
                margin: 0 !important;
                overflow: visible !important;
            }
            
            .max-w-5xl {
                max-width: 100% !important;
                width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
            }
            
            /* Remove shadow, border, and background from the wrapper container */
            .overflow-x-auto {
                overflow: visible !important;
                box-shadow: none !important;
                border: none !important;
                border-radius: 0 !important;
                background: transparent !important;
                width: 100% !important;
                min-width: 100% !important;
            }

            .pdf-like-view {
                font-size: 11px !important;
                width: 100% !important;
                min-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                background: #ffffff !important;
            }

            .pdf-like-view .invoice-container {
                padding: 0 !important;
                margin: 0 !important;
                border: none !important;
            }
        }
    </style>

    <div class="max-w-5xl mx-auto pb-20 px-4">
        <!-- Top Action Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8 no-print">
            <a href="{{ route('performa-invoices.index') }}" class="text-xs font-black uppercase tracking-widest text-slate-400 hover:text-slate-900 transition-colors flex items-center gap-2">
                <i data-lucide="arrow-left" class="w-4 h-4"></i> Back to list
            </a>
            <div class="flex flex-wrap gap-3">
                <a href="{{ route('performa-invoices.edit', $invoice) }}" class="flex items-center gap-2 px-6 py-3 bg-amber-50 rounded-2xl text-xs font-black uppercase tracking-widest text-amber-600 hover:bg-amber-100 transition-all border border-amber-100">
                    <i data-lucide="edit-3" class="w-4 h-4"></i> Edit
                </a>
                <button onclick="window.print()" class="flex items-center gap-2 px-6 py-3 bg-white border border-slate-200 rounded-2xl text-xs font-black uppercase tracking-widest text-slate-600 hover:bg-slate-50 transition-all">
                    <i data-lucide="printer" class="w-4 h-4"></i> Print
                </button>
                <a href="{{ route('performa-invoices.download', $invoice) }}" class="flex items-center gap-2 px-6 py-3 bg-[#d32d27] rounded-2xl text-xs font-black uppercase tracking-widest text-white hover:bg-[#b21f24] transition-all shadow-lg shadow-red-500/30">
                    <i data-lucide="download" class="w-4 h-4"></i> Download PDF
                </a>
            </div>
        </div>

        <!-- Invoice Box (matches PDF layout exactly) -->
        <div class="overflow-x-auto rounded-2xl sm:rounded-[2.5rem] shadow-2xl border border-slate-100 bg-white w-full">
            <div class="pdf-like-view min-w-[768px]">
                <div class="invoice-container p-8 sm:p-10">
                <!-- Header -->
                <div class="header">
                    <div style="text-align: center;">
                        @if($company?->logo && file_exists(public_path('storage/' . $company->logo)))
                            <img src="{{ asset('storage/' . $company->logo) }}" class="logo" alt="Logo">
                        @else
                            <img src="{{ asset('storage/logo.png') }}" class="logo" alt="Logo">
                        @endif
                    </div>
                    
                    <div class="meta-info">
                        <div class="meta-item">Date: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }}</div>
                        <div class="meta-item">Proforma No. {{ $invoice->invoice_number }}</div>
                    </div>
                    
                    <div style="text-align: center; font-weight: bold; font-size: 15px; margin-top: 10px; margin-bottom: 10px;">:: Proforma Invoice ::</div>
                </div>

                <div class="invoice-body" style="border: 1px solid #000; border-bottom: none;">
                <!-- Billing Info -->
                <table class="billing-section" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="billing-box">
                            <span class="billing-label">To,</span>
                            <div class="billing-name">{{ $customer?->company_name ?: $customer?->name ?: 'Customer' }}</div>
                            <div class="billing-address">
                                {!! nl2br(e($customer?->billing_address ?: '-')) !!}
                                @if($customer?->state)
                                    <br>State: {{ $customer->state }}
                                @endif
                            </div>
                            @if($customer?->gst_number)
                                <div class="gst-row">GST Regd. No : {{ $customer->gst_number }}</div>
                            @endif
                        </td>
                        <td class="billing-box">
                            <span class="billing-label">From,</span>
                            <div class="billing-name">{{ $companyName }}</div>
                            <div class="billing-address">
                                {!! nl2br(e($company?->address ?: '-')) !!}
                                @if($company?->state)
                                    <br>State: {{ $company->state }}
                                @endif
                            </div>
                            @if($company?->gst_number)
                                <div class="gst-row">GST Regd. No. : {{ $company->gst_number }}</div>
                            @endif
                        </td>
                    </tr>
                </table>

                <!-- Items Table -->
                <table class="items-table" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th style="width: 8%;">S.No.</th>
                            <th style="width: 72%;">Description</th>
                            <th style="width: 20%;">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoice->items as $index => $item)
                            <tr>
                                <td class="text-center">{{ $index + 1 }}.</td>
                                <td>
                                    <div class="bold">{!! $item->name !!}</div>
                                    @if($item->description && $item->description != $item->name)
                                        <div style="margin-top: 4px; font-size: 11px; color: #64748b;">
                                            {!! $item->description !!}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-right bold">
                                    Rs. {{ number_format($item->rate * $item->quantity, 2) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Totals Area -->
                <table class="totals-table" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="summary-label" style="text-align: right; font-weight: bold; position: relative;">
                            @if($invoice->gst_enabled && $invoice->items->first()?->hsn_sac)
                                <span style="float: left; font-weight: bold; font-size: 11px; color: #000;">HSN Code {{ $invoice->items->first()->hsn_sac }}</span>
                            @endif
                            Total
                        </td>
                        <td class="summary-value">Rs. {{ number_format($invoice->subtotal, 2) }}</td>
                    </tr>
                    
                    @if($invoice->gst_enabled && $invoice->cgst > 0)
                        <tr>
                            <td class="summary-label">CGST (9%)</td>
                            <td class="summary-value">Rs. {{ number_format($invoice->cgst, 2) }}</td>
                        </tr>
                        <tr>
                            <td class="summary-label">SGST (9%)</td>
                            <td class="summary-value">Rs. {{ number_format($invoice->sgst, 2) }}</td>
                        </tr>
                    @elseif($invoice->gst_enabled)
                        <tr>
                            <td class="summary-label">IGST (18%)</td>
                            <td class="summary-value">Rs. {{ number_format($invoice->igst, 2) }}</td>
                        </tr>
                    @endif

                    <tr>
                        <td class="summary-label" style="font-size: 13px;">Grand Total</td>
                        <td class="summary-value" style="font-size: 13px;">Rs. {{ number_format($invoice->grand_total, 2) }}</td>
                    </tr>
                </table>

                <!-- Amount in Words -->
                <div class="words-section">
                    Amount in Words: {{ $invoice->amount_in_words }}
                </div>
                </div>

                <!-- Bank & Sign -->
                <table class="footer-section" cellpadding="0" cellspacing="0">
                    <tr>
                        <td class="bank-details" @if($company?->qr_code) style="width: 40%;" @else style="width: 60%;" @endif>
                            <div class="bank-title">Bank Details:</div>
                            <div>Account Name : {{ $accountName }}</div>
                            <div>Bank : {{ $company?->bank_name ?: '-' }}</div>
                            <div>Account No. : {{ $company?->account_number ?: '-' }}</div>
                            <div>Account Type : {{ $company?->account_type ?: 'Current' }} &nbsp; IFSC : {{ $company?->ifsc_code ?: '-' }}@if($company?->swift_code) &nbsp; SWIFT : {{ $company->swift_code }}@endif</div>
                        </td>
                        @if($company?->qr_code)
                            <td class="qr-code-area" style="width: 20%; text-align: center; vertical-align: middle; padding: 10px;">
                                <img src="{{ asset('storage/' . $company->qr_code) }}" style="max-height: 110px; display: inline-block;" alt="QR Code">
                            </td>
                        @endif
                        <td class="sign-area" style="width: 40%;">
                            @php
                                $signaturePath = $company?->signature ?: 'signature.png';
                                $isCommonStamp = ($signaturePath === 'signature.png');
                            @endphp

                            @if(!$isCommonStamp)
                                <div style="font-weight: bold; margin-bottom: 10px;">For {{ strtoupper($companyName) }}</div>
                            @endif

                            @if($signaturePath)
                                <div style="text-align: right; margin-bottom: 5px;">
                                    <img src="{{ asset('storage/' . $signaturePath) }}" height="{{ $isCommonStamp ? 85 : 80 }}" style="display: inline-block; height: {{ $isCommonStamp ? 85 : 80 }}px !important; width: auto;" alt="Signature">
                                </div>
                            @endif

                            @if(!$isCommonStamp)
                                <div class="sign-prop">Proprietor</div>
                            @endif

                            <div style="margin-top: 15px;">Regards</div>
                            <div style="font-weight: bold; margin-top: 5px;">Abdul Karim Sumra</div>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Page Footer -->
        <div style="margin-top: 15px; text-align: center; font-size: 10px; color: #000; line-height: 1.5; font-weight: bold;">
            @if($company && str_contains(strtolower($companyName), 'web work'))
                office add : 101, 1st floor, Shabnam Apt., Near ITM Institute of Design, Amboli, Andheri West, Mumbai - 400053.<br>
                call us: +91 8655 32 8655 | +91 8898 92 9759 &nbsp;|&nbsp; email: info@webwork.co.in &nbsp;|&nbsp; web: www.webwork.co.in
            @elseif($company)
                office add : {{ str_replace("\n", ', ', $company->address ?: '-') }}@if($company->state), {{ $company->state }}@endif<br>
                call us: {{ $company->phone ?: '-' }} | email: {{ $company->email ?: '-' }} | web: {{ $company->website ?: '-' }}
            @endif
        </div>
    </div>
</x-app-layout>
