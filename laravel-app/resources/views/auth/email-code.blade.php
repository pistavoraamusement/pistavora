<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enter Code | Pistavora Amusement Park</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/landing.css') }}">
    <style>
        .email-center { display: flex !important; width: 100%; min-height: 100vh; align-items: center; justify-content: center; }
        .email-center .auth-form-side { flex: 1 1 100% !important; width: 100% !important; max-width: none !important;
            margin: 0 !important; min-height: 100vh; display: flex !important; align-items: center; justify-content: center; }
    </style>
</head>

<body>
    <div class="auth-split email-center">
        <div class="auth-form-side" style="position:relative;">
            <a href="{{ url('/login') }}" class="auth-back-btn">Back</a>
            <div class="auth-card">
                <span class="ticket-label coral">Check your email</span>
                <h1>Enter your code</h1>
                <p class="auth-sub">We sent a 6-digit code to <strong>{{ session('emaillogin_email') }}</strong>.</p>

                @if (session('error'))
                    <div class="auth-error">{{ session('error') }}</div>
                @endif
                @if (session('status'))
                    <div class="auth-error" style="background:#e6f7f1;color:#0c8b80;border-color:#0c8b8033;">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ url('/login/email/verify') }}">
                    @csrf
                    <div class="field">
                        <label for="code">Verification code</label>
                        <input id="code" type="text" name="code" inputmode="numeric" maxlength="6"
                            pattern="[0-9]{6}" required autofocus autocomplete="one-time-code">
                    </div>
                    <button type="submit" class="btn btn-primary">Verify &amp; Log In</button>
                </form>

                <form method="POST" action="{{ url('/login/email/resend') }}" style="margin-top:12px;">
                    @csrf
                    <button type="submit" style="background:none;border:0;color:#2563eb;cursor:pointer;font-weight:600;">Resend code</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>