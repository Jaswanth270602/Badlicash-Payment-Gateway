<?php

namespace App\Http\Controllers;

use App\Traits\LogsConditionally;
use App\Services\PaymentService;
use App\Services\PaymentSimulationService;
use Illuminate\Http\Request;
use App\Models\PaymentLink;
use App\Models\Order;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PaymentCheckoutController extends Controller
{
    use LogsConditionally;

    protected PaymentService $paymentService;
    protected PaymentSimulationService $simulationService;

    public function __construct(PaymentService $paymentService, PaymentSimulationService $simulationService)
    {
        $this->paymentService = $paymentService;
        $this->simulationService = $simulationService;
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
                'expires_at' => $paymentLink->expires_at ? $paymentLink->expires_at->toDateTimeString() : null,
                'allow_partial_payment' => $paymentLink->allow_partial_payment,
                'amount' => $paymentLink->amount,
                'amount_paid' => $paymentLink->amount_paid ?? 0,
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
            
            if (!$paymentLink->isActive()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This payment link is no longer available.',
                ], 410);
            }

            // Check if fully paid (only block if not allowing partial payments)
            if ($paymentLink->status === 'paid' || $paymentLink->isFullyPaid()) {
                return response()->json([
                    'success' => false,
                    'message' => 'This payment link has already been fully paid.',
                ], 400);
            }

            // Validate request
            $validator = Validator::make($request->all(), [
                'payment_method' => 'required|in:card,upi,netbanking,wallet',
                'customer_details' => 'required|array',
                'customer_details.name' => 'required|string|max:255',
                'customer_details.email' => 'required|email',
                'customer_details.phone' => 'required|string|regex:/^[0-9]{10}$/',
                'payment_details' => 'required|array',
                'amount' => 'nullable|numeric|min:0.01', // Optional custom amount for partial payment
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Validate payment method details
            $paymentMethod = $request->payment_method;
            $paymentDetails = $request->payment_details;

            if ($paymentMethod === 'card') {
                $cardValidator = Validator::make($paymentDetails, [
                    'card_number' => 'required|string',
                    'card_holder' => 'required|string|max:255',
                    'expiry_month' => 'required|digits:2',
                    'expiry_year' => 'required|digits:4',
                    'cvv' => 'required|digits:3',
                ]);

                if ($cardValidator->fails()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid card details',
                        'errors' => $cardValidator->errors(),
                    ], 422);
                }
            }

            // Determine payment amount
            $paymentAmount = $paymentLink->amount; // Default to full amount
            
            // If partial payment is allowed and custom amount is provided
            if ($paymentLink->allow_partial_payment && $request->has('amount') && $request->amount > 0) {
                $customAmount = (float) $request->amount;
                $remainingBalance = $paymentLink->getRemainingBalance();
                
                // Validate custom amount doesn't exceed remaining balance
                if ($customAmount > $remainingBalance) {
                    return response()->json([
                        'success' => false,
                        'message' => "Payment amount cannot exceed remaining balance of " . number_format($remainingBalance, 2) . " " . $paymentLink->currency,
                    ], 422);
                }
                
                // Validate minimum amount (at least 0.01)
                if ($customAmount < 0.01) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment amount must be at least 0.01',
                    ], 422);
                }
                
                $paymentAmount = $customAmount;
            } elseif (!$paymentLink->allow_partial_payment) {
                // If partial payment not allowed, must pay full remaining balance
                $remainingBalance = $paymentLink->getRemainingBalance();
                if ($remainingBalance < $paymentLink->amount) {
                    // Link was partially paid but partial payments are not allowed anymore
                    return response()->json([
                        'success' => false,
                        'message' => 'This payment link does not allow partial payments. Please pay the full amount.',
                    ], 422);
                }
            }

            // Prepare payment data
            $paymentData = [
                'merchant_id' => $paymentLink->merchant_id,
                'payment_link_id' => $paymentLink->id,
                'amount' => $paymentAmount,
                'currency' => $paymentLink->currency,
                'payment_method' => $paymentMethod,
                'payment_details' => $paymentDetails,
                'customer_details' => $request->customer_details,
                'test_mode' => $paymentLink->test_mode,
                'description' => $paymentLink->title . ($paymentLink->allow_partial_payment ? ' (Partial Payment)' : ''),
            ];

            // Process payment through simulation service
            try {
                $result = $this->simulationService->processPayment($paymentData);
                
                $this->logInfo('Payment processed', [
                    'success' => $result['success'],
                    'order_id' => $result['order_id'] ?? null,
                    'transaction_id' => $result['transaction_id'] ?? null,
                    'status' => $result['status'] ?? 'unknown'
                ]);

                // Update payment link with partial payment info if successful
                if ($result['success']) {
                    // Refresh payment link to get latest status
                    $paymentLink->refresh();
                    
                    // Add payment link info to response (for both partial and full payments)
                    $result['payment_link'] = [
                        'amount_paid' => $paymentLink->amount_paid ?? 0,
                        'remaining_balance' => $paymentLink->getRemainingBalance(),
                        'is_fully_paid' => $paymentLink->isFullyPaid(),
                        'is_partially_paid' => $paymentLink->isPartiallyPaid(),
                    ];
                }

                // Add redirect URLs - use full request URL with port
                $baseUrl = $request->getSchemeAndHttpHost();
                $port = $request->getPort();
                
                // Ensure port is included in URL
                if ($port && $port != 80 && $port != 443) {
                    // Check if port is already in URL
                    if (strpos($baseUrl, ':') === false || (strpos($baseUrl, ':80') !== false && $port != 80) || (strpos($baseUrl, ':443') !== false && $port != 443)) {
                        // Remove existing port if wrong, then add correct one
                        $baseUrl = preg_replace('/:\d+$/', '', $baseUrl);
                        $baseUrl .= ':' . $port;
                    }
                }
                
                // Fallback to config app.url if baseUrl is invalid
                if (!$baseUrl || $baseUrl === 'http://' || $baseUrl === 'https://') {
                    $baseUrl = config('app.url', 'http://127.0.0.1:8000');
                }
                
                if ($result['success']) {
                    $result['redirect_url'] = rtrim($baseUrl, '/') . '/success-simple.html?transaction_id=' . ($result['transaction_id'] ?? '');
                } else {
                    $result['redirect_url'] = rtrim($baseUrl, '/') . '/failure-simple.html?transaction_id=' . ($result['transaction_id'] ?? '');
                }

                return response()->json($result, $result['success'] ? 200 : 402);
                
            } catch (\Exception $serviceError) {
                $this->logError('Payment simulation service error', [
                    'token' => $token,
                    'error' => $serviceError->getMessage(),
                    'file' => $serviceError->getFile(),
                    'line' => $serviceError->getLine(),
                    'trace' => $serviceError->getTraceAsString()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Payment processing failed. Please try again.',
                    'error' => config('app.debug') ? $serviceError->getMessage() : null,
                ], 500);
            }

        } catch (\Exception $e) {
            $this->logError('Payment processing error', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'An error occurred. Please try again.',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
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

 