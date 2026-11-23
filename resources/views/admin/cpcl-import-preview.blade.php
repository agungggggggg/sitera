<!doctype html>
<html lang="id">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#086288">
    <title>Validasi &amp; Pratinjau Impor CPCL — SITERA</title>
    <link rel="icon" type="image/webp" href="{{ asset('sitera/sitera-logo.webp') }}">
    <link rel="stylesheet" href="{{ asset('sitera/theme.css') }}">
    <link rel="stylesheet" href="{{ asset('sitera/pelapor-review.css') }}">
    <link rel="stylesheet" href="{{ asset('sitera/superadmin-review.css') }}">
    <link rel="stylesheet" href="{{ asset('css/mobile-shell.css') }}">
    <style>
        .logout-form {
            display: inline-flex;
            margin: 0
        }

        .logout-form .exit-link {
            cursor: pointer
        }

        .nav-item {
            text-decoration: none
        }

        .form-foot form {
            margin: 0
        }

        .form-foot {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
            padding: 16px 0 0
        }

        .form-foot button:disabled {
            opacity: .5;
            cursor: not-allowed
        }

        @media(max-width:480px){.form-foot{flex-direction:column;align-items:stretch;padding:12px;gap:9px}.form-foot form,.form-foot button{width:100%}.form-foot button{min-height:44px}}
    </style>
</head>

