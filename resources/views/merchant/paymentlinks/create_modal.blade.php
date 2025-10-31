<div class="modal fade" id="createLinkModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Payment Link</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="createLinkForm" onsubmit="return false;">
                    <div class="mb-3">
                        <label class="form-label">Title <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" ng-model="plc.newLink.title" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" ng-model="plc.newLink.description" rows="3"></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" class="form-control" ng-model="plc.newLink.amount" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Currency</label>
                        <select class="form-select" ng-model="plc.newLink.currency">
                            <option value="INR">INR - Indian Rupee</option>
                            <option value="USD">USD - US Dollar</option>
                            <option value="EUR">EUR - Euro</option>
                            <option value="GBP">GBP - British Pound</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Expires In (hours)</label>
                        <input type="number" class="form-control" ng-model="plc.newLink.expires_in_hours" ng-init="plc.newLink.expires_in_hours = 24" value="24">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Payment Methods <span class="text-danger">*</span></label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pm_card" ng-model="plc.newLink.payment_methods.card" ng-true-value="true" ng-false-value="false">
                                    <label class="form-check-label" for="pm_card">
                                        <i class="bi bi-credit-card"></i> Cards (Credit/Debit)
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pm_upi" ng-model="plc.newLink.payment_methods.upi" ng-true-value="true" ng-false-value="false">
                                    <label class="form-check-label" for="pm_upi">
                                        <i class="bi bi-phone"></i> UPI
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pm_netbanking" ng-model="plc.newLink.payment_methods.netbanking" ng-true-value="true" ng-false-value="false">
                                    <label class="form-check-label" for="pm_netbanking">
                                        <i class="bi bi-bank"></i> Net Banking
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pm_wallet" ng-model="plc.newLink.payment_methods.wallet" ng-true-value="true" ng-false-value="false">
                                    <label class="form-check-label" for="pm_wallet">
                                        <i class="bi bi-wallet2"></i> Wallets (Paytm, PhonePe, etc.)
                                    </label>
                                </div>
                            </div>
                        </div>
                        <small class="text-muted">Select at least one payment method</small>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" ng-disabled="plc.creating">Cancel</button>
                <button type="button" class="btn btn-primary" ng-click="plc.createPaymentLink($event)" ng-disabled="plc.creating || !plc.newLink.title || !plc.newLink.amount">
                    <span class="spinner-border spinner-border-sm me-2" ng-if="plc.creating" style="display: inline-block;"></span>
                    <span ng-bind="plc.creating ? 'Creating...' : 'Create Link'"></span>
                </button>
            </div>
        </div>
    </div>
</div>

