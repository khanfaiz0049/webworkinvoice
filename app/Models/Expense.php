<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id', 'name', 'category', 'amount', 
        'date', 'payment_method', 'notes', 'attachment'
    ];

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }
}
