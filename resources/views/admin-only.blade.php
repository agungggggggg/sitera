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
        .account-create-head{margin:0 0 13px}
        .account-create-head h3{margin:0;color:#173d4d;font-size:14px;font-weight:750;letter-spacing:-.01em}
        .account-create-head p{margin:5px 0 0;color:#7a939c;font-size:10px;line-height:1.6}
        .account-create-grid{margin-bottom:13px;grid-template-columns:repeat(auto-fit,minmax(190px,1fr))}
        .account-create-grid .account-email-field{grid-column:1}
        .account-create-note{margin:0 0 13px}
        .account-create-note strong{color:#3d6070}
        .account-create-actions{display:flex;justify-content:flex-end;gap:9px;flex-wrap:wrap;padding-top:13px;border-top:1px solid #e4eef2}
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
.row-actions{display:flex;align-items:center;justify-content:flex-end;gap:6px;flex-wrap:nowrap;white-space:nowrap;min-width:max-content}.row-actions form{display:inline-flex;margin:0}.row-actions button{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;padding:0;border:0;border-radius:4px;color:#fff;cursor:pointer;box-shadow:0 1px 2px #173b4921;transition:filter .15s ease,transform .15s ease}.row-actions button:hover{filter:brightness(1.12)}.row-actions button:active{transform:translateY(1px)}.row-actions button:focus-visible{outline:2px solid #173b49;outline-offset:2px}.row-actions svg{width:13px;height:13px;fill:none;stroke:currentColor;stroke-width:2;stroke-linecap:round;stroke-linejoin:round}.row-actions .verify-action{background:#1f7a5a}.row-actions .reset-action{background:#2b4c7e}.row-actions .deactivate-action{background:#c3ce3a;color:#3f4a08}.row-actions .activate-action{background:#c3ce3a;color:#3f4a08}.row-actions .delete-action{background:#b02626}.row-actions .disabled-action{display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:4px;background:#eef2f4;color:#93a3ad;font-size:9px;font-weight:700}        .acc-status{display:block;font-size:9px;font-weight:800;letter-spacing:.07em;text-transform:uppercase;line-height:1.55}.acc-status.active{color:#15803d}.acc-status.inactive{color:#6b7785}.acc-status.pending{color:#b45309}.acc-status.verification{color:#2c5282}.acc-status.verified{color:#9aa7b2}#accounts-table .data-table th{background:#e8edf2;color:#4a5568}#accounts-table .data-table th:last-child{text-align:right}#accounts-table .data-table th:last-child,#accounts-table .data-table td:last-child{padding-right:14px}
        .view-note{margin:0 18px 16px;padding:11px 13px;border:1px solid #e2ebef;border-radius:8px;background:#f8fbfc;color:#7d929b;font-size:9px;line-height:1.55}
        .admin-empty{padding:30px 15px;text-align:center;color:#82969e;font-size:10px}
        .logout-form{display:inline-flex;margin:0}.logout-form .exit-link{cursor:pointer}
        .nav-item{text-decoration:none}
        .table td{vertical-align:middle}
        @media(max-width:700px){.account-filter{align-items:stretch}.account-filter .search-control,.account-filter .filter-control{width:100%;min-width:0}.account-actions{justify-content:flex-start}.row-actions{gap:8px}.row-actions button{width:44px;height:44px;min-height:44px;padding:0;font-size:10px}.row-actions svg{width:16px;height:16px}}
        @media(max-width:560px){.audit-panel-heading{padding:15px 14px 12px}.audit-filter-form{padding:0 14px 12px}.audit-filter-main{grid-template-columns:minmax(0,1fr) minmax(0,1fr);gap:8px}.audit-search{grid-column:1/-1;height:44px}.audit-search input{height:44px;min-height:44px}.audit-select,.audit-reset{height:44px;min-height:44px}.audit-table th,.audit-table td{padding:11px 12px}}
    </style>
</head>
<body>
<div class="prototype-ribbon"><span class="live-dot"></span> ADMINISTRASI SISTEM <span class="ribbon-divider">•</span> DATA OPERASIONAL</div>
<header class="app-header">
    <a class="brand" href="{{ route('dashboard') }}" aria-label="SITERA beranda"><span class="brand-logo"><img src="{{ asset('sitera/sitera-logo.webp') }}" alt="Logo Irigasi Pertanian"></span><span class="brand-copy"><strong>SITERA</strong><small>Sistem Informasi Rekapitulasi & Pemantauan Kegiatan Irigasi Pertanian</small></span></a>
    <div class="header-right"><button class="sidebar-toggle" id="sidebar-toggle" type="button" aria-label="Buka menu navigasi" aria-controls="main-nav" aria-expanded="false"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 6h16M4 12h16M4 18h16" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg></button><span class="internal-tag"><span></span> Sistem internal</span>
        @include('partials.notification-button')
        <div class="profile"><span class="avatar">SA</span><span class="profile-copy"><strong>{{ auth()->user()->name }}</strong><small>Administrasi Sistem</small></span></div>@include('partials.logout-button')
    </div>
</header>
<nav class="main-nav" id="main-nav" aria-label="Navigasi Super Admin">
    <a class="nav-item {{ $activeView === 'dashboard' ? 'active' : '' }}" href="{{ route('dashboard') }}"><span class="nav-icon">@include('partials.icon', ['name' => 'home'])</span>Ringkasan</a>
    <a class="nav-item {{ $activeView === 'accounts' ? 'active' : '' }}" href="{{ route('admin.index', ['view' => 'accounts']) }}"><span class="nav-icon">@include('partials.icon', ['name' => 'users'])</span>Manajemen Akun</a>
    <a class="nav-item {{ $activeView === 'cpcl' ? 'active' : '' }}" href="{{ route('admin.index', ['view' => 'cpcl']) }}"><span class="nav-icon">@include('partials.icon', ['name' => 'file-text'])</span>Data CPCL</a>
    <a class="nav-item {{ $activeView === 'imports' ? 'active' : '' }}" href="{{ route('admin.index', ['view' => 'imports']) }}"><span class="nav-icon">@include('partials.icon', ['name' => 'upload'])</span>Impor CPCL</a>
    <a class="nav-item {{ $activeView === 'audit' ? 'active' : '' }}" href="{{ route('admin.index', ['view' => 'audit']) }}"><span class="nav-icon">@include('partials.icon', ['name' => 'clock'])</span>Audit Log</a>
</nav>
<div class="sidebar-backdrop" id="sidebar-backdrop" hidden></div>
<main class="workspace">
    @php($viewLabels = ['accounts' => ['Manajemen Akun', 'Catat calon pengguna, verifikasi PIC, dan kelola status akses akun.'], 'cpcl' => ['Data CPCL', 'Kelola data kelompok tani dan identitas wilayah berdasarkan 14 kolom sumber.'], 'imports' => ['Impor Data CPCL', 'Kelola data kelompok tani dan identitas wilayah berdasarkan 14 kolom sumber.'], 'audit' => ['Audit Log', 'Telusuri tindakan administratif tanpa mengubah catatan audit.']])
    @php($heading = $viewLabels[$activeView] ?? $viewLabels['accounts'])
    <div class="page-heading"><div><div class="breadcrumb"><span>Ruang Administrasi</span><span>/</span><strong>{{ $heading[0] }}</strong></div><h1>{{ $heading[0] }}</h1><p>{{ $heading[1] }}</p></div>
        @if (! in_array($activeView, ['cpcl', 'imports', 'audit'], true))
        <div class="heading-actions">
            <span class="period-chip"><span></span> {{ $totalUsers }} akun · {{ $cpclTotal }} CPCL</span>
            @if ($activeView === 'accounts')
                <a class="btn btn-primary btn-sm" href="#account-create">＋ Tambah akun</a>
            @endif
        </div>
        @endif
    </div>
    @if (session('status'))<div class="admin-alert" role="status">{{ session('status') }}</div>@endif
    @if ($errors->any())<div class="admin-error" role="alert"><strong>Periksa kembali:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif

    @if ($activeView === 'accounts')
    <section id="view-accounts" class="view active">
        <section class="panel table-panel">
            <div class="panel-heading"><div><h2>Manajemen akun</h2><p>Buat akun pengguna dan kelola status akses. Data PIC dilengkapi pengguna saat aktivasi pertama.</p></div><span class="read-only-chip">{{ $users->count() }} akun ditampilkan</span></div>
            <details id="account-create" class="account-form-toggle" @if ($errors->any()) open @endif>
                <summary>＋ Tambah akun secara manual</summary>
                <div class="account-create-head">
                    <h3>Buat akun pengguna</h3>
                    <p>Super Admin menetapkan peran dan wilayah, lalu memberikan email serta password bawaan kepada pengguna.</p>
                </div>
                <form id="account-create-form" method="post" action="{{ route('admin.users.store') }}" class="admin-form-grid account-create-grid">
                    @csrf
                    <label>Peran<select id="account-role" name="role" required><option value="">Pilih peran</option><option value="pelapor_daerah" @selected(old('role') === 'pelapor_daerah')>Pelapor Daerah</option><option value="verifikator_pusat" @selected(old('role') === 'verifikator_pusat')>Verifikator Pusat</option><option value="pimpinan_kementan" @selected(old('role') === 'pimpinan_kementan')>Pimpinan</option></select></label>
                    <label id="account-province-field">Provinsi<select id="account-province" name="provinsi"><option value="">Pilih provinsi</option>@foreach ($provinces as $province)<option value="{{ $province }}" @selected(old('provinsi') === $province)>{{ $province }}</option>@endforeach</select></label>
                    <label id="account-regency-field">Kabupaten/Kota<select id="account-regency" name="kabupaten"><option value="">Pilih kabupaten/kota</option></select></label>
                    <label class="account-email-field">Email<input id="account-email" name="email" type="email" value="{{ old('email') }}" required maxlength="255" placeholder="nama@instansi.go.id" autocomplete="email"></label>
                </form>
                <div class="admin-form-note account-create-note">
                    <strong>Password bawaan mengikuti konfigurasi server.</strong>
                    Sampaikan email dan password bawaan kepada pengguna melalui kanal aman. Saat login pertama, pengguna mengisi sendiri Nama PIC dan Nomor Kontak PIC (opsional) pada halaman aktivasi, lalu membuat password baru. Untuk Pelapor, satu kabupaten maksimal satu akun.
                </div>
                <div class="account-create-actions">
                    <button class="btn btn-outline" type="button" onclick="this.closest('details').open=false">Batal</button>
                    <button class="btn btn-primary" type="submit" form="account-create-form">Buat akun pengguna</button>
                </div>
            </details>
            <form method="get" action="{{ route('admin.index') }}" class="toolbar account-filter">
                <input type="hidden" name="view" value="accounts">
                <label class="search-control"><span>@include('partials.icon', ['name' => 'search'])</span><input name="q" type="search" value="{{ $filters['q'] }}" placeholder="Cari email, PIC, provinsi, atau kabupaten"></label>
                <select class="filter-control" name="status"><option value="">Semua status</option><option value="pending" @selected($filters['status'] === 'pending')>Menunggu aktivasi</option><option value="pic_pending" @selected($filters['status'] === 'pic_pending')>Menunggu verifikasi PIC</option><option value="active" @selected($filters['status'] === 'active')>Aktif</option><option value="inactive" @selected($filters['status'] === 'inactive')>Nonaktif</option></select>
                <select class="filter-control" name="role"><option value="">Semua peran</option><option value="pelapor_daerah" @selected($filters['role'] === 'pelapor_daerah')>Pelapor Daerah</option><option value="verifikator_pusat" @selected($filters['role'] === 'verifikator_pusat')>Verifikator Pusat</option><option value="pimpinan_kementan" @selected($filters['role'] === 'pimpinan_kementan')>Pimpinan</option><option value="super_admin" @selected($filters['role'] === 'super_admin')>Super Admin</option></select>
                <button class="btn btn-outline btn-sm" type="submit">Filter</button><a class="btn btn-outline btn-sm" href="{{ route('admin.index', ['view' => 'accounts']) }}">Reset</a>
            </form>
            <div id="accounts-table">@if ($users->isNotEmpty())<div class="table-wrap" tabindex="0" role="region" aria-label="Tabel data; geser horizontal untuk melihat kolom lainnya"><table class="data-table"><thead><tr><th>ID Akun</th><th>Peran</th><th>PIC</th><th>Kontak HP</th><th>Wilayah</th><th>Email</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
                @foreach ($users as $user)
                @php($isPending = $user->must_change_password)
                @php($isEnabled = $user->is_active && ! $isPending)
                @php($isReporter = $user->role?->code === 'pelapor_daerah')
                @php($picPending = $isReporter && (filled($user->contact) || ! $isPending) && ! $user->pic_verified_at)
                @php($accountStatus = $isPending ? 'Menunggu aktivasi' : ($user->is_active ? 'Aktif' : 'Nonaktif'))
                @php($statusLabel = $isPending ? 'Menunggu aktivasi' : ($picPending ? 'Verifikasi PIC' : ($user->is_active ? 'Aktif' : 'Nonaktif')))
                @php($statusTone = $isPending ? 'pending' : ($picPending ? 'verification' : ($user->is_active ? 'active' : 'inactive')))
                <tr><td><span class="ticket">AK-{{ str_pad((string) $user->id, 3, '0', STR_PAD_LEFT) }}</span></td><td>{{ $user->role?->name ?? 'Tanpa role' }}</td><td>@if ($isPending)<span class="cell-sub">Belum diaktivasi</span>@else<span class="cell-main">{{ $user->name }}</span>@endif</td><td>@if ($isPending)<span class="cell-sub">Belum diaktivasi</span>@elseif (filled($user->contact))<span class="cell-main">{{ $user->contact }}</span>@else<span class="cell-sub">Tidak diisi</span>@endif</td><td>{{ $user->provinsi ? $user->provinsi.' · '.$user->kabupaten : 'Pusat' }}</td><td><span class="cell-main">{{ $user->email }}</span>@if (filled($user->username))<span class="cell-sub">{{ $user->username }}</span>@endif</td><td><span class="acc-status {{ $statusTone }}">{{ $statusLabel }}</span></td><td><span class="row-actions">
                    @if ($picPending)<form method="post" action="{{ route('admin.users.verify-pic', $user) }}" onsubmit='return confirm({{ Illuminate\Support\Js::from('Verifikasi kontak PIC '.($user->contact ?: 'yang tercatat').' untuk akun ini? Ini tidak mengaktifkan akun atau melewati ganti password.') }})'>@csrf<button class="verify-action" type="submit" title="Verifikasi PIC{{ $user->contact ? ': '.$user->contact : '' }}" aria-label="Verifikasi PIC"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20 6 9 17l-5-5"/></svg></button></form>@endif
                    @if (! $isPending)<form method="post" action="{{ route('admin.users.reset-password', $user) }}" onsubmit="return confirm('Reset akses? Pengguna harus mengganti password saat login berikutnya.')">@csrf<button class="reset-action" type="submit" title="Reset akun" aria-label="Reset akun"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21 12a9 9 0 1 1-3.2-6.9"/><path d="M21 3.5V10h-6.5"/></svg></button></form>@endif
                    @if (! $isPending && ! $user->is(auth()->user()))<form method="post" action="{{ route('admin.users.status', $user) }}">@csrf @method('PATCH')<input type="hidden" name="is_active" value="{{ $isEnabled ? 0 : 1 }}"><button class="{{ $isEnabled ? 'deactivate-action' : 'activate-action' }}" type="submit" title="{{ $isEnabled ? 'Nonaktifkan akun' : 'Aktifkan akun' }}" aria-label="{{ $isEnabled ? 'Nonaktifkan akun' : 'Aktifkan akun' }}"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 3v9"/><path d="M18.4 6.6a9 9 0 1 1-12.8 0"/></svg></button></form>@endif
                    @if (! $user->is(auth()->user()))<form method="post" action="{{ route('admin.users.destroy', $user) }}" data-delete-account-form data-account-label="{{ $user->email }}">@csrf @method('DELETE')<button class="delete-action" type="submit" title="Hapus akun" aria-label="Hapus akun"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16"/><path d="M10 11v6"/><path d="M14 11v6"/><path d="M6 7l1 13h10l1-13"/><path d="M9 7V4h6v3"/></svg></button></form>@else<span class="disabled-action" title="Akun Anda">—</span>@endif
                </span></td></tr>
                @endforeach
            </tbody></table></div><div class="table-bottom">{{ $users->count() }} akun ditampilkan · {{ $users->total() }} cocok · {{ $totalUsers }} total pengguna</div>{{ $users->links() }}@else<div class="filter-empty">Tidak ada akun yang cocok dengan filter.</div>@endif</div>
            <div class="admin-footnote"><strong>Keamanan:</strong> kredensial tidak ditampilkan. Akun nonaktif ditolak saat login dan dikeluarkan dari sesi terlindungi berikutnya.</div>
        </section>
    </section>
    <div id="account-delete-modal" class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="account-delete-title" hidden>
        <div class="admin-modal-card account-confirm-card">
            <div class="account-confirm-top">
                <button type="button" id="account-delete-back" class="account-confirm-back" aria-label="Kembali" title="Kembali">
                    <svg viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M5 12h13m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                    </svg>
                </button>
            </div>
            <div class="account-confirm-body">
                <p id="account-delete-title">Yakin mau menghapus Akun <strong id="account-delete-target">-</strong></p>
            </div>
            <div class="account-confirm-actions">
                <button type="button" id="account-delete-cancel" class="account-confirm-cancel">Batal</button>
                <button type="button" id="account-delete-confirm" class="account-confirm-submit">Hapus</button>
            </div>
        </div>
    </div>
    @elseif ($activeView === 'cpcl')
    @php($cpclFormOpen = $errors->hasAny(['no', 'nama_di', 'kewenangan', 'keterangan', 'provinsi', 'kabupaten', 'kecamatan', 'desa', 'kel_tani', 'nama_ketua', 'realisasi', 'luas_ha', 'x', 'y']) || old('nama_di') !== null || old('provinsi') !== null || old('kabupaten') !== null || old('kecamatan') !== null || old('desa') !== null || old('kel_tani') !== null)
    @php($cpclFields = [
        ['no', 'Nomor', false],
        ['nama_di', 'Nama DI', true],
        ['kewenangan', 'Kewenangan', false],
        ['keterangan', 'Keterangan', false],
        ['provinsi', 'Provinsi', true],
        ['kabupaten', 'Kabupaten/Kota', true],
        ['kecamatan', 'Kecamatan', true],
        ['desa', 'Desa/Kelurahan', true],
        ['kel_tani', 'Kelompok Tani', true],
        ['nama_ketua', 'Nama Ketua', false],
        ['realisasi', 'Realisasi', false],
        ['luas_ha', 'Luas (ha)', false],
        ['x', 'X', false],
        ['y', 'Y', false],
    ])
    <section id="view-cpcl" class="view active">
        <section class="panel table-panel cpcl-workspace-panel">
            <div class="cpcl-panel-head">
                <div>
                    <h2>Tambah CPCL secara manual</h2>
                    <p>Tambahkan baris data CPCL baru ke referensi. Data lama tidak ditimpa.</p>
                </div>
                <button type="button" id="cpcl-toggle-form" class="btn btn-primary btn-sm cpcl-add-btn" aria-expanded="{{ $cpclFormOpen ? 'true' : 'false' }}" aria-controls="cpcl-create-panel">＋ Tambah CPCL</button>
            </div>
            <div id="cpcl-create-panel" class="cpcl-inner-card" @unless ($cpclFormOpen) hidden @endunless>
                <div class="cpcl-inner-head">
                    <h3>Referensi CPCL</h3>
                    <p>Kelola data kelompok tani dan identitas wilayah pada satu referensi.</p>
                </div>
                <form id="cpcl-create-form" method="post" action="{{ route('admin.cpcl.store') }}" class="cpcl-admin-form">
                    @csrf
                    <div class="cpcl-entry-grid">
                        @foreach ($cpclFields as [$field, $label, $required])
                            @php($value = old($field))
                            <div class="cpcl-float-field {{ filled($value) ? 'has-value' : '' }}">
                                <input id="cpcl-field-{{ $field }}" name="{{ $field }}" value="{{ $value }}" placeholder=" " @if ($required) required @endif @if ($field === 'luas_ha') type="number" min="0" step="0.01" @else type="text" @endif>
                                <label for="cpcl-field-{{ $field }}">{{ $label }}@if ($required) <span class="required-mark">*</span>@endif</label>
                            </div>
                        @endforeach
                    </div>
                    <div class="cpcl-entry-footer">
                        <p class="cpcl-entry-note">Kunci identitas unik: Provinsi + Kab/Kota + Kecamatan + Desa/Kelurahan + Kelompok Tani. Kolom bertanda * wajib diisi; luas dan koordinat X/Y diisi angka bila tersedia.</p>
                        <div class="cpcl-entry-actions">
                            <button type="button" id="cpcl-cancel-form" class="btn btn-outline btn-sm">Batal</button>
                            <button type="submit" class="btn btn-primary btn-sm">Simpan CPCL</button>
                        </div>
                    </div>
                </form>
            </div>
            <form data-cascade-filters method="get" action="{{ route('admin.index') }}" class="toolbar cpcl-toolbar">
                <input type="hidden" name="view" value="cpcl">
                <div class="cpcl-search-wrap">
                    <input name="q" type="search" value="{{ $cpclSearch }}" placeholder="Cari nama DI, Kelompok Tani atau Wilayah" aria-label="Cari nama DI, Kelompok Tani atau Wilayah">
                    <button class="cpcl-search-submit" type="submit" title="Cari CPCL" aria-label="Cari CPCL">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.6"/><path d="m16 16 4.4 4.4"/></svg>
                    </button>
                </div>
                <select class="filter-control" name="provinsi" aria-label="Filter provinsi CPCL"><option value="">Semua Provinsi</option>@foreach ($cpclProvinces as $province)<option value="{{ $province }}" @selected($cpclProvince === $province)>{{ $province }}</option>@endforeach</select>
                <select class="filter-control" name="kabupaten" aria-label="Filter kabupaten CPCL"><option value="">Semua Kabupaten/Kota</option>@foreach ($cpclRegencies as $regency)<option value="{{ $regency }}" @selected($cpclRegency === $regency)>{{ $regency }}</option>@endforeach</select>
                <a class="btn btn-outline btn-sm cpcl-reset-btn" href="{{ route('admin.index', ['view' => 'cpcl']) }}">Reset</a>
            </form>
            <div id="cpcl-table">
                @if ($cpclRecords->isNotEmpty())
                <div class="table-wrap" tabindex="0" role="region" aria-label="Tabel data CPCL; geser horizontal untuk melihat kolom lainnya">
                    <table class="data-table">
                        <thead>
                            <tr><th>NO</th><th>NAMA DI</th><th>KEWENANGAN</th><th>KETERANGAN</th><th>PROVINSI</th><th>KAB/KOTA</th><th>KECAMATAN</th><th>DESA/KELURAHAN</th><th>KELOMPOK TANI</th><th>AKSI</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($cpclRecords as $record)
                            @php($detailPayload = [
                                'id' => $record->id,
                                'no' => $record->no ?? '-',
                                'nama_di' => $record->nama_di,
                                'kewenangan' => $record->kewenangan ?: '-',
                                'keterangan' => $record->keterangan ?: '-',
                                'provinsi' => $record->provinsi,
                                'kabupaten' => $record->kabupaten,
                                'kecamatan' => $record->kecamatan,
                                'desa' => $record->desa,
                                'kel_tani' => $record->kel_tani,
                                'nama_ketua' => $record->nama_ketua ?: '-',
                                'realisasi' => $record->realisasi ?: '-',
                                'luas_ha' => $record->luas_ha !== null ? (string) $record->luas_ha : '-',
                                'x' => $record->x !== null ? (string) $record->x : '-',
                                'y' => $record->y !== null ? (string) $record->y : '-',
                            ])
                            <tr>
                                <td>{{ $record->no ?? $record->id }}</td>
                                <td><span class="cell-main">{{ $record->nama_di }}</span><span class="cell-sub">CPCL #{{ $record->id }}</span></td>
                                <td>{{ $record->kewenangan ?: '-' }}</td>
                                <td>{{ $record->keterangan ?: '-' }}</td>
                                <td>{{ $record->provinsi }}</td>
                                <td>{{ $record->kabupaten }}</td>
                                <td>{{ $record->kecamatan }}</td>
                                <td>{{ $record->desa }}</td>
                                <td>{{ $record->kel_tani }}</td>
                                <td>
                                    <span class="cpcl-row-actions">
                                        <button type="button" class="cpcl-act-btn cpcl-act-detail" data-cpcl-detail="{{ json_encode($detailPayload, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) }}">Detail</button>
                                        <form method="post" action="{{ route('admin.cpcl.destroy', $record) }}" onsubmit='return confirm({{ Illuminate\Support\Js::from("Hapus data CPCL ".$record->nama_di." (".$record->kel_tani.")?") }})'>
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="cpcl-act-btn cpcl-act-delete">Hapus</button>
                                        </form>
                                    </span>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="table-bottom">Menampilkan {{ $cpclRecords->firstItem() }}-{{ $cpclRecords->lastItem() }} dari {{ $cpclRecords->total() }} data CPCL ({{ $cpclTotal }} total)</div>
                {{ $cpclRecords->links() }}
                @else
                <div class="filter-empty">Belum ada data CPCL operasional yang sesuai dengan filter.</div>
                @endif
            </div>
        </section>
    </section>
    <div id="cpcl-detail-modal" class="admin-modal" role="dialog" aria-modal="true" aria-labelledby="cpcl-modal-title" hidden>
        <div class="admin-modal-card cpcl-modal-card">
            <div class="form-panel-title">
                <div>
                    <h3 id="cpcl-modal-title">Detail CPCL</h3>
                    <p id="cpcl-modal-sub" class="cpcl-modal-sub">Seluruh 14 kolom referensi CPCL.</p>
                </div>
                <button type="button" id="cpcl-modal-close" class="icon-close" aria-label="Tutup detail CPCL">×</button>
            </div>
            <div id="cpcl-detail-fields" class="cpcl-detail-grid cpcl-admin-detail-grid"></div>
            <div class="form-actions">
                <button type="button" id="cpcl-modal-close-btn" class="btn btn-outline btn-sm">Tutup</button>
            </div>
        </div>
    </div>
    @elseif ($activeView === 'imports')
    @php($importResult = session('import_result'))
    @php($importFields = [
        ['no', 'Nomor'],
        ['nama_di', 'Nama DI'],
        ['kewenangan', 'Kewenangan'],
        ['keterangan', 'Keterangan'],
        ['provinsi', 'Provinsi'],
        ['kabupaten', 'Kabupaten/Kota'],
        ['kecamatan', 'Kecamatan'],
        ['desa', 'Desa/Kelurahan'],
        ['kel_tani', 'Kelompok Tani'],
        ['nama_ketua', 'Nama Ketua'],
        ['realisasi', 'Realisasi'],
        ['luas_ha', 'Luas_ha'],
        ['x', 'X'],
        ['y', 'Y'],
    ])
    <section id="view-imports" class="view active">
        <section class="panel import-panel cpcl-import-panel">
            <div class="cpcl-import-head">
                <div>
                    <h2>Impor data CPCL</h2>
                    <p>Alur tambah saja: unggah file CSV, periksa pemetaan 14 kolom, validasi pratinjau, lalu konfirmasi.</p>
                </div>
                <a class="btn btn-outline btn-sm" href="{{ route('admin.cpcl.import.template') }}">Template Excel (.xlsx)</a>
            </div>
            <div class="cpcl-import-stepper" aria-label="Tahapan impor CSV CPCL">
                <div class="cpcl-import-step {{ $importResult ? 'done' : 'active' }}" data-import-step="1"><span>1</span><strong>Unggah File</strong></div>
                <div class="cpcl-import-step {{ $importResult ? 'done' : '' }}" data-import-step="2"><span>2</span><strong>Pemetaan 14 Kolom</strong></div>
                <div class="cpcl-import-step {{ $importResult ? 'done' : '' }}" data-import-step="3"><span>3</span><strong>Validasi &amp; Pratinjau</strong></div>
                <div class="cpcl-import-step {{ $importResult ? 'active' : '' }}" data-import-step="4"><span>4</span><strong>Konfirmasi Penambahan</strong></div>
            </div>

            @if ($importResult)
                <div class="cpcl-import-stage" id="cpcl-import-step-4">
                    <div class="cpcl-import-cards">
                        <article class="cpcl-import-card is-valid">
                            <span>Ditambahkan</span>
                            <strong>{{ $importResult['added'] }}</strong>
                            <small>Baris baru disimpan</small>
                        </article>
                        <article class="cpcl-import-card">
                            <span>Duplikat</span>
                            <strong>{{ $importResult['duplicate'] }}</strong>
                            <small>Identik dengan data lama</small>
                        </article>
                        <article class="cpcl-import-card is-conflict">
                            <span>Konflik</span>
                            <strong>{{ $importResult['conflict'] }}</strong>
                            <small>Data lama tidak ditimpa</small>
                        </article>
                        <article class="cpcl-import-card is-invalid">
                            <span>Tidak valid</span>
                            <strong>{{ $importResult['invalid'] }}</strong>
                            <small>Kolom wajib atau format salah</small>
                        </article>
                    </div>
                    <div class="cpcl-import-outcome">
                        <p>
                            @if ($importResult['added'] > 0)
                                Sebanyak {{ $importResult['added'] }} baris CPCL baru berhasil ditambahkan ke referensi. Data CPCL lama yang sudah ada tidak ditimpa maupun diubah.
                            @else
                                Seluruh baris pada file merupakan duplikat, konflik, atau tidak valid sehingga tidak ada data CPCL baru yang ditambahkan.
                            @endif
                        </p>
                        <p>File: {{ $importResult['source_name'] }} · Waktu: {{ $importResult['confirmed_at'] }} WIB</p>
                    </div>
                    <div class="cpcl-import-bar">
                        <div class="cpcl-import-actions">
                            <a class="btn btn-outline btn-sm" href="{{ route('admin.index', ['view' => 'audit']) }}">Lihat audit log</a>
                            <a class="btn btn-primary btn-sm" href="{{ route('admin.index', ['view' => 'imports']) }}">Impor file lain</a>
                        </div>
                    </div>
                </div>
            @else
                <div id="cpcl-csv-client-error" class="cpcl-inline-error" role="alert" hidden></div>
                <form id="cpcl-import-form" method="post" action="{{ route('admin.cpcl.import.preview') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="cpcl-import-stage" id="cpcl-import-step-1">
                        <label class="cpcl-csv-dropzone" id="cpcl-csv-dropzone" for="import-file">
                            <span class="cpcl-csv-upload-icon" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round"><path d="M12 16V4"/><path d="m7 9 5-5 5 5"/><path d="M4 20h16"/></svg>
                            </span>
                            <strong>Pilih file CSV</strong>
                            <small>Format unggahan yang didukung: .csv. Jika mengisi menggunakan Template Excel (.xlsx), simpan terlebih dahulu sebagai CSV (.csv) sebelum diunggah (maks. 10 MB, 5.000 baris).</small>
                            <span class="btn btn-outline btn-sm cpcl-csv-choose-btn">Pilih File</span>
                            <input id="import-file" type="file" name="file" accept=".csv,text/csv" required>
                        </label>
                        <noscript>
                            <div class="form-actions"><button class="btn btn-primary btn-sm" type="submit">Validasi &amp; Pratinjau</button></div>
                        </noscript>
                    </div>
                    <div class="cpcl-import-stage" id="cpcl-import-step-2" hidden>
                        <div class="cpcl-csv-meta-box">
                            <div class="cpcl-csv-meta-copy">
                                <strong id="cpcl-csv-filename">file.csv</strong>
                                <span id="cpcl-csv-meta-detail">0 baris data terbaca</span>
                            </div>
                            <button type="button" id="cpcl-csv-change" class="btn btn-outline btn-sm">Ganti file</button>
                        </div>
                        <div class="cpcl-csv-map-card">
                            <div class="cpcl-entry-grid">
                                @foreach ($importFields as [$fieldKey, $fieldLabel])
                                    <div class="cpcl-float-field has-value">
                                        <input id="cpcl-import-preview-{{ $fieldKey }}" type="text" data-csv-preview-field="{{ $fieldKey }}" placeholder=" " value="-" readonly>
                                        <label for="cpcl-import-preview-{{ $fieldKey }}">{{ $fieldLabel }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="cpcl-import-bar">
                            <div id="cpcl-csv-pager" class="cpcl-csv-pager" aria-label="Navigasi baris pratinjau CSV"></div>
                            <div class="cpcl-import-actions">
                                <button type="button" id="cpcl-csv-cancel" class="btn btn-outline btn-sm">Batal</button>
                                <button type="submit" class="btn btn-primary btn-sm">Validasi &amp; Pratinjau</button>
                            </div>
                        </div>
                    </div>
                </form>
            @endif
        </section>
    </section>
    @else
    <section id="view-audit" class="view active">@include('admin.partials.audit-log', ['suppressAuditErrors' => true])</section>
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
(() => {
    const toggleBtn = document.getElementById('cpcl-toggle-form');
    const cancelBtn = document.getElementById('cpcl-cancel-form');
    const panel = document.getElementById('cpcl-create-panel');
    const form = document.getElementById('cpcl-create-form');
    const setPanelOpen = (open) => {
        if (!panel || !toggleBtn) return;
        panel.hidden = !open;
        toggleBtn.setAttribute('aria-expanded', String(open));
        if (open) {
            panel.querySelector('input')?.focus();
        }
    };
    if (toggleBtn && panel) {
        toggleBtn.addEventListener('click', () => setPanelOpen(panel.hidden));
        if (window.location.hash === '#cpcl-create') setPanelOpen(true);
    }
    if (cancelBtn) {
        cancelBtn.addEventListener('click', () => {
            form?.reset();
            document.querySelectorAll('.cpcl-float-field').forEach(wrap => wrap.classList.remove('has-value'));
            setPanelOpen(false);
            toggleBtn?.focus();
        });
    }
    document.querySelectorAll('.cpcl-float-field input').forEach(input => {
        const sync = () => input.closest('.cpcl-float-field')?.classList.toggle('has-value', input.value.trim() !== '');
        input.addEventListener('input', sync);
        input.addEventListener('change', sync);
        sync();
    });

    const modal = document.getElementById('cpcl-detail-modal');
    const modalTitle = document.getElementById('cpcl-modal-title');
    const modalSub = document.getElementById('cpcl-modal-sub');
    const modalFields = document.getElementById('cpcl-detail-fields');
    const closeBtn = document.getElementById('cpcl-modal-close');
    const closeFooterBtn = document.getElementById('cpcl-modal-close-btn');
    let lastTrigger = null;

    const fieldLabels = [
        ['no', 'No.'],
        ['nama_di', 'Nama DI'],
        ['kewenangan', 'Kewenangan'],
        ['keterangan', 'Keterangan'],
        ['provinsi', 'Provinsi'],
        ['kabupaten', 'Kabupaten/Kota'],
        ['kecamatan', 'Kecamatan'],
        ['desa', 'Desa/Kelurahan'],
        ['kel_tani', 'Kelompok Tani'],
        ['nama_ketua', 'Nama Ketua'],
        ['realisasi', 'Realisasi'],
        ['luas_ha', 'Luas (ha)'],
        ['x', 'X'],
        ['y', 'Y'],
    ];

    const closeModal = () => {
        if (!modal || modal.hidden) return;
        modal.hidden = true;
        lastTrigger?.focus();
        lastTrigger = null;
    };

    if (modal && modalFields) {
        document.querySelectorAll('[data-cpcl-detail]').forEach(button => {
            button.addEventListener('click', () => {
                let record;
                try {
                    record = JSON.parse(button.getAttribute('data-cpcl-detail') || '{}');
                } catch (_) {
                    return;
                }
                lastTrigger = button;
                if (modalTitle) modalTitle.textContent = 'Detail CPCL · ' + (record.nama_di || '-');
                if (modalSub) modalSub.textContent = 'CPCL #' + (record.id || '-') + ' · Seluruh 14 kolom referensi CPCL.';
                modalFields.replaceChildren(...fieldLabels.map(([key, label]) => {
                    const box = document.createElement('div');
                    box.className = 'detail-field';
                    const small = document.createElement('small');
                    small.textContent = label;
                    const strong = document.createElement('strong');
                    strong.textContent = record[key] || '-';
                    box.append(small, strong);
                    return box;
                }));
                modal.hidden = false;
                closeBtn?.focus();
            });
        });

        closeBtn?.addEventListener('click', closeModal);
        closeFooterBtn?.addEventListener('click', closeModal);
        modal.addEventListener('click', (event) => {
            if (event.target === modal) closeModal();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !modal.hidden) closeModal();
        });
    }

    const deleteModal = document.getElementById('account-delete-modal');
    const deleteTarget = document.getElementById('account-delete-target');
    const deleteBackBtn = document.getElementById('account-delete-back');
    const deleteCancelBtn = document.getElementById('account-delete-cancel');
    const deleteConfirmBtn = document.getElementById('account-delete-confirm');
    let pendingDeleteForm = null;
    let lastDeleteTrigger = null;

    const closeDeleteModal = () => {
        if (!deleteModal || deleteModal.hidden) return;
        deleteModal.hidden = true;
        pendingDeleteForm = null;
        if (deleteConfirmBtn) deleteConfirmBtn.disabled = false;
        lastDeleteTrigger?.focus();
        lastDeleteTrigger = null;
    };

    if (deleteModal && deleteTarget) {
        document.querySelectorAll('[data-delete-account-form]').forEach(form => {
            form.addEventListener('submit', (event) => {
                event.preventDefault();
                pendingDeleteForm = form;
                lastDeleteTrigger = form.querySelector('button[type="submit"]');
                deleteTarget.textContent = (form.getAttribute('data-account-label') || 'akun ini') + '?';
                deleteModal.hidden = false;
                deleteCancelBtn?.focus();
            });
        });

        deleteBackBtn?.addEventListener('click', closeDeleteModal);
        deleteCancelBtn?.addEventListener('click', closeDeleteModal);
        deleteModal.addEventListener('click', (event) => {
            if (event.target === deleteModal) closeDeleteModal();
        });
        deleteConfirmBtn?.addEventListener('click', () => {
            if (!pendingDeleteForm) return;
            deleteConfirmBtn.disabled = true;
            pendingDeleteForm.submit();
        });
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !deleteModal.hidden) closeDeleteModal();
        });
    }
})();
(() => {
    const importForm = document.getElementById('cpcl-import-form');
    const fileInput = document.getElementById('import-file');
    const dropzone = document.getElementById('cpcl-csv-dropzone');
    const step1 = document.getElementById('cpcl-import-step-1');
    const step2 = document.getElementById('cpcl-import-step-2');
    const clientError = document.getElementById('cpcl-csv-client-error');
    const fileNameEl = document.getElementById('cpcl-csv-filename');
    const metaDetailEl = document.getElementById('cpcl-csv-meta-detail');
    const pagerEl = document.getElementById('cpcl-csv-pager');
    const changeBtn = document.getElementById('cpcl-csv-change');
    const cancelBtn = document.getElementById('cpcl-csv-cancel');
    if (!importForm || !fileInput || !step1 || !step2) return;

    const aliases = {
        no: ['no', 'nomor'],
        nama_di: ['namadi', 'namadaerahirigasi'],
        kewenangan: ['kewenangan'],
        keterangan: ['keterangan'],
        provinsi: ['provinsi'],
        kabupaten: ['kabkota', 'kabupatenkota', 'kabupaten', 'kota'],
        kecamatan: ['kecamatan'],
        desa: ['desakelurahan', 'desa', 'kelurahan'],
        kel_tani: ['keltani', 'kelompoktani'],
        nama_ketua: ['namaketua'],
        realisasi: ['realisasi'],
        luas_ha: ['luasha', 'luas'],
        x: ['x'],
        y: ['y'],
    };
    const fieldKeys = Object.keys(aliases);
    let parsedRows = [];
    let currentIndex = 0;

    const showError = (message) => {
        if (!clientError) return;
        clientError.textContent = message;
        clientError.hidden = false;
    };

    const clearError = () => {
        if (!clientError) return;
        clientError.textContent = '';
        clientError.hidden = true;
    };

    const setStep = (activeStep) => {
        document.querySelectorAll('[data-import-step]').forEach(el => {
            const stepNo = Number(el.getAttribute('data-import-step'));
            el.classList.toggle('done', stepNo < activeStep);
            el.classList.toggle('active', stepNo === activeStep);
        });
        step1.hidden = activeStep !== 1;
        step2.hidden = activeStep !== 2;
    };

    const detectDelimiter = (text) => {
        const lines = text.split(/\r\n|\n|\r/).filter(line => line.trim() !== '').slice(0, 10);
        let commaScore = 0;
        let semicolonScore = 0;
        for (const line of lines) {
            const unquoted = line.replace(/"[^"]*"/g, '');
            commaScore += (unquoted.match(/,/g) || []).length;
            semicolonScore += (unquoted.match(/;/g) || []).length;
        }
        return semicolonScore > commaScore ? ';' : ',';
    };

    const parseCsvMatrix = (rawText) => {
        const text = rawText.replace(/^\uFEFF/, '');
        const delimiter = detectDelimiter(text);
        const matrix = [];
        let row = [];
        let cell = '';
        let inQuotes = false;

        for (let i = 0; i < text.length; i++) {
            const ch = text[i];
            if (inQuotes) {
                if (ch === '"' && text[i + 1] === '"') {
                    cell += '"';
                    i++;
                } else if (ch === '"') {
                    inQuotes = false;
                } else {
                    cell += ch;
                }
            } else if (ch === '"') {
                inQuotes = true;
            } else if (ch === delimiter) {
                row.push(cell.trim());
                cell = '';
            } else if (ch === '\n') {
                row.push(cell.trim());
                matrix.push(row);
                row = [];
                cell = '';
            } else if (ch !== '\r') {
                cell += ch;
            }
        }
        if (cell.length > 0 || row.length > 0) {
            row.push(cell.trim());
            matrix.push(row);
        }
        return matrix;
    };

    const matchHeaderRow = (row) => {
        const candidate = {};
        row.forEach((label, col) => {
            const normalized = String(label || '').toLowerCase().replace(/[^a-z0-9]/g, '');
            for (const field of fieldKeys) {
                if (aliases[field].includes(normalized)) {
                    candidate[field] = col;
                }
            }
        });
        if (
            candidate.nama_di !== undefined &&
            candidate.provinsi !== undefined &&
            candidate.kabupaten !== undefined &&
            candidate.kel_tani !== undefined
        ) {
            return candidate;
        }
        return null;
    };

    const renderRow = (index) => {
        currentIndex = index;
        const row = parsedRows[index] || {};
        fieldKeys.forEach(key => {
            const input = importForm.querySelector('[data-csv-preview-field="' + key + '"]');
            if (input) input.value = row[key] || '-';
        });
        renderPager();
    };

    const renderPager = () => {
        if (!pagerEl) return;
        pagerEl.replaceChildren();
        const total = parsedRows.length;
        if (total <= 1) return;

        const pages = [];
        if (total <= 5) {
            for (let i = 0; i < total; i++) pages.push(i);
        } else {
            pages.push(0);
            if (currentIndex > 2) pages.push('ellipsis');
            const start = Math.max(1, currentIndex - 1);
            const end = Math.min(total - 2, currentIndex + 1);
            for (let i = start; i <= end; i++) pages.push(i);
            if (currentIndex < total - 3) pages.push('ellipsis');
            pages.push(total - 1);
        }

        pages.forEach(item => {
            if (item === 'ellipsis') {
                const span = document.createElement('span');
                span.className = 'cpcl-page-ellipsis';
                span.textContent = '...';
                pagerEl.appendChild(span);
                return;
            }
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'cpcl-page-btn' + (item === currentIndex ? ' active' : '');
            btn.textContent = String(item + 1);
            btn.setAttribute('aria-label', 'Baris data ke-' + (item + 1));
            if (item === currentIndex) btn.setAttribute('aria-current', 'page');
            btn.addEventListener('click', () => renderRow(item));
            pagerEl.appendChild(btn);
        });
    };

    const resetImport = () => {
        importForm.reset();
        fileInput.value = '';
        parsedRows = [];
        currentIndex = 0;
        if (pagerEl) pagerEl.replaceChildren();
        clearError();
        setStep(1);
    };

    const handleFile = async (file) => {
        clearError();
        if (!file) return;
        if (!file.name.toLowerCase().endsWith('.csv')) {
            fileInput.value = '';
            showError('Format impor CPCL hanya mendukung file CSV (.csv).');
            return;
        }
        if (file.size > 10 * 1024 * 1024) {
            fileInput.value = '';
            showError('Ukuran file CSV maksimal 10 MB.');
            return;
        }

        const matrix = parseCsvMatrix(await file.text());
        if (matrix.length < 2) {
            fileInput.value = '';
            showError('File CSV harus berisi satu baris header dan minimal satu baris data.');
            return;
        }

        let headerIndex = -1;
        let mapping = null;
        for (let i = 0; i < Math.min(10, matrix.length); i++) {
            const matched = matchHeaderRow(matrix[i]);
            if (matched) {
                headerIndex = i;
                mapping = matched;
                break;
            }
        }
        if (!mapping) {
            fileInput.value = '';
            showError('Header CPCL tidak ditemukan. Gunakan template CSV SITERA dan pastikan kolom Nama DI, Provinsi, Kab_Kota, dan Kel_Tani tersedia.');
            return;
        }

        const rows = [];
        for (let i = headerIndex + 1; i < matrix.length; i++) {
            const rawRow = matrix[i];
            if (matchHeaderRow(rawRow)) continue;
            const record = {};
            let hasValue = false;
            fieldKeys.forEach(key => {
                const value = mapping[key] !== undefined ? String(rawRow[mapping[key]] || '').trim() : '';
                record[key] = value;
                if (value !== '') hasValue = true;
            });
            if (!hasValue) continue;
            rows.push(record);
            if (rows.length > 5000) {
                fileInput.value = '';
                showError('Maksimal 5.000 baris data per file.');
                return;
            }
        }
        if (rows.length === 0) {
            fileInput.value = '';
            showError('File CSV tidak memiliki baris data di bawah header.');
            return;
        }

        parsedRows = rows;
        if (fileNameEl) fileNameEl.textContent = file.name;
        if (metaDetailEl) metaDetailEl.textContent = rows.length + ' baris data terbaca · Header pada baris ' + (headerIndex + 1);
        setStep(2);
        renderRow(0);
    };

    fileInput.addEventListener('change', () => {
        const file = fileInput.files?.[0];
        if (file) handleFile(file);
    });

    if (dropzone) {
        ['dragenter', 'dragover'].forEach(evt => dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            dropzone.classList.add('is-dragover');
        }));
        ['dragleave', 'drop'].forEach(evt => dropzone.addEventListener(evt, (e) => {
            e.preventDefault();
            dropzone.classList.remove('is-dragover');
        }));
        dropzone.addEventListener('drop', (e) => {
            const file = e.dataTransfer?.files?.[0];
            if (!file) return;
            try {
                const dt = new DataTransfer();
                dt.items.add(file);
                fileInput.files = dt.files;
            } catch (_) {}
            handleFile(file);
        });
    }

    changeBtn?.addEventListener('click', () => fileInput.click());
    cancelBtn?.addEventListener('click', resetImport);
})();
</script>
<script src="{{ asset('sitera/sidebar.js') }}"></script>
<script src="{{ asset('sitera/filter-controls.js') }}"></script>
</body>
</html>
