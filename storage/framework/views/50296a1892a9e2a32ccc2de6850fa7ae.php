

<?php $__env->startSection('title', 'Payment Links - BadliCash'); ?>

<?php $__env->startSection('page-title','Payment Links'); ?>

<?php $__env->startSection('content'); ?>
<div id="paymentLinksApp" ng-controller="PaymentLinksController as plc">
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label'=>'Dashboard','url'=>route('dashboard')],
        ['label'=>'Payment Links']
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label'=>'Dashboard','url'=>route('dashboard')],
        ['label'=>'Payment Links']
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

    <div class="row mb-4">
        <div class="col-md-8">
            <h2>Payment Links</h2>
            <p class="text-muted">Create and manage payment links for your customers</p>
        </div>
        <div class="col-md-4 text-end">
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createLinkModal" ng-click="plc.initModal()">
                <i class="bi bi-plus-circle"></i> Create Payment Link
            </button>
        </div>
    </div>

    <?php echo $__env->make('merchant.paymentlinks.filters', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <div class="stat-card">
        <div ng-show="plc.loading" class="loader-overlay position-relative" style="min-height: 400px;">
            <div class="position-absolute top-50 start-50 translate-middle">
                <div class="spinner-violet"></div>
                <p class="mt-2 text-muted text-center">Loading payment links...</p>
            </div>
        </div>
        
        <?php echo $__env->make('merchant.paymentlinks.grid', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    </div>

    <!-- Create Payment Link Modal -->
    <?php echo $__env->make('merchant.paymentlinks.create_modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

    <!-- Toast Notification -->
    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="toast" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header" ng-class="{'bg-success': plc.toastType === 'success', 'bg-danger': plc.toastType === 'error'}">
                <strong class="me-auto text-white">{{ plc.toastType === 'success' ? 'Success' : 'Error' }}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body">{{ plc.toastMessage }}</div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('merchant.paymentlinks.angular.main_controller', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>

<?php $__env->startPush('scripts'); ?>
<script>
// Manual bootstrap after controller is registered
(function() {
    function bootstrapApp() {
        if (typeof angular === 'undefined') {
            setTimeout(bootstrapApp, 10);
            return;
        }
        try {
            var app = angular.module('badlicashApp');
            var element = document.getElementById('paymentLinksApp');
            if (element && !angular.element(element).injector()) {
                angular.bootstrap(element, ['badlicashApp']);
            }
        } catch(e) {
            setTimeout(bootstrapApp, 10);
        }
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrapApp);
    } else {
        bootstrapApp();
    }
})();
</script>
<?php $__env->stopPush(); ?>

<?php echo $__env->make('layouts.app-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pc\Desktop\Badlicash-Payment-Gateway\resources\views/merchant/paymentlinks/index.blade.php ENDPATH**/ ?>