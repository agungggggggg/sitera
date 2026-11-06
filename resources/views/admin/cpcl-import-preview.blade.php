@extends('layouts.sitera')

@section('title', 'Pratinjau Impor CPCL — SITERA')
@section('breadcrumb', 'Impor CPCL')

@section('content')
<div class="page-head"><div><div class="eyebrow">Super Admin · Tambah saja</div><h1>Validasi impor CPCL</h1><p>File {{ $batch->source_name }} diproses sebagai data, bukan dijalankan. Tidak ada data yang tersimpan sampai konfirmasi.</p></div><a class="secondary" href="{{ url('/admin-only') }}">Batal</a></div>
<div class="stats">
    @foreach (['Baru valid' => 'new', 'Duplikat' => 'review', 'Konflik' => 'warn', 'Tidak valid' => 'draft'] as $label => $class)
    <div class="stat"><div class="label">{{ $label }}</div><strong>{{ $batch->summary[$label] ?? 0 }}</strong><div class="trend">{{ $class === 'new' ? 'Menunggu konfirmasi' : 'Tidak akan menimpa data lama' }}</div></div>
    @endforeach
</div>
<section class="panel"><div class="panel-head"><div><h2>Rincian per baris</h2><p>Hanya baris Baru valid yang akan ditambahkan.</p></div></div>
<div class="table-wrap" tabindex="0" role="region" aria-label="Tabel data; geser horizontal untuk melihat kolom lainnya"><table class="table"><thead><tr><th>Baris</th><th>Nama DI / Kelompok Tani</th><th>Wilayah</th><th>Validasi</th><th>Alasan</th></tr></thead><tbody>
@foreach ($batch->rows as $row)
<tr><td>{{ $row['_row'] }}</td><td><strong>{{ $row['nama_di'] ?: '—' }}</strong><div class="sub">{{ $row['kel_tani'] ?: '—' }}</div></td><td>{{ $row['kabupaten'] ?: '—' }} · {{ $row['provinsi'] ?: '—' }}<div class="sub">{{ $row['kecamatan'] ?: '—' }} · {{ $row['desa'] ?: '—' }}</div></td><td>{{ $row['_status'] }}</td><td>{{ $row['_reason'] }}</td></tr>
@endforeach
</tbody></table></div>
<div class="form-foot">
<form method="post" action="{{ route('admin.cpcl.import.cancel', $batch) }}">@csrf<button class="secondary" type="submit">Batalkan impor</button></form>
<form method="post" action="{{ route('admin.cpcl.import.confirm', $batch) }}">@csrf<button class="primary" type="submit" @disabled(($batch->summary['Baru valid'] ?? 0) < 1)>Konfirmasi tambah data baru valid</button></form>
</div></section>
@endsection

@push('styles')
<style>.form-foot form{margin:0}.form-foot{display:flex;justify-content:space-between;gap:12px;padding:18px}.form-foot button:disabled{opacity:.5;cursor:not-allowed}@media(max-width:480px){.form-foot{flex-direction:column;align-items:stretch;padding:12px;gap:9px}.form-foot form,.form-foot button{width:100%}.form-foot button{min-height:44px}.page-head{align-items:flex-start;flex-wrap:wrap}.page-head>.secondary{min-height:44px;display:inline-flex;align-items:center;justify-content:center}}</style>
@endpush

