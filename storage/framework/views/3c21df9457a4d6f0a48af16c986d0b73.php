

<?php $__env->startSection('title','Transactions - BadliCash'); ?>
<?php $__env->startSection('page-title','Transactions'); ?>

<?php $__env->startSection('content'); ?>
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
                    <td>{{ (tc.pagination.current_page - 1) * tc.pagination.per_page + $index + 1 }}</td>
                    <td>
                        <code class="text-primary" style="font-size: 12px;">{{ t.transaction_id || t.txn_id }}</code>
                    </td>
                    <td>
                        <code class="text-info" style="font-size: 12px;">{{ (t.order && t.order.order_id) || 'N/A' }}</code>
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
                            <div class="fw-semibold" style="font-size: 13px;">{{ t.customer_email || (t.order && t.order.customer_details.name) || 'N/A' }}</div>
                            <small class="text-muted">{{ t.customer_phone || (t.order && t.order.customer_details.phone) || '' }}</small>
                        </div>
                        <span ng-if="!t.customer_email && (!t.order || !t.order.customer_details)" class="text-muted">N/A</span>
                    </td>
                    <td>
                        <strong class="text-success">{{ t.currency || 'INR' }} {{ t.amount | number:2 }}</strong>
                        <div ng-if="t.fee_amount" style="font-size: 11px; color: #94a3b8;">Fee: {{ t.currency }} {{ t.fee_amount | number:2 }}</div>
                    </td>
                    <td>
                        <span class="badge" style="background: #6366f1;">{{ t.payment_method | uppercase }}</span>
                        <div ng-if="t.payment_details && t.payment_details.card_number" style="font-size: 11px; color: #64748b; margin-top: 4px;">
                            **** {{ t.payment_details.card_number }}
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
                            {{ t.status | uppercase }}
                        </span>
                    </td>
                    <td style="white-space: nowrap;">
                        <div style="font-size: 13px;">{{ t.created_at | date:'MMM d, y' }}</div>
                        <small class="text-muted">{{ t.created_at | date:'HH:mm:ss' }}</small>
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

        <?php if (isset($component)) { $__componentOriginal41032d87daf360242eb88dbda6c75ed1 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal41032d87daf360242eb88dbda6c75ed1 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.pagination','data' => []] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('pagination'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal41032d87daf360242eb88dbda6c75ed1)): ?>
<?php $attributes = $__attributesOriginal41032d87daf360242eb88dbda6c75ed1; ?>
<?php unset($__attributesOriginal41032d87daf360242eb88dbda6c75ed1); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal41032d87daf360242eb88dbda6c75ed1)): ?>
<?php $component = $__componentOriginal41032d87daf360242eb88dbda6c75ed1; ?>
<?php unset($__componentOriginal41032d87daf360242eb88dbda6c75ed1); ?>
<?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('merchant.transactions.angular.main_controller', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('layouts.app-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\agdp_projects\Badlicash-Payment-Gateway\resources\views/merchant/transactions/index.blade.php ENDPATH**/ ?>