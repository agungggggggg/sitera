@extends('layouts.sitera')

@section('title', 'Detail Laporan — SITERA')
@section('breadcrumb', 'Detail laporan')

@section('content')
<div class="page-head"><div><div class="eyebrow">{{ $report->status === 'TERKIRIM' ? 'Laporan masuk' : 'Laporan saya' }}</div><h1>Detail laporan</h1><p>{{ $report->nomor_tiket }} · dibuat {{ $report->created_at?->format('d M Y H:i') }} WIB</p></div><div style="display:flex;gap:10px;align-items:center">@if ($report->status === 'DRAF' && ! $report->koreksi_dari && auth()->user()->id === $report->user_id)<a class="primary" href="{{ route('reports.edit', $report) }}">Edit draf</a>@endif<a class="secondary" href="{{ url()->previous() !== url()->current() ? url()->previous() : route('reports.index') }}">← Kembali</a></div></div>
@if ($report->originalReport)<div class="notice" style="margin-bottom:16px"><span>↗</span><div>Koreksi dari <a href="{{ route('reports.show', $report->originalReport) }}">{{ $report->originalReport->nomor_tiket }}</a></div></div>@endif
@if ($report->correction)<div class="notice" style="margin-bottom:16px"><span>↗</span><div>Laporan koreksi <a href="{{ route('reports.show', $report->correction) }}">{{ $report->correction->nomor_tiket }}</a> · {{ Str::title(strtolower($report->correction->status)) }}</div></div>@endif
<section class="panel form-card">
    <div class="panel-head" style="margin:-25px -25px 22px"><div><h2>{{ $report->nomor_tiket }}</h2><p>Data laporan tersimpan di database SITERA.</p></div><span class="badge {{ $report->status === 'DRAF' ? 'draft' : 'sent' }}">{{ Str::title(strtolower($report->status)) }}</span></div>
    <div class="section-title" style="margin-top:0">Informasi pelaporan</div>
    <div class="fields">
        <div class="field"><label>Pelapor</label><div class="readonly">{{ $report->user?->name ?? '—' }}</div></div>
        <div class="field"><label>Sumber isian</label><div class="readonly">{{ $report->sumber_isian }}</div></div>
        <div class="field"><label>Tanggal kejadian</label><div class="readonly">{{ $report->tanggal_kejadian?->format('d M Y') }}</div></div>
        <div class="field"><label>Waktu dikirim</label><div class="readonly">{{ $report->dikirim_pada?->format('d M Y H:i') ?? 'Belum dikirim' }}{{ $report->dikirim_pada ? ' WIB' : '' }}</div></div>
    </div>
    <div class="section-title">Lokasi</div>
    <div class="fields">
        <div class="field"><label>Provinsi</label><div class="readonly">{{ $report->provinsi }}</div></div>
        <div class="field"><label>Kabupaten</label><div class="readonly">{{ $report->kabupaten }}</div></div>
        <div class="field"><label>Kecamatan</label><div class="readonly">{{ $report->kecamatan ?: '—' }}</div></div>
        <div class="field"><label>Desa</label><div class="readonly">{{ $report->desa ?: '—' }}</div></div>
        <div class="field full"><label>Nama saluran / bangunan</label><div class="readonly">{{ $report->nama_saluran ?: '—' }}</div></div>
        <div class="field full"><label>Koordinat</label><div class="readonly">{{ $report->koordinat ?: '—' }}</div></div>
    </div>
    <div class="section-title">Detail kerusakan</div>
    <div class="fields">
        <div class="field"><label>Jenis kerusakan</label><div class="readonly">{{ $report->jenis_kerusakan }}</div></div>
        <div class="field"><label>Panjang terdampak</label><div class="readonly">{{ $report->panjang_terdampak_m !== null ? $report->panjang_terdampak_m.' m' : '—' }}</div></div>
        <div class="field"><label>Luas terdampak</label><div class="readonly">{{ $report->luas_terdampak_ha !== null ? $report->luas_terdampak_ha.' ha' : '—' }}</div></div>
        <div class="field full"><label>Deskripsi</label><div class="readonly multiline">{{ $report->deskripsi }}</div></div>
        <div class="field full"><label>Catatan</label><div class="readonly multiline">{{ $report->catatan ?: '—' }}</div></div>
    </div>
