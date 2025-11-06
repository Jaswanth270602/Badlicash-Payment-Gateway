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
            app.controller('AdminDisputesController', ['$http', function($http) {
                var vm = this;
                vm.filters = { merchant_id: '', status: '' };
                vm.items = { data: [] };

                vm.load = function(page) {
                    var params = { merchant_id: vm.filters.merchant_id || '', status: vm.filters.status || '' };
                    if (page) params.page = page;
                    $http.get('/admin/disputes/data', { params: params }).then(function(resp) {
                        vm.items = resp.data.data;
                    });
                };

                vm.updateStatus = function(d) {
                    var payload = { status: d.status, evidence_url: d.evidence_url || '' };
                    $http.post('/admin/disputes/' + d.id + '/status', payload).then(function() {
                        // refreshed
                    }, function() {
                        alert('Failed to update');
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
<?php $__env->stopPush(); ?>


<?php /**PATH C:\Users\pc\Desktop\Badlicash-Payment-Gateway\resources\views/admin/disputes/angular/main_controller.blade.php ENDPATH**/ ?>