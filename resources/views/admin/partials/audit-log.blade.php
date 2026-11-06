<section class="panel table-panel audit-panel" aria-labelledby="audit-card-title">
    <div class="audit-panel-heading">
        <div>
            <h2 id="audit-card-title">Audit log</h2>
            <p>Riwayat tindakan administratif. Tampilan ini hanya-baca.</p>
        </div>
        <span class="audit-readonly">Hanya baca</span>
    </div>

    <form class="audit-filter-form" method="GET" action="{{ url()->current() }}" aria-label="Filter Audit Log">
        @if (request()->query('view') === 'audit')
            <input type="hidden" name="view" value="audit">
        @endif
        <div class="audit-filter-main">
            <label class="audit-search">
                <span class="audit-sr-only">Cari aktor, objek, tindakan, atau hasil</span>
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="10.8" cy="10.8" r="6.6" fill="none" stroke="currentColor" stroke-width="1.8"/><path d="m16 16 4.4 4.4" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"/></svg>
                <input name="q" type="search" maxlength="200" value="{{ $auditFilters['q'] ?? '' }}" placeholder="Cari aktor, objek, tindakan, atau hasil">
            </label>
            <label>
                <span class="audit-sr-only">Filter tindakan</span>
                <select class="audit-select" name="action" aria-label="Filter tindakan">
                    <option value="">Semua tindakan</option>
                    @foreach ($auditActions as $action => $label)
                        <option value="{{ $action }}" @selected(($auditFilters['action'] ?? '') === $action)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <label>
                <span class="audit-sr-only">Filter hasil</span>
                <select class="audit-select" name="result" aria-label="Filter hasil">
                    <option value="">Semua hasil</option>
                    @foreach ($auditResults as $result => $label)
                        <option value="{{ $result }}" @selected(($auditFilters['result'] ?? '') === $result)>{{ $label }}</option>
                    @endforeach
                </select>
            </label>
            <a class="audit-reset" href="{{ route('admin.index', ['view' => 'audit']) }}">Reset</a>
        </div>
        <details class="audit-advanced" @if (filled($auditFilters['date_from'] ?? '') || filled($auditFilters['date_to'] ?? '') || (int) ($auditFilters['per_page'] ?? 25) !== 25) open @endif>
            <summary>Rentang tanggal &amp; tampilan</summary>
            <div class="audit-advanced-grid">
                <label>Dari tanggal<input type="date" name="date_from" value="{{ $auditFilters['date_from'] ?? '' }}"></label>
                <label>Sampai tanggal<input type="date" name="date_to" value="{{ $auditFilters['date_to'] ?? '' }}"></label>
                <label>Baris per halaman<input type="number" name="per_page" min="1" max="100" value="{{ $auditFilters['per_page'] ?? 25 }}"></label>
                <button class="btn btn-outline btn-sm" type="submit">Terapkan rentang</button>
            </div>
        </details>
    </form>

    @if ($errors->any())
        <div class="admin-error" role="alert"><strong>Periksa filter:</strong><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <p class="audit-count">{{ number_format($auditLogs->total(), 0, ',', '.') }} aktivitas</p>
    <div class="table-wrap audit-table-wrap" tabindex="0" role="region" aria-label="Audit log; geser horizontal untuk melihat kolom lainnya">
        <table class="data-table audit-table">
            <thead><tr><th scope="col">WAKTU</th><th scope="col">AKTOR</th><th scope="col">TINDAKAN</th><th scope="col">OBJEK</th><th scope="col">HASIL</th><th scope="col">RINCIAN</th></tr></thead>
            <tbody>
                @forelse ($auditLogs as $log)
                    <tr>
                        <td>{{ $log->created_at->translatedFormat('j M Y, H.i') }}</td>
                        <td>{{ $log->display_actor }}</td>
                        <td class="audit-action">{{ $log->display_action }}</td>
                        <td>{{ $log->display_object }}</td>
                        <td><span class="audit-result {{ $log->result }}">{{ $log->display_result }}</span></td>
                        <td class="audit-details">{{ $log->display_details }}</td>
                    </tr>
                @empty
                    <tr><td class="audit-empty" colspan="6">Belum ada aktivitas yang sesuai filter.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="audit-pagination">{{ $auditLogs->links() }}</div>
</section>
<script>
    document.querySelectorAll('.audit-filter-main select').forEach((select) => {
        select.addEventListener('change', () => select.form.requestSubmit());
    });
</script>

