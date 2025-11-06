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
            app.controller('AdminReportsController', ['$http', function($http) {
                var vm = this;
                vm.filters = { merchant_id: '', from_date: '', to_date: '' };
                vm.reportData = null;
                vm.generating = false;
                vm.exporting = false;

                vm.generateReport = function() {
                    vm.generating = true;
                    var params = {
                        merchant_id: vm.filters.merchant_id || '',
                        from_date: vm.filters.from_date || '',
                        to_date: vm.filters.to_date || ''
                    };

                    $http.get('/admin/reports/data', { params: params }).then(function(response) {
                        vm.reportData = response.data.data || null;
                        vm.generating = false;
                    }, function(error) {
                        vm.generating = false;
                        alert('Unable to generate report. Please try again.');
                        console.error('Error generating admin report:', error);
                    });
                };

                vm.exportReport = function() {
                    vm.exporting = true;
                    var params = new URLSearchParams();
                    if (vm.filters.merchant_id) params.append('merchant_id', vm.filters.merchant_id);
                    if (vm.filters.from_date) params.append('from_date', vm.filters.from_date);
                    if (vm.filters.to_date) params.append('to_date', vm.filters.to_date);

                    window.location.href = '/admin/reports/export?' + params.toString();
                    setTimeout(function() {
                        vm.exporting = false;
                    }, 1000);
                };
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


<?php /**PATH C:\Users\pc\Desktop\Badlicash-Payment-Gateway\resources\views/admin/reports/angular/main_controller.blade.php ENDPATH**/ ?>