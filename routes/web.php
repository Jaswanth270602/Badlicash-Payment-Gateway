<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Merchant\PaymentLinksController;
use App\Http\Controllers\Merchant\TransactionsController;
use App\Http\Controllers\Merchant\OrdersController;
use App\Http\Controllers\Merchant\RefundsController;
use App\Http\Controllers\Merchant\SettlementsController;
use App\Http\Controllers\Merchant\SettingsController;
use App\Http\Controllers\Merchant\ApiKeysController;
use App\Http\Controllers\Merchant\IntegrationController;
use App\Http\Controllers\Merchant\WebhooksController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\MerchantsController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\DisputesController;
use App\Http\Controllers\PaymentCheckoutController;
use App\Http\Controllers\Admin\SubscriptionsController;
use App\Http\Controllers\Admin\RiskManagementController;
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

Route::get('/', function () {
    return redirect()->route('login');
});

// Public payment checkout
Route::get('/pay/{token}', [PaymentCheckoutController::class, 'show'])->name('payment.checkout');
Route::post('/pay/{token}', [PaymentCheckoutController::class, 'process'])->name('payment.process');
Route::get('/payment/success/{token}', [PaymentCheckoutController::class, 'success'])->name('payment.success');
Route::get('/payment/failed/{token}', [PaymentCheckoutController::class, 'failed'])->name('payment.failed');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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
        Route::get('/transactions', [TransactionsController::class, 'index'])
            ->name('merchant.transactions.index');
        Route::get('/transactions/data', [TransactionsController::class, 'getData'])
            ->name('merchant.transactions.data');

        // Orders
        Route::get('/orders', [OrdersController::class, 'index'])
            ->name('merchant.orders.index');
        Route::get('/orders/data', [OrdersController::class, 'getData'])
            ->name('merchant.orders.data');

        // Refunds
        Route::get('/refunds', [RefundsController::class, 'index'])
            ->name('merchant.refunds.index');
        Route::get('/refunds/data', [RefundsController::class, 'getData'])
            ->name('merchant.refunds.data');
        Route::post('/refunds', [RefundsController::class, 'store'])
            ->name('merchant.refunds.store');

        // Settlements
        Route::get('/settlements', [SettlementsController::class, 'index'])
            ->name('merchant.settlements.index');
        Route::get('/settlements/data', [SettlementsController::class, 'getData'])
            ->name('merchant.settlements.data');

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

        // Onboarding
        Route::get('/onboarding', [\App\Http\Controllers\Merchant\OnboardingController::class, 'index'])->name('merchant.onboarding.index');
        Route::post('/onboarding/step/{step}', [\App\Http\Controllers\Merchant\OnboardingController::class, 'updateStep'])->name('merchant.onboarding.step');

        // Settings
        Route::get('/settings', [SettingsController::class, 'index'])->name('merchant.settings.index');
        Route::post('/settings/switch-mode', [SettingsController::class, 'switchMode'])->name('merchant.settings.switch-mode');
        Route::post('/settings/webhook', [SettingsController::class, 'updateWebhook'])->name('merchant.settings.update-webhook');
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

        // Admin can also access all merchant routes
        Route::get('/transactions', [TransactionsController::class, 'indexAdmin'])
            ->name('admin.transactions.index');
        Route::get('/transactions/data', [TransactionsController::class, 'getDataAdmin'])
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
    });
});

