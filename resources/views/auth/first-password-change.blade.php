<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#086288">
    <title>Ubah Password — SITERA</title>
    <link rel="icon" type="image/webp" href="{{ asset('sitera/sitera-logo.webp') }}">
    <link rel="stylesheet" href="{{ asset('sitera/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('sitera/app.css') }}">
    <style>
        *{box-sizing:border-box}body{margin:0;min-height:100vh;display:grid;place-items:center;background:#f2f6f5;color:#173b49;font-family:'DM Sans',Arial,sans-serif}.activation-page{position:relative;display:grid;place-items:center;min-height:100vh;width:100%;padding:35px 20px;overflow:hidden}.activation-page:before,.activation-page:after{content:"";position:fixed;top:0;bottom:0;width:8px;background:linear-gradient(180deg,#086288 0 34%,#8cbf9d 34% 67%,#dcebe6 67%);opacity:.76}.activation-page:before{left:0}.activation-page:after{right:0}.activation-card{position:relative;width:min(100%,560px);padding:40px 44px 32px;border:1px solid #e2eae8;border-radius:20px;background:#fff;box-shadow:0 22px 65px #173b4914}.activation-kicker{display:flex;align-items:center;gap:9px;margin-bottom:14px;color:#66818b;font-size:10px;font-weight:800;letter-spacing:.13em}.activation-kicker:before{content:"";width:25px;height:2px;background:#086288}.activation-card h1{margin:0;color:#173d4d;font-family:Manrope,Arial,sans-serif;font-size:30px;line-height:1.2;letter-spacing:-.035em}.activation-intro{margin:10px 0 24px;color:#748b94;font-size:12px;line-height:1.6}.activation-field{margin-bottom:16px}.activation-field label{display:block;margin-bottom:7px;color:#385764;font-size:10px;font-weight:750}.activation-input{position:relative;display:flex;align-items:center;min-height:47px;border:1px solid #dce7e7;border-radius:9px;background:#f9fbfb}.activation-input:focus-within{border-color:#48a0b9;box-shadow:0 0 0 3px #08628817;background:#fff}.activation-input .field-icon{width:42px;flex:none;text-align:center;color:#8099a1;font-size:15px}.activation-input input{width:100%;min-width:0;min-height:45px;border:0;outline:0;background:transparent;color:#244856;font:12px 'DM Sans',Arial,sans-serif}.activation-input input::placeholder{color:#9aabb0}.activation-input input[readonly]{color:#41606b}.show-password{width:37px;height:35px;margin-right:6px;border:1px solid #e0e9ea;border-radius:8px;background:#fff;color:#607f8a;cursor:pointer}.activation-help{margin:-2px 0 22px;color:#83969c;font-size:10px;line-height:1.55}.activation-errors{margin:0 0 16px;padding:11px 13px;border:1px solid #f0cfca;border-radius:9px;background:#fff6f5;color:#a1423c;font-size:10px;line-height:1.55}.activation-errors ul{margin:5px 0 0;padding-left:17px}.activation-submit{width:100%;min-height:47px;border:0;border-radius:9px;background:#086288;color:#fff;font-size:12px;font-weight:800;cursor:pointer;box-shadow:0 8px 18px #08628824}.activation-submit:hover{background:#074d6b}.activation-footer{margin-top:24px;color:#8b9da2;text-align:center;font-size:9px;letter-spacing:.01em}.mobile-brand{display:none}
        @media(max-width:600px){.activation-page{padding:24px 18px}.activation-card{padding:30px 23px 25px;border-radius:15px}.activation-card h1{font-size:26px}.activation-intro{font-size:11px}.activation-page:before,.activation-page:after{width:5px}}
    </style>
</head>
<body>
<main class="activation-page">
    <section class="activation-card" aria-labelledby="activation-title">
        <div class="activation-kicker">PENGATURAN AKUN</div>
        <h1 id="activation-title">Ubah Password</h1>
        <p class="activation-intro">Login pertama berhasil. Silakan ganti password awal agar akun dapat digunakan.</p>
        @if ($errors->any())<div class="activation-errors" role="alert"><strong>Password belum berhasil diubah.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <form method="post" action="{{ route('password.first-login.update') }}" autocomplete="on">
            @csrf
            <div class="activation-field"><label for="username">Username</label><div class="activation-input"><span class="field-icon" aria-hidden="true">♙</span><input id="username" type="text" value="{{ $user->username ?: $user->email }}" readonly aria-readonly="true"></div></div>
            <div class="activation-field"><label for="password">Password baru</label><div class="activation-input"><span class="field-icon" aria-hidden="true">⌑</span><input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required autofocus placeholder="Buat password pribadi, minimal 8 karakter"><button class="show-password" id="toggle-password" type="button" aria-label="Tampilkan password" aria-pressed="false">◉</button></div></div>
            <div class="activation-field"><label for="password_confirmation">Konfirmasi password baru</label><div class="activation-input"><span class="field-icon" aria-hidden="true">✓</span><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required placeholder="Ulangi password baru"></div></div>
            <p class="activation-help">Password baru minimal 8 karakter dan harus berbeda dari password awal.</p>
            <button class="activation-submit" type="submit">Ubah Password</button>
        </form>
        <div class="activation-footer">SITERA · Sistem Informasi Irigasi Tersier · Kementerian Pertanian Pasar Minggu</div>
    </section>
</main>
<script>
(() => {
    const toggle = document.getElementById('toggle-password');
    const password = document.getElementById('password');
    const confirmation = document.getElementById('password_confirmation');
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
  
  
  
  
  
  
  