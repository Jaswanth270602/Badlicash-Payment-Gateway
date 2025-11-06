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
            --primary-violet: #6366f1;
            --primary-violet-dark: #4f46e5;
            --gradient-start: #6366f1;
            --gradient-end: #8b5cf6;
        }

        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #e9ecef 100%);
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            min-height: 100vh;
            padding: 20px 0;
        }

        .payment-container {
            max-width: 900px;
            margin: 0 auto;
        }

        .payment-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
            overflow: hidden;
            margin-bottom: 20px;
        }

        .payment-header {
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }

        .payment-header h1 {
            font-size: 28px;
            font-weight: 700;
            margin: 0 0 10px 0;
        }

        .payment-body {
            padding: 30px;
        }

        .amount-section {
            text-align: center;
            padding: 30px 0;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 30px;
        }

        .amount-label {
            font-size: 14px;
            color: #6b7280;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
        }

        .amount-value {
            font-size: 48px;
            font-weight: 700;
            color: #1f2937;
            margin: 0;
        }

        .amount-currency {
            font-size: 24px;
            color: #6b7280;
            font-weight: 500;
        }

        .payment-methods {
            margin-bottom: 30px;
        }

        .payment-method-title {
            font-size: 18px;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 20px;
        }

        .payment-method-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .payment-method-card {
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: #fff;
            user-select: none;
            -webkit-user-select: none;
        }

        .payment-method-card:focus {
            outline: 2px solid var(--primary-violet);
            outline-offset: 2px;
        }

        .payment-method-card:hover {
            border-color: var(--primary-violet);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.15);
        }

        .payment-method-card.selected {
            border-color: var(--primary-violet);
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(139, 92, 246, 0.05) 100%);
        }

        .payment-method-card.disabled {
            opacity: 0.5;
            cursor: not-allowed;
            pointer-events: none;
        }

        .payment-method-card i {
            font-size: 36px;
            color: var(--primary-violet);
            margin-bottom: 10px;
        }

        .payment-method-card.disabled i {
            color: #9ca3af;
        }

        .payment-method-card .method-name {
            font-weight: 600;
            color: #1f2937;
            margin: 0;
        }

        .payment-method-card.disabled .method-name {
            color: #9ca3af;
        }

        .customer-form {
            background: #f9fafb;
            border-radius: 12px;
            padding: 25px;
            margin-bottom: 30px;
        }

        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 8px;
        }

        .form-control, .form-select {
            border-radius: 8px;
            border: 1px solid #d1d5db;
            padding: 12px 16px;
            font-size: 15px;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-violet);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        .btn-pay {
            width: 100%;
            padding: 16px;
            font-size: 18px;
            font-weight: 600;
            border-radius: 12px;
            background: linear-gradient(135deg, var(--gradient-start) 0%, var(--gradient-end) 100%);
            border: none;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-pay:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }

        .btn-pay:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .alert {
            border-radius: 12px;
            border: none;
        }

        .spinner-border-sm {
            width: 1rem;
            height: 1rem;
        }

        .merchant-info {
            text-align: center;
            padding: 20px;
            background: #f9fafb;
            border-radius: 12px;
            margin-top: 20px;
        }

        .merchant-info small {
            color: #6b7280;
        }

        .method-error {
            color: #dc3545;
            font-size: 14px;
            margin-top: 10px;
            display: none;
        }

        .method-error.show {
            display: block;
        }

        @media (max-width: 768px) {
            .payment-header h1 {
                font-size: 24px;
            }

            .amount-value {
                font-size: 36px;
            }

            .payment-method-grid {
                grid-template-columns: 1fr;
            }

            .payment-body {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="payment-container">
        @if(!$paymentLink->isActive())
            <div class="payment-card">
                <div class="payment-body text-center py-5">
                    <i class="bi bi-exclamation-triangle-fill text-warning" style="font-size: 64px;"></i>
                    <h2 class="mt-3">Payment Link Expired</h2>
                    <p class="text-muted">This payment link is no longer active or has expired.</p>
                </div>
            </div>
        @else
            <div class="payment-card">
                <div class="payment-header">
                    <h1>{{ $paymentLink->title }}</h1>
                    @if($paymentLink->description)
                        <p>{{ $paymentLink->description }}</p>
                    @endif
                </div>

                <div class="payment-body">
                    <div class="amount-section">
                        <div class="amount-label">Amount to Pay</div>
                        <h2 class="amount-value">
                            {{ number_format($paymentLink->amount, 2) }}
                            <span class="amount-currency">{{ $paymentLink->currency }}</span>
                        </h2>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">
                            <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
                        </div>
                    @endif

                    <form id="paymentForm" method="POST" action="{{ route('payment.process', $paymentLink->link_token) }}">
                        @csrf
                        <input type="hidden" name="amount" value="{{ $paymentLink->amount }}" id="amountInput">
                        
                        <div class="payment-methods">
                            <h3 class="payment-method-title">Select Payment Method</h3>
                            <div class="payment-method-grid" id="paymentMethodGrid">
                                @php
                                    $paymentMethods = $paymentLink->payment_methods ?? ['card', 'upi', 'netbanking', 'wallet'];
                                @endphp

                                @if(in_array('card', $paymentMethods))
                                <div class="payment-method-card" data-method="card" role="button" tabindex="0" aria-label="Select Card payment method">
                                    <i class="bi bi-credit-card"></i>
                                    <p class="method-name">Card</p>
                                    <small class="text-muted">Credit/Debit Card</small>
                                </div>
                                @endif

                                @if(in_array('upi', $paymentMethods))
                                <div class="payment-method-card" data-method="upi" role="button" tabindex="0" aria-label="Select UPI payment method">
                                    <i class="bi bi-phone"></i>
                                    <p class="method-name">UPI</p>
                                    <small class="text-muted">Pay via UPI</small>
                                </div>
                                @endif

                                @if(in_array('netbanking', $paymentMethods))
                                <div class="payment-method-card" data-method="netbanking" role="button" tabindex="0" aria-label="Select Net Banking payment method">
                                    <i class="bi bi-bank"></i>
                                    <p class="method-name">Net Banking</p>
                                    <small class="text-muted">Online Banking</small>
                                </div>
                                @endif

                                @if(in_array('wallet', $paymentMethods))
                                <div class="payment-method-card" data-method="wallet" role="button" tabindex="0" aria-label="Select Wallet payment method">
                                    <i class="bi bi-wallet2"></i>
                                    <p class="method-name">Wallets</p>
                                    <small class="text-muted">Paytm, PhonePe, etc.</small>
                                </div>
                                @endif
                            </div>
                            <input type="hidden" name="payment_method" id="paymentMethodInput" value="" required>
                            <div class="method-error" id="methodError">
                                <i class="bi bi-exclamation-circle me-1"></i>Please select a payment method
                            </div>
                        </div>

                        <div class="customer-form">
                            <h4 class="mb-4">Customer Details</h4>
                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="customer_name" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control" name="customer_email" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone <span class="text-danger">*</span></label>
                                    <input type="tel" class="form-control" name="customer_phone" required>
                                </div>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-pay" id="payButton" disabled>
                            <span id="payButtonText">Pay {{ number_format($paymentLink->amount, 2) }} {{ $paymentLink->currency }}</span>
                            <span id="payButtonLoader" style="display: none;">
                                <span class="spinner-border spinner-border-sm me-2"></span>
                                Processing...
                            </span>
                        </button>
                    </form>

                    <div class="merchant-info">
                        <small>
                            <i class="bi bi-shield-check text-success"></i>
                            Secured by <strong>BadliCash</strong>
                            @if($paymentLink->merchant)
                                <br>Merchant: {{ $paymentLink->merchant->name }}
                            @endif
                        </small>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (function() {
            'use strict';
            
            var selectedMethod = null;
            var methodInput = null;
            var payButton = null;
            var methodError = null;
            var payButtonText = null;
            var payButtonLoader = null;

            function init() {
                methodInput = document.getElementById('paymentMethodInput');
                payButton = document.getElementById('payButton');
                methodError = document.getElementById('methodError');
                payButtonText = document.getElementById('payButtonText');
                payButtonLoader = document.getElementById('payButtonLoader');
                
                // Setup form submission
                var form = document.getElementById('paymentForm');
                if (form) {
                    form.addEventListener('submit', handleFormSubmit);
                }
                
                // Hide amount input
                var amountInput = document.getElementById('amountInput');
                if (amountInput) {
                    amountInput.style.display = 'none';
                }

                // Add click handlers to all payment method cards
                var cards = document.querySelectorAll('.payment-method-card');
                cards.forEach(function(card) {
                    var method = card.getAttribute('data-method');
                    if (method) {
                        // Remove existing onclick and use event listener instead
                        card.onclick = null;
                        card.addEventListener('click', function(e) {
                            e.preventDefault();
                            selectPaymentMethod(method, card);
                        });
                    }
                });
            }

            // Global function for onclick handlers (for backward compatibility)
            window.selectPaymentMethod = function(method, element) {
                selectPaymentMethod(method, element);
            };

            function selectPaymentMethod(method, element) {
                if (!method) {
                    console.error('Invalid payment method selection');
                    return;
                }
                
                if (!element) {
                    // Find element by data-method attribute
                    element = document.querySelector('.payment-method-card[data-method="' + method + '"]');
                    if (!element) {
                        console.error('Payment method card not found for method:', method);
                        return;
                    }
                }
                
                selectedMethod = method;
                
                // Update hidden input
                if (methodInput) {
                    methodInput.value = method;
                    methodInput.setAttribute('value', method);
                    // Trigger change event to ensure form validation
                    var event = new Event('change', { bubbles: true });
                    methodInput.dispatchEvent(event);
                }
                
                // Update UI - remove selected from all
                var cards = document.querySelectorAll('.payment-method-card');
                cards.forEach(function(card) {
                    card.classList.remove('selected');
                });
                
                // Add selected to clicked card
                if (element) {
                    element.classList.add('selected');
                }
                
                // Enable pay button
                if (payButton) {
                    payButton.disabled = false;
                    payButton.classList.remove('disabled');
                }
                
                // Hide error
                if (methodError) {
                    methodError.classList.remove('show');
                    methodError.style.display = 'none';
                }
                
                console.log('Payment method selected:', method);
            }

            function handleFormSubmit(e) {
                // Check if method is selected
                if (!selectedMethod) {
                    // Try to get from hidden input
                    if (methodInput && methodInput.value) {
                        selectedMethod = methodInput.value;
                    }
                }

                if (!selectedMethod || !methodInput || !methodInput.value) {
                    e.preventDefault();
                    e.stopPropagation();
                    
                    if (methodError) {
                        methodError.classList.add('show');
                        methodError.style.display = 'block';
                        methodError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }
                    
                    // Highlight payment method section
                    var methodGrid = document.getElementById('paymentMethodGrid');
                    if (methodGrid) {
                        methodGrid.style.border = '2px solid #dc3545';
                        methodGrid.style.borderRadius = '8px';
                        methodGrid.style.padding = '10px';
                        setTimeout(function() {
                            methodGrid.style.border = '';
                            methodGrid.style.borderRadius = '';
                            methodGrid.style.padding = '';
                        }, 2000);
                    }
                    
                    return false;
                }

                // Show loading state
                if (payButtonText) {
                    payButtonText.style.display = 'none';
                }
                if (payButtonLoader) {
                    payButtonLoader.style.display = 'inline';
                }
                if (payButton) {
                    payButton.disabled = true;
                }

                // Form will submit normally
                return true;
            }

            // Initialize when DOM is ready
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', init);
            } else {
                // DOM already loaded, initialize immediately
                setTimeout(init, 100);
            }
        })();
    </script>
</body>
</html>
