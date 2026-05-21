<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Proforma Invoice #{{ $invoice->invoice_number }}</title>
    <style>
        @page { margin: 20px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #1e293b; line-height: 1.4; margin: 0; padding: 0; }
        .invoice-container { border: none; padding: 0; }
        
        /* Header */
        .header { padding: 10px; position: relative; border-bottom: none; }
        .logo { max-height: 80px; margin-bottom: 15px; display: inline-block; }
        .meta-info { text-align: right; padding-right: 10px; margin-bottom: 15px; }
        .meta-item { margin-bottom: 5px; font-weight: bold; font-size: 13px; }

        /* To/From Section */
        .billing-section { width: 100%; border-bottom: 1px solid #000; }
        .billing-box { width: 50%; padding: 10px; vertical-align: top; }
        .billing-box:first-child { border-right: 1px solid #000; }
        .billing-label { font-weight: bold; display: block; margin-bottom: 5px; padding-bottom: 2px; }
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
        .totals-table td { padding: 8px; border-bottom: 1px solid #000; }
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
    @php
        $company = $invoice->company;
        $customer = $invoice->customer;
        $companyName = $company?->legal_name ?: $company?->name ?: 'Company';
        $accountName = $company?->account_name ?: $companyName;

        // Resolve QR Code Path
        $qrSrc = '';
        if ($company?->qr_code) {
            if (file_exists(public_path('storage/' . $company->qr_code))) {
                $qrSrc = public_path('storage/' . $company->qr_code);
            } elseif (file_exists(public_path($company->qr_code))) {
                $qrSrc = public_path($company->qr_code);
            } else {
                $qrSrc = asset('storage/' . $company->qr_code);
            }
        }

        // Resolve Signature Path
        $signaturePath = $company?->signature ?: 'signature.png';
        $isCommonStamp = ($signaturePath === 'signature.png');
        $sigSrc = '';
        if ($signaturePath) {
            if (file_exists(public_path('storage/' . $signaturePath))) {
                $sigSrc = public_path('storage/' . $signaturePath);
            } elseif (file_exists(public_path($signaturePath))) {
                $sigSrc = public_path($signaturePath);
            } else {
                $sigSrc = asset('storage/' . $signaturePath);
            }
        }
    @endphp
    <div class="invoice-container">
        <!-- Header -->
        <div class="header">
            <div style="text-align: center;">
                @if($company?->logo && (file_exists(public_path('storage/' . $company->logo)) || file_exists(public_path($company->logo))))
                    @if(file_exists(public_path('storage/' . $company->logo)))
                        <img src="{{ public_path('storage/' . $company->logo) }}" class="logo">
                    @else
                        <img src="{{ public_path($company->logo) }}" class="logo">
                    @endif
                @else
                    @if(file_exists(public_path('storage/logo.png')))
                        <img src="{{ public_path('storage/logo.png') }}" class="logo">
                    @elseif(file_exists(public_path('logo.png')))
                        <img src="{{ public_path('logo.png') }}" class="logo">
                    @else
                        <img src="{{ asset('storage/logo.png') }}" class="logo">
                    @endif
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
                    <div class="billing-address" style="color: #000;">
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
                    <div class="billing-address" style="color: #000;">
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
                    <th width="8%">S.No.</th>
                    <th width="72%">Description</th>
                    <th width="20%">Amount</th>
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
                <td class="summary-label" style="font-size: 12px;">Grand Total</td>
                <td class="summary-value" style="font-size: 12px;">Rs. {{ number_format($invoice->grand_total, 2) }}</td>
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
                    <div>Account Type : {{ $company?->account_type ?: 'Current' }} IFSC : {{ $company?->ifsc_code ?: '-' }}@if($company?->swift_code) SWIFT : {{ $company->swift_code }}@endif</div>
                </td>
                @if($qrSrc)
                    <td class="qr-code-area" style="width: 20%; text-align: center; vertical-align: middle; padding: 10px;">
                        <img src="{{ $qrSrc }}" style="max-height: 110px; display: inline-block;">
                    </td>
                @endif
                <td class="sign-area" style="width: 40%;">
                    @if(!$isCommonStamp)
                        <div style="font-weight: bold; margin-bottom: 10px;">For {{ strtoupper($companyName) }}</div>
                    @endif

                    @if($sigSrc)
                        <div style="text-align: right; margin-bottom: 5px;">
                            <img src="{{ $sigSrc }}" height="{{ $isCommonStamp ? 85 : 80 }}" style="display: inline-block; height: {{ $isCommonStamp ? 85 : 80 }}px; width: auto;">
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

    <!-- Page Footer -->
    <div style="margin-top: 15px; text-align: center; font-size: 9px; color: #000; line-height: 1.5; font-weight: bold;">
        @if($company && str_contains(strtolower($companyName), 'web work'))
            office add : 101, 1st floor, Shabnam Apt., Near ITM Institute of Design, Amboli, Andheri West, Mumbai - 400053.<br>
            call us: +91 8655 32 8655 | +91 8898 92 9759 &nbsp;|&nbsp; email: info@webwork.co.in &nbsp;|&nbsp; web: www.webwork.co.in
        @elseif($company)
            office add : {{ str_replace("\n", ', ', $company->address ?: '-') }}@if($company->state), {{ $company->state }}@endif<br>
            call us: {{ $company->phone ?: '-' }} | email: {{ $company->email ?: '-' }} | web: {{ $company->website ?: '-' }}
        @endif
    </div>
</body>
</html>
