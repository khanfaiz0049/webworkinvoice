<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'customer_id', 'company_id', 'name', 'services', 'amount',
        'start_date', 'renewal_date', 'renewal_period', 'status', 'notes',
    ];

    protected $casts = [
        'services'     => 'array',
        'amount'       => 'decimal:2',
        'start_date'   => 'date',
        'renewal_date' => 'date',
    ];

    /* ── Relationships ─────────────────────────────── */

    public function customer()
    {
        return $this->belongsTo(Customer::class)->withTrashed();
    }

    public function company()
    {
        return $this->belongsTo(Company::class)->withTrashed();
    }

    /* ── Helpers ───────────────────────────────────── */

    /* ── Services Helper ────────────────────────────── */

    /**
     * Return active service names from the services master table.
     * Falls back to an empty array if the table doesn't exist yet.
     */
    public static function getServicesList(): array
    {
        try {
            return \App\Models\Service::active()->orderBy('name')->pluck('name')->toArray();
        } catch (\Throwable $e) {
            return [];
        }
    }

    public const RENEWAL_PERIODS = [
        'none'     => 'None',
        '1_month'  => '1 Month',
        '3_months' => '3 Months',
        '6_months' => '6 Months',
        'yearly'   => 'Yearly',
    ];

    public function getRenewalPeriodLabelAttribute(): string
    {
        return self::RENEWAL_PERIODS[$this->renewal_period] ?? $this->renewal_period;
    }
}
