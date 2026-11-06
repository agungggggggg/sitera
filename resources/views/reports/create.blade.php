@extends('layouts.sitera')

@section('title', isset($report) ? 'Edit Draf Laporan — SITERA' : 'Buat Laporan — SITERA')
@section('breadcrumb', 'Buat laporan')

@section('content')
@php($report = $report ?? null)
<div class="page-head"><div><div class="eyebrow">Input manual</div><h1>{{ isset($report) ? 'Edit draf laporan' : 'Buat laporan' }}</h1><p>Isi data kejadian dengan lengkap. Anda dapat menyimpannya sebagai draf.</p></div></div>
<div class="form-layout">
<section class="panel form-card">
    <h2>Informasi kerusakan</h2><p>Field bertanda <b>*</b> wajib diisi.</p>
    @if ($errors->any())<div class="notice" style="margin-top:16px"><span>!</span><div><b>Periksa kembali isian</b><ul style="margin:4px 0 0;padding-left:18px">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div></div>@endif
    <form method="post" action="{{ isset($report) ? route('reports.update', $report) : route('reports.store') }}">
        @csrf
        @if (isset($report)) @method('PUT') @endif
        <div class="section-title">Waktu & lokasi</div>
        <div class="fields">
            <div class="field"><label for="tanggal_kejadian">Tanggal kejadian *</label><input id="tanggal_kejadian" type="date" name="tanggal_kejadian" value="{{ old('tanggal_kejadian', $report?->tanggal_kejadian?->format('Y-m-d') ?? '') }}" required></div>
            <div class="field"><label for="provinsi">Provinsi *</label><select id="provinsi" name="provinsi" required><option value="">Pilih provinsi</option>@foreach ($provinces as $province)<option value="{{ $province }}" @selected(old('provinsi', $report?->provinsi ?? '') === $province)>{{ $province }}</option>@endforeach</select></div>
            <div class="field"><label for="kabupaten">Kabupaten / Kota *</label><select id="kabupaten" name="kabupaten" required data-selected="{{ old('kabupaten', $report?->kabupaten ?? '') }}" @disabled(! old('provinsi', $report?->provinsi))><option value="">{{ old('provinsi', $report?->provinsi) ? 'Pilih kabupaten/kota' : 'Pilih provinsi terlebih dahulu' }}</option>@foreach ($regencies as $regency)<option value="{{ $regency['value'] }}" @selected(old('kabupaten', $report?->kabupaten ?? '') === $regency['value'])>{{ $regency['label'] }}</option>@endforeach</select></div>
            <div class="field"><label for="kecamatan">Kecamatan</label><input id="kecamatan" name="kecamatan" value="{{ old('kecamatan', $report?->kecamatan ?? '') }}" placeholder="Contoh: Pamanukan"></div>
            <div class="field"><label for="desa">Desa</label><input id="desa" name="desa" value="{{ old('desa', $report?->desa ?? '') }}" placeholder="Contoh: Mulyasari"></div>
            <div class="field"><label for="nama_saluran">Nama saluran / bangunan</label><input id="nama_saluran" name="nama_saluran" value="{{ old('nama_saluran', $report?->nama_saluran ?? '') }}" placeholder="Contoh: Saluran Sekunder Ciasem"></div>
        </div>
        <div class="section-title">Detail kerusakan</div>
        <div class="fields">
            <div class="field"><label for="jenis_kerusakan">Jenis kerusakan *</label><select id="jenis_kerusakan" name="jenis_kerusakan" required><option value="">Pilih jenis kerusakan</option>@foreach (['Saluran Primer','Saluran Sekunder','Saluran Tersier','Bendung','Bangunan Bagi','Pintu Air','Lainnya'] as $type)<option value="{{ $type }}" @selected(old('jenis_kerusakan', $report?->jenis_kerusakan ?? '') === $type)>{{ $type }}</option>@endforeach</select></div>
            <div class="field"><label for="koordinat">Koordinat <span class="hint">(opsional)</span></label><input id="koordinat" name="koordinat" value="{{ old('koordinat', $report?->koordinat ?? '') }}" placeholder="-6.287912, 107.942150"></div>
            <div class="field"><label for="panjang_terdampak_m">Panjang terdampak (m)</label><input id="panjang_terdampak_m" type="number" step="0.01" min="0" name="panjang_terdampak_m" value="{{ old('panjang_terdampak_m', $report?->panjang_terdampak_m ?? '') }}" placeholder="0,00"></div>
            <div class="field"><label for="luas_terdampak_ha">Luas terdampak (ha)</label><input id="luas_terdampak_ha" type="number" step="0.01" min="0" name="luas_terdampak_ha" value="{{ old('luas_terdampak_ha', $report?->luas_terdampak_ha ?? '') }}" placeholder="0,00"></div>
            <div class="field full"><label for="deskripsi">Deskripsi kerusakan *</label><textarea id="deskripsi" name="deskripsi" maxlength="500" required placeholder="Uraikan kondisi kerusakan, dampak, dan kebutuhan penanganan...">{{ old('deskripsi', $report?->deskripsi ?? '') }}</textarea><div class="hint">Maksimal 500 karakter.</div></div>
            <div class="field full"><label for="catatan">Catatan tambahan</label><textarea id="catatan" name="catatan" placeholder="Informasi tambahan jika ada...">{{ old('catatan', $report?->catatan ?? '') }}</textarea></div>
        </div>
        <div class="form-foot"><a class="secondary" href="{{ isset($report) ? route('reports.show', $report) : route('reports.index') }}">Batal</a><button class="primary" type="submit">{{ isset($report) ? 'Simpan perubahan' : 'Simpan sebagai draf' }}</button></div>
    </form>
