<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login - {{ config('app.name') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
            min-height: 100vh;
        }

        .auth-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px 12px;
        }

        .auth-card {
            max-width: 480px;
            width: 100%;
            background: rgba(15, 23, 42, 0.9);
            border-radius: 22px;
            border: 1px solid rgba(55, 65, 81, 0.9);
            box-shadow: 0 24px 60px rgba(15, 23, 42, 0.9);
            padding: 40px 32px;
        }

        .auth-logo {
            text-align: center;
            margin-bottom: 24px;
        }

        .auth-logo img {
            height: 42px;
            width: auto;
        }

        .auth-title {
            font-size: 24px;
            font-weight: 700;
            color: #f9fafb;
            text-align: center;
            margin-bottom: 8px;
        }

        .auth-subtitle {
            font-size: 14px;
            color: #9ca3af;
            text-align: center;
            margin-bottom: 32px;
        }

        .form-label {
            font-size: 13px;
            font-weight: 600;
            color: #d1d5db;
            margin-bottom: 8px;
        }

        .form-control {
            background: #f8fafc;
            border: 1px solid rgba(55, 65, 81, 0.3);
            border-radius: 10px;
            color: #1f2937;
            font-size: 14px;
            padding: 12px 16px;
        }

        .form-control:focus {
            background: #ffffff;
            border-color: #6366f1;
            color: #1f2937;
            box-shadow: 0 0 0 1px rgba(99, 102, 241, 0.4);
        }

        .form-control::placeholder {
            color: #9ca3af;
        }

        .form-check-label {
            font-size: 16px;
            color: #d1d5db;
            font-weight: 500;
        }

        .form-check-input {
            background-color: #020617;
            border: 1px solid rgba(55, 65, 81, 0.9);
            width: 20px;
            height: 20px;
            margin-top: 0.25rem;
            cursor: pointer;
        }

        .form-check-input:checked {
            background-color: #6366f1;
            border-color: #6366f1;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--bc-primary) 0%, var(--bc-primary-dark) 100%);
            border: none;
            border-radius: 999px;
            padding: 12px 24px;
            font-weight: 600;
            box-shadow: 0 18px 45px rgba(79, 70, 229, 0.5);
        }

        .btn-primary:hover {
            transform: translateY(-1px);
            box-shadow: 0 22px 55px rgba(79, 70, 229, 0.7);
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fecaca;
            border-radius: 10px;
            padding: 12px 16px;
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 24px;
            border-top: 1px solid rgba(55, 65, 81, 0.9);
        }

        .auth-footer a {
            color: #818cf8;
            text-decoration: none;
            font-weight: 600;
        }

        .auth-footer a:hover {
            color: #6366f1;
            text-decoration: underline;
        }

        .test-credentials {
            font-size: 12px;
            color: #9ca3af;
            margin-top: 20px;
        }

        .test-credentials strong {
            color: #d1d5db;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <div class="auth-logo">
                <img src="{{ asset(logo_path()) }}" alt="{{ config('app.name') }}">
            </div>
           
            <p class="auth-subtitle">Sign in to your dashboard</p>

            @if($errors->any())
            <div class="alert alert-danger mb-4">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}">
                @csrf
                
                <div class="mb-3">
                    <label for="email" class="form-label">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="{{ old('email') }}" required autofocus>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>

                <div class="mb-3 form-check">
                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>

                <button type="submit" class="btn btn-primary w-100 mb-3">Sign In</button>
            </form>

            <div class="auth-footer">
                <div class="mb-3">
                    New to BadiliCash?
                    <a href="{{ route('signup') }}">Create a merchant account</a>
                </div>
                <div class="test-credentials">
                    <p class="mb-1"><strong>Test Credentials:</strong></p>
                    <p class="mb-0">Admin: admin@badlicash.test / Password123!</p>
                    <p class="mb-0">Merchant: merchant1@badlicash.test / Password123!</p>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

