<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * One row per triggered rule – full explainability for each decision.
     */
    public function up(): void
    {
        Schema::create('fraud_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fraud_transaction_id')->constrained('fraud_transactions')->onDelete('cascade');
            $table->string('rule_name', 100)->index();
            $table->decimal('weight', 10, 2);
            $table->string('reason', 500);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['fraud_transaction_id', 'rule_name']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fraud_events');
    }
};
