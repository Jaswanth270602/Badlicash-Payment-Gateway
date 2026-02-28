<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * One row per transaction evaluated by the FDS.
 * No PII or card data stored.
 */
class FraudTransaction extends Model
{
    protected $table = 'fraud_transactions';

    protected $fillable = [
        'transaction_id',
        'merchant_id',
        'risk_score',
        'decision',
        'triggered_rules',
        'execution_time_ms',
    ];

    protected $casts = [
        'risk_score' => 'decimal:2',
        'triggered_rules' => 'array',
    ];

    public function merchant(): BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }

    public function fraudEvents(): HasMany
    {
        return $this->hasMany(FraudEvent::class, 'fraud_transaction_id');
    }
}
