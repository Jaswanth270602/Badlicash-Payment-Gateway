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
            app.controller('MerchantDisputesController', ['$http', function($http) {
                var vm = this;
                vm.filters = { status: '' };
                vm.items = { data: [] };
                vm.form = { transaction_id: '', reason: '', amount: '', notes: '' };
                vm.creating = false;

                vm.load = function(page) {
                    var params = { status: vm.filters.status || '' };
                    if (page) params.page = page;
                    $http.get('/merchant/disputes/data', { params: params }).then(function(resp) {
                        vm.items = resp.data.data;
                    });
                };

                vm.create = function() {
                    vm.creating = true;
                    $http.post('/merchant/disputes', vm.form).then(function() {
                        vm.creating = false;
                        vm.form = { transaction_id: '', reason: '', amount: '', notes: '' };
                        var modalEl = document.getElementById('newDisputeModal');
                        var modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modal.hide();
                        vm.load();
                    }, function() {
                        vm.creating = false;
                        alert('Failed to create dispute');
                    });
                };

                vm.load();
            }]);
        } catch(e) {
            setTimeout(registerController, 50);
        }
    }
    if (typeof angular !== 'undefined') {
        registerController();
    } else {
        setTimeout(registerController, 50);
    }
})();
</script>
@endpush


