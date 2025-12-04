@extends('layouts.app')

@section('title', 'BadliCash – The Safer, Smarter Payment Gateway for Modern Businesses')

@section('content')
<style>
    :root {
        --bc-primary: #6366f1;
        --bc-primary-dark: #4f46e5;
        --bc-primary-soft: #eef2ff;
        --bc-bg: #020617;
    }

    body {
        background: radial-gradient(circle at top left, #1d213b 0, #020617 40%, #020617 100%);
        color: #e5e7eb;
    }

    .landing-wrapper {
        min-height: 100vh;
        display: flex;
        flex-direction: column;
    }

    .landing-nav {
        padding: 20px 0;
    }

    .landing-badge {
        background: rgba(148, 163, 184, 0.25);
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .12em;
        color: #cbd5f5;
    }

    .gradient-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        border-radius: 999px;
        padding: 4px 10px 4px 4px;
        background: linear-gradient(135deg, rgba(56, 189, 248, 0.2), rgba(129, 140, 248, 0.4));
        border: 1px solid rgba(129, 140, 248, 0.7);
        font-size: 12px;
        color: #e0f2fe;
    }

    .hero-title {
        font-size: clamp(2.6rem, 4vw, 3.4rem);
        font-weight: 800;
        line-height: 1.05;
        letter-spacing: -.04em;
        color: #f9fafb;
    }

    .hero-gradient-text {
        background: linear-gradient(135deg, #a855f7, #6366f1, #22c55e);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }

    .hero-subtitle {
        font-size: 16px;
        color: #9ca3af;
        max-width: 520px;
    }

    .hero-metrics {
        display: flex;
        gap: 24px;
        flex-wrap: wrap;
        margin-top: 24px;
    }

    .hero-metric {
        min-width: 120px;
    }

    .hero-metric-value {
        font-size: 20px;
        font-weight: 700;
        color: #e5e7eb;
    }

    .hero-metric-label {
        font-size: 12px;
        color: #9ca3af;
        text-transform: uppercase;
        letter-spacing: .08em;
    }

    .btn-primary-hero {
        background: linear-gradient(135deg, var(--bc-primary) 0%, var(--bc-primary-dark) 100%);
        border: none;
        padding: 12px 26px;
        border-radius: 999px;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 18px 45px rgba(79, 70, 229, 0.5);
    }

    .btn-primary-hero:hover {
        transform: translateY(-1px);
        box-shadow: 0 22px 55px rgba(79, 70, 229, 0.7);
    }

    .btn-outline-hero {
        border-radius: 999px;
        border: 1px solid rgba(148, 163, 184, 0.5);
        color: #e5e7eb;
        padding: 11px 22px;
        font-weight: 500;
    }

    .btn-outline-hero:hover {
        background: rgba(15, 23, 42, 0.85);
    }

    .hero-card {
        background: radial-gradient(circle at top, rgba(248, 250, 252, 0.06), rgba(15, 23, 42, 0.95));
        border-radius: 24px;
        padding: 22px 22px 18px;
        border: 1px solid rgba(148, 163, 184, 0.35);
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.7);
    }

    .hero-logos {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        align-items: center;
        margin-top: 14px;
    }

    .logo-pill {
        border-radius: 999px;
        padding: 6px 12px;
        background: rgba(15, 23, 42, 0.8);
        border: 1px solid rgba(148, 163, 184, 0.5);
        font-size: 11px;
        color: #cbd5f5;
    }

    .features-grid {
        margin-top: 64px;
    }

    .feature-card {
        background: rgba(15, 23, 42, 0.9);
        border-radius: 18px;
        padding: 18px 18px 16px;
        border: 1px solid rgba(55, 65, 81, 0.9);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.8);
        height: 100%;
    }

    .feature-icon {
        width: 32px;
        height: 32px;
        border-radius: 999px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(37, 99, 235, 0.12);
        color: #60a5fa;
        margin-bottom: 8px;
    }

    .testimonials-strip {
        margin-top: 50px;
        border-radius: 18px;
        padding: 14px 18px;
        background: rgba(15, 23, 42, 0.9);
        border: 1px solid rgba(55, 65, 81, 0.9);
        display: flex;
        flex-wrap: wrap;
        gap: 12px;
        align-items: center;
    }

    .testimonial-avatar {
        width: 36px;
        height: 36px;
        border-radius: 50%;
        background: linear-gradient(135deg, #38bdf8, #6366f1);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 18px;
        color: #0f172a;
        font-weight: 700;
    }

    .testimonial-quote {
        font-size: 13px;
        color: #e5e7eb;
    }

    .floating-badge {
        position: absolute;
        top: -14px;
        right: 24px;
        padding: 4px 10px;
        border-radius: 999px;
        background: rgba(34, 197, 94, 0.15);
        border: 1px solid rgba(34, 197, 94, 0.6);
        font-size: 10px;
        color: #bbf7d0;
        text-transform: uppercase;
        letter-spacing: .1em;
    }

    @media (max-width: 992px) {
        .hero-card {
            margin-top: 32px;
        }
    }
</style>

<div class="landing-wrapper">
    <header class="landing-nav">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-primary bg-opacity-20 d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                    <i class="bi bi-wallet2 text-primary"></i>
                </div>
                <div>
                    <div class="fw-bold text-white">BadliCash</div>
                    <div style="font-size:11px;color:#9ca3af;">Safer payments for ambitious teams</div>
                </div>
            </div>
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('login') }}" class="btn btn-sm btn-outline-hero">
                    <i class="bi bi-box-arrow-in-right me-1"></i> Login
                </a>
                <a href="{{ route('signup') }}" class="btn btn-sm btn-primary-hero">
                    Start accepting payments
                    <i class="bi bi-arrow-right-short"></i>
                </a>
            </div>
        </div>
    </header>

    <section class="py-4 py-md-5">
        <div class="row align-items-center g-4">
            <div class="col-lg-6">
                <div class="mb-3">
                    <span class="gradient-pill">
                        <span class="badge bg-dark border border-info border-opacity-50 rounded-pill me-1">
                            <i class="bi bi-shield-check"></i>
                        </span>
                        PCI-ready sandbox · Go live in days, not weeks
                    </span>
                </div>
                <h1 class="hero-title mb-3">
                    The payment gateway
                    <span class="hero-gradient-text">teams actually love</span>.
                </h1>
                <p class="hero-subtitle mb-3">
                    BadliCash is a developer-first, bank-grade payment gateway designed to feel as smooth as Razorpay,
                    but with obsessive focus on observability, sandbox–production parity, and merchant UX.
                </p>
                <ul class="list-unstyled mb-4" style="font-size:13px;color:#9ca3af;">
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> Unified APIs for payments, refunds, disputes & settlements</li>
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> Realistic sandbox flows with webhooks that mirror production</li>
                    <li class="mb-1"><i class="bi bi-check-circle-fill text-success me-1"></i> SDK checkout widget that drops into your app in minutes</li>
                </ul>

                <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                    <a href="{{ route('signup') }}" class="btn btn-primary-hero">
                        Create free test account
                        <i class="bi bi-arrow-right-short"></i>
                    </a>
                    <a href="#features" class="btn btn-outline-hero">
                        View features
                    </a>
                </div>

                <div class="hero-metrics">
                    <div class="hero-metric">
                        <div class="hero-metric-value">99.95%</div>
                        <div class="hero-metric-label">Uptime Simulated</div>
                    </div>
                    <div class="hero-metric">
                        <div class="hero-metric-value">&lt; 60 sec</div>
                        <div class="hero-metric-label">Webhook Latency</div>
                    </div>
                    <div class="hero-metric">
                        <div class="hero-metric-value">0</div>
                        <div class="hero-metric-label">Bank Dependencies Today</div>
                    </div>
                </div>
            </div>

            <div class="col-lg-6 position-relative">
                <div class="hero-card">
                    <div class="floating-badge">
                        Designed for India-first SaaS
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div>
                            <div style="font-size:13px;color:#9ca3af;">Sandbox Checkout Preview</div>
                            <div style="font-size:16px;font-weight:600;color:#e5e7eb;">BadliCash Widget</div>
                        </div>
                        <span class="landing-badge">
                            SANDBOX PREVIEW
                        </span>
                    </div>
                    <div class="mt-2 p-3 rounded-4" style="background:rgba(15,23,42,0.85);border:1px solid rgba(55,65,81,0.9);">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="text-muted" style="font-size:11px;">LIVE DEMO</div>
                                <div style="font-size:14px;">Payment for <span class="fw-semibold">Acme Pro Plan</span></div>
                            </div>
                            <div class="text-end">
                                <div style="font-size:11px;color:#9ca3af;">Amount</div>
                                <div style="font-size:17px;font-weight:700;">₹4,999</div>
                            </div>
                        </div>
                        <div class="mt-2 mb-2 d-flex gap-2 flex-wrap">
                            <span class="badge rounded-pill text-bg-dark border border-secondary">
                                <i class="bi bi-credit-card me-1"></i> Cards
                            </span>
                            <span class="badge rounded-pill text-bg-dark border border-secondary">
                                <i class="bi bi-phone me-1"></i> UPI
                            </span>
                            <span class="badge rounded-pill text-bg-dark border border-secondary">
                                <i class="bi bi-bank me-1"></i> Netbanking
                            </span>
                        </div>
                        <button class="btn btn-primary w-100 mt-2" style="border-radius:999px;background:linear-gradient(135deg,#22c55e,#16a34a);border:none;">
                            Try a sandbox payment
                        </button>
                        <div class="mt-2" style="font-size:11px;color:#9ca3af;">
                            The sandbox mirrors live webhooks and flows, so your QA environment behaves like production from day one.
                        </div>
                    </div>

                    <div class="mt-3">
                        <div style="font-size:11px;color:#9ca3af;margin-bottom:4px;">Trusted by product teams building:</div>
                        <div class="hero-logos">
                            <span class="logo-pill"><i class="bi bi-bag-check me-1"></i> SaaS & Subscriptions</span>
                            <span class="logo-pill"><i class="bi bi-cash-coin me-1"></i> Marketplaces</span>
                            <span class="logo-pill"><i class="bi bi-motherboard me-1"></i> Fintech MVPs</span>
                            <span class="logo-pill"><i class="bi bi-building me-1"></i> Enterprise Portals</span>
                        </div>
                    </div>
                </div>

                <div class="testimonials-strip">
                    <div class="testimonial-avatar">
                        <span>A</span>
                    </div>
                    <div class="flex-grow-1">
                        <div class="testimonial-quote">
                            “We wired BadliCash into our sandbox in a weekend. The webhook observability and realistic test flows
                            beat every other provider we tried.”
                        </div>
                        <div style="font-size:11px;color:#9ca3af;">Arjun · Dummy CTO, NeoStack Labs</div>
                    </div>
                    <div style="font-size:12px;color:#6ee7b7;">
                        ★★★★★
                        <span class="text-muted ms-1">Dummy rating</span>
                    </div>
                </div>
            </div>
        </div>

        <div id="features" class="features-grid mt-5">
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="bi bi-columns-gap"></i>
                        </div>
                        <div class="fw-semibold mb-1">Merchant-first dashboards</div>
                        <div style="font-size:13px;color:#9ca3af;">
                            Rich dashboards for merchants and admins, including test/live toggles, settlement insights,
                            disputes, and detailed transaction timelines.
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon" style="background:rgba(16,185,129,.12);color:#6ee7b7;">
                            <i class="bi bi-webhook"></i>
                        </div>
                        <div class="fw-semibold mb-1">Webhooks that just work</div>
                        <div style="font-size:13px;color:#9ca3af;">
                            Payment, refund, subscription and payment-link webhooks with retries, signatures,
                            event toggles and test/live headers out of the box.
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon" style="background:rgba(244,114,182,.12);color:#f9a8d4;">
                            <i class="bi bi-code-slash"></i>
                        </div>
                        <div class="fw-semibold mb-1">Drop-in checkout widget</div>
                        <div style="font-size:13px;color:#9ca3af;">
                            A lightweight JS SDK that opens a beautiful hosted checkout in a modal, while your app receives
                            simple success/failure callbacks.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-5 text-center">
            <div class="landing-badge mb-2">Get started in minutes</div>
            <h2 style="font-size:22px;font-weight:700;color:#e5e7eb;">Open your BadliCash merchant account today</h2>
            <p style="font-size:13px;color:#9ca3af;max-width:480px;margin:6px auto 16px;">
                No calls. No PDFs. Fill a modern, guided signup and start integrating with BadliCash’s sandbox and live-ready APIs.
            </p>
            <a href="{{ route('signup') }}" class="btn btn-primary-hero">
                Sign up – free developer account
                <i class="bi bi-arrow-right-short"></i>
            </a>
        </div>
    </section>
</div>
@endsection


