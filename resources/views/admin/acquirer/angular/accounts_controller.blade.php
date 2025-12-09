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
            app.controller('AdminAcquirerAccountsController', ['$http', '$scope', '$timeout', '$compile', function($http, $scope, $timeout, $compile) {
                var vm = this;
                var csrf = document.querySelector('meta[name="csrf-token"]').content;
                vm.accounts = [];
                vm.pagination = { current_page: 1, per_page: 5, total: 0, last_page: 1 };
                vm.filters = {
                    filter_id: '',
                    filter_account_id: '',
                    filter_acquirer_name: 'all',
                    filter_team: '',
                    filter_description: '',
                    filter_whitelist_url: '',
                    filter_mode: 'all',
                    filter_sector: 'all',
                    filter_hdfc_me_code: '',
                    filter_settlement_account_name: '',
                    filter_email_ids: '',
                    filter_live_request_url: '',
                    filter_live_query_url: '',
                    filter_live_refund_url: '',
                    filter_test_request_url: '',
                    filter_test_query_url: '',
                    filter_test_refund_url: '',
                    filter_merchants: ''
                };
                vm.loading = false;
                vm.submitting = false;
                vm.selectedAccount = null;
                vm.sortColumn = 'id';
                vm.sortDirection = 'desc';
                vm.modalTitle = 'Create new entry';
                vm.isEditMode = false;

                // Column visibility
                vm.visibleColumns = {
                    id: { visible: true, label: 'Id' },
                    account_id: { visible: true, label: 'Account Id' },
                    acquirer_name: { visible: true, label: 'Acquirer Name' },
                    team: { visible: true, label: 'Team' },
                    description: { visible: true, label: 'Description' },
                    whitelist_url: { visible: true, label: 'Whitelist Url' },
                    mode: { visible: true, label: 'Mode' },
                    sector: { visible: true, label: 'Sector' },
                    hdfc_me_code: { visible: true, label: 'Hdfc Me Code' },
                    settlement_account_name: { visible: true, label: 'Settlement Account Name' },
                    refund_allowed: { visible: true, label: 'Refund Allowed' },
                    settlements_to_be_created: { visible: true, label: 'Settlements to be created for this TID ?' },
                    mask_pii: { visible: true, label: 'Mask Pii' },
                    email_ids: { visible: true, label: 'Email Ids' },
                    live_request_url: { visible: false, label: 'Live Request URL' },
                    live_query_url: { visible: false, label: 'Live Query URL' },
                    live_refund_url: { visible: false, label: 'Live Refund URL' },
                    test_request_url: { visible: false, label: 'Test Request URL' },
                    test_query_url: { visible: false, label: 'Test Query URL' },
                    test_refund_url: { visible: false, label: 'Test Refund URL' },
                    merchants: { visible: true, label: 'Merchants' }
                };

                // Account form
                vm.accountForm = {
                    account_id: '',
                    acquirer_name: '',
                    team: '',
                    description: '',
                    whitelist_url: '',
                    mode: 'TEST',
                    sector: '',
                    hdfc_me_code: '',
                    settlement_account_name: '',
                    refund_allowed: true,
                    settlements_to_be_created: true,
                    mask_pii: false,
                    email_ids: '',
                    secret_key: '',
                    salt: '',
                    additional_key_1: '',
                    additional_key_2: '',
                    additional_key_3: '',
                    additional_key_data: '',
                    live_request_url: '',
                    live_query_url: '',
                    live_refund_url: '',
                    test_request_url: '',
                    test_query_url: '',
                    test_refund_url: '',
                    nodal_account: '',
                    merchant_ids: []
                };

                vm.acquirerNames = ['A2Pay', 'Paytm', 'Switch', 'HDFC', 'ICICI', 'Axis', 'SBI', 'Razorpay', 'PayU'];
                vm.sectors = ['B2B', 'Education', 'E-commerce', 'Travel & Hospitality', 'Insurance', 'Utilities', 'Telecom', 'Healthcare', 'Others'];
                vm.merchants = [];

                // Load acquirer names
                vm.loadAcquirerNames = function() {
                    $http.get('/admin/acquirer-accounts/acquirer-names', {
                        headers: { 'X-CSRF-TOKEN': csrf }
                    }).then(function(response) {
                        if (response.data.success) {
                            vm.acquirerNames = response.data.data;
                        }
                    });
                };

                // Load merchants
                vm.loadMerchants = function() {
                    $http.get('/admin/acquirer-accounts/merchants', {
                        headers: { 'X-CSRF-TOKEN': csrf }
                    }).then(function(response) {
                        if (response.data.success) {
                            vm.merchants = response.data.data;
                        }
                    });
                };

                // Load accounts
                vm.loadAccounts = function(page) {
                    if (page) vm.pagination.current_page = page;
                    vm.loading = true;
                    
                    var params = {
                        page: vm.pagination.current_page,
                        per_page: vm.pagination.per_page,
                        sort_by: vm.sortColumn,
                        sort_direction: vm.sortDirection
                    };

                    // Add filters
                    if (vm.filters.filter_acquirer_name && vm.filters.filter_acquirer_name !== 'all') {
                        params.acquirer_name = vm.filters.filter_acquirer_name;
                    }
                    if (vm.filters.filter_mode && vm.filters.filter_mode !== 'all') {
                        params.mode = vm.filters.filter_mode;
                    }
                    if (vm.filters.filter_sector && vm.filters.filter_sector !== 'all') {
                        params.sector = vm.filters.filter_sector;
                    }
                    if (vm.filters.filter_team) {
                        params.team = vm.filters.filter_team;
                    }

                    // Add search
                    var searchTerms = [];
                    if (vm.filters.filter_id) searchTerms.push(vm.filters.filter_id);
                    if (vm.filters.filter_account_id) searchTerms.push(vm.filters.filter_account_id);
                    if (vm.filters.filter_description) searchTerms.push(vm.filters.filter_description);
                    if (vm.filters.filter_hdfc_me_code) searchTerms.push(vm.filters.filter_hdfc_me_code);
                    if (searchTerms.length > 0) {
                        params.search = searchTerms.join(' ');
                    }

                    $http.get('/admin/acquirer-accounts/data', {
                        params: params,
                        headers: { 'X-CSRF-TOKEN': csrf }
                    }).then(function(response) {
                        if (response.data.success) {
                            vm.accounts = response.data.data;
                            vm.pagination = response.data.pagination;
                        }
                        vm.loading = false;
                    }).catch(function(error) {
                        console.error('Error loading accounts:', error);
                        vm.loading = false;
                    });
                };

                // Select account
                vm.selectAccount = function(account) {
                    vm.selectedAccount = account;
                };

                // Clear filters
                vm.clearFilters = function() {
                    vm.filters = {
                        filter_id: '',
                        filter_account_id: '',
                        filter_acquirer_name: 'all',
                        filter_team: '',
                        filter_description: '',
                        filter_whitelist_url: '',
                        filter_mode: 'all',
                        filter_sector: 'all',
                        filter_hdfc_me_code: '',
                        filter_settlement_account_name: '',
                        filter_email_ids: '',
                        filter_live_request_url: '',
                        filter_live_query_url: '',
                        filter_live_refund_url: '',
                        filter_test_request_url: '',
                        filter_test_query_url: '',
                        filter_test_refund_url: '',
                        filter_merchants: ''
                    };
                    vm.loadAccounts();
                };

                // Apply filters
                vm.applyFilters = function() {
                    vm.pagination.current_page = 1;
                    vm.loadAccounts();
                };

                // Toggle column visibility
                vm.toggleColumn = function(key) {
                    if (vm.visibleColumns[key]) {
                        vm.visibleColumns[key].visible = !vm.visibleColumns[key].visible;
                    }
                };

                // Reset view
                vm.resetView = function() {
                    vm.clearFilters();
                    // Reset column visibility to defaults
                    Object.keys(vm.visibleColumns).forEach(function(key) {
                        vm.visibleColumns[key].visible = true;
                    });
                    vm.loadAccounts();
                };

                // Sort
                vm.sortBy = function(column) {
                    if (vm.sortColumn === column) {
                        vm.sortDirection = vm.sortDirection === 'asc' ? 'desc' : 'asc';
                    } else {
                        vm.sortColumn = column;
                        vm.sortDirection = 'asc';
                    }
                    vm.loadAccounts();
                };

                // Open new modal
                vm.openNewModal = function() {
                    vm.modalTitle = 'Create new entry';
                    vm.isEditMode = false;
                    vm.accountForm = {
                        account_id: '',
                        acquirer_name: '',
                        team: '',
                        description: '',
                        whitelist_url: '',
                        mode: 'TEST',
                        sector: '',
                        hdfc_me_code: '',
                        settlement_account_name: '',
                        refund_allowed: true,
                        settlements_to_be_created: true,
                        mask_pii: false,
                        email_ids: '',
                        secret_key: '',
                        salt: '',
                        additional_key_1: '',
                        additional_key_2: '',
                        additional_key_3: '',
                        additional_key_data: '',
                        live_request_url: '',
                        live_query_url: '',
                        live_refund_url: '',
                        test_request_url: '',
                        test_query_url: '',
                        test_refund_url: '',
                        nodal_account: '',
                        merchant_ids: []
                    };
                    var modalElement = document.getElementById('acquirerAccountModal');
                    var modal = new bootstrap.Modal(modalElement);
                    
                    // Ensure Angular compiles the modal when shown
                    modalElement.addEventListener('shown.bs.modal', function() {
                        $timeout(function() {
                            // Manually trigger Angular digest cycle
                            if (!$scope.$$phase && !$scope.$root.$$phase) {
                                $scope.$apply();
                            }
                        }, 100);
                    }, { once: true });
                    
                    modal.show();
                };

                // Open edit modal
                vm.openEditModal = function(account) {
                    if (account) vm.selectedAccount = account;
                    if (!vm.selectedAccount) return;

                    vm.modalTitle = 'Edit Acquirer Account';
                    vm.isEditMode = true;
                    vm.accountForm = {
                        account_id: vm.selectedAccount.account_id,
                        acquirer_name: vm.selectedAccount.acquirer_name,
                        team: vm.selectedAccount.team || '',
                        description: vm.selectedAccount.description || '',
                        whitelist_url: vm.selectedAccount.whitelist_url || '',
                        mode: vm.selectedAccount.mode || 'TEST',
                        sector: vm.selectedAccount.sector || '',
                        hdfc_me_code: vm.selectedAccount.hdfc_me_code || '',
                        settlement_account_name: vm.selectedAccount.settlement_account_name || '',
                        refund_allowed: vm.selectedAccount.refund_allowed,
                        settlements_to_be_created: vm.selectedAccount.settlements_to_be_created,
                        mask_pii: vm.selectedAccount.mask_pii,
                        email_ids: vm.selectedAccount.email_ids || '',
                        secret_key: '',
                        salt: '',
                        additional_key_1: '',
                        additional_key_2: '',
                        additional_key_3: '',
                        additional_key_data: '',
                        live_request_url: vm.selectedAccount.live_request_url || '',
                        live_query_url: vm.selectedAccount.live_query_url || '',
                        live_refund_url: vm.selectedAccount.live_refund_url || '',
                        test_request_url: vm.selectedAccount.test_request_url || '',
                        test_query_url: vm.selectedAccount.test_query_url || '',
                        test_refund_url: vm.selectedAccount.test_refund_url || '',
                        nodal_account: '',
                        merchant_ids: vm.selectedAccount.merchant_ids || []
                    };

                    var modalElement = document.getElementById('acquirerAccountModal');
                    var modal = new bootstrap.Modal(modalElement);
                    
                    // Ensure Angular compiles the modal when shown
                    modalElement.addEventListener('shown.bs.modal', function() {
                        $timeout(function() {
                            // Manually trigger Angular digest cycle
                            if (!$scope.$$phase && !$scope.$root.$$phase) {
                                $scope.$apply();
                            }
                        }, 100);
                    }, { once: true });
                    
                    modal.show();
                };

                // Submit account
                vm.submitAccount = function() {
                    vm.submitting = true;

                    var url = vm.isEditMode 
                        ? '/admin/acquirer-accounts/' + vm.selectedAccount.id
                        : '/admin/acquirer-accounts';
                    var method = vm.isEditMode ? 'PUT' : 'POST';

                    var data = angular.copy(vm.accountForm);
                    data.refund_allowed = data.refund_allowed === true || data.refund_allowed === 'true';
                    data.settlements_to_be_created = data.settlements_to_be_created === true || data.settlements_to_be_created === 'true';
                    data.mask_pii = data.mask_pii === true || data.mask_pii === 'true';

                    $http({
                        method: method,
                        url: url,
                        data: data,
                        headers: { 'X-CSRF-TOKEN': csrf }
                    }).then(function(response) {
                        if (response.data.success) {
                            alert(response.data.message);
                            var modal = bootstrap.Modal.getInstance(document.getElementById('acquirerAccountModal'));
                            modal.hide();
                            vm.loadAccounts();
                        } else {
                            alert('Error: ' + (response.data.message || 'Failed to save account'));
                        }
                        vm.submitting = false;
                    }).catch(function(error) {
                        console.error('Error saving account:', error);
                        var errorMsg = error.data && error.data.message 
                            ? error.data.message 
                            : 'Failed to save account';
                        if (error.data && error.data.errors) {
                            var errors = Object.values(error.data.errors).flat().join(', ');
                            errorMsg += ': ' + errors;
                        }
                        alert(errorMsg);
                        vm.submitting = false;
                    });
                };

                // Delete account
                vm.deleteAccount = function() {
                    if (!vm.selectedAccount) return;
                    if (!confirm('Are you sure you want to delete this acquirer account?')) return;

                    $http.delete('/admin/acquirer-accounts/' + vm.selectedAccount.id, {
                        headers: { 'X-CSRF-TOKEN': csrf }
                    }).then(function(response) {
                        if (response.data.success) {
                            alert(response.data.message);
                            vm.selectedAccount = null;
                            vm.loadAccounts();
                        } else {
                            alert('Error: ' + (response.data.message || 'Failed to delete account'));
                        }
                    }).catch(function(error) {
                        console.error('Error deleting account:', error);
                        alert('Failed to delete account');
                    });
                };

                // Initialize
                vm.loadAcquirerNames();
                vm.loadMerchants();
                vm.loadAccounts();
            }]);
        } catch(e) {
            console.error('Error registering controller:', e);
            setTimeout(registerController, 100);
        }
    }
    registerController();
})();
</script>
@endpush

