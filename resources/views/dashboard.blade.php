@extends('layouts.sitera')

@section('title', 'Ringkasan — SITERA')
@section('breadcrumb', 'Ringkasan')

@section('content')
<section>
    <div class="page-head">
        <div>
            <div class="eyebrow">Ruang kerja pelapor</div>
            <h1>Selamat datang, {{ Str::before(auth()->user()->name, ' ') }}.</h1>
            <p>Pantau laporan kerusakan irigasi yang Anda buat dari satu tempat.</p>
        </div>
        @if (auth()->user()->role?->code === 'pelapor_daerah')
            <a class="primary" href="{{ route('reports.create') }}">＋ Buat laporan baru</a>
        @endif
    </div>

    <div class="stats">
        <div class="stat"><div class="label">Total laporan</div><strong>{{ $totalReports }}</strong><div class="trend">Tersimpan di akun Anda</div></div>
        <div class="stat"><div class="label">Masih draf</div><strong>{{ $draftReports }}</strong><div class="trend warn">Perlu Anda lengkapi</div></div>
        <div class="stat"><div class="label">Sudah terkirim</div><strong>{{ $submittedReports }}</strong><div class="trend">Masuk ke antrean pusat</div></div>
        <div class="stat"><div class="label">Laporan terakhir</div><strong style="font-size:22px">{{ $latestReport?->created_at?->format('d M') ?? '—' }}</strong><div class="trend">{{ $latestReport?->nomor_tiket ?? 'Belum ada laporan' }}</div></div>
    </div>

    <div class="grid">
        <section class="panel">
            <div class="panel-head"><div><h2>Laporan terbaru</h2><p>Perubahan terakhir dari laporan Anda</p></div><a class="secondary" href="{{ route('reports.index') }}">Lihat semua</a></div>
            <div class="table-wrap" tabindex="0" role="region" aria-label="Tabel data; geser horizontal untuk melihat kolom lainnya">
                <table class="table"><thead><tr><th>Nomor tiket</th><th>Lokasi</th><th>Status</th><th>Diperbarui</th></tr></thead><tbody>
                @forelse ($reports->take(5) as $report)
                    <tr><td><div class="ticket">{{ $report->nomor_tiket }}</div><div class="sub">{{ $report->jenis_kerusakan }}</div></td><td><div>{{ $report->kabupaten }}</div><div class="sub">{{ $report->provinsi }}</div></td><td><span class="badge {{ $report->status === 'DRAF' ? 'draft' : 'sent' }}">{{ Str::title(strtolower($report->status)) }}</span></td><td><div>{{ $report->updated_at?->format('d M Y') }}</div><div class="sub">{{ $report->updated_at?->format('H:i') }} WIB</div></td></tr>
                @empty
                    <tr><td colspan="4"><div class="empty-state">Belum ada laporan. Mulai dengan membuat laporan pertama.</div></td></tr>
                @endforelse
                </tbody></table>
            </div>
        </section>
        <aside class="side-list">
            @if ($draftReports > 0)<div class="notice"><span>!</span><div><b>{{ $draftReports }} draf belum selesai</b>Lengkapi data sebelum dikirim ke Kementan.</div></div>@endif
            <section class="panel"><div class="panel-head"><div><h2>Alur berikutnya</h2><p>Fokus pekerjaan Anda</p></div></div><div class="panel-body side-list"><div class="activity"><i class="dot"></i><div><b>Periksa data laporan</b><span>Pastikan lokasi dan deskripsi sudah jelas.</span></div></div><div class="activity"><i class="dot" style="background:#d18a35;box-shadow:0 0 0 4px #fff1dc"></i><div><b>Tambahkan lampiran</b><span>Fitur lampiran akan tersedia pada tahap berikutnya.</span></div></div><div class="activity"><i class="dot" style="background:#8c9da1;box-shadow:0 0 0 4px #edf1f2"></i><div><b>Kirim ke Kementan</b><span>Laporan terkirim tidak dapat diubah.</span></div></div></div></section>
        </aside>
    </div>
</section>
@endsection

@push('styles')
<style>.user-chip-form{margin:0}.user-chip-form .user-chip{cursor:pointer}.empty-state{padding:22px;color:var(--muted);text-align:center}.nav a{border:0;background:transparent;color:#bfd3d5;text-align:left;padding:11px 12px;border-radius:10px;display:flex;align-items:center;gap:11px;text-decoration:none}.nav a:hover,.nav a.active{background:rgba(255,255,255,.12);color:#fff}.primary,.secondary{display:inline-block;text-decoration:none}</style>
@endpush
