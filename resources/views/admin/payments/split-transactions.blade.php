@extends('layouts.app-sidebar')

@section('title', 'Split Transactions - Admin - BadliCash')
@section('page-title', 'Split Transactions')

@section('content')
<div ng-app="badlicashApp" ng-controller="AdminSplitTransactionsController as astc">
    <x-breadcrumbs :items="[
        ['label'=>'Home','url'=>route('admin.dashboard')],
        ['label'=>'Split Transactions']
    ]" />

    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Split Transactions</h2>
            <p class="text-muted">List of Split Transactions</p>
        </div>
    </div>

    <!-- Date Range -->
    <div class="stat-card mb-3">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label">Select Date Range :</label>
                <input type="text" class="form-control" ng-model="astc.dateRange" placeholder="14/11/2025 00:00:00 - 29/11/2025 23:59:59">
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="stat-card mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <label class="form-label me-2">Show</label>
                <select class="form-select form-select-sm d-inline-block" style="width: auto;" ng-model="astc.pagination.per_page" ng-change="astc.loadTransactions()">
                    <option value="5">5 entries</option>
                    <option value="10">10 entries</option>
                    <option value="25">25 entries</option>
                    <option value="50">50 entries</option>
                </select>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-sm btn-outline-secondary" ng-click="astc.clearFilters()">
                    <i class="bi bi-funnel"></i> Clear Filters
                </button>
                <button class="btn btn-sm btn-outline-secondary" ng-click="astc.loadTransactions()">
                    <i class="bi bi-arrow-clockwise"></i> Reload
                </button>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-eye"></i> Columns
                    </button>
                    <ul class="dropdown-menu">
                        <li ng-repeat="(key, col) in astc.visibleColumns">
                            <a class="dropdown-item" href="#" ng-click="astc.toggleColumn(key)">
                                <i class="bi" ng-class="col.visible ? 'bi-check-square' : 'bi-square'"></i> @{{ col.label }}
                            </a>
                        </li>
                    </ul>
                </div>
                <button class="btn btn-sm btn-outline-secondary" ng-click="astc.resetView()">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="stat-card">
        <div ng-show="astc.loading" class="loader-overlay position-relative" style="min-height: 400px;">
            <div class="position-absolute top-50 start-50 translate-middle">
                <div class="spinner-violet"></div>
                <p class="mt-2 text-muted text-center">Loading split transactions...</p>
            </div>
        </div>

        <div ng-hide="astc.loading">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th ng-show="astc.visibleColumns.transaction_date.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Transaction Date</span>
                                </div>
                            </th>
                            <th ng-show="astc.visibleColumns.merchant_id.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Merchant Id</span>
                                </div>
                                <input type="text" class="form-control form-control-sm mt-1" placeholder="Filter..." ng-model="astc.filters.filter_merchant_id" ng-change="astc.applyFilters()">
                            </th>
                            <th ng-show="astc.visibleColumns.merchant_name.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Merchant Name</span>
                                </div>
                                <input type="text" class="form-control form-control-sm mt-1" placeholder="Filter..." ng-model="astc.filters.filter_merchant_name" ng-change="astc.applyFilters()">
                            </th>
                            <th ng-show="astc.visibleColumns.msac_code.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>MSAC Code</span>
                                </div>
                            </th>
                            <th ng-show="astc.visibleColumns.tran_id.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Tran Id</span>
                                </div>
                                <input type="text" class="form-control form-control-sm mt-1" placeholder="Filter..." ng-model="astc.filters.filter_transaction_id" ng-change="astc.applyFilters()">
                            </th>
                            <th ng-show="astc.visibleColumns.order_id.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Order Id</span>
                                </div>
                                <input type="text" class="form-control form-control-sm mt-1" placeholder="Filter..." ng-model="astc.filters.filter_order_id" ng-change="astc.applyFilters()">
                            </th>
                            <th ng-show="astc.visibleColumns.amount_paid_by_customer.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Amount Paid by Customer</span>
                                </div>
                            </th>
                            <th ng-show="astc.visibleColumns.account.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Account</span>
                                </div>
                            </th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-if="astc.transactions.length === 0">
                            <td colspan="9" class="text-center text-danger py-4">No matching records found</td>
                        </tr>
                        <tr ng-repeat="transaction in astc.transactions track by $index">
                            <td ng-show="astc.visibleColumns.transaction_date.visible">@{{ transaction.transaction_date }}</td>
                            <td ng-show="astc.visibleColumns.merchant_id.visible">@{{ transaction.merchant_id }}</td>
                            <td ng-show="astc.visibleColumns.merchant_name.visible">@{{ transaction.merchant_name }}</td>
                            <td ng-show="astc.visibleColumns.msac_code.visible">@{{ transaction.msac_code }}</td>
                            <td ng-show="astc.visibleColumns.tran_id.visible">@{{ transaction.tran_id }}</td>
                            <td ng-show="astc.visibleColumns.order_id.visible">@{{ transaction.order_id }}</td>
                            <td ng-show="astc.visibleColumns.amount_paid_by_customer.visible">@{{ transaction.amount_paid_by_customer }}</td>
                            <td ng-show="astc.visibleColumns.account.visible">@{{ transaction.account }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" ng-click="astc.viewTransaction(transaction)">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing @{{ (astc.pagination.current_page - 1) * astc.pagination.per_page + 1 }} to @{{ Math.min(astc.pagination.current_page * astc.pagination.per_page, astc.pagination.total) }} of @{{ astc.pagination.total }} entries
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-secondary" 
                            ng-click="astc.changePage(astc.pagination.current_page - 1)" 
                            ng-disabled="astc.pagination.current_page === 1">
                        Previous
                    </button>
                    <span class="mx-2">...</span>
                    <button class="btn btn-sm btn-outline-secondary" 
                            ng-click="astc.changePage(astc.pagination.current_page + 1)" 
                            ng-disabled="astc.pagination.current_page === astc.pagination.last_page">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function() {
    'use strict';
    function registerController() {
        if (typeof angular === 'undefined') {
            setTimeout(registerController, 50);
            return;
        }
        try {
            var app = angular.module('badlicashApp');
            app.controller('AdminSplitTransactionsController', ['$http', function($http) {
                var vm = this;
                vm.transactions = [];
                vm.pagination = { current_page: 1, per_page: 5, total: 0, last_page: 1 };
                vm.filters = {};
                vm.loading = false;
                vm.dateRange = '';
                vm.sortColumn = 'id';
                vm.sortDirection = 'desc';
                
                vm.visibleColumns = {
                    transaction_date: { visible: true, label: 'Transaction Date' },
                    merchant_id: { visible: true, label: 'Merchant Id' },
                    merchant_name: { visible: true, label: 'Merchant Name' },
                    msac_code: { visible: true, label: 'MSAC Code' },
                    tran_id: { visible: true, label: 'Tran Id' },
                    order_id: { visible: true, label: 'Order Id' },
                    amount_paid_by_customer: { visible: true, label: 'Amount Paid by Customer' },
                    account: { visible: true, label: 'Account' }
                };

                vm.loadTransactions = function() {
                    vm.loading = true;
                    var params = {
                        page: vm.pagination.current_page,
                        per_page: vm.pagination.per_page,
                        date_range: vm.dateRange,
                        sort_by: vm.sortColumn,
                        sort_direction: vm.sortDirection
                    };

                    Object.keys(vm.filters).forEach(function(key) {
                        if (vm.filters[key]) {
                            params[key] = vm.filters[key];
                        }
                    });
                    
                    $http.get('/admin/payments/split-transactions/data', { params: params }).then(function(response) {
                        vm.transactions = response.data.data || [];
                        vm.pagination = {
                            current_page: response.data.pagination.current_page,
                            last_page: response.data.pagination.last_page,
                            total: response.data.pagination.total,
                            per_page: response.data.pagination.per_page
                        };
                        vm.loading = false;
                    }, function(error) {
                        vm.loading = false;
                        console.error('Error loading split transactions:', error);
                    });
                };

                vm.changePage = function(page) {
                    if (page >= 1 && page <= vm.pagination.last_page) {
                        vm.pagination.current_page = page;
                        vm.loadTransactions();
                    }
                };

                vm.applyFilters = function() {
                    vm.pagination.current_page = 1;
                    vm.loadTransactions();
                };

                vm.clearFilters = function() {
                    vm.filters = {};
                    vm.dateRange = '';
                    vm.applyFilters();
                };

                vm.toggleColumn = function(key) {
                    if (vm.visibleColumns.hasOwnProperty(key)) {
                        vm.visibleColumns[key].visible = !vm.visibleColumns[key].visible;
                    }
                };

                vm.resetView = function() {
                    Object.keys(vm.visibleColumns).forEach(function(key) {
                        vm.visibleColumns[key].visible = true;
                    });
                    vm.clearFilters();
                };

                vm.viewTransaction = function(transaction) {
                    alert('View transaction: ' + transaction.tran_id);
                };

                vm.loadTransactions();
            }]);
        } catch(e) {
            setTimeout(registerController, 50);
        }
    }
    if (typeof angular !== 'undefined') {
        registerController();
    } else {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', registerController);
        } else {
            registerController();
        }
    }
})();
</script>
@endpush

