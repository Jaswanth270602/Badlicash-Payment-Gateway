@extends('layouts.app-sidebar')

@section('title', 'Payment Links - BadliCash')

@section('page-title','Payment Links')
@section('content')
<div ng-app="badlicashApp" ng-controller="PaymentLinksController as plc">
    <x-breadcrumbs :items="[
        ['label'=>'Dashboard','url'=>route('dashboard')],
        ['label'=>'Payment Links']
    ]" />

    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Payment Links</h2>
            <p class="text-muted">Create and manage payment links for your customers</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLinkModal">
                <i class="bi bi-plus-circle"></i> Create Payment Link
            </button>
        </div>
    </div>

    @include('merchant.paymentlinks.filters')

    <div class="stat-card">
        <div ng-show="plc.loading" class="loader-overlay position-relative" style="min-height: 400px;">
            <div class="position-absolute top-50 start-50 translate-middle">
                <div class="spinner-violet"></div>
                <p class="mt-2 text-muted text-center">Loading payment links...</p>
            </div>
        </div>
        
        @include('merchant.paymentlinks.grid')
    </div>

    <!-- Create Payment Link Modal -->
    @include('merchant.paymentlinks.create_modal')

    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header" ng-class="{'bg-success': plc.toastType === 'success', 'bg-danger': plc.toastType === 'error'}">
                <strong class="me-auto text-white">@{{ plc.toastType === 'success' ? 'Success' : 'Error' }}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">@{{ plc.toastMessage }}</div>
        </div>
    </div>
</div>
@endsection

@include('merchant.paymentlinks.angular.main_controller')

