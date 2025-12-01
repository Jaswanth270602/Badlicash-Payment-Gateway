@push('scripts')
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
            app.controller('RefundsController', ['$http', '$timeout', function($http, $timeout) {
        var vm = this;
        vm.refunds = [];
        vm.loading = false;
        vm.creating = false;
        vm.perPage = 10;
        vm.pagination = { current_page: 1, last_page: 1, total: 0, from: 0, to: 0, per_page: 10 };
        vm.filters = { status: '', from_date: '', to_date: '', search: '' };
        vm.newRefund = { transaction_id: '', amount: '', reason: '' };

        vm.loadRefunds = function() {
            vm.loading = true;
            var params = {
                page: vm.pagination.current_page,
                per_page: vm.perPage,
                status: vm.filters.status || '',
                from_date: vm.filters.from_date || '',
                to_date: vm.filters.to_date || '',
                search: vm.filters.search || ''
            };
            
            $http.get('/merchant/refunds/data', { params: params }).then(function(response) {
                vm.refunds = response.data.data || [];
                vm.pagination = {
                    current_page: response.data.pagination.current_page,
                    last_page: response.data.pagination.last_page,
                    total: response.data.pagination.total,
                    from: response.data.pagination.from,
                    to: response.data.pagination.to,
                    per_page: response.data.pagination.per_page
                };
                vm.loading = false;
            }, function(error) {
                vm.loading = false;
                alert('Unable to load refunds. Please try again.');
                console.error('Error loading refunds:', error);
            });
        };

        vm.createRefund = function() {
            if (!vm.newRefund.transaction_id || !vm.newRefund.amount) {
                alert('Please fill in all required fields');
                return;
            }

            vm.creating = true;
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            $http.post('/merchant/refunds', {
                transaction_id: vm.newRefund.transaction_id,
                amount: parseFloat(vm.newRefund.amount),
                reason: vm.newRefund.reason || ''
            }, {
                headers: { 'X-CSRF-TOKEN': csrf }
            }).then(function(response) {
                vm.creating = false;
                if (response.data.success) {
                    var modal = bootstrap.Modal.getInstance(document.getElementById('createRefundModal'));
                    if (modal) modal.hide();
                    vm.newRefund = { transaction_id: '', amount: '', reason: '' };
                    vm.loadRefunds();
                    alert('Refund created successfully');
                }
            }, function(error) {
                vm.creating = false;
                alert(error.data?.message || 'Failed to create refund');
            });
        };

        var filterTimeout;
        vm.applyFilters = function() {
            if (filterTimeout) $timeout.cancel(filterTimeout);
            filterTimeout = $timeout(function() {
                vm.pagination.current_page = 1;
                vm.loadRefunds();
            }, 300);
        };

        vm.clearFilters = function() {
            vm.filters = { status: '', from_date: '', to_date: '', search: '' };
            vm.pagination.current_page = 1;
            vm.loadRefunds();
        };

        vm.loadPage = function(page) {
            if (page < 1 || page > vm.pagination.last_page) return;
            vm.pagination.current_page = page;
            vm.loadRefunds();
        };

        vm.getPaginationPages = function() {
            var pages = [];
            var start = Math.max(1, vm.pagination.current_page - 2);
            var end = Math.min(vm.pagination.last_page, vm.pagination.current_page + 2);
            for (var i = start; i <= end; i++) {
                pages.push(i);
            }
            return pages;
        };

        vm.loadRefunds();
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
@endpush

