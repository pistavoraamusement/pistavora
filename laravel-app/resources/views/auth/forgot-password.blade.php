<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot password | Pistavora Amusement Park</title>
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
                    <h2>Forgot your password?</h2>
                    <p>No worries — enter your registered email and we'll send you a code to reset it.</p>
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
                <span class="ticket-label coral">Forgot Password</span>
                <h1>Reset your password</h1>
                <p class="auth-sub">Enter the email you registered with. We'll email you a 6-digit code.</p>

                @if (session('error'))
                    <div class="auth-error">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="auth-error">{{ $errors->first() }}</div>
                @endif

                <form method="POST" action="{{ url('/forgot-password') }}">
                    @csrf

                    <div class="field">
                        <label for="email">Registered email</label>
                        <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                            autocomplete="email">
                    </div>

                    <button type="submit" class="btn btn-primary">Send reset code</button>
                </form>

                <p class="auth-signup">Remembered it? <a href="{{ url('/login') }}">Back to login</a></p>
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