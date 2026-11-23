@extends('layouts.sitera-verifier')

@section('title', 'Tinjau Paket — SITERA')
@section('suppress_layout_errors', '1')

@section('content')
@php
    $report = $report->loadMissing(['verification.verifier', 'cpclRecord', 'user']);
    $verification = $report->verification;
    $currentAttachments = $report->attachments->where('versi', $report->versi)->sortBy('kategori');
    $extraAttachments = $currentAttachments->whereNotIn('kategori', ['BAST', 'LPJ', 'DOKUMENTASI']);
    $categories = [
        'BAST' => ['BAST', 'PDF'],
        'LPJ' => ['LPJ', 'PDF'],
        'DOKUMENTASI' => ['Dokumentasi', 'PPTX'],
    ];
    $resultLabel = $verification?->hasil === 'SESUAI'
        ? 'Sesuai'
        : ($verification?->hasil === 'TIDAK_SESUAI' ? 'Tidak sesuai' : 'Menunggu pemeriksaan');
    $statusClass = $verification?->hasil === 'SESUAI'
        ? 'is-compliant'
        : ($verification?->hasil === 'TIDAK_SESUAI' ? 'is-rejected' : 'is-pending');
    $backUrl = $verification ? route('verification.history') : route('verification.index');
    $backLabel = $verification ? 'Kembali ke riwayat pemeriksaan' : 'Kembali ke antrean pemeriksaan';
    $identityFields = [
        ['No', $report->cpclRecord?->no],
        ['Nama DI', $report->cpclRecord?->nama_di ?? $report->nama_saluran],
        ['Kewenangan', $report->cpclRecord?->kewenangan],
        ['Keterangan', $report->cpclRecord?->keterangan],
        ['Kabupaten/Kota', $report->kabupaten],
        ['Kecamatan', $report->kecamatan],
        ['Desa/Kelurahan', $report->desa],
        ['Kelompok Tani', $report->cpclRecord?->kel_tani],
        ['Provinsi', $report->provinsi],
        ['Nama Ketua', $report->cpclRecord?->nama_ketua],
        ['Realisasi', $report->cpclRecord?->realisasi],
        ['Luas_ha', $report->cpclRecord?->luas_ha],
        ['X', $report->cpclRecord?->x],
        ['Y', $report->cpclRecord?->y],
    ];
    $docItems = [];
    foreach ($categories as $category => [$label, $format]) {
        $attachment = $currentAttachments->where('kategori', $category)->sortByDesc('id')->first();
        $kind = ! $attachment
            ? 'missing'
            : ($attachment->mime_type === 'application/pdf'
                ? 'pdf'
                : (in_array($attachment->mime_type, ['image/jpeg', 'image/png'], true) ? 'image' : 'download'));
        $docItems[] = [
            'id' => $attachment?->id ?? $category,
            'tabLabel' => $label,
            'label' => $label,
            'format' => $format,
            'badgeClass' => $format === 'PPTX' ? 'is-pptx' : 'is-pdf',
            'attachment' => $attachment,
            'title' => $attachment?->original_name ?? $label.' belum tersedia',
            'meta' => $label.' · '.$format.' · '.($attachment ? number_format($attachment->size_bytes / 1048576, 2, ',', '.').' MB' : 'Belum diunggah'),
            'kind' => $kind,
            'previewUrl' => ($attachment && in_array($kind, ['pdf', 'image'], true)) ? route('reports.attachments.preview', $attachment) : '',
            'downloadUrl' => $attachment ? route('reports.attachments.download', $attachment) : '',
        ];
    }
    foreach ($extraAttachments as $attachment) {
        $kind = $attachment->mime_type === 'application/pdf'
            ? 'pdf'
            : (in_array($attachment->mime_type, ['image/jpeg', 'image/png'], true) ? 'image' : 'download');
        $docItems[] = [
            'id' => 'extra-'.$attachment->id,
            'tabLabel' => Str::limit($attachment->original_name, 20),
            'label' => 'Lampiran tambahan',
            'format' => 'FILE',
            'badgeClass' => 'is-file',
            'attachment' => $attachment,
            'title' => $attachment->original_name,
            'meta' => 'Lampiran tambahan · '.number_format($attachment->size_bytes / 1048576, 2, ',', '.').' MB',
            'kind' => $kind,
            'previewUrl' => in_array($kind, ['pdf', 'image'], true) ? route('reports.attachments.preview', $attachment) : '',
            'downloadUrl' => route('reports.attachments.download', $attachment),
        ];
    }
    $availableDocs = array_values(array_filter($docItems, static fn (array $item): bool => $item['attachment'] !== null));
