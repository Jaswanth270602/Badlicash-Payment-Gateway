<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Sign up – BadliCash Merchant Account</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        :root {
            --bc-primary: #6366f1;
            --bc-primary-dark: #4f46e5;
            --bc-bg: #020617;
        }

        body {
            background: radial-gradient(circle at top left, #1d213b 0, #020617 40%, #020617 100%);
            font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            color: #e5e7eb;
        }
        .auth-wrapper {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 12px;
        }
        .auth-card {
            max-width: 1080px;
            width: 100%;
            background: rgba(15, 23, 42, 0.9);
            border-radius: 22px;
            border: 1px solid rgba(55, 65, 81, 0.9);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.9);
            overflow: hidden;
        }
        .auth-left {
            padding: 26px 26px 18px;
            border-right: 1px solid rgba(55, 65, 81, 0.9);
        }
        .auth-right {
            padding: 22px 24px 20px;
        }
        .step-pill {
            border-radius: 999px;
            padding: 4px 10px;
            background: rgba(15, 23, 42, 0.9);
            border: 1px solid rgba(55, 65, 81, 0.9);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            color: #9ca3af;
        }
        .nav-tabs {
            border-bottom: 1px solid rgba(55, 65, 81, 0.9);
        }
        .nav-tabs .nav-link {
            border: none;
            color: #d1d5db;
            font-size: 13px;
            font-weight: 500;
            padding: 10px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .nav-tabs .nav-link:hover {
            color: #d1d5db;
            background: rgba(248, 250, 252, 0.05);
        }
        .nav-tabs .nav-link .step-index {
            width: 20px;
            height: 20px;
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.8);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 600;
            color: #d1d5db;
        }
        .nav-tabs .nav-link.active {
            color: #f9fafb;
            border-bottom: 2px solid #6366f1;
            font-weight: 600;
            background: rgba(248, 250, 252, 0.08) !important;
        }
        .nav-tabs .nav-link.active .step-index {
            background: #6366f1;
            border-color: #6366f1;
            color: #f9fafb;
        }
        label.form-label {
            font-size: 12px;
            color: #d1d5db;
        }
        .form-control, .form-select {
            background: #f8fafc;
            border-radius: 10px;
            border: 1px solid rgba(55, 65, 81, 0.3);
            color: #1f2937;
            font-size: 13px;
        }
        .form-control:focus, .form-select:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.4);
            background: #ffffff;
            color: #1f2937;
        }
        .form-control::placeholder {
            color: #9ca3af;
        }
        .btn-primary-pill {
            border-radius: 999px;
            background: linear-gradient(135deg, var(--bc-primary) 0%, var(--bc-primary-dark) 100%);
            border: none;
            padding: 10px 22px;
            font-size: 14px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 18px 45px rgba(79, 70, 229, 0.5);
        }
        .btn-primary-pill:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 55px rgba(79, 70, 229, 0.7);
        }
        .btn-outline-light-pill {
            border-radius: 999px;
            border: 1px solid rgba(148, 163, 184, 0.5);
            padding: 9px 18px;
            font-size: 13px;
            color: #e5e7eb;
        }
        .btn-outline-light-pill:hover {
            background: rgba(15, 23, 42, 0.85);
        }
        .small-muted {
            font-size: 12px;
            color: #9ca3af;
        }
        .error-text {
            font-size: 12px;
            color: #fecaca;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fecaca;
            border-radius: 10px;
        }
        .link-light {
            color: #818cf8;
            text-decoration: none;
            font-weight: 600;
        }
        .link-light:hover {
            color: #6366f1;
            text-decoration: underline;
        }
    </style>
