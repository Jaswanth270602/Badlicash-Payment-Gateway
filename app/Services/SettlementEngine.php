<?php

namespace App\Services;

use App\Models\Settlement;
use App\Models\Transaction;
use App\Models\Merchant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SettlementEngine
{
    /**
     * Process daily settlements for all merchants.
     * This runs every day at configured time (default: 11 PM).
     */
    public function processDailySettlements(Carbon $date = null): array
    {
        $date = $date ?? Carbon::yesterday(); // Process previous day's transactions
        $results = [];

        Log::info('Starting daily settlement processing', ['date' => $date->toDateString()]);

        try {
            // Get all merchants
            $merchants = Merchant::where('status', 'active')->get();

            foreach ($merchants as $merchant) {
                $result = $this->processSettlementForMerchant($merchant, $date);
                $results[] = $result;
            }

            Log::info('Daily settlement processing completed', [
                'date' => $date->toDateString(),
                'merchants_processed' => count($results),
                'settlements_created' => collect($results)->where('created', true)->count()
            ]);

            return $results;
        } catch (\Exception $e) {
            Log::error('Error in daily settlement processing', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Process settlement for a specific merchant.
     */
    public function processSettlementForMerchant(Merchant $merchant, Carbon $date): array
    {
        $startOfDay = $date->copy()->startOfDay();
        $endOfDay = $date->copy()->endOfDay();

        // Get all successful transactions that are pending settlement
        $transactions = Transaction::where('merchant_id', $merchant->id)
            ->where('status', 'success')
            ->where('settlement_status', 'pending')
            ->whereBetween('captured_at', [$startOfDay, $endOfDay])
            ->get();

        if ($transactions->isEmpty()) {
            return [
                'merchant_id' => $merchant->id,
                'merchant_name' => $merchant->name,
                'created' => false,
                'message' => 'No transactions to settle'
            ];
        }

        // Calculate settlement amounts
        $calculation = $this->calculateSettlementAmounts($transactions);

        // Create settlement
        $settlement = $this->createSettlement($merchant, $transactions, $calculation, $date);

        // Mark transactions as settled
        $this->markTransactionsAsSettled($transactions, $settlement);

        return [
            'merchant_id' => $merchant->id,
            'merchant_name' => $merchant->name,
            'created' => true,
            'settlement_id' => $settlement->settlement_id,
            'transaction_count' => $transactions->count(),
            'net_amount' => $settlement->net_amount
        ];
    }

    /**
     * Calculate settlement amounts including fees, GST, refunds.
     */
    protected function calculateSettlementAmounts($transactions): array
    {
        $grossAmount = $transactions->sum('amount');
        $feeAmount = $transactions->sum('fee_amount');
        $gstAmount = $transactions->sum('gst_amount');
        $otherFees = $transactions->sum('other_fees');

        // Get refunds for these transactions
        $transactionIds = $transactions->pluck('id');
        $refundAmount = DB::table('refunds')
            ->whereIn('transaction_id', $transactionIds)
            ->where('status', 'completed')
            ->sum('amount');

        $refundCount = DB::table('refunds')
            ->whereIn('transaction_id', $transactionIds)
            ->where('status', 'completed')
            ->count();

        // Net amount = Gross - Fees - GST - Other Fees - Refunds
        $netAmount = $grossAmount - $feeAmount - $gstAmount - $otherFees - $refundAmount;

        return [
            'gross_amount' => $grossAmount,
            'fee_amount' => $feeAmount,
            'gst_amount' => $gstAmount,
            'other_fees' => $otherFees,
            'refund_amount' => $refundAmount,
            'refund_count' => $refundCount,
            'net_amount' => $netAmount,
            'transaction_count' => $transactions->count(),
        ];
    }

    /**
     * Create settlement record.
     */
    protected function createSettlement(Merchant $merchant, $transactions, array $calculation, Carbon $date): Settlement
    {
        $settlementSchedule = $merchant->settlement_schedule ?? 'T+2';
        $daysToAdd = (int) str_replace('T+', '', $settlementSchedule);

        $settlement = Settlement::create([
            'merchant_id' => $merchant->id,
            'settlement_id' => $this->generateSettlementId($merchant, $date),
            'amount' => $calculation['gross_amount'],
            'fee_amount' => $calculation['fee_amount'] + $calculation['gst_amount'] + $calculation['other_fees'],
            'refund_amount' => $calculation['refund_amount'],
            'net_amount' => $calculation['net_amount'],
            'payout_amount' => $calculation['net_amount'],
            'currency' => $transactions->first()->currency ?? 'INR',
            'transaction_count' => $calculation['transaction_count'],
            'refund_count' => $calculation['refund_count'],
            'period_start' => $transactions->min('captured_at'),
            'period_end' => $transactions->max('captured_at'),
            'payment_start_date' => $date->toDateString(),
            'payment_end_date' => $date->toDateString(),
            'settlement_date' => now()->addDays($daysToAdd)->toDateString(),
            'status' => 'pending',
            'settlement_status' => 'pending',
            'bank_details' => [
                'account_name' => $merchant->bank_account_holder_name,
                'account_number' => $merchant->bank_account_number,
                'ifsc_code' => $merchant->bank_ifsc_code,
                'bank_name' => $merchant->bank_name,
                'branch' => $merchant->bank_branch,
            ],
            'account_name' => $merchant->bank_account_holder_name,
            'account_number' => $merchant->bank_account_number,
            'ifsc_code' => $merchant->bank_ifsc_code,
            'bank_name' => $merchant->bank_name,
            'bank_branch' => $merchant->bank_branch,
            'settlement_description' => "Daily settlement for {$date->toDateString()}",
        ]);

        Log::info('Settlement created', [
            'settlement_id' => $settlement->settlement_id,
            'merchant_id' => $merchant->id,
            'net_amount' => $settlement->net_amount
        ]);

        return $settlement;
    }

    /**
     * Mark transactions as settled.
     */
    protected function markTransactionsAsSettled($transactions, Settlement $settlement): void
    {
        Transaction::whereIn('id', $transactions->pluck('id'))
            ->update([
                'settlement_id' => $settlement->id,
                'settlement_status' => 'settled',
                'settled_at' => now(),
            ]);
    }

    /**
     * Generate unique settlement ID.
     */
    protected function generateSettlementId(Merchant $merchant, Carbon $date): string
    {
        return 'STL_' . strtoupper($date->format('Ymd')) . '_M' . $merchant->id . '_' . strtoupper(\Illuminate\Support\Str::random(8));
    }
}

