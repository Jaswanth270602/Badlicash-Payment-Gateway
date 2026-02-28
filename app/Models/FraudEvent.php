<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * One row per triggered fraud rule – explainability audit.
 */
class FraudEvent extends Model
{
    protected $table = 'fraud_events';

    protected $fillable = [
        'fraud_transaction_id',
        'rule_name',
        'weight',
        'reason',
        'metadata',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function fraudTransaction(): BelongsTo
    {
        return $this->belongsTo(FraudTransaction::class);
    }
}
