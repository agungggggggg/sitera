<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#086288">
    <title>{{ ['accounts' => 'Manajemen Akun', 'cpcl' => 'Data CPCL', 'imports' => 'Impor CPCL', 'audit' => 'Audit Log'][$activeView] ?? 'Manajemen Akun' }} — SITERA</title>
    <link rel="icon" type="image/webp" href="{{ asset('sitera/sitera-logo.webp') }}">
    <link rel="stylesheet" href="{{ asset('sitera/theme.css') }}"><link rel="stylesheet" href="{{ asset('sitera/pelapor-review.css') }}"><link rel="stylesheet" href="{{ asset('sitera/superadmin-review.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile-shell.css') }}">
    <style>
        .admin-alert{margin:0 18px 14px;padding:11px 13px;border:1px solid #cde6d6;border-radius:8px;background:#f0f8f3;color:#39704f;font-size:10px;line-height:1.5}
        .admin-error{margin:0 18px 14px;padding:11px 13px;border:1px solid #f0cfca;border-radius:8px;background:#fff7f6;color:#a1423c;font-size:10px;line-height:1.5}
        .account-form-toggle{margin:0 18px 12px;padding:14px 16px;border:1px solid #d9e8ee;border-radius:9px;background:#f8fbfc}
        .account-form-toggle summary{color:#294d5b;font-size:12px;font-weight:750;cursor:pointer}
        .account-form-toggle[open] summary{margin-bottom:14px}
        .account-filter{display:flex;align-items:center;gap:9px;flex-wrap:wrap}
        .account-filter .search-control{min-width:220px;flex:1}
        .account-filter .filter-control{min-width:150px}
        .account-actions{display:flex;align-items:center;justify-content:flex-end;gap:9px;flex-wrap:wrap}
        .account-actions form{display:inline-flex;margin:0}
        .account-actions button{background:none;border:0;padding:4px 0;color:#086288;font-size:9px;font-weight:700;cursor:pointer;white-space:nowrap}
        .account-actions .danger{color:#af4b48}
        .account-actions .disabled-action{color:#a8b4b9;cursor:not-allowed}
        .account-state{display:inline-flex;padding:5px 8px;border-radius:30px;font-size:9px;font-weight:750;white-space:nowrap}
        .account-state.active{background:#e9f5ed;color:#27764d}.account-state.inactive{background:#f0f3f4;color:#778891}.account-state.pending{background:#fff4e5;color:#9b681d}.account-state.verification{margin-top:5px;background:#fff4e5;color:#9b681d}
        .row-actions{display:flex;align-items:center;justify-content:flex-start;gap:7px;flex-wrap:nowrap;white-space:nowrap;min-width:max-content}.row-actions form{display:inline-flex;margin:0}.row-actions button{display:inline-flex;align-items:center;justify-content:center;gap:4px;min-height:28px;padding:5px 9px;border:1px solid transparent;border-radius:6px;font-size:9px;font-weight:700;cursor:pointer;white-space:nowrap;transition:background-color .15s ease,border-color .15s ease,transform .15s ease}.row-actions button:hover{transform:translateY(-1px)}.row-actions .verify-action,.row-actions .reset-action{color:#17648a;background:#eaf4fc;border-color:#cfe5f4}.row-actions .verify-action:hover,.row-actions .reset-action:hover{background:#dceefa;border-color:#b6d7eb}.row-actions .deactivate-action{color:#936313;background:#fff7e8;border-color:#f0e3c6}.row-actions .deactivate-action:hover{background:#fff1d5;border-color:#e7d4ac}.row-actions .activate-action{color:#27764d;background:#e9f5ed;border-color:#cce5d4}.row-actions .activate-action:hover{background:#dff0e5;border-color:#b8d8c2}.row-actions .delete-action{color:#af4b48;background:#fff0ef;border-color:#f1d1cf}.row-actions .delete-action:hover{background:#ffe5e3;border-color:#eabbb8}.row-actions button:focus-visible{outline:2px solid #086288;outline-offset:2px}.row-actions .disabled-action{padding:5px 0;color:#a8b4b9;font-size:9px;font-weight:700}
        .view-note{margin:0 18px 16px;padding:11px 13px;border:1px solid #e2ebef;border-radius:8px;background:#f8fbfc;color:#7d929b;font-size:9px;line-height:1.55}
        .admin-empty{padding:30px 15px;text-align:center;color:#82969e;font-size:10px}
        .audit-page-heading{align-items:center}.audit-page-heading .breadcrumb{display:none}.audit-page-heading h1{font-size:28px}.audit-updated{display:flex;align-items:center;gap:8px;color:#718892;font-size:11px;white-space:nowrap}.audit-updated span{width:7px;height:7px;border-radius:50%;background:#086288}.audit-info-banner{display:flex;align-items:flex-start;gap:11px;margin:0 0 17px;padding:13px 15px;border:1px solid #d5e9f0;border-radius:9px;background:#f2f8fb;color:#617e89;font-size:11px;line-height:1.55}.audit-info-icon{width:19px;height:19px;flex:none;display:grid;place-items:center;border:1px solid #a7cbd8;border-radius:50%;color:#086288;font-weight:800}.audit-info-banner strong{color:#173f52}.audit-panel{padding:0}.audit-panel-heading{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;padding:18px 20px 14px}.audit-panel-heading h2{margin:0;color:#203f4c;font-size:15px;font-weight:750}.audit-panel-heading p{margin:4px 0 0;color:#81949c;font-size:11px}.audit-readonly{padding:5px 10px;border:1px solid #e1eaee;border-radius:20px;background:#f3f7f8;color:#718891;font-size:10px;font-weight:700;white-space:nowrap}.audit-filter-form{padding:0 20px 14px}.audit-filter-main{display:grid;grid-template-columns:minmax(250px,1fr) minmax(145px,190px) minmax(130px,160px) auto;align-items:center;gap:9px}.audit-search{height:38px;display:flex;align-items:center;gap:9px;padding:0 11px;border:1px solid #dce8ed;border-radius:8px;background:#fbfdfe;color:#7e969f}.audit-search svg{width:16px;height:16px;flex:none}.audit-search input{width:100%;min-width:0;border:0;outline:0;background:transparent;color:#17323d;font-size:11px}.audit-search input::placeholder{color:#93a4aa}.audit-select{height:38px;width:100%;padding:0 28px 0 11px;border:1px solid #dce8ed;border-radius:8px;background:#fff;color:#526f7a;font-size:11px}.audit-reset{height:38px;display:inline-flex;align-items:center;justify-content:center;padding:0 15px;border:1px solid #dce8ed;border-radius:8px;background:#fff;color:#086288;font-size:11px;font-weight:700;text-decoration:none;white-space:nowrap}.audit-reset:hover{border-color:#a8cbd8;background:#f2f8fb}.audit-advanced{margin-top:10px;border-top:1px solid #edf2f4;padding-top:9px}.audit-advanced summary{width:max-content;color:#66818d;font-size:10px;font-weight:700;cursor:pointer}.audit-advanced-grid{display:flex;align-items:flex-end;gap:10px;flex-wrap:wrap;padding-top:11px}.audit-advanced-grid label{display:flex;flex-direction:column;gap:4px;color:#82969e;font-size:9px;font-weight:700}.audit-advanced-grid input{height:34px;min-width:145px;padding:0 9px;border:1px solid #dce8ed;border-radius:7px;background:#fff;color:#526f7a;font-size:10px}.audit-count{margin:0;padding:12px 20px;border-top:1px solid #edf2f4;color:#82969e;font-size:10px}.audit-table-wrap{border-top:1px solid #edf2f4}.audit-table{min-width:1120px;border-collapse:collapse}.audit-table th{padding:12px 16px;background:#f7fafb;color:#81949c;font-size:9px;letter-spacing:.06em;text-align:left;white-space:nowrap}.audit-table td{padding:13px 16px;border-bottom:1px solid #edf2f4;color:#526f7a;font-size:10px;vertical-align:top}.audit-table td:first-child{white-space:nowrap}.audit-table .audit-action{color:#294d5b;font-weight:700;white-space:nowrap}.audit-table .audit-details{min-width:360px;max-width:480px;color:#70858e;line-height:1.5}.audit-result{display:inline-flex;padding:4px 8px;border-radius:20px;background:#e9f5ed;color:#27764d;font-size:9px;font-weight:700;white-space:nowrap}.audit-result.failure,.audit-result.denied{background:#fff0ef;color:#af4b48}.audit-empty{padding:28px 16px!important;text-align:center;color:#82969e!important}.audit-pagination{padding:12px 18px}.audit-sr-only{position:absolute;width:1px;height:1px;padding:0;margin:-1px;overflow:hidden;clip:rect(0,0,0,0);white-space:nowrap;border:0}
        .logout-form{display:inline-flex;margin:0}.logout-form .exit-link{cursor:pointer}
        .nav-item{text-decoration:none}
        .table td{vertical-align:middle}
        @media(max-width:900px){.audit-filter-main{grid-template-columns:minmax(180px,1fr) minmax(125px,1fr) minmax(125px,1fr) auto}}
        @media(max-width:700px){.account-filter{align-items:stretch}.account-filter .search-control,.account-filter .filter-control{width:100%;min-width:0}.account-actions{justify-content:flex-start}.row-actions{gap:8px}.row-actions button{min-height:44px;padding:8px 11px;font-size:10px}}
        @media(max-width:560px){.audit-page-heading{align-items:flex-start}.audit-page-heading h1{font-size:24px}.audit-page-heading .heading-actions{align-self:flex-start}.audit-updated{font-size:10px}.audit-info-banner{font-size:10px}.audit-panel-heading{padding:15px 14px 12px}.audit-filter-form{padding:0 14px 12px}.audit-filter-main{grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:8px}.audit-search{grid-column:1/-1;height:44px}.audit-search input{height:44px;min-height:44px}.audit-select,.audit-reset{height:44px;min-height:44px}.audit-advanced-grid{align-items:stretch;flex-direction:column}.audit-advanced-grid label,.audit-advanced-grid input,.audit-advanced-grid button{width:100%}.audit-advanced-grid input{min-width:0;min-height:44px}.audit-count{padding:10px 14px}.audit-table th,.audit-table td{padding:11px 12px}}
    </style>
</head>
<body>
<div class="prototype-ribbon"><span class="live-dot"></span> ADMINISTRASI SISTEM <span class="ribbon-divider">•</span> DATA OPERASIONAL</div>
<header class="app-header">
    <a class="brand" href="{{ route('dashboard') }}" aria-label="SITERA beranda"><span class="brand-logo"><img src="{{ asset('sitera/sitera-logo.webp') }}" alt="Logo Irigasi Pertanian"></span><span class="brand-copy"><strong>SITERA</strong><small>Sistem Informasi Rekapitulasi & Pemantauan Kegiatan Irigasi Pertanian</small></span></a>
    <div class="header-right"><button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-label="Buka menu navigasi" aria-controls="main-nav" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button><span class="internal-tag"><span></span> Sistem internal</span>
        <a class="icon-button" href="{{ route('notifications.index') }}" aria-label="Notifikasi" title="Notifikasi"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M18 9a6 6 0 0 0-12 0c0 7-3 7-3 9h18c0-2-3-2-3-9M10 21h4" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"/></svg></a>
        <div class="profile"><span class="avatar">SA</span><span class="profile-copy"><strong>{{ auth()->user()->name }}</strong><small>Administrasi Sistem</small></span></div><form method="post" action="{{ route('logout') }}" class="logout-form">@csrf<button class="exit-link" type="submit">Keluar</button></form>
    </div>
</header>
<nav class="main-nav" id="main-nav" aria-label="Navigasi Super Admin">
    <a class="nav-item {{ $activeView === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-icon">⌂</span>Ringkasan</a>
    <a class="nav-item {{ $activeView === 'accounts' ? 'active' : '' }}" href="{{ route('admin.index', ['view' => 'accounts']) }}"><span class="nav-icon">♙</span>Manajemen Akun</a>
    <a class="nav-item {{ $activeView === 'cpcl' ? 'active' : '' }}" href="{{ route('admin.index', ['view' => 'cpcl']) }}"><span class="nav-icon">▤</span>Data CPCL</a>
    <a class="nav-item {{ $activeView === 'imports' ? 'active' : '' }}" href="{{ route('admin.index', ['view' => 'imports']) }}"><span class="nav-icon">⇧</span>Impor CPCL</a>
    <a class="nav-item {{ $activeView === 'audit' ? 'active' : '' }}" href="{{ route('admin.index', ['view' => 'audit']) }}"><span class="nav-icon">◷</span>Audit Log</a>
</nav>
<div class="sidebar-backdrop" id="sidebar-backdrop" hidden></div>
<main class="workspace">
    @php($viewLabels = ['accounts' => ['Manajemen Akun', 'Catat calon pengguna, verifikasi PIC, dan kelola status akses akun.'], 'cpcl' => ['Data CPCL', 'Kelola referensi kelompok tani dan identitas wilayah.'], 'imports' => ['Impor CPCL', 'Unggah, validasi, pratinjau, lalu konfirmasi penambahan CPCL.'], 'audit' => ['Audit log', 'Telusuri tindakan administratif tanpa mengubah catatan audit.']])
    @php($heading = $viewLabels[$activeView] ?? $viewLabels['accounts'])
    <div class="page-heading {{ $activeView === 'audit' ? 'audit-page-heading' : '' }}"><div><div class="breadcrumb"><span>Ruang Administrasi</span><span>/</span><strong>{{ $heading[0] }}</strong></div><h1>{{ $heading[0] }}</h1><p>{{ $heading[1] }}</p></div>
        <div class="heading-actions">
            @if ($activeView === 'audit')
                <span class="audit-updated"><span></span>Pembaruan · {{ now()->translatedFormat('d M Y') }}</span>
            @else
                <span class="period-chip"><span></span> {{ $totalUsers }} akun · {{ $cpclTotal }} CPCL</span>
                @if ($activeView === 'accounts')
                    <a class="btn btn-primary btn-sm" href="#account-create">＋ Tambah akun</a>
                @elseif ($activeView === 'cpcl')
                    <a class="btn btn-primary btn-sm" href="#cpcl-create">＋ Tambah CPCL</a>
                @elseif ($activeView === 'imports')
                    <a class="btn btn-outline btn-sm" href="{{ route('admin.cpcl.import.template') }}">Unduh template CSV</a>
                @endif
            @endif
        </div>
    </div>
    @if (session('status'))<div class="admin-alert" role="status">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="admin-error" role="alert"><strong>Periksa kembali:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
    @if ($activeView === 'audit')<div class="audit-info-banner"><span class="audit-info-icon" aria-hidden="true">i</span><div><strong>Mode audit operasional.</strong> Aktivitas yang terintegrasi dicatat oleh server dan disimpan di database. Tampilan ini hanya-baca untuk Super Admin; rahasia autentikasi tidak ditampilkan.</div></div>@endif

    @if ($activeView === 'accounts')
    <section id="view-accounts" class="view active">
        <section class="panel table-panel">
            <div class="panel-heading"><div><h2>Manajemen akun</h2><p>Catat calon pengguna, verifikasi PIC, dan kelola status akses.</p></div><span class="read-only-chip">{{ $users->count() }} akun ditampilkan</span></div>
            <details id="account-create" class="account-form-toggle" @if ($errors->any()) open @endif>
                <summary>＋ Tambah akun secara manual</summary>
                <p class="admin-form-note">Akun baru berstatus Menunggu aktivasi dan memakai password awal server. Sampaikan username/password lewat kanal aman. Pengguna wajib mengganti password saat login pertama; akun aktif setelah perubahan berhasil. Verifikasi PIC dicatat terpisah dan tidak menghalangi aktivasi.</p>
                <form method="post" action="{{ route('admin.users.store') }}" class="admin-form-grid">
                    @csrf
                    <label>Peran<select id="account-role" name="role" required><option value="">Pilih peran</option><option value="pelapor_daerah" @selected(old('role') === 'pelapor_daerah')>Pelapor Daerah</option><option value="verifikator_pusat" @selected(old('role') === 'verifikator_pusat')>Verifikator Pusat</option><option value="pimpinan_kementan" @selected(old('role') === 'pimpinan_kementan')>Pimpinan</option></select></label>
                    <label>Username<input name="username" type="text" value="{{ old('username') }}" required minlength="3" maxlength="80" pattern="[a-z0-9._-]+" placeholder="contoh: surabaya" autocomplete="off"></label>
                    <label>Nama PIC / pengguna<input name="name" type="text" value="{{ old('name') }}" required maxlength="255" placeholder="Nama lengkap"></label>
                    <label>Kontak PIC<input name="contact" type="text" value="{{ old('contact') }}" maxlength="50" placeholder="Nomor kontak (opsional)"></label>
                    <label>Email akun<input name="email" type="email" value="{{ old('email') }}" required maxlength="255" placeholder="nama@instansi.go.id" autocomplete="email"></label>
                    <label id="account-province-field">Provinsi<select id="account-province" name="provinsi"><option value="">Pilih provinsi</option>@foreach ($provinces as $province)<option value="{{ $province }}" @selected(old('provinsi') === $province)>{{ $province }}</option>@endforeach</select></label>
                    <label id="account-regency-field">Kabupaten/Kota<select id="account-regency" name="kabupaten"><option value="">Pilih kabupaten/kota</option></select></label>
                    <div class="form-wide admin-form-note">Untuk Pelapor, wilayah akun wajib ditentukan dan satu kabupaten hanya dapat memiliki satu akun Pelapor. Verifikator/Pimpinan menggunakan cakupan Pusat.</div>
                    <div class="form-wide form-actions"><button class="btn btn-primary" type="submit">Simpan akun</button></div>
                </form>
            </details>
            <form method="get" action="{{ route('admin.index') }}" class="toolbar account-filter">
                <input type="hidden" name="view" value="accounts">
                <label class="search-control"><span>⌕</span><input name="q" type="search" value="{{ $filters['q'] }}" placeholder="Cari username, PIC, provinsi, atau kabupaten"></label>
                <select class="filter-control" name="status"><option value="">Semua status</option><option value="pending" @selected($filters['status'] === 'pending')>Menunggu aktivasi</option><option value="pic_pending" @selected($filters['status'] === 'pic_pending')>Menunggu verifikasi PIC</option><option value="active" @selected($filters['status'] === 'active')>Aktif</option><option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option></select>
                <select class="filter-control" name="role"><option value="">Semua peran</option><option value="pelapor_daerah" @selected($filters['role'] === 'pelapor_daerah')>Pelapor Daerah</option><option value="verifikator_pusat" @selected($filters['role'] === 'verifikator_pusat')>Verifikator Pusat</option><option value="pimpinan_kementan" @selected($filters['role'] === 'pimpinan_kementan')>Pimpinan</option><option value="super_admin" @selected($filters['role'] === 'super_admin')>Super Admin</option></select>
                <button class="btn btn-outline btn-sm" type="submit">Filter</button><a class="btn btn-outline btn-sm" href="{{ route('admin.index', ['view' => 'accounts']) }}">Reset</a>
            </form>
            <div id="accounts-table">@if ($users->isNotEmpty())<div class="table-wrap" tabindex="0" role="region" aria-label="Tabel data; geser horizontal untuk melihat kolom lainnya"><table class="data-table"><thead><tr><th>ID Akun</th><th>Peran</th><th>Pengguna / PIC</th><th>Wilayah</th><th>Username</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
                @foreach ($users as $user)
                @php($isPending = $user->must_change_password)
                @php($isEnabled = $user->is_active && ! $isPending)
                @php($isReporter = $user->role?->code === 'pelapor_daerah')
                @php($picPending = $isReporter && filled($user->contact) && ! $user->pic_verified_at)
                @php($accountStatus = $isPending ? 'Menunggu aktivasi' : ($user->is_active ? 'Aktif' : 'Nonaktif'))
                <tr><td><span class="ticket">AK-{{ str_pad((string) $user->id, 3, '0', STR_PAD_LEFT) }}</span></td><td>{{ $user->role?->name ?? 'Tanpa role' }}</td><td><span class="cell-main">{{ $user->name }}</span><span class="cell-sub">{{ $user->contact ?: 'Belum diisi' }}</span></td><td>{{ $user->provinsi ? $user->provinsi.' · '.$user->kabupaten : 'Pusat' }}</td><td><span class="cell-main">{{ $user->username ?: '—' }}</span>@if ($user->email)<span class="cell-sub">{{ $user->email }}</span>@endif</td><td><span class="account-state {{ $isPending ? 'pending' : ($user->is_active ? 'active' : 'inactive') }}">{{ $accountStatus }}</span>@if ($picPending)<span class="account-state verification">Menunggu verifikasi</span>@elseif ($user->pic_verified_at)<span class="cell-sub">PIC terverifikasi</span>@endif</td><td><span class="row-actions">
                    @if ($picPending)<form method="post" action="{{ route('admin.users.verify-pic', $user) }}" onsubmit="return confirm('Verifikasi kontak PIC akun ini? Ini tidak mengaktifkan akun atau melewati ganti password.')">@csrf<button class="verify-action" type="submit">Verifikasi PIC</button></form>@endif
                    @if (! $isPending)<form method="post" action="{{ route('admin.users.reset-password', $user) }}" onsubmit="return confirm('Reset akses? Pengguna harus mengganti password saat login berikutnya.')">@csrf<button class="reset-action" type="submit">↻ Reset akses</button></form>@endif
                    @if (! $isPending && ! $user->is(auth()->user()))<form method="post" action="{{ route('admin.users.status', $user) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $isEnabled ? 0 : 1 }}"><button class="{{ $isEnabled ? 'deactivate-action' : 'activate-action' }}" type="submit">{{ $isEnabled ? '⊘ Nonaktifkan' : 'Aktifkan' }}</button></form>@endif
                    @if (! $user->is(auth()->user()))<form method="post" action="{{ route('admin.users.destroy', $user) }}" onsubmit="return confirm('Hapus akun ini dari daftar aktif? Riwayat laporan akan tetap disimpan.')">@csrf @method('DELETE')<button class="delete-action" type="submit">× Hapus</button></form>@else<span class="disabled-action">Akun Anda</span>@endif
                </span></td></tr>
                @endforeach
            </tbody></table></div><div class="table-bottom">{{ $users->count() }} akun ditampilkan · {{ $users->total() }} cocok · {{ $totalUsers }} total pengguna</div>{{ $users->links() }}@else<div class="filter-empty">Tidak ada akun yang cocok dengan filter.</div>@endif</div>
            <div class="admin-footnote"><strong>Keamanan:</strong> kredensial tidak ditampilkan. Akun nonaktif ditolak saat login dan dikeluarkan dari sesi terlindungi berikutnya.</div>
        </section>
    </section>
    @elseif ($activeView === 'cpcl')
    <section id="view-cpcl" class="view active">
        <section class="panel table-panel"><div class="panel-heading"><div><h2>Referensi CPCL</h2><p>Kelola data kelompok tani dan identitas wilayah pada satu referensi.</p></div></div>
            <details id="cpcl-create" class="account-form-toggle"><summary>＋ Tambah CPCL secara manual</summary>
                <form method="post" action="{{ route('admin.cpcl.store') }}" class="cpcl-admin-form">
                    @csrf
                    <div class="cpcl-fields-grid">
                    @foreach ([['no','No.'],['nama_di','Nama DI'],['kewenangan','Kewenangan'],['keterangan','Keterangan'],['provinsi','Provinsi'],['kabupaten','Kabupaten/Kota'],['kecamatan','Kecamatan'],['desa','Desa/Kelurahan'],['kel_tani','Kelompok Tani'],['nama_ketua','Nama Ketua'],['realisasi','Realisasi'],['luas_ha','Luas (ha)'],['x','X'],['y','Y']] as [$field,$label])<label>{{ $label }}@if (in_array($field,['nama_di','provinsi','kabupaten','kecamatan','desa','kel_tani'],true)<span class="required-mark">*</span>@endif<input name="{{ $field }}" value="{{ old($field) }}" @if (in_array($field,['nama_di','provinsi','kabupaten','kecamatan','desa','kel_tani'],true)) required @endif @if ($field==='luas_ha') type="number" min="0" step="0.01" @endif></label>@endforeach
                </div><div class="form-actions"><button class="btn btn-primary" type="submit">Simpan CPCL</button></div></form>
            </details>
            <form data-cascade-filters method="get" action="{{ route('admin.index') }}" class="toolbar"><input type="hidden" name="view" value="cpcl"><label class="search-control"><span>⌕</span><input name="q" value="{{ $cpclSearch }}" placeholder="Cari Nama DI, kelompok tani, atau wilayah"></label><select class="filter-control" name="provinsi" aria-label="Filter provinsi CPCL"><option value="">Semua provinsi</option>@foreach ($cpclProvinces as $province)<option value="{{ $province }}" @selected($cpclProvince === $province)>{{ $province }}</option>@endforeach</select><select class="filter-control" name="kabupaten" aria-label="Filter kabupaten CPCL"><option value="">Semua kabupaten/kota</option>@foreach ($cpclRegencies as $regency)<option value="{{ $regency }}" @selected($cpclRegency === $regency)>{{ $regency }}</option>@endforeach</select><a class="btn btn-outline btn-sm" href="{{ route('admin.index', ['view'=>'cpcl']) }}">Reset</a><button class="btn btn-outline btn-sm" type="submit">Cari</button></form>
            <div class="table-wrap" tabindex="0" role="region" aria-label="Tabel data; geser horizontal untuk melihat kolom lainnya"><table class="data-table"><thead><tr><th>No.</th><th>Nama DI</th><th>Provinsi</th><th>Kab/Kota</th><th>Kecamatan</th><th>Desa</th><th>Kel_Tani</th><th>Realisasi</th><th>Luas_ha</th></tr></thead><tbody>@forelse ($cpclRecords as $record)<tr><td>{{ $record->no ?? $record->id }}</td><td>{{ $record->nama_di }}</td><td>{{ $record->provinsi }}</td><td>{{ $record->kabupaten }}</td><td>{{ $record->kecamatan }}</td><td>{{ $record->desa }}</td><td>{{ $record->kel_tani }}</td><td>{{ $record->realisasi ?? '—' }}</td><td>{{ $record->luas_ha ?? '—' }}</td></tr>@empty<tr><td colspan="9"><div class="filter-empty">Belum ada data CPCL operasional.</div></td></tr>@endforelse</tbody></table></div>{{ $cpclRecords->links() }}
        </section>
    </section>
    @elseif ($activeView === 'imports')
    <section id="view-imports" class="view active"><section class="panel import-panel"><div class="panel-heading"><div><h2>Impor data CPCL</h2><p>Alur tambah saja: unggah, validasi, pratinjau, lalu konfirmasi.</p></div><div class="heading-actions"><a class="btn btn-outline btn-sm" href="{{ route('admin.cpcl.import.template') }}">Template CSV</a></div></div>
        <div class="import-warning"><strong>Perlindungan data lama</strong><span>Impor tidak pernah menimpa CPCL yang sudah ada. Formula/makro ditolak dan tidak dieksekusi. Maksimal 5.000 baris.</span></div>
        <div class="import-steps"><div class="import-step active"><span>1</span><strong>Unggah file</strong></div><div class="import-step"><span>2</span><strong>Validasi data</strong></div><div class="import-step"><span>3</span><strong>Pratinjau hasil</strong></div><div class="import-step"><span>4</span><strong>Konfirmasi</strong></div></div>
        <div class="import-stage"><form method="post" action="{{ route('admin.cpcl.import.preview') }}" enctype="multipart/form-data"><div class="import-upload"><label class="file-drop" for="import-file"><span class="upload-symbol">⇧</span><strong>Pilih file CSV atau Excel XLSX</strong><small>Template berisi 14 kolom CPCL. Maksimal 10 MB.</small><span class="btn btn-outline btn-sm">Pilih file</span><input id="import-file" type="file" name="file" accept=".csv,.xlsx" required></label></div>@csrf<div class="form-actions"><button class="btn btn-primary" type="submit">Unggah & validasi</button></div></form></div>
    </section></section>
    @else
    <section id="view-audit" class="view active">@include('admin.partials.audit-log')</section>
    @endif
</main>
<script>
(() => {
    const catalog = @json($regionCatalog);
    const province = document.getElementById('account-province');
    const regency = document.getElementById('account-regency');
    const role = document.getElementById('account-role');
    const previousRegency = @json(old('kabupaten', ''));
    if (!province || !regency || !role) return;
    const update = (selected = '') => {
        regency.replaceChildren(new Option('Pilih kabupaten/kota', ''));
        (catalog[province.value] || []).forEach(item => regency.add(new Option(item.label, item.value, false, item.value === selected)));
        const reporter = role.value === 'pelapor_daerah';
        document.getElementById('account-province-field').hidden = !reporter;
        document.getElementById('account-regency-field').hidden = !reporter;
        province.required = reporter;
        regency.required = reporter;
        if (!reporter) { province.value = ''; regency.value = ''; }
    };
    province.addEventListener('change', () => update());
    role.addEventListener('change', () => update());
    update(previousRegency);
    const accountCreate = document.getElementById('account-create');
    if (accountCreate && window.location.hash === '#account-create') accountCreate.open = true;
    document.querySelectorAll('a[href="#account-create"]').forEach(link => link.addEventListener('click', () => { if (accountCreate) accountCreate.open = true; }));
})();
</script>
<script src="{{ asset('sitera/sidebar.js') }}"></script>
<script src="{{ asset('sitera/filter-controls.js') }}"></script>
</body>
</html>
