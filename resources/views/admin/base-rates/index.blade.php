@extends('layouts.app-sidebar')

@section('title', 'Base Rates Management - Admin - ' . config('app.name'))
@section('page-title', 'Base Rates Configuration')

@section('content')
<div ng-app="badlicashApp" ng-controller="BaseRatesController as brc">
    <x-breadcrumbs :items="[
        ['label'=>'Home','url'=>route('admin.dashboard')],
        ['label'=>'Base Rates']
    ]" />

    <div class="row mb-4">
        <div class="col-md-12">
            <h2>Base Rates Management</h2>
            <p class="text-muted">Configure base rates for banks, merchants, receivers, and pricers</p>
        </div>
    </div>

    <!-- Filters and Actions -->
    <div class="stat-card mb-3">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Rate Type</label>
                <select class="form-select" ng-model="brc.filters.rate_type" ng-change="brc.applyFilters()">
                    <option value="all">All Types</option>
                    <option value="bank">Bank</option>
                    <option value="merchant">Merchant</option>
                    <option value="receiver">Receiver</option>
                    <option value="pricer">Pricer</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Payment Method</label>
                <select class="form-select" ng-model="brc.filters.payment_method" ng-change="brc.applyFilters()">
                    <option value="all">All Methods</option>
                    <option value="card">Card</option>
                    <option value="upi">UPI</option>
                    <option value="netbanking">Net Banking</option>
                    <option value="wallet">Wallet</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Service Type</label>
                <select class="form-select" ng-model="brc.filters.service_type" ng-change="brc.applyFilters()">
                    <option value="all">All Services</option>
                    <option value="payment">Payment</option>
                    <option value="refund">Refund</option>
                    <option value="chargeback">Chargeback</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" ng-model="brc.filters.is_active" ng-change="brc.applyFilters()">
                    <option value="">All</option>
                    <option value="true">Active</option>
                    <option value="false">Inactive</option>
                </select>
            </div>
            <div class="col-md-6">
                <label class="form-label">Search</label>
                <input type="text" class="form-control" ng-model="brc.filters.search" ng-change="brc.applyFilters()" placeholder="Search by rate type, payment method...">
            </div>
            <div class="col-md-6 d-flex align-items-end gap-2">
                <button class="btn btn-outline-secondary" ng-click="brc.clearFilters()">
                    <i class="bi bi-x-circle"></i> Clear
                </button>
                <button class="btn btn-primary" ng-click="brc.openNewModal()">
                    <i class="bi bi-plus-lg"></i> New Base Rate
                </button>
            </div>
        </div>
    </div>

    <!-- Rates Table -->
    <div class="stat-card">
        <div ng-show="brc.loading" class="loader-overlay position-relative" style="min-height: 400px;">
            <div class="position-absolute top-50 start-50 translate-middle">
                <div class="spinner-violet"></div>
                <p class="mt-2 text-muted text-center">Loading base rates...</p>
            </div>
        </div>

        <div ng-hide="brc.loading">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Rate Type</th>
                            <th>Entity</th>
                            <th>Payment Method</th>
                            <th>Service Type</th>
                            <th>Transaction Type</th>
                            <th>Percentage Fee</th>
                            <th>Flat Fee</th>
                            <th>Status</th>
                            <th>Effective Period</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="rate in brc.rates">
                            <td>
                                <span class="badge" ng-class="{
                                    'bg-primary': rate.rate_type === 'bank',
                                    'bg-success': rate.rate_type === 'merchant',
                                    'bg-info': rate.rate_type === 'receiver',
                                    'bg-warning': rate.rate_type === 'pricer'
                                }">
                                    @{{ rate.rate_type | uppercase }}
                                </span>
                            </td>
                            <td>
                                <div ng-if="rate.merchant">
                                    <strong>@{{ rate.merchant.name }}</strong>
                                    <small class="text-muted d-block">@{{ rate.merchant.email }}</small>
                                </div>
                                <div ng-if="rate.bank">
                                    <strong>@{{ rate.bank.name }}</strong>
                                    <small class="text-muted d-block">@{{ rate.bank.code }}</small>
                                </div>
                                <span ng-if="!rate.merchant && !rate.bank" class="text-muted">-</span>
                            </td>
                            <td><span class="badge bg-secondary">@{{ rate.payment_method | uppercase }}</span></td>
                            <td>@{{ rate.service_type }}</td>
                            <td>
                                <span class="badge" ng-class="rate.transaction_type === 'domestic' ? 'bg-success' : 'bg-warning'">
                                    @{{ rate.transaction_type | uppercase }}
                                </span>
                            </td>
                            <td><strong>@{{ rate.percentage_fee }}%</strong></td>
                            <td><strong>₹@{{ rate.flat_fee }}</strong></td>
                            <td>
                                <span class="badge" ng-class="rate.is_active ? 'bg-success' : 'bg-secondary'">
                                    @{{ rate.is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td>
                                <small>
                                    <div ng-if="rate.effective_from">From: @{{ rate.effective_from | date:'MMM d, y' }}</div>
                                    <div ng-if="rate.effective_to">To: @{{ rate.effective_to | date:'MMM d, y' }}</div>
                                    <span ng-if="!rate.effective_from && !rate.effective_to" class="text-muted">Always active</span>
                                </small>
                            </td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" ng-click="brc.editRate(rate)" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" ng-click="brc.deleteRate(rate)" title="Delete">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </td>
                        </tr>
                        <tr ng-if="brc.rates.length === 0 && !brc.loading">
                            <td colspan="10" class="text-center text-muted py-4">
                                <i class="bi bi-inbox" style="font-size: 48px;"></i>
                                <p class="mt-2">No base rates found</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing @{{ (brc.pagination.current_page - 1) * brc.pagination.per_page + 1 }} to @{{ Math.min(brc.pagination.current_page * brc.pagination.per_page, brc.pagination.total) }} of @{{ brc.pagination.total }} entries
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-secondary" 
                            ng-click="brc.changePage(brc.pagination.current_page - 1)" 
                            ng-disabled="brc.pagination.current_page === 1">
                        Previous
                    </button>
                    <span class="mx-2">Page @{{ brc.pagination.current_page }} of @{{ brc.pagination.last_page }}</span>
                    <button class="btn btn-sm btn-outline-secondary" 
                            ng-click="brc.changePage(brc.pagination.current_page + 1)" 
                            ng-disabled="brc.pagination.current_page === brc.pagination.last_page">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- New/Edit Base Rate Modal -->
    <div class="modal fade" id="baseRateModal" tabindex="-1" aria-labelledby="baseRateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="baseRateModalLabel">
                        <i class="bi bi-percent"></i> @{{ brc.isEditing ? 'Edit' : 'New' }} Base Rate
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form id="baseRateForm">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Rate Type <span class="text-danger">*</span></label>
                                <select class="form-select" ng-model="brc.rateForm.rate_type" ng-change="brc.onRateTypeChange()" required>
                                    <option value="">Select Type</option>
                                    <option value="bank">Bank</option>
                                    <option value="merchant">Merchant</option>
                                    <option value="receiver">Receiver</option>
                                    <option value="pricer">Pricer</option>
                                </select>
                            </div>
                            <div class="col-md-6" ng-if="brc.rateForm.rate_type">
                                <label class="form-label">Entity <span class="text-danger">*</span></label>
                                <select class="form-select" ng-model="brc.rateForm.entity_id" ng-disabled="!brc.entities.length" required>
                                    <option value="">Select @{{ brc.rateForm.rate_type }}</option>
                                    <option ng-repeat="entity in brc.entities" value="@{{ entity.id }}">@{{ entity.name }}</option>
                                </select>
                                <small class="text-muted" ng-if="!brc.entities.length">Loading entities...</small>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Payment Method <span class="text-danger">*</span></label>
                                <select class="form-select" ng-model="brc.rateForm.payment_method" required>
                                    <option value="">Select Method</option>
                                    <option value="card">Card</option>
                                    <option value="upi">UPI</option>
                                    <option value="netbanking">Net Banking</option>
                                    <option value="wallet">Wallet</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Service Type <span class="text-danger">*</span></label>
                                <select class="form-select" ng-model="brc.rateForm.service_type" required>
                                    <option value="payment">Payment</option>
                                    <option value="refund">Refund</option>
                                    <option value="chargeback">Chargeback</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Transaction Type <span class="text-danger">*</span></label>
                                <select class="form-select" ng-model="brc.rateForm.transaction_type" required>
                                    <option value="domestic">Domestic</option>
                                    <option value="international">International</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Percentage Fee (%) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" ng-model="brc.rateForm.percentage_fee" step="0.001" min="0" max="100" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Flat Fee (INR) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" ng-model="brc.rateForm.flat_fee" step="0.01" min="0" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">GST Percentage (%)</label>
                                <input type="number" class="form-control" ng-model="brc.rateForm.gst_percentage" step="0.01" min="0" max="100" value="18">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Status</label>
                                <select class="form-select" ng-model="brc.rateForm.is_active">
                                    <option value="true">Active</option>
                                    <option value="false">Inactive</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Effective From</label>
                                <input type="date" class="form-control" ng-model="brc.rateForm.effective_from">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Effective To</label>
                                <input type="date" class="form-control" ng-model="brc.rateForm.effective_to">
                            </div>
                            <div class="col-12">
                                <label class="form-label">Notes</label>
                                <textarea class="form-control" ng-model="brc.rateForm.notes" rows="3"></textarea>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-primary" ng-click="brc.saveRate()" ng-disabled="brc.saving">
                        <span ng-if="brc.saving" class="spinner-border spinner-border-sm me-1"></span>
                        <i class="bi bi-check-lg" ng-if="!brc.saving"></i> @{{ brc.isEditing ? 'Update' : 'Create' }} Rate
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
            app.controller('BaseRatesController', ['$http', '$scope', function($http, $scope) {
                var vm = this;
                var csrf = document.querySelector('meta[name="csrf-token"]').content;
                vm.rates = [];
                vm.pagination = { current_page: 1, per_page: 15, total: 0, last_page: 1 };
                vm.loading = false;
                vm.saving = false;
                vm.isEditing = false;
                vm.entities = [];
                vm.filters = {
                    rate_type: 'all',
                    payment_method: 'all',
                    service_type: 'all',
                    is_active: '',
                    search: ''
                };

                vm.rateForm = {
                    rate_type: '',
                    entity_type: '',
                    entity_id: null,
                    payment_method: 'card',
                    service_type: 'payment',
                    transaction_type: 'domestic',
                    percentage_fee: 0,
                    flat_fee: 0,
                    gst_percentage: 18,
                    is_active: true,
                    effective_from: null,
                    effective_to: null,
                    notes: ''
                };

                vm.loadRates = function() {
                    vm.loading = true;
                    var params = {
                        page: vm.pagination.current_page,
                        per_page: vm.pagination.per_page
                    };

                    Object.keys(vm.filters).forEach(function(key) {
                        if (vm.filters[key] && vm.filters[key] !== 'all') {
                            params[key] = vm.filters[key];
                        }
                    });

                    $http.get('/admin/base-rates/data', { params: params }).then(function(response) {
                        vm.rates = response.data.data || [];
                        vm.pagination = {
                            current_page: response.data.pagination.current_page,
                            last_page: response.data.pagination.last_page,
                            total: response.data.pagination.total,
                            per_page: response.data.pagination.per_page
                        };
                        vm.loading = false;
                    }, function(error) {
                        vm.loading = false;
                        console.error('Error loading rates:', error);
                        alert('Failed to load base rates');
                    });
                };

                vm.changePage = function(page) {
                    if (page >= 1 && page <= vm.pagination.last_page) {
                        vm.pagination.current_page = page;
                        vm.loadRates();
                    }
                };

                vm.applyFilters = function() {
                    vm.pagination.current_page = 1;
                    vm.loadRates();
                };

                vm.clearFilters = function() {
                    vm.filters = {
                        rate_type: 'all',
                        payment_method: 'all',
                        service_type: 'all',
                        is_active: '',
                        search: ''
                    };
                    vm.applyFilters();
                };

                vm.onRateTypeChange = function() {
                    if (vm.rateForm.rate_type && (vm.rateForm.rate_type === 'merchant' || vm.rateForm.rate_type === 'bank')) {
                        vm.rateForm.entity_type = vm.rateForm.rate_type;
                        $http.get('/admin/base-rates/entities', { params: { type: vm.rateForm.rate_type } }).then(function(response) {
                            vm.entities = response.data.data || [];
                        });
                    } else {
                        vm.entities = [];
                        vm.rateForm.entity_id = null;
                    }
                };

                vm.openNewModal = function() {
                    vm.isEditing = false;
                    vm.rateForm = {
                        rate_type: '',
                        entity_type: '',
                        entity_id: null,
                        payment_method: 'card',
                        service_type: 'payment',
                        transaction_type: 'domestic',
                        percentage_fee: 0,
                        flat_fee: 0,
                        gst_percentage: 18,
                        is_active: true,
                        effective_from: null,
                        effective_to: null,
                        notes: ''
                    };
                    vm.entities = [];
                    var modal = new bootstrap.Modal(document.getElementById('baseRateModal'));
                    modal.show();
                };

                vm.editRate = function(rate) {
                    vm.isEditing = true;
                    vm.rateForm = {
                        id: rate.id,
                        rate_type: rate.rate_type,
                        entity_type: rate.entity_type,
                        entity_id: rate.entity_id,
                        payment_method: rate.payment_method,
                        service_type: rate.service_type,
                        transaction_type: rate.transaction_type,
                        percentage_fee: parseFloat(rate.percentage_fee),
                        flat_fee: parseFloat(rate.flat_fee),
                        gst_percentage: parseFloat(rate.gst_percentage || 18),
                        is_active: rate.is_active,
                        effective_from: rate.effective_from,
                        effective_to: rate.effective_to,
                        notes: rate.notes || ''
                    };
                    vm.onRateTypeChange();
                    var modal = new bootstrap.Modal(document.getElementById('baseRateModal'));
                    modal.show();
                };

                vm.saveRate = function() {
                    if (!vm.rateForm.rate_type || !vm.rateForm.payment_method || !vm.rateForm.service_type) {
                        if (typeof showToast === 'function') {
                            showToast('Please fill in all required fields', 'error');
                        } else {
                            alert('Please fill in all required fields');
                        }
                        return;
                    }

                    vm.saving = true;
                    var url = vm.isEditing ? '/admin/base-rates/' + vm.rateForm.id : '/admin/base-rates';
                    var method = vm.isEditing ? 'POST' : 'POST';

                    $http({
                        method: method,
                        url: url,
                        data: vm.rateForm,
                        headers: { 'X-CSRF-TOKEN': csrf }
                    }).then(function(response) {
                        vm.saving = false;
                        if (response.data.success) {
                            var modal = bootstrap.Modal.getInstance(document.getElementById('baseRateModal'));
                            modal.hide();
                            var successMsg = vm.isEditing ? 'Base rate updated successfully' : 'Base rate created successfully';
                            if (typeof showToast === 'function') {
                                showToast(successMsg, 'success');
                            } else {
                                alert(successMsg);
                            }
                            vm.loadRates();
                        } else {
                            var errorMsg = response.data.message || 'Failed to save base rate';
                            if (response.data.errors) {
                                var errors = Object.values(response.data.errors).flat();
                                errorMsg = errors.join(', ');
                            }
                            if (typeof showToast === 'function') {
                                showToast(errorMsg, 'error');
                            } else {
                                alert(errorMsg);
                            }
                        }
                    }, function(error) {
                        vm.saving = false;
                        var errorMsg = 'Failed to save base rate';
                        if (error.data && error.data.message) {
                            errorMsg = error.data.message;
                        } else if (error.data && error.data.errors) {
                            var errors = Object.values(error.data.errors).flat();
                            errorMsg = errors.join(', ');
                        }
                        if (typeof showToast === 'function') {
                            showToast(errorMsg, 'error');
                        } else {
                            alert(errorMsg);
                        }
                    });
                };

                vm.deleteRate = function(rate) {
                    if (!confirm('Are you sure you want to delete this base rate?')) {
                        return;
                    }

                    $http.delete('/admin/base-rates/' + rate.id, {
                        headers: { 'X-CSRF-TOKEN': csrf }
                    }).then(function(response) {
                        if (response.data.success) {
                            if (typeof showToast === 'function') {
                                showToast('Base rate deleted successfully', 'success');
                            } else {
                                alert('Base rate deleted successfully');
                            }
                            vm.loadRates();
                        } else {
                            var errorMsg = 'Failed to delete base rate: ' + (response.data.message || 'Unknown error');
                            if (typeof showToast === 'function') {
                                showToast(errorMsg, 'error');
                            } else {
                                alert(errorMsg);
                            }
                        }
                    }, function(error) {
                        if (typeof showToast === 'function') {
                            showToast('Failed to delete base rate', 'error');
                        } else {
                            alert('Failed to delete base rate');
                        }
                        console.error('Error:', error);
                    });
                };

                vm.loadRates();
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

