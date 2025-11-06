

<?php $__env->startSection('title','Admin Disputes - BadliCash'); ?>
<?php $__env->startSection('page-title','Admin Disputes'); ?>

<?php $__env->startSection('content'); ?>
<div ng-app="badlicashApp" ng-controller="AdminDisputesController as adc">
    <div class="stat-card mb-3">
        <div class="row g-3">
            <div class="col-md-3">
                <label class="form-label">Merchant ID</label>
                <input type="number" class="form-control" ng-model="adc.filters.merchant_id" ng-change="adc.load()">
            </div>
            <div class="col-md-3">
                <label class="form-label">Status</label>
                <select class="form-select" ng-model="adc.filters.status" ng-change="adc.load()">
                    <option value="">All</option>
                    <option value="open">Open</option>
                    <option value="needs_evidence">Needs Evidence</option>
                    <option value="won">Won</option>
                    <option value="lost">Lost</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
        </div>
    </div>

    <div class="stat-card">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Merchant</th>
                        <th>Txn</th>
                        <th>Reason</th>
                        <th>Amount</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="d in adc.items.data">
                        <td>{{ ($index + 1) }}</td>
                        <td>{{ d.merchant_id }}</td>
                        <td>{{ d.transaction_id || '-' }}</td>
                        <td>{{ d.reason }}</td>
                        <td>INR {{ d.amount || 0 | number:2 }}</td>
                        <td>
                            <select class="form-select form-select-sm" ng-model="d.status" ng-change="adc.updateStatus(d)">
                                <option value="open">open</option>
                                <option value="needs_evidence">needs_evidence</option>
                                <option value="won">won</option>
                                <option value="lost">lost</option>
                                <option value="closed">closed</option>
                            </select>
                        </td>
                        <td>
                            <input type="url" class="form-control form-control-sm mb-1" placeholder="Evidence URL" ng-model="d.evidence_url" ng-blur="adc.updateStatus(d)">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.disputes.angular.main_controller', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>



<?php echo $__env->make('layouts.app-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\pc\Desktop\Badlicash-Payment-Gateway\resources\views/admin/disputes/index.blade.php ENDPATH**/ ?>