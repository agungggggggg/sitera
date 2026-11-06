<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#086288">
    <meta name="description" content="Panduan bantuan akses akun SITERA.">
    <title>Lupa Password — SITERA</title>
    <link rel="icon" type="image/webp" href="{{ asset('sitera/sitera-logo.webp') }}">
    <link rel="stylesheet" href="{{ asset('sitera/theme.css') }}">
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;min-height:100svh;display:grid;place-items:center;padding:24px;background:radial-gradient(ellipse at 12% 10%,#e4f2f6 0,transparent 38%),#f5f9fb;color:#17323d;font-family:'DM Sans',Arial,sans-serif}
        .password-help-card{width:min(100%,500px);padding:36px;border:1px solid #dce8ed;border-radius:18px;background:#fff;box-shadow:0 18px 54px rgba(8,62,83,.09)}
        .password-help-brand{display:inline-flex;align-items:center;gap:11px;margin-bottom:34px;color:#17323d;text-decoration:none}
        .password-help-brand .brand-logo{display:grid;width:46px;height:46px;place-items:center}
        .password-help-brand img{display:block;width:100%;height:100%;object-fit:contain}
        .password-help-brand strong{display:block;font:800 16px Manrope,Arial,sans-serif;letter-spacing:.04em}
        .password-help-brand small{display:block;margin-top:3px;color:#728892;font-size:9px}
        .password-help-icon{display:grid;width:43px;height:43px;place-items:center;margin-bottom:17px;border:1px solid #cfe2ea;border-radius:12px;background:#f2f8fb;color:#086288;font-size:20px}
        .password-help-kicker{color:#086288;font-size:9px;font-weight:800;letter-spacing:.12em}
        .password-help-card h1{margin:8px 0 9px;color:#173d4d;font:800 clamp(24px,6vw,30px)/1.2 Manrope,Arial,sans-serif;letter-spacing:-.035em}
        .password-help-intro{margin:0;color:#71858e;font-size:13px;line-height:1.7}
        .password-help-note{margin:21px 0;padding:14px 15px;border:1px solid #dceaf0;border-radius:10px;background:#f6fafc;color:#5e7782;font-size:12px;line-height:1.7}
        .password-help-note strong{display:block;margin-bottom:4px;color:#294d5b;font-size:12px}
        .password-help-contact{margin:0 0 22px;color:#526f7b;font-size:12px;line-height:1.6}
        .password-help-actions{display:flex;flex-wrap:wrap;gap:10px}
        .password-help-actions a{display:inline-flex;min-height:46px;align-items:center;justify-content:center;padding:0 18px;border:1px solid #cfe2ea;border-radius:9px;background:#086288;color:#fff;font-size:12px;font-weight:750;text-decoration:none}
        .password-help-actions a:hover{background:#064d69}
        .password-help-footer{margin-top:27px;padding-top:16px;border-top:1px solid #edf2f4;color:#91a0a6;font-size:9px;line-height:1.6}
        @media(max-width:480px){body{padding:15px}.password-help-card{padding:25px 21px;border-radius:14px}.password-help-brand{margin-bottom:27px}.password-help-note{margin:17px 0;padding:12px}.password-help-actions a{width:100%;min-height:48px}}
    </style>
</head>
<body class="password-help-page">
    <main class="password-help-card" aria-labelledby="password-help-title">
        <a class="password-help-brand" href="{{ route('login') }}" aria-label="SITERA — kembali ke login">
            <span class="brand-logo"><img src="{{ asset('sitera/sitera-logo.webp') }}" alt="Logo SITERA"></span>
            <span><strong>SITERA</strong><small>Sistem Informasi Irigasi Tersier</small></span>
        </a>
        <div class="password-help-icon" aria-hidden="true">⌑</div>
        <span class="password-help-kicker">BANTUAN AKSES AKUN</span>
        <h1 id="password-help-title">Lupa password?</h1>
        <p class="password-help-intro">Untuk menjaga keamanan akun, reset password dilakukan oleh Super Admin SITERA.</p>
        <div class="password-help-note"><strong>Langkah selanjutnya</strong>Silakan hubungi Super Admin SITERA untuk meminta password Anda dikembalikan ke password default.</div>
        <p class="password-help-contact"><strong>Hubungi Super Admin</strong> instansi Anda untuk bantuan akses.</p>
        <div class="password-help-actions"><a href="{{ route('login') }}">← Kembali ke login</a></div>
        <footer class="password-help-footer">SITERA · Kementerian Pertanian · Portal operasional internal</footer>
    </main>
</body>
</html>
