<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BaseRate;
use App\Models\Merchant;
use App\Models\Bank;
use App\Services\BaseRateService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class BaseRatesController extends Controller
{
    protected BaseRateService $baseRateService;

    public function __construct(BaseRateService $baseRateService)
    {
        $this->baseRateService = $baseRateService;
    }

    /**
     * Display base rates management page.
     */
    public function index(): View
    {
        return view('admin.base-rates.index');
    }

    /**
     * Get base rates data.
     */
    public function getData(Request $request): JsonResponse
    {
        try {
            $perPage = min($request->get('per_page', 15), 100);
            
            $query = BaseRate::query();

            // Filters
            if ($request->has('rate_type') && $request->get('rate_type') !== 'all') {
                $query->where('rate_type', $request->get('rate_type'));
            }

            if ($request->has('payment_method') && $request->get('payment_method') !== 'all') {
                $query->where('payment_method', $request->get('payment_method'));
            }

            if ($request->has('service_type') && $request->get('service_type') !== 'all') {
                $query->where('service_type', $request->get('service_type'));
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            // Search
            if ($request->has('search') && $request->get('search')) {
                $search = $request->get('search');
                $query->where(function($q) use ($search) {
                    $q->where('rate_type', 'like', "%{$search}%")
                      ->orWhere('payment_method', 'like', "%{$search}%")
                      ->orWhere('service_type', 'like', "%{$search}%");
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'created_at');
            $sortDirection = $request->get('sort_direction', 'desc');
            $query->orderBy($sortBy, $sortDirection);

            $rates = $query->with(['merchant', 'bank'])->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $rates->items(),
                'pagination' => [
                    'current_page' => $rates->currentPage(),
                    'per_page' => $rates->perPage(),
                    'total' => $rates->total(),
                    'last_page' => $rates->lastPage(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch base rates: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Store a newly created base rate.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'rate_type' => 'required|in:bank,merchant,receiver,pricer',
                'entity_type' => 'required|string',
                'entity_id' => 'nullable|integer',
                'payment_method' => 'required|in:card,upi,netbanking,wallet',
                'service_type' => 'required|string',
                'transaction_type' => 'required|in:domestic,international',
                'percentage_fee' => 'required|numeric|min:0|max:100',
                'flat_fee' => 'required|numeric|min:0',
                'gst_percentage' => 'nullable|numeric|min:0|max:100',
                'is_active' => 'boolean',
                'effective_from' => 'nullable|date',
                'effective_to' => 'nullable|date|after:effective_from',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $data = $validator->validated();
            // Set entity_type based on rate_type if not provided
            if (!isset($data['entity_type']) && isset($data['rate_type'])) {
                if (in_array($data['rate_type'], ['merchant', 'bank'])) {
                    $data['entity_type'] = $data['rate_type'];
                }
            }

            // Use BaseRateService to create or update
            $rate = $this->baseRateService->createOrUpdateRate($data);

            return response()->json([
                'success' => true,
                'message' => 'Base rate created successfully',
                'data' => $rate->load(['merchant', 'bank']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create base rate: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified base rate.
     */
    public function update(Request $request, $id): JsonResponse
    {
        try {
            $rate = BaseRate::findOrFail($id);

            $validator = Validator::make($request->all(), [
                'rate_type' => 'sometimes|in:bank,merchant,receiver,pricer',
                'entity_type' => 'sometimes|string',
                'entity_id' => 'nullable|integer',
                'payment_method' => 'sometimes|in:card,upi,netbanking,wallet',
                'service_type' => 'sometimes|string',
                'transaction_type' => 'sometimes|in:domestic,international',
                'percentage_fee' => 'sometimes|numeric|min:0|max:100',
                'flat_fee' => 'sometimes|numeric|min:0',
                'gst_percentage' => 'nullable|numeric|min:0|max:100',
                'is_active' => 'boolean',
                'effective_from' => 'nullable|date',
                'effective_to' => 'nullable|date|after:effective_from',
                'notes' => 'nullable|string',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $rate->update($validator->validated());

            return response()->json([
                'success' => true,
                'message' => 'Base rate updated successfully',
                'data' => $rate->fresh(['merchant', 'bank']),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update base rate: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Remove the specified base rate.
     */
    public function destroy($id): JsonResponse
    {
        try {
            $rate = BaseRate::findOrFail($id);
            $rate->delete();

            return response()->json([
                'success' => true,
                'message' => 'Base rate deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete base rate: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get entities (merchants, banks) for dropdowns.
     */
    public function getEntities(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type');

            if ($type === 'merchant') {
                $entities = Merchant::select('id', 'name', 'email')
                    ->where('status', 'active')
                    ->get()
                    ->map(function($m) {
                        return ['id' => $m->id, 'name' => $m->name . ' (' . $m->email . ')'];
                    });
            } elseif ($type === 'bank') {
                $entities = Bank::select('id', 'name', 'code')
                    ->get()
                    ->map(function($b) {
                        return ['id' => $b->id, 'name' => $b->name . ' (' . $b->code . ')'];
                    });
            } else {
                $entities = [];
            }

            return response()->json([
                'success' => true,
                'data' => $entities,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch entities: ' . $e->getMessage(),
            ], 500);
        }
    }
}
