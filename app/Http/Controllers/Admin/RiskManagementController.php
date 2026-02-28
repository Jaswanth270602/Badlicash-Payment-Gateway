<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RiskRule;
use App\Models\RiskEvent;
use App\Models\FraudAlert;
use App\Models\FraudTransaction;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RiskManagementController extends Controller
{
    public function index(): View
    {
        return view('admin.risk.index');
    }

    // Risk Rules
    public function getRules(Request $request)
    {
        $q = RiskRule::query();
        if ($request->status) $q->where('status', $request->status);
        if ($request->type) $q->where('type', $request->type);
        if ($request->search) $q->where('name', 'like', '%'.$request->search.'%');
        return response()->json(['success' => true, 'data' => $q->orderBy('priority', 'desc')->orderByDesc('created_at')->paginate(10)]);
    }

    public function storeRule(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:velocity,amount_limit,geo_block,merchant_block,ip_block',
            'rule_config' => 'required|array',
            'action' => 'required|in:block,alert,review',
            'status' => 'required|in:active,inactive',
            'priority' => 'nullable|integer|min:0|max:100',
        ]);
        $validated['priority'] = $validated['priority'] ?? 0;
        $rule = RiskRule::create($validated);
        return response()->json(['success' => true, 'data' => $rule]);
    }

    public function updateRule(Request $request, int $id)
    {
        $rule = RiskRule::findOrFail($id);
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:velocity,amount_limit,geo_block,merchant_block,ip_block',
            'rule_config' => 'sometimes|array',
            'action' => 'sometimes|in:block,alert,review',
            'status' => 'sometimes|in:active,inactive',
            'priority' => 'sometimes|integer|min:0|max:100',
        ]);
        $rule->fill($validated)->save();
        return response()->json(['success' => true, 'data' => $rule]);
    }

    public function deleteRule(int $id)
    {
        $rule = RiskRule::findOrFail($id);
        $rule->delete();
        return response()->json(['success' => true]);
    }

    // Risk Events
    public function getEvents(Request $request)
    {
        $q = RiskEvent::with(['rule', 'merchant', 'transaction']);
        if ($request->severity) $q->where('severity', $request->severity);
        if ($request->resolved !== null) $q->where('resolved', $request->resolved);
        if ($request->merchant_id) $q->where('merchant_id', $request->merchant_id);
        if ($request->rule_id) $q->where('rule_id', $request->rule_id);
        return response()->json(['success' => true, 'data' => $q->orderByDesc('created_at')->paginate(15)]);
    }

    public function resolveEvent(Request $request, int $id)
    {
        $event = RiskEvent::findOrFail($id);
        $validated = $request->validate([
            'resolved' => 'required|boolean',
        ]);
        $event->resolved = $validated['resolved'];
        $event->resolved_at = $validated['resolved'] ? now() : null;
        $event->resolved_by = $validated['resolved'] ? auth()->id() : null;
        $event->save();
        return response()->json(['success' => true, 'data' => $event]);
    }

    // FDS Fraud Transactions (Fraud Decisions)
    public function getFraudTransactions(Request $request)
    {
        $query = FraudTransaction::query();

        if ($request->merchant_id) {
            $query->where('merchant_id', $request->merchant_id);
        }

        if ($request->decision) {
            $query->where('decision', $request->decision);
        }

        if ($request->transaction_id) {
            $query->where('transaction_id', 'like', '%' . $request->transaction_id . '%');
        }

        return response()->json([
            'success' => true,
            'data' => $query->orderByDesc('created_at')->paginate(15),
        ]);
    }

    public function getFraudTransactionDetails(int $id)
    {
        $fraudTxn = FraudTransaction::with('fraudEvents')->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => $fraudTxn,
        ]);
    }

    // Fraud Alerts
    public function getAlerts(Request $request)
    {
        $q = FraudAlert::with(['merchant', 'transaction']);
        if ($request->status) $q->where('status', $request->status);
        if ($request->severity) $q->where('severity', $request->severity);
        if ($request->alert_type) $q->where('alert_type', $request->alert_type);
        if ($request->merchant_id) $q->where('merchant_id', $request->merchant_id);
        return response()->json(['success' => true, 'data' => $q->orderByDesc('created_at')->paginate(15)]);
    }

    public function createAlert(Request $request)
    {
        $validated = $request->validate([
            'merchant_id' => 'nullable|exists:merchants,id',
            'transaction_id' => 'nullable|exists:transactions,id',
            'alert_type' => 'required|in:suspicious_pattern,chargeback_risk,velocity_anomaly,amount_anomaly,geo_anomaly',
            'severity' => 'required|in:low,medium,high,critical',
            'description' => 'required|string',
            'risk_score' => 'nullable|integer|min:0|max:100',
        ]);
        $validated['risk_score'] = $validated['risk_score'] ?? 50;
        $alert = FraudAlert::create($validated);
        return response()->json(['success' => true, 'data' => $alert->load(['merchant', 'transaction'])]);
    }

    public function updateAlert(Request $request, int $id)
    {
        $alert = FraudAlert::findOrFail($id);
        $validated = $request->validate([
            'status' => 'sometimes|in:open,investigating,resolved,false_positive',
            'assigned_to' => 'nullable|integer',
            'resolution_notes' => 'nullable|string',
        ]);
        if (isset($validated['status']) && in_array($validated['status'], ['resolved', 'false_positive'])) {
            $validated['resolved_at'] = now();
        }
        $alert->fill($validated)->save();
        return response()->json(['success' => true, 'data' => $alert]);
    }

    // Dashboard Stats
    public function getStats()
    {
        // Respect admin view mode (test vs live) so stats match the selected environment.
        // In test mode we count only test transactions; in live mode we count only live ones.
        $adminMode = session('admin_view_mode', 'test'); // 'test' | 'live'
        $isTestMode = $adminMode === 'test';

        // Base queries
        $riskEventsQuery = RiskEvent::query()->where('resolved', false);
        $criticalAlertsQuery = FraudAlert::query()
            ->where('severity', 'critical')
            ->where('status', 'open');
        $highAlertsQuery = FraudAlert::query()
            ->where('severity', 'high')
            ->where('status', 'open');

        // Filter by transaction test_mode when available so that stats reflect LIVE / TEST correctly
        $riskEventsQuery->whereHas('transaction', function ($q) use ($isTestMode) {
            $q->where('test_mode', $isTestMode);
        });

        $criticalAlertsQuery->whereHas('transaction', function ($q) use ($isTestMode) {
            $q->where('test_mode', $isTestMode);
        });

        $highAlertsQuery->whereHas('transaction', function ($q) use ($isTestMode) {
            $q->where('test_mode', $isTestMode);
        });

        return response()->json([
            'success' => true,
            'data' => [
                'total_rules' => RiskRule::where('status', 'active')->count(),
                'total_events' => $riskEventsQuery->count(),
                'critical_alerts' => $criticalAlertsQuery->count(),
                'high_alerts' => $highAlertsQuery->count(),
            ],
        ]);
    }
}

