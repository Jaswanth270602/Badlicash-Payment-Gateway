<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('federal_vpa_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('merchant_id')->nullable()->constrained('merchants')->onDelete('set null');
            $table->string('reference_id')->unique();
            $table->enum('payment_status', ['pending', 'success', 'failed', 'processing'])->default('pending');
            $table->enum('response_received', ['Yes', 'No'])->default('No');
            $table->text('response_data')->nullable();
            $table->timestamps();
            
            $table->index('merchant_id');
            $table->index('payment_status');
            $table->index('reference_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('federal_vpa_payments');
    }
};
