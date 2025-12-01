

<?php $__env->startSection('title', 'Admin Dashboard - BadliCash'); ?>
<?php $__env->startSection('page-title', 'Admin Dashboard'); ?>

<?php $__env->startSection('content'); ?>
<div ng-app="badlicashApp" ng-controller="AdminDashboardController as adc">
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label'=>'Dashboard']
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label'=>'Dashboard']
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

    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-primary">
                        <i class="bi bi-building"></i>
                    </div>
                    <div class="text-end">
                        <div class="stat-value">{{ adc.stats.total_merchants || 0 }}</div>
                        <div class="stat-label">Total Merchants</div>
                    </div>
                </div>
                <div class="stat-change text-success">
                    <i class="bi bi-arrow-up"></i> {{ adc.stats.active_merchants || 0 }} active
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-success">
                        <i class="bi bi-credit-card"></i>
                    </div>
                    <div class="text-end">
                        <div class="stat-value">{{ adc.stats.total_transactions || 0 }}</div>
                        <div class="stat-label">Transactions</div>
                    </div>
                </div>
                <div class="stat-change text-muted">
                    All time
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-info">
                        <i class="bi bi-currency-rupee"></i>
                    </div>
                    <div class="text-end">
                        <div class="stat-value">INR {{ (adc.stats.total_volume || 0) | number:2 }}</div>
                        <div class="stat-label">Total Volume</div>
                    </div>
                </div>
                <div class="stat-change text-muted">
                    Successful transactions
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="stat-card bg-gradient-primary text-white">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div class="stat-icon bg-white bg-opacity-20">
                        <i class="bi bi-check-circle-fill"></i>
                    </div>
                    <div class="text-end">
                        <div class="stat-value text-white">Operational</div>
                        <div class="stat-label text-white-50">System Status</div>
                    </div>
                </div>
                <div class="stat-change text-white-50">
                    All systems running
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">Quick Actions</h5>
                </div>
                <div class="list-group list-group-flush">
                    <a href="<?php echo e(route('admin.merchants.index')); ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="bi bi-building me-3 text-primary"></i>
                        <span>Manage Merchants</span>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </a>
                    <a href="<?php echo e(route('admin.transactions.index')); ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="bi bi-credit-card me-3 text-success"></i>
                        <span>View All Transactions</span>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </a>
                    <a href="<?php echo e(route('admin.reports.index')); ?>" class="list-group-item list-group-item-action d-flex align-items-center">
                        <i class="bi bi-file-earmark-bar-graph me-3 text-info"></i>
                        <span>System Reports</span>
                        <i class="bi bi-chevron-right ms-auto"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="stat-card">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="mb-0">Recent Activity</h5>
                </div>
                <div ng-show="adc.loading" class="text-center py-4">
                    <div class="spinner-violet"></div>
                    <p class="text-muted mt-2">Loading...</p>
                </div>
                <div ng-hide="adc.loading">
                    <div ng-if="adc.recentActivity && adc.recentActivity.length === 0" class="text-center py-4 text-muted">
                        No recent activity
                    </div>
                    <div ng-repeat="activity in adc.recentActivity" class="d-flex align-items-center mb-3 pb-3 border-bottom">
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ activity.title }}</div>
                            <small class="text-muted">{{ activity.description }}</small>
                        </div>
                        <small class="text-muted">{{ activity.created_at | date:'MMM d, HH:mm' }}</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
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
            app.controller('AdminDashboardController', ['$http', function($http) {
                var vm = this;
                vm.stats = {
                    total_merchants: 0,
                    active_merchants: 0,
                    total_transactions: 0,
                    total_volume: 0
                };
                vm.recentActivity = [];
                vm.loading = true;

                vm.loadStats = function() {
                    $http.get('/admin/dashboard/data').then(function(response) {
                        if (response.data && response.data.success) {
                            vm.stats = response.data.data.stats || vm.stats;
                            vm.recentActivity = response.data.data.recent_activity || [];
                        }
                        vm.loading = false;
                    }, function(error) {
                        vm.loading = false;
                        console.error('Error loading dashboard data:', error);
                    });
                };

                vm.loadStats();
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
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pc\Desktop\Badlicash-Payment-Gateway\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>