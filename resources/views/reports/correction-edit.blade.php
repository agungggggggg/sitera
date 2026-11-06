@extends('layouts.sitera')

@section('title', 'Koreksi Laporan — SITERA')
@section('breadcrumb', 'Koreksi laporan')

@section('content')
<div class="page-head"><div><div class="eyebrow">Tindak lanjut laporan</div><h1>Koreksi laporan</h1><p>{{ $report->nomor_tiket }} · koreksi dari {{ $report->originalReport?->nomor_tiket }}</p></div><a class="secondary" href="{{ route('reports.show', $report->originalReport) }}">← Kembali ke laporan awal</a></div>
@if (session('status'))<div class="notice" style="margin-bottom:18px"><span>✓</span><div><b>Berhasil</b>{{ session('status') }}</div></div>@endif
<div class="notice" style="margin-bottom:18px"><span>!</span><div><b>Catatan pemeriksa</b>{{ $report->originalReport?->verification?->catatan ?? 'Laporan perlu diperbaiki.' }}</div></div>
<section class="panel form-card">
    <h2>Perbaiki data laporan</h2><p>Perubahan disimpan sebagai draf koreksi. Laporan awal tetap tidak berubah.</p>
    @if ($errors->any())<div class="notice" style="margin-top:16px"><span>!</span><div><b>Periksa kembali isian</b><ul style="margin:4px 0 0;padding-left:18px">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
    <form method="post" action="{{ route('reports.correction.update', $report) }}">
        @csrf @method('PUT')
        <div class="section-title" style="margin-top:18px">Waktu & lokasi</div>
        <div class="fields">
            <div class="field"><label>Tanggal kejadian *</label><input type="date" name="tanggal_kejadian" value="{{ old('tanggal_kejadian', $report->tanggal_kejadian?->format('Y-m-d')) }}" required></div>
            <div class="field"><label>Provinsi *</label><input name="provinsi" value="{{ old('provinsi', $report->provinsi) }}" required></div>
            <div class="field"><label>Kabupaten *</label><input name="kabupaten" value="{{ old('kabupaten', $report->kabupaten) }}" required></div>
            <div class="field"><label>Kecamatan</label><input name="kecamatan" value="{{ old('kecamatan', $report->kecamatan) }}"></div>
            <div class="field"><label>Desa</label><input name="desa" value="{{ old('desa', $report->desa) }}"></div>
            <div class="field"><label>Nama saluran / bangunan</label><input name="nama_saluran" value="{{ old('nama_saluran', $report->nama_saluran) }}"></div>
        </div>
        <div class="section-title">Detail kerusakan</div>
        <div class="fields">
            <div class="field"><label>Jenis kerusakan *</label><select name="jenis_kerusakan" required><option value="">Pilih</option>@foreach (['Saluran Primer','Saluran Sekunder','Saluran Tersier','Bendung','Bangunan Bagi','Pintu Air','Lainnya'] as $type)<option value="{{ $type }}" @selected(old('jenis_kerusakan', $report->jenis_kerusakan) === $type)>{{ $type }}</option>@endforeach</select></div>
            <div class="field"><label>Koordinat</label><input name="koordinat" value="{{ old('koordinat', $report->koordinat) }}"></div>
            <div class="field"><label>Panjang terdampak (m)</label><input type="number" step="0.01" min="0" name="panjang_terdampak_m" value="{{ old('panjang_terdampak_m', $report->panjang_terdampak_m) }}"></div>
            <div class="field"><label>Luas terdampak (ha)</label><input type="number" step="0.01" min="0" name="luas_terdampak_ha" value="{{ old('luas_terdampak_ha', $report->luas_terdampak_ha) }}"></div>
            <div class="field full"><label>Deskripsi kerusakan *</label><textarea name="deskripsi" maxlength="500" required>{{ old('deskripsi', $report->deskripsi) }}</textarea></div>
            <div class="field full"><label>Catatan tambahan</label><textarea name="catatan">{{ old('catatan', $report->catatan) }}</textarea></div>
        </div>
        <div class="form-foot"><a class="secondary" href="{{ route('reports.show', $report->originalReport) }}">Batal</a><button class="secondary" type="submit">Simpan koreksi sebagai draf</button></div>
    </form>
    <div class="notice" style="margin-top:18px"><span>i</span><div><b>Lampiran</b>{{ $report->attachments->count() }} file tersimpan. Minimal satu lampiran diperlukan sebelum dikirim.</div></div>
    <form method="post" action="{{ route('reports.attachments.store', $report) }}" enctype="multipart/form-data" style="margin-top:14px"><div class="field"><label for="file">Tambah file bukti</label><input id="file" type="file" name="file" accept=".jpg,.jpeg,.png,.pdf,.xls,.xlsx,.csv" required></div>@csrf<button class="secondary" type="submit">Unggah lampiran</button></form>
    @if ($report->attachments->count())<ul>@foreach ($report->attachments as $attachment)<li>@if (in_array($attachment->mime_type, ['image/jpeg', 'image/png', 'application/pdf'], true))<a href="{{ route('reports.attachments.preview', $attachment) }}" target="_blank" rel="noopener">Pratinjau</a> · @endif<a href="{{ route('reports.attachments.download', $attachment) }}">{{ $attachment->original_name }}</a> · {{ number_format($attachment->size_bytes / 1024, 0) }} KB</li>@endforeach</ul>@endif
    <form method="post" action="{{ route('reports.correction.submit', $report) }}" style="display:flex;justify-content:flex-end;margin-top:-66px;margin-right:0" onsubmit="return confirm('Kirim koreksi ini ke Kementan?')">
        @csrf
        <button class="primary" type="submit">Kirim koreksi</button>
    </form>
</section>
@endsection

@push('styles')
<style>.user-chip-form{margin:0}.user-chip-form .user-chip{cursor:pointer}.nav a{border:0;background:transparent;color:#bfd3d5;text-align:left;padding:11px 12px;border-radius:10px;display:flex;align-items:center;gap:11px;text-decoration:none}.nav a:hover,.nav a.active{background:rgba(255,255,255,.12);color:#fff}.primary,.secondary{display:inline-block;text-decoration:none}</style>
@endpush
