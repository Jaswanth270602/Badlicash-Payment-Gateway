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
            app.controller('PaymentLinksController', ['$http', '$window', '$timeout', '$scope', function($http, $window, $timeout, $scope) {
                var vm = this;
                
                // Initialize all data
                vm.paymentLinks = [];
                vm.pagination = { 
                    current_page: 1, 
                    per_page: 10, 
                    total: 0, 
                    last_page: 1, 
                    from: 0, 
                    to: 0 
                };
                vm.perPage = 10;
                vm.filters = { status: 'all', search: '' };
                vm.loading = false;
                vm.creating = false;
                vm.newLink = { 
                    title: '', 
                    description: '', 
                    amount: '', 
                    currency: 'INR', 
                    expires_in_hours: 24
                };
                vm.toastMessage = '';
                vm.toastType = 'success';

                // Initialize modal - COMPLETELY RESET STATE
                vm.initModal = function() {
                    vm.creating = false;
                    vm.newLink = { 
                        title: '', 
                        description: '', 
                        amount: '', 
                        currency: 'INR', 
                        expires_in_hours: 24
                    };
                    // Force scope apply
                    if (!$scope.$$phase) {
                        $scope.$apply();
                    }
                };

                // Load payment links
                vm.loadPaymentLinks = function() {
                    vm.loading = true;
                    
                    var params = {
                        page: vm.pagination.current_page,
                        per_page: vm.perPage,
                        status: vm.filters.status === 'all' ? '' : vm.filters.status,
                        search: vm.filters.search || ''
                    };
                    
                    $http.get('/merchant/payment-links/data', { 
                        params: params,
                        timeout: 30000
                    }).then(function(response) {
                        if (response && response.data && response.data.success) {
                            vm.paymentLinks = response.data.data || [];
                            if (response.data.pagination) {
                                vm.pagination = {
                                    current_page: response.data.pagination.current_page || 1,
                                    last_page: response.data.pagination.last_page || 1,
                                    total: response.data.pagination.total || 0,
                                    from: response.data.pagination.from || 0,
                                    to: response.data.pagination.to || 0,
                                    per_page: response.data.pagination.per_page || 10
                                };
                            }
                        } else {
                            vm.paymentLinks = [];
                            if (response && response.data && response.data.message) {
                                vm.showToast(response.data.message, 'error');
                            }
                        }
                        vm.loading = false;
                    }, function(error) {
                        console.error('Error loading payment links:', error);
                        vm.loading = false;
                        vm.paymentLinks = [];
                        
                        var errorMsg = 'Failed to load payment links';
                        if (error && error.data && error.data.message) {
                            errorMsg = error.data.message;
                        } else if (error && error.status === -1) {
                            errorMsg = 'Request timeout. Please refresh the page.';
                        }
                        vm.showToast(errorMsg, 'error');
                    });
                };

                // Apply filters
                var filterTimeout;
                vm.applyFilters = function() {
                    if (filterTimeout) {
                        $timeout.cancel(filterTimeout);
                    }
                    filterTimeout = $timeout(function() {
                        vm.pagination.current_page = 1;
                        vm.loadPaymentLinks();
                    }, 300);
                };

                // Clear filters
                vm.clearFilters = function() {
                    vm.filters = { status: 'all', search: '' };
                    vm.pagination.current_page = 1;
                    vm.applyFilters();
                };

                // Change page
                vm.loadPage = function(page) {
                    if (page >= 1 && page <= vm.pagination.last_page) {
                        vm.pagination.current_page = page;
                        vm.loadPaymentLinks();
                    }
                };

                // Get pagination pages
                vm.getPaginationPages = function() {
                    var pages = [];
                    var start = Math.max(1, vm.pagination.current_page - 2);
                    var end = Math.min(vm.pagination.last_page, vm.pagination.current_page + 2);
                    for (var i = start; i <= end; i++) {
                        pages.push(i);
                    }
                    return pages;
                };

                // Create payment link - COMPLETELY REWRITTEN
                vm.createPaymentLink = function(event) {
                    if (event) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    
                    // PREVENT DUPLICATE - check immediately
                    if (vm.creating === true) {
                        return false;
                    }
                    
                    // Validate
                    if (!vm.newLink.title || !String(vm.newLink.title).trim()) {
                        vm.showToast('Please enter a title', 'error');
                        return false;
                    }

                    if (!vm.newLink.amount) {
                        vm.showToast('Please enter an amount', 'error');
                        return false;
                    }

                    var amount = parseFloat(vm.newLink.amount);
                    if (isNaN(amount) || amount <= 0) {
                        vm.showToast('Please enter a valid amount greater than 0', 'error');
                        return false;
                    }

                    // Get CSRF
                    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                    if (!csrfMeta) {
                        vm.showToast('CSRF token not found. Please refresh the page.', 'error');
                        return false;
                    }
                    var csrfToken = csrfMeta.getAttribute('content');

                    // SET CREATING FLAG FIRST - then apply scope
                    vm.creating = true;
                    if (!$scope.$$phase) {
                        $scope.$apply();
                    }

                    // Payload
                    var payload = {
                        title: String(vm.newLink.title).trim(),
                        description: vm.newLink.description ? String(vm.newLink.description).trim() : '',
                        amount: amount,
                        currency: vm.newLink.currency || 'INR',
                        expires_in_hours: parseInt(vm.newLink.expires_in_hours) || 24,
                        payment_methods: ['card', 'upi', 'netbanking', 'wallet']
                    };

                    // HTTP Request
                    $http.post('/merchant/payment-links', payload, {
                        headers: {
                            'X-CSRF-TOKEN': csrfToken
                        },
                        timeout: 30000
                    }).then(function(response) {
                        vm.creating = false;
                        if (!$scope.$$phase) {
                            $scope.$apply();
                        }
                        
                        if (response && response.data && response.data.success) {
                            vm.showToast('Payment link created successfully!', 'success');
                            vm.initModal();
                            
                            $timeout(function() {
                                var modalEl = document.getElementById('createLinkModal');
                                if (modalEl) {
                                    var bsModal = bootstrap.Modal.getInstance(modalEl);
                                    if (bsModal) {
                                        bsModal.hide();
                                    }
                                }
                            }, 500);
                            
                            $timeout(function() {
                                vm.loadPaymentLinks();
                            }, 800);
                        } else {
                            var msg = response && response.data && response.data.message 
                                ? response.data.message 
                                : 'Failed to create payment link';
                            vm.showToast(msg, 'error');
                        }
                    }, function(error) {
                        vm.creating = false;
                        if (!$scope.$$phase) {
                            $scope.$apply();
                        }
                        
                        var errorMsg = 'Failed to create payment link';
                        if (error && error.data) {
                            if (error.data.message) {
                                errorMsg = error.data.message;
                            } else if (error.data.errors) {
                                var errors = error.data.errors;
                                var firstKey = Object.keys(errors)[0];
                                if (firstKey && errors[firstKey]) {
                                    errorMsg = Array.isArray(errors[firstKey]) 
                                        ? errors[firstKey][0] 
                                        : String(errors[firstKey]);
                                }
                            }
                        } else if (error && error.status === -1) {
                            errorMsg = 'Request timeout. Please try again.';
                        }
                        
                        vm.showToast(errorMsg, 'error');
                    });
                    
                    return false;
                };

                // Copy link
                vm.copyLink = function(link) {
                    var url = $window.location.origin + '/pay/' + link.link_token;
                    var textarea = document.createElement('textarea');
                    textarea.value = url;
                    textarea.style.position = 'fixed';
                    textarea.style.left = '-9999px';
                    document.body.appendChild(textarea);
                    textarea.select();
                    
                    try {
                        document.execCommand('copy');
                        document.body.removeChild(textarea);
                        vm.showToast('Payment link copied to clipboard!', 'success');
                    } catch(e) {
                        document.body.removeChild(textarea);
                        vm.showToast('Failed to copy. Please copy manually.', 'error');
                    }
                };

                // Show toast
                vm.showToast = function(msg, type) {
                    vm.toastMessage = msg || '';
                    vm.toastType = type || 'success';
                    
                    $timeout(function() {
                        var toastElement = document.getElementById('toast');
                        if (toastElement && vm.toastMessage) {
                            var toastBody = toastElement.querySelector('.toast-body');
                            if (toastBody) {
                                toastBody.textContent = vm.toastMessage;
                            }
                            
                            var toastInstance = bootstrap.Toast.getInstance(toastElement);
                            if (!toastInstance) {
                                toastInstance = new bootstrap.Toast(toastElement, {
                                    autohide: true,
                                    delay: 4000
                                });
                            }
                            toastInstance.show();
                        }
                    }, 50);
                };

                // Setup modal listeners - ENHANCED
                $timeout(function() {
                    var modalEl = document.getElementById('createLinkModal');
                    if (modalEl) {
                        modalEl.addEventListener('show.bs.modal', function() {
                            vm.creating = false;
                            vm.initModal();
                        });
                        
                        modalEl.addEventListener('hidden.bs.modal', function() {
                            vm.creating = false;
                            vm.initModal();
                        });
                    }
                }, 500);

                // Initial load
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
<?php /**PATH C:\Users\pc\Desktop\Badlicash-Payment-Gateway\resources\views/merchant/paymentlinks/angular/main_controller.blade.php ENDPATH**/ ?>