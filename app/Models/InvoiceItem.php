<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    protected $fillable = [
        'invoice_id', 'name', 'description', 'hsn_sac', 'quantity', 'unit',
        'rate', 'discount', 'gst_percentage', 'cgst', 'sgst', 'igst', 'total'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
