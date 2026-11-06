<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#086288">
    <title>Ringkasan Administrasi — SITERA</title>
    <link rel="icon" type="image/webp" href="{{ asset('sitera/sitera-logo.webp') }}">
    <link rel="stylesheet" href="{{ asset('sitera/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('sitera/pelapor-review.css') }}">
    <link rel="stylesheet" href="{{ asset('sitera/superadmin-review.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile-shell.css') }}">
    <style>
        .admin-cta{display:flex;align-items:center;gap:9px}
        .admin-cta .btn{min-height:36px;text-decoration:none}
        .admin-activity-empty{padding:12px 0;color:#82969e;font-size:10px}
        .admin-avatar{display:inline-grid;place-items:center;width:31px;height:31px;border-radius:50%;background:#e8f4f8;color:#086288;font-weight:800;font-size:10px;flex:none}
        .admin-recent-copy{display:flex;align-items:center;gap:9px;min-width:0}
        .cpcl-admin-link{color:#086288;font-size:9px;font-weight:750;text-decoration:none;white-space:nowrap}
        .cpcl-admin-link:hover,.text-action:hover{text-decoration:underline}
        .admin-data-note{margin:0 18px 14px;color:#80949c;font-size:8px;line-height:1.5}
        .dashboard-panel{margin-bottom:15px}
        @media(max-width:700px){.admin-cta{width:100%;flex-wrap:wrap}.admin-cta .btn{flex:1}.heading-actions{align-items:stretch}}
    </style>
</head>
<body>
<header class="app-header">
    <a class="brand" href="{{ route('dashboard') }}" aria-label="SITERA beranda">
        <span class="brand-logo"><img src="{{ asset('sitera/sitera-logo.webp') }}" alt="Logo Irigasi Pertanian"></span>
        <span class="brand-copy"><strong>SITERA</strong><small>Sistem Informasi Rekapitulasi & Pemantauan Kegiatan Irigasi Pertanian</small></span>
    </a>
    <div class="header-right">
        <button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-label="Buka menu navigasi" aria-controls="main-nav" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button>
        <span class="internal-tag"><span></span> Sistem internal</span>
        <a class="icon-button" href="{{ route('notifications.index') }}" aria-label="Notifikasi" title="Notifikasi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg>@php($unread = auth()->user()->unreadNotifications()->count())@if ($unread > 0)<i class="notification-count">{{ $unread }}</i>@endif</a>
        <div class="profile"><span class="avatar">SA</span><span class="profile-copy"><strong>{{ auth()->user()->name }}</strong><small>Administrasi Sistem</small></span></div>
        <form method="post" action="{{ route('logout') }}" class="logout-form">@csrf<button class="exit-link" type="submit">Keluar</button></form>
    </div>
</header>
<nav class="main-nav" id="main-nav" aria-label="Navigasi Super Admin">
    <a class="nav-item active" href="{{ route('dashboard') }}"><span class="nav-icon">⌂</span>Ringkasan</a>
    <a class="nav-item" href="{{ route('admin.index', ['view' => 'accounts']) }}"><span class="nav-icon">♙</span>Manajemen Akun</a>
    <a class="nav-item" href="{{ route('admin.index', ['view' => 'cpcl']) }}"><span class="nav-icon">▤</span>Data CPCL</a>
    <a class="nav-item" href="{{ route('admin.index', ['view' => 'imports']) }}"><span class="nav-icon">⇧</span>Impor CPCL</a>
    <a class="nav-item" href="{{ route('admin.index', ['view' => 'audit']) }}"><span class="nav-icon">◷</span>Audit Log</a>
</nav>
<div class="sidebar-backdrop" id="sidebar-backdrop" hidden></div>
<main class="workspace">
    <div class="page-heading">
        <div><div class="breadcrumb"><span>Ruang Administrasi</span><span>/</span><strong>Ringkasan</strong></div><h1>Ringkasan administrasi</h1><p>Pantau akun, referensi CPCL, dan pelaporan pada sistem.</p></div>
        <div class="heading-actions"><span class="period-chip"><span></span> Data operasional · {{ now()->format('d M Y') }}</span><div class="admin-cta"><a class="btn btn-outline btn-sm" href="{{ route('admin.index', ['view' => 'imports']) }}">⇧ Impor CPCL</a><a class="btn btn-primary btn-sm" href="{{ route('admin.index', ['view' => 'accounts']) }}">＋ Tambah akun</a></div></div>
    </div>
    <div class="notice"><span class="notice-icon">i</span><span><strong>Data operasional.</strong> Ringkasan berikut dihitung dari database SITERA. Data contoh demo tidak digunakan.</span></div>

    <section id="view-dashboard" class="view active">
        <div class="metrics-grid admin-metrics" id="admin-metrics">
            <article class="metric"><div class="metric-top"><span>Total akun</span><span class="metric-icon">♙</span></div><div class="metric-value">{{ $totalUsers }}</div><div class="metric-meta">Semua peran terdaftar</div></article>
            <article class="metric"><div class="metric-top"><span>Akun Pelapor aktif</span><span class="metric-icon">✓</span></div><div class="metric-value">{{ $reporterCount }}</div><div class="metric-meta">Pelapor Daerah</div></article>
            <article class="metric"><div class="metric-top"><span>Akun Pusat</span><span class="metric-icon">◷</span></div><div class="metric-value">{{ $verifierCount + $executiveCount }}</div><div class="metric-meta">Verifikator & Pimpinan</div></article>
            <article class="metric"><div class="metric-top"><span>Total referensi CPCL</span><span class="metric-icon">▤</span></div><div class="metric-value">{{ $cpclCount }}</div><div class="metric-meta">Baris CPCL tersimpan</div></article>
        </div>

        <section class="panel cpcl-donut-panel role-cpcl-donut dashboard-panel">
            <div class="panel-heading cpcl-chart-heading"><div><h2>Rekap Status CPCL</h2><p>Jumlah CPCL berdasarkan status pelaporan.</p></div>
                <form class="cpcl-scope-control" method="get" action="{{ route('dashboard') }}"><span>Cakupan diagram</span><select name="cakupan" aria-label="Pilih cakupan diagram" onchange="this.form.submit()">@foreach ($scopeOptions as $option)<option value="{{ $option['value'] }}" @selected($scope === $option['value'])>{{ $option['label'] }}</option>@endforeach</select></form>
            </div>
            <div class="cpcl-donut-layout"><div class="cpcl-donut-wrap"><div class="cpcl-donut" role="img" aria-label="Diagram status {{ $scope }}">
                <svg viewBox="0 0 220 220" aria-hidden="true">
                    @if ($chartTotal > 0)
                        @foreach ($chartSegments as $segment)<circle cx="110" cy="110" r="82" fill="none" stroke="{{ $segment['color'] }}" stroke-width="26" stroke-dasharray="{{ $segment['dash'] }} {{ $circumference - $segment['dash'] }}" stroke-dashoffset="{{ $segment['offset'] }}" transform="rotate(-90 110 110)"/>@endforeach
                    @else
                        <circle cx="110" cy="110" r="82" fill="none" stroke="#e5edf0" stroke-width="26"/>
                    @endif
                </svg>
                <div class="cpcl-donut-center"><span>Total CPCL</span><strong>{{ $chartTotal }}</strong><small>{{ $scope === 'Nasional' ? 'Seluruh Indonesia' : $scopeOptions->firstWhere('value', $scope)['label'] }}</small></div>
            </div></div><div class="cpcl-donut-legend" aria-live="polite">
                @foreach ($statusCounts as $label => $count)<div class="cpcl-donut-item"><i class="cpcl-donut-dot" style="background:{{ $chartColors[$loop->index] }}"></i><span class="cpcl-donut-name">{{ $label }}</span><strong class="cpcl-donut-value">{{ $count }}</strong></div>@endforeach
            </div></div>
            <div class="cpcl-chart-note">Statistik dihitung dari CPCL dan status paket yang tersimpan. Belum tersedia status verifikasi akan ditampilkan sebagai “Menunggu pemeriksaan”.</div>
        </section>

        <div class="admin-dashboard-grid">
            <section class="panel"><div class="panel-heading"><div><h2>Akun terbaru</h2><p>Akun pengguna yang baru terdaftar.</p></div><a class="text-action" href="{{ route('admin.index', ['view' => 'accounts']) }}">Buka manajemen akun →</a></div><div class="admin-list">
                @forelse ($recentUsers as $account)<div class="admin-list-row"><div class="admin-recent-copy"><span class="admin-avatar">{{ strtoupper(substr($account->name, 0, 1)) }}</span><span class="admin-list-copy"><strong>{{ $account->name }}</strong><small>{{ $account->role?->name ?? 'Tanpa role' }} · {{ $account->email }}</small></span></div><span class="admin-list-meta">{{ $account->created_at?->format('d M Y') ?? '—' }}</span></div>@empty<div class="admin-activity-empty">Belum ada akun pengguna.</div>@endforelse
            </div></section>
            <section class="panel"><div class="panel-heading"><div><h2>Aktivitas pelaporan</h2><p>Jumlah paket laporan CPCL dalam sistem.</p></div><a class="text-action" href="{{ route('exports.csv', ['format' => 'xlsx']) }}">Ekspor rekap →</a></div><div class="admin-list"><div class="admin-list-row"><div class="admin-list-copy"><strong>Draf</strong><small>Belum dikirim</small></div><span class="admin-list-meta">{{ $draftCount }} paket</span></div><div class="admin-list-row"><div class="admin-list-copy"><strong>Terkirim</strong><small>Menunggu atau selesai diverifikasi</small></div><span class="admin-list-meta">{{ $submittedCount }} paket</span></div><div class="admin-list-row"><div class="admin-list-copy"><strong>Impor CPCL</strong><small>Data baru ditambahkan tanpa menimpa data lama</small></div><a class="cpcl-admin-link" href="{{ route('admin.index', ['view' => 'imports']) }}">Buka →</a></div></div></section>
        </div>

        <section class="panel dashboard-panel"><div class="panel-heading"><div><h2>Referensi CPCL terbaru</h2><p>Data CPCL terakhir yang tersedia di sistem.</p></div><a class="text-action" href="{{ route('admin.index', ['view' => 'cpcl']) }}">Kelola CPCL →</a></div><div class="table-wrap" tabindex="0" role="region" aria-label="Tabel data; geser horizontal untuk melihat kolom lainnya"><table class="data-table"><thead><tr><th>Nama DI</th><th>Kewenangan</th><th>Wilayah</th><th>Kelompok Tani</th><th>Status laporan</th></tr></thead><tbody>
            @forelse ($recentCpcl as $record)
                @php($currentStatus = $record->reports_count === 0 ? 'Belum ada laporan' : ($record->draft_reports_count > 0 ? 'Draf' : ($record->tidak_sesuai_reports_count > 0 ? 'Tidak sesuai' : ($record->sesuai_reports_count > 0 ? 'Sesuai' : 'Menunggu pemeriksaan'))))
                <tr><td><span class="cell-main">{{ $record->nama_di }}</span><span class="cell-sub">CPCL #{{ $record->id }}</span></td><td>{{ $record->kewenangan ?? '—' }}</td><td><span class="cell-main">{{ $record->kabupaten }}, {{ $record->provinsi }}</span><span class="cell-sub">{{ $record->kecamatan }} · {{ $record->desa }}</span></td><td>{{ $record->kel_tani }}</td><td>{{ $currentStatus }}</td></tr>
            @empty<tr><td colspan="5"><div class="filter-empty">Belum ada referensi CPCL. Tambah manual atau impor CPCL untuk memulai.</div></td></tr>@endforelse
        </tbody></table></div></section>

        <section class="panel"><div class="panel-heading"><div><h2>Prinsip pengelolaan SITERA</h2><p>Aturan penting untuk administrasi.</p></div></div><div class="admin-principles"><div><span>01</span><p><strong>Akun berbasis peran</strong><small>Pelapor, Verifikator, dan Pimpinan memiliki akses sesuai tugas.</small></p></div><div><span>02</span><p><strong>Impor CPCL bersifat tambah-saja</strong><small>Duplikat, konflik, dan baris tidak valid dilaporkan terpisah; data lama tidak ditimpa.</small></p></div><div><span>03</span><p><strong>Data operasional, bukan simulasi</strong><small>Ringkasan dan daftar di halaman ini berasal dari database SITERA.</small></p></div></div></section>
    </section>
</main>
<script src="{{ asset('sitera/sidebar.js') }}"></script>
</body>
</html>
