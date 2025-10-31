<?php

namespace App\Listeners;

use App\Events\PaymentCreated;
use App\Events\PaymentSuccess;
use App\Events\PaymentFailed;
use App\Jobs\DeliverWebhookJob;
use App\Models\WebhookEvent;

class SendPaymentWebhook
{
    /**
     * Handle the event.
     */
    public function handle(PaymentCreated|PaymentSuccess|PaymentFailed $event): void
    {
        if ($event instanceof PaymentCreated) {
            $merchant = $event->order->merchant;
            $eventType = 'payment.created';
            $payload = [
                'order_id' => $event->order->order_id,
                'amount' => $event->order->amount,
                'currency' => $event->order->currency,
                'status' => $event->order->status,
                'created_at' => $event->order->created_at->toIso8601String(),
            ];
        } elseif ($event instanceof PaymentSuccess) {
            $merchant = $event->transaction->merchant;
            $eventType = 'payment.success';
            $payload = [
                'order_id' => $event->transaction->order->order_id,
                'transaction_id' => $event->transaction->txn_id,
                'amount' => $event->transaction->amount,
                'currency' => $event->transaction->currency,
                'payment_method' => $event->transaction->payment_method,
                'status' => $event->transaction->status,
                'captured_at' => $event->transaction->captured_at?->toIso8601String(),
            ];
        } else { // PaymentFailed
            $merchant = $event->transaction->merchant;
            $eventType = 'payment.failed';
            $payload = [
                'order_id' => $event->transaction->order->order_id,
                'transaction_id' => $event->transaction->txn_id,
                'amount' => $event->transaction->amount,
                'currency' => $event->transaction->currency,
                'status' => $event->transaction->status,
                'error' => $event->transaction->gateway_response['message'] ?? 'Payment failed',
            ];
        }

        if ($merchant->webhook_url) {
            $webhookEvent = WebhookEvent::create([
                'merchant_id' => $merchant->id,
                'event_type' => $eventType,
                'payload' => $payload,
                'webhook_url' => $merchant->webhook_url,
                'delivered' => false,
                'attempt_count' => 0,
                'max_attempts' => config('badlicash.webhook.max_retry_attempts', 5),
                'next_retry_at' => now(),
            ]);

            // Dispatch webhook delivery job
            DeliverWebhookJob::dispatch($webhookEvent);
        }
    }
}

