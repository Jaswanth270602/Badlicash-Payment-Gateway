<?php $__env->startPush('scripts'); ?>
<script>
(function() {
    'use strict';
    // Register controller - wait for Angular to be ready
    function registerController() {
        if (typeof angular === 'undefined') {
            setTimeout(registerController, 50);
            return;
        }
        
        try {
            var app = angular.module('badlicashApp');
            app.controller('DashboardController', ['$http', function($http) {
                var vm = this;
                vm.loading = false;
                vm.recentTransactions = [];

                vm.loadRecentTransactions = function() {
                    vm.loading = true;
                    $http.get('/merchant/transactions/data', {
                        params: { page: 1, per_page: 5 }
                    }).then(function(response) {
                        vm.recentTransactions = response.data.data || [];
                        vm.loading = false;
                    }, function(error) {
                        vm.loading = false;
                        console.error('Error loading recent transactions:', error);
                    });
                };

                vm.loadRecentTransactions();
            }]);
        } catch(e) {
            setTimeout(registerController, 50);
        }
    }
    
    // Register immediately if Angular is ready, otherwise wait
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

<?php /**PATH C:\Users\pc\Desktop\Badlicash-Payment-Gateway\resources\views/merchant/dashboard/angular/main_controller.blade.php ENDPATH**/ ?>