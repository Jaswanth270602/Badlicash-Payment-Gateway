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
            app.controller('PaymentLinksController', ['$http', '$window', '$timeout', function($http, $window, $timeout) {
        var vm = this;
        vm.paymentLinks = [];
        vm.pagination = { current_page: 1, per_page: 10, total: 0, last_page: 1, from: 0, to: 0 };
        vm.perPage = 10;
        vm.filters = { status: 'all', search: '' };
        vm.loading = false;
        vm.creating = false;
        vm.newLink = { 
            title: '', 
            description: '', 
            amount: '', 
            currency: 'INR', 
            expires_in_hours: 24,
            payment_methods: {
                card: true,
                upi: true,
                netbanking: true,
                wallet: true
            }
        };
        vm.toastMessage = '';
        vm.toastType = 'success';

        vm.loadPaymentLinks = function() {
            vm.loading = true;
            var params = {
                page: vm.pagination.current_page,
                per_page: vm.perPage,
                status: vm.filters.status === 'all' ? '' : vm.filters.status,
                search: vm.filters.search || ''
            };
            
            $http.get('/merchant/payment-links/data', { params: params }).then(function(response) {
                vm.paymentLinks = response.data.data || [];
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
                vm.showToast('Failed to load payment links', 'error');
            });
        };

        var filterTimeout;
        vm.applyFilters = function() {
            if (filterTimeout) $timeout.cancel(filterTimeout);
            filterTimeout = $timeout(function() {
                vm.pagination.current_page = 1;
                vm.loadPaymentLinks();
            }, 300);
        };

        vm.clearFilters = function() {
            vm.filters = { status: 'all', search: '' };
            vm.pagination.current_page = 1;
            vm.loadPaymentLinks();
        };

        vm.loadPage = function(page) {
            if (page < 1 || page > vm.pagination.last_page) return;
            vm.pagination.current_page = page;
            vm.loadPaymentLinks();
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

        vm.createPaymentLink = function(event) {
            if (event) {
                event.preventDefault();
                event.stopPropagation();
            }
            
            // Validation
            if (!vm.newLink.title || !vm.newLink.amount) {
                vm.showToast('Please fill in all required fields', 'error');
                return false;
            }

            // Check if at least one payment method is selected
            var methods = vm.newLink.payment_methods || {};
            var hasMethod = (methods.card === true) || (methods.upi === true) || (methods.netbanking === true) || (methods.wallet === true);
            if (!hasMethod) {
                vm.showToast('Please select at least one payment method', 'error');
                return false;
            }

            // Prevent double submission
            if (vm.creating) {
                return false;
            }

            vm.creating = true;
            var csrf = document.querySelector('meta[name="csrf-token"]').content;
            
            // Build payment methods array
            var paymentMethods = [];
            if (methods.card === true) paymentMethods.push('card');
            if (methods.upi === true) paymentMethods.push('upi');
            if (methods.netbanking === true) paymentMethods.push('netbanking');
            if (methods.wallet === true) paymentMethods.push('wallet');
            
            var payload = {
                title: vm.newLink.title,
                description: vm.newLink.description || '',
                amount: parseFloat(vm.newLink.amount),
                currency: vm.newLink.currency || 'INR',
                expires_in_hours: parseInt(vm.newLink.expires_in_hours) || 24,
                payment_methods: paymentMethods
            };
            
            $http.post('/merchant/payment-links', payload, {
                headers: { 'X-CSRF-TOKEN': csrf }
            }).then(function(response) {
                vm.creating = false;
                if (response && response.data && response.data.success) {
                    vm.showToast('Payment link created successfully', 'success');
                    // Reset form
                    vm.newLink = { 
                        title: '', 
                        description: '', 
                        amount: '', 
                        currency: 'INR', 
                        expires_in_hours: 24,
                        payment_methods: {
                            card: true,
                            upi: true,
                            netbanking: true,
                            wallet: true
                        }
                    };
                    // Close modal
                    $timeout(function() {
                        var modalEl = document.getElementById('createLinkModal');
                        if (modalEl) {
                            var modal = bootstrap.Modal.getInstance(modalEl);
                            if (modal) {
                                modal.hide();
                            } else {
                                // If no instance, try to hide using jQuery/bootstrap method
                                var bsModal = new bootstrap.Modal(modalEl);
                                bsModal.hide();
                            }
                        }
                    }, 100);
                    vm.loadPaymentLinks();
                } else {
                    var msg = 'Failed to create payment link';
                    if (response && response.data && response.data.message) {
                        msg = response.data.message;
                    }
                    vm.showToast(msg, 'error');
                }
            }, function(error) {
                vm.creating = false;
                var errorMsg = 'Failed to create payment link';
                if (error && error.data) {
                    if (error.data.message) {
                        errorMsg = error.data.message;
                    } else if (error.data.errors) {
                        var errors = error.data.errors;
                        var firstErrorKey = Object.keys(errors)[0];
                        if (firstErrorKey && errors[firstErrorKey]) {
                            if (Array.isArray(errors[firstErrorKey])) {
                                errorMsg = errors[firstErrorKey][0];
                            } else {
                                errorMsg = errors[firstErrorKey];
                            }
                        }
                    }
                }
                console.error('Error creating payment link:', error);
                vm.showToast(errorMsg, 'error');
            });
            
            return false;
        };

        vm.copyLink = function(link) {
            var url = $window.location.origin + '/pay/' + link.link_token;
            var textarea = document.createElement('textarea');
            textarea.value = url;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            try {
                document.execCommand('copy');
                vm.showToast('Link copied to clipboard', 'success');
            } catch(e) {
                vm.showToast('Copy failed', 'error');
            }
            document.body.removeChild(textarea);
        };

        vm.showToast = function(msg, type) {
            // Update values
            vm.toastMessage = msg || '';
            vm.toastType = type || 'success';
            
            // Use $timeout to ensure Angular digest cycle runs
            $timeout(function() {
                var el = document.getElementById('toast');
                if (el && vm.toastMessage) {
                    // Ensure toast-body content is updated
                    var toastBody = el.querySelector('.toast-body');
                    if (toastBody) {
                        toastBody.textContent = vm.toastMessage;
                    }
                    
                    var toast = bootstrap.Toast.getInstance(el) || new bootstrap.Toast(el, {
                        autohide: true,
                        delay: 3000
                    });
                    toast.show();
                }
            }, 100);
        };

        vm.loadPaymentLinks();
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
<?php $__env->stopPush(); ?>

<?php /**PATH C:\Users\dell\Desktop\gateway\resources\views/merchant/paymentlinks/angular/main_controller.blade.php ENDPATH**/ ?>