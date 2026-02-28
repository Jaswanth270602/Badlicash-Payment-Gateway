@extends('layouts.app-sidebar')

@section('title', 'Risk Management - ' . config('app.name'))
@section('page-title','Risk Management')

@section('content')
<div ng-app="badlicashApp" ng-controller="AdminRiskController as arc">
    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">@{{ arc.stats.total_rules || 0 }}</div>
                <div class="stat-label text-muted">Active Rules</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">@{{ arc.stats.total_events || 0 }}</div>
                <div class="stat-label text-muted">Unresolved Events</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">@{{ arc.stats.critical_alerts || 0 }}</div>
                <div class="stat-label text-muted">Critical Alerts</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stat-card">
                <div class="stat-value">@{{ arc.stats.high_alerts || 0 }}</div>
                <div class="stat-label text-muted">High Alerts</div>
            </div>
        </div>
    </div>

    <!-- Tabs -->
    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" data-bs-toggle="tab" href="#rules" ng-click="arc.loadRules()">Risk Rules</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#events" ng-click="arc.loadEvents()">Risk Events</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#alerts" ng-click="arc.loadAlerts()">Fraud Alerts</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#fraud-decisions" ng-click="arc.loadFraudTransactions()">Fraud Decisions</a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-bs-toggle="tab" href="#fds-guide">FDS Events Guide</a>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Risk Rules Tab -->
        <div class="tab-pane fade show active" id="rules">
            <div class="stat-card mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Risk Rules</h5>
                    <button class="btn btn-primary" ng-click="arc.openRuleModal()">
                        <i class="bi bi-plus-lg"></i> New Rule
                    </button>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input class="form-control" placeholder="Search..." ng-model="arc.ruleSearch" ng-change="arc.loadRules()">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" ng-model="arc.ruleFilters.status" ng-change="arc.loadRules()">
                            <option value="">All Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" ng-model="arc.ruleFilters.type" ng-change="arc.loadRules()">
                            <option value="">All Types</option>
                            <option value="velocity">Velocity</option>
                            <option value="amount_limit">Amount Limit</option>
                            <option value="geo_block">Geo Block</option>
                            <option value="merchant_block">Merchant Block</option>
                            <option value="ip_block">IP Block</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Type</th>
                                <th>Action</th>
                                <th>Priority</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="r in arc.rules.data">
                                <td>@{{ $index + 1 }}</td>
                                <td>@{{ r.name }}</td>
                                <td><span class="badge bg-secondary">@{{ r.type }}</span></td>
                                <td><span class="badge bg-info">@{{ r.action }}</span></td>
                                <td>@{{ r.priority }}</td>
                                <td>
                                    <select class="form-select form-select-sm" ng-model="r.status" ng-change="arc.updateRule(r)">
                                        <option value="active">active</option>
                                        <option value="inactive">inactive</option>
                                    </select>
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-danger" ng-click="arc.deleteRule(r)">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Risk Events Tab -->
        <div class="tab-pane fade" id="events">
            <div class="stat-card mb-3">
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input class="form-control" placeholder="Merchant ID" ng-model="arc.eventFilters.merchant_id" ng-change="arc.loadEvents()">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" ng-model="arc.eventFilters.severity" ng-change="arc.loadEvents()">
                            <option value="">All Severity</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" ng-model="arc.eventFilters.resolved" ng-change="arc.loadEvents()">
                            <option value="">All</option>
                            <option value="0">Unresolved</option>
                            <option value="1">Resolved</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Rule</th>
                                <th>Merchant</th>
                                <th>Type</th>
                                <th>Severity</th>
                                <th>Resolved</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="e in arc.events.data">
                                <td>@{{ $index + 1 }}</td>
                                <td>@{{ e.rule?.name || '-' }}</td>
                                <td>@{{ e.merchant_id || '-' }}</td>
                                <td><span class="badge bg-secondary">@{{ e.event_type }}</span></td>
                                <td>
                                    <span class="badge" ng-class="{
                                        'bg-success': e.severity === 'low',
                                        'bg-warning': e.severity === 'medium',
                                        'bg-danger': e.severity === 'high' || e.severity === 'critical'
                                    }">@{{ e.severity }}</span>
                                </td>
                                <td>
                                    <span class="badge" ng-class="e.resolved ? 'bg-success' : 'bg-danger'">
                                        @{{ e.resolved ? 'Resolved' : 'Open' }}
                                    </span>
                                </td>
                                <td>@{{ e.created_at }}</td>
                                <td>
                                    <button class="btn btn-sm btn-primary" ng-if="!e.resolved" ng-click="arc.resolveEvent(e)">
                                        Resolve
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Fraud Alerts Tab -->
        <div class="tab-pane fade" id="alerts">
            <div class="stat-card mb-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">Fraud Alerts</h5>
                    <button class="btn btn-primary" ng-click="arc.openAlertModal()">
                        <i class="bi bi-plus-lg"></i> New Alert
                    </button>
                </div>
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input class="form-control" placeholder="Merchant ID" ng-model="arc.alertFilters.merchant_id" ng-change="arc.loadAlerts()">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" ng-model="arc.alertFilters.status" ng-change="arc.loadAlerts()">
                            <option value="">All Status</option>
                            <option value="open">Open</option>
                            <option value="investigating">Investigating</option>
                            <option value="resolved">Resolved</option>
                            <option value="false_positive">False Positive</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" ng-model="arc.alertFilters.severity" ng-change="arc.loadAlerts()">
                            <option value="">All Severity</option>
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Merchant</th>
                                <th>Type</th>
                                <th>Severity</th>
                                <th>Risk Score</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="a in arc.alerts.data">
                                <td>@{{ $index + 1 }}</td>
                                <td>@{{ a.merchant_id || '-' }}</td>
                                <td><span class="badge bg-secondary">@{{ a.alert_type }}</span></td>
                                <td>
                                    <span class="badge" ng-class="{
                                        'bg-success': a.severity === 'low',
                                        'bg-warning': a.severity === 'medium',
                                        'bg-danger': a.severity === 'high' || a.severity === 'critical'
                                    }">@{{ a.severity }}</span>
                                </td>
                                <td>@{{ a.risk_score }}/100</td>
                                <td>
                                    <select class="form-select form-select-sm" ng-model="a.status" ng-change="arc.updateAlert(a)">
                                        <option value="open">open</option>
                                        <option value="investigating">investigating</option>
                                        <option value="resolved">resolved</option>
                                        <option value="false_positive">false_positive</option>
                                    </select>
                                </td>
                                <td>@{{ a.created_at }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info" ng-click="arc.viewAlert(a)">
                                        View
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Fraud Decisions (FDS) Tab -->
        <div class="tab-pane fade" id="fraud-decisions">
            <div class="stat-card mb-3">
                <div class="row g-2 mb-3">
                    <div class="col-md-3">
                        <input class="form-control" placeholder="Merchant ID" ng-model="arc.fraudFilters.merchant_id" ng-change="arc.loadFraudTransactions()">
                    </div>
                    <div class="col-md-3">
                        <input class="form-control" placeholder="Transaction Ref" ng-model="arc.fraudFilters.transaction_id" ng-change="arc.loadFraudTransactions()">
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" ng-model="arc.fraudFilters.decision" ng-change="arc.loadFraudTransactions()">
                            <option value="">All Decisions</option>
                            <option value="allow">Allow</option>
                            <option value="review">Review</option>
                            <option value="block">Block</option>
                        </select>
                    </div>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Transaction Ref</th>
                                <th>Merchant</th>
                                <th>Risk Score</th>
                                <th>Decision</th>
                                <th>Evaluated At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="t in arc.fraudTxns.data">
                                <td>@{{ $index + 1 }}</td>
                                <td>@{{ t.transaction_id }}</td>
                                <td>@{{ t.merchant_id || '-' }}</td>
                                <td>@{{ t.risk_score }}</td>
                                <td>
                                    <span class="badge" ng-class="{
                                        'bg-success': t.decision === 'allow',
                                        'bg-warning': t.decision === 'review',
                                        'bg-danger': t.decision === 'block'
                                    }">@{{ t.decision }}</span>
                                </td>
                                <td>@{{ t.created_at }}</td>
                                <td>
                                    <button class="btn btn-sm btn-info" ng-click="arc.viewFraudTransaction(t)">
                                        View Rules
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- FDS Events Guide Tab -->
        <div class="tab-pane fade" id="fds-guide">
            <div class="stat-card mb-3">
                <h5 class="mb-3">Fraud Detection System – Events Guide</h5>
                <p class="text-muted">
                    This guide explains what each fraud signal means, when it triggers, and how you should handle it as an admin.
                    It is based on the Fraud Detection System (FDS) design guide for this payment gateway.
                </p>

                {{-- 1. Velocity Checks --}}
                <h6 class="mt-4">1. Velocity Checks (Frequency of Activity)</h6>
                <p class="text-muted">
                    Velocity checks look at how many transactions happen in a short time window.
                    Sudden spikes usually indicate bots, testing stolen cards, or scripted abuse.
                </p>
                <ul>
                    <li>
                        <strong>Card velocity – Too many transactions on one card</strong><br>
                        <span class="text-muted">
                            Example: &gt; 5 transactions on the same card in 5 minutes.<br>
                            Meaning: Possible card testing or abnormal card usage.<br>
                            What to do: Check the merchant and card history; if many small test amounts, consider blocking further attempts and alerting the merchant.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>IP velocity – Too many transactions from one IP</strong><br>
                        <span class="text-muted">
                            Example: &gt; 10 transactions from the same IP in 10 minutes.<br>
                            Meaning: Possible bot traffic or a fraud operator running many cards.<br>
                            What to do: Check if it is a legitimate terminal vs. many different cards/users. For suspicious patterns, rate-limit or block that IP.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>Failed attempts velocity – Too many failures</strong><br>
                        <span class="text-muted">
                            Example: &gt; 3 failed transactions in 2 minutes.<br>
                            Meaning: Likely wrong CVV/OTP guesses or credential stuffing.<br>
                            What to do: Review failure reasons. If concentrated on one card or IP, consider temporary blocking or extra verification.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>Amount velocity – Sudden spike vs normal</strong><br>
                        <span class="text-muted">
                            Meaning: Transaction amounts jump suddenly compared to normal behavior.<br>
                            What to do: Compare with historical amounts for that merchant / user. Verify with merchant if this is expected (campaign, bulk bill, etc.).
                        </span>
                    </li>
                </ul>

                {{-- 2. Amount-Based Risk --}}
                <h6 class="mt-4">2. Amount-Based Risk</h6>
                <p class="text-muted">
                    Amount checks highlight when a single transaction amount is unusual compared to user or merchant profiles.
                </p>
                <ul>
                    <li>
                        <strong>Amount much higher than user’s normal spend</strong><br>
                        <span class="text-muted">
                            Rule: Amount &gt; user average × 3.<br>
                            Meaning: Customer is spending far more than usual – could be a genuine big purchase or a compromised account.<br>
                            What to do: Review user history and card details; for high risk, ask merchant to verify the customer.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>Amount above merchant’s configured maximum</strong><br>
                        <span class="text-muted">
                            Rule: Amount &gt; merchant max ticket size.<br>
                            Meaning: Merchant is processing outside their normal profile or agreed limits.<br>
                            What to do: Confirm if this fits their business; if clearly abnormal, consider blocking or requiring manual approval.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>Unusual currency</strong><br>
                        <span class="text-muted">
                            Meaning: Currency differs from what the merchant usually uses or is allowed to use.<br>
                            What to do: Check if the merchant is actually enabled for that currency. If not, treat such transactions as high risk.
                        </span>
                    </li>
                </ul>

                {{-- 3. Location & Geo Anomalies --}}
                <h6 class="mt-4">3. Location & Geo Anomalies</h6>
                <p class="text-muted">
                    Geo checks compare IP country, billing country, card country, and merchant country.
                    Large mismatches or rapid country changes are strong fraud signals.
                </p>
                <ul>
                    <li>
                        <strong>Country changed within minutes</strong><br>
                        <span class="text-muted">
                            Meaning: Same user/card/device appears from very different countries in a short time.<br>
                            What to do: Check for VPN/proxy usage; if unexplained, treat as high risk and consider blocking or review.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>High-risk country</strong><br>
                        <span class="text-muted">
                            Meaning: Country is in a configured high-risk list (per your risk policy).<br>
                            What to do: Use stricter thresholds for these geos; prioritize for manual review or auto-review flows.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>IP vs billing country mismatch</strong><br>
                        <span class="text-muted">
                            Meaning: IP geo and billing address country do not match.<br>
                            What to do: Could be legitimate travel or VPN, or card used abroad. Combine with amount, device, and history before deciding.
                        </span>
                    </li>
                </ul>

                {{-- 4. IP Reputation --}}
                <h6 class="mt-4">4. IP Reputation</h6>
                <p class="text-muted">
                    IP reputation checks use external or internal lists to rate the trustworthiness of an IP address.
                </p>
                <ul>
                    <li>
                        <strong>VPN / Proxy IP</strong><br>
                        <span class="text-muted">
                            Meaning: User is hiding their true location; not always fraud, but higher baseline risk.<br>
                            What to do: Combine with amount and velocity. For high-value or already risky cases, treat cautiously.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>TOR exit node</strong><br>
                        <span class="text-muted">
                            Meaning: Traffic comes from the TOR network (high anonymity).<br>
                            What to do: Many gateways auto-review or block TOR for sensitive operations; apply stricter policies here.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>Known bad IP</strong><br>
                        <span class="text-muted">
                            Meaning: IP is flagged in internal/third-party lists due to past fraud or chargebacks.<br>
                            What to do: Treat as critical; generally block or require very strong evidence to allow.
                        </span>
                    </li>
                </ul>

                {{-- 5. Device Fingerprinting --}}
                <h6 class="mt-4">5. Device Fingerprinting</h6>
                <p class="text-muted">
                    Device signals track how a browser/device fingerprint is used across users and transactions.
                </p>
                <ul>
                    <li>
                        <strong>New device</strong><br>
                        <span class="text-muted">
                            Meaning: First time this device is seen for this user/merchant.<br>
                            What to do: On its own, low–medium risk. Watch more closely when combined with high amount or risky geo.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>Device hopping</strong><br>
                        <span class="text-muted">
                            Meaning: Same user/account moves across multiple devices quickly, or one device is used by many different accounts.<br>
                            What to do: For normal merchants this is suspicious; check whether it fits a call-center or shared-terminal scenario.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>Same device across many users</strong><br>
                        <span class="text-muted">
                            Meaning: A single fingerprint is used for many different users/cards.<br>
                            What to do: Strong sign of a fraud operator; consider blocking the device and investigating all related accounts.
                        </span>
                    </li>
                </ul>

                {{-- 6. Payment Method Risk --}}
                <h6 class="mt-4">6. Payment Method–Specific Risk</h6>
                <p class="text-muted">
                    Different payment methods (Card, UPI, Wallet, Bank) carry different risk patterns.
                </p>
                <ul>
                    <li>
                        <strong>Card – BIN country mismatch</strong><br>
                        <span class="text-muted">
                            Meaning: Card issuing country (from BIN) does not match user or merchant country.<br>
                            What to do: Acceptable for cross-border merchants; otherwise treat as medium risk and combine with other signals.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>Card – Reused across many users</strong><br>
                        <span class="text-muted">
                            Meaning: Same card is used on multiple unrelated accounts.<br>
                            What to do: Very suspicious; likely stolen card. Consider blocking and contacting the merchant.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>Card – CVV / AVS mismatch</strong><br>
                        <span class="text-muted">
                            Meaning: CVV or address verification fails.<br>
                            What to do: Often a hard decline signal; if you allow exceptions, ensure they are well justified.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>UPI / Wallet – New VPA / account</strong><br>
                        <span class="text-muted">
                            Meaning: First time this VPA or wallet ID is seen.<br>
                            What to do: Similar to “new device”. Low–medium risk alone; treat carefully when combined with other red flags.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>UPI / Wallet – Rapid retries</strong><br>
                        <span class="text-muted">
                            Meaning: Many retries in a short time for the same VPA/wallet.<br>
                            What to do: Could be confusion or scripted attacks; check patterns and failures.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>UPI / Wallet – Different device every attempt</strong><br>
                        <span class="text-muted">
                            Meaning: Same UPI/wallet identity is used from many devices in a short window.<br>
                            What to do: Strong signal of shared or stolen credentials; treat as high risk.
                        </span>
                    </li>
                </ul>

                {{-- 7. Behavioral Signals --}}
                <h6 class="mt-4">7. Behavioral Analysis (On-Page Behaviour)</h6>
                <p class="text-muted">
                    These signals come from how the customer behaves on the payment page (from frontend events).
                </p>
                <ul>
                    <li>
                        <strong>No mouse movement / robotic behavior</strong><br>
                        <span class="text-muted">
                            Meaning: Form is filled and submitted with minimal human-like interaction, suggesting bots.<br>
                            What to do: Treat as medium risk alone; escalate when combined with other anomalies (velocity, geo, amount).
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>Very fast checkout</strong><br>
                        <span class="text-muted">
                            Meaning: Extremely short time from page load to payment completion.<br>
                            What to do: Often indicates scripts. For high-value or new users, consider review or blocking.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>Copy–paste card number</strong><br>
                        <span class="text-muted">
                            Meaning: Card number pasted instead of typed.<br>
                            What to do: Can be legitimate (password managers) but is also common in scripted fraud. Use along with other signals.
                        </span>
                    </li>
                </ul>

                {{-- 8. Historical Risk --}}
                <h6 class="mt-4">8. Historical Risk Signals</h6>
                <p class="text-muted">
                    Historical data about users, cards, or merchants strongly influences risk.
                </p>
                <ul>
                    <li>
                        <strong>Previous chargeback</strong><br>
                        <span class="text-muted">
                            Meaning: This identity had chargebacks before.<br>
                            What to do: Treat as very high risk; apply stricter limits or blocks for high-value transactions.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>Past fraud flag</strong><br>
                        <span class="text-muted">
                            Meaning: This user/card/merchant was explicitly marked as fraudulent earlier.<br>
                            What to do: Generally block new attempts or require strong, verified justification to allow.
                        </span>
                    </li>
                    <li class="mt-2">
                        <strong>Clean history / good behavior</strong><br>
                        <span class="text-muted">
                            Meaning: Long history with low or no fraud and chargebacks.<br>
                            What to do: You can be slightly more tolerant on borderline cases, but still respect other strong signals.
                        </span>
                    </li>
                </ul>

                <p class="mt-4 text-muted">
                    <strong>Note for admins:</strong> Always look at the combination of signals, not a single event in isolation.
                    A simple, explainable FDS with clear reasons is more reliable than a black-box model you cannot interpret.
                </p>
            </div>
        </div>
    </div>

    <!-- New Rule Modal -->
    <div class="modal fade" id="ruleModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Risk Rule</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><input class="form-control" placeholder="Rule Name" ng-model="arc.ruleForm.name"></div>
                    <div class="mb-2">
                        <select class="form-select" ng-model="arc.ruleForm.type">
                            <option value="velocity">Velocity</option>
                            <option value="amount_limit">Amount Limit</option>
                            <option value="geo_block">Geo Block</option>
                            <option value="merchant_block">Merchant Block</option>
                            <option value="ip_block">IP Block</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <label class="form-label">Rule Config (JSON)</label>
                        <textarea class="form-control" rows="3" ng-model="arc.ruleForm.rule_config_json" placeholder='{"max_transactions": 10, "time_window": "1h"}'></textarea>
                    </div>
                    <div class="mb-2">
                        <select class="form-select" ng-model="arc.ruleForm.action">
                            <option value="block">Block</option>
                            <option value="alert">Alert</option>
                            <option value="review">Review</option>
                        </select>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <input type="number" min="0" max="100" class="form-control" placeholder="Priority" ng-model="arc.ruleForm.priority">
                        </div>
                        <div class="col-6">
                            <select class="form-select" ng-model="arc.ruleForm.status">
                                <option value="active">active</option>
                                <option value="inactive">inactive</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" ng-click="arc.createRule()">Create</button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Alert Modal -->
    <div class="modal fade" id="alertModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Fraud Alert</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-2"><input class="form-control" placeholder="Merchant ID" ng-model="arc.alertForm.merchant_id"></div>
                    <div class="mb-2"><input class="form-control" placeholder="Transaction ID" ng-model="arc.alertForm.transaction_id"></div>
                    <div class="mb-2">
                        <select class="form-select" ng-model="arc.alertForm.alert_type">
                            <option value="suspicious_pattern">Suspicious Pattern</option>
                            <option value="chargeback_risk">Chargeback Risk</option>
                            <option value="velocity_anomaly">Velocity Anomaly</option>
                            <option value="amount_anomaly">Amount Anomaly</option>
                            <option value="geo_anomaly">Geo Anomaly</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <select class="form-select" ng-model="arc.alertForm.severity">
                            <option value="low">Low</option>
                            <option value="medium">Medium</option>
                            <option value="high">High</option>
                            <option value="critical">Critical</option>
                        </select>
                    </div>
                    <div class="mb-2">
                        <textarea class="form-control" rows="3" placeholder="Description" ng-model="arc.alertForm.description"></textarea>
                    </div>
                    <div class="mb-2">
                        <input type="number" min="0" max="100" class="form-control" placeholder="Risk Score (0-100)" ng-model="arc.alertForm.risk_score">
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button class="btn btn-primary" ng-click="arc.createAlert()">Create</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Fraud Decision Details Modal -->
    <div class="modal fade" id="fraudTxnModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Fraud Decision Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p><strong>Transaction Ref:</strong> @{{ arc.selectedFraudTxn.transaction_id }}</p>
                    <p><strong>Merchant ID:</strong> @{{ arc.selectedFraudTxn.merchant_id || '-' }}</p>
                    <p><strong>Risk Score:</strong> @{{ arc.selectedFraudTxn.risk_score }}</p>
                    <p><strong>Decision:</strong> @{{ arc.selectedFraudTxn.decision }}</p>
                    <p><strong>Execution Time:</strong> @{{ arc.selectedFraudTxn.execution_time_ms }} ms</p>

                    <h6 class="mt-3">Triggered Rules</h6>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Rule</th>
                                    <th>Weight</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="r in arc.selectedFraudEvents">
                                    <td>@{{ r.rule_name }}</td>
                                    <td>@{{ r.weight }}</td>
                                    <td>@{{ r.reason }}</td>
                                </tr>
                                <tr ng-if="!arc.selectedFraudEvents || !arc.selectedFraudEvents.length">
                                    <td colspan="3" class="text-muted">No rules were triggered for this decision.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('admin.risk.angular.main_controller')

