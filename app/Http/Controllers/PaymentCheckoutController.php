<?php

namespace App\Http\Controllers;

use App\Traits\LogsConditionally;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use App\Models\PaymentLink;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class PaymentCheckoutController extends Controller
{
    use LogsConditionally;

    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function show(string $token)
    {
        try {
            $paymentLink = PaymentLink::where('link_token', $token)->firstOrFail();
            
            // Refresh the model to get latest status
            $paymentLink->refresh();
            
            // Check if link is active - abort if not
            if (!$paymentLink->isActive()) {
                $this->logInfo('Payment link accessed but not active', [
                    'link_token' => $token,
                    'status' => $paymentLink->status,
                    'expires_at' => $paymentLink->expires_at ? $paymentLink->expires_at->toDateTimeString() : null,
                    'now' => now()->toDateTimeString()
                ]);
                
                $message = 'This payment link is no longer available.';
                if ($paymentLink->status === 'expired') {
                    $message = 'This payment link has expired.';
                } elseif ($paymentLink->status === 'paid') {
                    $message = 'This payment link has already been paid.';
                } elseif ($paymentLink->status === 'cancelled') {
                    $message = 'This payment link has been cancelled.';
                }
                
                abort(404, $message);
            }

            $this->logInfo('Payment checkout page accessed', [
                'link_token' => $token,
                'merchant_id' => $paymentLink->merchant_id,
                'status' => $paymentLink->status,
                'expires_at' => $paymentLink->expires_at ? $paymentLink->expires_at->toDateTimeString() : null
            ]);

            return view('checkout.payment', compact('paymentLink'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            $this->logError('Payment link not found', [
                'token' => $token
            ]);
            abort(404, 'Payment link not found');
        } catch (\Exception $e) {
            $this->logError('Error loading payment checkout', [
                'token' => $token,
                'error' => $e->getMessage()
            ]);
            abort(404, 'Payment link not found');
        }
    }

    public function process(Request $request, string $token)
    {
        try {
            $paymentLink = PaymentLink::where('link_token', $token)->firstOrFail();
            
            // Refresh to get latest status
            $paymentLink->refresh();

            // SECURITY: Validate link is active
            if (!$paymentLink->isActive()) {
                $this->logWarning('Payment attempt on inactive link', [
                    'link_token' => $token,
                    'status' => $paymentLink->status
                ]);
                return redirect()->route('payment.checkout', $token)
                    ->with('error', 'This payment link is no longer active.');
            }

            // SECURITY: Validate and enforce amount from database (cannot be changed in URL)
            $requestAmount = $request->input('amount');
            if ($requestAmount && abs($requestAmount - $paymentLink->amount) > 0.01) {
                $this->logError('Amount tampering detected', [
                    'link_token' => $token,
                    'expected_amount' => $paymentLink->amount,
                    'received_amount' => $requestAmount,
                    'ip' => $request->ip()
                ]);
                return redirect()->route('payment.checkout', $token)
                    ->with('error', 'Invalid payment amount. Please try again.');
            }

            // Get allowed payment methods
            $allowedMethods = $paymentLink->payment_methods ?? ['card', 'upi', 'netbanking', 'wallet'];
            
            // Validate request
            $validated = $request->validate([
                'payment_method' => ['required', 'string', 'in:' . implode(',', $allowedMethods)],
                'customer_name' => 'required|string|max:255',
                'customer_email' => 'required|email|max:255',
                'customer_phone' => 'required|string|max:20',
            ]);

            // SECURITY: Ensure payment method is in allowed list
            if (!in_array($validated['payment_method'], $allowedMethods)) {
                $this->logWarning('Invalid payment method attempted', [
                    'link_token' => $token,
                    'attempted_method' => $validated['payment_method'],
                    'allowed_methods' => $allowedMethods
                ]);
                return redirect()->route('payment.checkout', $token)
                    ->with('error', 'Selected payment method is not available for this link.');
            }

            $this->logInfo('Processing payment from link', [
                'link_token' => $token,
                'merchant_id' => $paymentLink->merchant_id,
                'amount' => $paymentLink->amount,
                'payment_method' => $validated['payment_method']
            ]);

            // Process payment in transaction
            return DB::transaction(function () use ($paymentLink, $validated, $request) {
                // Create order
                $order = Order::create([
                    'merchant_id' => $paymentLink->merchant_id,
                    'order_id' => Order::generateOrderId(),
                    'amount' => $paymentLink->amount, // Always use amount from database
                    'currency' => $paymentLink->currency,
                    'customer_details' => [
                        'name' => $validated['customer_name'],
                        'email' => $validated['customer_email'],
                        'phone' => $validated['customer_phone'],
                    ],
                    'status' => 'created',
                    'description' => $paymentLink->title,
                    'test_mode' => $paymentLink->test_mode,
                ]);

                // Process payment through PaymentService
                $transaction = $this->paymentService->processPayment($order, [
                    'payment_method' => $validated['payment_method'],
                    'payment_details' => [
                        'customer_name' => $validated['customer_name'],
                        'customer_email' => $validated['customer_email'],
                        'customer_phone' => $validated['customer_phone'],
                    ],
                ]);

                // Update payment link
                if ($transaction->status === 'success') {
                    $paymentLink->update([
                        'status' => 'paid',
                        'paid_at' => now(),
                        'usage_count' => $paymentLink->usage_count + 1,
                        'customer_details' => [
                            'name' => $validated['customer_name'],
                            'email' => $validated['customer_email'],
                            'phone' => $validated['customer_phone'],
                        ],
                    ]);

                    $this->logInfo('Payment successful from link', [
                        'link_token' => $token,
                        'transaction_id' => $transaction->txn_id,
                        'order_id' => $order->order_id
                    ]);

                    return redirect()->route('payment.success', $token);
                } else {
                    $this->logWarning('Payment failed from link', [
                        'link_token' => $token,
                        'transaction_id' => $transaction->txn_id,
                        'status' => $transaction->status
                    ]);

                    return redirect()->route('payment.failed', $token)
                        ->with('error', $transaction->gateway_response['message'] ?? 'Payment failed. Please try again.');
                }
            });

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logError('Payment validation failed', [
                'token' => $token,
                'errors' => $e->errors()
            ]);
            return redirect()->route('payment.checkout', $token)
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            $this->logError('Payment processing error', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return redirect()->route('payment.checkout', $token)
                ->with('error', 'An error occurred while processing your payment. Please try again.');
        }
    }

    public function success(string $token)
    {
        $paymentLink = PaymentLink::where('link_token', $token)->firstOrFail();
        return view('checkout.success', compact('paymentLink'));
    }

    public function failed(string $token)
    {
        $paymentLink = PaymentLink::where('link_token', $token)->firstOrFail();
        return view('checkout.failed', compact('paymentLink'));
    }
}

 