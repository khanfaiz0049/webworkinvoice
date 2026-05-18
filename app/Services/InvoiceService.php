<?php

namespace App\Services;

use App\Repositories\InvoiceRepository;

class InvoiceService
{
    protected $invoiceRepository;

    public function __construct(InvoiceRepository $invoiceRepository)
    {
        $this->invoiceRepository = $invoiceRepository;
    }

    public function calculateGst($rate, $quantity, $gstPercentage)
    {
        $taxableAmount = $rate * $quantity;
        $totalGst = ($taxableAmount * $gstPercentage) / 100;
        
        // Simple logic for CGST/SGST/IGST (to be refined based on state)
        return [
            'taxable_amount' => $taxableAmount,
            'total_gst' => $totalGst,
            'cgst' => $totalGst / 2,
            'sgst' => $totalGst / 2,
            'igst' => 0, // Default to 0 for intra-state
            'grand_total' => $taxableAmount + $totalGst
        ];
    }
}