</head>
<body>
<div class="auth-wrapper">
    <div class="auth-card">
        <div class="row g-0">
            <div class="col-lg-4 auth-left d-flex flex-column">
                <div class="d-flex align-items-center mb-3">
                    <div class="rounded-circle bg-primary bg-opacity-20 d-flex align-items-center justify-content-center" style="width:0px;height:0px;">
                        <!-- <i class="bi bi-wallet2 text-primary"></i> -->
                    </div>
                    <div class="ms-2">
                        <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 4px;">
                            <img src="{{ asset(logo_path()) }}" alt="{{ config('app.name') }}" style="height: 42px; width: auto;">
                           
                        </div>
                        <div class="small-muted">Merchant onboarding</div>
                    </div>
                </div>
                <div class="step-pill mb-2">
                    Guided · 4 short steps · Production-ready account
                </div>
                <h2 class="h4 text-white mt-2 mb-2">Create your merchant account</h2>
                <p class="small-muted mb-3">
                    Tell us about your business, bank account and login details. We’ll spin up a secure BadliCash
                    account with full sandbox and live-mode readiness so you can start integrating immediately.
                </p>
                <ul class="list-unstyled mb-3 small-muted">
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> No paperwork for sandbox access</li>
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> Test webhooks that mirror production</li>
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> Upgrade to live mode after review</li>
                </ul>
                <div class="mt-auto pt-3 small-muted">
                    Already have an account?
                    <a href="{{ route('login') }}" class="link-light text-decoration-none">Sign in</a>
                </div>
            </div>

            <div class="col-lg-8 auth-right">
                @if(session('error'))
                    <div class="alert alert-danger py-2 small mb-3">
                        {{ session('error') }}
                    </div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger py-2 small mb-3">
                        <strong class="d-block mb-1">Please fix the highlighted fields.</strong>
                    </div>
                @endif

                <ul class="nav nav-tabs mb-3" id="signupTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="tab-business" data-bs-toggle="tab" data-bs-target="#pane-business" type="button" role="tab">
                            <span class="step-index">1</span> Business
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-tax" data-bs-toggle="tab" data-bs-target="#pane-tax" type="button" role="tab">
                            <span class="step-index">2</span> Compliance
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-bank" data-bs-toggle="tab" data-bs-target="#pane-bank" type="button" role="tab">
                            <span class="step-index">3</span> Bank
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="tab-login" data-bs-toggle="tab" data-bs-target="#pane-login" type="button" role="tab">
                            <span class="step-index">4</span> Login
                        </button>
                    </li>
                </ul>

                <form method="POST" action="{{ route('signup.post') }}" id="signupForm" novalidate>
                    @csrf
                    <div class="tab-content">
                        <!-- Step 1: Business -->
                        <div class="tab-pane fade show active" id="pane-business" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Business / Brand Name <span class="text-danger">*</span></label>
                                    <input type="text" name="business_name" value="{{ old('business_name') }}" class="form-control @error('business_name') is-invalid @enderror" required>
                                    @error('business_name')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Legal Name (as per PAN) <span class="text-danger">*</span></label>
                                    <input type="text" name="legal_name" value="{{ old('legal_name') }}" class="form-control @error('legal_name') is-invalid @enderror" required>
                                    @error('legal_name')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Business Email <span class="text-danger">*</span></label>
                                    <input type="email" name="business_email" value="{{ old('business_email') }}" class="form-control @error('business_email') is-invalid @enderror" required>
                                    @error('business_email')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Business Phone <span class="text-danger">*</span></label>
                                    <input type="tel" name="business_phone" value="{{ old('business_phone') }}" class="form-control @error('business_phone') is-invalid @enderror" pattern="[0-9]{10,15}" maxlength="15" required>
                                    @error('business_phone')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Website (optional)</label>
                                    <input type="url" name="website_link" value="{{ old('website_link') }}" class="form-control @error('website_link') is-invalid @enderror" placeholder="https://">
                                    @error('website_link')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Business Category <span class="text-danger">*</span></label>
                                    <select name="merchant_category" class="form-select @error('merchant_category') is-invalid @enderror" required>
                                        <option value="">Select category</option>
                                        @foreach(['B2B','Education','Insurance','Utilities','E-commerce','Travel & Hospitality','Telecom','High Risk','Grocery','NBFC','Government','Others'] as $cat)
                                            <option value="{{ $cat }}" @selected(old('merchant_category') === $cat)>{{ $cat }}</option>
                                        @endforeach
                                    </select>
                                    @error('merchant_category')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Country <span class="text-danger">*</span></label>
                                    <select name="business_country" class="form-select @error('business_country') is-invalid @enderror" required>
                                        <option value="">Select</option>
                                        <option value="India" @selected(old('business_country') === 'India')>India</option>
                                        <option value="USA" @selected(old('business_country') === 'USA')>USA</option>
                                        <option value="UK" @selected(old('business_country') === 'UK')>UK</option>
                                    </select>
                                    @error('business_country')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">State <span class="text-danger">*</span></label>
                                    <input type="text" name="business_state" value="{{ old('business_state') }}" class="form-control @error('business_state') is-invalid @enderror" required>
                                    @error('business_state')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">City <span class="text-danger">*</span></label>
                                    <input type="text" name="business_city" value="{{ old('business_city') }}" class="form-control @error('business_city') is-invalid @enderror" required>
                                    @error('business_city')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-8">
                                    <label class="form-label">Address Line 1 <span class="text-danger">*</span></label>
                                    <input type="text" name="address_line_1" value="{{ old('address_line_1') }}" class="form-control @error('address_line_1') is-invalid @enderror" required>
                                    @error('address_line_1')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label">Pin / ZIP Code <span class="text-danger">*</span></label>
                                    <input type="text" name="business_postal_code" value="{{ old('business_postal_code') }}" class="form-control @error('business_postal_code') is-invalid @enderror" maxlength="10" required>
                                    @error('business_postal_code')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Address Line 2 (optional)</label>
                                    <input type="text" name="address_line_2" value="{{ old('address_line_2') }}" class="form-control @error('address_line_2') is-invalid @enderror">
                                    @error('address_line_2')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Compliance -->
                        <div class="tab-pane fade" id="pane-tax" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">PAN Number <span class="text-danger">*</span></label>
                                    <input type="text" name="merchant_pan_number" value="{{ old('merchant_pan_number') }}" class="form-control text-uppercase @error('merchant_pan_number') is-invalid @enderror" maxlength="10" required>
                                    @error('merchant_pan_number')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Name on PAN <span class="text-danger">*</span></label>
                                    <input type="text" name="name_on_pan_card" value="{{ old('name_on_pan_card') }}" class="form-control @error('name_on_pan_card') is-invalid @enderror" required>
                                    @error('name_on_pan_card')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">GSTIN (optional)</label>
                                    <input type="text" name="gst_identification_no" value="{{ old('gst_identification_no') }}" class="form-control @error('gst_identification_no') is-invalid @enderror">
                                    @error('gst_identification_no')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">GSTIN State</label>
                                    <input type="text" name="gstin_state" value="{{ old('gstin_state') }}" class="form-control @error('gstin_state') is-invalid @enderror">
                                    @error('gstin_state')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">TAN (optional)</label>
                                    <input type="text" name="tan_no" value="{{ old('tan_no') }}" class="form-control @error('tan_no') is-invalid @enderror">
                                    @error('tan_no')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <p class="small-muted mb-0">
                                        We use these details to prepare your onboarding for live payouts. During sandbox testing
                                        no real charges or settlements are triggered.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Bank -->
                        <div class="tab-pane fade" id="pane-bank" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_holder_name" value="{{ old('bank_account_holder_name') }}" class="form-control @error('bank_account_holder_name') is-invalid @enderror" required>
                                    @error('bank_account_holder_name')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bank Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_number" value="{{ old('bank_account_number') }}" class="form-control @error('bank_account_number') is-invalid @enderror" minlength="8" maxlength="25" required>
                                    @error('bank_account_number')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" value="{{ old('bank_name') }}" class="form-control @error('bank_name') is-invalid @enderror" required>
                                    @error('bank_name')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Account Type <span class="text-danger">*</span></label>
                                    <select name="account_type" class="form-select @error('account_type') is-invalid @enderror" required>
                                        <option value="">Select type</option>
                                        <option value="Savings Account" @selected(old('account_type') === 'Savings Account')>Savings Account</option>
                                        <option value="Current Account" @selected(old('account_type') === 'Current Account')>Current Account</option>
                                    </select>
                                    @error('account_type')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Branch <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_branch" value="{{ old('bank_branch') }}" class="form-control @error('bank_branch') is-invalid @enderror" required>
                                    @error('bank_branch')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">IFSC Code <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_ifsc_code" value="{{ old('bank_ifsc_code') }}" class="form-control text-uppercase @error('bank_ifsc_code') is-invalid @enderror" maxlength="11" required>
                                    @error('bank_ifsc_code')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <p class="small-muted mb-0">
                                        In sandbox, we never hit real banks while you are testing. Once you are approved for live mode,
                                        these same details will be used for actual settlements.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Step 4: Login -->
                        <div class="tab-pane fade" id="pane-login" role="tabpanel">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Primary Contact Name <span class="text-danger">*</span></label>
                                    <input type="text" name="contact_name" value="{{ old('contact_name') }}" class="form-control @error('contact_name') is-invalid @enderror" required>
                                    @error('contact_name')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Mobile <span class="text-danger">*</span></label>
                                    <input type="tel" name="contact_mobile" value="{{ old('contact_mobile') }}" class="form-control @error('contact_mobile') is-invalid @enderror" pattern="[0-9]{10,15}" maxlength="15" required>
                                    @error('contact_mobile')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Contact Email <span class="text-danger">*</span></label>
                                    <input type="email" name="contact_email" value="{{ old('contact_email') }}" class="form-control @error('contact_email') is-invalid @enderror" required>
                                    @error('contact_email')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Login Email (for BadliCash) <span class="text-danger">*</span></label>
                                    <input type="email" name="login_name" value="{{ old('login_name') }}" class="form-control @error('login_name') is-invalid @enderror" required>
                                    @error('login_name')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" minlength="12" required>
                                    @error('password')<div class="error-text">{{ $message }}</div>@enderror
                                    <div class="small-muted mt-1">
                                        Min 12 characters with uppercase, lowercase, number & symbol.
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Confirm Password <span class="text-danger">*</span></label>
                                    <input type="password" name="password_confirmation" class="form-control @error('password_confirmation') is-invalid @enderror" minlength="12" required>
                                    @error('password_confirmation')<div class="error-text">{{ $message }}</div>@enderror
                                </div>
                                <div class="col-12">
                                    <p class="small-muted mb-0">
                                        By signing up, you’ll start in the BadliCash sandbox. Our team will review your details and
                                        enable live money flow once compliance checks are complete.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mt-4">
                        <button type="button" class="btn btn-outline-light-pill" id="prevStepBtn" disabled>
                            <i class="bi bi-chevron-left"></i> Back
                        </button>
                        <div class="d-flex align-items-center gap-2">
                            <span class="small-muted d-none d-md-inline">Step <span id="stepIndicator">1</span> of 4</span>
                            <button type="button" class="btn btn-primary-pill" id="nextStepBtn">
                                Next <i class="bi bi-chevron-right"></i>
                            </button>
                            <button type="submit" class="btn btn-primary-pill d-none" id="submitBtn">
                                Create my account <i class="bi bi-arrow-right-circle"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function () {
        const tabs = ['business','tax','bank','login'];
        let currentIndex = 0;
        const prevBtn = document.getElementById('prevStepBtn');
        const nextBtn = document.getElementById('nextStepBtn');
        const submitBtn = document.getElementById('submitBtn');
        const stepIndicator = document.getElementById('stepIndicator');

        function updateButtonStates(index) {
            currentIndex = index;
            prevBtn.disabled = currentIndex === 0;
            nextBtn.classList.toggle('d-none', currentIndex === tabs.length - 1);
            submitBtn.classList.toggle('d-none', currentIndex !== tabs.length - 1);
            stepIndicator.textContent = (currentIndex + 1).toString();
        }

        function updateStep(delta) {
            const newIndex = Math.min(Math.max(currentIndex + delta, 0), tabs.length - 1);
            const targetId = 'tab-' + tabs[newIndex];
            const tabTriggerEl = document.querySelector('#' + targetId);
            if (tabTriggerEl) {
                new bootstrap.Tab(tabTriggerEl).show();
            }
            updateButtonStates(newIndex);
        }

        // Listen for tab changes from nav bar clicks
        const tabElements = document.querySelectorAll('[data-bs-toggle="tab"]');
        tabElements.forEach(function(tabEl, index) {
            tabEl.addEventListener('shown.bs.tab', function (event) {
                // Find which tab was shown
                const tabId = event.target.id;
                const tabIndex = tabs.findIndex(tab => 'tab-' + tab === tabId);
                if (tabIndex !== -1) {
                    updateButtonStates(tabIndex);
                }
            });
        });

        prevBtn.addEventListener('click', function () {
            updateStep(-1);
        });

        nextBtn.addEventListener('click', function () {
            updateStep(1);
        });

        // Initialize button states on page load
        updateButtonStates(0);
    })();
</script>
</body>
</html>