@endphp

<div class="page-heading review-page-heading">
    <div>
        <div class="breadcrumb">
            <span>Ruang Verifikator</span><span>/</span><a href="{{ $backUrl }}">{{ $verification ? 'Riwayat Pemeriksaan' : 'Antrean Pemeriksaan' }}</a>
        </div>
        <h1>{{ $verification ? 'Detail pemeriksaan' : 'Antrean pemeriksaan' }}</h1>
        <p>Tinjau satu versi kiriman dan berikan satu hasil gabungan.</p>
    </div>
    <a class="review-back-btn" href="{{ $backUrl }}" aria-label="{{ $backLabel }}" title="{{ $backLabel }}">
        <svg viewBox="0 0 24 24" aria-hidden="true">
            <path d="M5 12h13m-5-5 5 5-5 5" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
    </a>
</div>

@if (session('status'))
    <div class="notice" role="status"><span class="notice-icon">✓</span><span>{{ session('status') }}</span></div>
@endif
@if ($errors->any())
    <div class="notice review-alert-error" role="alert">
        <span class="notice-icon">!</span>
        <div>
            <strong>Periksa kembali hasil pemeriksaan.</strong>
            <ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    </div>
@endif

<div class="review-3col-grid">
    <div class="review-left-col">
        <section class="panel review-identity-card">
            <div class="review-identity-head">
                <div>
                    <span class="section-kicker">IDENTITAS LAPORAN</span>
                    <h2>{{ $report->nomor_tiket }}</h2>
                    <p>{{ $report->cpclRecord?->kel_tani ?? $report->user?->name ?? 'Pelapor' }} · {{ $report->cpclRecord?->nama_di ?? $report->nama_saluran ?? '—' }} · {{ $report->kabupaten }}</p>
                </div>
                <span class="review-inline-status {{ $statusClass }}">Status · {{ $resultLabel }}</span>
            </div>

            <div class="review-identity-fields">
                @foreach ($identityFields as [$label, $value])
                    <div class="review-field-box">
                        <small>{{ $label }}</small>
                        <strong>{{ $value ?: '—' }}</strong>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="panel review-history-card">
            <div class="review-card-head">
                <div>
                    <h2>Riwayat versi dan pemeriksaan</h2>
                    <p>Seluruh riwayat kiriman versi dan hasil pemeriksaan untuk paket laporan ini.</p>
                </div>
            </div>
            <div class="review-history-items">
                <div class="review-history-item">
                    <div>
                        <strong>Versi {{ $report->versi }} dikirim</strong>
                        <small>Paket lengkap masuk antrean pemeriksaan · {{ $report->user?->name ?? 'Pelapor Daerah' }}</small>
                    </div>
                    <time>{{ $report->dikirim_pada?->translatedFormat('j M Y') ?? '—' }}</time>
                </div>
                @if ($verification)
                    <div class="review-history-item">
                        <div>
                            <strong>Hasil {{ $resultLabel }}</strong>
                            <small>{{ $verification->verifier?->name ?? 'Verifikator Pusat' }} · {{ $verification->catatan ?: 'Tanpa catatan.' }}</small>
                        </div>
                        <time>{{ $verification->diperiksa_pada?->translatedFormat('j M Y') ?? '—' }}</time>
                    </div>
                @endif
            </div>
        </section>
    </div>

    <section class="panel review-docs-card">
        <div class="review-card-head review-docs-head">
            <div>
                <h2>Dokumen yang diperiksa</h2>
                <p>Ketiga bagian dinilai sebagai satu paket, bukan hasil terpisah.</p>
            </div>
            <span class="review-version-tag">V{{ $report->versi }}</span>
        </div>

        <div class="review-doc-list">
            @foreach ($docItems as $item)
                <div class="review-doc-row">
                    <span class="review-doc-badge {{ $item['badgeClass'] }}">{{ $item['format'] }}</span>
                    <div class="review-doc-info">
                        <strong>{{ $item['title'] }}</strong>
                        <small>{{ $item['meta'] }}</small>
                    </div>
                    @if ($item['attachment'])
                        <button
                            type="button"
                            class="review-open-btn"
                            data-doc-preview
                            data-doc-id="{{ $item['id'] }}"
                            data-doc-title="{{ $item['title'] }}"
                            data-doc-meta="{{ $item['meta'] }}"
                            data-doc-kind="{{ $item['kind'] }}"
                            data-preview-url="{{ $item['previewUrl'] }}"
                            data-download-url="{{ $item['downloadUrl'] }}"
                        >Buka</button>
                    @else
                        <span class="review-open-btn is-disabled" aria-disabled="true">Kosong</span>
                    @endif
                </div>
            @endforeach
        </div>
    </section>

    <aside class="panel review-decision-card">
        <div class="review-card-head">
            <div>
                <h2>Hasil pemeriksaan gabungan</h2>
                <p>Pilih satu hasil untuk versi yang sedang diperiksa.</p>
            </div>
        </div>

        @if (! $verification)
            <div class="review-decision-rules">
                <div class="review-decision-rule">
                    <span class="review-rule-icon is-ok" aria-hidden="true">✓</span>
                    <div>
                        <strong>Sesuai</strong>
                        <small>Tidak memerlukan catatan wajib.</small>
                    </div>
                </div>
                <div class="review-decision-rule">
                    <span class="review-rule-icon is-err" aria-hidden="true">!</span>
                    <div>
                        <strong>Tidak sesuai</strong>
                        <small>Catatan teks wajib diisi.</small>
                    </div>
                </div>
            </div>

            <form method="post" action="{{ route('reports.verify', $report) }}" id="verifier-review-form" class="review-decision-form">
                @csrf
                <div class="review-note-head">
                    <label for="catatan">Catatan pemeriksaan</label>
                    <span>Wajib untuk Tidak sesuai</span>
                </div>
                <textarea id="catatan" name="catatan" rows="6" maxlength="2000" placeholder="Tulis bagian yang perlu diperbaiki">{{ old('catatan') }}</textarea>
                <div id="review-client-error" class="review-client-error" role="alert" hidden></div>
                <div class="review-decision-actions">
                    <button id="review-submit-reject" class="review-btn-reject" type="submit" name="hasil" value="TIDAK_SESUAI">Tidak sesuai</button>
                    <button id="review-submit-approve" class="review-btn-approve" type="submit" name="hasil" value="SESUAI">Sesuai</button>
                </div>
            </form>
        @else
            <div class="review-decision-rules">
                <div class="review-decision-rule">
                    <span class="review-rule-icon {{ $verification->hasil === 'SESUAI' ? 'is-ok' : 'is-err' }}" aria-hidden="true">{{ $verification->hasil === 'SESUAI' ? '✓' : '!' }}</span>
                    <div>
                        <strong>Hasil: {{ $resultLabel }}</strong>
                        <small>{{ $verification->verifier?->name ?? 'Verifikator Pusat' }} · {{ $verification->diperiksa_pada?->translatedFormat('j M Y, H.i') }} WIB</small>
                    </div>
                </div>
            </div>
            <div class="review-decision-readonly">
                <strong>Catatan pemeriksaan</strong>
                <p>{{ $verification->catatan ?: 'Tidak ada catatan.' }}</p>
            </div>
        @endif
    </aside>
