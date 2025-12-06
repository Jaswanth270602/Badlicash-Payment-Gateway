<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BaseRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'rate_type',
        'entity_type',
        'entity_id',
        'payment_method',
        'service_type',
        'transaction_type',
        'percentage_fee',
        'flat_fee',
        'gst_percentage',
        'is_active',
        'effective_from',
        'effective_to',
        'notes',
    ];

    protected $casts = [
        'percentage_fee' => 'decimal:3',
        'flat_fee' => 'decimal:2',
        'gst_percentage' => 'decimal:2',
        'is_active' => 'boolean',
        'effective_from' => 'date',
        'effective_to' => 'date',
    ];

    /**
     * Get the merchant for this rate (if applicable).
     */
    public function merchant()
    {
        if ($this->entity_type === 'merchant') {
            return $this->belongsTo(Merchant::class, 'entity_id');
        }
        return null;
    }

    /**
     * Get the bank for this rate (if applicable).
     */
    public function bank()
    {
        return $this->belongsTo(Bank::class, 'entity_id');
    }

    /**
     * Scope to get rates by type.
     */
    public function scopeByType($query, string $type)
    {
        return $query->where('rate_type', $type);
    }

    /**
     * Scope to get active rates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function($q) {
                $q->whereNull('effective_from')
                  ->orWhere('effective_from', '<=', now());
            })
            ->where(function($q) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', now());
            });
    }
}
