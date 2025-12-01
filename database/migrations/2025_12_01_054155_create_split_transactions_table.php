<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('split_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('transaction_id')->constrained('transactions')->onDelete('cascade');
            $table->foreignId('merchant_id')->constrained('merchants')->onDelete('cascade');
            $table->string('order_id')->nullable();
            $table->decimal('amount_paid_by_customer', 15, 2);
            $table->string('account_holder_name')->nullable();
            $table->string('account_number')->nullable();
            $table->enum('split_type', ['percentage', 'fixed'])->default('percentage');
            $table->decimal('split_amount', 15, 2);
            $table->decimal('split_percentage', 5, 2)->nullable();
            $table->string('msac_code')->nullable();
            $table->string('account')->nullable();
            $table->date('transaction_date');
            $table->timestamps();
            
            $table->index('transaction_id');
            $table->index('merchant_id');
            $table->index('transaction_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('split_transactions');
    }
};