</section>
<div class="notice" style="margin-top:18px"><span>i</span><div><b>Lampiran</b>{{ $report->attachments->count() }} file tersimpan. Minimal satu lampiran diperlukan sebelum laporan dikirim.</div></div>
@if ($report->status === 'DRAF' && auth()->user()->id === $report->user_id)
<section class="panel form-card" style="margin-top:18px"><h2>Tambah lampiran</h2><p>JPG, PNG, PDF, Excel/CSV · maksimal 10 MB per file · maksimal 5 file.</p><form method="post" action="{{ route('reports.attachments.store', $report) }}" enctype="multipart/form-data">@csrf<div class="field"><label for="file">File bukti</label><input id="file" type="file" name="file" accept=".jpg,.jpeg,.png,.pdf,.xls,.xlsx,.csv" required></div><div class="form-foot"><button class="primary" type="submit">Unggah lampiran</button></div></form></section>
@endif
@if ($report->attachments->count())<section class="panel form-card" style="margin-top:18px"><h2>File terlampir</h2><ul>@foreach ($report->attachments as $attachment)<li>@if (in_array($attachment->mime_type, ['image/jpeg', 'image/png', 'application/pdf'], true))<a href="{{ route('reports.attachments.preview', $attachment) }}" target="_blank" rel="noopener">Pratinjau</a> · @endif<a href="{{ route('reports.attachments.download', $attachment) }}">{{ $attachment->original_name }}</a> · {{ number_format($attachment->size_bytes / 1024, 0) }} KB</li>@endforeach</ul></section>@endif
@if (auth()->user()->role?->code === 'verifikator_pusat' && $report->status === 'TERKIRIM' && ! $report->verification)
<section class="panel form-card" style="margin-top:18px"><h2>Hasil pemeriksaan</h2><p>Berikan hasil pemeriksaan untuk laporan ini.</p><form method="post" action="{{ route('reports.verify', $report) }}">@csrf<div class="fields"><div class="field"><label for="hasil">Hasil *</label><select id="hasil" name="hasil" required><option value="">Pilih hasil</option><option value="SESUAI">Sesuai</option><option value="TIDAK_SESUAI">Tidak sesuai</option></select></div><div class="field full"><label for="catatan">Catatan</label><textarea id="catatan" name="catatan" maxlength="2000" placeholder="Wajib diisi jika hasil tidak sesuai."></textarea></div></div><div class="form-foot"><button class="primary" type="submit">Simpan hasil pemeriksaan</button></div></form></section>
@elseif ($report->verification)
<section class="panel form-card" style="margin-top:18px"><div class="panel-head" style="margin:-25px -25px 18px"><div><h2>Hasil pemeriksaan</h2><p>{{ $report->verification->diperiksa_pada?->format('d M Y H:i') }} WIB</p></div><span class="badge {{ $report->verification->hasil === 'SESUAI' ? 'sent' : 'review' }}">{{ Str::title(strtolower(str_replace('_', ' ', $report->verification->hasil))) }}</span></div><p>{{ $report->verification->catatan ?: 'Tidak ada catatan.' }}</p></section>
@if (auth()->user()->id === $report->user_id && $report->verification->hasil === 'TIDAK_SESUAI' && ! $report->correction)
<div class="notice" style="margin-top:18px"><span>!</span><div><b>Perlu koreksi</b>Perbaiki laporan mengikuti catatan Verifikator, lalu kirimkan draf koreksi.</div></div>
<form method="post" action="{{ route('reports.correction.create', $report) }}" style="margin-top:12px">@csrf<button class="primary" type="submit">Buat draf koreksi</button></form>
@endif
@endif
@endsection

@push('styles')
<style>.user-chip-form{margin:0}.user-chip-form .user-chip{cursor:pointer}.nav a{border:0;background:transparent;color:#bfd3d5;text-align:left;padding:11px 12px;border-radius:10px;display:flex;align-items:center;gap:11px;text-decoration:none}.nav a:hover,.nav a.active{background:rgba(255,255,255,.12);color:#fff}.primary,.secondary{display:inline-block;text-decoration:none}.readonly{min-height:20px;padding:10px 11px;border:1px solid var(--line);border-radius:9px;background:#f8fbfa;color:var(--ink)}.multiline{white-space:pre-wrap;min-height:72px}</style>
@endpush
