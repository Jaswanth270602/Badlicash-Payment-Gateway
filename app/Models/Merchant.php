<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Merchant extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'email',
        'company_name',
        'status',
        'default_currency',
        'webhook_url',
        'webhook_secret',
        'test_mode',
        'fee_percentage',
        'fee_flat',
        'business_details',
        'settings',
        // KYC Fields
        'kyc_status',
        'kyc_document_type',
        'kyc_document_number',
        'kyc_document_file',
        // Business Details
        'business_type',
        'tax_id',
        'business_registration_number',
        'business_address',
        'business_city',
        'business_state',
        'business_country',
        'business_postal_code',
        'business_phone',
        'business_website',
        // Bank Account Details
        'bank_account_holder_name',
        'bank_account_number',
        'bank_ifsc_code',
        'bank_name',
        'bank_branch',
        // Card Details (optional for onboarding)
        'card_holder_name',
        'card_number_encrypted',
        'card_expiry_month',
        'card_expiry_year',
        'card_cvv_encrypted',
        // Onboarding
        'onboarding_status',
        'onboarding_completed_at',
        'onboarding_steps',
    ];

    protected $casts = [
        'settings' => 'array',
        'test_mode' => 'boolean',
        'fee_percentage' => 'decimal:2',
        'fee_flat' => 'decimal:2',
        'onboarding_steps' => 'array',
        'onboarding_completed_at' => 'datetime',
    ];

    /**
     * Get users associated with this merchant.
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Get API keys for this merchant.
     */
    public function apiKeys(): HasMany
    {
        return $this->hasMany(ApiKey::class);
    }

    /**
     * Get orders for this merchant.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get transactions for this merchant.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get refunds for this merchant.
     */
    public function refunds(): HasMany
    {
        return $this->hasMany(Refund::class);
    }

    /**
     * Get settlements for this merchant.
     */
    public function settlements(): HasMany
    {
        return $this->hasMany(Settlement::class);
    }

    /**
     * Get payment links for this merchant.
     */
    public function paymentLinks(): HasMany
    {
        return $this->hasMany(PaymentLink::class);
    }

    /**
     * Get webhook events for this merchant.
     */
    public function webhookEvents(): HasMany
    {
        return $this->hasMany(WebhookEvent::class);
    }

    /**
     * Get payouts for this merchant.
     */
    public function payouts(): HasMany
    {
        return $this->hasMany(Payout::class);
    }

    /**
     * Get subscriptions for this merchant.
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    /**
     * Calculate fee for a given amount.
     */
    public function calculateFee(float $amount): float
    {
        $percentageFee = ($amount * $this->fee_percentage) / 100;
        return round($percentageFee + $this->fee_flat, 2);
    }

    /**
     * Check if merchant is active.
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}

