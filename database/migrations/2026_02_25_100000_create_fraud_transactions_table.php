<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Stores one row per transaction evaluated by the FDS (explainability audit trail).
     * No PII or card data – only risk score, decision, and rule references.
     */
    public function up(): void
    {
        Schema::create('fraud_transactions', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id', 191)->index()->comment('Reference to order/transaction (e.g. order_id or txn_id)');
            $table->unsignedBigInteger('merchant_id')->nullable()->index();
            $table->decimal('risk_score', 10, 2)->default(0);
            $table->string('decision', 20)->comment('allow | review | block');
            $table->json('triggered_rules')->nullable()->comment('Array of {rule_name, weight, reason, metadata}');
            $table->unsignedInteger('execution_time_ms')->nullable();
            $table->timestamps();

            $table->foreign('merchant_id')->references('id')->on('merchants')->onDelete('set null');
            $table->index(['merchant_id', 'decision']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_transactions');
    }
};