</div>

<div id="review-doc-modal" class="doc-modal" role="dialog" aria-modal="true" aria-labelledby="doc-modal-title" hidden>
    <div class="doc-modal-card">
        <div class="doc-modal-header">
            <div class="doc-modal-title-group">
                <h2 id="doc-modal-title">Pratinjau Dokumen</h2>
                <p id="doc-modal-meta">—</p>
            </div>
            @if (count($availableDocs) > 0)
                <div class="doc-modal-tabs" role="tablist" aria-label="Pilih dokumen untuk dipratinjau">
                    @foreach ($availableDocs as $doc)
                        <button
                            type="button"
                            class="doc-modal-tab"
                            data-doc-tab="{{ $doc['id'] }}"
                            data-doc-title="{{ $doc['title'] }}"
                            data-doc-meta="{{ $doc['meta'] }}"
                            data-doc-kind="{{ $doc['kind'] }}"
                            data-preview-url="{{ $doc['previewUrl'] }}"
                            data-download-url="{{ $doc['downloadUrl'] }}"
                        >{{ $doc['tabLabel'] }}</button>
                    @endforeach
                </div>
            @endif
            <div class="doc-modal-header-actions">
                <a id="doc-modal-download" class="doc-modal-link" href="#">Unduh</a>
                <a id="doc-modal-newtab" class="doc-modal-link" href="#" target="_blank" rel="noopener" hidden>Tab baru</a>
                <button type="button" id="doc-modal-close" class="doc-modal-close" aria-label="Tutup pratinjau dokumen" title="Tutup (Esc)">×</button>
            </div>
        </div>

        <div class="doc-modal-body">
            <div class="doc-modal-viewer">
                <iframe id="doc-modal-frame" title="Pratinjau dokumen" hidden></iframe>
                <img id="doc-modal-image" alt="Pratinjau gambar lampiran" hidden>
                <div id="doc-modal-fallback" class="doc-modal-fallback" hidden>
                    <span class="doc-modal-fallback-badge">PPTX</span>
                    <strong>Pratinjau langsung tidak tersedia di browser</strong>
                    <p>Berkas presentasi atau format non-PDF perlu diunduh untuk dibuka di Microsoft PowerPoint / LibreOffice Impress.</p>
                    <a id="doc-modal-fallback-download" class="review-btn-approve doc-modal-fallback-btn" href="#">Unduh dokumen</a>
                </div>
            </div>

            <div class="doc-modal-notes">
                <div class="doc-modal-notes-head">
                    <h3>Catatan pemeriksaan</h3>
                    @if (! $verification)
                        <span>Wajib untuk Tidak sesuai</span>
                    @endif
                </div>
                @if (! $verification)
                    <textarea id="doc-modal-note" rows="10" maxlength="2000" placeholder="Tulis bagian yang perlu diperbaiki sambil meninjau dokumen...">{{ old('catatan') }}</textarea>
                    <p class="doc-modal-sync-hint">Catatan otomatis tersinkron dengan formulir utama di halaman pemeriksaan.</p>
                    <div id="doc-modal-note-error" class="review-client-error" role="alert" hidden></div>
                    <div class="doc-modal-note-actions">
                        <button type="button" id="doc-modal-reject" class="review-btn-reject">Tidak sesuai</button>
                        <button type="button" id="doc-modal-approve" class="review-btn-approve">Sesuai</button>
                    </div>
                @else
                    <div class="review-decision-rules">
                        <div class="review-decision-rule">
                            <span class="review-rule-icon {{ $verification->hasil === 'SESUAI' ? 'is-ok' : 'is-err' }}" aria-hidden="true">{{ $verification->hasil === 'SESUAI' ? '✓' : '!' }}</span>
                            <div>
                                <strong>Hasil: {{ $resultLabel }}</strong>
                                <small>{{ $verification->verifier?->name ?? 'Verifikator Pusat' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="doc-modal-readonly-note">{{ $verification->catatan ?: 'Tidak ada catatan.' }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(() => {
    const form = document.getElementById('verifier-review-form');
    const note = document.getElementById('catatan');
    const errorBox = document.getElementById('review-client-error');
    const approveBtn = document.getElementById('review-submit-approve');
    const rejectBtn = document.getElementById('review-submit-reject');

    if (form && note && errorBox) {
        form.addEventListener('submit', (event) => {
            const submitter = event.submitter;
            if (submitter && submitter.value === 'TIDAK_SESUAI' && note.value.trim() === '') {
                event.preventDefault();
                errorBox.textContent = 'Catatan pemeriksaan wajib diisi sebelum menandai laporan Tidak sesuai.';
                errorBox.hidden = false;
                note.focus();
                return;
            }
            errorBox.hidden = true;
        });

        note.addEventListener('input', () => {
            if (note.value.trim() !== '') {
                errorBox.hidden = true;
            }
        });
    }

    const modal = document.getElementById('review-doc-modal');
    if (!modal) return;

    const modalTitle = document.getElementById('doc-modal-title');
    const modalMeta = document.getElementById('doc-modal-meta');
    const modalDownload = document.getElementById('doc-modal-download');
    const modalNewTab = document.getElementById('doc-modal-newtab');
    const modalClose = document.getElementById('doc-modal-close');
    const modalFrame = document.getElementById('doc-modal-frame');
    const modalImage = document.getElementById('doc-modal-image');
    const modalFallback = document.getElementById('doc-modal-fallback');
    const modalFallbackDownload = document.getElementById('doc-modal-fallback-download');
    const modalNote = document.getElementById('doc-modal-note');
    const modalNoteError = document.getElementById('doc-modal-note-error');
    const modalRejectBtn = document.getElementById('doc-modal-reject');
    const modalApproveBtn = document.getElementById('doc-modal-approve');
    const tabs = Array.from(modal.querySelectorAll('[data-doc-tab]'));
    let lastTrigger = null;

    const syncNoteToMain = () => {
        if (modalNote && note) {
            note.value = modalNote.value;
            if (note.value.trim() !== '' && errorBox) errorBox.hidden = true;
            if (modalNote.value.trim() !== '' && modalNoteError) modalNoteError.hidden = true;
        }
    };

    const syncMainToModal = () => {
        if (modalNote && note) {
            modalNote.value = note.value;
            if (modalNoteError) modalNoteError.hidden = true;
        }
    };

    const loadDoc = (dataset) => {
        const docId = dataset.docId || dataset.docTab || '';
        const title = dataset.docTitle || 'Pratinjau Dokumen';
        const meta = dataset.docMeta || '—';
        const kind = dataset.docKind || 'download';
        const previewUrl = dataset.previewUrl || '';
        const downloadUrl = dataset.downloadUrl || '#';

        if (modalTitle) modalTitle.textContent = title;
        if (modalMeta) modalMeta.textContent = meta;
        if (modalDownload) modalDownload.href = downloadUrl;
        if (modalFallbackDownload) modalFallbackDownload.href = downloadUrl;

        tabs.forEach(tab => {
            tab.classList.toggle('is-active', tab.dataset.docTab === docId);
        });

        if (modalFrame) {
            modalFrame.hidden = true;
            modalFrame.removeAttribute('src');
        }
        if (modalImage) {
            modalImage.hidden = true;
            modalImage.removeAttribute('src');
        }
        if (modalFallback) modalFallback.hidden = true;
        if (modalNewTab) {
            modalNewTab.hidden = true;
            modalNewTab.removeAttribute('href');
        }

        if (kind === 'pdf' && previewUrl && modalFrame) {
            modalFrame.src = previewUrl;
            modalFrame.hidden = false;
            if (modalNewTab) {
                modalNewTab.href = previewUrl;
                modalNewTab.hidden = false;
            }
        } else if (kind === 'image' && previewUrl && modalImage) {
            modalImage.src = previewUrl;
            modalImage.hidden = false;
            if (modalNewTab) {
                modalNewTab.href = previewUrl;
                modalNewTab.hidden = false;
            }
        } else if (modalFallback) {
            modalFallback.hidden = false;
        }
    };

    const closeModal = () => {
        if (modal.hidden) return;
        syncNoteToMain();
        modal.hidden = true;
        if (modalFrame) modalFrame.removeAttribute('src');
        if (modalImage) modalImage.removeAttribute('src');
        lastTrigger?.focus();
        lastTrigger = null;
    };

    document.querySelectorAll('[data-doc-preview]').forEach(button => {
        button.addEventListener('click', () => {
            lastTrigger = button;
            syncMainToModal();
            loadDoc(button.dataset);
            modal.hidden = false;
            modalClose?.focus();
        });
    });

    tabs.forEach(tab => {
        tab.addEventListener('click', () => loadDoc(tab.dataset));
    });

    modalClose?.addEventListener('click', closeModal);
    modal.addEventListener('click', (event) => {
        if (event.target === modal) closeModal();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !modal.hidden) closeModal();
    });

    if (modalNote) {
        modalNote.addEventListener('input', syncNoteToMain);
    }

    const submitFromModal = (action) => {
        if (!form || !note) return;
        syncNoteToMain();
        if (action === 'TIDAK_SESUAI' && note.value.trim() === '') {
            if (modalNoteError) {
                modalNoteError.textContent = 'Catatan pemeriksaan wajib diisi sebelum menandai laporan Tidak sesuai.';
                modalNoteError.hidden = false;
            }
            modalNote?.focus();
            return;
        }
        const targetBtn = action === 'TIDAK_SESUAI' ? rejectBtn : approveBtn;
        if (targetBtn && typeof form.requestSubmit === 'function') {
            form.requestSubmit(targetBtn);
        } else {
            targetBtn?.click();
        }
    };

    modalRejectBtn?.addEventListener('click', () => submitFromModal('TIDAK_SESUAI'));
    modalApproveBtn?.addEventListener('click', () => submitFromModal('SESUAI'));
})();
</script>
@endpush
