@extends('layouts.sitera-verifier')

@section('title', 'Ringkasan Pemeriksaan — SITERA')

@section('content')
@php
    $scopeLabel = $scope === 'Nasional' ? 'Seluruh Indonesia' : ($scopeOptions->firstWhere('value', $scope)['label'] ?? $scope);
    $circumference = 2 * pi() * 82;
    $donutColors = [
        'sesuai' => '#1B3B73',
        'belum_sesuai' => '#5682C1',
        'belum_melapor' => '#B5CCE4',
    ];
    $dashSesuai = $cpclTotal > 0 ? round($cpclCounts['sesuai'] / $cpclTotal * $circumference, 2) : 0;
    $dashBelumSesuai = $cpclTotal > 0 ? round($cpclCounts['belum_sesuai'] / $cpclTotal * $circumference, 2) : 0;
    $dashBelumLapor = $cpclTotal > 0 ? max(0, round($circumference - $dashSesuai - $dashBelumSesuai, 2)) : 0;
@endphp
<div class="page-heading verifier-page-heading">
    <div>
        <div class="breadcrumb"><span>Ruang Verifikator</span><span>/</span><strong>Ringkasan pemeriksaan</strong></div>
        <h1>Ringkasan pemeriksaan</h1>
        <p>Periksa laporan dari seluruh wilayah sesuai kewenangan akun Anda.</p>
    </div>
</div>
@if (session('status'))
    <div class="notice" role="status"><span class="notice-icon">✓</span><span>{{ session('status') }}</span></div>
@endif

<div class="metrics-grid verifier-metrics">
    <article class="metric verifier-kpi-card">
        <div class="verifier-kpi-title">Menunggu pemeriksaan</div>
        <div class="metric-value">{{ $pendingCount }}</div>
        <div class="metric-meta">Paket siap ditinjau</div>
    </article>
    <article class="metric verifier-kpi-card">
        <div class="verifier-kpi-title">Sesuai</div>
        <div class="metric-value">{{ $compliantCount }}</div>
        <div class="metric-meta">Hasil gabungan tersimpan</div>
    </article>
    <article class="metric verifier-kpi-card">
        <div class="verifier-kpi-title">Tidak sesuai</div>
        <div class="metric-value">{{ $nonCompliantCount }}</div>
        <div class="metric-meta">Menunggu tindak lanjut Pelapor</div>
    </article>
    <article class="metric verifier-kpi-card">
        <div class="verifier-kpi-title">Sudah diperiksa</div>
        <div class="metric-value">{{ $reviewedCount }}</div>
        <div class="metric-meta">Riwayat hasil tersimpan</div>
    </article>
</div>

<section class="panel cpcl-donut-panel verifier-donut-panel">
    <div class="panel-heading cpcl-chart-heading">
        <div>
            <h2>Rekap Status CPCL</h2>
            <p>Jumlah CPCL berdasarkan status pelaporan BAST.</p>
        </div>
        <form class="cpcl-scope-control verifier-scope-control" method="get" action="{{ route('dashboard') }}">
            <label for="cakupan">Cakupan Diagram</label>
            <select id="cakupan" name="cakupan" onchange="this.form.submit()">
                @foreach ($scopeOptions as $option)
                    <option value="{{ $option['value'] }}" @selected($scope === $option['value'])>{{ $option['label'] }}</option>
                @endforeach
            </select>
        </form>
    </div>
    <div class="cpcl-donut-layout">
        <div class="cpcl-donut-wrap">
            <div class="cpcl-donut" role="img" aria-label="Rekap {{ $cpclTotal }} CPCL">
                <svg viewBox="0 0 220 220" aria-hidden="true">
                    @if ($cpclTotal > 0)
                        @if ($dashSesuai > 0)
                            <circle cx="110" cy="110" r="82" fill="none" stroke="{{ $donutColors['sesuai'] }}" stroke-width="26" stroke-dasharray="{{ $dashSesuai }} {{ $circumference - $dashSesuai }}" stroke-dashoffset="0" transform="rotate(-90 110 110)"/>
                        @endif
                        @if ($dashBelumSesuai > 0)
                            <circle cx="110" cy="110" r="82" fill="none" stroke="{{ $donutColors['belum_sesuai'] }}" stroke-width="26" stroke-dasharray="{{ $dashBelumSesuai }} {{ $circumference - $dashBelumSesuai }}" stroke-dashoffset="{{ -$dashSesuai }}" transform="rotate(-90 110 110)"/>
                        @endif
                        @if ($dashBelumLapor > 0)
                            <circle cx="110" cy="110" r="82" fill="none" stroke="{{ $donutColors['belum_melapor'] }}" stroke-width="26" stroke-dasharray="{{ $dashBelumLapor }} {{ $circumference - $dashBelumLapor }}" stroke-dashoffset="{{ -($dashSesuai + $dashBelumSesuai) }}" transform="rotate(-90 110 110)"/>
                        @endif
                    @else
                        <circle cx="110" cy="110" r="82" fill="none" stroke="#e5edf0" stroke-width="26"/>
                    @endif
                </svg>
                <div class="cpcl-donut-center">
                    <span>Total CPCL</span>
                    <strong>{{ number_format($cpclTotal, 0, ',', '.') }}</strong>
                    <small>{{ $scopeLabel }}</small>
                </div>
            </div>
        </div>
        <div class="cpcl-donut-legend" aria-live="polite">
            <div class="cpcl-donut-item">
                <span class="cpcl-donut-dot" style="background:{{ $donutColors['sesuai'] }}"></span>
                <span class="cpcl-donut-name">BAST sudah sesuai</span>
                <strong class="cpcl-donut-value">{{ number_format($cpclCounts['sesuai'], 0, ',', '.') }}</strong>
            </div>
            <div class="cpcl-donut-item">
                <span class="cpcl-donut-dot" style="background:{{ $donutColors['belum_sesuai'] }}"></span>
                <span class="cpcl-donut-name">BAST belum sesuai</span>
                <strong class="cpcl-donut-value">{{ number_format($cpclCounts['belum_sesuai'], 0, ',', '.') }}</strong>
            </div>
            <div class="cpcl-donut-item">
                <span class="cpcl-donut-dot" style="background:{{ $donutColors['belum_melapor'] }}"></span>
                <span class="cpcl-donut-name">Poktan belum melapor</span>
                <strong class="cpcl-donut-value">{{ number_format($cpclCounts['belum_melapor'], 0, ',', '.') }}</strong>
            </div>
        </div>
    </div>
