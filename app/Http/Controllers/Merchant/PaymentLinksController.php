<?php

namespace App\Http\Controllers\Merchant;

use App\Http\Controllers\Controller;
use App\Traits\LogsConditionally;
use App\Models\PaymentLink;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class PaymentLinksController extends Controller
{
    use LogsConditionally;

    /**
     * Display payment links page.
     */
    public function index(): View
    {
        $this->logInfo('Payment links page accessed', ['user_id' => auth()->id()]);
        return view('merchant.paymentlinks.index');
    }

    /**
     * Get payment links data for Angular.
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $merchant = $request->user()->merchant;
            
            if (!$merchant) {
                $this->logError('Merchant not found for user', ['user_id' => auth()->id()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found',
                ], 404);
            }

            $this->logInfo('Payment links data requested', [
                'merchant_id' => $merchant->id,
                'filters' => $request->only(['status', 'search', 'per_page'])
            ]);

            $perPage = min((int)$request->get('per_page', 10), 50);
            $status = $request->get('status');
            $search = $request->get('search');

            $query = $merchant->paymentLinks()->latest();

            if ($status && $status !== 'all' && $status !== '') {
                $query->where('status', $status);
            }

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('link_token', 'like', "%{$search}%");
                });
            }

            $paymentLinks = $query->paginate($perPage);

            $this->logDebug('Payment links retrieved', [
                'count' => $paymentLinks->count(),
                'total' => $paymentLinks->total()
            ]);

            return response()->json([
                'success' => true,
                'data' => $paymentLinks->items(),
                'pagination' => [
                    'current_page' => $paymentLinks->currentPage(),
                    'per_page' => $paymentLinks->perPage(),
                    'total' => $paymentLinks->total(),
                    'last_page' => $paymentLinks->lastPage(),
                    'from' => $paymentLinks->firstItem(),
                    'to' => $paymentLinks->lastItem(),
                ],
            ]);
        } catch (\Exception $e) {
            $this->logError('Error fetching payment links', [
                'user_id' => auth()->id(),
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payment links: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a new payment link.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $merchant = $request->user()->merchant;
            
            if (!$merchant) {
                $this->logError('Merchant not found for user', ['user_id' => auth()->id()]);
                return response()->json([
                    'success' => false,
                    'message' => 'Merchant not found',
                ], 404);
            }

            // Validate input
            $validator = Validator::make($request->all(), [
                'title' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'amount' => 'required|numeric|min:0.01|max:999999999.99',
                'currency' => 'nullable|string|size:3',
                'expires_in_hours' => 'nullable|integer|min:1|max:720',
                'payment_methods' => 'nullable|array',
                'payment_methods.*' => 'in:card,upi,netbanking,wallet',
            ]);

            if ($validator->fails()) {
                $this->logWarning('Payment link validation failed', [
                    'merchant_id' => $merchant->id,
                    'errors' => $validator->errors()->toArray()
                ]);
                
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $this->logInfo('Creating payment link', [
                'merchant_id' => $merchant->id,
                'title' => $request->title,
                'amount' => $request->amount,
                'currency' => $request->currency ?? 'INR'
            ]);

            // Calculate expiry
            $expiresInHours = (int)($request->expires_in_hours ?? 24);
            $expiresAt = now()->addHours($expiresInHours);

            // Default payment methods
            $paymentMethods = $request->payment_methods ?? ['card', 'upi', 'netbanking', 'wallet'];

            // Create payment link in transaction
            $paymentLink = DB::transaction(function () use ($merchant, $request, $expiresAt, $paymentMethods) {
                return PaymentLink::create([
                    'merchant_id' => $merchant->id,
                    'link_token' => PaymentLink::generateLinkToken(),
                    'title' => $request->title,
                    'description' => $request->description,
                    'amount' => $request->amount,
                    'currency' => $request->currency ?? $merchant->default_currency ?? 'INR',
                    'status' => 'active',
                    'test_mode' => $merchant->test_mode ?? false,
                    'payment_methods' => $paymentMethods,
                    'expires_at' => $expiresAt,
                    'usage_count' => 0,
                ]);
            });

            $this->logInfo('Payment link created successfully', [
                'payment_link_id' => $paymentLink->id,
                'link_token' => $paymentLink->link_token,
                'merchant_id' => $merchant->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Payment link created successfully',
                'data' => [
                    'id' => $paymentLink->id,
                    'link_token' => $paymentLink->link_token,
                    'title' => $paymentLink->title,
                    'amount' => $paymentLink->amount,
                    'currency' => $paymentLink->currency,
                    'status' => $paymentLink->status,
                    'payment_url' => $paymentLink->getPaymentUrl(),
                    'expires_at' => $paymentLink->expires_at,
                ],
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->logError('Payment link validation exception', [
                'merchant_id' => $request->user()->merchant->id ?? null,
                'errors' => $e->errors()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            $this->logError('Error creating payment link', [
                'merchant_id' => $request->user()->merchant->id ?? null,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment link. Please try again.',
            ], 500);
        }
    }
}
