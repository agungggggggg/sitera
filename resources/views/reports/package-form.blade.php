@extends('layouts.sitera-pelapor')

@section('title', ($report ? 'Edit Draf' : 'Dokumen Pelaporan') . ' — SITERA')

@section('content')
<div class="page-heading">
    <div>
        <div class="breadcrumb"><a href="{{ route('reports.create') }}">Pilih CPCL</a><span>/</span><strong>Dokumen laporan</strong></div>
        <h1>{{ $report ? 'Lanjutkan draf' : 'Dokumen pelaporan' }}</h1>
        <p>Pastikan dokumen sesuai dengan CPCL yang dipilih. Draf dapat disimpan dan dilanjutkan nanti.</p>
    </div>
    <span class="draft-pill">{{ $report?->nomor_tiket ?? 'Draf baru' }}</span>
</div>

@if ($errors->any())
    <div class="notice notice-error" role="alert">
        <span class="notice-icon">!</span>
        <div>
            <strong>Periksa kembali berkas pelaporan.</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    </div>
@endif

<div class="form-progress panel" aria-label="Tahapan pengisian dokumen pelaporan">
    <div class="step done"><span>✓</span><div><strong>Data CPCL</strong><small>Identitas kelompok</small></div></div>
    <div class="step-line"></div>
    <div class="step active"><span>2</span><div><strong>Dokumen laporan</strong><small>Unggah tiga bagian</small></div></div>
    <div class="step-line"></div>
    <div class="step"><span>3</span><div><strong>Tinjau &amp; kirim</strong><small>Validasi akhir</small></div></div>
</div>

