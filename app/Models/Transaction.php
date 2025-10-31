<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'merchant_id',
        'txn_id',
        'payment_method',
        'amount',
        'fee_amount',
        'net_amount',
        'currency',
        'status',
        'gateway_response',
        'payment_details',
        'gateway_txn_id',
        'bank_id',
        'test_mode',
        'idempotency_key',
        'ip_address',
        'user_agent',
        'authorized_at',
        'captured_at',
    ];

    protected $casts = [
        'gateway_response' => 'array',
        'payment_details' => 'array',
        'test_mode' => 'boolean',
        'amount' => 'decimal:2',
        'fee_amount' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'authorized_at' => 'datetime',
        'captured_at' => 'datetime',
    ];

    /**
     * Get the order that owns the transaction.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the merchant that owns the transaction.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Get the bank for this transaction.
     */
    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    /**
     * Get refunds for this transaction.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Generate a unique transaction ID.
     */
    public static function generateTxnId(): string
    {
        return 'TXN_' . strtoupper(Str::random(20));
    }

    /**
     * Calculate fee amount based on merchant settings.
     */
    public function calculateFee(): float
    {
        if ($this->merchant) {
            return $this->merchant->calculateFee($this->amount);
        }

        $percentageFee = ($this->amount * config('badlicash.fee.percentage', 2.5)) / 100;
        return round($percentageFee + config('badlicash.fee.flat', 0.30), 2);
    }

    /**
     * Calculate net amount after fee.
     */
    public function calculateNetAmount(): float
    {
        return round($this->amount - $this->fee_amount, 2);
    }

    /**
     * Check if transaction is successful.
     */
    public function isSuccessful(): bool
    {
        return $this->status === 'success';
    }

    /**
     * Get total refunded amount.
     */
    public function totalRefunded(): float
    {
        return $this->refunds()
            ->where('status', 'completed')
            ->sum('amount');
    }

    /**
     * Get refundable amount.
     */
    public function refundableAmount(): float
    {
        return $this->amount - $this->totalRefunded();
    }
}