</section>

<div class="content-grid">
    <section class="panel">
        <div class="panel-heading">
            <div>
                <h2>Antrean terlama menunggu</h2>
                <p>Paket menunggu satu hasil pemeriksaan gabungan.</p>
            </div>
            <a class="text-action" href="{{ route('verification.index') }}">Buka antrean →</a>
        </div>
        <div class="activity-list">
            @forelse ($recentQueue as $report)
                <a class="activity-item" href="{{ route('verification.show', $report) }}">
                    <span class="activity-mark amber">▤</span>
                    <span class="activity-copy">
                        <strong>{{ $report->cpclRecord?->kel_tani ?? $report->nomor_tiket }}</strong>
                        <small>{{ $report->nomor_tiket }} · {{ $report->kabupaten }}</small>
                    </span>
                    <span class="activity-date">{{ $report->dikirim_pada?->diffForHumans() }}</span>
                </a>
            @empty
                <div class="empty-state">Tidak ada laporan menunggu pemeriksaan.</div>
            @endforelse
        </div>
    </section>

    <section class="panel info-panel">
        <div class="panel-heading">
            <div>
                <h2>Panduan pemeriksaan</h2>
                <p>Hasil gabungan untuk satu paket.</p>
            </div>
        </div>
        <div class="review-guide">
            <div><span class="guide-mark green">✓</span><p><strong>Sesuai</strong><small>Catatan tidak wajib.</small></p></div>
            <div><span class="guide-mark red">!</span><p><strong>Tidak sesuai</strong><small>Catatan yang jelas wajib diisi.</small></p></div>
            <div><span class="guide-mark blue">i</span><p><strong>Tiga dokumen</strong><small>BAST PDF, LPJ PDF, dan dokumentasi PPTX.</small></p></div>
        </div>
        <div class="helper-note"><span>ⓘ</span> Hasil pemeriksaan bukan pengesahan atau persetujuan pimpinan.</div>
    </section>
</div>

<section class="panel table-panel">
    <div class="panel-heading">
        <div>
            <h2>Perlu ditindaklanjuti</h2>
            <p>Paket berstatus Tidak sesuai menunggu perbaikan Pelapor.</p>
        </div>
        <a class="text-action" href="{{ route('verification.history', ['result' => 'Tidak sesuai']) }}">Lihat riwayat →</a>
    </div>
    @if ($followUp->isNotEmpty())
        <div class="table-wrap" tabindex="0" role="region" aria-label="Tabel data; geser horizontal untuk melihat kolom lainnya">
            <table class="data-table">
                <thead><tr><th>ID Laporan</th><th>Kelompok Tani</th><th>Kabupaten</th><th>Catatan pemeriksaan</th><th>Aksi</th></tr></thead>
                <tbody>
                    @foreach ($followUp as $report)
                        <tr>
                            <td><span class="ticket">{{ $report->nomor_tiket }}</span></td>
                            <td>{{ $report->cpclRecord?->kel_tani ?? '—' }}</td>
                            <td>{{ $report->kabupaten }}</td>
                            <td><span class="cell-sub">{{ Str::limit($report->verification?->catatan, 100) }}</span></td>
                            <td><a class="action-link" href="{{ route('verification.show', $report) }}">Detail →</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="empty-state">Belum ada paket yang memerlukan tindak lanjut.</div>
    @endif
</section>
@endsection

@push('styles')
<style>
.review-guide .guide-mark{flex:none}
@media(max-width:760px){.content-grid{grid-template-columns:1fr}.cpcl-chart-heading{display:grid}.cpcl-scope-control{width:100%}}
</style>
@endpush
