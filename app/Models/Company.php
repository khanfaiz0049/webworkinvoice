<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'legal_name', 'gst_number', 'hsn_code', 'pan_number', 'cin_number', 'address', 'state', 'country',
        'pincode', 'phone', 'email', 'website', 'logo', 'signature', 'bank_name', 'account_name', 'account_number',
        'ifsc_code', 'swift_code', 'branch', 'upi_id', 'qr_code', 'invoice_prefix', 'invoice_starting_number', 'performa_invoice_starting_number', 'terms_conditions',
        'footer_notes', 'is_active', 'is_default'
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function performaInvoices()
    {
        return $this->hasMany(PerformaInvoice::class);
    }

    public function settings()
    {
        return $this->hasMany(CompanySetting::class);
    }

    public function renewals()
    {
        return $this->hasMany(Renewal::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function projects()
    {
        return $this->hasMany(Project::class);
    }
}
