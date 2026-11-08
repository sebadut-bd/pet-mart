<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login | PetMart</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #eef2f7;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .wrapper {
            width: 100%;
            max-width: 420px;
        }

        /* ── Logo ── */
        .logo-area {
            text-align: center;
            margin-bottom: 28px;
        }
        .logo-icon {
            width: 64px;
            height: 64px;
            background: #1a56db;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 12px;
            box-shadow: 0 4px 14px rgba(26, 86, 219, 0.35);
        }
        .logo-icon svg { width: 36px; height: 36px; fill: #fff; }
        .logo-area h1 {
            font-size: 22px;
            font-weight: 700;
            color: #1a56db;
            letter-spacing: -0.3px;
        }
        .logo-area span {
            font-size: 12px;
            color: #6b7280;
            font-weight: 500;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        /* ── Card ── */
        .card {
            background: #fff;
            border-radius: 20px;
            padding: 36px 32px 28px;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
        }
        .card-title {
            font-size: 20px;
            font-weight: 700;
            color: #111827;
            margin-bottom: 4px;
        }
        .card-subtitle {
            font-size: 13.5px;
            color: #6b7280;
            margin-bottom: 28px;
        }

        /* ── Alert ── */
        .alert-error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 10px;
            padding: 12px 14px;
            margin-bottom: 20px;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }
        .alert-error svg { width: 18px; height: 18px; fill: #ef4444; flex-shrink: 0; margin-top: 1px; }
        .alert-error p { font-size: 13.5px; color: #b91c1c; font-weight: 500; }

        /* ── Form ── */
        .form-group { margin-bottom: 18px; }
        label {
            display: block;
            font-size: 13.5px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
        }
        label span { color: #ef4444; }

        input[type="email"],
        input[type="password"],
        input[type="text"] {
            width: 100%;
            padding: 13px 16px;
            border: 1.5px solid #d1d5db;
            border-radius: 12px;
            font-size: 14px;
            font-family: inherit;
            color: #111827;
            background: #f9fafb;
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }
        input:focus {
            border-color: #1a56db;
            background: #fff;
            box-shadow: 0 0 0 3px rgba(26, 86, 219, 0.12);
        }
        input::placeholder { color: #9ca3af; }

        .password-wrap { position: relative; }
        .password-wrap input { padding-right: 48px; }
        .toggle-pass {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            padding: 0;
            color: #6b7280;
            display: flex;
            align-items: center;
        }
        .toggle-pass:hover { color: #1a56db; }
        .toggle-pass svg { width: 20px; height: 20px; }

        /* ── Remember + Forgot ── */
        .row-flex {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 24px;
        }
        .remember {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }
        .remember input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: #1a56db;
            cursor: pointer;
            border-radius: 4px;
        }
        .remember span { font-size: 13.5px; color: #374151; }
        .forgot-link {
            font-size: 13.5px;
            color: #1a56db;
            text-decoration: none;
            font-weight: 500;
        }
        .forgot-link:hover { text-decoration: underline; }

        /* ── Button ── */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: #1a56db;
            color: #fff;
            border: none;
            border-radius: 50px;
            font-size: 15px;
            font-weight: 600;
            font-family: inherit;
            cursor: pointer;
            letter-spacing: 0.2px;
            transition: background .2s, transform .1s, box-shadow .2s;
            box-shadow: 0 4px 14px rgba(26, 86, 219, 0.35);
        }
        .btn-login:hover {
            background: #1647b8;
            box-shadow: 0 6px 18px rgba(26, 86, 219, 0.45);
        }
        .btn-login:active { transform: scale(0.99); }

        /* ── Footer ── */
        footer {
            margin-top: 28px;
            text-align: center;
            font-size: 12.5px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

<div class="wrapper">

    <!-- Logo -->
    <div class="logo-area">
        <div class="logo-icon">
            <!-- Paw print icon -->
            <svg viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg">
                <ellipse cx="16" cy="14" rx="6" ry="8"/>
                <ellipse cx="30" cy="10" rx="5" ry="7"/>
                <ellipse cx="44" cy="14" rx="6" ry="8"/>
                <ellipse cx="52" cy="28" rx="5" ry="7"/>
                <path d="M32 26c-9 0-18 7-15 18 1.5 5.5 6 9 10 11 2 1 5 1.5 5 1.5s3-.5 5-1.5c4-2 8.5-5.5 10-11 3-11-6-18-15-18z"/>
            </svg>
        </div>
        <h1>PetMart</h1>
        <span>Admin Dashboard</span>
    </div>

    <!-- Card -->
    <div class="card">
        <p class="card-title">Welcome back!</p>
        <p class="card-subtitle">Sign in to your admin account to continue.</p>

        @if ($errors->any())
        <div class="alert-error">
            <svg viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <p>{{ $errors->first() }}</p>
        </div>
        @endif

        @if (session('error'))
        <div class="alert-error">
            <svg viewBox="0 0 20 20"><path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
            <p>{{ session('error') }}</p>
        </div>
        @endif

        <form action="{{ route('login') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="email">Email Address <span>*</span></label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    value="{{ old('email') }}"
                    placeholder="admin@petmart.com"
                    autocomplete="email"
                    autofocus
                >
            </div>

            <div class="form-group">
                <label for="password">Password <span>*</span></label>
                <div class="password-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        placeholder="Enter your password"
                        autocomplete="current-password"
                    >
                    <button type="button" class="toggle-pass" onclick="togglePassword()" id="toggleBtn">
                        <!-- Eye icon (show) -->
                        <svg id="eyeShow" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                            <circle cx="12" cy="12" r="3"/>
                        </svg>
                        <!-- Eye-off icon (hide) - initially hidden -->
                        <svg id="eyeHide" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="display:none">
                            <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                            <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                            <line x1="1" y1="1" x2="23" y2="23"/>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="row-flex">
                <label class="remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <span>Remember me</span>
                </label>
                <a href="#" class="forgot-link">Forgot Password?</a>
            </div>

            <button type="submit" class="btn-login">Sign In</button>
        </form>
    </div>

    <footer>
        &copy; {{ date('Y') }} PetMart. All rights reserved.
    </footer>

</div>

<script>
function togglePassword() {
    const input   = document.getElementById('password');
    const eyeShow = document.getElementById('eyeShow');
    const eyeHide = document.getElementById('eyeHide');

    if (input.type === 'password') {
        input.type    = 'text';
        eyeShow.style.display = 'none';
        eyeHide.style.display = 'block';
    } else {
        input.type    = 'password';
        eyeShow.style.display = 'block';
        eyeHide.style.display = 'none';
    }
}
</script>

</body>
</html>
