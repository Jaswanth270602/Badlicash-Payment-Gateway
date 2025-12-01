@extends('layouts.app-sidebar')

@section('title', 'Dashboard - BadliCash')
@section('page-title', 'Dashboard')

@section('content')
<div ng-app="badlicashApp" ng-controller="DashboardController as dc">
    <div class="row mb-4">
        <div class="col-md-12">
            <h3 class="fw-bold">Welcome, {{ $user->name }}</h3>
            <p class="text-muted">{{ $merchant->name }} - <span class="badge {{ $merchant->test_mode ? 'bg-warning' : 'bg-success' }}">{{ $merchant->test_mode ? 'TEST MODE' : 'LIVE MODE' }}</span></p>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="text-muted mb-0">Total Transactions</h6>
                    <i class="bi bi-credit-card-2-front text-primary"></i>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($stats['total_transactions']) }}</h3>
                <small class="text-success">
                    <i class="bi bi-check-circle"></i> {{ number_format($stats['successful_transactions']) }} successful
                </small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="text-muted mb-0">Total Volume</h6>
                    <i class="bi bi-currency-dollar text-primary"></i>
                </div>
                <h3 class="fw-bold mb-1">{{ $merchant->default_currency }} {{ number_format($stats['total_volume'], 2) }}</h3>
                <small class="text-muted">Lifetime</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="text-muted mb-0">Pending Refunds</h6>
                    <i class="bi bi-arrow-counterclockwise text-warning"></i>
                </div>
                <h3 class="fw-bold mb-1">{{ number_format($stats['pending_refunds']) }}</h3>
                <small class="text-muted">Awaiting processing</small>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="text-muted mb-0">Success Rate</h6>
                    <i class="bi bi-graph-up text-success"></i>
                </div>
                <h3 class="fw-bold mb-1">
                    @if($stats['total_transactions'] > 0)
                        {{ number_format(($stats['successful_transactions'] / $stats['total_transactions']) * 100, 1) }}%
                    @else
                        0%
                    @endif
                </h3>
                <small class="text-muted">Payment success</small>
            </div>
        </div>
    </div>

    <!-- Quick Actions & Info -->
    <div class="row g-4">
        <div class="col-md-8">
            <div class="stat-card">
                <h5 class="mb-3"><i class="bi bi-lightning-charge me-2"></i>Quick Actions</h5>
                <div class="row g-3">
                    <div class="col-md-6">
                        <a href="{{ route('merchant.payment_links.index') }}" class="card border text-decoration-none text-dark">
                            <div class="card-body">
                                <i class="bi bi-link-45deg text-primary fs-4"></i>
                                <h6 class="mt-2 mb-1">Create Payment Link</h6>
                                <small class="text-muted">Generate a payment link for customers</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('merchant.transactions.index') }}" class="card border text-decoration-none text-dark">
                            <div class="card-body">
                                <i class="bi bi-credit-card-2-front text-primary fs-4"></i>
                                <h6 class="mt-2 mb-1">View Transactions</h6>
                                <small class="text-muted">Browse all payment transactions</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('merchant.integration.index') }}" class="card border text-decoration-none text-dark">
                            <div class="card-body">
                                <i class="bi bi-code-square text-primary fs-4"></i>
                                <h6 class="mt-2 mb-1">Integration Guide</h6>
                                <small class="text-muted">Get integration code for your app</small>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-6">
                        <a href="{{ route('merchant.api_keys.index') }}" class="card border text-decoration-none text-dark">
                            <div class="card-body">
                                <i class="bi bi-key text-primary fs-4"></i>
                                <h6 class="mt-2 mb-1">API Keys</h6>
                                <small class="text-muted">Manage your API credentials</small>
                            </div>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="stat-card">
                <h5 class="mb-3"><i class="bi bi-info-circle me-2"></i>Account Information</h5>
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">Webhook URL</small>
                    <code class="small d-block text-break">{{ $merchant->webhook_url ?? 'Not configured' }}</code>
                    @if(!$merchant->webhook_url)
                        <a href="{{ route('merchant.webhooks.index') }}" class="btn btn-sm btn-primary mt-2">Configure</a>
                    @endif
                </div>
                <div class="mb-3">
                    <small class="text-muted d-block mb-1">API Documentation</small>
                    <a href="/docs/api" class="btn btn-sm btn-outline-primary">View API Docs</a>
                </div>
                @if($merchant->onboarding_status !== 'completed')
                    <div class="alert alert-warning">
                        <i class="bi bi-exclamation-triangle"></i>
                        <strong>Onboarding Incomplete</strong>
                        <p class="mb-0 small">Complete your KYC to enable live mode.</p>
                        <a href="{{ route('merchant.onboarding.index') }}" class="btn btn-sm btn-warning mt-2">Complete Onboarding</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Recent Transactions -->
    <div class="row mt-4">
        <div class="col-md-12">
            <div class="stat-card">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Transactions</h5>
                    <a href="{{ route('merchant.transactions.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
                </div>
                <div ng-show="dc.loading" class="text-center py-5">
                    <div class="spinner-violet"></div>
                </div>
                <div ng-hide="dc.loading" class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Txn ID</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="txn in dc.recentTransactions">
                                <td><code>@{{ txn.txn_id }}</code></td>
                                <td><strong>@{{ txn.currency }} @{{ txn.amount | number:2 }}</strong></td>
                                <td><span class="badge bg-secondary">@{{ txn.payment_method | uppercase }}</span></td>
                                <td>
                                    <span class="badge" ng-class="{
                                        'bg-success': txn.status === 'success',
                                        'bg-danger': txn.status === 'failed',
                                        'bg-warning': txn.status === 'pending'
                                    }">@{{ txn.status | uppercase }}</span>
                                </td>
                                <td>@{{ txn.created_at | date:'MMM d, y HH:mm' }}</td>
                            </tr>
                            <tr ng-if="dc.recentTransactions.length === 0">
                                <td colspan="5" class="text-center text-muted py-4">No recent transactions</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('merchant.dashboard.angular.main_controller')