</section>
<aside class="panel step-card"><h2 style="font-size:16px;margin:0 0 18px">Alur laporan</h2><div class="step"><div class="step-no">1</div><div><b>Isi data kejadian</b><p>Lengkapi informasi lokasi dan kerusakan.</p></div></div><div class="step"><div class="step-no">2</div><div><b>Tambahkan lampiran</b><p>Foto atau dokumen bukti akan tersedia di tahap berikutnya.</p></div></div><div class="step"><div class="step-no">3</div><div><b>Periksa lalu kirim</b><p>Data yang sudah terkirim tidak dapat diubah.</p></div></div><div class="notice" style="margin-top:20px"><span>i</span><div><b>Tips pengisian</b>Gunakan deskripsi yang singkat, jelas, dan menyebutkan dampak kerusakan.</div></div></aside>
</div>
@endsection

@push('styles')
<style>.user-chip-form{margin:0}.user-chip-form .user-chip{cursor:pointer}.nav a{border:0;background:transparent;color:#bfd3d5;text-align:left;padding:11px 12px;border-radius:10px;display:flex;align-items:center;gap:11px;text-decoration:none}.nav a:hover,.nav a.active{background:rgba(255,255,255,.12);color:#fff}.primary,.secondary{display:inline-block;text-decoration:none}</style>
@endpush

@push('scripts')
<script>
(() => {
    const catalog = @json($regionCatalog);
    const province = document.getElementById('provinsi');
    const regency = document.getElementById('kabupaten');
    if (!province || !regency) return;

    const fillRegencies = (selected = '') => {
        const list = catalog[province.value] || [];
        regency.replaceChildren(new Option(list.length ? 'Pilih kabupaten/kota' : 'Pilih provinsi terlebih dahulu', ''));
        list.forEach(({value, label}) => regency.add(new Option(label, value, false, value === selected)));
        regency.disabled = list.length === 0;
    };

    province.addEventListener('change', () => fillRegencies());
    if (province.value) fillRegencies(regency.dataset.selected || regency.value);
})();
</script>
@endpush
