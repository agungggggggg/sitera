@extends('layouts.sitera-pelapor')

@section('title', ($report ? 'Edit Draf' : 'Dokumen Pelaporan') . ' — SITERA')

@section('content')
<div class="page-heading"><div><div class="breadcrumb"><a href="{{ route('reports.create') }}">Pilih CPCL</a><span>/</span><strong>Dokumen laporan</strong></div><h1>{{ $report ? 'Lanjutkan draf' : 'Dokumen pelaporan' }}</h1><p>Pastikan dokumen sesuai dengan CPCL yang dipilih. Draf dapat disimpan dan dilanjutkan nanti.</p></div><span class="draft-pill">{{ $report?->nomor_tiket ?? 'Draf baru' }}</span></div>
@if ($errors->any())<div class="notice" role="alert"><span class="notice-icon">!</span><span><strong>Periksa berkas.</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></span></div>@endif
@if (session('status'))<div class="notice" role="status"><span class="notice-icon">✓</span><span>{{ session('status') }}</span></div>@endif
<div class="form-progress panel"><div class="step active"><span>1</span><div><strong>Data CPCL</strong><small>Identitas kelompok</small></div></div><div class="step-line"></div><div class="step active"><span>2</span><div><strong>Dokumen laporan</strong><small>Unggah tiga bagian</small></div></div><div class="step-line"></div><div class="step"><span>3</span><div><strong>Tinjau & kirim</strong><small>Validasi akhir</small></div></div></div>
<div class="form-layout">
    <div class="form-main"><section class="panel form-panel">
        <div class="panel-heading"><div><span class="section-kicker">IDENTITAS LAPORAN</span><h2>Dokumen pelaporan</h2><p>Data identitas diambil dari referensi CPCL.</p></div><span class="draft-pill">{{ $report ? 'Draf' : 'Draf baru' }}</span></div>
        <div class="selected-cpcl"><div class="selected-cpcl-head"><span class="section-kicker">CPCL TERPILIH · #{{ $cpcl->id }}</span><a class="text-action" href="{{ route('reports.create') }}">Ganti CPCL</a></div><h3>{{ $cpcl->kel_tani }}</h3><p>{{ $cpcl->nama_di }} · {{ $cpcl->kecamatan }}, {{ $cpcl->kabupaten }}, {{ $cpcl->provinsi }}</p><div class="cpcl-detail-grid">@foreach ([['Kewenangan',$cpcl->kewenangan],['Desa/Kelurahan',$cpcl->desa],['Ketua',$cpcl->nama_ketua],['Luas (ha)',$cpcl->luas_ha]] as [$label,$value])<div><small>{{ $label }}</small><strong>{{ $value ?: '—' }}</strong></div>@endforeach</div></div>
        <div class="document-section-heading"><div><h3>Dokumen pendukung</h3><p>BAST, LPJ, dan dokumentasi wajib tersedia sebelum pengiriman.</p></div><span class="required-label">* Wajib</span></div>
        <form id="package-form" method="post" enctype="multipart/form-data" action="{{ $report ? route('reports.package.update', $report) : route('reports.package.store', $cpcl) }}">
            @csrf @if ($report) @method('PUT') @endif
            @foreach ([['BAST','bast','Berita Acara Serah Terima','PDF','pdf','maks. 10 MB'],['LPJ','lpj','Laporan Pertanggungjawaban / kuitansi belanja','PDF','pdf','maks. 10 MB'],['DOKUMENTASI','dokumentasi','Dokumentasi foto kegiatan dalam presentasi','PPTX','pptx','maks. 25 MB']] as [$category,$input,$description,$format,$extension,$limit])
                @php($existing = $report?->attachments->where('kategori', $category)->where('versi', $report->versi)->sortByDesc('id')->first())
                <div class="upload-card"><div class="upload-card-top"><span class="document-mark {{ $format === 'PPTX' ? 'ppt' : 'pdf' }}">{{ $format }}</span><div><strong>{{ $category === 'DOKUMENTASI' ? 'Dokumentasi' : $category }}</strong><small>{{ $description }}</small></div><span class="required-label">* Wajib</span></div>
                    <label class="dropzone" for="file-{{ $input }}"><span class="upload-symbol">↑</span><strong id="name-{{ $input }}">{{ $existing ? 'Tersimpan: '.$existing->original_name : 'Pilih dokumen '.$category }}</strong><small id="hint-{{ $input }}">Format {{ $format }} · {{ $limit }}</small><input id="file-{{ $input }}" type="file" name="{{ $input }}" accept=".{{ $extension }}"></label>
                </div>
            @endforeach
            <div class="form-error" id="form-error" role="alert" hidden></div>
            <div class="form-actions"><a class="btn btn-outline" href="{{ $report ? route('reports.index') : route('reports.create') }}">Kembali</a><button class="btn btn-secondary" type="submit" name="_action" value="draft">Simpan Draf</button><button class="btn btn-primary" type="submit" name="_action" value="submit">Tinjau & Kirim <span>→</span></button></div>
        </form>
    </section></div>
    <aside class="form-aside"><section class="panel aside-panel"><h3>Checklist dokumen</h3><p>Pastikan setiap kategori sudah terisi.</p>
        @foreach ([['BAST','bast'],['LPJ','lpj'],['DOKUMENTASI','dokumentasi']] as [$category,$input])@php($existing = $report?->attachments->where('kategori', $category)->where('versi', $report->versi)->isNotEmpty())<div class="check-row" id="check-{{ $input }}"><span>{{ $existing ? '✓' : '○' }}</span>{{ $category === 'DOKUMENTASI' ? 'Dokumentasi (PPTX)' : $category.' (PDF)' }}</div>@endforeach
        <div class="aside-tip">Berkas diperiksa jenis dan ukurannya saat diunggah. Pemeriksaan kesesuaian dokumen dilakukan Verifikator setelah laporan dikirim.</div></section>
        <section class="panel aside-panel"><h3>Alur berikutnya</h3><div class="mini-step"><span>1</span><div><strong>Kirim laporan</strong><small>Laporan masuk antrean pemeriksaan.</small></div></div><div class="mini-step"><span>2</span><div><strong>Pemeriksaan pusat</strong><small>Verifikator memeriksa tiga kategori dokumen.</small></div></div><div class="mini-step"><span>3</span><div><strong>Hasil pemeriksaan</strong><small>Sesuai atau Tidak sesuai disertai catatan bila perlu perbaikan.</small></div></div></section>
    </aside>
