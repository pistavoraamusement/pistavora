<!DOCTYPE html>
<html lang="en">
<head>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    <title>@yield('title', 'Waiver') · Pistavora Amusement Park</title>
    <link rel="stylesheet" href="{{ asset('css/user-app-theme.css') }}">
    <link rel="stylesheet" href="{{ asset('css/user-landing-theme.css') }}?v={{ filemtime(public_path('css/user-landing-theme.css')) }}">

    <style>
        body{
            margin:0;
            min-height:100vh;
            display:flex;
            align-items:flex-start;
            justify-content:center;
            background:var(--bg,#F8FAFC);
            font-family:inherit;
            padding:32px 16px;
        }
        .waiver-shell{
            width:100%;
            max-width:640px;
        }
        .waiver-brand{
            text-align:center;
            margin-bottom:20px;
        }
        .waiver-brand img{
            height:48px;
            margin-bottom:8px;
        }
        .waiver-brand h1{
            font-size:1.1rem;
            margin:0;
            color:var(--ink);
            letter-spacing:.02em;
        }
        .waiver-brand p{
            margin:2px 0 0;
            font-size:.85rem;
            color:var(--ink-soft);
        }
        .waiver-shell .u-card,
        .waiver-shell .waiver-box{
            box-shadow:0 8px 28px rgba(20,10,25,.06);
        }
        .waiver-shell form{
            margin-top:16px;
        }
    </style>

    @stack('styles')
</head>
<body>
    <div class="waiver-shell">
        <div class="waiver-brand">
            <h1>Pistavora Amusement Park</h1>
            <p>Please review and accept before continuing</p>
        </div>

        @if (session('success'))
            <div class="u-alert success">{{ session('success') }}</div>
        @endif
        @if (session('error'))
            <div class="u-alert error">{{ session('error') }}</div>
        @endif
        @if ($errors->any())
            <div class="u-alert error">{{ $errors->first() }}</div>
        @endif

        @yield('content')
    </div>

    @stack('scripts')
</body>
</html>