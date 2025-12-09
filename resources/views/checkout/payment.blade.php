<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Payment - {{ $paymentLink->title }} - BadliCash</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --primary: #6366f1;
            --primary-dark: #4f46e5;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .payment-container {
            max-width: 900px;
            width: 100%;
        }

        .payment-card {
            background: white;
            border-radius: 16px;
            box-shadow: 0 15px 35px -10px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            display: grid;
            grid-template-columns: 300px 1fr;
            max-height: 90vh;
        }

        /* LEFT PANEL */
        .left-panel {
            background: linear-gradient(180deg, var(--primary) 0%, var(--primary-dark) 100%);
            color: white;
            padding: 25px 20px;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: auto;
        }

        .left-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            pointer-events: none;
        }

        .merchant-info {
            position: relative;
            z-index: 1;
            margin-bottom: 20px;
        }

        .merchant-logo {
            width: 42px;
            height: 42px;
            background: rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            margin-bottom: 12px;
        }

        .merchant-name {
            font-size: 18px;
            font-weight: 700;
            margin: 0 0 4px 0;
        }

        .merchant-desc {
            font-size: 13px;
            opacity: 0.95;
            line-height: 1.4;
        }

        .amount-section {
            position: relative;
            z-index: 1;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border-radius: 12px;
            padding: 18px;
            margin-bottom: 18px;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .amount-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            opacity: 0.9;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .amount-value {
            font-size: 30px;
            font-weight: 800;
            margin: 0;
            line-height: 1;
        }

        .test-mode-badge {
            position: relative;
            z-index: 1;
            background: rgba(245, 158, 11, 0.2);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 2px solid rgba(245, 158, 11, 0.4);
            border-radius: 10px;
            padding: 14px;
            margin-bottom: 18px;
        }

        .test-mode-badge strong {
            display: block;
            margin-bottom: 8px;
            font-size: 12px;
        }

        .test-mode-badge div {
            font-size: 11px;
            line-height: 1.5;
            opacity: 0.95;
        }

        .secured-by {
            position: relative;
            z-index: 1;
            margin-top: auto;
            padding-top: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 12px;
            opacity: 0.95;
        }

        /* RIGHT PANEL */
        .right-panel {
            padding: 25px 28px;
            overflow-y: auto;
            max-height: 90vh;
        }

        .panel-title {
            font-size: 20px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 18px;
        }

        .alert {
            border-radius: 10px;
            border: none;
            padding: 12px 16px;
            margin-bottom: 18px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert i {
            font-size: 20px;
        }

        .alert strong {
            font-size: 14px;
        }

        .alert div {
            font-size: 13px;
        }

        /* CUSTOMER FORM */
        .customer-section {
            margin-bottom: 18px;
        }

        .section-title {
            font-size: 15px;
            font-weight: 600;
            color: #334155;
            margin-bottom: 12px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 500;
            color: #475569;
            margin-bottom: 6px;
            display: block;
        }

        .form-control, .form-select {
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 14px;
            transition: all 0.2s;
            width: 100%;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.1);
            outline: none;
        }

        .form-control.is-invalid {
            border-color: #dc3545;
            box-shadow: 0 0 0 4px rgba(220, 53, 69, 0.1);
        }

        .form-control.is-valid {
            border-color: #10b981;
            box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.1);
        }

        #amountError {
            display: block;
            margin-top: 6px;
            padding: 8px 12px;
            background-color: #fee;
            border: 1px solid #fcc;
            border-radius: 6px;
            font-size: 13px;
            font-weight: 600;
            color: #dc3545;
        }

        /* PAYMENT METHODS */
        .payment-methods-section {
            margin-bottom: 18px;
        }

        .payment-methods-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin-bottom: 18px;
        }

        .payment-method-btn {
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            padding: 14px 10px;
            text-align: center;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .payment-method-btn:hover {
            border-color: var(--primary);
            transform: translateY(-3px);
            box-shadow: 0 8px 16px rgba(99, 102, 241, 0.2);
            background: white;
        }

        .payment-method-btn.active {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(139, 92, 246, 0.08) 100%);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
        }

        .payment-method-btn i {
            font-size: 24px;
            color: var(--primary);
            margin-bottom: 6px;
            display: block;
        }

        .payment-method-btn .method-label {
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
            display: block;
            margin-bottom: 2px;
        }

        .payment-method-btn .method-desc {
            font-size: 10px;
            color: #64748b;
        }

        .payment-method-btn.active .method-label {
            color: var(--primary);
        }

        /* PAYMENT FORMS */
        .payment-form {
            display: none;
            animation: fadeIn 0.3s ease;
        }

        .payment-form.active {
            display: block;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .card-preview {
            background: linear-gradient(135deg, #1e293b 0%, #334155 100%);
            color: white;
            padding: 24px;
            border-radius: 14px;
            margin-bottom: 25px;
        }

        .card-number-display {
            font-family: 'Courier New', monospace;
            font-size: 20px;
            letter-spacing: 3px;
            margin: 15px 0;
        }

        /* PAY BUTTON */
        .pay-button {
            background: linear-gradient(135deg, var(--primary) 0%, #8b5cf6 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px;
            width: 100%;
            font-size: 16px;
            font-weight: 700;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .pay-button::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .pay-button:hover::before {
            width: 300px;
            height: 300px;
        }

        .pay-button:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .pay-button:not(:disabled):hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 28px rgba(99, 102, 241, 0.4);
        }

        .pay-button:not(:disabled):active {
            transform: translateY(-1px);
        }

        .spinner {
            width: 20px;
            height: 20px;
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 992px) {
            .payment-card {
                grid-template-columns: 1fr;
            }
            .left-panel {
                padding: 35px 25px;
                min-height: auto;
            }
            .payment-methods-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        <div class="payment-card">
            <!-- LEFT PANEL -->
            <div class="left-panel">
                <div class="merchant-info">
                    <div class="merchant-logo">
                        <i class="bi bi-wallet2"></i>
                    </div>
                    <h1 class="merchant-name">{{ $paymentLink->title }}</h1>
                    @if($paymentLink->description)
                        <p class="merchant-desc">{{ $paymentLink->description }}</p>
                    @endif
                </div>

                <div class="amount-section">
                    <div class="amount-label">Total Amount</div>
                    <h2 class="amount-value">{{ $paymentLink->currency }} {{ number_format($paymentLink->amount, 2) }}</h2>
                    @if($paymentLink->allow_partial_payment)
                        @if(($paymentLink->amount_paid ?? 0) > 0)
                            <div style="margin-top: 12px; padding-top: 12px; border-top: 1px solid rgba(255, 255, 255, 0.2);">
                                <div style="font-size: 12px; opacity: 0.9; margin-bottom: 6px;">Amount Paid</div>
                                <div style="font-size: 18px; font-weight: 600; color: #10b981;">{{ $paymentLink->currency }} {{ number_format($paymentLink->amount_paid ?? 0, 2) }}</div>
                                <div style="font-size: 12px; opacity: 0.9; margin-top: 8px; margin-bottom: 6px;">Remaining Balance</div>
                                <div style="font-size: 18px; font-weight: 600; color: #fbbf24;">{{ $paymentLink->currency }} {{ number_format($paymentLink->getRemainingBalance(), 2) }}</div>
                            </div>
                        @else
                            <div style="margin-top: 10px; padding: 10px; background: rgba(251, 191, 36, 0.15); border-radius: 8px; border: 1px solid rgba(251, 191, 36, 0.3);">
                                <div style="font-size: 12px; opacity: 0.95; margin-bottom: 4px;">
                                    <i class="bi bi-info-circle"></i> Partial Payment Enabled
                                </div>
                                <div style="font-size: 13px; opacity: 0.9;">
                                    Pay any amount up to {{ $paymentLink->currency }} {{ number_format($paymentLink->amount, 2) }}
                                </div>
                            </div>
                        @endif
                    @endif
                </div>

                @if($paymentLink->test_mode)
                <div class="test-mode-badge">
                    <strong><i class="bi bi-info-circle"></i> TEST MODE - Simulate Payment</strong>
                    <div style="margin-top: 10px; display: flex; flex-direction: column; gap: 8px;">
                        <button class="btn btn-sm btn-success" id="simulateSuccessBtn" style="width: 100%; background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.5); color: white; font-weight: 600;">
                            <i class="bi bi-check-circle"></i> Simulate Success
                        </button>
                        <button class="btn btn-sm btn-danger" id="simulateFailBtn" style="width: 100%; background: rgba(239, 68, 68, 0.2); border: 1px solid rgba(239, 68, 68, 0.5); color: white; font-weight: 600;">
                            <i class="bi bi-x-circle"></i> Simulate Failure
                        </button>
                    </div>
                </div>
                @endif

                <div class="secured-by">
                    <i class="bi bi-shield-check" style="font-size: 22px;"></i>
                    <span>Secured by <strong>BadliCash</strong></span>
                </div>
            </div>

            <!-- RIGHT PANEL -->
            <div class="right-panel" id="paymentApp">
                <h2 class="panel-title">Complete Your Payment</h2>

                <!-- Success/Error Messages -->
                <div class="alert alert-success" id="successAlert" style="display: none;">
                    <i class="bi bi-check-circle-fill"></i>
                    <div>
                        <strong>Payment Successful!</strong>
                        <div style="font-size: 14px; margin-top: 4px;" id="successMessage"></div>
                    </div>
                </div>

                <div class="alert alert-danger" id="errorAlert" style="display: none;">
                    <i class="bi bi-x-circle-fill"></i>
                    <div>
                        <strong>Payment Failed</strong>
                        <div style="font-size: 14px; margin-top: 4px;" id="errorMessage"></div>
                    </div>
                </div>

                @php
                    // Check allow_partial_payment - the model casts it to boolean, so just check if truthy
                    $isPartialEnabled = (bool)($paymentLink->allow_partial_payment ?? false);
                @endphp
                
                @if($isPartialEnabled)
                <!-- Partial Payment Amount - SHOWN FIRST -->
                <div class="customer-section" id="partialPaymentSection" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: 2px solid #667eea; border-radius: 12px; padding: 24px; margin-bottom: 24px; box-shadow: 0 4px 15px rgba(102, 126, 234, 0.3); position: relative; z-index: 10; display: block !important; visibility: visible !important; opacity: 1 !important;">
                    <h4 class="section-title" style="color: white; margin-bottom: 16px; font-size: 18px; font-weight: 700;">
                        <i class="bi bi-cash-coin" style="margin-right: 10px; font-size: 20px;"></i>Enter Payment Amount
                    </h4>
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label" style="font-weight: 600; color: white; font-size: 14px; margin-bottom: 10px;">
                                How much would you like to pay? <span class="text-danger">*</span>
                            </label>
                            <div class="input-group" style="margin-bottom: 10px;">
                                <span class="input-group-text" style="background: white; color: #667eea; font-weight: 700; font-size: 16px; border: none;">{{ $paymentLink->currency }}</span>
                                <input type="number" class="form-control form-control-lg" id="customAmount" 
                                       placeholder="Enter amount (max: {{ number_format($paymentLink->amount, 2) }})" 
                                       min="0.01" 
                                       max="{{ $paymentLink->amount }}" 
                                       step="0.01"
                                       value="{{ $paymentLink->getRemainingBalance() }}"
                                       required
                                       style="font-size: 18px; font-weight: 700; padding: 14px; border: none; border-radius: 0 8px 8px 0;">
                            </div>
                            <div id="amountError" class="text-danger mt-2" style="display: none; padding: 10px 14px; background-color: rgba(255, 255, 255, 0.95); border: 2px solid #dc3545; border-radius: 8px; font-size: 14px; font-weight: 600; color: #dc3545;"></div>
                            <small class="d-block mt-2" style="font-size: 13px; color: rgba(255, 255, 255, 0.9);">
                                @if(($paymentLink->amount_paid ?? 0) > 0)
                                    <strong>✓ Already paid:</strong> {{ $paymentLink->currency }} {{ number_format($paymentLink->amount_paid, 2) }} | 
                                    <strong>Remaining:</strong> {{ $paymentLink->currency }} {{ number_format($paymentLink->getRemainingBalance(), 2) }}
                                @else
                                    <strong>Total amount:</strong> {{ $paymentLink->currency }} {{ number_format($paymentLink->amount, 2) }}. 
                                    <strong>You can pay any amount up to this total.</strong>
                                @endif
                            </small>
                        </div>
                    </div>
                </div>
                @endif

                <!-- Customer Details -->
                <div class="customer-section">
                    <h4 class="section-title">Customer Details</h4>
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label">Full Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="customerName" placeholder="John Doe" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email <span class="text-danger">*</span></label>
                            <input type="email" class="form-control" id="customerEmail" placeholder="john@example.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Phone <span class="text-danger">*</span></label>
                            <input type="tel" class="form-control" id="customerPhone" placeholder="9876543210" maxlength="10" pattern="[0-9]{10}" required>
                        </div>
                    </div>
                </div>

                <!-- Payment Methods -->
                <div class="payment-methods-section">
                    <h4 class="section-title">Select Payment Method</h4>
                    <div class="payment-methods-grid">
                        <div class="payment-method-btn active" data-method="card">
                            <i class="bi bi-credit-card-fill"></i>
                            <div class="method-label">Card</div>
                            <div class="method-desc">Credit/Debit</div>
                        </div>
                        <div class="payment-method-btn" data-method="upi">
                            <i class="bi bi-phone-fill"></i>
                            <div class="method-label">UPI</div>
                            <div class="method-desc">Pay via UPI</div>
                        </div>
                        <div class="payment-method-btn" data-method="netbanking">
                            <i class="bi bi-bank"></i>
                            <div class="method-label">Net Banking</div>
                            <div class="method-desc">Online Banking</div>
                        </div>
                        <div class="payment-method-btn" data-method="wallet">
                            <i class="bi bi-wallet2"></i>
                            <div class="method-label">Wallets</div>
                            <div class="method-desc">Paytm, PhonePe</div>
                        </div>
                    </div>
                </div>

                <!-- CARD FORM -->
                <div class="payment-form active" id="cardForm">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Card Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cardNumber" placeholder="4242 4242 4242 4242" maxlength="19">
                        </div>
                        <div class="col-12">
                            <label class="form-label">Card Holder Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="cardHolder" placeholder="JOHN DOE" style="text-transform: uppercase;">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Month <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="expiryMonth" placeholder="12" maxlength="2">
                        </div>
                        <div class="col-4">
                            <label class="form-label">Year <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="expiryYear" placeholder="2025" maxlength="4">
                        </div>
                        <div class="col-4">
                            <label class="form-label">CVV <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="cvv" placeholder="123" maxlength="3">
                        </div>
                    </div>
                </div>

                <!-- UPI FORM -->
                <div class="payment-form" id="upiForm">
                    <div class="mb-3">
                        <label class="form-label">UPI ID</label>
                        <input type="text" class="form-control" id="upiId" placeholder="yourname@upi">
                        <div style="text-align: center; margin: 20px 0; color: #94a3b8; font-weight: 600;">OR</div>
                        <label class="form-label">Choose UPI App</label>
                        <select class="form-select" id="upiApp">
                            <option value="">Select UPI App</option>
                            <option value="gpay">Google Pay</option>
                            <option value="phonepe">PhonePe</option>
                            <option value="paytm">Paytm</option>
                            <option value="amazonpay">Amazon Pay</option>
                        </select>
                    </div>
                </div>

                <!-- NET BANKING FORM -->
                <div class="payment-form" id="netbankingForm">
                    <div class="mb-3">
                        <label class="form-label">Select Your Bank <span class="text-danger">*</span></label>
                        <select class="form-select" id="bankCode">
                            <option value="">Choose your bank</option>
                            <option value="SBI">State Bank of India</option>
                            <option value="HDFC">HDFC Bank</option>
                            <option value="ICICI">ICICI Bank</option>
                            <option value="AXIS">Axis Bank</option>
                            <option value="KOTAK">Kotak Mahindra Bank</option>
                            <option value="PNB">Punjab National Bank</option>
                            <option value="BOB">Bank of Baroda</option>
                            <option value="IDBI">IDBI Bank</option>
                            <option value="YES">Yes Bank</option>
                        </select>
                    </div>
                </div>

                <!-- WALLET FORM -->
                <div class="payment-form" id="walletForm">
                    <div class="mb-3">
                        <label class="form-label">Select Wallet <span class="text-danger">*</span></label>
                        <select class="form-select" id="walletProvider">
                            <option value="">Choose wallet</option>
                            <option value="paytm">Paytm</option>
                            <option value="phonepe">PhonePe</option>
                            <option value="mobikwik">MobiKwik</option>
                            <option value="freecharge">Freecharge</option>
                            <option value="amazonpay">Amazon Pay</option>
                        </select>
                    </div>
                </div>

                <!-- PAY BUTTON -->
                <button class="pay-button" id="payButton" disabled>
                    <span id="payButtonText">Enter details to continue</span>
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const paymentLink = @json($paymentLink);
        let selectedMethod = 'card';
        
        const payButton = document.getElementById('payButton');
        const payButtonText = document.getElementById('payButtonText');
        const successAlert = document.getElementById('successAlert');
        const errorAlert = document.getElementById('errorAlert');
        const successMessage = document.getElementById('successMessage');
        const errorMessage = document.getElementById('errorMessage');

        // Payment method switching
        document.querySelectorAll('.payment-method-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.payment-method-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                
                const method = btn.dataset.method;
                selectedMethod = method;
                document.querySelectorAll('.payment-form').forEach(f => f.classList.remove('active'));
                document.getElementById(method + 'Form').classList.add('active');
                
                validateForm();
            });
        });

        // Card number formatting
        const cardNumberInput = document.getElementById('cardNumber');
        if (cardNumberInput) {
            cardNumberInput.addEventListener('input', (e) => {
                let value = e.target.value.replace(/\s/g, '');
                let formattedValue = value.match(/.{1,4}/g)?.join(' ') || value;
                e.target.value = formattedValue;
                validateForm();
            });
        }

        // Real-time validation
        document.querySelectorAll('input, select').forEach(input => {
            input.addEventListener('input', validateForm);
            input.addEventListener('change', validateForm);
        });

        // Update pay button text and validate amount in real-time (for partial payments)
        @if($paymentLink->allow_partial_payment)
        const customAmountInput = document.getElementById('customAmount');
        const amountErrorDiv = document.getElementById('amountError');
        
        if (customAmountInput && amountErrorDiv) {
            customAmountInput.addEventListener('input', () => {
                const customAmount = parseFloat(customAmountInput.value) || 0;
                const remainingBalance = parseFloat({{ $paymentLink->getRemainingBalance() }});
                const totalAmount = parseFloat({{ $paymentLink->amount }});
                
                // Clear previous error styling
                customAmountInput.classList.remove('is-invalid', 'is-valid');
                amountErrorDiv.style.display = 'none';
                amountErrorDiv.textContent = '';
                
                // Validate amount
                if (customAmountInput.value && customAmountInput.value.trim() !== '') {
                    // First check: Amount cannot exceed total amount
                    if (customAmount > totalAmount) {
                        customAmountInput.classList.add('is-invalid');
                        customAmountInput.classList.remove('is-valid');
                        amountErrorDiv.textContent = `❌ Error: Amount cannot exceed total amount of ${paymentLink.currency} ${totalAmount.toFixed(2)}`;
                        amountErrorDiv.style.display = 'block';
                        amountErrorDiv.style.color = '#dc3545';
                        amountErrorDiv.style.fontWeight = '600';
                        if (payButton) {
                            payButton.disabled = true;
                            payButtonText.textContent = 'Invalid Amount';
                        }
                        return;
                    }
                    
                    // Second check: Amount cannot exceed remaining balance
                    if (customAmount > remainingBalance) {
                        customAmountInput.classList.add('is-invalid');
                        customAmountInput.classList.remove('is-valid');
                        amountErrorDiv.textContent = `❌ Error: Amount cannot exceed remaining balance of ${paymentLink.currency} ${remainingBalance.toFixed(2)}`;
                        amountErrorDiv.style.display = 'block';
                        amountErrorDiv.style.color = '#dc3545';
                        amountErrorDiv.style.fontWeight = '600';
                        if (payButton) {
                            payButton.disabled = true;
                            payButtonText.textContent = 'Invalid Amount';
                        }
                        return;
                    }
                    
                    // Third check: Amount must be at least 0.01
                    if (customAmount < 0.01) {
                        customAmountInput.classList.add('is-invalid');
                        customAmountInput.classList.remove('is-valid');
                        amountErrorDiv.textContent = '❌ Error: Amount must be at least 0.01';
                        amountErrorDiv.style.display = 'block';
                        amountErrorDiv.style.color = '#dc3545';
                        amountErrorDiv.style.fontWeight = '600';
                        if (payButton) {
                            payButton.disabled = true;
                            payButtonText.textContent = 'Invalid Amount';
                        }
                        return;
                    }
                    
                    // Valid amount - clear errors
                    customAmountInput.classList.remove('is-invalid');
                    customAmountInput.classList.add('is-valid');
                    amountErrorDiv.style.display = 'none';
                    amountErrorDiv.textContent = '';
                    
                    // Update pay button text immediately with custom amount
                    if (payButton && payButtonText) {
                        // Check if form is valid (customer details and payment method)
                        const formValid = validateForm();
                        if (formValid) {
                            payButton.disabled = false;
                            // Force update to custom amount (don't let validateForm override it)
                            payButtonText.textContent = `Pay ${paymentLink.currency} ${customAmount.toFixed(2)}`;
                        } else {
                            payButton.disabled = true;
                            payButtonText.textContent = 'Complete payment details';
                        }
                    }
                } else {
                    // Empty input - reset to default
                    customAmountInput.classList.remove('is-invalid', 'is-valid');
                    amountErrorDiv.style.display = 'none';
                    if (payButton) {
                        const formValid = validateForm();
                        if (formValid) {
                            payButton.disabled = false;
                            payButtonText.textContent = `Pay ${paymentLink.currency} ${remainingBalance.toFixed(2)}`;
                        } else {
                            payButton.disabled = true;
                            payButtonText.textContent = 'Complete payment details';
                        }
                    }
                }
            });
        }
        @endif

        function validateForm() {
            const name = document.getElementById('customerName').value.trim();
            const email = document.getElementById('customerEmail').value.trim();
            const phone = document.getElementById('customerPhone').value.trim();
            
            if (!name || !email || !phone || phone.length !== 10) {
                payButton.disabled = true;
                payButtonText.textContent = 'Enter customer details';
                return false;
            }

            let methodValid = false;
            
            if (selectedMethod === 'card') {
                const cardNumber = document.getElementById('cardNumber').value.replace(/\s/g, '');
                const cardHolder = document.getElementById('cardHolder').value.trim();
                const month = document.getElementById('expiryMonth').value.trim();
                const year = document.getElementById('expiryYear').value.trim();
                const cvv = document.getElementById('cvv').value.trim();
                
                methodValid = cardNumber.length >= 15 && cardHolder && 
                             month.length === 2 && year.length === 4 && cvv.length === 3;
            } else if (selectedMethod === 'upi') {
                const upiId = document.getElementById('upiId').value.trim();
                const upiApp = document.getElementById('upiApp').value;
                methodValid = upiId || upiApp;
            } else if (selectedMethod === 'netbanking') {
                methodValid = document.getElementById('bankCode').value;
            } else if (selectedMethod === 'wallet') {
                methodValid = document.getElementById('walletProvider').value;
            }

            if (methodValid) {
                payButton.disabled = false;
                @if($paymentLink->allow_partial_payment)
                    // Check if custom amount is entered
                    const customAmountInput = document.getElementById('customAmount');
                    if (customAmountInput && customAmountInput.value && customAmountInput.value.trim() !== '') {
                        const customAmount = parseFloat(customAmountInput.value) || 0;
                        const totalAmount = parseFloat({{ $paymentLink->amount }});
                        const remainingBalance = parseFloat({{ $paymentLink->getRemainingBalance() }});
                        
                        // Only update if amount is valid
                        if (customAmount >= 0.01 && customAmount <= totalAmount && customAmount <= remainingBalance) {
                            payButtonText.textContent = `Pay ${paymentLink.currency} ${customAmount.toFixed(2)}`;
                        } else {
                            const remainingBalance = parseFloat({{ $paymentLink->getRemainingBalance() }});
                            payButtonText.textContent = `Pay ${paymentLink.currency} ${remainingBalance.toFixed(2)}`;
                        }
                    } else {
                        const remainingBalance = parseFloat({{ $paymentLink->getRemainingBalance() }});
                        payButtonText.textContent = `Pay ${paymentLink.currency} ${remainingBalance.toFixed(2)}`;
                    }
                @else
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${parseFloat(paymentLink.amount).toFixed(2)}`;
                @endif
            } else {
                payButton.disabled = true;
                payButtonText.textContent = 'Complete payment details';
            }
            
            return methodValid;
        }

        // Process payment
        payButton.addEventListener('click', async () => {
            if (payButton.disabled) return;
            
            successAlert.style.display = 'none';
            errorAlert.style.display = 'none';
            
            payButton.disabled = true;
            payButtonText.innerHTML = '<span class="spinner"></span> Processing...';

            const paymentData = {
                payment_method: selectedMethod,
                customer_details: {
                    name: document.getElementById('customerName').value.trim(),
                    email: document.getElementById('customerEmail').value.trim(),
                    phone: document.getElementById('customerPhone').value.trim(),
                },
                payment_details: {}
            };

            if (selectedMethod === 'card') {
                paymentData.payment_details = {
                    card_number: document.getElementById('cardNumber').value.replace(/\s/g, ''),
                    card_holder: document.getElementById('cardHolder').value.trim(),
                    expiry_month: document.getElementById('expiryMonth').value.trim(),
                    expiry_year: document.getElementById('expiryYear').value.trim(),
                    cvv: document.getElementById('cvv').value.trim(),
                };
            } else if (selectedMethod === 'upi') {
                paymentData.payment_details = {
                    upi_id: document.getElementById('upiId').value.trim() || null,
                    upi_app: document.getElementById('upiApp').value || null,
                };
            } else if (selectedMethod === 'netbanking') {
                paymentData.payment_details = {
                    bank_code: document.getElementById('bankCode').value,
                };
            } else if (selectedMethod === 'wallet') {
                paymentData.payment_details = {
                    wallet_provider: document.getElementById('walletProvider').value,
                };
            }

            // Add custom amount if partial payment is enabled
            @if($paymentLink->allow_partial_payment)
            const customAmountInput = document.getElementById('customAmount');
            if (customAmountInput && customAmountInput.value) {
                const customAmount = parseFloat(customAmountInput.value);
                const totalAmount = parseFloat({{ $paymentLink->amount }});
                const remainingBalance = parseFloat({{ $paymentLink->getRemainingBalance() }});
                
                // Validate against total amount first
                if (customAmount > totalAmount) {
                    errorAlert.style.display = 'block';
                    errorMessage.textContent = `Error: Amount cannot exceed total amount of ${paymentLink.currency} ${totalAmount.toFixed(2)}`;
                    payButton.disabled = false;
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${remainingBalance.toFixed(2)}`;
                    return;
                }
                
                // Validate against remaining balance
                if (customAmount > remainingBalance) {
                    errorAlert.style.display = 'block';
                    errorMessage.textContent = `Error: Amount cannot exceed remaining balance of ${paymentLink.currency} ${remainingBalance.toFixed(2)}`;
                    payButton.disabled = false;
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${remainingBalance.toFixed(2)}`;
                    return;
                }
                
                if (customAmount < 0.01) {
                    errorAlert.style.display = 'block';
                    errorMessage.textContent = 'Error: Payment amount must be at least 0.01';
                    payButton.disabled = false;
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${remainingBalance.toFixed(2)}`;
                    return;
                }
                
                paymentData.amount = customAmount;
            }
            @endif

            try {
                const response = await fetch(`/pay/${paymentLink.link_token}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    },
                    body: JSON.stringify(paymentData),
                });

                const result = await response.json();

                if (result.success) {
                    let successMsg = `Order ID: ${result.order_id} | Transaction ID: ${result.transaction_id}`;
                    
                    // Show partial payment info if available
                    @if($paymentLink->allow_partial_payment)
                    if (result.payment_link) {
                        const paymentLinkInfo = result.payment_link;
                        if (paymentLinkInfo.is_partially_paid) {
                            successMsg += `\n\nAmount Paid: ${paymentLink.currency} ${parseFloat(paymentLinkInfo.amount_paid).toFixed(2)}`;
                            successMsg += `\nRemaining Balance: ${paymentLink.currency} ${parseFloat(paymentLinkInfo.remaining_balance).toFixed(2)}`;
                            if (!paymentLinkInfo.is_fully_paid) {
                                successMsg += `\n\nYou can use this same link to pay the remaining balance later.`;
                            }
                        }
                    }
                    @endif
                    
                    successMessage.textContent = successMsg;
                    successAlert.style.display = 'flex';
                    payButtonText.textContent = 'Payment Successful!';
                    payButton.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                    successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Redirect to merchant test app success page after 2 seconds
                    if (result.redirect_url) {
                        setTimeout(() => {
                            window.location.href = result.redirect_url;
                        }, 2000);
                    }
                    
                    // Disable all inputs
                    document.querySelectorAll('input, select, button').forEach(el => el.disabled = true);
                } else {
                    errorMessage.textContent = result.message || 'Payment failed. Please try again.';
                    errorAlert.style.display = 'flex';
                    errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    
                    // Redirect to merchant test app failure page after 2 seconds
                    if (result.redirect_url) {
                        setTimeout(() => {
                            window.location.href = result.redirect_url;
                        }, 2000);
                    } else {
                        payButton.disabled = false;
                        @if($paymentLink->allow_partial_payment)
                    const remainingBalance = parseFloat({{ $paymentLink->getRemainingBalance() }});
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${remainingBalance.toFixed(2)}`;
                @else
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${parseFloat(paymentLink.amount).toFixed(2)}`;
                @endif
                    }
                }
            } catch (error) {
                console.error('Payment error:', error);
                errorMessage.textContent = 'Network error. Please check your connection and try again.';
                errorAlert.style.display = 'flex';
                payButton.disabled = false;
                @if($paymentLink->allow_partial_payment)
                    const remainingBalance = parseFloat({{ $paymentLink->getRemainingBalance() }});
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${remainingBalance.toFixed(2)}`;
                @else
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${parseFloat(paymentLink.amount).toFixed(2)}`;
                @endif
            }
        });

        validateForm();

        // Test mode simulation buttons
        @if($paymentLink->test_mode)
        const simulateSuccessBtn = document.getElementById('simulateSuccessBtn');
        const simulateFailBtn = document.getElementById('simulateFailBtn');

        if (simulateSuccessBtn) {
            simulateSuccessBtn.addEventListener('click', async () => {
                if (payButton.disabled && !document.getElementById('customerName').value.trim()) {
                    alert('Please fill in customer details first');
                    return;
                }

                successAlert.style.display = 'none';
                errorAlert.style.display = 'none';
                
                payButton.disabled = true;
                payButtonText.innerHTML = '<span class="spinner"></span> Simulating Success...';

                // Simulate successful payment
                const paymentData = {
                    payment_method: selectedMethod || 'card',
                    customer_details: {
                        name: document.getElementById('customerName').value.trim() || 'Test Customer',
                        email: document.getElementById('customerEmail').value.trim() || 'test@example.com',
                        phone: document.getElementById('customerPhone').value.trim() || '9876543210',
                    },
                    payment_details: {
                        simulate: true,
                        simulate_result: 'success'
                    }
                };

                try {
                    const response = await fetch(`/pay/${paymentLink.link_token}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(paymentData),
                    });

                    const result = await response.json();

                    if (result.success) {
                        successMessage.textContent = `Order ID: ${result.order_id} | Transaction ID: ${result.transaction_id}`;
                        successAlert.style.display = 'flex';
                        payButtonText.textContent = 'Payment Successful!';
                        payButton.style.background = 'linear-gradient(135deg, #10b981 0%, #059669 100%)';
                        successAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        // Redirect after showing success
                        setTimeout(() => {
                            const baseUrl = window.location.origin;
                            window.location.href = result.redirect_url || `${baseUrl}/success-simple.html?transaction_id=${result.transaction_id}`;
                        }, 2000);
                    } else {
                        errorMessage.textContent = result.message || 'Simulation failed';
                        errorAlert.style.display = 'flex';
                        errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        // Redirect to failure page after showing error
                        setTimeout(() => {
                            const baseUrl = window.location.origin;
                            window.location.href = result.redirect_url || `${baseUrl}/failure-simple.html?transaction_id=${result.transaction_id}`;
                        }, 2000);
                    }
                } catch (error) {
                    console.error('Simulation error:', error);
                    errorMessage.textContent = 'Simulation error occurred';
                    errorAlert.style.display = 'flex';
                    payButton.disabled = false;
                    @if($paymentLink->allow_partial_payment)
                    const remainingBalance = parseFloat({{ $paymentLink->getRemainingBalance() }});
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${remainingBalance.toFixed(2)}`;
                @else
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${parseFloat(paymentLink.amount).toFixed(2)}`;
                @endif
                }
            });
        }

        if (simulateFailBtn) {
            simulateFailBtn.addEventListener('click', async () => {
                if (payButton.disabled && !document.getElementById('customerName').value.trim()) {
                    alert('Please fill in customer details first');
                    return;
                }

                successAlert.style.display = 'none';
                errorAlert.style.display = 'none';
                
                payButton.disabled = true;
                payButtonText.innerHTML = '<span class="spinner"></span> Simulating Failure...';

                // Simulate failed payment
                const paymentData = {
                    payment_method: selectedMethod || 'card',
                    customer_details: {
                        name: document.getElementById('customerName').value.trim() || 'Test Customer',
                        email: document.getElementById('customerEmail').value.trim() || 'test@example.com',
                        phone: document.getElementById('customerPhone').value.trim() || '9876543210',
                    },
                    payment_details: {
                        simulate: true,
                        simulate_result: 'failed'
                    }
                };

                try {
                    const response = await fetch(`/pay/${paymentLink.link_token}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                        body: JSON.stringify(paymentData),
                    });

                    const result = await response.json();

                    if (result.success) {
                        errorMessage.textContent = 'Unexpected: Simulation returned success';
                        errorAlert.style.display = 'flex';
                    } else {
                        errorMessage.textContent = result.message || 'Payment failed (simulated)';
                        errorAlert.style.display = 'flex';
                        errorAlert.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        
                        // Redirect to failure page
                        setTimeout(() => {
                            const baseUrl = window.location.origin;
                            window.location.href = result.redirect_url || `${baseUrl}/failure-simple.html?transaction_id=${result.transaction_id || ''}`;
                        }, 2000);
                    }
                    
                    payButton.disabled = false;
                    @if($paymentLink->allow_partial_payment)
                    const remainingBalance = parseFloat({{ $paymentLink->getRemainingBalance() }});
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${remainingBalance.toFixed(2)}`;
                @else
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${parseFloat(paymentLink.amount).toFixed(2)}`;
                @endif
                } catch (error) {
                    console.error('Simulation error:', error);
                    errorMessage.textContent = 'Payment failed (simulated)';
                    errorAlert.style.display = 'flex';
                    payButton.disabled = false;
                    @if($paymentLink->allow_partial_payment)
                    const remainingBalance = parseFloat({{ $paymentLink->getRemainingBalance() }});
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${remainingBalance.toFixed(2)}`;
                @else
                    payButtonText.textContent = `Pay ${paymentLink.currency} ${parseFloat(paymentLink.amount).toFixed(2)}`;
                @endif
                }
            });
        }
        @endif
    </script>
</body>
</html>
