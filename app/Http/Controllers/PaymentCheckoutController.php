<?php

namespace App\Http\Controllers;

use App\Traits\LogsConditionally;
use App\Services\PaymentService;
use App\Services\PaymentSimulationService;
use App\Services\Acquirers\AcquirerResolver;
use App\Services\Acquirers\RazorpayEmbeddedPayment;
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

            // Don't use embedded iframe - use our own payment forms with Razorpay Checkout.js
            // This keeps our UI visible and processes payments through Razorpay API
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

            // Check if this will use Razorpay Checkout.js (which handles card input on frontend)
            // We need to check this BEFORE validation to conditionally validate card details
            $merchant = $paymentLink->merchant;
            $hasAcquirerAccount = $merchant->getActiveAcquirerAccount() !== null;
            $isRazorpayCard = false;
            
            if ($hasAcquirerAccount && $request->payment_method === 'card') {
                $acquirerAccount = $merchant->getActiveAcquirerAccount();
                $isRazorpayCard = stripos($acquirerAccount->acquirer_name, 'razorpay') !== false;
            }

            // Validate request - payment_details can be nullable for Razorpay Checkout.js
            $validator = Validator::make($request->all(), [
                'payment_method' => 'required|in:card,upi,netbanking,wallet',
                'customer_details' => 'required|array',
                'customer_details.name' => 'required|string|max:255',
                'customer_details.email' => 'required|email',
                'customer_details.phone' => 'required|string|regex:/^[0-9]{10}$/',
                'payment_details' => $isRazorpayCard ? 'nullable|array' : 'required|array', // Optional for Razorpay Checkout.js
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
            $paymentDetails = $request->payment_details ?? [];

            // Only validate card details if NOT using Razorpay Checkout.js
            // Razorpay Checkout.js handles card input securely on the frontend
            if ($paymentMethod === 'card' && !$isRazorpayCard && empty($paymentDetails)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Card details are required',
                    'errors' => ['payment_details' => ['Card details must be provided']],
                ], 422);
            }

            if ($paymentMethod === 'card' && !$isRazorpayCard && !empty($paymentDetails)) {
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
            } elseif ($paymentMethod === 'card' && $isRazorpayCard) {
                // For Razorpay Checkout.js, payment_details can be empty
                // Razorpay will collect card details securely on the frontend
                $paymentDetails = [];
            } elseif ($paymentMethod === 'card' && $isRazorpayCard) {
                // For Razorpay Checkout.js, card details are handled by Razorpay
                // Just ensure payment_details is an array (can be empty)
                if (!is_array($paymentDetails)) {
                    $paymentDetails = [];
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

            // Check if merchant has Razorpay acquirer account - use PaymentService if available
            // Note: $merchant and $hasAcquirerAccount already set above during validation
            
            // Process payment through PaymentService (Razorpay) or SimulationService (fallback)
            try {
                if ($hasAcquirerAccount) {
                    // Use PaymentService which will route to Razorpay adapter
                    $acquirerAccount = $merchant->getActiveAcquirerAccount();
                    $this->logInfo('Processing payment through acquirer adapter', [
                        'merchant_id' => $merchant->id,
                        'acquirer_account' => $acquirerAccount->acquirer_name,
                    ]);
                    
                    // Check if this is Razorpay and card payment - use Checkout.js
                    $isRazorpay = stripos($acquirerAccount->acquirer_name, 'razorpay') !== false;
                    $isCardPayment = $paymentMethod === 'card';
                    
                    if ($isRazorpay && $isCardPayment) {
                        // For Razorpay card payments, use Checkout.js on frontend
                        // Create order first
                        $order = $this->paymentService->createOrder($merchant, [
                            'amount' => $paymentAmount,
                            'currency' => $paymentLink->currency,
                            'customer_details' => $request->customer_details,
                            'description' => $paymentLink->title,
                            'metadata' => ['payment_link_id' => $paymentLink->id],
                        ]);
                        
                        // Create Razorpay order using the adapter
                        $resolver = app(\App\Services\Acquirers\AcquirerResolver::class);
                        $adapter = $resolver->resolve($acquirerAccount);
                        
                        $razorpayOrderResult = $adapter->createOrder([
                            'order_id' => $order->order_id,
                            'amount' => $paymentAmount,
                            'currency' => $paymentLink->currency,
                            'customer_details' => $request->customer_details,
                            'description' => $paymentLink->title,
                            'metadata' => ['payment_link_id' => $paymentLink->id],
                        ]);
                        
                        // Save Razorpay order ID to our order
                        if ($razorpayOrderResult['success'] && isset($razorpayOrderResult['gateway_order_id'])) {
                            $order->gateway_order_id = $razorpayOrderResult['gateway_order_id'];
                            $order->save();
                            
                            $this->logInfo('Razorpay order created and saved', [
                                'order_id' => $order->order_id,
                                'gateway_order_id' => $order->gateway_order_id,
                            ]);
                        } else {
                            $this->logError('Failed to create Razorpay order', [
                                'order_id' => $order->order_id,
                                'razorpay_result' => $razorpayOrderResult,
                            ]);
                            throw new \RuntimeException('Failed to create Razorpay order');
                        }
                        
                        // Get Razorpay API key for frontend
                        $razorpayKeyId = $acquirerAccount->additional_key_1 ?? $acquirerAccount->secret_key;
                        
                        // Return Razorpay order details for Checkout.js
                        $result = [
                            'success' => true,
                            'use_razorpay_checkout' => true,
                            'razorpay_key' => $razorpayKeyId,
                            'razorpay_order_id' => $order->gateway_order_id,
                            'amount' => $paymentAmount * 100, // Razorpay expects amount in paise
                            'currency' => $paymentLink->currency,
                            'order_id' => $order->order_id,
                            'customer_details' => $request->customer_details,
                            'payment_link_id' => $paymentLink->id,
                            'message' => 'Please complete payment using Razorpay Checkout',
                        ];
                    } else {
                        // For non-card payments or non-Razorpay, process normally
                        $order = $this->paymentService->createOrder($merchant, [
                            'amount' => $paymentAmount,
                            'currency' => $paymentLink->currency,
                            'customer_details' => $request->customer_details,
                            'description' => $paymentLink->title,
                            'metadata' => ['payment_link_id' => $paymentLink->id],
                        ]);
                        
                        // Process payment with card details
                        $transaction = $this->paymentService->processPayment($order, [
                            'payment_method' => $paymentMethod,
                            'card_number' => $paymentDetails['card_number'] ?? null,
                            'cvv' => $paymentDetails['cvv'] ?? null,
                            'expiry_month' => $paymentDetails['expiry_month'] ?? null,
                            'expiry_year' => $paymentDetails['expiry_year'] ?? null,
                            'card_holder' => $paymentDetails['card_holder'] ?? null,
                        ]);
                        
                        // Update payment link if successful
                        if ($transaction->status === 'success') {
                            if ($paymentLink->allow_partial_payment) {
                                $isFullyPaid = $paymentLink->addPartialPayment($paymentAmount);
                            } else {
                                $paymentLink->markAsPaid();
                            }
                        }
                        
                        // Format result similar to simulation service
                        $result = [
                            'success' => $transaction->status === 'success',
                            'message' => $transaction->status === 'success' ? 'Payment successful' : ($transaction->failure_reason ?? 'Payment failed'),
                            'order_id' => $order->order_id,
                            'transaction_id' => $transaction->txn_id,
                            'status' => $transaction->status,
                            'gateway_txn_id' => $transaction->gateway_txn_id,
                        ];
                    }
                } else {
                    // Fallback to simulation service if no acquirer account
                    $this->logInfo('No acquirer account found, using simulation service', [
                        'merchant_id' => $merchant->id,
                    ]);
                    $result = $this->simulationService->processPayment($paymentData);
                }
                
                $this->logInfo('Payment processed', [
                    'success' => $result['success'],
                    'order_id' => $result['order_id'] ?? null,
                    'transaction_id' => $result['transaction_id'] ?? null,
                    'status' => $result['status'] ?? 'unknown',
                    'gateway_txn_id' => $result['gateway_txn_id'] ?? null,
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

    public function verifyRazorpay(Request $request, string $token)
    {
        try {
            $paymentLink = PaymentLink::where('link_token', $token)->firstOrFail();
            $merchant = $paymentLink->merchant;
            
            // Validate request
            $validator = Validator::make($request->all(), [
                'razorpay_payment_id' => 'required|string',
                'razorpay_order_id' => 'required|string',
                'razorpay_signature' => 'required|string',
                'order_id' => 'required|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid verification data',
                    'errors' => $validator->errors(),
                ], 422);
            }

            // Get the order
            $order = Order::where('order_id', $request->order_id)
                         ->where('merchant_id', $merchant->id)
                         ->first();
                         
            if (!$order) {
                $this->logError('Order not found for verification', [
                    'order_id' => $request->order_id,
                    'merchant_id' => $merchant->id,
                    'razorpay_order_id' => $request->razorpay_order_id,
                ]);
                return response()->json([
                    'success' => false,
                    'message' => 'Order not found',
                ], 404);
            }

            // Get acquirer adapter
            $acquirerAccount = $merchant->getActiveAcquirerAccount();
            if (!$acquirerAccount) {
                return response()->json([
                    'success' => false,
                    'message' => 'No active acquirer account found',
                ], 400);
            }

            $resolver = app(\App\Services\Acquirers\AcquirerResolver::class);
            $adapter = $resolver->resolve($acquirerAccount);

            $this->logInfo('Verifying Razorpay payment', [
                'payment_id' => $request->razorpay_payment_id,
                'order_id' => $request->order_id,
                'razorpay_order_id' => $request->razorpay_order_id,
                'token' => $token,
                'merchant_id' => $merchant->id,
            ]);

            // Verify payment signature
            $verifyResult = $adapter->verifyPayment([
                'razorpay_payment_id' => $request->razorpay_payment_id,
                'razorpay_order_id' => $request->razorpay_order_id,
                'razorpay_signature' => $request->razorpay_signature,
            ], $request->razorpay_signature);

            if ($verifyResult['success']) {
                $this->logInfo('Payment verification successful, creating transaction', [
                    'order_id' => $order->id,
                    'order_order_id' => $order->order_id,
                    'razorpay_payment_id' => $request->razorpay_payment_id,
                    'razorpay_order_id' => $request->razorpay_order_id,
                ]);
                
                // Calculate fees for the transaction
                $baseRateService = app(\App\Services\BaseRateService::class);
                $bank = $merchant->bank ?? null;
                $feeCalculation = $baseRateService->calculateFee(
                    $merchant,
                    $order->amount,
                    'card',
                    $bank,
                    \App\Models\BaseRate::SERVICE_TYPE_PAYMENT,
                    \App\Models\BaseRate::TRANSACTION_TYPE_DOMESTIC
                );

                // Get or create transaction
                // First try to find by order_id and gateway_txn_id matching razorpay_order_id
                $transaction = Transaction::where('order_id', $order->id)
                    ->where(function($query) use ($request) {
                        $query->where('gateway_txn_id', $request->razorpay_order_id)
                              ->orWhereJsonContains('gateway_response->gateway_order_id', $request->razorpay_order_id)
                              ->orWhereJsonContains('gateway_response->order_id', $request->razorpay_order_id);
                    })
                    ->first();
                    
                $this->logInfo('Transaction lookup result', [
                    'found' => $transaction !== null,
                    'transaction_id' => $transaction ? $transaction->id : null,
                ]);

                if (!$transaction) {
                    // Store gateway_order_id in gateway_response JSON
                    $gatewayResponse = array_merge($verifyResult['raw_response'] ?? [], [
                        'gateway_order_id' => $request->razorpay_order_id,
                        'order_id' => $request->razorpay_order_id,
                    ]);
                    
                    $this->logInfo('Creating new transaction', [
                        'order_id' => $order->id,
                        'merchant_id' => $order->merchant_id,
                        'amount' => $order->amount,
                        'fee_amount' => $feeCalculation['fee_amount'],
                        'net_amount' => $order->amount - $feeCalculation['total_fee'],
                    ]);
                    
                    // Create transaction if it doesn't exist
                    $transaction = Transaction::create([
                        'order_id' => $order->id,
                        'merchant_id' => $order->merchant_id,
                        'txn_id' => Transaction::generateTxnId(),
                        'amount' => $order->amount,
                        'fee_amount' => $feeCalculation['fee_amount'],
                        'gst_amount' => $feeCalculation['gst_amount'] ?? 0,
                        'net_amount' => $order->amount - $feeCalculation['total_fee'],
                        'currency' => $order->currency,
                        'payment_method' => 'card',
                        'status' => 'success',
                        'gateway_txn_id' => $request->razorpay_payment_id,
                        'gateway_response' => $gatewayResponse,
                        'test_mode' => $order->test_mode,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'captured_at' => now(),
                    ]);
                    
                    $this->logInfo('Transaction created successfully', [
                        'transaction_id' => $transaction->id,
                        'txn_id' => $transaction->txn_id,
                        'status' => $transaction->status,
                    ]);
                } else {
                    // Update existing transaction
                    // Merge gateway_order_id into gateway_response
                    $currentResponse = $transaction->gateway_response ?? [];
                    $gatewayResponse = array_merge($currentResponse, $verifyResult['raw_response'] ?? [], [
                        'gateway_order_id' => $request->razorpay_order_id,
                        'order_id' => $request->razorpay_order_id,
                    ]);
                    
                    $transaction->update([
                        'status' => 'success',
                        'gateway_txn_id' => $request->razorpay_payment_id,
                        'gateway_response' => $gatewayResponse,
                        'fee_amount' => $feeCalculation['fee_amount'],
                        'gst_amount' => $feeCalculation['gst_amount'] ?? 0,
                        'net_amount' => $order->amount - $feeCalculation['total_fee'],
                        'captured_at' => now(),
                    ]);
                }
                
                // Fire success event
                event(new \App\Events\PaymentSuccess($transaction));

                // Update order
                $order->update(['status' => 'completed']);

                // Update payment link
                $paymentAmount = $transaction->amount;
                if ($paymentLink->allow_partial_payment) {
                    $isFullyPaid = $paymentLink->addPartialPayment($paymentAmount);
                } else {
                    $paymentLink->markAsPaid();
                }

                $paymentLink->refresh();

                // Add redirect URLs
                $baseUrl = $request->getSchemeAndHttpHost();
                $port = $request->getPort();
                
                if ($port && $port != 80 && $port != 443) {
                    if (strpos($baseUrl, ':') === false || (strpos($baseUrl, ':80') !== false && $port != 80) || (strpos($baseUrl, ':443') !== false && $port != 443)) {
                        $baseUrl = preg_replace('/:\d+$/', '', $baseUrl);
                        $baseUrl .= ':' . $port;
                    }
                }
                
                if (!$baseUrl || $baseUrl === 'http://' || $baseUrl === 'https://') {
                    $baseUrl = config('app.url', 'http://127.0.0.1:8000');
                }

                $result = [
                    'success' => true,
                    'message' => 'Payment verified successfully',
                    'order_id' => $order->order_id,
                    'transaction_id' => $transaction->txn_id,
                    'status' => $transaction->status,
                    'gateway_txn_id' => $transaction->gateway_txn_id,
                    'redirect_url' => rtrim($baseUrl, '/') . '/success-simple.html?transaction_id=' . $transaction->txn_id,
                ];

                // Add payment link info if partial payment
                if ($paymentLink->allow_partial_payment) {
                    $result['payment_link'] = [
                        'amount_paid' => $paymentLink->amount_paid ?? 0,
                        'remaining_balance' => $paymentLink->getRemainingBalance(),
                        'is_fully_paid' => $paymentLink->isFullyPaid(),
                        'is_partially_paid' => $paymentLink->isPartiallyPaid(),
                    ];
                }

                return response()->json($result);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $verifyResult['message'] ?? 'Payment verification failed',
                ], 400);
            }

        } catch (\Exception $e) {
            $this->logError('Razorpay verification error', [
                'token' => $token,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Payment verification failed',
                'error' => config('app.debug') ? $e->getMessage() : null,
            ], 500);
        }
    }

    /**
     * Handle callback from embedded Razorpay Payment Page.
     * This is called when payment succeeds or fails in the iframe.
     */
    public function handleEmbeddedCallback(Request $request, string $token)
    {
        try {
            $paymentLink = PaymentLink::where('link_token', $token)->firstOrFail();
            $merchant = $paymentLink->merchant;
            
            // Get callback parameters from Razorpay
            $razorpayPaymentId = $request->get('razorpay_payment_id');
            $razorpayPaymentLinkId = $request->get('razorpay_payment_link_id');
            $razorpayPaymentLinkReferenceId = $request->get('razorpay_payment_link_reference_id');
            $razorpayPaymentLinkStatus = $request->get('razorpay_payment_link_status');
            $razorpaySignature = $request->get('razorpay_signature');
            
            $this->logInfo('Embedded Razorpay callback received', [
                'token' => $token,
                'payment_id' => $razorpayPaymentId,
                'payment_link_id' => $razorpayPaymentLinkId,
                'status' => $razorpayPaymentLinkStatus,
            ]);

            // If payment was successful, verify and process
            if ($razorpayPaymentId && $razorpayPaymentLinkStatus === 'paid') {
                $acquirerAccount = $merchant->getActiveAcquirerAccount();
                if (!$acquirerAccount) {
                    return redirect("/pay/{$token}")->with('error', 'Payment processing error. Please contact support.');
                }

                $resolver = app(AcquirerResolver::class);
                $adapter = $resolver->resolve($acquirerAccount);

                // Fetch payment details from Razorpay
                $razorpayPayment = $adapter->getPaymentStatus($razorpayPaymentId);
                
                if ($razorpayPayment['success']) {
                    // Create or update transaction
                    $order = $this->paymentService->createOrder($merchant, [
                        'amount' => $paymentLink->amount,
                        'currency' => $paymentLink->currency,
                        'customer_details' => [],
                        'description' => $paymentLink->title,
                        'metadata' => ['payment_link_id' => $paymentLink->id],
                    ]);

                    $baseRateService = app(\App\Services\BaseRateService::class);
                    $bank = $merchant->bank ?? null;
                    $feeCalculation = $baseRateService->calculateFee(
                        $merchant,
                        $order->amount,
                        'card',
                        $bank,
                        \App\Models\BaseRate::SERVICE_TYPE_PAYMENT,
                        \App\Models\BaseRate::TRANSACTION_TYPE_DOMESTIC
                    );

                    $transaction = Transaction::create([
                        'order_id' => $order->id,
                        'merchant_id' => $order->merchant_id,
                        'txn_id' => Transaction::generateTxnId(),
                        'amount' => $order->amount,
                        'fee_amount' => $feeCalculation['fee_amount'],
                        'gst_amount' => $feeCalculation['gst_amount'] ?? 0,
                        'net_amount' => $order->amount - $feeCalculation['total_fee'],
                        'currency' => $order->currency,
                        'payment_method' => 'card',
                        'status' => 'success',
                        'gateway_txn_id' => $razorpayPaymentId,
                        'gateway_response' => $razorpayPayment,
                        'test_mode' => $order->test_mode,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->userAgent(),
                        'captured_at' => now(),
                    ]);

                    $order->update(['status' => 'completed']);

                    if ($paymentLink->allow_partial_payment) {
                        $paymentLink->addPartialPayment($transaction->amount);
                    } else {
                        $paymentLink->markAsPaid();
                    }

                    event(new \App\Events\PaymentSuccess($transaction));

                    // Return success page with transaction details
                    return redirect("/payment/success/{$token}")->with('transaction_id', $transaction->txn_id);
                }
            }

            // Payment failed or cancelled
            return redirect("/payment/failed/{$token}")->with('error', 'Payment was not completed.');

        } catch (\Exception $e) {
            $this->logError('Error handling embedded Razorpay callback', [
                'token' => $token,
                'error' => $e->getMessage(),
            ]);

            return redirect("/pay/{$token}")->with('error', 'Payment processing error. Please try again.');
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

 