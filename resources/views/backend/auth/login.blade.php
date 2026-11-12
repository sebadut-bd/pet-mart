<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin login · PetMart</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --navy: #0f172a; --navy-2: #1e293b; --emerald: #10b981; --emerald-600: #059669;
            --text: #0f172a; --muted: #64748b; --border: #d5dde8; --bg: #f1f5f9;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', system-ui, sans-serif; color: var(--text); background: var(--bg); min-height: 100vh; -webkit-font-smoothing: antialiased; }

        .wrap { min-height: 100vh; display: grid; grid-template-columns: 1.05fr 1fr; }

        .brand {
            position: relative; overflow: hidden; color: #cbd5e1; padding: 48px;
            background: radial-gradient(circle at 20% 20%, rgba(16,185,129,.22), transparent 45%),
                        radial-gradient(circle at 85% 80%, rgba(99,102,241,.18), transparent 40%),
                        linear-gradient(160deg, var(--navy) 0%, #111c33 100%);
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .brand::after {
            content: ''; position: absolute; inset: 0; pointer-events: none; opacity: .5;
            background-image: radial-gradient(rgba(148,163,184,.18) 1px, transparent 1px); background-size: 22px 22px;
            mask-image: linear-gradient(180deg, transparent, #000 40%, #000 60%, transparent);
        }
        .logo { display: flex; align-items: center; gap: 12px; position: relative; z-index: 1; }
        .logo-mark { width: 42px; height: 42px; border-radius: 12px; display: grid; place-items: center; color: #fff; font-size: 19px; background: linear-gradient(135deg, var(--emerald), var(--emerald-600)); box-shadow: 0 10px 24px -8px rgba(16,185,129,.7); }
        .logo strong { color: #fff; font-size: 18px; display: block; }
        .logo small { font-size: 11px; color: #64748b; text-transform: uppercase; letter-spacing: .1em; font-weight: 600; }
        .pitch { position: relative; z-index: 1; max-width: 440px; }
        .pitch h1 { color: #fff; font-size: 32px; line-height: 1.2; letter-spacing: -.02em; margin-bottom: 14px; }
        .pitch h1 span { color: var(--emerald); }
        .pitch p { font-size: 15px; line-height: 1.6; color: #94a3b8; }
        .points { list-style: none; margin-top: 28px; display: grid; gap: 12px; }
        .points li { display: flex; gap: 12px; align-items: center; font-size: 14px; color: #cbd5e1; }
        .points i { width: 32px; height: 32px; border-radius: 9px; display: grid; place-items: center; background: rgba(148,163,184,.1); font-size: 13px; }
        .points li:nth-child(1) i { color: #f59e0b; } .points li:nth-child(2) i { color: #f43f5e; } .points li:nth-child(3) i { color: #8b5cf6; }
        .brand footer { position: relative; z-index: 1; font-size: 12.5px; color: #475569; }

        .panel { display: flex; align-items: center; justify-content: center; padding: 40px 24px; background: #fff; }
        .form { width: 100%; max-width: 380px; }
        .form h2 { font-size: 24px; letter-spacing: -.01em; margin-bottom: 6px; }
        .form .sub { color: var(--muted); font-size: 14px; margin-bottom: 28px; }
        .alert { display: flex; gap: 10px; align-items: flex-start; background: #fff1f2; border: 1px solid #fecdd3; color: #be123c; border-radius: 12px; padding: 12px 14px; font-size: 13.5px; margin-bottom: 20px; }
        .field { margin-bottom: 18px; }
        label { display: block; font-size: 13px; font-weight: 600; margin-bottom: 6px; }
        .input { position: relative; }
        .input > i { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 14px; }
        input[type=email], input[type=password], input[type=text] {
            width: 100%; height: 46px; border: 1px solid var(--border); border-radius: 12px; padding: 0 44px 0 40px;
            font: inherit; font-size: 14.5px; color: var(--text); background: #fff; outline: none; transition: border-color .15s, box-shadow .15s;
        }
        input:focus { border-color: var(--emerald); box-shadow: 0 0 0 3px rgba(16,185,129,.18); }
        input[aria-invalid=true] { border-color: #e11d48; }
        .toggle { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); width: 34px; height: 34px; border: 0; border-radius: 8px; background: transparent; color: #94a3b8; cursor: pointer; }
        .toggle:hover { color: var(--text); background: var(--bg); }
        .row { display: flex; justify-content: space-between; align-items: center; margin: 4px 0 24px; font-size: 13.5px; }
        .remember { display: flex; align-items: center; gap: 8px; font-weight: 500; margin: 0; cursor: pointer; }
        .remember input { width: 16px; height: 16px; accent-color: var(--emerald); }
        .btn { width: 100%; height: 46px; border: 0; border-radius: 12px; font: inherit; font-size: 15px; font-weight: 600; color: #fff; cursor: pointer;
            background: linear-gradient(135deg, var(--emerald), var(--emerald-600)); box-shadow: 0 10px 22px -10px rgba(16,185,129,.8);
            display: flex; align-items: center; justify-content: center; gap: 8px; transition: transform .1s, box-shadow .15s, opacity .15s; }
        .btn:hover { box-shadow: 0 14px 26px -10px rgba(16,185,129,.9); }
        .btn:active { transform: translateY(1px); }
        .btn[disabled] { opacity: .7; cursor: wait; }
        .note { margin-top: 22px; font-size: 12.5px; color: var(--muted); text-align: center; }
        .note i { color: var(--emerald); margin-right: 4px; }

        @media (max-width: 900px) {
            .wrap { grid-template-columns: 1fr; }
            .brand { padding: 28px 24px; }
            .pitch, .brand footer { display: none; }
            .panel { align-items: flex-start; padding-top: 32px; }
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body>
<div class="wrap">
    <section class="brand" aria-hidden="true">
        <div class="logo">
            <span class="logo-mark"><i class="fa-solid fa-paw"></i></span>
            <span><strong>PetMart</strong><small>Admin panel</small></span>
        </div>
        <div class="pitch">
            <h1>Keep the marketplace <span>safe and trusted.</span></h1>
            <p>Review new listings, handle reports and support members across Bangladesh.</p>
            <ul class="points">
                <li><i class="fa-solid fa-clipboard-check"></i> Approve or reject listings before they go live</li>
                <li><i class="fa-solid fa-flag"></i> Act on reports from the community</li>
                <li><i class="fa-solid fa-user-shield"></i> Verify trusted sellers</li>
            </ul>
        </div>
        <footer>&copy; {{ date('Y') }} PetMart</footer>
    </section>

    <main class="panel">
        <form class="form" action="{{ route('login') }}" method="POST" novalidate onsubmit="this.querySelector('.btn').setAttribute('disabled', 'disabled')">
            @csrf
            <h2>Welcome back</h2>
            <p class="sub">Sign in with your admin account.</p>

            @if ($errors->any() || session('error'))
                <div class="alert" role="alert">
                    <i class="fa-solid fa-circle-exclamation" style="margin-top:2px"></i>
                    <span>{{ $errors->first() ?: session('error') }}</span>
                </div>
            @endif

            <div class="field">
                <label for="email">Email</label>
                <div class="input">
                    <i class="fa-regular fa-envelope"></i>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="you@example.com"
                           autocomplete="username" required autofocus @if($errors->has('email')) aria-invalid="true" @endif>
                </div>
            </div>

            <div class="field">
                <label for="password">Password</label>
                <div class="input">
                    <i class="fa-solid fa-lock"></i>
                    <input type="password" id="password" name="password" placeholder="Your password" autocomplete="current-password" required>
                    <button type="button" class="toggle" id="toggle" aria-label="Show password" aria-pressed="false">
                        <i class="fa-regular fa-eye"></i>
                    </button>
                </div>
            </div>

            <div class="row">
                <label class="remember">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    Keep me signed in
                </label>
            </div>

            <button type="submit" class="btn"><i class="fa-solid fa-arrow-right-to-bracket"></i> Sign in</button>
            <p class="note"><i class="fa-solid fa-shield-halved"></i> Admins only. Attempts are rate limited.</p>
        </form>
    </main>
</div>

<script>
    (function () {
        var btn = document.getElementById('toggle');
        var input = document.getElementById('password');
        btn.addEventListener('click', function () {
            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            btn.setAttribute('aria-pressed', show ? 'true' : 'false');
            btn.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            btn.innerHTML = show ? '<i class="fa-regular fa-eye-slash"></i>' : '<i class="fa-regular fa-eye"></i>';
        });
    })();
</script>
</body>
</html>
