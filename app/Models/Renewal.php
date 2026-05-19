<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Renewal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id', 'company_id', 'invoice_id', 'name', 
        'due_date', 'frequency', 'status', 'last_renewal_date'
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class)->withTrashed();
    }
}
