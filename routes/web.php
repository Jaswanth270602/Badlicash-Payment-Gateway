<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\RegistrationController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Merchant\PaymentLinksController;
use App\Http\Controllers\Merchant\TransactionsController as MerchantTransactionsController;
use App\Http\Controllers\Merchant\OrdersController;
use App\Http\Controllers\Merchant\RefundsController as MerchantRefundsController;
use App\Http\Controllers\Merchant\SettlementsController;
use App\Http\Controllers\Merchant\SettingsController;
use App\Http\Controllers\Merchant\ProfileController;
use App\Http\Controllers\Merchant\ApiKeysController;
use App\Http\Controllers\Merchant\IntegrationController;
use App\Http\Controllers\Merchant\SubscriptionsController as MerchantSubscriptionsController;
use App\Http\Controllers\Merchant\WebhooksController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\MerchantsController;
use App\Http\Controllers\Admin\MerchantAccountsController;
use App\Http\Controllers\Admin\MerchantRegistrationKeysController;
use App\Http\Controllers\Admin\MerchantVendorsController;
use App\Http\Controllers\Admin\SettlementSummaryController;
use App\Http\Controllers\Admin\RefundsController as AdminRefundsController;
use App\Http\Controllers\Admin\TransactionsController as AdminTransactionsController;
use App\Http\Controllers\Admin\BulkRefundUpdateController;
use App\Http\Controllers\Admin\ChargebacksController;
use App\Http\Controllers\Admin\BulkChargebacksController;
use App\Http\Controllers\Admin\SplitTransactionsController;
use App\Http\Controllers\Admin\FederalVPAController;
use App\Http\Controllers\Admin\SettlementDetailsController;
use App\Http\Controllers\Admin\FundTransferController;
use App\Http\Controllers\Admin\PendingSettlementController;
use App\Http\Controllers\Admin\MISReportController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\DisputesController;
use App\Http\Controllers\PaymentCheckoutController;
use App\Http\Controllers\Admin\SubscriptionsController;
use App\Http\Controllers\Admin\RiskManagementController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AcquirerAccountsController;
use App\Http\Controllers\Admin\AcquirerAccountUploadController;
use App\Http\Controllers\Admin\AcquirerRatesController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [LandingController::class, 'index'])->name('landing');

// Public payment checkout
Route::get('/pay/{token}', [PaymentCheckoutController::class, 'show'])->name('payment.checkout');
Route::post('/pay/{token}', [PaymentCheckoutController::class, 'process'])->name('payment.process');
Route::get('/payment/success/{token}', [PaymentCheckoutController::class, 'success'])->name('payment.success');
Route::get('/payment/failed/{token}', [PaymentCheckoutController::class, 'failed'])->name('payment.failed');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public merchant signup
Route::middleware('guest')->group(function () {
    Route::get('/signup', [RegistrationController::class, 'showSignup'])->name('signup');
    Route::post('/signup', [RegistrationController::class, 'register'])->name('signup.post');
});

