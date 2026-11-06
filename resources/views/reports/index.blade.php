@extends('layouts.sitera-pelapor')

@section('title', 'Laporan Saya — SITERA')

@section('content')
<div class="page-heading"><div><div class="breadcrumb"><span>Ruang Pelapor</span><span>/</span><strong>Laporan Saya</strong></div><h1>Laporan Saya</h1><p>Kelola draf dan pantau laporan yang sudah dikirim untuk akun Anda.</p></div><div class="heading-actions"><a class="btn btn-primary" href="{{ route('reports.create') }}">＋ Buat Laporan</a></div></div>
<section class="panel table-panel">
    <form data-cascade-filters class="toolbar" method="get" action="{{ route('reports.index') }}">
        <label class="search-control"><span>⌕</span><input id="report-search" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Cari ID, kelompok tani, atau Nama DI"></label>
        <select class="filter-control" name="provinsi" aria-label="Filter provinsi"><option value="">Semua provinsi</option>@foreach ($provinces as $province)<option value="{{ $province }}" @selected($filters['provinsi'] === $province)>{{ $province }}</option>@endforeach</select>
        <select class="filter-control" name="kelompok_tani" aria-label="Filter kelompok tani"><option value="">Semua kelompok tani</option>@foreach ($groups as $group)<option value="{{ $group }}" @selected($filters['kelompok_tani'] === $group)>{{ $group }}</option>@endforeach</select>
        <select class="filter-control" name="nama_di" aria-label="Filter nama DI"><option value="">Semua Nama DI</option>@foreach ($irrigations as $di)<option value="{{ $di }}" @selected($filters['nama_di'] === $di)>{{ $di }}</option>@endforeach</select>
        <select class="filter-control" name="kecamatan" aria-label="Filter kecamatan"><option value="">Semua kecamatan</option>@foreach ($districts as $district)<option value="{{ $district }}" @selected($filters['kecamatan'] === $district)>{{ $district }}</option>@endforeach</select>
        <select class="filter-control" name="desa" aria-label="Filter desa"><option value="">Semua desa/kelurahan</option>@foreach ($villages as $village)<option value="{{ $village }}" @selected($filters['desa'] === $village)>{{ $village }}</option>@endforeach</select>
        <select class="filter-control" name="status" aria-label="Filter status"><option value="">Semua status</option>@foreach (['DRAF'=>'Draf','Menunggu pemeriksaan'=>'Menunggu pemeriksaan','Tidak sesuai'=>'Tidak sesuai','Sesuai'=>'Sesuai'] as $value=>$label)<option value="{{ $value }}" @selected($filters['status'] === $value)>{{ $label }}</option>@endforeach</select>
        <label class="date-filter"><span>Dari</span><input class="filter-control" name="date_from" type="date" value="{{ $filters['date_from'] }}" aria-label="Tanggal kirim dari"></label>
        <label class="date-filter"><span>Sampai</span><input class="filter-control" name="date_to" type="date" value="{{ $filters['date_to'] }}" aria-label="Tanggal kirim sampai"></label>
        <select class="filter-control" name="sort" aria-label="Urutkan laporan"><option value="date-desc" @selected($filters['sort'] === 'date-desc')>Pembaruan terbaru</option><option value="date-asc" @selected($filters['sort'] === 'date-asc')>Pembaruan terlama</option><option value="group-asc" @selected($filters['sort'] === 'group-asc')>Kelompok A–Z</option><option value="group-desc" @selected($filters['sort'] === 'group-desc')>Kelompok Z–A</option><option value="di-asc" @selected($filters['sort'] === 'di-asc')>Nama DI A–Z</option><option value="di-desc" @selected($filters['sort'] === 'di-desc')>Nama DI Z–A</option></select>
        <button class="btn btn-outline btn-sm" type="submit">Filter</button><a class="btn btn-outline btn-sm" href="{{ route('reports.index') }}">Reset</a>
    </form>
    <div class="panel-heading"><div><h2>{{ $reports->total() }} laporan</h2><p>Baris laporan dibatasi ke akun Pelapor yang sedang masuk.</p></div></div>
    @if ($reports->isNotEmpty())<div id="reports-table" class="table-wrap" tabindex="0" role="region" aria-label="Tabel data; geser horizontal untuk melihat kolom lainnya"><table class="data-table"><thead><tr><th>ID Laporan</th><th>Kelompok Tani</th><th>Daerah Irigasi</th><th>Terakhir Diperbarui</th><th>Status</th><th>Aksi</th></tr></thead><tbody>
        @foreach ($reports as $report)
            @php($status = $report->status === 'DRAF' ? 'Draf' : ($report->verification?->hasil === 'SESUAI' ? 'Sesuai' : ($report->verification?->hasil === 'TIDAK_SESUAI' ? 'Tidak sesuai' : 'Menunggu pemeriksaan')))
            <tr><td><span class="ticket">{{ $report->nomor_tiket }}</span><span class="cell-sub">{{ $report->dikirim_pada?->format('d M Y') ?? 'Belum dikirim' }}</span></td><td><span class="cell-main">{{ $report->cpclRecord?->kel_tani ?? '—' }}</span><span class="cell-sub">{{ $report->kecamatan }} · {{ $report->desa }}</span></td><td>{{ $report->cpclRecord?->nama_di ?? $report->nama_saluran }}</td><td>{{ $report->updated_at?->format('d M Y H:i') }} WIB</td><td><span class="status {{ Str::slug($status) }}">{{ $status }}</span></td><td><div class="row-actions"><a class="action-link" href="{{ route('reports.show', $report) }}">Detail →</a>@if ($report->status === 'DRAF' && $report->cpcl_id)<a class="action-link" href="{{ route('reports.package.edit', $report) }}">Lanjutkan draf</a>@endif</div></td></tr>
        @endforeach
    </tbody></table></div>{{ $reports->links() }}@else<div class="empty-state"><strong>Tidak ada laporan yang sesuai filter.</strong><p>Ubah kata kunci/filter atau buat paket baru dari CPCL.</p></div>@endif
</section>
@endsection

    
  