<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'SITERA')</title>
    <link rel="stylesheet" href="{{ asset('css/sitera-ui.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile-shell.css') }}">
    <style>.unread-count{position:absolute;top:-5px;right:-5px;min-width:17px;height:17px;border-radius:9px;background:#d25353;color:#fff;font-size:10px;display:grid;place-items:center;padding:0 4px}</style>
    @stack('styles')
</head>
<body>
@php
    $user = auth()->user();
    $initials = collect(explode(' ', trim($user->name)))->filter()->map(fn ($part) => strtoupper(substr($part, 0, 1)))->take(2)->join('');
@endphp
<div class="app">
    <aside class="sidebar" id="main-nav" role="navigation" aria-label="Navigasi SITERA">
        <div class="brand">
            <div class="logo">SI<br>TE</div>
            <div><strong>SITERA</strong><small>Sistem Irigasi Tersier</small></div>
        </div>
        <div class="nav-label">Ruang kerja</div>
        <nav class="nav">
            <a class="{{ request()->routeIs('dashboard') ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="ico">⌂</span><span>Ringkasan</span></a>
            @if ($user->role?->code === 'pelapor_daerah')
                <a class="{{ request()->routeIs('reports.index') || request()->routeIs('reports.show') ? 'active' : '' }}" href="{{ route('reports.index') }}"><span class="ico">▤</span><span>Laporan saya</span></a>
                <a class="{{ request()->routeIs('reports.create') ? 'active' : '' }}" href="{{ route('reports.create') }}"><span class="ico">＋</span><span>Pilih CPCL</span></a>
            @endif
            @if ($user->role?->code === 'verifikator_pusat')
                <a class="{{ request()->routeIs('verification.index') ? 'active' : '' }}" href="{{ route('verification.index') }}"><span class="ico">◉</span><span>Antrean pemeriksaan</span></a>
            @endif
            @if ($user->role?->code === 'pimpinan_kementan')
                <a class="{{ request()->routeIs('monitoring.index') ? 'active' : '' }}" href="{{ route('monitoring.index') }}"><span class="ico">◫</span><span>Monitoring nasional</span></a>
            @endif
            @if ($user->role?->code === 'super_admin')
                <a class="{{ request()->is('admin-only') ? 'active' : '' }}" href="{{ url('/admin-only') }}"><span class="ico">⚙</span><span>Manajemen Akun</span></a>
            @endif
            <a href="{{ route('exports.csv', ['format' => 'xlsx']) }}"><span class="ico">⇩</span><span>Ekspor rekap</span></a>
        </nav>
        <div class="nav-label" style="margin-top:28px">Bantuan</div>
        <nav class="nav"><a class="nav-item" href="#panduan" onclick="return false"><span class="ico">?</span><span>Panduan singkat</span></a></nav>
        <div class="sidebar-foot"><div class="profile"><div class="avatar">{{ $initials ?: 'U' }}</div><div><b>{{ $user->name }}</b><span>{{ $user->role?->name ?? 'Pengguna' }}</span></div></div></div>
    </aside>
    <div class="sidebar-backdrop" id="sidebar-backdrop" hidden></div>
    <main class="main">
        <header class="topbar">
            <button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-label="Buka menu navigasi" aria-controls="main-nav" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg></button>
            <div class="crumb">SITERA <span>/</span> <b>@yield('breadcrumb', 'Ringkasan')</b></div>
            <div class="top-actions">
                <a style="position:relative" class="icon-btn notification-link" href="{{ route('notifications.index') }}" title="Notifikasi" aria-label="Notifikasi">♧@php($unreadNotifications = $user->unreadNotifications()->count())@if ($unreadNotifications)<span class="unread-count">{{ $unreadNotifications > 9 ? '9+' : $unreadNotifications }}</span>@endif</a>
                <form method="post" action="{{ route('logout') }}" class="user-chip-form">
                    @csrf
                    <button class="user-chip" type="submit" title="Keluar"><span class="avatar mini">{{ $initials ?: 'U' }}</span><span>{{ $user->name }}</span>↪</button>
                </form>
            </div>
        </header>
        <div class="content">@yield('content')</div>
    </main>
</div>
<script src="{{ asset('sitera/sidebar.js') }}"></script>
@stack('scripts')
</body>
</html>
