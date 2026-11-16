<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f4c63">
    <title>Verifikasi &amp; Aktivasi Akun — SITERA</title>
    <link rel="icon" type="image/webp" href="{{ asset('sitera/sitera-logo.webp') }}">
    <style>
        *{box-sizing:border-box}
        body{margin:0;min-height:100vh;display:grid;place-items:center;background:#eef3f6;color:#1b3a57;font-family:'Inclusive Sans','DM Sans',Arial,Helvetica,sans-serif}
        .activation-page{position:relative;display:grid;place-items:center;width:100%;min-height:100vh;padding:24px 20px;overflow:hidden}
        .activation-page::before,.activation-page::after{pointer-events:none}
        .activation-page::before{content:"";position:absolute;inset:0;background:url("{{ asset('sitera/login/assets/kementan.jpg') }}") center/cover no-repeat;filter:blur(9px) saturate(1.1);transform:scale(1.06)}
        .activation-page::after{content:"";position:absolute;inset:0;background:linear-gradient(135deg,#0f4c631f,#8fb7ca2b)}
        .activation-card{position:relative;z-index:1;width:min(100%,560px);padding:30px 40px 26px;border-radius:16px;background:#fff;box-shadow:0 24px 70px #1b3a5733}
        .activation-kicker{display:flex;align-items:center;gap:9px;margin-bottom:11px;color:#2c4a63;font-size:10px;font-weight:700;letter-spacing:.14em;text-transform:uppercase}
        .activation-kicker::before{content:"";width:24px;height:2px;flex:none;background:#1b3a57}
        .activation-card h1{margin:0;color:#1b3a57;font-size:26px;line-height:1.2;font-weight:800;letter-spacing:-.02em}
        .activation-intro{margin:8px 0 18px;color:#5b6b7a;font-size:12.5px;line-height:1.55}
        .activation-errors{margin:0 0 15px;padding:10px 12px;border:1px solid #f0cfca;border-radius:9px;background:#fff7f6;color:#a1423c;font-size:11px;line-height:1.55}
        .activation-errors strong{display:block;margin-bottom:4px}
        .activation-errors ul{margin:0;padding-left:16px}
        .activation-field{margin-bottom:14px}
        .activation-field label{display:block;margin-bottom:6px;color:#33475b;font-size:10px;font-weight:700;letter-spacing:.06em;text-transform:uppercase}
        .activation-field label .optional{color:#8a99a8;font-weight:500;letter-spacing:0;text-transform:none}
        .activation-input{position:relative;display:flex;align-items:center;min-height:46px;border:1px solid #dde3e8;border-radius:8px;background:#fff;transition:border-color .15s,box-shadow .15s}
        .activation-input:focus-within{border-color:#0f4c63;box-shadow:0 0 0 3px #0f4c6314}
        .activation-input .field-icon{display:grid;place-items:center;width:40px;flex:none;color:#8a99a8}
        .activation-input .field-icon svg{width:16px;height:16px;display:block}
        .activation-input input{width:100%;min-width:0;height:44px;border:0;outline:0;background:transparent;color:#244856;font:13.5px 'Inclusive Sans','DM Sans',Arial,sans-serif}
        .activation-input input::placeholder{color:#8a99a8}
        .activation-input.invalid{border-color:#d98b84;background:#fffafa}
        .show-password{display:grid;place-items:center;width:32px;height:32px;margin-right:7px;flex:none;border:1px solid #d7e9f3;border-radius:7px;background:#e8f3fa;cursor:pointer;color:#0f4c63}
        .show-password svg{width:15px;height:15px;display:block}
        .show-password .eye-off{display:none}
        .show-password[aria-pressed="true"] .eye-on{display:none}
        .show-password[aria-pressed="true"] .eye-off{display:block}
        .activation-help{margin:0 0 15px;color:#7a8896;font-size:10.5px;line-height:1.55}
        .activation-help strong{color:#33475b;font-weight:700}
        .activation-submit{width:100%;min-height:48px;border:0;border-radius:9px;background:#0f4c63;color:#fff;font:700 15px 'Inclusive Sans','DM Sans',Arial,sans-serif;cursor:pointer;box-shadow:0 8px 20px #0f4c6326;transition:background .15s}
        .activation-submit:hover{background:#0d4154}
        .activation-submit:focus-visible{outline:2px solid #0f4c63;outline-offset:3px}
        .activation-footer{margin-top:16px;color:#8a9aa8;font-size:10px;line-height:1.55;text-align:left}
        @media(max-width:560px){.activation-page{padding:22px 15px}.activation-card{padding:28px 22px 20px;border-radius:13px}.activation-card h1{font-size:24px}.activation-intro{font-size:12px}.activation-input .field-icon{width:36px}}
    </style>
</head>
<body>
<main class="activation-page">
    <section class="activation-card" aria-labelledby="activation-title">
        <div class="activation-kicker">Verifikasi Pengguna</div>
        <h1 id="activation-title">Verifikasi &amp; Aktivasi Akun</h1>
        <p class="activation-intro">Lengkapi identitas PIC dan nomor kontak milik Anda sendiri. Setelah itu, buat password baru untuk mengaktifkan akun.</p>

        @if (isset($errors) && $errors->any())
            <div class="activation-errors" role="alert">
                <strong>Aktivasi belum berhasil.</strong>
                <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif

        <form method="post" action="{{ route('password.first-login.update') }}" autocomplete="on" novalidate>
            @csrf
            <div class="activation-field">
                <label for="name">PIC</label>
                <div class="activation-input">
                    <span class="field-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                    <input id="name" name="name" type="text" value="{{ old('name') }}" maxlength="255" required autofocus autocomplete="name" placeholder="Masukkan nama lengkap PIC">
                </div>
            </div>

            <div class="activation-field">
                <label for="contact">Nomor Kontak PIC <span class="optional">(opsional)</span></label>
                <div class="activation-input">
                    <span class="field-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.9v3a2 2 0 0 1-2.2 2 19.8 19.8 0 0 1-8.6-3.1 19.5 19.5 0 0 1-6-6A19.8 19.8 0 0 1 2.1 4.2 2 2 0 0 1 4.1 2h3a2 2 0 0 1 2 1.7c.1 1 .4 1.9.7 2.8a2 2 0 0 1-.5 2.1L8.1 9.9a16 16 0 0 0 6 6l1.3-1.3a2 2 0 0 1 2.1-.4c.9.3 1.8.6 2.8.7a2 2 0 0 1 1.7 2z"/></svg></span>
                    <input id="contact" name="contact" type="tel" value="{{ old('contact', $user->contact) }}" maxlength="50" autocomplete="tel" placeholder="Masukkan nomor kontak PIC">
                </div>
            </div>

            <div class="activation-field">
                <label for="email_confirmation">Ulangi Email</label>
                <div class="activation-input">
                    <span class="field-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 7L2 7"/></svg></span>
                    <input id="email_confirmation" name="email_confirmation" type="email" value="{{ old('email_confirmation') }}" maxlength="255" required autocomplete="off" placeholder="Ketik ulang email akun Anda">
                </div>
            </div>

            <div class="activation-field">
                <label for="password">Password Baru</label>
                <div class="activation-input">
                    <span class="field-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/></svg></span>
                    <input id="password" name="password" type="password" minlength="8" required autocomplete="new-password" placeholder="Buat password baru, minimal 8 karakter">
                    <button class="show-password" id="toggle-password" type="button" aria-label="Tampilkan password" aria-pressed="false">
                        <svg class="eye-on" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/></svg>
                        <svg class="eye-off" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M9.9 5.2A10.9 10.9 0 0 1 12 5c6.4 0 10 7 10 7a17.8 17.8 0 0 1-3.3 4.1M6.1 6.6A17.6 17.6 0 0 0 2 12s3.6 7 10 7a10.7 10.7 0 0 0 4.4-.9"/><path d="M2 2l20 20"/></svg>
                    </button>
                </div>
            </div>

            <div class="activation-field">
                <label for="password_confirmation">Verifikasi Password</label>
                <div class="activation-input">
                    <span class="field-icon" aria-hidden="true"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg></span>
                    <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" required autocomplete="new-password" placeholder="Masukkan kembali password baru">
                </div>
            </div>

            <p class="activation-help">Isi Nama PIC dan Nomor Kontak PIC yang dapat dihubungi. Ketik ulang email akun untuk verifikasi tambahan. Password baru minimal 8 karakter dan <strong>harus berbeda dari password bawaan</strong>.</p>
            <button class="activation-submit" type="submit">Verifikasi &amp; Aktifkan Akun</button>
        </form>

        <div class="activation-footer">SITERA — Sistem Informasi Irigasi Tersier — Kementerian Pertanian</div>
    </section>
</main>
<script>
(() => {
    const toggle = document.getElementById('toggle-password');
    const password = document.getElementById('password');
    const confirmation = document.getElementById('password_confirmation');
    if (!toggle || !password || !confirmation) return;
    toggle.addEventListener('click', () => {
        const show = password.type === 'password';
        password.type = show ? 'text' : 'password';
        confirmation.type = show ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(show));
        toggle.setAttribute('aria-label', show ? 'Sembunyikan password' : 'Tampilkan password');
    });
})();
</script>
</body>
</html>
