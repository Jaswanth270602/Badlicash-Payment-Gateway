

<?php $__env->startSection('title','Settings - BadliCash'); ?>
<?php $__env->startSection('page-title','Settings'); ?>

<?php $__env->startSection('content'); ?>
<?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
    ['label'=>'Dashboard','url'=>route('dashboard')],
    ['label'=>'Settings']
]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
    ['label'=>'Dashboard','url'=>route('dashboard')],
    ['label'=>'Settings']
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

<?php if(session('success')): ?>
<div class="alert alert-success"><?php echo e(session('success')); ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-md-8">
        <div class="stat-card mb-4">
            <h5 class="mb-3">API Keys</h5>
            <div class="table-responsive">
                <table class="table">
                    <thead><tr><th>Key</th><th>Status</th><th>Created</th></tr></thead>
                    <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $apiKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><code><?php echo e($k->key); ?></code></td>
                            <td><span class="badge <?php echo e($k->status==='active'?'bg-success':'bg-secondary'); ?>"><?php echo e($k->status); ?></span></td>
                            <td><?php echo e($k->created_at->format('M d, Y')); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="3" class="text-muted">No keys yet</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="stat-card">
            <h5 class="mb-3">Webhook</h5>
            <form method="POST" action="<?php echo e(route('merchant.settings.update-webhook')); ?>">
                <?php echo csrf_field(); ?>
                <div class="mb-3">
                    <label class="form-label">Webhook URL</label>
                    <input type="url" name="webhook_url" class="form-control" value="<?php echo e(old('webhook_url',$merchant->webhook_url)); ?>" placeholder="https://example.com/webhooks/badlicash">
                </div>
                <button class="btn btn-primary">Save</button>
            </form>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stat-card">
            <h5 class="mb-3">Account Mode</h5>
            <div class="d-flex gap-2">
                <button class="btn <?php echo e($merchant->test_mode?'btn-warning':'btn-outline-warning'); ?>" onclick="switchMode('test')">Test</button>
                <button class="btn <?php echo e(!$merchant->test_mode?'btn-success':'btn-outline-success'); ?>" onclick="switchMode('live')">Live</button>
            </div>
            <div class="mt-3">
                <span class="badge <?php echo e($merchant->test_mode?'bg-warning':'bg-success'); ?>"><?php echo e($merchant->test_mode?'TEST':'LIVE'); ?></span>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

 
<?php echo $__env->make('layouts.app-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pc\Desktop\Badlicash-Payment-Gateway\resources\views/merchant/settings/index.blade.php ENDPATH**/ ?>