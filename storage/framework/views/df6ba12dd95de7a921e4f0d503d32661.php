<div ng-if="!plc.loading">
    <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Title</th>
                        <th>Amount</th>
                        <th>Link Token</th>
                        <th>Status</th>
                        <th>Usage</th>
                        <th>Expires At</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="link in plc.paymentLinks track by $index">
                        <td>{{ (plc.pagination.current_page - 1) * plc.pagination.per_page + $index + 1 }}</td>
                        <td>{{ link.title || 'Untitled' }}</td>
                        <td><strong>{{ link.currency || 'INR' }} {{ link.amount | number:2 }}</strong></td>
                        <td>
                            <code class="small">{{ link.link_token }}</code>
                        </td>
                        <td>
                            <span class="badge" ng-class="{
                                'bg-success': link.status === 'active',
                                'bg-warning': link.status === 'expired',
                                'bg-info': link.status === 'paid',
                                'bg-secondary': link.status === 'cancelled'
                            }">
                                {{ link.status }}
                            </span>
                        </td>
                        <td>{{ link.usage_count }} / {{ link.max_usage || '∞' }}</td>
                        <td>{{ link.expires_at | date:'short' }}</td>
                        <td>
                            <button class="btn btn-sm btn-outline-primary" ng-click="plc.copyLink(link)">
                                <i class="bi bi-clipboard"></i> Copy
                            </button>
                        </td>
                    </tr>
                    <tr ng-if="plc.paymentLinks.length === 0">
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="bi bi-inbox" style="font-size: 48px;"></i>
                            <p class="mt-2">No payment links found</p>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div ng-if="plc.pagination.last_page > 1" class="d-flex justify-content-between align-items-center mt-4 flex-wrap gap-3">
            <div class="text-muted small">Showing {{ plc.pagination.from || 0 }} to {{ plc.pagination.to || 0 }} of {{ plc.pagination.total || 0 }} results</div>
            <div class="pagination">
                <a href="#" class="page-link" ng-if="plc.pagination.current_page > 1" ng-click="plc.loadPage(plc.pagination.current_page - 1)">Previous</a>
                <a href="#" class="page-link" ng-repeat="page in plc.getPaginationPages() track by page" ng-class="{'active': page === plc.pagination.current_page}" ng-click="plc.loadPage(page)">{{ page }}</a>
                <a href="#" class="page-link" ng-if="plc.pagination.current_page < plc.pagination.last_page" ng-click="plc.loadPage(plc.pagination.current_page + 1)">Next</a>
            </div>
        </div>
    </div>
</div>

<?php /**PATH C:\Users\pc\Desktop\Badlicash-Payment-Gateway\resources\views/merchant/paymentlinks/grid.blade.php ENDPATH**/ ?>