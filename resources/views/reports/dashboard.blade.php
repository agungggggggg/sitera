@extends('layouts.sitera-pelapor')

@section('title', 'Area Pelapor — SITERA')

@section('content')
<div class="page-heading"><div><div class="breadcrumb"><span>Ruang Pelapor</span><span>/</span><strong>Ringkasan</strong></div><h1>Ringkasan laporan</h1><p>Pantau status paket laporan irigasi untuk wilayah {{ auth()->user()->kabupaten ?: 'akun Anda' }}.</p></div><div class="heading-actions"><span class="period-chip"><span></span> Periode data · {{ now()->year }}</span><a class="btn btn-primary" href="{{ route('reports.create') }}"><span class="plus">＋</span> Buat Laporan</a></div></div>
@if (blank(auth()->user()->provinsi) || blank(auth()->user()->kabupaten))<div class="notice"><span class="notice-icon">!</span><span><strong>Wilayah akun belum ditetapkan.</strong> Hubungi Super Admin untuk memperbarui wilayah sebelum memilih CPCL.</span></div>@endif
<div class="metrics-grid" id="dashboard-metrics">
    <article class="metric"><div class="metric-top"><span>Total laporan</span><span class="metric-icon">▤</span></div><div class="metric-value">{{ $reports->count() }}</div><div class="metric-meta">Paket pada akun ini</div></article>
    <article class="metric"><div class="metric-top"><span>Draf</span><span class="metric-icon">✎</span></div><div class="metric-value">{{ $statusCounts['Draf'] }}</div><div class="metric-meta">Belum dikirim</div></article>
    <article class="metric"><div class="metric-top"><span>Menunggu pemeriksaan</span><span class="metric-icon">◷</span></div><div class="metric-value">{{ $statusCounts['Menunggu pemeriksaan'] }}</div><div class="metric-meta">Sedang dalam antrean</div></article>
    <article class="metric red"><div class="metric-top"><span>Tidak sesuai</span><span class="metric-icon">!</span></div><div class="metric-value">{{ $statusCounts['Tidak sesuai'] }}</div><div class="metric-meta">Memerlukan tindak lanjut</div></article>
    <article class="metric green"><div class="metric-top"><span>Sesuai</span><span class="metric-icon">✓</span></div><div class="metric-value">{{ $statusCounts['Sesuai'] }}</div><div class="metric-meta">Pemeriksaan selesai</div></article>
</div>
<div class="content-grid">
    <section class="panel"><div class="panel-heading"><div><h2>Aktivitas laporan</h2><p>Perubahan status laporan terbaru</p></div><a class="text-action" href="{{ route('reports.index') }}">Lihat semua <span>→</span></a></div>
        <div class="activity-list" id="activity-list">
            @forelse ($reports->take(4) as $report)
                @php($status = $statusFor($report))
                @php($activityColor = $status === 'Sesuai' ? 'green' : ($status === 'Tidak sesuai' ? 'amber' : ''))
                @php($activityIcon = $status === 'Sesuai' ? '✓' : ($status === 'Tidak sesuai' ? '!' : ($status === 'Draf' ? '✎' : '◷')))
                <a class="activity-item" href="{{ route('reports.show', $report) }}"><span class="activity-mark {{ $activityColor }}">{{ $activityIcon }}</span><span class="activity-copy"><strong>{{ $report->nomor_tiket }} · {{ $report->cpclRecord?->kel_tani ?? 'Laporan' }}</strong><small>{{ $status }} · {{ $report->cpclRecord?->nama_di ?? $report->nama_saluran }}</small></span><time class="activity-date">{{ $report->updated_at?->format('d M Y') }}</time></a>
            @empty<div class="empty-state">Belum ada aktivitas laporan. Pilih CPCL untuk memulai paket laporan pertama.</div>@endforelse
        </div>
    </section>
    <section class="panel info-panel"><div class="panel-heading"><div><h2>Ringkasan kelengkapan</h2><p>Kategori dokumen pada laporan Anda</p></div></div>
        @foreach ([['BAST','complete-bast','bar-bast'],['LPJ','complete-lpj','bar-lpj'],['DOKUMENTASI','complete-doc','bar-doc']] as [$category,$id,$bar])
            @php($complete = $documentCounts[$category])
            @php($percent = $reports->count() ? (int) round($complete / $reports->count() * 100) : 0)
            <div class="completion-item"><div class="completion-top"><span>{{ $category === 'DOKUMENTASI' ? 'Dokumentasi' : $category }}</span><strong id="{{ $id }}">{{ $complete }} dari {{ $reports->count() }}</strong></div><div class="progress-track"><span id="{{ $bar }}" style="width:{{ $percent }}%"></span></div></div>
        @endforeach
        <div class="helper-note"><span>ⓘ</span> Berkas lengkap tidak otomatis berarti laporan sudah dinyatakan Sesuai oleh Verifikator.</div>
    </section>
</div>
<section class="panel table-panel"><div class="panel-heading"><div><h2>Laporan terbaru</h2><p>Empat paket laporan terakhir pada akun kabupaten ini</p></div><a class="text-action" href="{{ route('reports.index') }}">Buka Laporan Saya <span>→</span></a></div>
    @if ($reports->isNotEmpty())<div class="table-wrap" tabindex="0" role="region" aria-label="Tabel data; geser horizontal untuk melihat kolom lainnya"><table class="data-table"><thead><tr><th>ID Laporan</th><th>Kelompok Tani</th><th>Daerah Irigasi</th><th>Terakhir Diperbarui</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        @foreach ($reports->take(4) as $report)
            @php($status = $statusFor($report))
            <tr><td><span class="ticket">{{ $report->nomor_tiket }}</span></td><td><span class="cell-main">{{ $report->cpclRecord?->kel_tani ?? '—' }}</span><span class="cell-sub">{{ $report->kecamatan }}</span></td><td>{{ $report->cpclRecord?->nama_di ?? $report->nama_saluran }}</td><td>{{ $report->updated_at?->format('d M Y H:i') }} WIB</td><td><span class="status {{ Str::slug($status) }}">{{ $status }}</span></td><td><a class="action-link" href="{{ route('reports.show', $report) }}">Detail →</a></td></tr>
        @endforeach
    </tbody></table></div>@else<div class="empty-state">Belum ada paket laporan pada akun Anda.</div>@endif
</section>
@endsection

     
  