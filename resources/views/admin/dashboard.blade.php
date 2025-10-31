@extends('layouts.app')

@section('title', 'Admin Dashboard - BadliCash')

@section('content')
<div class="row">
    <div class="col-md-12 mb-4">
        <h2>Admin Dashboard</h2>
        <p class="text-muted">System Overview</p>
    </div>
</div>

<div class="row g-4">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Total Merchants</h6>
                <h3>{{ number_format($stats['total_merchants']) }}</h3>
                <small class="text-success">{{ number_format($stats['active_merchants']) }} active</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Total Transactions</h6>
                <h3>{{ number_format($stats['total_transactions']) }}</h3>
                <small class="text-muted">All time</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <h6 class="text-muted">Total Volume</h6>
                <h3>USD {{ number_format($stats['total_volume'], 2) }}</h3>
                <small class="text-muted">Successful transactions</small>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card bg-primary text-white">
            <div class="card-body">
                <h6>System Status</h6>
                <h3><i class="bi bi-check-circle-fill"></i> Operational</h3>
                <small>All systems running</small>
            </div>
        </div>
    </div>
</div>

<div class="row mt-4">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Admin Actions</h5>
            </div>
            <div class="list-group list-group-flush">
                <a href="{{ route('admin.merchants.index') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-building"></i> Manage Merchants
                </a>
                <a href="{{ route('admin.transactions.index') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-credit-card"></i> View All Transactions
                </a>
                <a href="{{ route('admin.reports.index') }}" class="list-group-item list-group-item-action">
                    <i class="bi bi-file-earmark-bar-graph"></i> System Reports
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

