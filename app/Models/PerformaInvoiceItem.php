<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerformaInvoiceItem extends Model
{
    protected $table = 'performa_invoice_items';

    protected $fillable = [
        'performa_invoice_id', 'name', 'description', 'hsn_sac', 'quantity', 'unit',
        'rate', 'discount', 'gst_percentage', 'cgst', 'sgst', 'igst', 'total'
    ];

    public function performaInvoice()
    {
        return $this->belongsTo(PerformaInvoice::class, 'performa_invoice_id')->withTrashed();
    }
}
