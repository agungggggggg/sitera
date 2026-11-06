<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#086288">
    <meta name="description" content="Masuk ke portal operasional SITERA.">
    <title>Login — SITERA</title>
    <link rel="icon" type="image/webp" href="{{ asset('sitera/sitera-logo.webp') }}">
    <link rel="stylesheet" href="{{ asset('sitera/app.css') }}">
    <link rel="stylesheet" href="{{ asset('sitera/theme.css') }}">
    <style>
        .login-error{margin:0 0 14px;padding:11px 13px;border:1px solid #f0c7c2;border-radius:8px;background:#fff5f4;color:#a33128;font-size:12px;line-height:1.55}
        .login-error ul{margin:4px 0 0;padding-left:18px}
        .login-success{margin:0 0 14px;padding:11px 13px;border:1px solid #bde3d4;border-radius:8px;background:#effaf5;color:#12664e;font-size:12px;line-height:1.55}
        .password-toggle{cursor:pointer}
        .login-card .login-intro{margin-bottom:24px}
        .login-legal{margin-top:20px}
        @media(max-width:760px){.login-screen{grid-template-columns:1fr}.login-visual{display:none}.login-panel{min-height:100vh;padding:38px 22px 78px}.mobile-brand{display:flex!important}.panel-footer{left:22px;right:22px;bottom:18px}}
    </style>
</head>
<body>
<section class="login-screen">
    <div class="login-visual">
        <div class="login-brand">
            <div class="brand-emblem brand-logo"><img src="{{ asset('sitera/sitera-logo.webp') }}" alt="Logo Irigasi Pertanian"></div>
            <div><span class="brand-kicker">KEMENTERIAN PERTANIAN</span><strong>SITERA</strong><span class="brand-full">Sistem Informasi Irigasi Tersier</span></div>
        </div>
        <div class="visual-copy">
            <span class="eyebrow light-eyebrow">SATU PINTU PELAPORAN</span>
            <h1>Alur laporan lebih tertata.<br><em>Informasi lebih terlacak.</em></h1>
            <p>Ruang kerja internal untuk mengirim laporan kegiatan irigasi, memeriksa dokumen, dan memantau status pelaporan dari daerah.</p>
        </div>
        <div class="water-art" aria-hidden="true">
            <svg viewBox="0 0 670 320" preserveAspectRatio="xMidYMid meet">
                <path d="M-15 220 C95 180 130 240 224 208 S380 166 460 204 590 235 690 190" fill="none" stroke="#c0e5ef" stroke-width="2" opacity=".7"/>
                <path d="M-20 244 C75 208 150 273 244 235 S390 197 474 234 598 263 700 220" fill="none" stroke="#a9d5b2" stroke-width="2" opacity=".55"/>
                <path d="M-20 270 C75 235 150 298 244 261 S390 224 474 260 598 289 700 246" fill="none" stroke="#a9d5b2" stroke-width="2" opacity=".42"/>
                <path d="M270 320 366 116 458 320M316 218h96M294 266h144" fill="none" stroke="#d5edf3" stroke-width="1.5" opacity=".5"/>
                <path d="M345 320V136M345 136l-44 22M345 136l44 22" fill="none" stroke="#d1ead5" stroke-width="2" opacity=".55"/>
                <circle cx="345" cy="136" r="8" fill="#d1ead5" opacity=".8"/>
                <g fill="#d1ead5" opacity=".8"><circle cx="470" cy="84" r="3"/><circle cx="505" cy="104" r="2"/><circle cx="546" cy="60" r="2.5"/><circle cx="180" cy="110" r="2"/><circle cx="216" cy="78" r="3"/></g>
            </svg>
        </div>
        <div class="visual-bottom"><span>TERINTEGRASI DALAM SATU ALUR</span><span>INTERNAL • TERBATAS</span></div>
    </div>
    <div class="login-panel">
        <div class="login-card">
            <div class="mobile-brand"><div class="brand-emblem small-emblem brand-logo"><img src="{{ asset('sitera/sitera-logo.webp') }}" alt="Logo Kementerian Pertanian"></div><strong>SITERA</strong></div>
            <span class="eyebrow">PORTAL INTERNAL</span>
            <h2>Selamat Datang</h2>
            <p class="login-intro">Masuk ke ruang kerja pelaporan dan pemeriksaan dokumen kegiatan irigasi.</p>

            @if ($errors->any())
                <div class="login-error" role="alert"><strong>Username/email atau password tidak dapat digunakan.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @if (session('status'))<div class="login-success" role="status">{{ session('status') }}</div>@endif

            <form method="post" action="{{ route('login.store') }}" class="login-fields" autocomplete="on">
                @csrf
                <div class="login-field">
                    <label for="login">Username / Email</label>
                    <div class="login-input-wrap"><span class="input-symbol" aria-hidden="true">♙</span><input id="login" name="login" type="text" value="{{ old('login', old('email')) }}" autocomplete="username" placeholder="Masukkan username atau email" required autofocus></div>
                </div>
                <div class="login-field">
                    <label for="password">Password</label>
                    <div class="login-input-wrap"><span class="input-symbol" aria-hidden="true">⌑</span><input id="password" name="password" type="password" autocomplete="current-password" placeholder="Masukkan password" required><button type="button" id="toggle-password" class="password-toggle" aria-label="Tampilkan password" aria-pressed="false">◉</button></div>
                </div>
                <div class="login-options">
                    <label class="remember-option"><input type="checkbox" name="remember" value="1" @checked(old('remember'))><span>Ingat saya</span></label>
                    <a class="text-link" href="{{ route('password.request') }}">Lupa password?</a>
                </div>
                <button type="submit" class="btn btn-primary btn-full">Masuk <span aria-hidden="true">↗</span></button>
            </form>
            <div class="login-note"><span class="lock-icon" aria-hidden="true">⌑</span><span>Akses terbatas untuk pengguna yang telah memiliki akun SITERA. Jika belum memiliki akun, hubungi Super Admin instansi Anda.</span></div>
            <p class="login-legal">SITERA · Sistem Informasi Irigasi Tersier · Kementerian Pertanian Pasar Minggu</p>
        </div>
        <div class="panel-footer">Kementerian Pertanian · Pasar Minggu <span>Portal operasional</span></div>
    </div>
</section>
<script>
(() => {
    const button = document.getElementById('toggle-password');
    const input = document.getElementById('password');
    button.addEventListener('click', () => {
        const reveal = input.type === 'password';
        input.type = reveal ? 'text' : 'password';
        button.setAttribute('aria-pressed', String(reveal));
        button.setAttribute('aria-label', reveal ? 'Sembunyikan password' : 'Tampilkan password');
    });
})();
</script>
</body>
</html>
