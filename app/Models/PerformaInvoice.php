<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class PerformaInvoice extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'performa_invoices';

    protected $fillable = [
        'company_id', 'customer_id', 'invoice_number', 'invoice_date', 'due_date', 'status',
        'notes', 'terms_conditions', 'subtotal', 'discount_amount', 'taxable_amount',
        'cgst', 'sgst', 'igst', 'total_gst', 'grand_total', 'paid_amount', 'outstanding_amount',
        'gst_enabled', 'renewal_date', 'renewal_text'
    ];

    protected $casts = [
        'gst_enabled' => 'boolean',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function items()
    {
        return $this->hasMany(PerformaInvoiceItem::class, 'performa_invoice_id');
    }

    public function getAmountInWordsAttribute()
    {
        $amount = $this->grand_total;
        $number = floor($amount);
        $fraction = round(($amount - $number) * 100);
        
        $no = floor($number);
        $hundred = null;
        $digits_1 = strlen($no);
        $i = 0;
        $str = array();
        $words = array(
            '0' => '', '1' => 'one', '2' => 'two', '3' => 'three', '4' => 'four', '5' => 'five', '6' => 'six', '7' => 'seven', '8' => 'eight', '9' => 'nine',
            '10' => 'ten', '11' => 'eleven', '12' => 'twelve', '13' => 'thirteen', '14' => 'fourteen', '15' => 'fifteen', '16' => 'sixteen', '17' => 'seventeen', '18' => 'eighteen', '19' => 'nineteen',
            '20' => 'twenty', '30' => 'thirty', '40' => 'forty', '50' => 'fifty', '60' => 'sixty', '70' => 'seventy', '80' => 'eighty', '90' => 'ninety'
        );
        $digits = array('', 'hundred', 'thousand', 'lakh', 'crore');
        while ($i < $digits_1) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number] . " " . $digits[$counter] . $plural . " " . $hundred
                    : $words[floor($number / 10) * 10] . " " . $words[$number % 10] . " " . $digits[$counter] . $plural . " " . $hundred;
            } else $str[] = null;
        }
        $str = array_reverse($str);
        $result = implode('', $str);
        $points = ($fraction) ? "and " . (($fraction < 21) ? $words[$fraction] : $words[floor($fraction / 10) * 10] . " " . $words[$fraction % 10]) . " paise" : "";
        return ucwords($result) . "Rupees " . $points . " Only";
    }
}
