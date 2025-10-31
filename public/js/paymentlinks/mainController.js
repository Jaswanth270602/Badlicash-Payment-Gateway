/**
 * BadliCash Angular App - Payment Links Controller
 * 
 * This controller manages the payment links view with two-way binding,
 * pagination, filtering, and AJAX calls to the Laravel backend.
 */

(function() {
    'use strict';

    // Create Angular app
    var app = angular.module('badlicashApp', []);

    // Payment Links Controller
    app.controller('PaymentLinksController', ['$http', '$window', PaymentLinksController]);

    function PaymentLinksController($http, $window) {
        var vm = this;

        // Data properties
        vm.paymentLinks = [];
        vm.pagination = {
            current_page: 1,
            per_page: 10,
            total: 0,
            last_page: 1
        };
        vm.perPage = 10;
        vm.filters = {
            status: 'all',
            search: ''
        };
        vm.loading = false;
        vm.creating = false;

        // New payment link form
        vm.newLink = {
            title: '',
            description: '',
            amount: '',
            currency: 'USD',
            expires_in_hours: 24
        };

        // Toast notification
        vm.toastMessage = '';
        vm.toastType = 'success';

        // Methods
        vm.loadPaymentLinks = loadPaymentLinks;
        vm.loadPage = loadPage;
        vm.getPages = getPages;
        vm.createPaymentLink = createPaymentLink;
        vm.copyLink = copyLink;

        // Initialize
        init();

        function init() {
            loadPaymentLinks();
        }

        /**
         * Load payment links from API
         */
        function loadPaymentLinks() {
            vm.loading = true;

            var params = {
                page: vm.pagination.current_page,
                per_page: vm.perPage,
                status: vm.filters.status,
                search: vm.filters.search
            };

            $http.get('/merchant/payment-links/data', { params: params })
                .then(function(response) {
                    vm.paymentLinks = response.data.data;
                    vm.pagination = response.data.pagination;
                    vm.loading = false;
                })
                .catch(function(error) {
                    console.error('Error loading payment links:', error);
                    vm.loading = false;
                    showToast('Failed to load payment links', 'error');
                });
        }

        /**
         * Load specific page
         */
        function loadPage(page) {
            if (page < 1 || page > vm.pagination.last_page) {
                return;
            }
            vm.pagination.current_page = page;
            loadPaymentLinks();
        }

        /**
         * Get array of page numbers for pagination
         */
        function getPages() {
            var pages = [];
            var start = Math.max(1, vm.pagination.current_page - 2);
            var end = Math.min(vm.pagination.last_page, vm.pagination.current_page + 2);

            for (var i = start; i <= end; i++) {
                pages.push(i);
            }

            return pages;
        }

        /**
         * Create new payment link
         */
        function createPaymentLink() {
            vm.creating = true;

            // Get CSRF token
            var csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            $http.post('/merchant/payment-links', vm.newLink, {
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            })
            .then(function(response) {
                vm.creating = false;
                showToast('Payment link created successfully', 'success');
                
                // Close modal
                var modal = bootstrap.Modal.getInstance(document.getElementById('createLinkModal'));
                if (modal) {
                    modal.hide();
                }

                // Reset form
                vm.newLink = {
                    title: '',
                    description: '',
                    amount: '',
                    currency: 'USD',
                    expires_in_hours: 24
                };

                // Reload list
                loadPaymentLinks();
            })
            .catch(function(error) {
                vm.creating = false;
                console.error('Error creating payment link:', error);
                showToast('Failed to create payment link', 'error');
            });
        }

        /**
         * Copy payment link to clipboard
         */
        function copyLink(link) {
            var paymentUrl = $window.location.origin + '/pay/' + link.link_token;
            
            // Create temporary textarea to copy
            var textarea = document.createElement('textarea');
            textarea.value = paymentUrl;
            textarea.style.position = 'fixed';
            textarea.style.opacity = '0';
            document.body.appendChild(textarea);
            textarea.select();
            
            try {
                document.execCommand('copy');
                showToast('Payment link copied to clipboard', 'success');
            } catch (err) {
                showToast('Failed to copy link', 'error');
            }
            
            document.body.removeChild(textarea);
        }

        /**
         * Show toast notification
         */
        function showToast(message, type) {
            vm.toastMessage = message;
            vm.toastType = type;

            var toastEl = document.getElementById('toast');
            var toast = new bootstrap.Toast(toastEl);
            toast.show();
        }
    }
})();

