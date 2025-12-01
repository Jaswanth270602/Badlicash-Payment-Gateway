

<?php $__env->startSection('title', 'All Transactions - Admin - BadliCash'); ?>
<?php $__env->startSection('page-title', 'All Transactions'); ?>

<?php $__env->startSection('content'); ?>
<div ng-app="badlicashApp" ng-controller="AdminTransactionsController as atc">
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label'=>'Dashboard','url'=>route('admin.dashboard')],
        ['label'=>'All Transactions']
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label'=>'Dashboard','url'=>route('admin.dashboard')],
        ['label'=>'All Transactions']
    ])]); ?>
<?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $attributes = $__attributesOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__attributesOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal360d002b1b676b6f84d43220f22129e2)): ?>
<?php $component = $__componentOriginal360d002b1b676b6f84d43220f22129e2; ?>
<?php unset($__componentOriginal360d002b1b676b6f84d43220f22129e2); ?>
<?php endif; ?>

    <div class="stat-card mb-4">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" ng-model="atc.filters.status" ng-change="atc.applyFilters()">
                    <option value="all">All</option>
                    <option value="success">Success</option>
                    <option value="failed">Failed</option>
                    <option value="pending">Pending</option>
                    <option value="initiated">Initiated</option>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Merchant ID</label>
                <input type="number" class="form-control" placeholder="Merchant ID" ng-model="atc.filters.merchant_id" ng-change="atc.applyFilters()">
            </div>
            <div class="col-md-4">
                <label class="form-label">Search</label>
                <input class="form-control" placeholder="Search by transaction ID, order ID" ng-model="atc.filters.search" ng-change="atc.applyFilters()">
            </div>
            <div class="col-md-2 d-flex align-items-end">
                <button class="btn btn-outline-secondary w-100" ng-click="atc.clearFilters()">
                    <i class="bi bi-x-circle"></i> Clear
                </button>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div ng-show="atc.loading" class="loader-overlay position-relative" style="min-height: 400px;">
            <div class="position-absolute top-50 start-50 translate-middle">
                <div class="spinner-violet"></div>
                <p class="mt-2 text-muted text-center">Loading transactions...</p>
            </div>
        </div>

        <div ng-hide="atc.loading">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>S.No</th>
                            <th>Transaction ID</th>
                            <th>Merchant</th>
                            <th>Order ID</th>
                            <th>Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Created At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-if="atc.transactions.length === 0">
                            <td colspan="8" class="text-center text-muted py-4">No transactions found</td>
                        </tr>
                        <tr ng-repeat="t in atc.transactions track by $index">
                            <td>{{ (atc.pagination.current_page - 1) * atc.pagination.per_page + $index + 1 }}</td>
                            <td><code>{{ t.txn_id }}</code></td>
                            <td>
                                <div ng-if="t.merchant">
                                    <strong>{{ t.merchant.name }}</strong>
                                    <br><small class="text-muted">ID: {{ t.merchant.id }}</small>
                                </div>
                                <span ng-if="!t.merchant" class="text-muted">N/A</span>
                            </td>
                            <td><code>{{ (t.order && t.order.order_id) || 'N/A' }}</code></td>
                            <td><strong>{{ t.currency || 'INR' }} {{ t.amount | number:2 }}</strong></td>
                            <td><span class="badge bg-secondary">{{ t.payment_method | uppercase }}</span></td>
                            <td>
                                <span class="badge" ng-class="{'bg-success': t.status==='success', 'bg-danger': t.status==='failed', 'bg-warning': t.status==='pending', 'bg-secondary': t.status==='initiated'}">
                                    {{ t.status | uppercase }}
                                </span>
                            </td>
                            <td>{{ t.created_at | date:'MMM d, y HH:mm' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div ng-if="atc.pagination.last_page > 1" class="pagination-wrapper">
                <ul class="pagination justify-content-center">
                    <li class="page-item" ng-class="{'disabled': atc.pagination.current_page === 1}">
                        <a class="page-link" href="#" ng-click="atc.changePage(atc.pagination.current_page - 1)">Previous</a>
                    </li>
                    <li class="page-item" ng-repeat="page in atc.getPageNumbers() track by $index" ng-class="{'active': page === atc.pagination.current_page}">
                        <a class="page-link" href="#" ng-click="atc.changePage(page)">{{ page }}</a>
                    </li>
                    <li class="page-item" ng-class="{'disabled': atc.pagination.current_page === atc.pagination.last_page}">
                        <a class="page-link" href="#" ng-click="atc.changePage(atc.pagination.current_page + 1)">Next</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.transactions.angular.main_controller', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php echo $__env->make('layouts.app-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pc\Desktop\Badlicash-Payment-Gateway\resources\views/admin/transactions/index.blade.php ENDPATH**/ ?>