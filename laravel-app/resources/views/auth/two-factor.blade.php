<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify your identity | Pistavora Amusement Park</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link
        href="https://fonts.googleapis.com/css2?family=Baloo+2:wght@500;600;700;800&family=Inter:wght@400;500;600;700&family=Space+Mono:wght@400;700&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
</head>

<body>

    <div class="auth-split">

        <div class="auth-visual">
            <div class="bg-slide" data-bg="{{ asset('images/vikings.jpg') }}"></div>
            <div class="bg-slide" data-bg="{{ asset('images/roller-fever.jpg') }}"></div>
            <div class="auth-visual-content">
                <a href="{{ route('home') }}" class="logo">Pistavora<span class="dot">•</span>Amusement</a>
                <div class="auth-visual-copy">
                    <span class="ticket-label on-dark">Security check</span>
                    <h2>One quick step before you're in.</h2>
                    <p>We've emailed a 6-digit code to keep your account safe. Enter it here to finish signing in.</p>
                </div>
            </div>
        </div>

        <div class="auth-form-side" style="position:relative;">
            <a href="{{ url('/login') }}" class="auth-back-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Back to login
            </a>
            <div class="auth-card">
                <span class="ticket-label coral">Two-Factor Authentication</span>
                <h1>Enter your code</h1>
                <p class="auth-sub">We sent a 6-digit verification code to your registered email. It expires in 10 minutes.</p>

                @if (session('error'))
                    <div class="auth-error">{{ session('error') }}</div>
                @endif

                @if (session('status'))
                    <div class="auth-error" style="background:#e6f7f1;color:#0c8b80;border-color:#0c8b8033;">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ url('/two-factor') }}">
                    @csrf

                    <div class="field">
                        <label for="code">Verification code</label>
                        <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6"
                            autocomplete="one-time-code" required autofocus
                            style="letter-spacing:6px;font-family:'Space Mono',monospace;font-size:20px;text-align:center;">
                    </div>

                    <button type="submit" class="btn btn-primary">Verify &amp; Continue</button>
                </form>

                <form method="POST" action="{{ url('/two-factor/resend') }}" style="margin-top:14px;">
                    @csrf
                    <button type="submit" class="btn-social" style="width:100%;justify-content:center;background:none;border:1px solid var(--line,#eee);cursor:pointer;">
                        Resend code
                    </button>
                </form>

                <p class="auth-signup">Wrong account? <a href="{{ url('/logout') }}">Log out</a></p>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('.bg-slide[data-bg]').forEach(function (el) {
                el.style.backgroundImage = "url('" + el.dataset.bg + "')";
            });
        });
    </script>
</body>

</html>