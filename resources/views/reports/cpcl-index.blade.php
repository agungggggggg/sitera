@extends('layouts.sitera-pelapor')

@section('title', 'Pilih CPCL — SITERA')

@section('content')
<div class="page-heading"><div><div class="breadcrumb"><span>Ruang Pelapor</span><span>/</span><strong>Pilih CPCL</strong></div><h1>Pilih CPCL</h1><p>Pilih kelompok tani sebagai dasar paket laporan BAST, LPJ, dan dokumentasi.</p></div><div class="heading-actions"><span class="period-chip"><span></span> {{ auth()->user()->kabupaten ?: 'Wilayah belum ditetapkan' }}</span></div></div>
<div class="cpcl-intro panel"><div class="cpcl-intro-icon">⌕</div><div><h2>Pilih data CPCL</h2><p>Pilih kelompok tani yang akan menjadi dasar paket laporan. Data yang tampil dibatasi ke kabupaten akun Anda.</p></div></div>
@if (!$regionAssigned)
    <div class="notice"><span class="notice-icon">!</span><span><strong>Wilayah akun belum ditetapkan.</strong> Hubungi Super Admin agar provinsi dan kabupaten/kota di akun Anda diatur.</span></div>
@else
<section class="panel table-panel">
    <form class="toolbar" method="get" action="{{ route('reports.create') }}">
        <label class="search-control"><span>⌕</span><input id="cpcl-search" name="q" type="search" value="{{ $filters['q'] }}" placeholder="Cari nama kelompok tani atau Daerah Irigasi"></label>
        <select class="filter-control" id="cpcl-province" aria-label="Filter provinsi" disabled><option>{{ auth()->user()->provinsi }}</option></select>
        <select class="filter-control" id="cpcl-regency" aria-label="Filter kabupaten" disabled><option>{{ auth()->user()->kabupaten }}</option></select>
        <select class="filter-control" id="cpcl-district" name="kecamatan" aria-label="Filter kecamatan"><option value="">Semua kecamatan</option>@foreach ($districts as $district)<option value="{{ $district }}" @selected($filters['kecamatan'] === $district)>{{ $district }}</option>@endforeach</select>
        <button class="btn btn-outline btn-sm" type="submit">Filter</button><a class="btn btn-outline btn-sm" href="{{ route('reports.create') }}">Reset</a>
    </form>
    <div class="panel-heading"><div><h2>Referensi CPCL wilayah akun</h2><p>{{ $records->total() }} data tersedia · hanya wilayah terotorisasi</p></div></div>
    <div id="cpcl-table" class="table-wrap" tabindex="0" role="region" aria-label="Tabel data; geser horizontal untuk melihat kolom lainnya"><table class="data-table"><thead><tr><th>No.</th><th>Nama DI</th><th>Kewenangan</th><th>Keterangan</th><th>Wilayah</th><th>Kelompok Tani</th><th>Status laporan</th><th>Aksi</th></tr></thead><tbody>
        @forelse ($records as $record)
            @php($existing = $record->reports->first())
            <tr><td>{{ $record->no ?? $record->id }}</td><td><span class="cell-main">{{ $record->nama_di }}</span><span class="cell-sub">CPCL #{{ $record->id }}</span></td><td>{{ $record->kewenangan ?? '—' }}</td><td>{{ $record->keterangan ?? '—' }}</td><td><span class="cell-main">{{ $record->kabupaten }}, {{ $record->provinsi }}</span><span class="cell-sub">{{ $record->kecamatan }} · {{ $record->desa }}</span></td><td>{{ $record->kel_tani }}</td><td>@if ($existing)<span class="status {{ $existing->status === 'DRAF' ? 'draf' : 'menunggu-pemeriksaan' }}">{{ $existing->status === 'DRAF' ? 'Draf' : 'Paket dibuat' }}</span>@else<span class="status status-muted">Belum ada laporan</span>@endif</td><td><div class="row-actions"><a class="action-link" href="{{ route('reports.create', ['detail' => $record->id]) }}">Detail</a>@if ($existing)<a class="action-link" href="{{ $existing->status === 'DRAF' ? route('reports.package.edit', $existing) : route('reports.show', $existing) }}">{{ $existing->status === 'DRAF' ? 'Lanjutkan' : 'Buka' }} →</a>@else<a class="action-link" href="{{ route('reports.package.create', $record) }}">Pilih →</a>@endif</div></td></tr>
        @empty
            <tr><td colspan="8"><div class="empty-state">Tidak ada CPCL yang cocok pada halaman ini. Ubah pencarian atau tekan Reset.</div></td></tr>
        @endforelse
    </tbody></table></div>
    {{ $records->links() }}
</section>
@if ($selectedCpcl)
<section class="panel cpcl-detail-panel" id="cpcl-detail-panel"><div class="panel-heading"><div><span class="section-kicker">DETAIL REFERENSI</span><h2>Detail CPCL · {{ $selectedCpcl->nama_di }}</h2><p>Data CPCL referensi untuk wilayah Anda.</p></div><a class="btn btn-outline btn-sm" href="{{ route('reports.create') }}">Tutup</a></div>
    <div class="cpcl-detail-grid">@foreach ([['No.',$selectedCpcl->no],['Nama DI',$selectedCpcl->nama_di],['Kewenangan',$selectedCpcl->kewenangan],['Keterangan',$selectedCpcl->keterangan],['Provinsi',$selectedCpcl->provinsi],['Kabupaten/Kota',$selectedCpcl->kabupaten],['Kecamatan',$selectedCpcl->kecamatan],['Desa/Kelurahan',$selectedCpcl->desa],['Kelompok Tani',$selectedCpcl->kel_tani],['Nama Ketua',$selectedCpcl->nama_ketua],['Realisasi',$selectedCpcl->realisasi],['Luas (ha)',$selectedCpcl->luas_ha],['X',$selectedCpcl->x],['Y',$selectedCpcl->y]] as [$label,$value])<div class="detail-field"><small>{{ $label }}</small><strong>{{ $value ?: '—' }}</strong></div>@endforeach</div>
    @if (!$selectedCpcl->reports->isNotEmpty())<div class="form-actions"><a class="btn btn-primary" href="{{ route('reports.package.create', $selectedCpcl) }}">Pilih CPCL & Buat Laporan →</a></div>@endif
</section>
@endif
@endif
@endsection

@push('styles')
<style>.cpcl-detail-panel{margin-top:16px;padding:18px}.cpcl-detail-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px;padding:0 18px 18px}.cpcl-detail-grid .detail-field{padding:11px;border:1px solid #e5edf0;border-radius:8px;background:#fbfdfe;min-width:0}.cpcl-detail-grid small{display:block;color:#82969e;font-size:8px;text-transform:uppercase}.cpcl-detail-grid strong{display:block;color:#31515e;font-size:10px;overflow-wrap:anywhere}.status-muted{background:#f0f3f4;color:#71858e}@media(max-width:700px){.cpcl-detail-grid{grid-template-columns:repeat(2,minmax(0,1fr))}}</style>
@endpush
     
  