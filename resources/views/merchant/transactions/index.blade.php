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
                    <th>Source</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Method</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
                </thead>
                <tbody>
                <tr ng-repeat="t in tc.transactions track by $index">
                    <td>@{{ (tc.pagination.current_page - 1) * tc.pagination.per_page + $index + 1 }}</td>
                    <td>
                        <code class="text-primary" style="font-size: 12px;">@{{ t.transaction_id || t.txn_id }}</code>
                    </td>
                    <td>
                        <code class="text-info" style="font-size: 12px;">@{{ (t.order && t.order.order_id) || 'N/A' }}</code>
                    </td>
                    <td>
                        <span ng-if="t.order && t.order.payment_link_id" class="badge bg-primary">
                            <i class="bi bi-link-45deg"></i> Payment Link
                        </span>
                        <span ng-if="!t.order || !t.order.payment_link_id" class="badge bg-secondary">
                            <i class="bi bi-cart"></i> Direct
                        </span>
                    </td>
                    <td>
                        <div ng-if="t.customer_email || (t.order && t.order.customer_details)">
                            <div class="fw-semibold" style="font-size: 13px;">@{{ t.customer_email || (t.order && t.order.customer_details.name) || 'N/A' }}</div>
                            <small class="text-muted">@{{ t.customer_phone || (t.order && t.order.customer_details.phone) || '' }}</small>
                        </div>
                        <span ng-if="!t.customer_email && (!t.order || !t.order.customer_details)" class="text-muted">N/A</span>
                    </td>
                    <td>
                        <strong class="text-success">@{{ t.currency || 'INR' }} @{{ t.amount | number:2 }}</strong>
                        <div ng-if="t.fee_amount" style="font-size: 11px; color: #94a3b8;">Fee: @{{ t.currency }} @{{ t.fee_amount | number:2 }}</div>
                    </td>
                    <td>
                        <span class="badge" style="background: #6366f1;">@{{ t.payment_method | uppercase }}</span>
                        <div ng-if="t.payment_details && t.payment_details.card_number" style="font-size: 11px; color: #64748b; margin-top: 4px;">
                            **** @{{ t.payment_details.card_number }}
                        </div>
                    </td>
                    <td>
                        <span class="badge" ng-class="{
                            'bg-success': t.status==='success' || t.status==='completed',
                            'bg-danger': t.status==='failed',
                            'bg-warning text-dark': t.status==='pending',
                            'bg-info': t.status==='processing',
                            'bg-secondary': t.status==='initiated'
                        }">
                            @{{ t.status | uppercase }}
                        </span>
                        <div ng-if="t.status==='failed' && t.failure_reason" class="mt-1" style="font-size: 11px; color: #dc3545; max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="@{{ t.failure_reason }}">
                            <i class="bi bi-exclamation-circle"></i> @{{ t.failure_reason }}
                        </div>
                    </td>
                    <td style="white-space: nowrap;">
                        <div style="font-size: 13px;">@{{ t.created_at | date:'MMM d, y' }}</div>
                        <small class="text-muted">@{{ t.created_at | date:'HH:mm:ss' }}</small>
                    </td>
                    <td>
                        <button class="btn btn-sm btn-outline-primary" ng-click="tc.viewDetails(t)" title="View Details">
                            <i class="bi bi-eye"></i>
                        </button>
                    </td>
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

    <!-- Transaction Details Modal -->
    <div class="modal fade" id="transactionDetailsModal" tabindex="-1" aria-labelledby="transactionDetailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header" style="background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%); color: white;">
                    <h5 class="modal-title" id="transactionDetailsModalLabel">
                        <i class="bi bi-receipt"></i> Transaction Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body" ng-if="tc.selectedTransaction">
                    <div class="row g-3">
                        <!-- Transaction Status Card -->
                        <div class="col-12">
                            <div class="alert" ng-class="{
                                'alert-success': tc.selectedTransaction.status==='success' || tc.selectedTransaction.status==='completed',
                                'alert-danger': tc.selectedTransaction.status==='failed',
                                'alert-warning': tc.selectedTransaction.status==='pending',
                                'alert-info': tc.selectedTransaction.status==='processing' || tc.selectedTransaction.status==='initiated'
                            }" role="alert">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="mb-1">
                                            <i class="bi" ng-class="{
                                                'bi-check-circle-fill': tc.selectedTransaction.status==='success' || tc.selectedTransaction.status==='completed',
                                                'bi-x-circle-fill': tc.selectedTransaction.status==='failed',
                                                'bi-clock-fill': tc.selectedTransaction.status==='pending' || tc.selectedTransaction.status==='initiated',
                                                'bi-arrow-repeat': tc.selectedTransaction.status==='processing'
                                            }"></i>
                                            Transaction @{{ tc.selectedTransaction.status | uppercase }}
                                        </h6>
                                        <small>@{{ tc.selectedTransaction.created_at | date:'MMM d, y - HH:mm:ss' }}</small>
                                    </div>
                                    <div class="text-end">
                                        <h4 class="mb-0">@{{ tc.selectedTransaction.currency }} @{{ tc.selectedTransaction.amount | number:2 }}</h4>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transaction Information -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="bi bi-info-circle"></i> Transaction Info</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted" style="width: 40%;">Transaction ID:</td>
                                            <td><code class="text-primary">@{{ tc.selectedTransaction.txn_id || tc.selectedTransaction.transaction_id }}</code></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Order ID:</td>
                                            <td><code class="text-info">@{{ (tc.selectedTransaction.order && tc.selectedTransaction.order.order_id) || 'N/A' }}</code></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Payment Method:</td>
                                            <td><span class="badge" style="background: #6366f1;">@{{ tc.selectedTransaction.payment_method | uppercase }}</span></td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted">Source:</td>
                                            <td>
                                                <span ng-if="tc.selectedTransaction.order && tc.selectedTransaction.order.payment_link_id" class="badge bg-primary">
                                                    <i class="bi bi-link-45deg"></i> Payment Link
                                                </span>
                                                <span ng-if="!tc.selectedTransaction.order || !tc.selectedTransaction.order.payment_link_id" class="badge bg-secondary">
                                                    <i class="bi bi-cart"></i> Direct Order
                                                </span>
                                            </td>
                                        </tr>
                                        <tr ng-if="tc.selectedTransaction.test_mode">
                                            <td class="text-muted">Mode:</td>
                                            <td><span class="badge bg-warning text-dark">TEST MODE</span></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Customer Information -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="bi bi-person"></i> Customer Info</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr ng-if="tc.selectedTransaction.order && tc.selectedTransaction.order.customer_details && tc.selectedTransaction.order.customer_details.name">
                                            <td class="text-muted" style="width: 40%;">Name:</td>
                                            <td>@{{ tc.selectedTransaction.order.customer_details.name }}</td>
                                        </tr>
                                        <tr ng-if="tc.selectedTransaction.order && tc.selectedTransaction.order.customer_details && tc.selectedTransaction.order.customer_details.email">
                                            <td class="text-muted">Email:</td>
                                            <td>@{{ tc.selectedTransaction.order.customer_details.email }}</td>
                                        </tr>
                                        <tr ng-if="tc.selectedTransaction.order && tc.selectedTransaction.order.customer_details && tc.selectedTransaction.order.customer_details.phone">
                                            <td class="text-muted">Phone:</td>
                                            <td>@{{ tc.selectedTransaction.order.customer_details.phone }}</td>
                                        </tr>
                                        <tr ng-if="tc.selectedTransaction.ip_address">
                                            <td class="text-muted">IP Address:</td>
                                            <td><code>@{{ tc.selectedTransaction.ip_address }}</code></td>
                                        </tr>
                                        <tr ng-if="!tc.selectedTransaction.order || !tc.selectedTransaction.order.customer_details">
                                            <td colspan="2" class="text-muted">No customer information available</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Amount Breakdown -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="bi bi-cash-stack"></i> Amount Breakdown</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm mb-0">
                                        <tr>
                                            <td class="text-muted">Transaction Amount:</td>
                                            <td class="text-end"><strong class="text-success">@{{ tc.selectedTransaction.currency }} @{{ tc.selectedTransaction.amount | number:2 }}</strong></td>
                                        </tr>
                                        <tr ng-if="tc.selectedTransaction.fee_amount">
                                            <td class="text-muted">Processing Fee:</td>
                                            <td class="text-end text-danger">- @{{ tc.selectedTransaction.currency }} @{{ tc.selectedTransaction.fee_amount | number:2 }}</td>
                                        </tr>
                                        <tr ng-if="tc.selectedTransaction.net_amount" class="border-top">
                                            <td class="text-muted"><strong>Net Amount:</strong></td>
                                            <td class="text-end"><strong class="text-primary">@{{ tc.selectedTransaction.currency }} @{{ tc.selectedTransaction.net_amount | number:2 }}</strong></td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Payment Details -->
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="bi bi-credit-card"></i> Payment Details</h6>
                                </div>
                                <div class="card-body">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr ng-if="tc.selectedTransaction.payment_details && tc.selectedTransaction.payment_details.last4">
                                            <td class="text-muted" style="width: 40%;">Card:</td>
                                            <td>**** **** **** @{{ tc.selectedTransaction.payment_details.last4 }}</td>
                                        </tr>
                                        <tr ng-if="tc.selectedTransaction.payment_details && tc.selectedTransaction.payment_details.card_type">
                                            <td class="text-muted">Card Type:</td>
                                            <td>@{{ tc.selectedTransaction.payment_details.card_type | uppercase }}</td>
                                        </tr>
                                        <tr ng-if="tc.selectedTransaction.payment_details && tc.selectedTransaction.payment_details.upi_id">
                                            <td class="text-muted">UPI ID:</td>
                                            <td>@{{ tc.selectedTransaction.payment_details.upi_id }}</td>
                                        </tr>
                                        <tr ng-if="tc.selectedTransaction.gateway_txn_id">
                                            <td class="text-muted">Gateway Txn ID:</td>
                                            <td><code>@{{ tc.selectedTransaction.gateway_txn_id }}</code></td>
                                        </tr>
                                        <tr ng-if="tc.selectedTransaction.captured_at">
                                            <td class="text-muted">Captured At:</td>
                                            <td>@{{ tc.selectedTransaction.captured_at | date:'MMM d, y HH:mm:ss' }}</td>
                                        </tr>
                                        <tr ng-if="!tc.selectedTransaction.payment_details || Object.keys(tc.selectedTransaction.payment_details).length === 0">
                                            <td colspan="2" class="text-muted">No payment details available</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Order Description -->
                        <div class="col-12" ng-if="tc.selectedTransaction.order && tc.selectedTransaction.order.description">
                            <div class="card">
                                <div class="card-header bg-light">
                                    <h6 class="mb-0"><i class="bi bi-file-text"></i> Description</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-0">@{{ tc.selectedTransaction.order.description }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Gateway Response (if failed) -->
                        <div class="col-12" ng-if="tc.selectedTransaction.status === 'failed'">
                            <div class="card border-danger">
                                <div class="card-header bg-danger text-white">
                                    <h6 class="mb-0"><i class="bi bi-exclamation-triangle"></i> Failure Details</h6>
                                </div>
                                <div class="card-body">
                                    <p class="mb-2 text-danger fw-semibold" ng-if="tc.selectedTransaction.failure_reason">
                                        @{{ tc.selectedTransaction.failure_reason }}
                                    </p>
                                    <p class="mb-0 text-danger" ng-if="!tc.selectedTransaction.failure_reason && tc.selectedTransaction.gateway_response && tc.selectedTransaction.gateway_response.message">
                                        @{{ tc.selectedTransaction.gateway_response.message }}
                                    </p>
                                    <p class="mb-0 text-danger" ng-if="!tc.selectedTransaction.failure_reason && (!tc.selectedTransaction.gateway_response || !tc.selectedTransaction.gateway_response.message)">
                                        Payment failed
                                    </p>
                                    <small class="text-muted d-block mt-2" ng-if="tc.selectedTransaction.gateway_response && tc.selectedTransaction.gateway_response.error_code">
                                        Error Code: @{{ tc.selectedTransaction.gateway_response.error_code }}
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x"></i> Close
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('merchant.transactions.angular.main_controller')
