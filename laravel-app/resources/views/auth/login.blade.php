<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign In | Pistavora Amusement Park</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <link rel="stylesheet" href="{{ asset('css/auth.css') }}">
    <style>
        .email-login { margin-top: 12px; }
        .email-login input[type="email"] {
            width: 100%; box-sizing: border-box; padding: 14px 16px; font-size: 15px;
            border: 1px solid #e5ddd0; border-radius: 12px; background: #fffdf9; outline: none;
        }
        .email-login input[type="email"]:focus { border-color: #111; }
        .email-login .btn-email {
            width: 100%; margin-top: 10px; padding: 14px 16px; font-size: 15px; font-weight: 600;
            color: #fff; background: #000; border: 0; border-radius: 12px; cursor: pointer;
        }
        .email-login .btn-email:hover { background: #222; }
    </style>
</head>

<body>

    <div class="auth-split">

        <div class="auth-visual">
            <div class="bg-slide" data-bg="{{ asset('images/vikings.jpg') }}"></div>
            <div class="bg-slide" data-bg="{{ asset('images/roller-fever.jpg') }}"></div>
            <div class="auth-visual-content">
                <a href="{{ route('home') }}" class="logo">Pistavora<span class="dot">•</span>Amusement</a>
                <div class="auth-visual-copy">
                    <span class="ticket-label on-dark">Lipa Branch</span>
                    <h2>Three worlds of thrill, one login away.</h2>
                    <p>Manage bookings, inventory, and the front desk for Skataya, DinoLakbay, and the
                        PiyestaRides — all in one place.</p>
                    <div class="auth-visual-stamps">
                        <span class="stamp">Skataya</span>
                        <span class="stamp">DinoLakbay</span>
                        <span class="stamp">PiyestaRides</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="auth-form-side" style="position:relative;">
            <a href="{{ route('home') }}" class="auth-back-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Back
            </a>
            <div class="auth-card">
                <span class="ticket-label coral">You may Access</span>
                <h1>Welcome back</h1>
                <p class="auth-sub">Sign in to continue to your dashboard.</p>

                @if (session('error'))
                    <div class="auth-error">{{ session('error') }}</div>
                @endif
                @if (session('status'))
                    <div class="auth-error" style="background:#e6f7f1;color:#0c8b80;border-color:#0c8b8033;">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ url('/login') }}">
                    @csrf

                    <div class="field">
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" value="{{ old('username') }}" required
                            autofocus autocomplete="username">
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <input id="password" type="password" name="password" required
                            autocomplete="current-password">
                        <a href="{{ url('/forgot-password') }}" style="display:inline-block;margin-top:6px;font-size:11.5px;color:var(--pink-deep,#B82850);font-weight:600;">Forgot password?</a>
                    </div>

                    <button type="submit" class="btn btn-primary">Log In</button>
                </form>

                <div class="auth-divider">or</div>

                <div class="social-row">
                    <a href="{{ url('/auth/google/redirect') }}" class="btn-social google">
                        <svg viewBox="0 0 24 24">
                            <path fill="#4285F4"
                                d="M23.52 12.27c0-.85-.08-1.66-.22-2.45H12v4.63h6.47c-.28 1.48-1.13 2.74-2.4 3.58v2.98h3.88c2.27-2.09 3.57-5.17 3.57-8.74z" />
                            <path fill="#34A853"
                                d="M12 24c3.24 0 5.96-1.07 7.95-2.9l-3.88-2.98c-1.08.72-2.45 1.15-4.07 1.15-3.13 0-5.78-2.11-6.73-4.96H1.27v3.07C3.25 21.3 7.31 24 12 24z" />
                            <path fill="#FBBC05"
                                d="M5.27 14.31A7.2 7.2 0 0 1 4.9 12c0-.8.14-1.58.37-2.31V6.62H1.27A11.97 11.97 0 0 0 0 12c0 1.93.46 3.76 1.27 5.38l4-3.07z" />
                            <path fill="#EA4335"
                                d="M12 4.75c1.77 0 3.35.61 4.6 1.8l3.44-3.44C17.95 1.19 15.24 0 12 0 7.31 0 3.25 2.7 1.27 6.62l4 3.07C6.22 6.86 8.87 4.75 12 4.75z" />
                        </svg>
                        Continue with Google
                    </a>
                </div>

                <form method="POST" action="{{ url('/login/email') }}" class="email-login">
                    @csrf
                    <input type="email" name="email" value="{{ old('email') }}" placeholder="Enter your email"
                        required autocomplete="email" aria-label="Email address">
                    <button type="submit" class="btn-email">Continue with email</button>
                </form>

                <p class="auth-legal">
                    By signing in, you agree to our
                    <a href="#" data-open="termsModal">Terms &amp; Conditions</a> and
                    <a href="#" data-open="privacyModal">Privacy Notice</a>.
                </p>

                <p class="auth-signup">Don't have an account? <a href="{{ url('/register') }}">Create one</a></p>
            </div>
        </div>
    </div>

    @include('partials.legal-modals')

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.bg-slide[data-bg]').forEach(function (el) {
                el.style.backgroundImage = "url('" + el.dataset.bg + "')";
            });
        });
    </script>
</body>

</html>