<body>
    <div class="prototype-ribbon"><span class="live-dot"></span> ADMINISTRASI SISTEM <span
            class="ribbon-divider">•</span> DATA OPERASIONAL</div>
    <header class="app-header">
        <a class="brand" href="{{ route('dashboard') }}" aria-label="SITERA beranda"><span class="brand-logo"><img
                    src="{{ asset('sitera/sitera-logo.webp') }}" alt="Logo Irigasi Pertanian"></span><span
                class="brand-copy"><strong>SITERA</strong><small>Sistem Informasi Rekapitulasi &amp; Pemantauan Kegiatan
                    Irigasi Pertanian</small></span></a>
        <div class="header-right"><button class="sidebar-toggle" id="sidebar-toggle" type="button"
                aria-label="Buka menu navigasi" aria-controls="main-nav" aria-expanded="false"><svg viewBox="0 0 24 24"
                    aria-hidden="true">
                    <path d="M4 6h16M4 12h16M4 18h16" fill="none" stroke="currentColor" stroke-width="1.8"
                        stroke-linecap="round" />
                </svg></button><span class="internal-tag"><span></span> Sistem internal</span>
            @include('partials.notification-button')
            <div class="profile"><span class="avatar">SA</span><span
                    class="profile-copy"><strong>{{ auth()->user()->name }}</strong><small>Administrasi
                        Sistem</small></span></div>@include('partials.logout-button')
        </div>
    </header>
    <nav class="main-nav" id="main-nav" aria-label="Navigasi Super Admin">
        <a class="nav-item" href="{{ route('dashboard') }}"><span class="nav-icon">@include('partials.icon', ['name' => 'home'])</span>Ringkasan</a>
        <a class="nav-item" href="{{ route('admin.index', ['view' => 'accounts']) }}"><span
                class="nav-icon">@include('partials.icon', ['name' => 'users'])</span>Manajemen Akun</a>
        <a class="nav-item" href="{{ route('admin.index', ['view' => 'cpcl']) }}"><span class="nav-icon">@include('partials.icon', ['name' => 'file-text'])</span>Data
            CPCL</a>
        <a class="nav-item active" href="{{ route('admin.index', ['view' => 'imports']) }}"><span
                class="nav-icon">@include('partials.icon', ['name' => 'upload'])</span>Impor CPCL</a>
        <a class="nav-item" href="{{ route('admin.index', ['view' => 'audit']) }}"><span class="nav-icon">@include('partials.icon', ['name' => 'clock'])</span>Audit
            Log</a>
    </nav>
    <div class="sidebar-backdrop" id="sidebar-backdrop" hidden></div>
    <main class="workspace">
        <div class="page-heading">
            <div>
                <div class="breadcrumb"><span>Ruang Administrasi</span><span>/</span><strong>Impor Data CPCL</strong>
                </div>
                <h1>Impor Data CPCL</h1>
                <p>File {{ $batch->source_name }} diproses sebagai data CSV. Tidak ada data yang tersimpan sampai
                    konfirmasi.</p>
            </div>
        </div>
        <section class="panel import-panel cpcl-import-panel">
            <div class="cpcl-import-head">
                <div>
                    <h2>Impor data CPCL</h2>
                    <p>Pratinjau hasil validasi sebelum menambahkan baris Baru valid ke referensi CPCL.</p>
                </div>
                <a class="btn btn-outline btn-sm" href="{{ route('admin.cpcl.import.template') }}">Template Excel
                    (.xlsx)</a>
            </div>
            <div class="cpcl-import-stepper" aria-label="Tahapan impor CSV CPCL">
                <div class="cpcl-import-step done"><span>1</span><strong>Unggah File</strong></div>
                <div class="cpcl-import-step done"><span>2</span><strong>Pengecekan</strong></div>
                <div class="cpcl-import-step active"><span>3</span><strong>Validasi &amp; Pratinjau</strong></div>
                <div class="cpcl-import-step"><span>4</span><strong>Konfirmasi Penambahan</strong></div>
            </div>
            <div class="cpcl-import-stage">
                <div class="cpcl-import-cards">
                    <article class="cpcl-import-card is-valid">
                        <span>Baru valid</span>
                        <strong>{{ $batch->summary['Baru valid'] ?? 0 }}</strong>
                        <small>Siap ditambahkan</small>
                    </article>
                    <article class="cpcl-import-card">
                        <span>Duplikat</span>
                        <strong>{{ $batch->summary['Duplikat'] ?? 0 }}</strong>
                        <small>Identik dengan data lama</small>
                    </article>
                    <article class="cpcl-import-card is-conflict">
                        <span>Konflik</span>
                        <strong>{{ $batch->summary['Konflik'] ?? 0 }}</strong>
                        <small>Data lama tidak ditimpa</small>
                    </article>
                    <article class="cpcl-import-card is-invalid">
                        <span>Tidak valid</span>
                        <strong>{{ $batch->summary['Tidak valid'] ?? 0 }}</strong>
                        <small>Kolom wajib atau format salah</small>
                    </article>
                </div>
                <div class="cpcl-preview-table-wrap">
                    <div class="table-wrap" tabindex="0" role="region"
                        aria-label="Tabel rincian validasi impor CPCL; geser horizontal untuk melihat kolom lainnya">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Baris</th>
                                    <th>Nama DI / Kelompok Tani</th>
                                    <th>Wilayah</th>
                                    <th>Validasi</th>
                                    <th>Alasan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($batch->rows as $row)
                                @php($badgeClass = Str::slug($row['_status'] ?? 'tidak-valid'))
                                <tr>
                                    <td>{{ $row['_row'] }}</td>
                                    <td>
                                        <span
                                            class="cell-main">{{ ($row['nama_di'] ?? '') !== '' ? $row['nama_di'] : '-' }}</span>
                                        <span
                                            class="cell-sub">{{ ($row['kel_tani'] ?? '') !== '' ? $row['kel_tani'] : '-' }}</span>
                                    </td>
                                    <td>
                                        <span
                                            class="cell-main">{{ ($row['kabupaten'] ?? '') !== '' ? $row['kabupaten'] : '-' }}
                                            · {{ ($row['provinsi'] ?? '') !== '' ? $row['provinsi'] : '-' }}</span>
                                        <span
                                            class="cell-sub">{{ ($row['kecamatan'] ?? '') !== '' ? $row['kecamatan'] : '-' }}
                                            · {{ ($row['desa'] ?? '') !== '' ? $row['desa'] : '-' }}</span>
                                    </td>
                                    <td><span class="import-row-status {{ $badgeClass }}">{{ $row['_status'] }}</span>
                                    </td>
                                    <td>{{ $row['_reason'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-foot">
                    <form method="post" action="{{ route('admin.cpcl.import.cancel', $batch) }}">@csrf<button
                            class="btn btn-outline btn-sm" type="submit">Batalkan impor</button></form>
                    <form method="post" action="{{ route('admin.cpcl.import.confirm', $batch) }}">@csrf<button
                            class="btn btn-primary btn-sm" type="submit" @disabled(($batch->summary['Baru valid'] ?? 0) < 1)>Konfirmasi tambah data baru valid</button></form>
                </div>
            </div>
        </section>
    </main>
    <script src="{{ asset('sitera/sidebar.js') }}"></script>
</body>

</html>