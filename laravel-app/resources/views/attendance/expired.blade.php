<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>QR Code Expired</title>
<style>
    :root {
        --pink: #e8174a;
        --ink: #1a1a2e;
        --cream: #fdf6f0;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0;
        font-family: 'Segoe UI', Arial, sans-serif;
        background: var(--cream);
        min-height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 24px;
    }
    .card {
        background: #fff;
        width: 100%;
        max-width: 420px;
        border-radius: 18px;
        box-shadow: 0 10px 30px rgba(26, 26, 46, 0.15);
        overflow: hidden;
        text-align: center;
    }
    .card-body {
        padding: 40px 28px 36px;
    }
    .icon-circle {
        width: 64px;
        height: 64px;
        margin: 0 auto 20px;
        border-radius: 50%;
        background: #fdeaea;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .icon-circle svg {
        width: 30px;
        height: 30px;
        stroke: var(--pink);
    }
    .eyebrow {
        color: var(--pink);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        margin: 0 0 8px;
    }
    h1 {
        color: var(--ink);
        font-size: 20px;
        margin: 0 0 10px;
    }
    p.desc {
        color: #6b6b7a;
        font-size: 14px;
        line-height: 1.6;
        margin: 0;
    }
    .hint {
        font-size: 12px;
        color: #8a8a99;
        margin-top: 24px;
        padding-top: 20px;
        border-top: 1px solid #f1e6e6;
    }
</style>
</head>
<body>

<div class="card">
    <div class="card-body">
        <div class="icon-circle">
            <svg viewBox="0 0 24 24" fill="none" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <circle cx="12" cy="12" r="10"></circle>
                <line x1="12" y1="8" x2="12" y2="12"></line>
                <line x1="12" y1="16" x2="12.01" y2="16"></line>
            </svg>
        </div>

        <p class="eyebrow">QR Code Expired</p>
        <h1>This QR code is no longer valid</h1>
        <p class="desc">
            A newer check-in QR code has been issued. Please scan the current QR code posted at your workplace to time in or time out.
        </p>
    </div>
</div>

</body>
</html>