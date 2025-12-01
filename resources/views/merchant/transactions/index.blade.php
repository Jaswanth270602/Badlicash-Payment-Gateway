@extends('layouts.app-sidebar')

@section('title','Transactions - BadliCash')
@section('page-title','Transactions')

@section('content')
<div ng-app="badlicashApp" ng-controller="TransactionsController as tc">
    <div class="stat-card mb-3">
        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Status</label>
                <select class="form-select" ng-model="tc.filters.status" ng-change="tc.applyFilters()">
                    <option value="">All</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                    <option value="pending">Pending</option>
                    <option value="initiated">Initiated</option>
                </select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Payment Method</label>
                <select class="form-select" ng-model="tc.filters.payment_method" ng-change="tc.applyFilters()">
                    <option value="">All</option>
                    <option value="card">Card</option>
                    <option value="upi">UPI</option>
                    <option value="netbanking">Net Banking</option>
                </select>
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" ng-model="tc.filters.from_date" ng-change="tc.applyFilters()">
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" ng-model="tc.filters.to_date" ng-change="tc.applyFilters()">
            </div>
            <div class="col-md-12 col-lg-6">
                <label class="form-label">Search</label>
                <input class="form-control" placeholder="Search by transaction ID, order ID, or description" ng-model="tc.filters.search" ng-change="tc.applyFilters()">
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Per Page</label>
                <select class="form-select" ng-model="tc.perPage" ng-change="tc.applyFilters()">
                    <option value="10">10</option>
                    <option value="25">25</option>
                    <option value="50">50</option>
                    <option value="100">100</option>
                </select>
            </div>
            <div class="col-md-6 col-lg-3 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100" ng-click="tc.clearFilters()">
                    <i class="bi bi-x-circle"></i> Clear Filters
                </button>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div ng-show="tc.loading" class="loader-overlay position-relative" style="min-height: 400px;">
            <div class="position-absolute top-50 start-50 translate-middle">
                <div class="spinner-violet"></div>
                <p class="mt-2 text-muted text-center">Loading transactions...</p>
            </div>
        </div>
        <div ng-hide="tc.loading" class="table-responsive">
            <table class="table table-hover">
                <thead>
                <tr>
                    <th>#</th>
                    <th>Txn ID</th>
                    <th>Order ID</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Date</th>
                </tr>
                </thead>
                <tbody>
                <tr ng-repeat="t in tc.transactions track by $index">
                    <td>@{{ (tc.pagination.current_page - 1) * tc.pagination.per_page + $index + 1 }}</td>
                    <td><code>@{{ t.txn_id }}</code></td>
                    <td><code>@{{ (t.order && t.order.order_id) || 'N/A' }}</code></td>
                    <td>
                        <div ng-if="t.order && t.order.customer_details">@{{ t.order.customer_details.name || 'N/A' }}</div>
                        <small class="text-muted" ng-if="t.order && t.order.customer_details">@{{ t.order.customer_details.email }}</small>
                        <span ng-if="!t.order || !t.order.customer_details" class="text-muted">N/A</span>
                    </td>
                    <td><strong>@{{ t.currency || 'INR' }} @{{ t.amount | number:2 }}</strong></td>
                    <td><span class="badge bg-secondary">@{{ t.payment_method | uppercase }}</span></td>
                    <td>
                        <span class="badge" ng-class="{'bg-success': t.status==='success', 'bg-danger': t.status==='failed', 'bg-warning': t.status==='pending', 'bg-secondary': t.status==='initiated'}">@{{ t.status | uppercase }}</span>
                    </td>
                    <td>@{{ t.created_at | date:'MMM d, y HH:mm' }}</td>
                </tr>
                <tr ng-if="tc.transactions.length===0 && !tc.loading">
                    <td colspan="8" class="text-center text-muted py-4">
                        <i class="bi bi-inbox" style="font-size: 48px;"></i>
                        <p class="mt-2">No transactions found</p>
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        <x-pagination />
    </div>
</div>
@endsection

@include('merchant.transactions.angular.main_controller')