</div>
@endsection

@push('styles')
<style>
.form-progress{display:flex;align-items:center;gap:16px;padding:16px;margin-bottom:18px}.form-progress .step{display:flex;align-items:center;gap:10px}.form-progress .step>span{display:grid;place-items:center;width:28px;height:28px;border-radius:50%;background:#dcefe8;color:#08745f}.step-line{height:1px;background:#dbe4e2;flex:1}.form-progress small{display:block;color:#81949c}.form-layout{display:grid;grid-template-columns:minmax(0,1.65fr) minmax(260px,.8fr);gap:16px}.form-panel,.aside-panel{padding:18px}.selected-cpcl{padding:15px;border:1px solid #d9e8ee;border-radius:9px;background:#f7fbfc}.selected-cpcl-head{display:flex;justify-content:space-between;align-items:center}.selected-cpcl h3{margin:9px 0 4px;color:#254f60;font-size:15px}.selected-cpcl p{margin:0;color:#728a94;font-size:10px}.selected-cpcl .cpcl-detail-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:8px;margin-top:13px}.selected-cpcl .cpcl-detail-grid>div{padding:8px;border:1px solid #e5edf0;border-radius:7px;background:#fff}.selected-cpcl small{display:block;color:#82969e;font-size:8px;text-transform:uppercase}.selected-cpcl strong{display:block;margin-top:3px;color:#31515e;font-size:9px;overflow-wrap:anywhere}.document-section-heading{display:flex;justify-content:space-between;align-items:flex-end;margin:20px 0 10px}.document-section-heading h3{margin:0;color:#294d5b;font-size:13px}.document-section-heading p{margin:4px 0 0;color:#82969e;font-size:9px}.required-label{color:#a64e48;font-size:9px;font-weight:750}.upload-card{padding:12px;margin:10px 0;border:1px solid #e4ecef;border-radius:9px}.upload-card-top{display:flex;align-items:center;gap:10px;margin-bottom:10px}.upload-card-top>div{flex:1;display:grid;gap:3px}.upload-card-top strong{color:#31515e;font-size:11px}.upload-card-top small{color:#82969e;font-size:9px}.document-mark{display:grid;place-items:center;width:40px;height:38px;border-radius:8px;background:#fff0ef;color:#a94b48;font-size:9px;font-weight:850}.document-mark.ppt{background:#fff4e6;color:#a46a1f}.dropzone{display:flex;align-items:center;gap:10px;min-height:59px;padding:9px 11px;border:1px dashed #b9d2dc;border-radius:8px;background:#fbfdfe;cursor:pointer}.dropzone:hover{background:#f4fafc}.dropzone .upload-symbol{width:30px;height:30px;display:grid;place-items:center;border-radius:8px;background:#e8f4f8;color:#086288}.dropzone strong{display:block;color:#31515e;font-size:9px}.dropzone small{display:block;margin-top:3px;color:#84979f;font-size:8px}.dropzone input{position:absolute;width:1px;height:1px;opacity:0;pointer-events:none}.form-actions{display:flex;justify-content:flex-end;gap:8px;flex-wrap:wrap;margin-top:14px}.form-actions a{text-decoration:none}.aside-panel h3{margin:0 0 5px;color:#294d5b;font-size:12px}.aside-panel>p{margin:0 0 12px;color:#81949c;font-size:9px}.check-row{display:flex;align-items:center;gap:9px;padding:9px 0;border-bottom:1px solid #edf2f4;color:#536f7a;font-size:10px}.check-row span{display:grid;place-items:center;width:20px;height:20px;border-radius:50%;background:#eaf5ef;color:#39845a}.aside-tip{margin-top:13px;padding:10px;border-radius:7px;background:#f3f8fa;color:#718995;font-size:9px;line-height:1.5}.mini-step{display:flex;gap:9px;padding:10px 0;border-bottom:1px solid #edf2f4}.mini-step>span{display:grid;place-items:center;width:22px;height:22px;border-radius:50%;background:#e8f4f8;color:#086288;font-size:9px;font-weight:800}.mini-step div{display:grid;gap:3px}.mini-step strong{color:#31515e;font-size:9px}.mini-step small{color:#82969e;font-size:8px;line-height:1.4}.form-error{margin-top:12px;padding:10px;border-radius:7px;background:#fff0ef;color:#a94b48;font-size:10px}
@media(max-width:850px){.form-layout{grid-template-columns:1fr}.form-aside{display:grid;grid-template-columns:1fr 1fr;gap:12px}}
@media(max-width:560px){.form-progress{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));align-items:start;gap:8px;padding:12px}.form-progress .step{display:flex;flex-direction:column;align-items:flex-start;gap:5px;min-width:0}.form-progress .step>span{width:24px;height:24px;flex:none}.form-progress .step>div{min-width:0}.form-progress strong{font-size:10px;line-height:1.3}.form-progress small{font-size:9px;line-height:1.35}.step-line{display:none}.selected-cpcl .cpcl-detail-grid{grid-template-columns:1fr 1fr}.form-aside{grid-template-columns:1fr}.form-actions>*{flex:1}.document-section-heading{align-items:flex-start}}
</style>
@endpush
@push('scripts')
<script>
(() => {
    document.querySelectorAll('.dropzone input[type=file]').forEach(input => input.addEventListener('change', () => {
        const file = input.files?.[0];
        if (!file) return;
        document.getElementById('name-' + input.name).textContent = file.name;
        const check = document.querySelector('#check-' + input.name + ' span');
        if (check) check.textContent = '✓';
    }));
})();
</script>
@endpush
     
    
