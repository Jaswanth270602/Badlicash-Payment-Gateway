@extends('layouts.app-sidebar')

@section('title','Disputes - BadliCash')
@section('page-title','Disputes')

@section('content')
<div ng-app="badlicashApp" ng-controller="MerchantDisputesController as mdc">
    <div class="stat-card mb-3">
        <div class="row g-3">
            <div class="col-md-4">
                <label class="form-label">Status</label>
                <select class="form-select" ng-model="mdc.filters.status" ng-change="mdc.load()">
                    <option value="">All</option>
                    <option value="open">Open</option>
                    <option value="needs_evidence">Needs Evidence</option>
                    <option value="won">Won</option>
                    <option value="lost">Lost</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div class="col-md-8 d-flex align-items-end justify-content-end">
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newDisputeModal">
                    <i class="bi bi-plus-lg"></i> New Dispute
                </button>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Transaction</th>
                        <th>Reason</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Created</th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="d in mdc.items.data">
                        <td>@{{ ($index + 1) }}</td>
                        <td>@{{ d.transaction_id || '-' }}</td>
                        <td>@{{ d.reason }}</td>
                        <td>INR @{{ d.amount || 0 | number:2 }}</td>
                        <td><span class="badge bg-secondary text-uppercase">@{{ d.status }}</span></td>
                        <td>@{{ d.created_at }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="newDisputeModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Create Dispute</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Transaction ID (optional)</label>
                        <input type="number" class="form-control" ng-model="mdc.form.transaction_id">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Reason</label>
                        <input type="text" class="form-control" ng-model="mdc.form.reason">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Amount</label>
                        <input type="number" step="0.01" class="form-control" ng-model="mdc.form.amount">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Notes</label>
                        <textarea class="form-control" ng-model="mdc.form.notes"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary" ng-click="mdc.create()" ng-disabled="mdc.creating">
                        <span ng-if="mdc.creating" class="spinner-border spinner-border-sm me-2"></span>
                        Create
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('merchant.disputes.angular.main_controller')


