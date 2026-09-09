<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="Ward Inventory System - Secure Hospital Inventory Management Login">
    <title>Ward Inventory System â€“ Sign In</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        *, *::before, *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        :root {
            --blue-deep:    #1a2ecc;
            --blue-mid:     #2541e8;
            --blue-light:   #3d5af1;
            --blue-bright:  #4f6ef7;
            --white:        #ffffff;
            --card-bg:      #ffffff;
            --text-dark:    #1a1f36;
            --text-muted:   #6b7280;
            --text-label:   #374151;
            --border:       #e5e7eb;
            --input-bg:     #f9fafb;
            --input-focus:  #3d5af1;
            --btn-hover:    #2541e8;
            --secure-green: #22c55e;
        }

        html, body {
            height: 100%;
            font-family: 'Inter', sans-serif;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, #1a2ecc 0%, #2541e8 40%, #3d5af1 70%, #5b7fff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }

        /* Decorative background blobs */
        body::before {
            content: '';
            position: absolute;
            top: -120px;
            left: -120px;
            width: 380px;
            height: 380px;
            background: rgba(255,255,255,0.07);
            border-radius: 50%;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -100px;
            right: -80px;
            width: 320px;
            height: 320px;
            background: rgba(255,255,255,0.06);
            border-radius: 50%;
            pointer-events: none;
        }

        .blob-mid {
            position: absolute;
            top: 50%;
            right: -60px;
            transform: translateY(-60%);
            width: 200px;
            height: 200px;
            background: rgba(255,255,255,0.05);
            border-radius: 50%;
            pointer-events: none;
        }

        /* Wrapper */
        .wrapper {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 32px;
            width: 100%;
            max-width: 480px;
            position: relative;
            z-index: 1;
        }

        /* Brand header */
        .brand {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }

        .brand-icon {
            width: 62px;
            height: 62px;
            background: rgba(255,255,255,0.18);
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            backdrop-filter: blur(8px);
            border: 1px solid rgba(255,255,255,0.25);
            box-shadow: 0 4px 24px rgba(0,0,0,0.15);
        }

        .brand-icon svg {
            width: 34px;
            height: 34px;
            color: #ffffff;
        }

        .brand-name {
            font-size: 1.75rem;
            font-weight: 800;
            color: #ffffff;
            letter-spacing: -0.5px;
            text-align: center;
        }

        .brand-tagline {
            font-size: 0.875rem;
            color: rgba(255,255,255,0.75);
            font-weight: 400;
            letter-spacing: 0.2px;
            text-align: center;
        }

        /* Card */
        .card {
            background: var(--card-bg);
            border-radius: 20px;
            padding: 40px 36px 32px;
            width: 100%;
            box-shadow:
                0 20px 60px rgba(26, 46, 204, 0.25),
                0 4px 16px rgba(0,0,0,0.1);
            animation: slideUp 0.5s cubic-bezier(0.22, 1, 0.36, 1) both;
        }

        @keyframes slideUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .card-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 6px;
        }

        .card-subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-bottom: 28px;
        }

        /* Form */
        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--text-label);
            margin-bottom: 7px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: #9ca3af;
            pointer-events: none;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }

        input[type="text"],
        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 14px 12px 42px;
            border: 1.5px solid var(--border);
            border-radius: 10px;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background: var(--input-bg);
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
            outline: none;
        }

        input[type="text"]::placeholder,
        input[type="email"]::placeholder,
        input[type="password"]::placeholder {
            color: #c0c5cf;
        }

        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="password"]:focus {
            border-color: var(--input-focus);
            box-shadow: 0 0 0 3px rgba(61, 90, 241, 0.12);
            background: #ffffff;
        }

        /* Password toggle */
        .toggle-password {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            display: flex;
            align-items: center;
            padding: 0;
            transition: color 0.2s;
        }

        .toggle-password:hover { color: var(--blue-light); }

        /* Remember me + Forgot */
        .form-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }

        .remember-label {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.875rem;
            color: var(--text-muted);
            cursor: pointer;
            user-select: none;
        }

        .remember-label input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--blue-light);
            cursor: pointer;
        }

        .forgot-link {
            font-size: 0.875rem;
            color: var(--blue-light);
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover { color: var(--blue-deep); }

        /* Sign In button */
        .btn-signin {
            width: 100%;
            padding: 13px;
            background: linear-gradient(135deg, #2541e8, #3d5af1);
            color: #ffffff;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            letter-spacing: 0.3px;
            transition: background 0.25s, transform 0.15s, box-shadow 0.25s;
            box-shadow: 0 4px 14px rgba(37, 65, 232, 0.4);
        }

        .btn-signin:hover {
            background: linear-gradient(135deg, #1a2ecc, #2541e8);
            box-shadow: 0 6px 20px rgba(37, 65, 232, 0.5);
            transform: translateY(-1px);
        }

        .btn-signin:active {
            transform: translateY(0);
            box-shadow: 0 2px 8px rgba(37, 65, 232, 0.3);
        }

        /* Secure footer */
        .secure-footer {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-top: 20px;
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        .secure-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--secure-green);
            flex-shrink: 0;
        }

        /* Error messages */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px 14px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            color: #dc2626;
        }

        .field-error {
            font-size: 0.78rem;
            color: #dc2626;
            margin-top: 5px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .card { padding: 32px 24px 28px; }
            .brand-name { font-size: 1.5rem; }
        }
    </style>
</head>
<body>

    <div class="blob-mid" aria-hidden="true"></div>

    <div class="wrapper">

        <!-- Brand Header -->
        <div class="brand">
            <div class="brand-icon" aria-hidden="true">
                <!-- Medical heartbeat / pulse icon -->
                <svg viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path d="M2 16h5l3-9 6 18 4-12 2 3h8"
                          stroke="currentColor"
                          stroke-width="2.4"
                          stroke-linecap="round"
                          stroke-linejoin="round"/>
                </svg>
            </div>
            <div>
                <h1 class="brand-name">Ward Inventory</h1>
                <p class="brand-tagline">Hospital Inventory Management System</p>
            </div>
        </div>

        <!-- Login Card -->
        <div class="card" role="main">
            <h2 class="card-title">Sign in to your account</h2>
            <p class="card-subtitle">Enter your credentials to access inventory</p>

            @if (session('error'))
                <div class="alert-error" role="alert">{{ session('error') }}</div>
            @endif

            <form method="POST" action="{{ url('/login') }}" id="login-form" novalidate>
                @csrf

                <!-- Username / Email -->
                <div class="form-group">
                    <label for="username">Username or Email</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                                <circle cx="12" cy="7" r="4"/>
                            </svg>
                        </span>
                        <input
                            type="text"
                            id="username"
                            name="username"
                            placeholder="admin or your email"
                            value="{{ old('username') }}"
                            autocomplete="username"
                            autofocus
                            required
                        >
                    </div>
                    @error('username')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label for="password">Password</label>
                    <div class="input-wrapper">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                            </svg>
                        </span>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            placeholder="â€¢â€¢â€¢â€¢â€¢â€¢â€¢â€¢"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="toggle-password" id="toggle-pwd" aria-label="Show password">
                            <svg id="eye-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="field-error">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Remember + Forgot -->
                <div class="form-row">
                    <label class="remember-label" for="remember">
                        <input type="checkbox" id="remember" name="remember">
                        Remember me
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link" id="forgot-password-link">Forgot password?</a>
                    @else
                        <a href="#" class="forgot-link" id="forgot-password-link">Forgot password?</a>
                    @endif
                </div>

                <!-- Sign In -->
                <button type="submit" class="btn-signin" id="sign-in-btn">Sign In</button>
            </form>

            <div class="secure-footer">
                <span class="secure-dot" aria-hidden="true"></span>
                <span>Secured connection Â· Ward Inventory System</span>
            </div>
        </div>

    </div>

    <script>
        const toggleBtn = document.getElementById('toggle-pwd');
        const passwordInput = document.getElementById('password');
        const eyeIcon = document.getElementById('eye-icon');

        const eyeOpen  = `<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>`;
        const eyeClosed = `<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>`;

        if (toggleBtn) {
            toggleBtn.addEventListener('click', function () {
                const isPassword = passwordInput.type === 'password';
                passwordInput.type = isPassword ? 'text' : 'password';
                eyeIcon.innerHTML = isPassword ? eyeClosed : eyeOpen;
                toggleBtn.setAttribute('aria-label', isPassword ? 'Hide password' : 'Show password');
            });
        }
    </script>

</body>
</html>