// Protected routes
Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Merchant routes
    Route::middleware(['merchant'])->prefix('merchant')->group(function () {
        // Payment Links
        Route::get('/payment-links', [PaymentLinksController::class, 'index'])
            ->name('merchant.payment_links.index');
        Route::post('/payment-links', [PaymentLinksController::class, 'store'])
            ->name('merchant.payment_links.store');
        Route::get('/payment-links/data', [PaymentLinksController::class, 'getData'])
            ->name('merchant.payment_links.data');

        // Transactions
        Route::get('/transactions', [MerchantTransactionsController::class, 'index'])
            ->name('merchant.transactions.index');
        Route::get('/transactions/data', [MerchantTransactionsController::class, 'getData'])
            ->name('merchant.transactions.data');
        Route::get('/transactions/export', [MerchantTransactionsController::class, 'export'])
            ->name('merchant.transactions.export');

        // Orders
        Route::get('/orders', [OrdersController::class, 'index'])
            ->name('merchant.orders.index');
        Route::get('/orders/data', [OrdersController::class, 'getData'])
            ->name('merchant.orders.data');
        Route::get('/orders/export', [OrdersController::class, 'export'])
            ->name('merchant.orders.export');

        // Refunds
        Route::get('/refunds', [MerchantRefundsController::class, 'index'])
            ->name('merchant.refunds.index');
        Route::get('/refunds/data', [MerchantRefundsController::class, 'getData'])
            ->name('merchant.refunds.data');
        Route::get('/refunds/export', [MerchantRefundsController::class, 'export'])
            ->name('merchant.refunds.export');
        Route::post('/refunds', [MerchantRefundsController::class, 'store'])
            ->name('merchant.refunds.store');

        // Payments Module (matching admin structure)
        Route::get('/payments/bulk-refund-update', [\App\Http\Controllers\Merchant\BulkRefundUpdateController::class, 'index'])
            ->name('merchant.payments.bulk-refund-update');
        Route::get('/payments/bulk-refund-update/jobs', [\App\Http\Controllers\Merchant\BulkRefundUpdateController::class, 'getJobs'])
            ->name('merchant.payments.bulk-refund-update.jobs');
        Route::post('/payments/bulk-refund-update/upload', [\App\Http\Controllers\Merchant\BulkRefundUpdateController::class, 'upload'])
            ->name('merchant.payments.bulk-refund-update.upload');
        Route::get('/payments/bulk-refund-update/template', [\App\Http\Controllers\Merchant\BulkRefundUpdateController::class, 'downloadTemplate'])
            ->name('merchant.payments.bulk-refund-update.template');
        Route::get('/payments/bulk-refund-update/download/{id}', [\App\Http\Controllers\Merchant\BulkRefundUpdateController::class, 'downloadStatusFile'])
            ->name('merchant.payments.bulk-refund-update.download');
        Route::get('/payments/chargebacks', [\App\Http\Controllers\Merchant\ChargebacksController::class, 'index'])
            ->name('merchant.payments.chargebacks');
        Route::get('/payments/chargebacks/data', [\App\Http\Controllers\Merchant\ChargebacksController::class, 'getData'])
            ->name('merchant.payments.chargebacks.data');
        Route::get('/payments/bulk-chargebacks', [\App\Http\Controllers\Merchant\BulkChargebacksController::class, 'index'])
            ->name('merchant.payments.bulk-chargebacks');
        Route::get('/payments/bulk-chargebacks/jobs', [\App\Http\Controllers\Merchant\BulkChargebacksController::class, 'getJobs'])
            ->name('merchant.payments.bulk-chargebacks.jobs');
        Route::post('/payments/bulk-chargebacks/upload', [\App\Http\Controllers\Merchant\BulkChargebacksController::class, 'upload'])
            ->name('merchant.payments.bulk-chargebacks.upload');
        Route::get('/payments/bulk-chargebacks/template', [\App\Http\Controllers\Merchant\BulkChargebacksController::class, 'downloadTemplate'])
            ->name('merchant.payments.bulk-chargebacks.template');
        Route::get('/payments/split-transactions', [\App\Http\Controllers\Merchant\SplitTransactionsController::class, 'index'])
            ->name('merchant.payments.split-transactions');
        Route::get('/payments/split-transactions/data', [\App\Http\Controllers\Merchant\SplitTransactionsController::class, 'getData'])
            ->name('merchant.payments.split-transactions.data');
        Route::get('/payments/federal-vpa', [\App\Http\Controllers\Merchant\FederalVPAController::class, 'index'])
            ->name('merchant.payments.federal-vpa');
        Route::get('/payments/federal-vpa/data', [\App\Http\Controllers\Merchant\FederalVPAController::class, 'getData'])
            ->name('merchant.payments.federal-vpa.data');

        // Settlements
        Route::get('/settlements', [SettlementsController::class, 'index'])
            ->name('merchant.settlements.index');
        Route::get('/settlements/data', [SettlementsController::class, 'getData'])
            ->name('merchant.settlements.data');
        Route::get('/settlements/export', [SettlementsController::class, 'export'])
            ->name('merchant.settlements.export');
        
        // Settlement Summary
        Route::get('/settlements/summary', [\App\Http\Controllers\Merchant\SettlementSummaryController::class, 'index'])
            ->name('merchant.settlements.summary');
        Route::get('/settlements/summary/data', [\App\Http\Controllers\Merchant\SettlementSummaryController::class, 'getData'])
            ->name('merchant.settlements.summary.data');
        Route::post('/settlements/summary/mark-settled', [\App\Http\Controllers\Merchant\SettlementSummaryController::class, 'markAsSettled'])
            ->name('merchant.settlements.summary.mark-settled');
        
        // Settlement Details
        Route::get('/settlements/details', [\App\Http\Controllers\Merchant\SettlementDetailsController::class, 'index'])
            ->name('merchant.settlements.details');
        Route::get('/settlements/details/data', [\App\Http\Controllers\Merchant\SettlementDetailsController::class, 'getData'])
            ->name('merchant.settlements.details.data');

        // Reports
        Route::get('/reports', [ReportsController::class, 'index'])
            ->name('merchant.reports.index');
        Route::get('/reports/data', [ReportsController::class, 'getData'])
            ->name('merchant.reports.data');
        Route::get('/reports/export', [ReportsController::class, 'export'])
            ->name('merchant.reports.export');

        // Disputes
        Route::get('/disputes', [DisputesController::class, 'index'])
            ->name('merchant.disputes.index');
        Route::get('/disputes/data', [DisputesController::class, 'getData'])
            ->name('merchant.disputes.data');
        Route::post('/disputes', [DisputesController::class, 'store'])
            ->name('merchant.disputes.store');

        // API Keys
        Route::get('/api-keys', [ApiKeysController::class, 'index'])->name('merchant.api_keys.index');
        Route::get('/api-keys/data', [ApiKeysController::class, 'getData'])->name('merchant.api_keys.data');
        Route::post('/api-keys', [ApiKeysController::class, 'store'])->name('merchant.api_keys.store');
        Route::delete('/api-keys/{id}', [ApiKeysController::class, 'destroy'])->name('merchant.api_keys.destroy');
        Route::post('/api-keys/{id}/regenerate-secret', [ApiKeysController::class, 'regenerateSecret'])->name('merchant.api_keys.regenerate');

        // Integration
        Route::get('/integration', [IntegrationController::class, 'index'])->name('merchant.integration.index');
        Route::post('/integration/code', [IntegrationController::class, 'getIntegrationCode'])->name('merchant.integration.code');

        // Webhooks
        Route::get('/webhooks', [WebhooksController::class, 'index'])->name('merchant.webhooks.index');
        Route::get('/webhooks/data', [WebhooksController::class, 'getData'])->name('merchant.webhooks.data');
        Route::post('/webhooks/update-url', [WebhooksController::class, 'updateWebhookUrl'])->name('merchant.webhooks.update-url');
        Route::post('/webhooks/test', [WebhooksController::class, 'testWebhook'])->name('merchant.webhooks.test');
        Route::post('/webhooks/{id}/retry', [WebhooksController::class, 'retryWebhook'])->name('merchant.webhooks.retry');

        // Plans & Subscriptions (Merchant)
        Route::get('/subscriptions', [MerchantSubscriptionsController::class, 'index'])
            ->name('merchant.subscriptions.index');
        Route::get('/subscriptions/data', [MerchantSubscriptionsController::class, 'getSubscriptions'])
            ->name('merchant.subscriptions.data');
        Route::post('/subscriptions', [MerchantSubscriptionsController::class, 'createSubscription'])
            ->name('merchant.subscriptions.store');
        Route::post('/subscriptions/{id}', [MerchantSubscriptionsController::class, 'updateSubscription'])
            ->name('merchant.subscriptions.update');
        Route::get('/plans/data', [MerchantSubscriptionsController::class, 'getPlans'])
            ->name('merchant.plans.data');

        // Onboarding
        Route::get('/onboarding', [\App\Http\Controllers\Merchant\OnboardingController::class, 'index'])->name('merchant.onboarding.index');
        Route::post('/onboarding/step/{step}', [\App\Http\Controllers\Merchant\OnboardingController::class, 'updateStep'])->name('merchant.onboarding.step');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('merchant.settings.index');
        Route::post('/settings/switch-mode', [SettingsController::class, 'switchMode'])->name('merchant.settings.switch-mode');
        Route::post('/settings/webhook', [SettingsController::class, 'updateWebhook'])->name('merchant.settings.update-webhook');
        
        // Profile
        Route::get('/profile', [ProfileController::class, 'index'])->name('merchant.profile.index');
        Route::put('/profile', [ProfileController::class, 'update'])->name('merchant.profile.update');
    });

    // Admin routes
    Route::middleware(['admin'])->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('admin.dashboard');
        Route::get('/dashboard/data', [AdminDashboardController::class, 'getData'])
            ->name('admin.dashboard.data');
        
        Route::get('/merchants', [MerchantsController::class, 'index'])
            ->name('admin.merchants.index');
        Route::get('/merchants/data', [MerchantsController::class, 'getData'])
            ->name('admin.merchants.data');

        // Merchant Accounts
        Route::get('/merchant-accounts', [MerchantAccountsController::class, 'index'])
            ->name('admin.merchant-accounts.index');
        Route::get('/merchant-accounts/data', [MerchantAccountsController::class, 'getData'])
            ->name('admin.merchant-accounts.data');
        Route::post('/merchant-accounts', [MerchantAccountsController::class, 'store'])
            ->name('admin.merchant-accounts.store');
        Route::post('/merchant-accounts/{id}/update-status', [MerchantAccountsController::class, 'updateStatus'])
            ->name('admin.merchant-accounts.update-status');
        Route::post('/merchant-accounts/{id}/update-approval-status', [MerchantAccountsController::class, 'updateStatus'])
            ->name('admin.merchant-accounts.update-approval-status');
        Route::post('/merchant-accounts/{id}/update-settings', [MerchantAccountsController::class, 'updateSettings'])
            ->name('admin.merchant-accounts.update-settings');
        Route::post('/merchant-accounts/{id}/duplicate', [MerchantAccountsController::class, 'duplicate'])
            ->name('admin.merchant-accounts.duplicate');

        // Merchant Registration Keys
        Route::get('/merchant-registration-keys', [MerchantRegistrationKeysController::class, 'index'])
            ->name('admin.merchant-registration-keys.index');
        Route::get('/merchant-registration-keys/data', [MerchantRegistrationKeysController::class, 'getData'])
            ->name('admin.merchant-registration-keys.data');
        Route::get('/merchant-registration-keys/merchants', [MerchantRegistrationKeysController::class, 'getMerchants'])
            ->name('admin.merchant-registration-keys.merchants');
        Route::post('/merchant-registration-keys', [MerchantRegistrationKeysController::class, 'store'])
            ->name('admin.merchant-registration-keys.store');
        Route::post('/merchant-registration-keys/{id}', [MerchantRegistrationKeysController::class, 'update'])
            ->name('admin.merchant-registration-keys.update');

        // Merchant Vendors
        Route::get('/merchant-vendors', [MerchantVendorsController::class, 'index'])
            ->name('admin.merchant-vendors.index');
        Route::get('/merchant-vendors/data', [MerchantVendorsController::class, 'getData'])
            ->name('admin.merchant-vendors.data');
        Route::get('/merchant-vendors/merchants', [MerchantVendorsController::class, 'getMerchants'])
            ->name('admin.merchant-vendors.merchants');
        Route::post('/merchant-vendors/bulk-status', [MerchantVendorsController::class, 'bulkStatus'])
            ->name('admin.merchant-vendors.bulk-status');
        Route::post('/merchant-vendors', [MerchantVendorsController::class, 'store'])
            ->name('admin.merchant-vendors.store');
        Route::post('/merchant-vendors/{id}', [MerchantVendorsController::class, 'update'])
            ->name('admin.merchant-vendors.update');
        Route::delete('/merchant-vendors/{id}', [MerchantVendorsController::class, 'destroy'])
            ->name('admin.merchant-vendors.destroy');

        // Payments Module
        Route::get('/payments/transactions', [AdminTransactionsController::class, 'index'])
            ->name('admin.payments.transactions');
        Route::get('/payments/transactions/data', [AdminTransactionsController::class, 'getData'])
            ->name('admin.payments.transactions.data');
        Route::get('/payments/refunds', [AdminRefundsController::class, 'index'])
            ->name('admin.payments.refunds');
        Route::get('/payments/refunds/data', [AdminRefundsController::class, 'getData'])
            ->name('admin.payments.refunds.data');
        Route::get('/payments/refunds/export', [AdminRefundsController::class, 'export'])
            ->name('admin.payments.refunds.export');
        Route::get('/payments/transactions/export', [AdminTransactionsController::class, 'export'])
            ->name('admin.payments.transactions.export');
        Route::get('/payments/bulk-refund-update', [BulkRefundUpdateController::class, 'index'])
            ->name('admin.payments.bulk-refund-update');
        Route::get('/payments/bulk-refund-update/jobs', [BulkRefundUpdateController::class, 'getJobs'])
            ->name('admin.payments.bulk-refund-update.jobs');
        Route::post('/payments/bulk-refund-update/upload', [BulkRefundUpdateController::class, 'upload'])
            ->name('admin.payments.bulk-refund-update.upload');
        Route::get('/payments/bulk-refund-update/template', [BulkRefundUpdateController::class, 'downloadTemplate'])
            ->name('admin.payments.bulk-refund-update.template');
        Route::get('/payments/bulk-refund-update/download/{id}', [BulkRefundUpdateController::class, 'downloadStatusFile'])
            ->name('admin.payments.bulk-refund-update.download');
        Route::get('/payments/chargebacks', [ChargebacksController::class, 'index'])
            ->name('admin.payments.chargebacks');
        Route::get('/payments/chargebacks/data', [ChargebacksController::class, 'getData'])
            ->name('admin.payments.chargebacks.data');
        Route::get('/payments/bulk-chargebacks', [BulkChargebacksController::class, 'index'])
            ->name('admin.payments.bulk-chargebacks');
        Route::get('/payments/bulk-chargebacks/jobs', [BulkChargebacksController::class, 'getJobs'])
            ->name('admin.payments.bulk-chargebacks.jobs');
        Route::post('/payments/bulk-chargebacks/upload', [BulkChargebacksController::class, 'upload'])
            ->name('admin.payments.bulk-chargebacks.upload');
        Route::get('/payments/bulk-chargebacks/template', [BulkChargebacksController::class, 'downloadTemplate'])
            ->name('admin.payments.bulk-chargebacks.template');
        Route::get('/payments/split-transactions', [SplitTransactionsController::class, 'index'])
            ->name('admin.payments.split-transactions');
        Route::get('/payments/split-transactions/data', [SplitTransactionsController::class, 'getData'])
            ->name('admin.payments.split-transactions.data');
        Route::get('/payments/federal-vpa', [FederalVPAController::class, 'index'])
            ->name('admin.payments.federal-vpa');
        Route::get('/payments/federal-vpa/data', [FederalVPAController::class, 'getData'])
            ->name('admin.payments.federal-vpa.data');

        // Settlements Module
        Route::get('/settlements/summary', [SettlementSummaryController::class, 'index'])
            ->name('admin.settlements.summary');
        Route::get('/settlements/summary/data', [SettlementSummaryController::class, 'getData'])
            ->name('admin.settlements.summary.data');
        Route::post('/settlements/summary/mark-settled', [SettlementSummaryController::class, 'markAsSettled'])
            ->name('admin.settlements.summary.mark-settled');
        Route::get('/settlements/details', [SettlementDetailsController::class, 'index'])
            ->name('admin.settlements.details');
        Route::get('/settlements/details/data', [SettlementDetailsController::class, 'getData'])
            ->name('admin.settlements.details.data');
        Route::post('/settlements/details', [SettlementDetailsController::class, 'store'])
            ->name('admin.settlements.details.store');
        Route::get('/settlements/merchants', [SettlementDetailsController::class, 'getMerchants'])
            ->name('admin.settlements.merchants');
        Route::get('/settlements/fund-transfer', [FundTransferController::class, 'index'])
            ->name('admin.settlements.fund-transfer');
        Route::get('/settlements/fund-transfer/data', [FundTransferController::class, 'getData'])
            ->name('admin.settlements.fund-transfer.data');
        Route::post('/settlements/fund-transfer', [FundTransferController::class, 'store'])
            ->name('admin.settlements.fund-transfer.store');
        
        // Manage Settlements Module
        Route::get('/manage-settlements/pending', [PendingSettlementController::class, 'index'])
            ->name('admin.manage-settlements.pending');
        Route::get('/manage-settlements/pending/data', [PendingSettlementController::class, 'getData'])
            ->name('admin.manage-settlements.pending.data');
        Route::get('/manage-settlements/mis-report', [MISReportController::class, 'index'])
            ->name('admin.manage-settlements.mis-report');
        Route::get('/manage-settlements/mis-report/download', [MISReportController::class, 'download'])
            ->name('admin.manage-settlements.mis-report.download');

        // Orders
        Route::get('/orders', [\App\Http\Controllers\Admin\OrdersController::class, 'index'])
            ->name('admin.orders.index');
        Route::get('/orders/data', [\App\Http\Controllers\Admin\OrdersController::class, 'getData'])
            ->name('admin.orders.data');

        // Admin can also access all merchant routes
        Route::get('/transactions', [MerchantTransactionsController::class, 'indexAdmin'])
            ->name('admin.transactions.index');
        Route::get('/transactions/data', [MerchantTransactionsController::class, 'getDataAdmin'])
            ->name('admin.transactions.data');

        Route::get('/reports', [ReportsController::class, 'indexAdmin'])
            ->name('admin.reports.index');
        Route::get('/reports/data', [ReportsController::class, 'getDataAdmin'])
            ->name('admin.reports.data');
        Route::get('/reports/export', [ReportsController::class, 'exportAdmin'])
            ->name('admin.reports.export');

        // Disputes
        Route::get('/disputes', [DisputesController::class, 'indexAdmin'])
            ->name('admin.disputes.index');
        Route::get('/disputes/data', [DisputesController::class, 'getDataAdmin'])
            ->name('admin.disputes.data');
        Route::post('/disputes/{id}/status', [DisputesController::class, 'updateStatus'])
            ->name('admin.disputes.update-status');

        // Subscriptions (Admin)
        Route::get('/subscriptions', [SubscriptionsController::class, 'index'])
            ->name('admin.subscriptions.index');
        Route::get('/subscriptions/data', [SubscriptionsController::class, 'getSubscriptions'])
            ->name('admin.subscriptions.data');
        Route::post('/subscriptions', [SubscriptionsController::class, 'createSubscription'])
            ->name('admin.subscriptions.store');
        Route::post('/subscriptions/{id}', [SubscriptionsController::class, 'updateSubscription'])
            ->name('admin.subscriptions.update');

        // Plans (Admin)
        Route::get('/plans/data', [SubscriptionsController::class, 'getPlans'])
            ->name('admin.plans.data');
        Route::post('/plans', [SubscriptionsController::class, 'storePlan'])
            ->name('admin.plans.store');
        Route::post('/plans/{id}', [SubscriptionsController::class, 'updatePlan'])
            ->name('admin.plans.update');

        // Admin Settings (Mode Switching)
        Route::post('/settings/switch-mode', [AdminSettingsController::class, 'switchMode'])
            ->name('admin.settings.switch-mode');
        Route::get('/settings/mode', [AdminSettingsController::class, 'getMode'])
            ->name('admin.settings.mode');

        // Risk Management
        Route::get('/risk', [RiskManagementController::class, 'index'])
            ->name('admin.risk.index');
        Route::get('/risk/stats', [RiskManagementController::class, 'getStats'])
            ->name('admin.risk.stats');
        Route::get('/risk/rules/data', [RiskManagementController::class, 'getRules'])
            ->name('admin.risk.rules.data');
        Route::post('/risk/rules', [RiskManagementController::class, 'storeRule'])
            ->name('admin.risk.rules.store');
        Route::post('/risk/rules/{id}', [RiskManagementController::class, 'updateRule'])
            ->name('admin.risk.rules.update');
        Route::delete('/risk/rules/{id}', [RiskManagementController::class, 'deleteRule'])
            ->name('admin.risk.rules.delete');
        Route::get('/risk/events/data', [RiskManagementController::class, 'getEvents'])
            ->name('admin.risk.events.data');
        Route::post('/risk/events/{id}/resolve', [RiskManagementController::class, 'resolveEvent'])
            ->name('admin.risk.events.resolve');
        Route::get('/risk/alerts/data', [RiskManagementController::class, 'getAlerts'])
            ->name('admin.risk.alerts.data');
        Route::post('/risk/alerts', [RiskManagementController::class, 'createAlert'])
            ->name('admin.risk.alerts.store');
        Route::post('/risk/alerts/{id}', [RiskManagementController::class, 'updateAlert'])
            ->name('admin.risk.alerts.update');

        // Webhook Event Types Management
        Route::get('/webhook-event-types', [\App\Http\Controllers\Admin\WebhookEventTypesController::class, 'index'])
            ->name('admin.webhook-event-types.index');
        Route::get('/webhook-event-types/data', [\App\Http\Controllers\Admin\WebhookEventTypesController::class, 'getData'])
            ->name('admin.webhook-event-types.data');
        Route::post('/webhook-event-types/{id}', [\App\Http\Controllers\Admin\WebhookEventTypesController::class, 'update'])
            ->name('admin.webhook-event-types.update');
        Route::post('/webhook-event-types/{id}/toggle', [\App\Http\Controllers\Admin\WebhookEventTypesController::class, 'toggle'])
            ->name('admin.webhook-event-types.toggle');

        // Base Rates Management
        Route::get('/base-rates', [\App\Http\Controllers\Admin\BaseRatesController::class, 'index'])
            ->name('admin.base-rates.index');
        Route::get('/base-rates/data', [\App\Http\Controllers\Admin\BaseRatesController::class, 'getData'])
            ->name('admin.base-rates.data');
        Route::get('/base-rates/entities', [\App\Http\Controllers\Admin\BaseRatesController::class, 'getEntities'])
            ->name('admin.base-rates.entities');
        Route::post('/base-rates', [\App\Http\Controllers\Admin\BaseRatesController::class, 'store'])
            ->name('admin.base-rates.store');
        Route::post('/base-rates/{id}', [\App\Http\Controllers\Admin\BaseRatesController::class, 'update'])
            ->name('admin.base-rates.update');
        Route::delete('/base-rates/{id}', [\App\Http\Controllers\Admin\BaseRatesController::class, 'destroy'])
            ->name('admin.base-rates.destroy');

        // Acquirer Details
        Route::get('/acquirer-accounts', [AcquirerAccountsController::class, 'index'])
            ->name('admin.acquirer.accounts.index');
        Route::get('/acquirer-accounts/data', [AcquirerAccountsController::class, 'getData'])
            ->name('admin.acquirer.accounts.data');
        Route::get('/acquirer-accounts/acquirer-names', [AcquirerAccountsController::class, 'getAcquirerNames'])
            ->name('admin.acquirer.accounts.acquirer-names');
        Route::get('/acquirer-accounts/merchants', [AcquirerAccountsController::class, 'getMerchants'])
            ->name('admin.acquirer.accounts.merchants');
        Route::post('/acquirer-accounts', [AcquirerAccountsController::class, 'store'])
            ->name('admin.acquirer.accounts.store');
        Route::put('/acquirer-accounts/{id}', [AcquirerAccountsController::class, 'update'])
            ->name('admin.acquirer.accounts.update');
        Route::delete('/acquirer-accounts/{id}', [AcquirerAccountsController::class, 'destroy'])
            ->name('admin.acquirer.accounts.destroy');
        
        // Acquirer Account Details Upload
        Route::get('/acquirer-account-upload', [AcquirerAccountUploadController::class, 'index'])
            ->name('admin.acquirer.detail-upload.index');
        Route::get('/acquirer-account-upload/payment-modes', [AcquirerAccountUploadController::class, 'getPaymentModes'])
            ->name('admin.acquirer.detail-upload.payment-modes');
        Route::get('/acquirer-account-upload/banks', [AcquirerAccountUploadController::class, 'getBanksByPaymentMode'])
            ->name('admin.acquirer.detail-upload.banks');
        Route::post('/acquirer-account-upload/upload', [AcquirerAccountUploadController::class, 'upload'])
            ->name('admin.acquirer.detail-upload.upload');
        Route::get('/acquirer-account-upload/jobs', [AcquirerAccountUploadController::class, 'getJobs'])
            ->name('admin.acquirer.detail-upload.jobs');
        Route::get('/acquirer-account-upload/download-status/{id}', [AcquirerAccountUploadController::class, 'downloadStatusFile'])
            ->name('admin.acquirer.detail-upload.download-status');
        Route::get('/acquirer-account-upload/download-template', [AcquirerAccountUploadController::class, 'downloadTemplate'])
            ->name('admin.acquirer.detail-upload.download-template');
        
        // Acquirer Rates
        Route::get('/acquirer-rates', [AcquirerRatesController::class, 'index'])
            ->name('admin.acquirer.rates.index');
        Route::get('/acquirer-rates/data', [AcquirerRatesController::class, 'getData'])
            ->name('admin.acquirer.rates.data');
        Route::get('/acquirer-rates/acquirer-accounts', [AcquirerRatesController::class, 'getAcquirerAccounts'])
            ->name('admin.acquirer.rates.acquirer-accounts');
        Route::get('/acquirer-rates/acquirer-names', [AcquirerRatesController::class, 'getAcquirerNames'])
            ->name('admin.acquirer.rates.acquirer-names');
        Route::get('/acquirer-rates/payment-modes', [AcquirerRatesController::class, 'getPaymentModes'])
            ->name('admin.acquirer.rates.payment-modes');
        Route::get('/acquirer-rates/banks', [AcquirerRatesController::class, 'getBanks'])
            ->name('admin.acquirer.rates.banks');
        Route::post('/acquirer-rates', [AcquirerRatesController::class, 'store'])
            ->name('admin.acquirer.rates.store');
        Route::put('/acquirer-rates/{id}', [AcquirerRatesController::class, 'update'])
            ->name('admin.acquirer.rates.update');
        Route::delete('/acquirer-rates/{id}', [AcquirerRatesController::class, 'destroy'])
            ->name('admin.acquirer.rates.destroy');
        Route::post('/acquirer-rates/{id}/duplicate', [AcquirerRatesController::class, 'duplicate'])
            ->name('admin.acquirer.rates.duplicate');
    });
});

// TBD : also configure routes for ICICI bank apis - usha handle it 