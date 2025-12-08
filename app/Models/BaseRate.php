<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

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
     * Rate types
     */
    const RATE_TYPE_MERCHANT = 'merchant';
    const RATE_TYPE_BANK = 'bank';
    const RATE_TYPE_RECEIVER = 'receiver';
    const RATE_TYPE_PRICER = 'pricer';

    /**
     * Payment methods
     */
    const PAYMENT_METHOD_CARD = 'card';
    const PAYMENT_METHOD_UPI = 'upi';
    const PAYMENT_METHOD_NETBANKING = 'netbanking';
    const PAYMENT_METHOD_WALLET = 'wallet';

    /**
     * Service types
     */
    const SERVICE_TYPE_PAYMENT = 'payment';
    const SERVICE_TYPE_REFUND = 'refund';
    const SERVICE_TYPE_CHARGEBACK = 'chargeback';

    /**
     * Transaction types
     */
    const TRANSACTION_TYPE_DOMESTIC = 'domestic';
    const TRANSACTION_TYPE_INTERNATIONAL = 'international';

    /**
     * Get the merchant that owns this rate (if rate_type is merchant).
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class, 'entity_id')
            ->where('entity_type', 'merchant');
    }

    /**
     * Get the bank that owns this rate (if rate_type is bank).
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'entity_id')
            ->where('entity_type', 'bank');
    }

    /**
     * Get the entity (polymorphic relationship).
     */
    public function entity(): MorphTo
    {
        return $this->morphTo('entity', 'entity_type', 'entity_id');
    }

    /**
     * Scope to get active rates.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('effective_from')
                  ->orWhere('effective_from', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('effective_to')
                  ->orWhere('effective_to', '>=', now());
            });
    }

    /**
     * Scope to filter by rate type.
     */
    public function scopeOfType($query, string $rateType)
    {
        return $query->where('rate_type', $rateType);
    }

    /**
     * Scope to filter by payment method.
     */
    public function scopeForPaymentMethod($query, string $paymentMethod)
    {
        return $query->where('payment_method', $paymentMethod);
    }

    /**
     * Scope to filter by service type.
     */
    public function scopeForServiceType($query, string $serviceType)
    {
        return $query->where('service_type', $serviceType);
    }

    /**
     * Scope to filter by transaction type.
     */
    public function scopeForTransactionType($query, string $transactionType)
    {
        return $query->where('transaction_type', $transactionType);
    }

    /**
     * Check if rate is currently effective.
     */
    public function isEffective(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        $now = now();

        if ($this->effective_from && $now->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_to && $now->gt($this->effective_to)) {
            return false;
        }

        return true;
    }

    /**
     * Calculate fee for a given amount.
     */
    public function calculateFee(float $amount): float
    {
        $percentageFee = ($amount * $this->percentage_fee) / 100;
        $totalFee = $percentageFee + $this->flat_fee;
        return round($totalFee, 2);
    }

    /**
     * Calculate GST on fee.
     */
    public function calculateGST(float $feeAmount): float
    {
        $gst = ($feeAmount * $this->gst_percentage) / 100;
        return round($gst, 2);
    }
}
