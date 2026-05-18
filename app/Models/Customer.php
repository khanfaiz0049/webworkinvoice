<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'company_name', 'phone', 'whatsapp', 'email', 'gst_number', 'pan_number',
        'gst_type', 'billing_address', 'shipping_address', 'state', 'country', 'notes',
        'opening_balance', 'credit_limit', 'tags', 'status'
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
}