<div class="form-layout">
    <div class="form-main">
        <section class="panel form-panel">
            <div class="panel-heading">
                <div>
                    <span class="section-kicker">IDENTITAS LAPORAN</span>
                    <h2>Dokumen pelaporan</h2>
                    <p>Data identitas diambil dari referensi CPCL.</p>
                </div>
                <span class="draft-pill">{{ $report ? 'Draf' : 'Draf baru' }}</span>
            </div>

            <div class="selected-cpcl">
                <div class="selected-cpcl-head">
                    <span class="section-kicker">CPCL TERPILIH · #{{ $cpcl->id }}</span>
                    <a class="text-action" href="{{ route('reports.create') }}">Ganti CPCL</a>
                </div>
                <h3>{{ $cpcl->kel_tani }}</h3>
                <p>{{ $cpcl->nama_di }} · {{ $cpcl->kecamatan }}, {{ $cpcl->kabupaten }}, {{ $cpcl->provinsi }}</p>
                <div class="cpcl-detail-grid">
                    @foreach ([['Kewenangan', $cpcl->kewenangan], ['Desa/Kelurahan', $cpcl->desa], ['Ketua', $cpcl->nama_ketua], ['Luas (ha)', $cpcl->luas_ha]] as [$label, $value])
                        <div><small>{{ $label }}</small><strong>{{ $value ?: '—' }}</strong></div>
                    @endforeach
                </div>
            </div>

            <div class="document-section-heading">
                <div>
                    <h3>Dokumen pendukung</h3>
                    <p>BAST, LPJ, dan dokumentasi wajib tersedia sebelum pengiriman.</p>
                </div>
                <span class="required-label">* Wajib</span>
            </div>

            <form id="package-form" method="post" enctype="multipart/form-data" action="{{ $report ? route('reports.package.update', $report) : route('reports.package.store', $cpcl) }}">
                @csrf
                @if ($report)
                    @method('PUT')
                @endif
                @foreach ([
                    ['BAST', 'bast', 'Berita Acara Serah Terima', 'PDF', 'pdf', 'maks. 10 MB'],
                    ['LPJ', 'lpj', 'Laporan Pertanggungjawaban / kuitansi belanja', 'PDF', 'pdf', 'maks. 10 MB'],
                    ['DOKUMENTASI', 'dokumentasi', 'Dokumentasi foto kegiatan dalam presentasi', 'PPTX', 'pptx', 'maks. 25 MB'],
                ] as [$category, $input, $description, $format, $extension, $limit])
                    @php($existing = $report?->attachments->where('kategori', $category)->where('versi', $report->versi)->sortByDesc('id')->first())
                    <div class="upload-card">
                        <div class="upload-card-top">
                            <span class="document-mark {{ $format === 'PPTX' ? 'ppt' : 'pdf' }}">{{ $format }}</span>
                            <div>
                                <strong>{{ $category === 'DOKUMENTASI' ? 'Dokumentasi' : $category }}</strong>
                                <small>{{ $description }}</small>
                            </div>
                            <span class="required-label">* Wajib</span>
                        </div>
                        <label class="dropzone {{ $existing ? 'is-selected' : '' }}" id="drop-{{ $input }}" for="file-{{ $input }}">
                            <span class="upload-symbol">↑</span>
                            <strong id="name-{{ $input }}">{{ $existing ? 'Tersimpan: ' . $existing->original_name : 'Pilih dokumen ' . ($category === 'DOKUMENTASI' ? 'Dokumentasi' : $category) }}</strong>
                            <small id="hint-{{ $input }}">Format {{ $format }} · {{ $limit }}</small>
                            <input id="file-{{ $input }}" type="file" name="{{ $input }}" accept=".{{ $extension }}">
                        </label>
                    </div>
                @endforeach

                <div class="form-error" id="form-error" role="alert" hidden></div>
                <div class="form-actions">
                    <a class="btn btn-outline" href="{{ $report ? route('reports.index') : route('reports.create') }}">Kembali</a>
                    <button class="btn btn-secondary" type="submit" name="_action" value="draft">Simpan Draf</button>
                    <button class="btn btn-primary" type="submit" name="_action" value="submit">Tinjau &amp; Kirim <span>→</span></button>
                </div>
            </form>
        </section>
    </div>

    <aside class="form-aside">
        <section class="panel aside-panel">
            <h3>Checklist dokumen</h3>
            <p>Pastikan setiap kategori sudah terisi.</p>
            @foreach ([['BAST', 'bast'], ['LPJ', 'lpj'], ['DOKUMENTASI', 'dokumentasi']] as [$category, $input])
                @php($existing = $report?->attachments->where('kategori', $category)->where('versi', $report->versi)->isNotEmpty())
                <div class="check-row {{ $existing ? 'complete' : '' }}" id="check-{{ $input }}">
                    <span>{{ $existing ? '✓' : '○' }}</span>{{ $category === 'DOKUMENTASI' ? 'Dokumentasi (PPTX)' : $category . ' (PDF)' }}
                </div>
            @endforeach
            <div class="aside-tip">Berkas diperiksa jenis dan ukurannya saat diunggah. Pemeriksaan kesesuaian dokumen dilakukan Verifikator setelah laporan dikirim.</div>
        </section>

        <section class="panel aside-panel">
            <h3>Alur berikutnya</h3>
            <div class="mini-step"><span>1</span><div><strong>Kirim laporan</strong><small>Laporan masuk antrean pemeriksaan.</small></div></div>
            <div class="mini-step"><span>2</span><div><strong>Pemeriksaan pusat</strong><small>Verifikator memeriksa tiga kategori dokumen.</small></div></div>
            <div class="mini-step"><span>3</span><div><strong>Hasil pemeriksaan</strong><small>Sesuai atau Tidak sesuai disertai catatan bila perlu perbaikan.</small></div></div>
        </section>
    </aside>
</div>
@endsection

