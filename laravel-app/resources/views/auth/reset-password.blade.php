<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset password | Pistavora Amusement Park</title>
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
                    <span class="ticket-label on-dark">Account recovery</span>
                    <h2>Almost there.</h2>
                    <p>Enter the code we emailed you, then choose a new password.</p>
                </div>
            </div>
        </div>

        <div class="auth-form-side" style="position:relative;">
            <a href="{{ url('/forgot-password') }}" class="auth-back-btn">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"
                    stroke-linejoin="round">
                    <path d="M19 12H5M12 19l-7-7 7-7" />
                </svg>
                Back
            </a>
            <div class="auth-card">
                <span class="ticket-label coral">Reset Password</span>
                <h1>Enter your code</h1>
                <p class="auth-sub">Check your email for the 6-digit code. It expires in 10 minutes.</p>

                @if (session('error'))
                    <div class="auth-error">{{ session('error') }}</div>
                @endif
                @if (session('status'))
                    <div class="auth-error" style="background:#e6f7f1;color:#0c8b80;border-color:#0c8b8033;">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="auth-error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ url('/reset-password') }}">
                    @csrf

                    <div class="field">
                        <label for="code">Verification code</label>
                        <input id="code" type="text" name="code" inputmode="numeric" pattern="[0-9]*" maxlength="6"
                            autocomplete="one-time-code" required autofocus
                            style="letter-spacing:6px;font-family:'Space Mono',monospace;font-size:20px;text-align:center;">
                    </div>

                    <div class="field">
                        <label for="new_password">New password</label>
                        <input id="new_password" type="password" name="new_password" minlength="8" required
                            autocomplete="new-password">
                    </div>

                    <div class="field">
                        <label for="new_password_confirmation">Confirm new password</label>
                        <input id="new_password_confirmation" type="password" name="new_password_confirmation"
                            minlength="8" required autocomplete="new-password">
                    </div>

                    <button type="submit" class="btn btn-primary">Reset password</button>
                </form>

                <form method="POST" action="{{ url('/reset-password/resend') }}" style="margin-top:14px;">
                    @csrf
                    <button type="submit" class="btn-social" style="width:100%;justify-content:center;background:none;border:1px solid var(--line,#eee);cursor:pointer;">
                        Resend code
                    </button>
                </form>

                <p class="auth-signup"><a href="{{ url('/login') }}">Cancel and go back to login</a></p>
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