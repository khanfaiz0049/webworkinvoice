<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 20px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 0; padding: 0; }
        .invoice-container { border: 1px solid #000; padding: 0; }
        
        /* Header */
        .header { text-align: center; padding: 10px; position: relative; border-bottom: 1px solid #000; }
        .logo { max-height: 50px; margin-bottom: 5px; }
        .meta-info { position: absolute; right: 10px; top: 10px; text-align: right; }
        .meta-item { margin-bottom: 2px; }
        .meta-label { font-weight: bold; }

        /* To/From Section */
        .billing-section { width: 100%; border-bottom: 1px solid #000; }
        .billing-box { width: 50%; padding: 10px; vertical-align: top; }
        .billing-box:first-child { border-right: 1px solid #000; }
        .billing-label { font-weight: bold; border-bottom: 1px solid #000; display: block; margin-bottom: 5px; padding-bottom: 2px; }
        .billing-name { font-weight: bold; font-size: 12px; }
        .billing-address { margin-top: 5px; color: #475569; }
        .gst-row { margin-top: 5px; font-weight: bold; }

        /* Items Table */
        .items-table { width: 100%; border-collapse: collapse; }
        .items-table th { border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 6px; background: #f8fafc; font-weight: bold; text-align: center; }
        .items-table th:last-child { border-right: none; }
        .items-table td { border-bottom: 1px solid #000; border-right: 1px solid #000; padding: 8px; vertical-align: top; }
        .items-table td:last-child { border-right: none; }
        
        /* Totals Area */
        .totals-table { width: 100%; border-collapse: collapse; }
        .totals-table td { padding: 4px 8px; border-bottom: 1px solid #000; }
        .summary-label { width: 80%; border-right: 1px solid #000; text-align: right; font-weight: bold; }
        .summary-value { width: 20%; text-align: right; font-weight: bold; }
        
        /* Words */
        .words-section { padding: 8px; border-bottom: 1px solid #000; font-weight: bold; }

        /* Bank & Sign Section */
        .footer-section { width: 100%; table-layout: fixed; }
        .bank-details { width: 60%; padding: 10px; vertical-align: top; }
        .qr-code-area { width: 20%; padding: 10px; text-align: center; vertical-align: middle; }
        .sign-area { width: 40%; padding: 10px; text-align: right; vertical-align: top; }
        .bank-title { font-weight: bold; text-decoration: underline; margin-bottom: 5px; }
        .sign-prop { margin-top: 40px; font-weight: bold; }

        /* Utils */
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .bold { font-weight: bold; }
    </style>
</head>
<body>
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            @if($invoice->company->logo)
                @if(file_exists(public_path('storage/' . $invoice->company->logo)))
                    <img src="{{ public_path('storage/' . $invoice->company->logo) }}" class="logo">
                @elseif(file_exists(public_path($invoice->company->logo)))
                    <img src="{{ public_path($invoice->company->logo) }}" class="logo">
                @else
                    <img src="{{ asset('storage/' . $invoice->company->logo) }}" class="logo">
                @endif
            @else
                <div style="font-size: 24px; font-weight: 900; color: #0055a4;">{{ $invoice->company->name }}</div>
            @endif
            
            <div class="meta-info">
                <div class="meta-item"><span class="meta-label">Date:</span> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d-m-Y') }}</div>
                <div class="meta-item"><span class="meta-label">Invoice No.</span> {{ $invoice->invoice_number }}</div>
            </div>
            
            <div style="margin-top: 10px; font-weight: bold; font-size: 14px;">:: {{ $invoice->gst_enabled ? 'Tax Invoice' : 'Invoice' }} ::</div>
        </div>

        <!-- Billing Info -->
        <table class="billing-section" cellpadding="0" cellspacing="0">
            <tr>
                <td class="billing-box">
                    <span class="billing-label">To,</span>
                    <div class="billing-name">{{ $invoice->customer->company_name ?: $invoice->customer->name }}</div>
                    <div class="billing-address" style="color: #000;">
                        {!! nl2br(e($invoice->customer->billing_address)) !!}
                    </div>
                    @if($invoice->customer->gst_number)
                        <div class="gst-row">GST Regd. No : {{ $invoice->customer->gst_number }}</div>
                    @endif
                </td>
                <td class="billing-box">
                    <span class="billing-label">From,</span>
                    <div class="billing-name">{{ $invoice->company->name }},</div>
                    <div class="billing-address" style="color: #000;">
                        {!! nl2br(e($invoice->company->address)) !!}
                    </div>
                    @if($invoice->company->gst_number)
                        <div class="gst-row">GST Regd. No. : {{ $invoice->company->gst_number }}</div>
                    @endif
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="items-table" cellpadding="0" cellspacing="0">
            <thead>
                <tr>
                    <th width="8%">S.No.</th>
                    <th width="72%">Description</th>
                    <th width="20%">Amount (INR)</th>
                </tr>
            </thead>
            <tbody>
                @foreach($invoice->items as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}.</td>
                        <td>
                            <div class="bold">{!! $item->name !!}</div>
                            @if($item->description && $item->description != $item->name)
                                <div style="margin-top: 4px; font-size: 10px; color: #64748b;">
                                    {!! $item->description !!}
                                </div>
                            @endif
                        </td>
                        <td class="text-right bold">
                            {{ number_format($item->rate * $item->quantity, 2) }} INR
                        </td>
                    </tr>
                @endforeach
                
                @for($i = $invoice->items->count(); $i < 3; $i++)
                    <tr>
                        <td style="height: 35px;">&nbsp;</td>
                        <td>&nbsp;</td>
                        <td>&nbsp;</td>
                    </tr>
                @endfor
            </tbody>
        </table>

        <!-- Totals Area -->
        <table class="totals-table" cellpadding="0" cellspacing="0">
            <tr>
                <td class="summary-label" style="text-align: right; font-weight: bold; position: relative;">
                    @if($invoice->items->first()?->hsn_sac)
                        <span style="float: left; font-weight: bold; font-size: 11px; color: #000;">HSN Code {{ $invoice->items->first()->hsn_sac }}</span>
                    @endif
                    Total
                </td>
                <td class="summary-value">{{ number_format($invoice->subtotal, 2) }} INR</td>
            </tr>
            
            @if($invoice->gst_enabled && $invoice->cgst > 0)
                <tr>
                    <td class="summary-label">CGST (9%)</td>
                    <td class="summary-value">{{ number_format($invoice->cgst, 2) }} INR</td>
                </tr>
                <tr>
                    <td class="summary-label">SGST (9%)</td>
                    <td class="summary-value">{{ number_format($invoice->sgst, 2) }} INR</td>
                </tr>
            @elseif($invoice->gst_enabled)
                <tr>
                    <td class="summary-label">IGST (18%)</td>
                    <td class="summary-value">{{ number_format($invoice->igst, 2) }} INR</td>
                </tr>
            @endif

            <tr>
                <td class="summary-label" style="font-size: 12px;">Grand Total</td>
                <td class="summary-value" style="font-size: 12px;">{{ number_format($invoice->grand_total, 2) }} INR</td>
            </tr>
        </table>

        <!-- Amount in Words -->
        <div class="words-section">
            Amount in Words: {{ $invoice->amount_in_words }}
        </div>

        <!-- Bank & Sign -->
        <table class="footer-section" cellpadding="0" cellspacing="0">
            <tr>
                <td class="bank-details" style="width: {{ $invoice->company->qr_code ? '40%' : '60%' }};">
                    <div class="bank-title">Bank Details:</div>
                    <div>Account Name : {{ $invoice->company->account_name ?: $invoice->company->name }}</div>
                    <div>Bank : {{ $invoice->company->bank_name }}</div>
                    <div>Account No. : {{ $invoice->company->account_number }}</div>
                    <div>Account Type : {{ $invoice->company->account_type ?: 'Current' }} IFSC : {{ $invoice->company->ifsc_code }}@if($invoice->company->swift_code) SWIFT : {{ $invoice->company->swift_code }}@endif</div>
                </td>
                @if($invoice->company->qr_code)
                    <td class="qr-code-area" style="width: 20%; text-align: center; vertical-align: middle; padding: 10px;">
                        @if(file_exists(public_path('storage/' . $invoice->company->qr_code)))
                            <img src="{{ public_path('storage/' . $invoice->company->qr_code) }}" style="max-height: 110px; display: inline-block;">
                        @elseif(file_exists(public_path($invoice->company->qr_code)))
                            <img src="{{ public_path($invoice->company->qr_code) }}" style="max-height: 110px; display: inline-block;">
                        @else
                            <img src="{{ asset('storage/' . $invoice->company->qr_code) }}" style="max-height: 110px; display: inline-block;">
                        @endif
                    </td>
                @endif
                <td class="sign-area" style="width: 40%;">
                    <div style="font-weight: bold; margin-bottom: 10px;">For {{ strtoupper($invoice->company->name) }}</div>
                    @if($invoice->company->signature)
                        @if(file_exists(public_path('storage/' . $invoice->company->signature)))
                            <img src="{{ public_path('storage/' . $invoice->company->signature) }}" style="max-height: 40px; margin-bottom: 5px;">
                        @elseif(file_exists(public_path($invoice->company->signature)))
                            <img src="{{ public_path($invoice->company->signature) }}" style="max-height: 40px; margin-bottom: 5px;">
                        @else
                            <img src="{{ asset('storage/' . $invoice->company->signature) }}" style="max-height: 40px; margin-bottom: 5px;">
                        @endif
                    @endif
                    <div class="sign-prop">Proprietor</div>
                    <div style="margin-top: 10px;">Regards,</div>
                    <div style="font-weight: bold;">{{ $invoice->company->contact_person ?: 'Authorized Signatory' }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- Page Footer -->
    <div style="margin-top: 15px; text-align: center; font-size: 9px; color: #000; line-height: 1.5; font-weight: bold;">
        @if(str_contains(strtolower($invoice->company->name), 'web work'))
            office add : 101, 1st floor, Shabnam Apt., Near ITM Institute of Design, Amboli, Andheri West, Mumbai - 400053.<br>
            call us: +91 8655 32 8655 | +91 8898 92 9759 &nbsp;|&nbsp; email: info@webwork.co.in &nbsp;|&nbsp; web: www.webwork.co.in
        @else
            office add : {{ str_replace("\n", ", ", $invoice->company->address) }}<br>
            call us: {{ $invoice->company->phone }} | email: {{ $invoice->company->email }} | web: {{ $invoice->company->website }}
        @endif
    </div>
</body>
</html>
