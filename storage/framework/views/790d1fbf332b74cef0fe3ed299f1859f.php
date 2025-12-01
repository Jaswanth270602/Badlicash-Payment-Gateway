<?php $__env->startSection('title', 'Merchant Accounts - Admin - BadliCash'); ?>
<?php $__env->startSection('page-title', 'Merchants Management'); ?>

<?php $__env->startSection('content'); ?>
<div ng-app="badlicashApp" ng-controller="AdminMerchantAccountsController as amac">
    <?php if (isset($component)) { $__componentOriginal360d002b1b676b6f84d43220f22129e2 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal360d002b1b676b6f84d43220f22129e2 = $attributes; } ?>
<?php $component = Illuminate\View\AnonymousComponent::resolve(['view' => 'components.breadcrumbs','data' => ['items' => [
        ['label'=>'Home','url'=>route('admin.dashboard')],
        ['label'=>'Merchants']
    ]]] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? (array) $attributes->getIterator() : [])); ?>
<?php $component->withName('breadcrumbs'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag && $constructor = (new ReflectionClass(Illuminate\View\AnonymousComponent::class))->getConstructor()): ?>
<?php $attributes = $attributes->except(collect($constructor->getParameters())->map->getName()->all()); ?>
<?php endif; ?>
<?php $component->withAttributes(['items' => \Illuminate\View\Compilers\BladeCompiler::sanitizeComponentAttribute([
        ['label'=>'Home','url'=>route('admin.dashboard')],
        ['label'=>'Merchants']
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
        <div class="col-md-12">
            <h2>Merchants Management</h2>
            <p class="text-muted">List of Merchants</p>
        </div>
    </div>

    <!-- Status Filter Buttons -->
    <div class="stat-card mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm" 
                        ng-class="{'btn-secondary': amac.filters.approval_status !== 'all', 'btn-success': amac.filters.approval_status === 'all'}"
                        ng-click="amac.setApprovalStatus('all')">All</button>
                <button type="button" class="btn btn-sm" 
                        ng-class="{'btn-secondary': amac.filters.approval_status !== 'approved', 'btn-success': amac.filters.approval_status === 'approved'}"
                        ng-click="amac.setApprovalStatus('approved')">Approved</button>
                <button type="button" class="btn btn-sm" 
                        ng-class="{'btn-secondary': amac.filters.approval_status !== 'test_approved', 'btn-success': amac.filters.approval_status === 'test_approved'}"
                        ng-click="amac.setApprovalStatus('test_approved')">Test Approved</button>
                <button type="button" class="btn btn-sm" 
                        ng-class="{'btn-secondary': amac.filters.approval_status !== 'not_approved', 'btn-success': amac.filters.approval_status === 'not_approved'}"
                        ng-click="amac.setApprovalStatus('not_approved')">Not Approved</button>
                <button type="button" class="btn btn-sm" 
                        ng-class="{'btn-secondary': amac.filters.approval_status !== 'rejected', 'btn-success': amac.filters.approval_status === 'rejected'}"
                        ng-click="amac.setApprovalStatus('rejected')">Rejected</button>
            </div>
            <div class="btn-group" role="group">
                <button type="button" class="btn btn-sm" 
                        ng-class="{'btn-primary': amac.filters.merchant_type === 'merchant', 'btn-outline-primary': amac.filters.merchant_type !== 'merchant'}"
                        ng-click="amac.setMerchantType('merchant')">Merchants</button>
                <button type="button" class="btn btn-sm" 
                        ng-class="{'btn-primary': amac.filters.merchant_type === 'vendor_merchant', 'btn-outline-primary': amac.filters.merchant_type !== 'vendor_merchant'}"
                        ng-click="amac.setMerchantType('vendor_merchant')">Vendor Merchants</button>
            </div>
        </div>
    </div>

    <!-- Action Buttons and Controls -->
    <div class="stat-card mb-3">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <label class="form-label me-2">Show</label>
                <select class="form-select form-select-sm d-inline-block" style="width: auto;" ng-model="amac.pagination.per_page" ng-change="amac.loadMerchants()">
                    <option value="5">5 entries</option>
                    <option value="10">10 entries</option>
                    <option value="25">25 entries</option>
                    <option value="50">50 entries</option>
                </select>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <button class="btn btn-sm btn-outline-secondary" ng-click="amac.clearFilters()">
                    <i class="bi bi-funnel"></i> Clear Filters
                </button>
                <button class="btn btn-sm btn-outline-secondary" ng-click="amac.loadMerchants()">
                    <i class="bi bi-arrow-clockwise"></i> Reload
                </button>
                <div class="btn-group">
                    <button class="btn btn-sm btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                        <i class="bi bi-eye"></i> Columns
                    </button>
                    <ul class="dropdown-menu">
                        <li ng-repeat="(key, col) in amac.visibleColumns">
                            <a class="dropdown-item" href="#" ng-click="amac.toggleColumn(key)">
                                <i class="bi" ng-class="col ? 'bi-check-square' : 'bi-square'"></i> {{ col.label }}
                            </a>
                        </li>
                    </ul>
                </div>
                <button class="btn btn-sm btn-outline-secondary" ng-click="amac.resetView()">
                    <i class="bi bi-arrow-counterclockwise"></i> Reset
                </button>
                <button class="btn btn-sm btn-primary" ng-click="amac.openNewModal()">
                    <i class="bi bi-plus-lg"></i> + New
                </button>
                <button class="btn btn-sm btn-outline-primary" ng-click="amac.duplicateSelected()" ng-disabled="!amac.selectedMerchant">
                    <i class="bi bi-files"></i> Duplicate Merchant
                </button>
            </div>
        </div>
    </div>

    <!-- Main Table -->
    <div class="stat-card">
        <div ng-show="amac.loading" class="loader-overlay position-relative" style="min-height: 400px;">
            <div class="position-absolute top-50 start-50 translate-middle">
                <div class="spinner-violet"></div>
                <p class="mt-2 text-muted text-center">Loading merchants...</p>
            </div>
        </div>

        <div ng-hide="amac.loading">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>
                                <input type="checkbox" ng-model="amac.selectAll" ng-change="amac.toggleSelectAll()">
                            </th>
                            <th ng-show="amac.visibleColumns.id.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Merchant ID.</span>
                                    <i class="bi bi-arrow-up-down" style="cursor: pointer;" ng-click="amac.sortBy('id')"></i>
                                </div>
                                <input type="text" class="form-control form-control-sm mt-1" placeholder="Filter..." ng-model="amac.filters.filter_id" ng-change="amac.applyFilters()">
                            </th>
                            <th ng-show="amac.visibleColumns.name.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Merchant Name</span>
                                    <i class="bi bi-arrow-up-down" style="cursor: pointer;" ng-click="amac.sortBy('name')"></i>
                                </div>
                                <input type="text" class="form-control form-control-sm mt-1" placeholder="Filter..." ng-model="amac.filters.filter_name" ng-change="amac.applyFilters()">
                            </th>
                            <th ng-show="amac.visibleColumns.email.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Merchant Email</span>
                                    <i class="bi bi-arrow-up-down" style="cursor: pointer;" ng-click="amac.sortBy('email')"></i>
                                </div>
                                <input type="text" class="form-control form-control-sm mt-1" placeholder="Filter..." ng-model="amac.filters.filter_email" ng-change="amac.applyFilters()">
                            </th>
                            <th ng-show="amac.visibleColumns.phone.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Merchant Phone</span>
                                    <i class="bi bi-arrow-up-down" style="cursor: pointer;" ng-click="amac.sortBy('phone')"></i>
                                </div>
                                <input type="text" class="form-control form-control-sm mt-1" placeholder="Filter..." ng-model="amac.filters.filter_phone" ng-change="amac.applyFilters()">
                            </th>
                            <th ng-show="amac.visibleColumns.status.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Merchant Status</span>
                                    <i class="bi bi-arrow-up-down" style="cursor: pointer;" ng-click="amac.sortBy('approval_status')"></i>
                                </div>
                                <select class="form-select form-select-sm mt-1" ng-model="amac.filters.filter_status" ng-change="amac.applyFilters()">
                                    <option value="all">All</option>
                                    <option value="approved">Approved</option>
                                    <option value="test_approved">Test Approved</option>
                                    <option value="not_approved">Not Approved</option>
                                    <option value="rejected">Rejected</option>
                                </select>
                            </th>
                            <th ng-show="amac.visibleColumns.partner.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Partner Names</span>
                                </div>
                                <input type="text" class="form-control form-control-sm mt-1" placeholder="Filter..." ng-model="amac.filters.filter_partner" ng-change="amac.applyFilters()">
                            </th>
                            <th ng-show="amac.visibleColumns.organization.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Organization Name</span>
                                </div>
                                <input type="text" class="form-control form-control-sm mt-1" placeholder="Filter..." ng-model="amac.filters.filter_organization" ng-change="amac.applyFilters()">
                            </th>
                            <th ng-show="amac.visibleColumns.category.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Merchant Category</span>
                                </div>
                                <select class="form-select form-select-sm mt-1" ng-model="amac.filters.filter_category" ng-change="amac.applyFilters()">
                                    <option value="all">All</option>
                                    <option value="B2B">B2B</option>
                                    <option value="Education">Education</option>
                                    <option value="Insurance">Insurance</option>
                                    <option value="Utilities">Utilities</option>
                                    <option value="E-commerce">E-commerce</option>
                                    <option value="Travel & Hospitality">Travel & Hospitality</option>
                                    <option value="Telecom">Telecom</option>
                                    <option value="High Risk">High Risk</option>
                                    <option value="Grocery">Grocery</option>
                                    <option value="NBFC">NBFC</option>
                                    <option value="Government">Government</option>
                                    <option value="Others">Others</option>
                                    <option value="Forex">Forex</option>
                                    <option value="Real Estate">Real Estate</option>
                                    <option value="Housing Society">Housing Society</option>
                                    <option value="Housing Board">Housing Board</option>
                                    <option value="Govt E-Tendering">Govt E-Tendering</option>
                                </select>
                            </th>
                            <th ng-show="amac.visibleColumns.registration_date.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Registration Date</span>
                                </div>
                                <input type="text" class="form-control form-control-sm mt-1" placeholder="MM/DD/YYYY" ng-model="amac.filters.filter_registration_date" ng-change="amac.applyFilters()">
                            </th>
                            <th ng-show="amac.visibleColumns.challan_urn.visible">
                                <div class="d-flex align-items-center gap-2">
                                    <span>Challan URN</span>
                                </div>
                                <input type="text" class="form-control form-control-sm mt-1" placeholder="Filter..." ng-model="amac.filters.filter_challan_urn" ng-change="amac.applyFilters()">
                            </th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-if="amac.merchants.length === 0">
                            <td colspan="12" class="text-center text-danger py-4">No matching records found</td>
                        </tr>
                        <tr ng-repeat="merchant in amac.merchants track by $index" 
                            ng-click="amac.selectMerchant(merchant)" 
                            ng-class="{'table-active': amac.selectedMerchant && amac.selectedMerchant.id === merchant.id}">
                            <td>
                                <input type="checkbox" ng-model="merchant.selected" ng-click="$event.stopPropagation()">
                            </td>
                            <td ng-show="amac.visibleColumns.id.visible">{{ merchant.id }}</td>
                            <td ng-show="amac.visibleColumns.name.visible">{{ merchant.name }}</td>
                            <td ng-show="amac.visibleColumns.email.visible">{{ merchant.email }}</td>
                            <td ng-show="amac.visibleColumns.phone.visible">{{ merchant.phone || '-' }}</td>
                            <td ng-show="amac.visibleColumns.status.visible">
                                <span class="badge" ng-class="{
                                    'bg-success': merchant.approval_status === 'approved',
                                    'bg-info': merchant.approval_status === 'test_approved',
                                    'bg-warning': merchant.approval_status === 'not_approved',
                                    'bg-danger': merchant.approval_status === 'rejected'
                                }">
                                    {{ merchant.approval_status | uppercase | replace:'_':' ' }}
                                </span>
                            </td>
                            <td ng-show="amac.visibleColumns.partner.visible">{{ merchant.partner_name || '-' }}</td>
                            <td ng-show="amac.visibleColumns.organization.visible">{{ merchant.organization_name || merchant.company_name || '-' }}</td>
                            <td ng-show="amac.visibleColumns.category.visible">{{ merchant.merchant_category || '-' }}</td>
                            <td ng-show="amac.visibleColumns.registration_date.visible">{{ merchant.registration_date | date:'MM/dd/yyyy' }}</td>
                            <td ng-show="amac.visibleColumns.challan_urn.visible">{{ merchant.challan_urn || '-' }}</td>
                            <td>
                                <button class="btn btn-sm btn-outline-primary" ng-click="amac.viewMerchant(merchant); $event.stopPropagation();">
                                    <i class="bi bi-eye"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-flex justify-content-between align-items-center mt-3">
                <div>
                    Showing {{ (amac.pagination.current_page - 1) * amac.pagination.per_page + 1 }} to {{ Math.min(amac.pagination.current_page * amac.pagination.per_page, amac.pagination.total) }} of {{ amac.pagination.total }} entries
                </div>
                <div>
                    <button class="btn btn-sm btn-outline-secondary" 
                            ng-click="amac.changePage(amac.pagination.current_page - 1)" 
                            ng-disabled="amac.pagination.current_page === 1">
                        Previous
                    </button>
                    <span class="mx-2">...</span>
                    <button class="btn btn-sm btn-outline-secondary" 
                            ng-click="amac.changePage(amac.pagination.current_page + 1)" 
                            ng-disabled="amac.pagination.current_page === amac.pagination.last_page">
                        Next
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- New Merchant Modal -->
    <?php echo $__env->make('admin.merchants.partials.new-merchant-modal', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('admin.merchants.angular.accounts_controller', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>


<?php echo $__env->make('layouts.app-sidebar', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH C:\Users\ushar\OneDrive\Desktop\Badlicash-Payment-Gateway\resources\views/admin/merchants/accounts.blade.php ENDPATH**/ ?>