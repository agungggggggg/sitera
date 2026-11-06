<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#086288">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Area Pelapor — SITERA')</title>
    <link rel="icon" type="image/webp" href="{{ asset('sitera/sitera-logo.webp') }}">
    <link rel="stylesheet" href="{{ asset('sitera/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('sitera/pelapor-review.css') }}">
    <style>
        .status.draf{background:#f0f3f4;color:#778891}.status.menunggu-pemeriksaan{background:var(--blue-soft);color:var(--blue)}.status.tidak-sesuai{background:var(--red-soft);color:var(--red)}.status.sesuai{background:var(--green-soft);color:var(--green)}.status.status-muted{background:#f0f3f4;color:#71858e}.activity-item{text-decoration:none;color:inherit}.activity-item:hover .activity-copy strong{color:var(--blue)}
        .pagination{display:flex;justify-content:flex-end;gap:5px;padding:12px 16px}.pagination a,.pagination span{display:inline-flex;padding:6px 9px;border:1px solid var(--line);border-radius:5px;color:#587482;text-decoration:none;font-size:9px}.pagination .active span{background:var(--blue-soft);color:var(--blue)}
    </style>
    <link rel="stylesheet" href="{{ asset('css/mobile-shell.css') }}">
    @stack('styles')
</head>
<body>
    <div class="prototype-ribbon"><span class="live-dot"></span> SITERA · AREA PELAPOR <span class="ribbon-divider">•</span> DATA OPERASIONAL</div>
    <header class="app-header">
        <a class="brand" href="{{ route('dashboard') }}" aria-label="SITERA beranda">
            <span class="brand-logo"><img src="{{ asset('sitera/sitera-logo.webp') }}" alt="Logo Irigasi Pertanian"></span>
            <span class="brand-copy"><strong>SITERA</strong><small>Sistem Informasi Rekapitulasi & Pemantauan Kegiatan Irigasi Pertanian</small></span>
        </a>
        <div class="header-right">
            <button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-label="Buka menu navigasi" aria-controls="main-nav" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
            <span class="internal-tag"><span></span> Sistem internal</span>
            <a class="icon-button" href="{{ route('notifications.index') }}" aria-label="Notifikasi" title="Notifikasi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>@php($unread = auth()->user()->unreadNotifications()->count())@if ($unread > 0)<i>{{ $unread > 9 ? '9+' : $unread }}</i>@endif</a>
            <div class="profile"><span class="avatar">{{ collect(explode(' ', trim(auth()->user()->name)))->filter()->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->join('') ?: 'PD' }}</span><span class="profile-copy"><strong>{{ auth()->user()->name }}</strong><small>{{ auth()->user()->kabupaten ?: 'Wilayah belum ditetapkan' }}</small></span><span class="chevron">⌄</span></div>
            <form class="logout-form" method="post" action="{{ route('logout') }}">@csrf<button class="exit-link" type="submit">Keluar</button></form>
        </div>
    </header>
    <nav class="main-nav" id="main-nav" aria-label="Navigasi Pelapor">
        <a class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-icon">⌂</span>Ringkasan</a>
        <a class="nav-item {{ request()->routeIs('reports.index') || request()->routeIs('reports.show') ? 'active' : '' }}" href="{{ route('reports.index') }}"><span class="nav-icon">▤</span>Laporan Saya</a>
        <a class="nav-item {{ request()->routeIs('reports.create') || request()->routeIs('reports.package.*') ? 'active' : '' }}" href="{{ route('reports.create') }}"><span class="nav-icon">⌕</span>Pilih CPCL</a>
        <a class="nav-item {{ request()->routeIs('reports.all') ? 'active' : '' }}" href="{{ route('reports.all') }}"><span class="nav-icon">▦</span>Semua Laporan</a>
    </nav>
    <div class="sidebar-backdrop" id="sidebar-backdrop" hidden></div>
    <main class="workspace">
        @if (session('status'))<div class="notice" role="status"><span class="notice-icon">✓</span><span>{{ session('status') }}</span></div>@endif
        @include('layouts.filter-errors')
        @yield('content')
        <footer class="app-footer"><span>© {{ now()->year }} SITERA · Kementerian Pertanian</span><span>Ruang Pelapor · data sesuai hak akses akun</span></footer>
    </main>
    <script src="{{ asset('sitera/sidebar.js') }}"></script>
    @stack('scripts')
<script src="{{ asset('sitera/filter-controls.js') }}"></script>
</body>
</html>
     