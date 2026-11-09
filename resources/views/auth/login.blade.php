<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#3d75aa">
  <title>SITERA — Masuk</title>
  <link rel="icon" type="image/webp" href="{{ asset('sitera/sitera-logo.webp') }}">
  <link rel="stylesheet" href="{{ asset('sitera/login/global.css') }}">
  <link rel="stylesheet" href="{{ asset('sitera/login/style.css') }}">
</head>
<body>
  <main class="LOGI-nin">
    <div class="BG" aria-hidden="true">
      <img class="kementan" src="{{ asset('sitera/login/assets/kementan.jpg') }}" alt="">
      <div class="rectangle"></div>
    </div>

    <header class="brand">
      <!-- ViewBox removes the empty transparent margin without changing the source asset. -->
      <svg class="image" viewBox="650 600 720 780" role="img" aria-label="Logo irigasi">
        <image href="{{ asset('sitera/login/assets/logo_irigasi.webp') }}" width="2000" height="2000"/>
      </svg>
      <div class="text-wrapper-10">SITERA</div>
      <p class="text-wrapper-11">Sistem Informasi Irigasi Tersier</p>
    </header>
    <div class="login-layout">
    <section class="text-kiri" aria-label="Tentang SITERA">
      <p class="text-wrapper-12">SATU PINTU PELAPORAN</p>
      <h1 class="text-wrapper-14">Alur Laporan<br>Lebih Tertata.</h1>
      <p class="text-wrapper-15">Informasi<br>Lebih Terlacak.</p>
      <p class="text-wrapper-13">Ruang kerja internal untuk menerima laporan, memeriksa dokumen, dan memantau status pelaporan irigasi tersier dari daerah.</p>
    </section>

    <section class="kotak-in-login" aria-labelledby="login-title">
      <form id="login-form" method="post" action="{{ route('login.store') }}" autocomplete="on">
        @csrf
        <p class="text-wrapper-2">AKSES PEGAWAI</p>
        <h2 class="text-wrapper" id="login-title">Selamat Datang</h2>
        <p class="p">Masuk ke ruang kerja pemantauan kegiatan irigasi</p>

        @if (isset($errors) && $errors->any())
          <div class="form-message form-message--error" role="alert">
            @if ($errors->count() === 1)
              {{ $errors->first() }}
            @else
              <ul style="margin: 0; padding-left: 18px;">
                @foreach ($errors->all() as $error)
                  <li>{{ $error }}</li>
                @endforeach
              </ul>
            @endif
          </div>
        @elseif (session('status'))
          <div class="form-message form-message--success" role="status">
            {{ session('status') }}
          </div>
        @endif

        <label class="text-wrapper-3" for="login">Username atau Email Pegawai</label>
        <div class="input-wrap">
          <input class="rectangle-2" id="login" name="login" type="text" placeholder="Masukkan username atau email" autocomplete="username" value="{{ old('login', old('email')) }}" required autofocus>
          <svg class="person" viewBox="0 0 24 24" fill="#b7b7b7" aria-hidden="true"><circle cx="12" cy="7" r="5"/><path d="M3 22v-3a7 7 0 0 1 7-7h4a7 7 0 0 1 7 7v3z"/></svg>
        </div>

        <label class="text-wrapper-4" for="password">Password</label>
        <div class="input-wrap">
          <input class="rectangle-3" id="password" name="password" type="password" placeholder="Masukkan password" autocomplete="current-password" required>
          <svg class="unlock" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 10V6a4 4 0 0 1 8 0" fill="none" stroke="#b7b7b7" stroke-width="3"/><rect x="5" y="9" width="14" height="13" rx="6" fill="#b7b7b7"/><path d="M12 14v4" stroke="#fcfcfc" stroke-width="2"/></svg>
          <button class="eye-toggle" id="toggle-password" type="button" aria-label="Tampilkan password" aria-controls="password" aria-pressed="false">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/><path id="eye-slash" d="m3 3 18 18" style="display:none"/></svg>
          </button>
        </div>

        <div class="form-options">
          <div class="remember-option">
            <input class="remember-device" id="remember" name="remember" type="checkbox" value="1" @checked(old('remember'))>
            <label class="text-wrapper-8" for="remember">Ingat perangkat ini</label>
          </div>
          <a class="text-wrapper-9" id="forgot-password" href="{{ route('password.request') }}">Lupa Password?</a>
        </div>

        <button class="rectangle-4" type="submit">Masuk</button>
      </form>
    </section>
    </div>
  </main>
  <script>
    (() => {
      const password = document.getElementById('password');
      const toggle = document.getElementById('toggle-password');
      if (!password || !toggle) return;
      toggle.addEventListener('click', () => {
        const visible = password.type === 'password';
        password.type = visible ? 'text' : 'password';
        toggle.setAttribute('aria-pressed', String(visible));
        toggle.setAttribute('aria-label', visible ? 'Sembunyikan password' : 'Tampilkan password');
        const eyeSlash = document.getElementById('eye-slash');
        if (eyeSlash) eyeSlash.style.display = visible ? 'block' : 'none';
      });
    })();
  </script>
</body>
</html>
