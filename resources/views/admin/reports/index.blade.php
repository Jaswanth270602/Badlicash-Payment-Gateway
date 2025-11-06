@extends('layouts.app-sidebar')

@section('title','Admin Reports - BadliCash')
@section('page-title','Admin Reports')

@section('content')
<div ng-app="badlicashApp" ng-controller="AdminReportsController as arc">
    <div class="stat-card mb-3">
        <div class="row g-3">
            <div class="col-md-6 col-lg-3">
                <label class="form-label">Merchant ID (optional)</label>
                <input type="number" class="form-control" ng-model="arc.filters.merchant_id" placeholder="e.g. 1">
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">From Date</label>
                <input type="date" class="form-control" ng-model="arc.filters.from_date">
            </div>
            <div class="col-md-6 col-lg-3">
                <label class="form-label">To Date</label>
                <input type="date" class="form-control" ng-model="arc.filters.to_date">
            </div>
            <div class="col-md-6 col-lg-3 d-flex align-items-end">
                <button class="btn btn-primary w-100" ng-click="arc.generateReport()" ng-disabled="arc.generating">
                    <span ng-if="arc.generating" class="spinner-border spinner-border-sm me-2"></span>
                    <i class="bi bi-file-earmark-text"></i> Generate Report
                </button>
            </div>
            <div class="col-md-6 col-lg-3 d-flex align-items-end">
                <button class="btn btn-outline-primary w-100" ng-click="arc.exportReport()" ng-disabled="arc.exporting">
                    <span ng-if="arc.exporting" class="spinner-border spinner-border-sm me-2"></span>
                    <i class="bi bi-download"></i> Export CSV
                </button>
            </div>
        </div>
    </div>

    <div class="stat-card" ng-if="arc.reportData">
        <div class="row g-4">
            <div class="col-md-3">
                <div class="text-muted">Total Transactions</div>
                <div class="h4 mb-0">@{{ arc.reportData.total_transactions || 0 }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted">Total Successful</div>
                <div class="h4 mb-0">@{{ arc.reportData.successful || 0 }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted">Total Failed</div>
                <div class="h4 mb-0">@{{ arc.reportData.failed || 0 }}</div>
            </div>
            <div class="col-md-3">
                <div class="text-muted">Total Volume (Success)</div>
                <div class="h4 mb-0">INR @{{ (arc.reportData.total_amount || 0) | number:2 }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

@include('admin.reports.angular.main_controller')
 