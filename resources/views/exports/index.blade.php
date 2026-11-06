@extends('layouts.sitera')

@section('title', 'Ekspor Rekap — SITERA')
@section('breadcrumb', 'Ekspor rekap')

@section('content')
<div class="page-head"><div><div class="eyebrow">Unduh data laporan</div><h1>Ekspor rekap</h1><p>Pilih periode dan format file. Data mengikuti cakupan akses akun Anda.</p></div></div>
<section class="panel form-card" style="max-width:850px">
    <h2>Parameter rekap</h2>
    <p>Hanya laporan berstatus Terkirim yang akan dimasukkan.</p>
    <form method="get" action="{{ route('exports.csv') }}">
        <div class="fields">
            <div class="field"><label for="dari">Dari tanggal</label><input id="dari" type="date" name="dari" value="{{ request('dari') }}"></div>
            <div class="field"><label for="sampai">Sampai tanggal</label><input id="sampai" type="date" name="sampai" value="{{ request('sampai') }}"></div>
            <div class="field full"><label>Format file</label><div class="format-options"><label><input type="radio" name="format" value="csv" checked> CSV (Excel-compatible)</label><label><input type="radio" name="format" value="xlsx"> Excel (.xlsx)</label><label><input type="radio" name="format" value="pdf"> PDF</label></div></div>
        </div>
        <div class="export-scope"><b>Cakupan akses:</b> @if (auth()->user()->role?->code === 'pelapor_daerah') hanya laporan milik Anda @else seluruh laporan terkirim sesuai kewenangan role Anda @endif.</div>
        <div class="form-foot"><button class="primary" type="submit">⇩ Unduh rekap</button></div>
    </form>
</section>
@endsection

@push('styles')
<style>
.user-chip-form{margin:0}.user-chip-form .user-chip{cursor:pointer}.nav a{border:0;background:transparent;color:#bfd3d5;text-align:left;padding:11px 12px;border-radius:10px;display:flex;align-items:center;gap:11px;text-decoration:none}.nav a:hover,.nav a.active{background:rgba(255,255,255,.12);color:#fff}.primary,.secondary{display:inline-block;text-decoration:none}.format-options{display:flex;gap:12px;flex-wrap:wrap}.format-options label{display:flex;align-items:center;gap:8px;padding:11px 14px;border:1px solid var(--line);border-radius:10px;background:#fbfdfc;min-height:44px}.export-scope{margin-top:18px;padding:13px 15px;border-radius:10px;background:var(--mint);font-size:12px;color:var(--navy)}
</style>
@endpush
