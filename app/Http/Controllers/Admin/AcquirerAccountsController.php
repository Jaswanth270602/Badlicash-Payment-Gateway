<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AcquirerAccount;
use App\Models\Merchant;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AcquirerAccountsController extends Controller
{
    /**
     * Display the acquirer accounts page.
     */
    public function index(): View
    {
        return view('admin.acquirer.accounts');
    }

    /**
     * Get acquirer accounts data for the table.
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $perPage = min($request->get('per_page', 5), 100);
            
            $query = AcquirerAccount::query()->with('merchants');

            // Filters
            if ($request->has('acquirer_name') && $request->get('acquirer_name') !== 'all') {
                $query->where('acquirer_name', $request->get('acquirer_name'));
            }

            if ($request->has('mode') && $request->get('mode') !== 'all') {
                $query->where('mode', $request->get('mode'));
            }

            if ($request->has('sector') && $request->get('sector') !== 'all') {
                $query->where('sector', $request->get('sector'));
            }

            if ($request->has('team') && $request->get('team') !== 'all') {
                $query->where('team', $request->get('team'));
            }

            // Search
            if ($request->has('search') && $request->get('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('account_id', 'like', "%{$search}%")
                      ->orWhere('acquirer_name', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('hdfc_me_code', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'id');
            $sortDirection = $request->get('sort_direction', 'desc');
            if (in_array($sortBy, ['id', 'account_id', 'acquirer_name', 'team', 'mode', 'sector', 'created_at'])) {
                $query->orderBy($sortBy, $sortDirection);
            } else {
                $query->latest();
            }

            $accounts = $query->paginate($perPage);

            $data = collect($accounts->items())->map(function($account) {
                return [
                    'id' => $account->id,
                    'account_id' => $account->account_id,
                    'acquirer_name' => $account->acquirer_name,
                    'team' => $account->team,
                    'description' => $account->description,
                    'whitelist_url' => $account->whitelist_url,
                    'mode' => $account->mode,
                    'sector' => $account->sector,
                    'hdfc_me_code' => $account->hdfc_me_code,
                    'settlement_account_name' => $account->settlement_account_name,
                    'refund_allowed' => $account->refund_allowed,
                    'settlements_to_be_created' => $account->settlements_to_be_created,
                    'mask_pii' => $account->mask_pii,
                    'email_ids' => $account->email_ids,
                    'live_request_url' => $account->live_request_url,
                    'live_query_url' => $account->live_query_url,
                    'live_refund_url' => $account->live_refund_url,
                    'test_request_url' => $account->test_request_url,
                    'test_query_url' => $account->test_query_url,
                    'test_refund_url' => $account->test_refund_url,
                    'merchants' => $account->merchants->pluck('name')->implode(', '),
                    'merchant_ids' => $account->merchants->pluck('id')->toArray(),
                    'created_at' => $account->created_at->format('Y-m-d H:i:s'),
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'pagination' => [
                    'current_page' => $accounts->currentPage(),
                    'per_page' => $accounts->perPage(),
                    'total' => $accounts->total(),
                    'last_page' => $accounts->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch acquirer accounts: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created acquirer account.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'account_id' => 'required|string|max:255|unique:acquirer_accounts,account_id',
            'acquirer_name' => 'required|string|max:255',
            'team' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'whitelist_url' => 'nullable|url|max:500',
            'mode' => 'required|in:TEST,LIVE',
            'sector' => 'nullable|string|max:255',
            'hdfc_me_code' => 'nullable|string|max:255',
            'settlement_account_name' => 'nullable|string|max:255',
            'refund_allowed' => 'boolean',
            'settlements_to_be_created' => 'boolean',
            'mask_pii' => 'boolean',
            'email_ids' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'salt' => 'nullable|string',
            'additional_key_1' => 'nullable|string',
            'additional_key_2' => 'nullable|string',
            'additional_key_3' => 'nullable|string',
            'additional_key_data' => 'nullable|string',
            'live_request_url' => 'nullable|url|max:500',
            'live_query_url' => 'nullable|url|max:500',
            'live_refund_url' => 'nullable|url|max:500',
            'test_request_url' => 'nullable|url|max:500',
            'test_query_url' => 'nullable|url|max:500',
            'test_refund_url' => 'nullable|url|max:500',
            'nodal_account' => 'nullable|string|max:255',
            'merchant_ids' => 'nullable|array',
            'merchant_ids.*' => 'exists:merchants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $account = AcquirerAccount::create($validator->validated());

            // Attach merchants if provided
            if ($request->has('merchant_ids') && is_array($request->merchant_ids)) {
                $account->merchants()->sync($request->merchant_ids);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acquirer account created successfully',
                'data' => $account->load('merchants'),
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to create acquirer account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified acquirer account.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $account = AcquirerAccount::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'account_id' => 'required|string|max:255|unique:acquirer_accounts,account_id,' . $id,
            'acquirer_name' => 'required|string|max:255',
            'team' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'whitelist_url' => 'nullable|url|max:500',
            'mode' => 'required|in:TEST,LIVE',
            'sector' => 'nullable|string|max:255',
            'hdfc_me_code' => 'nullable|string|max:255',
            'settlement_account_name' => 'nullable|string|max:255',
            'refund_allowed' => 'boolean',
            'settlements_to_be_created' => 'boolean',
            'mask_pii' => 'boolean',
            'email_ids' => 'nullable|string',
            'secret_key' => 'nullable|string',
            'salt' => 'nullable|string',
            'additional_key_1' => 'nullable|string',
            'additional_key_2' => 'nullable|string',
            'additional_key_3' => 'nullable|string',
            'additional_key_data' => 'nullable|string',
            'live_request_url' => 'nullable|url|max:500',
            'live_query_url' => 'nullable|url|max:500',
            'live_refund_url' => 'nullable|url|max:500',
            'test_request_url' => 'nullable|url|max:500',
            'test_query_url' => 'nullable|url|max:500',
            'test_refund_url' => 'nullable|url|max:500',
            'nodal_account' => 'nullable|string|max:255',
            'merchant_ids' => 'nullable|array',
            'merchant_ids.*' => 'exists:merchants,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            DB::beginTransaction();

            $account->update($validator->validated());

            // Sync merchants if provided
            if ($request->has('merchant_ids')) {
                $account->merchants()->sync($request->merchant_ids ?? []);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Acquirer account updated successfully',
                'data' => $account->fresh('merchants'),
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to update acquirer account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified acquirer account.
     */
    public function destroy(string $id): JsonResponse
    {
        try {
            $account = AcquirerAccount::findOrFail($id);
            $account->delete();

            return response()->json([
                'success' => true,
                'message' => 'Acquirer account deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete acquirer account: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get acquirer names for dropdown.
     */
    public function getAcquirerNames(): JsonResponse
    {
        $names = AcquirerAccount::distinct()->pluck('acquirer_name')->filter()->values();
        // Add common acquirer names
        $commonNames = collect(['A2Pay', 'Paytm', 'Switch', 'HDFC', 'ICICI', 'Axis', 'SBI', 'Razorpay', 'PayU']);
        $allNames = $commonNames->merge($names)->unique()->sort()->values();
        
        return response()->json([
            'success' => true,
            'data' => $allNames,
        ]);
    }

    /**
     * Get merchants list for dropdown.
     */
    public function getMerchants(): JsonResponse
    {
        $merchants = Merchant::select('id', 'name', 'email')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $merchants,
        ]);
    }
}