@push('styles')
<style>
.breadcrumb a{color:var(--blue);font-weight:700;text-decoration:none}
.breadcrumb a:hover{text-decoration:underline}
.notice-error{border-color:#f0cfca;background:#fff7f6;color:#99322c}
.notice-error .notice-icon{border-color:#dfa29c;color:#99322c}
.notice-error strong{color:#862520}
.notice-error ul{margin:5px 0 0;padding-left:18px}
.form-progress{display:flex;align-items:center;gap:16px;padding:15px 19px;margin-bottom:16px}
.form-progress .step{display:flex;align-items:center;gap:10px;min-width:0}
.form-progress .step>span{display:grid;place-items:center;width:29px;height:29px;flex:none;border:1px solid #cfe4eb;border-radius:50%;background:#fff;color:#526773;font-size:11px;font-weight:800}
.form-progress .step.active>span{background:var(--blue);border-color:var(--blue);color:#fff}
.form-progress .step.done>span{background:#e9f5ed;border-color:#b8dfc7;color:#15803d}
.form-progress strong{display:block;color:#17323d;font-size:11px;font-weight:700}
.form-progress small{display:block;margin-top:2px;color:#526773;font-size:9.5px}
.step-line{height:1px;background:#dbe4e2;flex:1}
.form-layout{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(260px,.8fr);gap:16px;align-items:start}
.form-panel,.aside-panel{padding:18px 20px 20px}
.form-panel .panel-heading{padding:0 0 14px}
.form-panel .panel-heading h2{font-size:14px}
.form-panel .panel-heading p{color:#526773}
.draft-pill{background:#eef4f7;color:#385866;font-size:10px;padding:5px 10px}
.selected-cpcl{display:block;padding:16px;margin:0 0 18px;border:1px solid #d5e5ec;border-radius:9px;background:#f4f9fc}
.selected-cpcl-head{display:flex;justify-content:space-between;align-items:center;gap:10px;margin-bottom:6px}
.selected-cpcl-head .section-kicker{margin:0;font-size:9px;letter-spacing:.08em}
.selected-cpcl .text-action{text-decoration:none;font-size:10.5px}
.selected-cpcl h3{margin:0 0 3px;color:#17323d;font:700 15px/1.3 Manrope,sans-serif}
.selected-cpcl>p{margin:0;color:#526773;font-size:11px;line-height:1.45}
.selected-cpcl .cpcl-detail-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:9px;margin-top:13px;padding:0}
.selected-cpcl .cpcl-detail-grid>div{padding:9px 11px;border:1px solid #dce8ed;border-radius:8px;background:#fff;min-width:0}
.selected-cpcl .cpcl-detail-grid small{display:block;color:#526773;font-size:9px;font-weight:700;text-transform:uppercase;letter-spacing:.04em}
.selected-cpcl .cpcl-detail-grid strong{display:block;margin-top:3px;color:#17323d;font-size:11px;overflow-wrap:anywhere}
.document-section-heading{display:flex;justify-content:space-between;align-items:flex-end;gap:10px;margin:18px 0 10px;padding-bottom:0}
.document-section-heading h3{margin:0;color:#203f4c;font:700 13px Manrope,sans-serif}
.document-section-heading p{margin:3px 0 0;color:#526773;font-size:10px}
.required-label{color:#a1423c;font-size:10px;font-weight:700;white-space:nowrap}
.upload-card{padding:14px;margin:10px 0;border:1px solid #dce8ed;border-radius:9px;background:#fff}
.upload-card-top{display:flex;align-items:center;gap:11px;margin-bottom:10px}
.upload-card-top>div{flex:1;min-width:0;display:grid;gap:2px}
.upload-card-top strong{color:#17323d;font-size:12px;font-weight:700}
.upload-card-top small{color:#526773;font-size:10px}
.document-mark{display:grid;place-items:center;width:40px;height:40px;flex:none;border-radius:8px;background:#fff0ef;color:#a94b48;font-size:9.5px;font-weight:800}
.document-mark.ppt{background:#fff4e6;color:#9c6818}
.dropzone{display:flex;flex-direction:column;align-items:center;justify-content:center;gap:5px;min-height:84px;padding:14px 16px;border:1.5px dashed #a9cad7;border-radius:8px;background:#f8fbfd;text-align:center;cursor:pointer;transition:border-color .15s ease,background-color .15s ease}
.dropzone:hover{border-color:#086288;background:#f1f8fc}
.dropzone.is-selected{border-style:solid;border-color:#84b89c;background:#f4faf6}
.dropzone .upload-symbol{width:28px;height:28px;display:grid;place-items:center;border-radius:7px;background:#e8f4f8;color:#086288;font-size:14px;font-weight:700}
.dropzone.is-selected .upload-symbol{background:#dff2e6;color:#15803d}
.dropzone strong{display:block;max-width:100%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#17323d;font-size:11px}
.dropzone small{display:block;margin-top:0;color:#526773;font-size:9.5px}
.dropzone input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}
.form-actions{display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap;margin-top:16px;padding-top:16px;border-top:1px solid #edf2f4}
.form-actions a{text-decoration:none}
.form-aside{display:grid;gap:14px;align-content:start}
.aside-panel{margin-bottom:0}
.aside-panel h3{margin:0 0 4px;color:#203f4c;font:700 13px Manrope,sans-serif}
.aside-panel>p{margin:0 0 12px;color:#526773;font-size:10px}
.check-row{display:flex;align-items:center;gap:9px;padding:9px 0;border-bottom:1px solid #edf2f4;color:#34525e;font-size:11px;font-weight:500}
.check-row span{display:grid;place-items:center;width:22px;height:22px;flex:none;border-radius:50%;background:#eef3f5;color:#526773;font-size:12px;font-weight:700}
.check-row.complete span{background:#e9f5ed;color:#15803d;font-weight:800}
.aside-tip{margin-top:13px;padding:11px 12px;border:1px solid #d5e9f0;border-radius:8px;background:#f2f8fb;color:#385866;font-size:10px;line-height:1.55}
.mini-step{display:flex;gap:10px;padding:10px 0;border-bottom:1px solid #edf2f4;margin-top:0}
.mini-step:last-child{border-bottom:0;padding-bottom:0}
.mini-step>span{display:grid;place-items:center;width:23px;height:23px;flex:none;border-radius:50%;background:#e8f4f8;color:#086288;font-size:10px;font-weight:800}
.mini-step div{display:grid;gap:2px}
.mini-step strong{color:#17323d;font-size:10.5px}
.mini-step small{color:#526773;font-size:9.5px;line-height:1.45}
.form-error{margin-top:12px;padding:10px 12px;border:1px solid #f0cfca;border-radius:8px;background:#fff7f6;color:#99322c;font-size:10px}
@media(max-width:850px){.form-layout{grid-template-columns:1fr}.form-aside{grid-template-columns:1fr 1fr;gap:12px}}
@media(max-width:560px){.form-progress{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));align-items:start;gap:8px;padding:12px}.form-progress .step{display:flex;flex-direction:column;align-items:flex-start;gap:5px;min-width:0}.form-progress .step>span{width:24px;height:24px;flex:none}.form-progress .step>div{min-width:0}.form-progress strong{font-size:10px;line-height:1.3}.form-progress small{font-size:9px;line-height:1.35}.step-line{display:none}.selected-cpcl{display:block}.selected-cpcl .cpcl-detail-grid{grid-template-columns:1fr 1fr}.form-panel,.aside-panel{padding:15px 14px 16px}.form-aside{grid-template-columns:1fr}.form-actions>*{flex:1}.document-section-heading{align-items:flex-start}}
</style>
@endpush

@push('scripts')
<script>
(() => {
    document.querySelectorAll('.dropzone input[type=file]').forEach(input => input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) return;
        const nameEl = document.getElementById('name-' + input.name);
        if (nameEl) nameEl.textContent = file.name;
        const dropEl = document.getElementById('drop-' + input.name);
        dropEl?.classList.add('is-selected');
        const checkRow = document.getElementById('check-' + input.name);
        if (checkRow) {
            checkRow.classList.add('complete');
            const badge = checkRow.querySelector('span');
            if (badge) badge.textContent = '✓';
        }
    }));
})();
</script>
@endpush
