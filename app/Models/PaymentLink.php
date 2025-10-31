<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class PaymentLink extends Model
{
    use HasFactory;

    protected $fillable = [
        'merchant_id',
        'link_token',
        'title',
        'description',
        'amount',
        'currency',
        'customer_details',
        'status',
        'usage_count',
        'max_usage',
        'test_mode',
        'metadata',
        'payment_methods',
        'success_url',
        'cancel_url',
        'expires_at',
        'paid_at',
    ];

    protected $casts = [
        'customer_details' => 'array',
        'metadata' => 'array',
        'payment_methods' => 'array',
        'test_mode' => 'boolean',
        'amount' => 'decimal:2',
        'expires_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    /**
     * Get the merchant that owns the payment link.
     */
    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    /**
     * Generate a unique link token.
     */
    public static function generateLinkToken(): string
    {
        return Str::random(32);
    }

    /**
     * Get the full payment URL.
     */
    public function getPaymentUrl(): string
    {
        return url('/pay/' . $this->link_token);
    }

    /**
     * Check if link is active and valid.
     */
    public function isActive(): bool
    {
        if ($this->status !== 'active') {
            return false;
        }

        if ($this->expires_at && $this->expires_at < now()) {
            $this->update(['status' => 'expired']);
            return false;
        }

        if ($this->max_usage && $this->usage_count >= $this->max_usage) {
            $this->update(['status' => 'expired']);
            return false;
        }

        return true;
    }

    /**
     * Mark link as paid.
     */
    public function markAsPaid(): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => now(),
            'usage_count' => $this->usage_count + 1,
        ]);
    }
}

