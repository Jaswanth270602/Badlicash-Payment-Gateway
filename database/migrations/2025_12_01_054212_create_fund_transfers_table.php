<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fund_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->constrained('merchants')->onDelete('cascade');
            $table->string('reference_id')->unique();
            $table->string('transfer_reference_id')->nullable();
            $table->enum('transfer_qualifier', ['MERCHANT LEDGER', 'SETTLEMENT', 'REFUND'])->default('MERCHANT LEDGER');
            $table->string('purpose_of_payment')->nullable();
            $table->string('transfer_reference_no')->nullable();
            $table->string('transfer_mode')->default('SFTI ADJ');
            $table->date('transfer_date');
            $table->decimal('transfer_amount', 15, 2);
            $table->decimal('credited_amount', 15, 2)->default(0);
            $table->decimal('debited_amount', 15, 2)->default(0);
            $table->string('to_account')->nullable();
            $table->string('bank_name_ca')->nullable();
            $table->enum('fund_received', ['Yes', 'No'])->default('No');
            $table->enum('fund_received_with_commission', ['Yes', 'No'])->default('No');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->index('merchant_id');
            $table->index('transfer_date');
            $table->index('fund_received');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fund_transfers');
    }
};
