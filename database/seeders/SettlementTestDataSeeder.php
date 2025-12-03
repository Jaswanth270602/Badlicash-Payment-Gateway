<?php

namespace Database\Seeders;

use App\Models\Settlement;
use App\Models\Transaction;
use App\Models\Merchant;
use Illuminate\Database\Seeder;

class SettlementTestDataSeeder extends Seeder
{
    /**
     * Create test settlement data for testing settlement functionality.
     */
    public function run(): void
    {
        $this->command->info('🏦 Creating Settlement Test Data...');

        // Get test merchant
        $merchant = Merchant::where('name', 'Test Merchant A')
            ->orWhere('email', 'test@merchant.com')
            ->first();

        if (!$merchant) {
            $this->command->error('Test merchant not found. Run TestDataSeeder first.');
            return;
        }

        // Get successful test transactions
        $successfulTransactions = Transaction::where('merchant_id', $merchant->id)
            ->where('test_mode', true)
            ->where('status', 'success')
            ->limit(10)
            ->get();

        if ($successfulTransactions->isEmpty()) {
            $this->command->warn('No eligible transactions found. Creating some...');
            $this->createTestTransactions($merchant);
            
            $successfulTransactions = Transaction::where('merchant_id', $merchant->id)
                ->where('test_mode', true)
                ->where('status', 'success')
                ->limit(10)
                ->get();
        }

        $this->command->info("Found {$successfulTransactions->count()} eligible transactions");

        // Create Settlement #1: Pending
        $this->createSettlement($merchant, $successfulTransactions->take(5), 'pending');

        // Create Settlement #2: Processing
        $this->createSettlement($merchant, $successfulTransactions->slice(5, 3), 'processing');

        // Create Settlement #3: Completed
        $this->createSettlement($merchant, $successfulTransactions->slice(8, 2), 'completed');

        $this->command->info('');
        $this->command->info('✅ Settlement test data created successfully!');
        $this->command->info('');
        $this->command->info('📊 Created 3 test settlements:');
        $this->command->info('   1. Pending settlement (5 transactions)');
        $this->command->info('   2. Processing settlement (3 transactions)');
        $this->command->info('   3. Completed settlement (2 transactions)');
        $this->command->info('');
        $this->command->info('🧪 How to test:');
        $this->command->info('   1. Login as admin');
        $this->command->info('   2. Switch to TEST MODE');
        $this->command->info('   3. Go to Settlements → Settlement Summary');
        $this->command->info('   4. View pending/processing settlements');
        $this->command->info('   5. Mark settlements as settled');
    }

    private function createSettlement(Merchant $merchant, $transactions, string $status): void
    {
        $totalAmount = $transactions->sum('amount');
        $totalFees = $transactions->sum('fee_amount');
        $netAmount = $transactions->sum('net_amount');
        $count = $transactions->count();

        $settlement = Settlement::create([
            'merchant_id' => $merchant->id,
            'settlement_id' => Settlement::generateSettlementId(),
            'amount' => $totalAmount,
            'fee_amount' => $totalFees,
            'refund_amount' => 0,
            'net_amount' => $netAmount,
            'payout_amount' => $netAmount,
            'currency' => 'INR',
            'transaction_count' => $count,
            'refund_count' => 0,
            'period_start' => $transactions->min('created_at'),
            'period_end' => $transactions->max('created_at'),
            'payment_start_date' => $transactions->min('created_at')->toDateString(),
            'payment_end_date' => $transactions->max('created_at')->toDateString(),
            'settlement_date' => now()->addDays(2)->toDateString(),
            'status' => $status,
            'settlement_status' => $status === 'completed' ? 'settled' : 'pending',
            'bank_details' => [
                'account_name' => $merchant->bank_account_holder_name ?? 'Test Account',
                'account_number' => $merchant->bank_account_number ?? '1234567890',
                'ifsc_code' => $merchant->bank_ifsc_code ?? 'TEST0001234',
                'bank_name' => $merchant->bank_name ?? 'Test Bank',
            ],
            'account_name' => $merchant->bank_account_holder_name ?? 'Test Account',
            'account_number' => $merchant->bank_account_number ?? '1234567890',
            'ifsc_code' => $merchant->bank_ifsc_code ?? 'TEST0001234',
            'bank_name' => $merchant->bank_name ?? 'Test Bank',
            'settlement_description' => "Test settlement - $status",
            'utr_number' => $status === 'completed' ? 'UTR' . rand(100000000, 999999999) : null,
            'processed_at' => $status === 'completed' ? now() : null,
        ]);

        $this->command->info("✓ Created {$status} settlement: {$settlement->settlement_id} ({$count} txns, Net: INR {$netAmount})");
    }

    private function createTestTransactions(Merchant $merchant): void
    {
        $this->command->info('Creating additional test transactions...');
        
        for ($i = 1; $i <= 10; $i++) {
            $order = \App\Models\Order::create([
                'merchant_id' => $merchant->id,
                'order_id' => 'ORD_TEST_' . strtoupper(bin2hex(random_bytes(8))),
                'amount' => rand(100, 1000),
                'currency' => 'INR',
                'status' => 'completed',
                'test_mode' => true,
                'customer_details' => [
                    'name' => 'Test Customer ' . $i,
                    'email' => "customer{$i}@test.com",
                    'phone' => '+91' . rand(6000000000, 9999999999),
                ],
                'created_at' => now()->subDays(rand(1, 7)),
            ]);

            $feeAmount = $order->amount * 0.025;
            $netAmount = $order->amount - $feeAmount;

            Transaction::create([
                'order_id' => $order->id,
                'merchant_id' => $merchant->id,
                'txn_id' => 'TXN_TEST_' . strtoupper(bin2hex(random_bytes(8))),
                'payment_method' => 'upi',
                'amount' => $order->amount,
                'fee_amount' => $feeAmount,
                'net_amount' => $netAmount,
                'currency' => 'INR',
                'status' => 'success',
                'test_mode' => true,
                'gateway_txn_id' => 'GTW_' . bin2hex(random_bytes(8)),
                'captured_at' => now()->subDays(rand(1, 7)),
                'created_at' => $order->created_at,
            ]);
        }

        $this->command->info('✓ Created 10 additional test transactions');
    }
}

