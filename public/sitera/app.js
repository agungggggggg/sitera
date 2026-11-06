(() => {
  "use strict";

  const ROLE = {
    pelapor: { name: "Pelapor Daerah", short: "Pelapor", initials: "PD", pages: ["dashboard", "usulan", "laporan"] },
    verifikator: { name: "Verifikator Pusat", short: "Verifikator", initials: "VP", pages: ["dashboard", "usulan", "verifikasi", "laporan", "monitoring", "cpcl"] },
    pimpinan: { name: "Pimpinan", short: "Pimpinan", initials: "PM", pages: ["dashboard", "usulan", "laporan", "monitoring"] },
    admin: { name: "Super Admin", short: "Super Admin", initials: "SA", pages: ["dashboard", "cpcl", "admin"] }
  };
  const PAGE = {
    dashboard: ["Ringkasan", "Pantau alur usulan dan laporan dalam satu tempat."],
    usulan: ["Daftar usulan", "Kelola pengajuan daerah, lampiran, serta hasil pemeriksaan pusat."],
    verifikasi: ["Pemeriksaan usulan", "Periksa data usulan dan bukti verifikasi lapangan dari kabupaten."],
    laporan: ["Daftar laporan", "Pantau dokumen laporan, revisi, dan hasil pengesahan verifikator."],
    monitoring: ["Monitoring & rekap", "Ringkasan status pengajuan, kelengkapan dokumen, dan kebutuhan tindak lanjut."],
    cpcl: ["Referensi CPCL", "Lihat data referensi CPCL yang menjadi dasar pengajuan usulan."],
    admin: ["Administrasi", "Kelola akses pengguna dan informasi referensi dalam prototipe."]
  };
  const DEFAULT_USULAN = [
    { id: "u-025", ticket: "UT-2026-025", wilayah: "Kabupaten Cirebon", di: "DI Cisanggarung", kelompok: "P3A Harapan Tirta", jenis: "P3A", periode: "2026", date: "24 Okt 2026", status: "Sesuai", surat: "surat-permohonan-025.pdf", verifikasi: "hasil-verifikasi-025.pdf", note: "DATA DEMO — pengajuan contoh untuk demonstrasi alur.", rounds: 1 },
    { id: "u-024", ticket: "UT-2026-024", wilayah: "Kabupaten Kuningan", di: "DI Cijangkelok", kelompok: "GP3A Rahayu", jenis: "GP3A", periode: "2026", date: "24 Okt 2026", status: "Sesuai", surat: "surat-permohonan-024.pdf", verifikasi: "hasil-verifikasi-024.pdf", note: "DATA DEMO — pengajuan contoh untuk demonstrasi alur.", rounds: 1 },
    { id: "u-023", ticket: "UT-2026-023", wilayah: "Kabupaten Bandung Barat", di: "DI Citarum Barat", kelompok: "Poktan Sinar Padi", jenis: "Poktan", periode: "2026", date: "23 Okt 2026", status: "Sesuai", surat: "surat-permohonan-023.pdf", verifikasi: "hasil-verifikasi-023.pdf", note: "DATA DEMO — pengajuan contoh untuk demonstrasi alur.", rounds: 1 },
    { id: "u-020", ticket: "UT-2026-020", wilayah: "Kabupaten Purwakarta", di: "DI Cibuluh", kelompok: "GP3A Saluyu", jenis: "GP3A", periode: "2026", date: "23 Okt 2026", status: "Sesuai", surat: "surat-permohonan-020.pdf", verifikasi: "hasil-verifikasi-020.pdf", note: "DATA DEMO — pengajuan contoh untuk demonstrasi alur.", rounds: 1 },
    { id: "u-019", ticket: "UT-2026-019", wilayah: "Kabupaten Garut", di: "DI Cimanuk", kelompok: "P3A Citra Cai", jenis: "P3A", periode: "2026", date: "23 Okt 2026", status: "Dalam pemeriksaan", surat: "surat-permohonan-019.pdf", verifikasi: "hasil-verifikasi-019.pdf", note: "DATA DEMO — pengajuan contoh untuk demonstrasi alur.", rounds: 0 },
    { id: "u-018", ticket: "UT-2026-018", wilayah: "Kabupaten Sumedang", di: "DI Cipeles", kelompok: "GP3A Banyu Asri", jenis: "GP3A", periode: "2026", date: "22 Okt 2026", status: "Sesuai", surat: "surat-permohonan-018.pdf", verifikasi: "hasil-verifikasi-018.pdf", note: "DATA DEMO — pengajuan contoh untuk demonstrasi alur.", rounds: 1 },
    { id: "u-017", ticket: "UT-2026-017", wilayah: "Kabupaten Majalengka", di: "DI Cimanis", kelompok: "P3A Tani Makmur", jenis: "P3A", periode: "2026", date: "21 Okt 2026", status: "Perlu perbaikan", surat: "surat-permohonan-017.pdf", verifikasi: "hasil-verifikasi-017.pdf", note: "DATA DEMO — pengajuan contoh untuk demonstrasi alur.", rounds: 1, reviewNote: "Mohon lengkapi dan cocokkan nama kelompok dengan lampiran yang disampaikan." },
    { id: "u-016", ticket: "UT-2026-016", wilayah: "Kabupaten Tasikmalaya", di: "DI Cikunir", kelompok: "GP3A Tirta Wangi", jenis: "GP3A", periode: "2026", date: "20 Okt 2026", status: "Sesuai", surat: "surat-permohonan-016.pdf", verifikasi: "hasil-verifikasi-016.pdf", note: "DATA DEMO — pengajuan contoh untuk demonstrasi alur.", rounds: 1 },
    { id: "u-015", ticket: "UT-2026-015", wilayah: "Kabupaten Bandung", di: "DI Citarik", kelompok: "P3A Lestari Cai", jenis: "P3A", periode: "2026", date: "19 Okt 2026", status: "Sesuai", surat: "surat-permohonan-015.pdf", verifikasi: "hasil-verifikasi-015.pdf", note: "DATA DEMO — pengajuan contoh untuk demonstrasi alur.", rounds: 1 },
    { id: "u-009", ticket: "UT-2026-009", wilayah: "Kabupaten Bekasi", di: "DI Cikarang", kelompok: "P3A Sumber Makmur", jenis: "P3A", periode: "2026", date: "13 Okt 2026", status: "Sesuai", surat: "surat-permohonan-009.pdf", verifikasi: "hasil-verifikasi-009.pdf", note: "DATA DEMO — pengajuan contoh untuk demonstrasi alur.", rounds: 1 },
    { id: "u-014", ticket: "UT-2026-014", wilayah: "Kabupaten Bogor", di: "DI Cisarua", kelompok: "P3A Tirta Mukti", jenis: "P3A", periode: "2026", date: "22 Okt 2026", status: "Diajukan", surat: "surat-permohonan.pdf", verifikasi: "hasil-verifikasi-kabupaten.pdf", note: "Pengajuan bantuan kegiatan irigasi tersier.", rounds: 0 },
    { id: "u-013", ticket: "UT-2026-013", wilayah: "Kabupaten Karawang", di: "DI Ciwadas", kelompok: "GP3A Sumber Rejeki", jenis: "GP3A", periode: "2026", date: "20 Okt 2026", status: "Perlu perbaikan", surat: "surat-permohonan-v2.pdf", verifikasi: "hasil-verifikasi-lapangan.pdf", note: "Perlu penyesuaian informasi pada dokumen lampiran.", rounds: 1, reviewNote: "Mohon pastikan lampiran sesuai dengan data kelompok dan lokasi yang diajukan." },
    { id: "u-012", ticket: "UT-2026-012", wilayah: "Kabupaten Subang", di: "DI Tarum Timur", kelompok: "Poktan Mekar Tani", jenis: "Poktan", periode: "2026", date: "19 Okt 2026", status: "Dalam pemeriksaan", surat: "surat-bantuan.pdf", verifikasi: "hasil-verifikasi-subang.pdf", note: "Data dan dokumen sedang ditinjau oleh petugas pusat.", rounds: 0 },
    { id: "u-011", ticket: "UT-2026-011", wilayah: "Kabupaten Cianjur", di: "DI Cikondang", kelompok: "P3A Cibening", jenis: "P3A", periode: "2026", date: "17 Okt 2026", status: "Sesuai", surat: "surat-permohonan.pdf", verifikasi: "hasil-verifikasi-cianjur.pdf", note: "Data usulan telah dinyatakan sesuai oleh pemeriksa pusat.", rounds: 0 },
    { id: "u-010", ticket: "UT-2026-010", wilayah: "Kabupaten Indramayu", di: "DI Jatiluhur", kelompok: "GP3A Maju Bersama", jenis: "GP3A", periode: "2026", date: "14 Okt 2026", status: "Diajukan", surat: "surat-usulan.pdf", verifikasi: "hasil-verifikasi.pdf", note: "Lampiran tersedia untuk pemeriksaan pusat.", rounds: 0 }
  ];
  const DEFAULT_LAPORAN = [
    { id: "l-020", ticket: "LR-2026-020", usulanId: "u-020", usulan: "UT-2026-020", wilayah: "Kabupaten Purwakarta", kelompok: "GP3A Saluyu", date: "24 Okt 2026", status: "Diajukan", jenis: "Laporan akhir", bast: "BAST-saluyu.pdf", lpj: "LPJ-saluyu.pdf", kuitansi: 3, foto: "foto-kegiatan-saluyu.zip", note: "DATA DEMO — laporan contoh diterima untuk pemeriksaan." },
    { id: "l-019", ticket: "LR-2026-019", usulanId: "u-019", usulan: "UT-2026-019", wilayah: "Kabupaten Garut", kelompok: "P3A Citra Cai", date: "23 Okt 2026", status: "Dalam pemeriksaan", jenis: "Laporan akhir", bast: "BAST-citra-cai.pdf", lpj: "LPJ-citra-cai.pdf", kuitansi: 4, foto: "dokumentasi-citra-cai.zip", note: "DATA DEMO — dokumen menunggu pemeriksaan verifikator." },
    { id: "l-018", ticket: "LR-2026-018", usulanId: "u-018", usulan: "UT-2026-018", wilayah: "Kabupaten Sumedang", kelompok: "GP3A Banyu Asri", date: "22 Okt 2026", status: "Disahkan", jenis: "Laporan akhir", bast: "BAST-banyu-asri.pdf", lpj: "LPJ-banyu-asri.pdf", kuitansi: 5, foto: "dokumentasi-banyu-asri.zip", note: "DATA DEMO — contoh laporan yang sudah disahkan." },
    { id: "l-017", ticket: "LR-2026-017", usulanId: "u-017", usulan: "UT-2026-017", wilayah: "Kabupaten Majalengka", kelompok: "P3A Tani Makmur", date: "21 Okt 2026", status: "Perlu perbaikan", jenis: "Laporan akhir", bast: "BAST-tani-makmur.pdf", lpj: "LPJ-tani-makmur-revisi.pdf", kuitansi: 2, foto: "foto-tani-makmur.zip", note: "DATA DEMO — contoh laporan yang perlu perbaikan.", reviewNote: "Mohon cocokkan rekap kuitansi dengan rincian biaya pada LPJ." },
    { id: "l-016", ticket: "LR-2026-016", usulanId: "u-016", usulan: "UT-2026-016", wilayah: "Kabupaten Tasikmalaya", kelompok: "GP3A Tirta Wangi", date: "20 Okt 2026", status: "Dalam pemeriksaan", jenis: "Laporan akhir", bast: "BAST-tirta-wangi.pdf", lpj: "LPJ-tirta-wangi.pdf", kuitansi: 3, foto: "dokumentasi-tirta-wangi.zip", note: "DATA DEMO — dokumen menunggu pemeriksaan verifikator." },
    { id: "l-015", ticket: "LR-2026-015", usulanId: "u-015", usulan: "UT-2026-015", wilayah: "Kabupaten Bandung", kelompok: "P3A Lestari Cai", date: "19 Okt 2026", status: "Disahkan", jenis: "Laporan akhir", bast: "BAST-lestari-cai.pdf", lpj: "LPJ-lestari-cai.pdf", kuitansi: 4, foto: "dokumentasi-lestari-cai.zip", note: "DATA DEMO — contoh laporan yang sudah disahkan." },
    { id: "l-014", ticket: "LR-2026-014", usulanId: "u-014", usulan: "UT-2026-014", wilayah: "Kabupaten Bogor", kelompok: "P3A Tirta Mukti", date: "18 Okt 2026", status: "Draf", jenis: "Laporan akhir", bast: "", lpj: "", kuitansi: 0, foto: "", note: "DATA DEMO — draf laporan menunggu kelengkapan lampiran." },
    { id: "l-013", ticket: "LR-2026-013", usulanId: "u-013", usulan: "UT-2026-013", wilayah: "Kabupaten Karawang", kelompok: "GP3A Sumber Rejeki", date: "17 Okt 2026", status: "Disahkan", jenis: "Laporan akhir", bast: "BAST-sumber-rejeki.pdf", lpj: "LPJ-sumber-rejeki.pdf", kuitansi: 6, foto: "dokumentasi-sumber-rejeki.zip", note: "DATA DEMO — contoh laporan yang sudah disahkan." },
    { id: "l-012", ticket: "LR-2026-012", usulanId: "u-012", usulan: "UT-2026-012", wilayah: "Kabupaten Subang", kelompok: "Poktan Mekar Tani", date: "16 Okt 2026", status: "Diajukan", jenis: "Laporan akhir", bast: "BAST-mekar-tani.pdf", lpj: "LPJ-mekar-tani.pdf", kuitansi: 2, foto: "foto-mekar-tani.zip", note: "DATA DEMO — laporan contoh diajukan untuk pemeriksaan." },
    { id: "l-011", ticket: "LR-2026-011", usulanId: "u-023", usulan: "UT-2026-023", wilayah: "Kabupaten Bandung Barat", kelompok: "Poktan Sinar Padi", date: "15 Okt 2026", status: "Dalam pemeriksaan", jenis: "Laporan akhir", bast: "BAST-sinar-padi.pdf", lpj: "LPJ-sinar-padi.pdf", kuitansi: 3, foto: "dokumentasi-sinar-padi.zip", note: "DATA DEMO — dokumen menunggu pemeriksaan verifikator." },
    { id: "l-010", ticket: "LR-2026-010", usulanId: "u-024", usulan: "UT-2026-024", wilayah: "Kabupaten Kuningan", kelompok: "GP3A Rahayu", date: "14 Okt 2026", status: "Disahkan", jenis: "Laporan akhir", bast: "BAST-rahayu.pdf", lpj: "LPJ-rahayu.pdf", kuitansi: 4, foto: "dokumentasi-rahayu.zip", note: "DATA DEMO — contoh laporan yang sudah disahkan." },
    { id: "l-009", ticket: "LR-2026-009", usulanId: "u-025", usulan: "UT-2026-025", wilayah: "Kabupaten Cirebon", kelompok: "P3A Harapan Tirta", date: "13 Okt 2026", status: "Perlu perbaikan", jenis: "Laporan akhir", bast: "BAST-harapan-tirta.pdf", lpj: "LPJ-harapan-tirta-revisi.pdf", kuitansi: 2, foto: "foto-harapan-tirta.zip", note: "DATA DEMO — contoh laporan yang perlu perbaikan.", reviewNote: "Mohon pastikan lampiran dan nama kelompok konsisten dengan dokumen usulan." },
    { id: "l-008", ticket: "LR-2026-008", usulanId: "u-011", usulan: "UT-2026-011", wilayah: "Kabupaten Cianjur", kelompok: "P3A Cibening", date: "23 Okt 2026", status: "Dalam pemeriksaan", jenis: "Laporan akhir", bast: "BAST-cibening.pdf", lpj: "LPJ-cibening.pdf", kuitansi: 3, foto: "dokumentasi-kegiatan.zip", note: "Dokumen laporan sedang diperiksa." },
    { id: "l-007", ticket: "LR-2026-007", usulanId: "u-010", usulan: "UT-2026-010", wilayah: "Kabupaten Indramayu", kelompok: "GP3A Maju Bersama", date: "21 Okt 2026", status: "Perlu perbaikan", jenis: "Laporan akhir", bast: "BAST-indramayu.pdf", lpj: "LPJ-perbaikan.pdf", kuitansi: 2, foto: "foto-kegiatan.pdf", note: "Mohon lengkapi informasi kuitansi pendukung.", reviewNote: "Mohon periksa kembali keterhubungan kuitansi dengan rincian pada LPJ." },
    { id: "l-006", ticket: "LR-2026-006", usulanId: "u-009", usulan: "UT-2026-009", wilayah: "Kabupaten Bekasi", kelompok: "P3A Sumber Makmur", date: "16 Okt 2026", status: "Disahkan", jenis: "Laporan akhir", bast: "BAST-bekasi.pdf", lpj: "LPJ-bekasi.pdf", kuitansi: 4, foto: "foto-bekasi.pdf", note: "Laporan disahkan oleh verifikator pusat." }
  ];
  // Contoh CPCL untuk review UI. Kolom yang belum ada pada materi sumber ditandai belum dikonfirmasi.
  const CPCL = [
    { kode: "CP-00081", namaDI: "DI Cisarua", di: "DI Cisarua", kewenangan: "Belum dikonfirmasi", keterangan: "Data simulasi untuk review UI", provinsi: "Belum dikonfirmasi", kabKota: "Kabupaten Bogor", wilayah: "Kabupaten Bogor", kecamatan: "Belum dikonfirmasi", desaKelurahan: "Belum dikonfirmasi", kelTani: "P3A Tirta Mukti", kelompok: "P3A Tirta Mukti", jenis: "P3A", namaKetua: "Belum dikonfirmasi", ketua: "Belum dikonfirmasi", realisasi: "Belum dikonfirmasi", luasHa: "18,5", luas: "18,5 ha", x: "Belum dikonfirmasi", y: "Belum dikonfirmasi" },
    { kode: "CP-00082", namaDI: "DI Ciwadas", di: "DI Ciwadas", kewenangan: "Belum dikonfirmasi", keterangan: "Data simulasi untuk review UI", provinsi: "Belum dikonfirmasi", kabKota: "Kabupaten Karawang", wilayah: "Kabupaten Karawang", kecamatan: "Belum dikonfirmasi", desaKelurahan: "Belum dikonfirmasi", kelTani: "GP3A Sumber Rejeki", kelompok: "GP3A Sumber Rejeki", jenis: "GP3A", namaKetua: "Belum dikonfirmasi", ketua: "Belum dikonfirmasi", realisasi: "Belum dikonfirmasi", luasHa: "24", luas: "24 ha", x: "Belum dikonfirmasi", y: "Belum dikonfirmasi" },
    { kode: "CP-00083", namaDI: "DI Tarum Timur", di: "DI Tarum Timur", kewenangan: "Belum dikonfirmasi", keterangan: "Data simulasi untuk review UI", provinsi: "Belum dikonfirmasi", kabKota: "Kabupaten Subang", wilayah: "Kabupaten Subang", kecamatan: "Belum dikonfirmasi", desaKelurahan: "Belum dikonfirmasi", kelTani: "Poktan Mekar Tani", kelompok: "Poktan Mekar Tani", jenis: "Poktan", namaKetua: "Belum dikonfirmasi", ketua: "Belum dikonfirmasi", realisasi: "Belum dikonfirmasi", luasHa: "16", luas: "16 ha", x: "Belum dikonfirmasi", y: "Belum dikonfirmasi" },
    { kode: "CP-00084", namaDI: "DI Cikondang", di: "DI Cikondang", kewenangan: "Belum dikonfirmasi", keterangan: "Data simulasi untuk review UI", provinsi: "Belum dikonfirmasi", kabKota: "Kabupaten Cianjur", wilayah: "Kabupaten Cianjur", kecamatan: "Belum dikonfirmasi", desaKelurahan: "Belum dikonfirmasi", kelTani: "P3A Cibening", kelompok: "P3A Cibening", jenis: "P3A", namaKetua: "Belum dikonfirmasi", ketua: "Belum dikonfirmasi", realisasi: "Belum dikonfirmasi", luasHa: "12", luas: "12 ha", x: "Belum dikonfirmasi", y: "Belum dikonfirmasi" },
    { kode: "CP-00085", namaDI: "DI Jatiluhur", di: "DI Jatiluhur", kewenangan: "Belum dikonfirmasi", keterangan: "Data simulasi untuk review UI", provinsi: "Belum dikonfirmasi", kabKota: "Kabupaten Indramayu", wilayah: "Kabupaten Indramayu", kecamatan: "Belum dikonfirmasi", desaKelurahan: "Belum dikonfirmasi", kelTani: "GP3A Maju Bersama", kelompok: "GP3A Maju Bersama", jenis: "GP3A", namaKetua: "Belum dikonfirmasi", ketua: "Belum dikonfirmasi", realisasi: "Belum dikonfirmasi", luasHa: "31", luas: "31 ha", x: "Belum dikonfirmasi", y: "Belum dikonfirmasi" }
  ];

  const $ = (selector, root = document) => root.querySelector(selector);
  const $$ = (selector, root = document) => Array.from(root.querySelectorAll(selector));
  const escapeHTML = value => String(value == null ? "" : value).replace(/[&<>"']/g, char => ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" }[char]));
  const clone = value => JSON.parse(JSON.stringify(value));
  function loadArray(key, fallback) {
    try {
      const saved = localStorage.getItem(key);
      if (saved) {
        const data = JSON.parse(saved);
        if (Array.isArray(data)) {
          const savedIds = new Set(data.map(item => item && item.id).filter(Boolean));
          const missingDemoRows = clone(fallback).filter(item => item && item.id && !savedIds.has(item.id));
          return [...missingDemoRows, ...data];
        }
      }
    } catch (_) {}
    return clone(fallback);
  }
  const state = {
    role: "pelapor",
    page: "dashboard",
    usulan: loadArray("sitera-demo-usulan-v1", DEFAULT_USULAN),
    laporan: loadArray("sitera-demo-laporan-v1", DEFAULT_LAPORAN),
    accounts: loadArray("sitera-demo-accounts-v1", [
      { name: "Nadia Pratama", email: "pelapor.demo@contoh.id", wilayah: "Kabupaten Bogor", role: "pelapor", active: true },
      { name: "Raka Wibowo", email: "verifikator.demo@contoh.id", wilayah: "Pusat", role: "verifikator", active: true },
      { name: "Dewi Kartika", email: "pimpinan.demo@contoh.id", wilayah: "Pusat", role: "pimpinan", active: true }
    ]),
    noticeDismissed: false
  };
  let toastTimer = null;
  function persist() {
    try {
      localStorage.setItem("sitera-demo-usulan-v1", JSON.stringify(state.usulan));
      localStorage.setItem("sitera-demo-laporan-v1", JSON.stringify(state.laporan));
      localStorage.setItem("sitera-demo-accounts-v1", JSON.stringify(state.accounts));
    } catch (_) {}
  }
  function toast(message) {
    const el = $("#toast");
    if (!el) return;
    el.textContent = message;
    el.classList.add("show");
    clearTimeout(toastTimer);
    toastTimer = setTimeout(() => el.classList.remove("show"), 2900);
  }
  function statusClass(status) {
    if (["Sesuai", "Disahkan", "Lengkap", "Aktif"].includes(status)) return "badge-green";
    if (["Diajukan", "Perlu perbaikan", "Perlu tindak lanjut"].includes(status)) return "badge-amber";
    if (["Dalam pemeriksaan", "Draft", "Draf"].includes(status)) return "badge-blue";
    if (["Nonaktif", "Ditolak", "Dibatalkan"].includes(status)) return "badge-rose";
    return "badge-gray";
  }
  function badge(status) {
    return '<span class="status-badge ' + statusClass(status) + '">' + escapeHTML(status) + '</span>';
  }
  function countStatus(items, statuses) {
    return items.filter(item => statuses.includes(item.status)).length;
  }
  function metric(label, value, meta, icon, iconClass = "") {
    return '<article class="metric-card"><div class="metric-top"><span>' + label + '</span><span class="metric-icon ' + iconClass + '">' + icon + '</span></div><div class="metric-value">' + value + '</div><div class="metric-meta">' + meta + '</div></article>';
  }
  function topPanel(title, subtitle, body, action = "") {
    return '<section class="panel"><div class="panel-head"><div><h2 class="panel-title">' + title + '</h2><div class="panel-subtitle">' + subtitle + '</div></div>' + action + '</div>' + body + '</section>';
  }
  function currentUserLabel() {
    return ROLE[state.role] || ROLE.pelapor;
  }
  function setRole(role) {
    const selectedRole = ROLE[role] ? role : "pelapor";
    if (selectedRole === "pelapor") {
      window.location.assign("/sitera/pelapor-review.html");
      return;
    }
    if (selectedRole === "verifikator") {
      window.location.assign("/sitera/verifikator-review.html");
      return;
    }
    if (selectedRole === "pimpinan") {
      window.location.assign("/sitera/pimpinan-review.html");
      return;
    }
    if (selectedRole === "admin") {
      window.location.assign("/sitera/superadmin-review.html");
      return;
    }
    const note = $(".login-mode-note");
    if (note) note.innerHTML = '<span class="mode-note-dot"></span><span><strong>Halaman dalam pengembangan.</strong> Peran ' + ROLE[selectedRole].name + ' sedang diselaraskan dengan SRS v0.12. Pratinjau Pelapor, Verifikator, Pimpinan, dan Super Admin tersedia.</span>';
    return;
    state.role = selectedRole;
    const allowed = ROLE[state.role].pages;
    if (!allowed.includes(state.page)) state.page = "dashboard";
    $("#login-screen").hidden = true;
    $("#app-screen").hidden = false;
    $("#profile-name").textContent = ROLE[state.role].name;
    $("#profile-role").textContent = ROLE[state.role].short;
    $("#avatar").textContent = ROLE[state.role].initials;
    $("#admin-nav").hidden = state.role !== "admin";
    updateNavigation();
    renderPage();
  }
  function updateNavigation() {
    const allowed = ROLE[state.role].pages;
    $$(".nav-link").forEach(link => {
      const page = link.dataset.page;
      link.hidden = !allowed.includes(page);
      link.classList.toggle("active", page === state.page);
    });
    $("#review-count").textContent = String(countStatus(state.usulan, ["Diajukan", "Dalam pemeriksaan"]));
  }
  function go(page) {
    if (!ROLE[state.role].pages.includes(page)) {
      toast("Halaman ini tidak tersedia untuk role prototipe ini.");
      return;
    }
    state.page = page;
    updateNavigation();
    renderPage();
    window.scrollTo({ top: 0, behavior: "smooth" });
  }
  function setPageHeader() {
    const details = PAGE[state.page] || PAGE.dashboard;
    $("#breadcrumb-current").textContent = details[0];
    $("#page-title").textContent = state.page === "dashboard"
      ? (state.role === "pelapor" ? "Ringkasan pengajuan saya" : state.role === "verifikator" ? "Ringkasan pemeriksaan" : state.role === "pimpinan" ? "Ringkasan monitoring" : "Ringkasan administrasi")
      : details[0];
    $("#page-subtitle").textContent = details[1];
    const primary = $("#primary-action");
    let label = "", action = "";
    if (state.role === "pelapor" && ["dashboard", "usulan"].includes(state.page)) { label = "Buat usulan"; action = "new-usulan"; }
    else if (state.role === "pelapor" && state.page === "laporan") { label = "Buat laporan"; action = "new-laporan"; }
    else if (state.role === "verifikator" && state.page === "verifikasi") { label = "Mulai pemeriksaan"; action = "go-verifikasi"; }
    else if (state.role === "verifikator" && state.page === "laporan") { label = "Tinjau laporan"; action = "go-laporan-review"; }
    else if (state.role === "admin" && state.page === "admin") { label = "Tambah pengguna"; action = "new-account"; }
    else if (state.role === "admin" && state.page === "cpcl") { label = "Impor CPCL"; action = "import-cpcl"; }
    else if (state.role === "pimpinan" && state.page === "monitoring") { label = "Ekspor rekap"; action = "export"; }
    else if (state.role === "verifikator" && state.page === "monitoring") { label = "Ekspor rekap"; action = "export"; }
    else if (state.role === "pelapor") { label = "Buat usulan"; action = "new-usulan"; }
    else if (state.role === "verifikator") { label = "Buka antrean"; action = "go-verifikasi"; }
    else if (state.role === "admin") { label = "Kelola akun"; action = "new-account"; }
    else { label = "Lihat monitoring"; action = "go-monitoring"; }
    primary.dataset.action = action;
    primary.querySelector("span:last-child").textContent = label;
    primary.hidden = state.page === "verifikasi" && state.role === "verifikator" && label === "Mulai pemeriksaan";
  }
  function renderPage() {
    updateNavigation();
    setPageHeader();
    if (!state.noticeDismissed) $(".notice-strip").hidden = false;
    const root = $("#page-content");
    const renderers = {
      dashboard: renderDashboard, usulan: renderUsulan, verifikasi: renderVerifikasi,
      laporan: renderLaporan, monitoring: renderMonitoring, cpcl: renderCPCL, admin: renderAdmin
    };
    root.innerHTML = (renderers[state.page] || renderDashboard)();
    if (state.page === "usulan") refreshUsulanFilterOptions();
  }
  function renderDashboard() {
    const role = state.role;
    let cards = "";
    if (role === "pelapor") {
      const mine = state.usulan; // prototype includes all sample rows; real app must scope this server-side
      cards = metric("Total usulan", mine.length, '<span class="up">Semua periode</span> · data contoh', "▤") +
        metric("Menunggu pemeriksaan", countStatus(mine, ["Diajukan", "Dalam pemeriksaan"]), "Pengajuan belum selesai", "◷", "amber") +
        metric("Perlu perbaikan", countStatus(mine, ["Perlu perbaikan"]), "Periksa catatan verifikator", "!", "rose") +
        metric("Usulan sesuai", countStatus(mine, ["Sesuai"]), "Hasil pemeriksaan pusat", "✓", "blue");
    } else if (role === "verifikator") {
      cards = metric("Antrean usulan", countStatus(state.usulan, ["Diajukan", "Dalam pemeriksaan"]), "Perlu ditinjau petugas", "▤", "amber") +
        metric("Usulan perlu revisi", countStatus(state.usulan, ["Perlu perbaikan"]), "Menunggu perbaikan pelapor", "↺", "rose") +
        metric("Antrean laporan", countStatus(state.laporan, ["Diajukan", "Dalam pemeriksaan"]), "Dokumen menunggu tinjauan", "▧", "blue") +
        metric("Laporan disahkan", countStatus(state.laporan, ["Disahkan"]), "Selesai diperiksa pusat", "✓");
    } else if (role === "pimpinan") {
      cards = metric("Total usulan", state.usulan.length, "ID usulan unik · contoh", "▤") +
        metric("Usulan dalam proses", countStatus(state.usulan, ["Diajukan", "Dalam pemeriksaan"]), "Diajukan atau diperiksa", "◷", "amber") +
        metric("Total laporan", state.laporan.length, "ID laporan unik · contoh", "▧", "blue") +
        metric("Laporan disahkan", countStatus(state.laporan, ["Disahkan"]), "Pengesahan oleh verifikator", "✓");
    } else {
      cards = metric("Pengguna terdaftar", state.accounts.length, "Akun contoh pada prototipe", "♙") +
        metric("Akun aktif", state.accounts.filter(a => a.active).length, "Status akun simulasi", "✓") +
        metric("Referensi CPCL", CPCL.length, "Baris contoh saja", "⌕", "blue") +
        metric("Log aktivitas", 12, "Angka ilustratif, bukan audit log", "≋", "amber");
    }
    const rows = (role === "verifikator" ? state.usulan.filter(x => ["Diajukan", "Dalam pemeriksaan", "Perlu perbaikan"].includes(x.status)).slice(0, 4) :
      role === "admin" ? [] : state.usulan.slice(0, 4)).map(item => '<tr><td><span class="ticket-code">' + escapeHTML(item.ticket) + '</span><span class="cell-sub">' + escapeHTML(item.date) + '</span></td><td><span class="cell-title">' + escapeHTML(item.kelompok) + '</span><span class="cell-sub">' + escapeHTML(item.wilayah) + '</span></td><td>' + escapeHTML(item.di) + '</td><td>' + badge(item.status) + '</td><td><button class="row-action" data-action="detail-usulan" data-id="' + escapeHTML(item.id) + '" aria-label="Lihat detail">↗</button></td></tr>').join("");
    const table = '<div class="data-table-wrap"><table class="data-table"><thead><tr><th>Nomor / tanggal</th><th>Kelompok & wilayah</th><th>Daerah irigasi</th><th>Status</th><th></th></tr></thead><tbody>' + (rows || '<tr><td colspan="5">Belum ada data untuk ditampilkan.</td></tr>') + '</tbody></table></div><div class="table-bottom"><span>Menampilkan ' + Math.min(4, role === "verifikator" ? state.usulan.filter(x => ["Diajukan", "Dalam pemeriksaan", "Perlu perbaikan"].includes(x.status)).length : state.usulan.length) + ' data contoh</span><button class="panel-action" data-page="usulan">Lihat semua usulan →</button></div>';
    const chart = '<div class="chart-area"><div class="chart-legend"><span><i class="legend-dot" style="background:#78b990"></i>Usulan</span><span><i class="legend-dot" style="background:#d4e7da"></i>Laporan</span></div><div class="bar-chart">' + [
      ["Mei", 47, 27], ["Jun", 64, 35], ["Jul", 51, 42], ["Agu", 76, 50], ["Sep", 63, 39], ["Okt", 90, 62]
    ].map(x => '<div class="bar-group"><div class="bar" style="height:' + x[1] + '%"></div><div class="bar alt" style="height:' + x[2] + '%"></div><span class="bar-group-label">' + x[0] + '</span></div>').join("") + '</div><div class="chart-axis"><span>Ringkasan data simulasi</span><span>Mei–Okt 2026</span></div></div>';
    const statusBody = '<div class="status-list">' +
      [['Usulan diajukan', countStatus(state.usulan, ["Diajukan"]), ""], ["Dalam pemeriksaan", countStatus(state.usulan, ["Dalam pemeriksaan"]), "amber"], ["Perlu perbaikan", countStatus(state.usulan, ["Perlu perbaikan"]), "rose"], ["Usulan sesuai", countStatus(state.usulan, ["Sesuai"]), "blue"]].map(x => '<div class="status-row"><span class="status-mark ' + x[2] + '"></span><div class="status-text"><strong>' + x[0] + '</strong><span>Status terkini</span></div><span class="status-count">' + x[1] + '</span></div>').join("") +
      '</div>';
    const quick = role === "pelapor"
      ? '<div class="quick-grid"><button class="quick-card" data-action="new-usulan"><span class="quick-icon">＋</span><span class="quick-copy"><strong>Buat usulan baru</strong><span>Isi data pengajuan dan lampirkan dua dokumen wajib.</span></span></button><button class="quick-card" data-page="usulan"><span class="quick-icon">↺</span><span class="quick-copy"><strong>Periksa status usulan</strong><span>Lihat hasil pemeriksaan dan catatan perbaikan.</span></span></button><button class="quick-card" data-page="laporan"><span class="quick-icon">▧</span><span class="quick-copy"><strong>Dokumen laporan</strong><span>Lihat laporan yang terkait dengan usulan.</span></span></button></div>'
      : role === "verifikator"
        ? '<div class="quick-grid"><button class="quick-card" data-page="verifikasi"><span class="quick-icon">✓</span><span class="quick-copy"><strong>Periksa usulan</strong><span>Tinjau dokumen kabupaten dan catat hasil pemeriksaan pusat.</span></span></button><button class="quick-card" data-page="laporan"><span class="quick-icon">▧</span><span class="quick-copy"><strong>Pemeriksaan laporan</strong><span>Minta perbaikan atau sahkan laporan yang memenuhi syarat.</span></span></button><button class="quick-card" data-page="monitoring"><span class="quick-icon">◫</span><span class="quick-copy"><strong>Buka monitoring</strong><span>Lihat ringkasan status pengajuan dan laporan.</span></span></button></div>'
        : role === "pimpinan"
          ? '<div class="quick-grid"><button class="quick-card" data-page="monitoring"><span class="quick-icon">◫</span><span class="quick-copy"><strong>Monitoring status</strong><span>Pantau status usulan, laporan, dan kelengkapan dokumen.</span></span></button><button class="quick-card" data-page="usulan"><span class="quick-icon">▤</span><span class="quick-copy"><strong>Daftar usulan</strong><span>Telusuri usulan menurut wilayah dan status terakhir.</span></span></button><button class="quick-card" data-action="export"><span class="quick-icon">⇩</span><span class="quick-copy"><strong>Ekspor ringkasan</strong><span>Simulasikan proses ekspor sesuai filter aktif.</span></span></button></div>'
          : '<div class="quick-grid"><button class="quick-card" data-page="admin"><span class="quick-icon">♙</span><span class="quick-copy"><strong>Kelola akun</strong><span>Simulasikan penambahan dan perubahan status akun.</span></span></button><button class="quick-card" data-page="cpcl"><span class="quick-icon">⌕</span><span class="quick-copy"><strong>Referensi CPCL</strong><span>Lihat contoh data referensi kelompok dan wilayah.</span></span></button><button class="quick-card" data-page="audit"><span class="quick-icon">≋</span><span class="quick-copy"><strong>Audit aktivitas</strong><span>Audit log asli belum terhubung pada prototipe ini.</span></span></button></div>';
    const latest = role === "admin"
      ? '<div class="panel"><div class="panel-head"><div><h2 class="panel-title">Pengingat administrasi</h2><div class="panel-subtitle">Hal yang perlu ditetapkan sebelum implementasi</div></div></div><div class="timeline"><div class="timeline-item"><span class="timeline-icon">1</span><div class="timeline-copy"><strong>Pastikan matriks hak akses</strong><span>Aturan cakupan data pelapor dan akses tiap role masih perlu konfirmasi di D-01.</span></div></div><div class="timeline-item"><span class="timeline-icon amber">2</span><div class="timeline-copy"><strong>Tetapkan format dokumen</strong><span>Template, format dan ukuran file mengikuti keputusan D-08.</span></div></div></div></div>'
      : topPanel("Aktivitas terbaru", "Status usulan dalam data contoh", table);
    return '<div class="metric-grid">' + cards + '</div>' +
      '<div class="dashboard-grid">' + topPanel(role === "pimpinan" ? "Tren pengajuan & laporan" : role === "admin" ? "Aktivitas pengajuan" : "Ringkasan pengajuan", "Visualisasi ilustratif • bukan angka produksi", chart) +
      topPanel(role === "pelapor" ? "Status usulan" : role === "verifikator" ? "Status dalam antrean" : "Komposisi status usulan", "Berdasarkan status terkini", statusBody, '<button class="panel-action" data-page="' + (role === "verifikator" ? "verifikasi" : "usulan") + '">Lihat detail →</button>') + '</div>' +
      (role === "admin" ? latest : '<div class="panel table-panel"><div class="panel-head"><div><h2 class="panel-title">' + (role === "pelapor" ? "Usulan terbaru" : role === "verifikator" ? "Perlu perhatian" : "Usulan terbaru") + '</h2><div class="panel-subtitle">Daftar ringkas untuk akses cepat</div></div><button class="panel-action" data-page="' + (role === "verifikator" ? "verifikasi" : "usulan") + '">Lihat semua →</button></div>' + table + '</div>');
  }

  function filterBar(options = {}) {
    const regions = '<option value="">Semua wilayah</option>' + [...new Set(state.usulan.map(x => x.wilayah))].map(x => '<option>' + escapeHTML(x) + '</option>').join("");
    return '<div class="table-toolbar"><div class="toolbar-left"><div class="search-box"><input type="search" class="table-search" placeholder="Cari nomor, kelompok, wilayah…" aria-label="Cari data"></div><select class="filter-select filter-status" aria-label="Filter status"><option value="">Semua status</option>' + (options.statuses || ["Diajukan", "Dalam pemeriksaan", "Perlu perbaikan", "Sesuai", "Disahkan", "Draf"]).map(x => '<option>' + escapeHTML(x) + '</option>').join("") + '</select>' + (options.region === false ? "" : '<select class="filter-select filter-region" aria-label="Filter wilayah">' + regions + '</select>') + '</div><div class="toolbar-right"><span class="empty-chip" id="result-count">' + (options.count || 0) + ' data</span></div></div>';
  }
  function dataTable(headers, rows, emptyText = "Tidak ada data yang sesuai dengan filter.") {
    return '<div class="data-table-wrap"><table class="data-table"><thead><tr>' + headers.map(x => '<th>' + x + '</th>').join("") + '</tr></thead><tbody>' + (rows || '<tr><td colspan="' + headers.length + '"><div class="empty-state"><span>⌕</span><strong>Data tidak ditemukan</strong><span>' + emptyText + '</span></div></td></tr>') + '</tbody></table></div>';
  }
  function usulanMetadata(item) {
    const groupName = String(item.kelompok || "");
    const regionName = String(item.wilayah || "");
    const cpcl = CPCL.find(x => x.kelTani.toLowerCase() === groupName.toLowerCase() && x.kabKota.toLowerCase() === regionName.toLowerCase())
      || CPCL.find(x => x.kelTani.toLowerCase() === groupName.toLowerCase());
    return {
      provinsi: cpcl?.provinsi || "Belum dikonfirmasi",
      kabupaten: regionName || cpcl?.kabKota || "Belum dikonfirmasi",
      kecamatan: cpcl?.kecamatan || "Belum dikonfirmasi",
      desa: cpcl?.desaKelurahan || "Belum dikonfirmasi",
      kelompok: groupName || cpcl?.kelTani || "Belum dikonfirmasi"
    };
  }
  const USULAN_FILTERS = [
    { key: "provinsi", selector: ".filter-provinsi", label: "Semua provinsi" },
    { key: "kabupaten", selector: ".filter-kabupaten", label: "Semua kabupaten/wilayah" },
    { key: "kecamatan", selector: ".filter-kecamatan", label: "Semua kecamatan" },
    { key: "desa", selector: ".filter-desa", label: "Semua desa/kelurahan" },
    { key: "kelompok", selector: ".filter-kelompok", label: "Semua kelompok tani" }
  ];
  function applyUsulanFilters() {
    const panel = $("#page-content .table-panel");
    if (!panel) return;
    const search = ($(".table-search", panel)?.value || "").toLowerCase().trim();
    const status = $(".filter-status", panel)?.value || "";
    const selected = {};
    USULAN_FILTERS.forEach(filter => { selected[filter.key] = $(filter.selector, panel)?.value || ""; });
    let count = 0;
    $$("tbody tr", panel).forEach(row => {
      const text = row.textContent.toLowerCase();
      const match = text.includes(search)
        && (!status || row.dataset.status === status)
        && USULAN_FILTERS.every(filter => !selected[filter.key] || row.dataset[filter.key] === selected[filter.key]);
      row.hidden = !match;
      if (match) count++;
    });
    const result = $("#result-count", panel);
    if (result) result.textContent = count + " data";
  }
  function refreshUsulanFilterOptions() {
    const panel = $("#page-content .table-panel");
    if (!panel) return;
    const collator = new Intl.Collator("id", { sensitivity: "base", numeric: true });
    USULAN_FILTERS.forEach(filter => {
      const select = $(filter.selector, panel);
      if (!select) return;
      const previousValue = select.value;
      const otherSelections = {};
      USULAN_FILTERS.forEach(other => { otherSelections[other.key] = other.key === filter.key ? "" : ($(other.selector, panel)?.value || ""); });
      const matches = state.usulan.filter(item => {
        const metadata = usulanMetadata(item);
        return USULAN_FILTERS.every(other => !otherSelections[other.key] || metadata[other.key] === otherSelections[other.key]);
      });
      const values = [...new Set(matches.map(item => usulanMetadata(item)[filter.key]).filter(Boolean))].sort(collator.compare);
      select.innerHTML = '<option value="">' + escapeHTML(filter.label) + '</option>' + values.map(value => '<option value="' + escapeHTML(value) + '">' + escapeHTML(value) + '</option>').join("");
      select.value = values.includes(previousValue) ? previousValue : "";
    });
    applyUsulanFilters();
  }
  function rowUsulan(item, mode = "normal") {
    const action = mode === "review" ? '<button class="btn btn-secondary btn-sm" data-action="review-usulan" data-id="' + escapeHTML(item.id) + '">Periksa</button>' : '<button class="btn btn-outline btn-sm" data-action="detail-usulan" data-id="' + escapeHTML(item.id) + '">Detail ↗</button>';
    const metadata = usulanMetadata(item);
    return '<tr data-status="' + escapeHTML(item.status) + '" data-provinsi="' + escapeHTML(metadata.provinsi) + '" data-kabupaten="' + escapeHTML(metadata.kabupaten) + '" data-kecamatan="' + escapeHTML(metadata.kecamatan) + '" data-desa="' + escapeHTML(metadata.desa) + '" data-kelompok="' + escapeHTML(metadata.kelompok) + '"><td><span class="ticket-code">' + escapeHTML(item.ticket) + '</span><span class="cell-sub">' + escapeHTML(item.date) + '</span></td><td><span class="cell-title">' + escapeHTML(item.wilayah) + '</span><span class="cell-sub">' + escapeHTML(item.di) + '</span></td><td><span class="cell-title">' + escapeHTML(item.kelompok) + '</span><span class="cell-sub">' + escapeHTML(item.jenis) + '</span></td><td>' + badge(item.status) + '</td><td><span class="cell-sub">' + escapeHTML(item.surat ? "2 kategori dokumen" : "Dokumen belum lengkap") + '</span></td><td>' + action + '</td></tr>';
  }
  function renderUsulan() {
    const rows = state.usulan.map(x => rowUsulan(x)).join("");
    const filters = '<div class="table-toolbar usulan-filter-toolbar"><div class="toolbar-left usulan-filter-row"><div class="search-box"><input type="search" class="table-search" placeholder="Cari nomor, kelompok, wilayah…" aria-label="Cari usulan"></div>' +
      '<select class="filter-select filter-status" aria-label="Filter status"><option value="">Semua status</option><option>Draf</option><option>Diajukan</option><option>Dalam pemeriksaan</option><option>Perlu perbaikan</option><option>Sesuai</option></select>' +
      '<select class="filter-select filter-provinsi" aria-label="Filter provinsi"><option value="">Semua provinsi</option></select>' +
      '<select class="filter-select filter-kabupaten" aria-label="Filter kabupaten/wilayah"><option value="">Semua kabupaten/wilayah</option></select>' +
      '<select class="filter-select filter-kecamatan" aria-label="Filter kecamatan"><option value="">Semua kecamatan</option></select>' +
      '<select class="filter-select filter-desa" aria-label="Filter desa/kelurahan"><option value="">Semua desa/kelurahan</option></select>' +
      '<select class="filter-select filter-kelompok" aria-label="Filter kelompok tani"><option value="">Semua kelompok tani</option></select></div><div class="toolbar-right"><span class="empty-chip" id="result-count">' + state.usulan.length + ' data</span><button class="btn btn-outline btn-sm" data-action="reset-usulan-filters">Reset filter</button></div></div>';
    const intro = '<div class="panel table-panel"><div class="panel-head"><div><h2 class="panel-title">Semua usulan</h2><div class="panel-subtitle">Satu ID usulan tetap digunakan saat revisi dan kirim ulang.</div></div><span class="empty-chip">' + state.usulan.length + ' usulan</span></div>' +
      filters +
      dataTable(["Nomor usulan", "Wilayah / DI", "Kelompok", "Status", "Lampiran", "Tindakan"], rows) +
      '<div class="table-bottom"><span>Data lokal prototipe; bukan data server. Filter wilayah mengikuti referensi CPCL tersedia.</span><span class="pagination"><button class="page-no active">1</button></span></div></div>';
    const helper = state.role === "pelapor"
      ? '<div class="notice-strip"><span class="notice-icon">i</span><span>Untuk mengirim usulan, <strong>Surat permohonan bantuan kegiatan</strong> dan <strong>Hasil verifikasi usulan pelaksanaan kegiatan</strong> harus tersedia. Draf boleh disimpan belum lengkap.</span></div>'
      : '<div class="notice-strip"><span class="notice-icon">i</span><span>Hasil verifikasi lapangan kabupaten adalah lampiran usulan, bukan keputusan pemeriksaan pusat. Keputusan pusat dicatat terpisah.</span></div>';
    return helper + intro;
  }
  function renderVerifikasi() {
    const reviewable = state.usulan.filter(x => ["Diajukan", "Dalam pemeriksaan", "Perlu perbaikan"].includes(x.status));
    return '<div class="notice-strip"><span class="notice-icon">i</span><span>Hanya role <strong>Verifikator Pusat</strong> yang dapat mencatat hasil pemeriksaan. Label “Sesuai” tidak berarti bantuan otomatis dialokasikan atau pekerjaan fisik telah selesai.</span></div>' +
      '<div class="metric-grid">' +
      metric("Perlu ditinjau", countStatus(state.usulan, ["Diajukan"]), "Baru masuk antrean", "◷", "amber") +
      metric("Dalam pemeriksaan", countStatus(state.usulan, ["Dalam pemeriksaan"]), "Sedang ditinjau", "⌕", "blue") +
      metric("Perlu perbaikan", countStatus(state.usulan, ["Perlu perbaikan"]), "Menunggu pelapor", "↺", "rose") +
      metric("Sesuai", countStatus(state.usulan, ["Sesuai"]), "Hasil pemeriksaan pusat", "✓") + '</div>' +
      '<div class="panel table-panel"><div class="panel-head"><div><h2 class="panel-title">Antrean pemeriksaan usulan</h2><div class="panel-subtitle">Tinjau data usulan serta dua dokumen yang diserahkan kabupaten.</div></div><span class="empty-chip">' + reviewable.length + ' item</span></div>' +
      filterBar({ count: reviewable.length, statuses: ["Diajukan", "Dalam pemeriksaan", "Perlu perbaikan", "Sesuai"] }) +
      dataTable(["Nomor usulan", "Wilayah / DI", "Kelompok", "Status", "Lampiran", "Tindakan"], reviewable.map(x => rowUsulan(x, "review")).join("")) +
      '<div class="table-bottom"><span>Catatan perbaikan dan putaran pemeriksaan disimpan dalam contoh sesi ini.</span><span>Antrean terbaru di atas</span></div></div>';
  }
  function rowLaporan(item, review = false) {
    const action = review
      ? (["Diajukan", "Dalam pemeriksaan", "Perlu perbaikan"].includes(item.status) ? '<button class="btn btn-secondary btn-sm" data-action="review-laporan" data-id="' + escapeHTML(item.id) + '">Periksa</button>' : '<button class="row-action" data-action="detail-laporan" data-id="' + escapeHTML(item.id) + '" aria-label="Lihat detail">↗</button>')
      : '<button class="row-action" data-action="detail-laporan" data-id="' + escapeHTML(item.id) + '" aria-label="Lihat detail">↗</button>';
    return '<tr><td><span class="ticket-code">' + escapeHTML(item.ticket) + '</span><span class="cell-sub">' + escapeHTML(item.date) + '</span></td><td><span class="cell-title">' + escapeHTML(item.wilayah) + '</span><span class="cell-sub">' + escapeHTML(item.kelompok) + '</span></td><td><span class="cell-title">' + escapeHTML(item.usulan) + '</span><span class="cell-sub">' + escapeHTML(item.jenis) + '</span></td><td>' + badge(item.status) + '</td><td><span class="cell-sub">' + (item.bast ? 'BAST · LPJ · ' + escapeHTML(item.kuitansi) + ' kuitansi · foto' : 'Belum lengkap') + '</span></td><td>' + action + '</td></tr>';
  }
  function renderLaporan() {
    const verifier = state.role === "verifikator";
    return '<div class="notice-strip"><span class="notice-icon">i</span><span>Dokumen tersedia, dokumen telah diperiksa, dan status <strong>Disahkan</strong> adalah kondisi yang berbeda. Pengesahan hanya dapat dilakukan oleh verifikator berwenang.</span></div>' +
      '<div class="panel table-panel"><div class="panel-head"><div><h2 class="panel-title">Daftar laporan</h2><div class="panel-subtitle">Setiap laporan mempunyai ID, status, lampiran, dan histori pemeriksaan tersendiri.</div></div><span class="empty-chip">' + state.laporan.length + ' laporan</span></div>' +
      filterBar({ count: state.laporan.length, statuses: ["Draf", "Diajukan", "Dalam pemeriksaan", "Perlu perbaikan", "Disahkan"] }) +
      dataTable(["Nomor laporan", "Wilayah / kelompok", "Terkait usulan", "Status", "Dokumen", "Tindakan"], state.laporan.map(x => rowLaporan(x, verifier)).join("")) +
      '<div class="table-bottom"><span>LPJ dapat memiliki beberapa kuitansi pendukung.</span><span>Riwayat revisi tidak dihitung sebagai laporan baru.</span></div></div>' +
      (state.role === "pelapor" ? '<div class="quick-grid"><button class="quick-card" data-action="new-laporan"><span class="quick-icon">＋</span><span class="quick-copy"><strong>Buat laporan</strong><span>Pilih usulan yang memenuhi syarat dan lampirkan dokumen laporan.</span></span></button><button class="quick-card" data-page="usulan"><span class="quick-icon">▤</span><span class="quick-copy"><strong>Lihat usulan terkait</strong><span>Periksa hasil pemeriksaan usulan lebih dahulu.</span></span></button><button class="quick-card" data-action="guide-report"><span class="quick-icon">i</span><span class="quick-copy"><strong>Catatan dokumen</strong><span>Kategori dan kewajiban final mengikuti keputusan D-08.</span></span></button></div>' : '');
  }
  function renderMonitoring() {
    const submitted = countStatus(state.usulan, ["Diajukan", "Dalam pemeriksaan"]);
    const revision = countStatus(state.usulan, ["Perlu perbaikan"]);
    const reviewed = countStatus(state.usulan, ["Sesuai"]);
    const reportWait = countStatus(state.laporan, ["Diajukan", "Dalam pemeriksaan"]);
    const rows = [
      ["Usulan", state.usulan.length, "Jumlah ID usulan berbeda"],
      ["Usulan dalam proses", submitted, "Diajukan atau dalam pemeriksaan"],
      ["Usulan perlu perbaikan", revision, "Status terkini saja; bukan seluruh riwayat"],
      ["Usulan sesuai", reviewed, "Hasil pemeriksaan pusat"],
      ["Laporan", state.laporan.length, "Jumlah ID laporan berbeda"],
      ["Laporan dalam proses", reportWait, "Dokumen menunggu pemeriksaan"],
      ["Laporan disahkan", countStatus(state.laporan, ["Disahkan"]), "Pengesahan oleh verifikator"]
    ].map(row => '<tr><td><span class="cell-title">' + row[0] + '</span><span class="cell-sub">' + row[2] + '</span></td><td><strong>' + row[1] + '</strong></td></tr>').join("");
    const values = [
      ["Diajukan", countStatus(state.usulan, ["Diajukan"])],
      ["Dalam pemeriksaan", countStatus(state.usulan, ["Dalam pemeriksaan"])],
      ["Perlu perbaikan", revision],
      ["Sesuai", reviewed]
    ];
    const max = Math.max(1, ...values.map(x => x[1]));
    const bars = values.map((x, i) => '<div class="hbar-row"><span>' + x[0] + '</span><div class="hbar-track"><span style="width:' + Math.max(3, x[1] / max * 100) + '%"></span></div><span class="hbar-value">' + x[1] + '</span></div>').join("");
    return '<div class="notice-strip"><span class="notice-icon">i</span><span>Rekap ini menghitung <strong>status terkini per ID</strong>. Kelengkapan dokumen ditampilkan terpisah dari hasil pemeriksaan. Angka pada prototipe hanya ilustrasi.</span></div>' +
      '<div class="metric-grid">' +
      metric("Total usulan", state.usulan.length, "ID unik", "▤") +
      metric("Usulan dalam proses", submitted, "Diajukan + pemeriksaan", "◷", "amber") +
      metric("Total laporan", state.laporan.length, "ID unik", "▧", "blue") +
      metric("Laporan disahkan", countStatus(state.laporan, ["Disahkan"]), "Status terakhir", "✓") +
      '</div><div class="two-column"><section class="chart-card"><h3>Komposisi status usulan</h3><p>Jumlah ID usulan berdasarkan status terkini.</p><div class="horizontal-bars">' + bars + '</div></section>' +
      '<section class="panel"><div class="panel-head"><div><h2 class="panel-title">Kelengkapan & tindak lanjut</h2><div class="panel-subtitle">Ringkasan kondisi dokumen contoh</div></div></div><div class="status-list">' +
      '<div class="status-row"><span class="status-mark"></span><div class="status-text"><strong>Dokumen usulan</strong><span>2 kategori wajib saat dikirim</span></div><span class="status-count">2</span></div>' +
      '<div class="status-row"><span class="status-mark amber"></span><div class="status-text"><strong>Butuh perbaikan</strong><span>Usulan/laporan dengan catatan terbaru</span></div><span class="status-count">' + (revision + countStatus(state.laporan, ["Perlu perbaikan"])) + '</span></div>' +
      '<div class="status-row"><span class="status-mark blue"></span><div class="status-text"><strong>Laporan dalam proses</strong><span>Menunggu hasil pemeriksaan pusat</span></div><span class="status-count">' + reportWait + '</span></div></div><div class="status-callout"><strong>Catatan definisi</strong><p>Tidak ada perhitungan progres fisik di rilis inti SRS v0.6. Persentase kelengkapan menunggu keputusan D-07/D-08.</p></div></section></div>' +
      '<div class="panel table-panel"><div class="panel-head"><div><h2 class="panel-title">Rekap indikator</h2><div class="panel-subtitle">Gunakan definisi hitung yang konsisten antara tabel dan ekspor.</div></div><button class="btn btn-outline btn-sm" data-action="export">Ekspor rekap ↗</button></div>' +
      dataTable(["Indikator", "Jumlah / definisi"], rows) + '<div class="table-bottom"><span>Filter periode dalam tampilan ini bersifat contoh.</span><span>Belum berupa SLA atau laporan resmi</span></div></div>';
  }
  function cpclDetailPairs(cp, usulan = null) {
    const fields = [
      ["Nama DI", cp?.namaDI || usulan?.di || "Belum tersedia"],
      ["Kewenangan", cp?.kewenangan || "Belum dikonfirmasi"],
      ["Keterangan", cp?.keterangan || "Belum dikonfirmasi"],
      ["Provinsi", cp?.provinsi || "Belum dikonfirmasi"],
      ["Kab_Kota", cp?.kabKota || usulan?.wilayah || "Belum dikonfirmasi"],
      ["Kecamatan", cp?.kecamatan || "Belum dikonfirmasi"],
      ["Desa/Kelurahan", cp?.desaKelurahan || "Belum dikonfirmasi"],
      ["Kel_Tani", cp?.kelTani || usulan?.kelompok || "Belum dikonfirmasi"],
      ["Nama Ketua", cp?.namaKetua || "Belum dikonfirmasi"],
      ["Realisasi", cp?.realisasi || "Belum dikonfirmasi"],
      ["Luas_ha", cp?.luasHa || "Belum dikonfirmasi"],
      ["X", cp?.x || "Belum dikonfirmasi"],
      ["Y", cp?.y || "Belum dikonfirmasi"]
    ];
    return '<div class="detail-pairs cpcl-detail-pairs">' + fields.map(pair => '<div class="detail-pair"><small>' + escapeHTML(pair[0]) + '</small><strong>' + escapeHTML(pair[1]) + '</strong></div>').join("") + '</div><p class="form-help">Nilai yang bertuliskan “Belum dikonfirmasi” perlu diisi dari file sumber CPCL yang disahkan. Data baris contoh ini tidak boleh dianggap sebagai data Kementan resmi.</p>';
  }
  function renderCPCL() {
    const rows = CPCL.map(x => '<tr><td><span class="ticket-code">' + escapeHTML(x.kode) + '</span></td><td><span class="cell-title">' + escapeHTML(x.namaDI) + '</span></td><td>' + escapeHTML(x.kewenangan) + '</td><td><span class="cell-title">' + escapeHTML(x.provinsi) + '</span><span class="cell-sub">' + escapeHTML(x.kabKota) + '</span></td><td><span class="cell-title">' + escapeHTML(x.kelTani) + '</span><span class="cell-sub">' + escapeHTML(x.jenis) + '</span></td><td>' + escapeHTML(x.luasHa) + '</td><td><button class="btn btn-secondary btn-sm" data-action="cpcl-detail" data-id="' + escapeHTML(x.kode) + '">Detail ↗</button></td></tr>').join("");
    return '<div class="notice-strip"><span class="notice-icon">i</span><span>Daftar ini menampilkan kolom ringkasan agar tabel tetap terbaca. Klik <strong>Detail</strong> untuk melihat seluruh 13 atribut CPCL. Data baris contoh bukan data produksi; atribut yang belum ada di materi ditandai belum dikonfirmasi.</span></div>' +
      '<div class="metric-grid">' +
      metric("Realisasi · ringkasan sumber", "1.840", "Angka dari tabel yang kamu kirim; perlu dicocokkan dengan workbook", "▤", "blue") +
      metric("Luas_ha · ringkasan sumber", "30.690,2", "Angka dari tabel yang kamu kirim; definisi agregasi perlu dikonfirmasi", "⌖") +
      metric("Baris demo", CPCL.length, "Contoh lokal untuk review UI, bukan jumlah sumber", "⌕", "amber") +
      metric("Atribut detail", "13", "Nama DI sampai X dan Y", "≋") +
      '</div><div class="panel table-panel"><div class="panel-head"><div><h2 class="panel-title">Referensi CPCL</h2><div class="panel-subtitle">Daftar ringkasan CPCL dengan detail atribut lengkap.</div></div><span class="empty-chip">' + CPCL.length + ' baris demo</span></div>' +
      '<div class="table-toolbar"><div class="toolbar-left"><div class="search-box"><input type="search" class="table-search" placeholder="Cari kode, DI, kelompok, wilayah…" aria-label="Cari CPCL"></div><select class="filter-select cpcl-type"><option value="">Semua jenis kelompok</option><option>P3A</option><option>GP3A</option><option>Poktan</option></select></div><div class="toolbar-right"><span class="empty-chip">Read-only · data simulasi</span></div></div>' +
      dataTable(["Kode CPCL", "Nama DI", "Kewenangan", "Provinsi / Kab_Kota", "Kel_Tani", "Luas_ha", "Detail"], rows) +
      '<div class="table-bottom"><span>Kolom lengkap dibuka melalui menu Detail di tiap baris.</span><span>Impor sumber CPCL belum diaktifkan</span></div></div>';
  }
  function renderAdmin() {
    const rows = state.accounts.map((a, i) => '<tr><td><span class="cell-title">' + escapeHTML(a.name) + '</span><span class="cell-sub">' + escapeHTML(a.email) + '</span></td><td>' + escapeHTML(ROLE[a.role] ? ROLE[a.role].name : a.role) + '</td><td>' + escapeHTML(a.wilayah || "Belum diatur") + '</td><td>' + badge(a.active ? "Aktif" : "Nonaktif") + '</td><td><button class="btn btn-outline btn-sm" data-action="toggle-account" data-id="' + i + '">' + (a.active ? "Nonaktifkan" : "Aktifkan") + '</button></td></tr>').join("");
    return '<div class="notice-strip"><span class="notice-icon">i</span><span>Menu administrasi ini hanya simulasi. Aplikasi produksi wajib membuat/mengubah akun melalui mekanisme yang aman dan memeriksa izin di server; Super Admin tidak otomatis berhak mengesahkan usulan/laporan.</span></div>' +
      '<div class="metric-grid">' +
      metric("Seluruh akun", state.accounts.length, "Termasuk akun simulasi", "♙") +
      metric("Akun aktif", state.accounts.filter(x => x.active).length, "Boleh masuk (simulasi)", "✓") +
      metric("Role aplikasi", 4, "Pelapor, Verifikator, Pimpinan, Admin", "⌘", "blue") +
      metric("Registrasi publik", "Tidak", "Akun diterbitkan admin", "⌑", "amber") +
      '</div><div class="panel table-panel"><div class="panel-head"><div><h2 class="panel-title">Pengelolaan akun</h2><div class="panel-subtitle">Role aplikasi dipisahkan dari cakupan wilayah dan izin tindakan.</div></div><button class="btn btn-primary btn-sm" data-action="new-account">＋ Tambah akun</button></div>' +
      dataTable(["Nama / email contoh", "Role", "Cakupan contoh", "Status", "Tindakan"], rows) +
      '<div class="table-bottom"><span>Akun dan email pada halaman ini sepenuhnya fiktif.</span><span>Perubahan hanya tersimpan di browser demo</span></div></div>' +
      '<div class="two-column"><section class="panel"><div class="panel-head"><div><h2 class="panel-title">Referensi yang dikelola</h2><div class="panel-subtitle">Daftar master yang perlu disepakati pemilik data</div></div></div><div class="status-list">' +
      '<div class="status-row"><span class="status-mark"></span><div class="status-text"><strong>Wilayah bertingkat</strong><span>Provinsi, kabupaten, dan cakupan akun</span></div><span class="empty-chip">TBC</span></div>' +
      '<div class="status-row"><span class="status-mark blue"></span><div class="status-text"><strong>Daerah Irigasi (D.I.)</strong><span>Kode dan kewenangan daerah irigasi</span></div><span class="empty-chip">TBC</span></div>' +
      '<div class="status-row"><span class="status-mark amber"></span><div class="status-text"><strong>Jenis dokumen</strong><span>Kategori, tahap, format, ukuran, dan status wajib</span></div><span class="empty-chip">D-08</span></div></div></section>' +
      '<section class="panel"><div class="panel-head"><div><h2 class="panel-title">Batas kewenangan</h2><div class="panel-subtitle">Ringkasan rule yang dicatat di SRS v0.6</div></div></div><div class="status-list">' +
      '<div class="status-row"><span class="status-mark"></span><div class="status-text"><strong>Verifikator pusat</strong><span>Memeriksa usulan dan mengesahkan laporan</span></div></div>' +
      '<div class="status-row"><span class="status-mark blue"></span><div class="status-text"><strong>Pimpinan</strong><span>Monitoring tanpa aksi pengesahan</span></div></div>' +
      '<div class="status-row"><span class="status-mark amber"></span><div class="status-text"><strong>Super Admin</strong><span>Administrasi akun/referensi; bukan pemeriksa default</span></div></div></div></section></div>';
  }

  function openModal(title, intro, body, buttons = '<button class="btn btn-outline" data-action="close-modal">Tutup</button>') {
    $("#modal-root").innerHTML = '<div class="modal-backdrop" data-action="backdrop-close"><section class="modal" role="dialog" aria-modal="true" aria-label="' + escapeHTML(title) + '"><div class="modal-head"><div><h2>' + escapeHTML(title) + '</h2><p>' + escapeHTML(intro || "") + '</p></div><button class="modal-close" data-action="close-modal" aria-label="Tutup">×</button></div><div class="modal-body">' + body + '</div><div class="modal-footer">' + buttons + '</div></section></div>';
    document.body.style.overflow = "hidden";
    const first = $("input,select,textarea,button", $("#modal-root"));
    if (first) first.focus();
  }
  function closeModal() {
    $("#modal-root").innerHTML = "";
    document.body.style.overflow = "";
  }
  function fileRow(title, desc, value = "") {
    return '<div class="form-field full"><label>' + title + '</label><div class="upload-box"><span class="upload-symbol">⇧</span><div><strong>' + (value ? escapeHTML(value) : 'Pilih dokumen dari perangkat') + '</strong><span>' + desc + '</span></div><label class="btn btn-outline btn-sm">Pilih file<input type="file" class="doc-input" accept=".pdf,.jpg,.jpeg,.png,.xlsx,.xls,.doc,.docx"></label></div></div>';
  }
  function cpclSelectOptions(records, field, placeholder) {
    const values = [...new Set(records.map(record => record[field]).filter(Boolean))];
    return '<option value="">' + escapeHTML(placeholder) + '</option>' + values.map(value => '<option value="' + escapeHTML(value) + '">' + escapeHTML(value) + '</option>').join("");
  }
  const USULAN_COMBO_FIELDS = {
    "usulan-wilayah": { key: "kabKota", label: "Kabupaten / wilayah" },
    "usulan-di": { key: "namaDI", label: "Daerah irigasi" },
    "usulan-kelompok": { key: "kelTani", label: "Nama kelompok" },
    "usulan-jenis": { key: "jenis", label: "Jenis kelompok" }
  };
  function currentUsulanComboValues() {
    return {
      "usulan-wilayah": $("#usulan-wilayah")?.value.trim() || "",
      "usulan-di": $("#usulan-di")?.value.trim() || "",
      "usulan-kelompok": $("#usulan-kelompok")?.value.trim() || "",
      "usulan-jenis": $("#usulan-jenis")?.value.trim() || ""
    };
  }
  function recordsForUsulanCombo(fieldId) {
    const values = currentUsulanComboValues();
    const fieldToKey = {
      "usulan-wilayah": "kabKota",
      "usulan-di": "namaDI",
      "usulan-kelompok": "kelTani",
      "usulan-jenis": "jenis"
    };
    return CPCL.filter(record => Object.entries(fieldToKey).every(([id, key]) => {
      if (id === fieldId || !values[id]) return true;
      return String(record[key] || "").toLocaleLowerCase("id") === values[id].toLocaleLowerCase("id");
    }));
  }
  function renderUsulanComboOptions(fieldId, forceOpen = false) {
    const input = $("#" + fieldId);
    const options = $("#" + fieldId + "-options");
    if (!input || !options) return;
    const query = input.value.trim().toLocaleLowerCase("id");
    const records = recordsForUsulanCombo(fieldId);
    const key = USULAN_COMBO_FIELDS[fieldId].key;
    const values = [...new Set(records.map(record => String(record[key] || "").trim()).filter(Boolean))]
      .filter(value => !query || value.toLocaleLowerCase("id").includes(query))
      .sort(new Intl.Collator("id", { sensitivity: "base", numeric: true }).compare);
    if (!values.length) {
      options.innerHTML = '<div class="combo-empty">Tidak ada saran yang cocok. Teks yang diketik tetap boleh digunakan.</div>';
    } else {
      options.innerHTML = values.map(value => '<button type="button" class="combo-option" role="option" data-action="select-usulan-option" data-id="' + escapeHTML(fieldId) + '" data-value="' + escapeHTML(value) + '"><span>' + escapeHTML(value) + '</span><span class="combo-option-hint">Pilih</span></button>').join("");
    }
    const exactMatch = values.some(value => value.toLocaleLowerCase("id") === query);
    options.hidden = !forceOpen && (!query || (exactMatch && values.length === 1));
    input.setAttribute("aria-expanded", String(!options.hidden));
  }
  function closeUsulanComboOptions(exceptId = "") {
    Object.keys(USULAN_COMBO_FIELDS).forEach(id => {
      if (id === exceptId) return;
      const options = $("#" + id + "-options");
      const input = $("#" + id);
      if (options) options.hidden = true;
      if (input) input.setAttribute("aria-expanded", "false");
    });
  }
  function syncUsulanComboFromSelection(fieldId) {
    const values = currentUsulanComboValues();
    const fieldKey = USULAN_COMBO_FIELDS[fieldId]?.key;
    if (!fieldKey) return;
    const selectedValue = values[fieldId];
    if (!selectedValue) return;
    let record = CPCL.find(item => String(item[fieldKey] || "").toLocaleLowerCase("id") === selectedValue.toLocaleLowerCase("id"));
    if (fieldId === "usulan-di" || fieldId === "usulan-kelompok" || fieldId === "usulan-jenis") {
      const narrowed = CPCL.filter(item =>
        String(item[fieldKey] || "").toLocaleLowerCase("id") === selectedValue.toLocaleLowerCase("id") &&
        (!values["usulan-wilayah"] || item.kabKota.toLocaleLowerCase("id") === values["usulan-wilayah"].toLocaleLowerCase("id")) &&
        (!values["usulan-di"] || fieldId === "usulan-di" || item.namaDI.toLocaleLowerCase("id") === values["usulan-di"].toLocaleLowerCase("id")) &&
        (!values["usulan-kelompok"] || fieldId === "usulan-kelompok" || item.kelTani.toLocaleLowerCase("id") === values["usulan-kelompok"].toLocaleLowerCase("id")) &&
        (!values["usulan-jenis"] || fieldId === "usulan-jenis" || item.jenis.toLocaleLowerCase("id") === values["usulan-jenis"].toLocaleLowerCase("id"))
      );
      if (narrowed.length) record = narrowed[0];
    }
    if (!record) return;
    const regionEl = $("#usulan-wilayah");
    const diEl = $("#usulan-di");
    const groupEl = $("#usulan-kelompok");
    const typeEl = $("#usulan-jenis");
    if (fieldId === "usulan-kelompok") {
      regionEl.value = record.kabKota;
      diEl.value = record.namaDI;
      typeEl.value = record.jenis;
    } else if (fieldId === "usulan-di") {
      regionEl.value = record.kabKota;
    } else if (fieldId === "usulan-jenis") {
      const matchingGroup = CPCL.find(item => item.jenis === record.jenis &&
        (!values["usulan-wilayah"] || item.kabKota === values["usulan-wilayah"]) &&
        (!values["usulan-di"] || item.namaDI === values["usulan-di"]));
      if (matchingGroup && !values["usulan-kelompok"]) groupEl.value = matchingGroup.kelTani;
    }
  }
  function usulanComboField(label, id, placeholder, help = "") {
    return '<div class="form-field"><label for="' + id + '">' + label + ' <span class="required">*</span></label><div class="combo-wrap"><input type="text" id="' + id + '" name="' + id.replace("usulan-", "") + '" class="usulan-combo" required autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="' + id + '-options" placeholder="' + escapeHTML(placeholder) + '"><button type="button" class="combo-toggle" data-action="toggle-usulan-combo" data-id="' + id + '" aria-label="Tampilkan pilihan ' + escapeHTML(label) + '">⌄</button><div class="combo-options" id="' + id + '-options" role="listbox" hidden></div></div>' + (help ? '<div class="form-help">' + help + '</div>' : '') + '</div>';
  }
  function newUsulanModal() {
    if (state.role !== "pelapor") { toast("Pembuatan usulan hanya tersedia untuk role Pelapor pada prototipe."); return; }
    const body = '<form id="usulan-form"><div class="form-section-heading"><span class="section-number">1</span><div><h3>Informasi usulan</h3><p>Ketik nama secara langsung atau pilih saran dari referensi CPCL.</p></div></div><div class="form-grid">' +
      usulanComboField("Kabupaten / wilayah kegiatan", "usulan-wilayah", "Ketik atau pilih kabupaten/wilayah", "Bisa diisi manual bila belum ada di referensi.") +
      usulanComboField("Daerah irigasi (D.I.)", "usulan-di", "Ketik atau pilih daerah irigasi") +
      usulanComboField("Nama kelompok", "usulan-kelompok", "Ketik atau pilih nama kelompok") +
      usulanComboField("Jenis kelompok", "usulan-jenis", "Ketik atau pilih jenis kelompok") +
      '<div class="form-field"><label for="usulan-periode">Periode <span class="required">*</span></label><select id="usulan-periode" name="periode" required><option value="2026">2026</option><option value="2027">2027</option></select></div>' +
      '<div class="form-field full"><label for="usulan-note">Keterangan singkat</label><textarea id="usulan-note" name="note" placeholder="Tambahkan konteks seperlunya"></textarea></div>' +
      '</div><div class="divider"></div><div class="form-section-heading"><span class="section-number">2</span><div><h3>Dokumen wajib usulan</h3><p>Dua kategori ini wajib tersedia ketika usulan dikirim. Draf boleh belum lengkap.</p></div></div><div class="form-grid">' +
      fileRow('Surat permohonan bantuan kegiatan <span class="required">*</span>', 'Kategori wajib saat pengiriman. Format final mengikuti keputusan D-08.') +
      '<input type="hidden" name="unused">' + fileRow('Hasil verifikasi usulan pelaksanaan kegiatan <span class="required">*</span>', 'Lampiran hasil verifikasi lapangan kabupaten untuk diperiksa pusat.') +
      '</div><p class="form-help">Daftar pilihan ini memakai data CPCL contoh pada prototipe. Sebelum produksi, hubungkan ke referensi resmi yang disahkan Kementan. Prototipe hanya mencatat nama file di browser; file tidak diunggah atau disimpan di server.</p></form>';
    openModal("Buat usulan baru", "Lengkapi data pengajuan dan siapkan dua kategori dokumen wajib.", body,
      '<button class="btn btn-outline" data-action="close-modal">Batal</button><button class="btn btn-secondary" data-action="submit-usulan-draft">Simpan draf</button><button class="btn btn-primary" data-action="submit-usulan">Kirim usulan</button>');
  }
  function newLaporanModal() {
    if (state.role !== "pelapor") { toast("Pembuatan laporan hanya tersedia untuk role Pelapor pada prototipe."); return; }
    const available = state.usulan.filter(u => ["Sesuai"].includes(u.status));
    const options = available.map(u => '<option value="' + escapeHTML(u.id) + '">' + escapeHTML(u.ticket + " — " + u.wilayah + " / " + u.kelompok) + '</option>').join("");
    const body = '<form id="laporan-form"><div class="form-grid">' +
      '<div class="form-field full"><label for="laporan-usulan">Usulan terkait <span class="required">*</span></label><select id="laporan-usulan" name="usulanId" required><option value="">Pilih usulan yang telah memenuhi syarat</option>' + options + '</select><div class="form-help">Pelaporan hanya dibuka jika syarat pada keputusan D-02 terpenuhi. Pada demo, hanya status usulan Sesuai yang dapat dipilih.</div></div>' +
      '<div class="form-field"><label for="laporan-jenis">Jenis laporan <span class="required">*</span></label><select id="laporan-jenis" name="jenis"><option>Laporan akhir</option><option>Laporan berkala (contoh)</option></select></div>' +
      '<div class="form-field"><label for="laporan-ringkasan">Ringkasan</label><input id="laporan-ringkasan" name="note" placeholder="Ringkasan dokumen laporan"></div>' +
      '</div><div class="divider"></div><div class="form-section-heading"><span class="section-number">2</span><div><h3>Lampiran laporan</h3><p>Contoh kategori BAST, LPJ, bukti kuitansi, dan foto dokumentasi.</p></div></div><div class="form-grid">' +
      fileRow('BAST', 'Berita Acara Serah Terima; kewajiban final mengikuti D-08.') +
      fileRow('LPJ', 'Laporan Pertanggungjawaban kegiatan.') +
      fileRow('Bukti kuitansi', 'Dapat berjumlah lebih dari satu; pada demo dicatat sebagai satu nama file.') +
      fileRow('Foto dokumentasi', 'Bukti pendukung laporan, tanpa perhitungan progres fisik.') +
      '</div><p class="form-help">Prototipe hanya mencatat nama file di browser, tidak menyimpan isi dokumen.</p></form>';
    openModal("Buat laporan", "Hubungkan laporan ke usulan dan lampiran yang sesuai.", body,
      '<button class="btn btn-outline" data-action="close-modal">Batal</button><button class="btn btn-secondary" data-action="submit-laporan-draft">Simpan draf</button><button class="btn btn-primary" data-action="submit-laporan">Kirim laporan</button>');
  }
  function inputFileNames(form) {
    const names = $$('input[type="file"]', form).map(input => input.files && input.files[0] ? input.files[0].name : "");
    return names;
  }
  function nextTicket(prefix, items) {
    const current = items.reduce((max, item) => Math.max(max, Number((item.ticket.match(/(\d+)$/) || [0, 0])[1]) || 0), 0) + 1;
    return prefix + "-2026-" + String(current).padStart(3, "0");
  }
  function submitUsulan(form, mode) {
    const data = new FormData(form);
    const files = inputFileNames(form);
    const wilayah = String(data.get("wilayah") || "").trim();
    const di = String(data.get("di") || "").trim();
    const kelompok = String(data.get("kelompok") || "").trim();
    if (mode === "send") {
      if (!form.reportValidity()) return;
      if (!files[0] || !files[1]) {
        toast("Pilih kedua dokumen wajib sebelum mengirim usulan.");
        return;
      }
    }
    if (!wilayah || !di || !kelompok) {
      toast("Isi wilayah, daerah irigasi, dan nama kelompok terlebih dahulu.");
      return;
    }
    const item = {
      id: "u-demo-" + Date.now(), ticket: nextTicket("UT", state.usulan),
      wilayah, di, kelompok, jenis: String(data.get("jenis") || "Belum dipilih"),
      periode: String(data.get("periode") || "2026"), date: "Hari ini (demo)",
      status: mode === "send" ? "Diajukan" : "Draf",
      surat: files[0] || "", verifikasi: files[1] || "",
      note: String(data.get("note") || "").trim(), rounds: 0
    };
    state.usulan.unshift(item);
    persist(); closeModal(); renderPage();
    toast(mode === "send" ? "Usulan simulasi berhasil masuk ke daftar pengajuan." : "Draf simulasi tersimpan di browser.");
  }
  function submitLaporan(form, mode) {
    const data = new FormData(form);
    const usulanId = String(data.get("usulanId") || "");
    const usulan = state.usulan.find(x => x.id === usulanId);
    if (mode === "send") {
      if (!form.reportValidity()) return;
      const files = inputFileNames(form);
      if (!files.some(Boolean)) {
        toast("Pilih setidaknya satu lampiran laporan untuk demo.");
        return;
      }
    }
    if (!usulan) {
      toast("Pilih usulan yang memenuhi syarat pembukaan pelaporan.");
      return;
    }
    const files = inputFileNames(form);
    const item = {
      id: "l-demo-" + Date.now(), ticket: nextTicket("LR", state.laporan), usulanId: usulan.id,
      usulan: usulan.ticket, wilayah: usulan.wilayah, kelompok: usulan.kelompok,
      date: "Hari ini (demo)", status: mode === "send" ? "Diajukan" : "Draf",
      jenis: String(data.get("jenis") || "Laporan akhir"), bast: files[0] || "", lpj: files[1] || "",
      kuitansi: files[2] ? 1 : 0, foto: files[3] || "", note: String(data.get("note") || "").trim()
    };
    state.laporan.unshift(item); persist(); closeModal(); renderPage();
    toast(mode === "send" ? "Laporan simulasi berhasil masuk ke daftar." : "Draf laporan simulasi tersimpan di browser.");
  }
  function detailUsulanModal(item) {
    if (!item) return;
    const linkedCpcl = CPCL.find(x => x.kelTani.toLowerCase() === String(item.kelompok || "").toLowerCase() && x.kabKota === item.wilayah);
    const cpclSection = '<div class="divider"></div><h3 class="panel-title">Informasi referensi CPCL</h3><p class="form-help">Atribut wilayah dan kelompok ditampilkan terpisah dari status usulan dan dokumen pemeriksaan pusat.</p>' + cpclDetailPairs(linkedCpcl, item);
    const body = '<div class="detail-pairs"><div class="detail-pair"><small>Nomor usulan</small><strong>' + escapeHTML(item.ticket) + '</strong></div><div class="detail-pair"><small>Status terkini</small><strong>' + badge(item.status) + '</strong></div><div class="detail-pair"><small>Wilayah kegiatan</small><strong>' + escapeHTML(item.wilayah) + '</strong></div><div class="detail-pair"><small>Daerah irigasi</small><strong>' + escapeHTML(item.di) + '</strong></div><div class="detail-pair"><small>Kelompok</small><strong>' + escapeHTML(item.kelompok) + '</strong><p>' + escapeHTML(item.jenis) + '</p></div><div class="detail-pair"><small>Periode</small><strong>' + escapeHTML(item.periode) + '</strong><p>Dikirim: ' + escapeHTML(item.date) + '</p></div></div>' +
      '<div class="divider"></div><h3 class="panel-title">Dokumen usulan</h3>' +
      '<div class="document-row"><span class="document-icon">PDF</span><div class="document-copy"><strong>Surat permohonan bantuan kegiatan</strong><span>' + escapeHTML(item.surat || "Belum tersedia") + '</span></div><span class="doc-state">' + (item.surat ? "Tercatat" : "Belum ada") + '</span></div>' +
      '<div class="document-row"><span class="document-icon">PDF</span><div class="document-copy"><strong>Hasil verifikasi usulan pelaksanaan kegiatan</strong><span>' + escapeHTML(item.verifikasi || "Belum tersedia") + '</span></div><span class="doc-state">' + (item.verifikasi ? "Tercatat" : "Belum ada") + '</span></div>' +
      cpclSection +
      (item.reviewDocument ? '<div class="divider"></div><h3 class="panel-title">Dokumen catatan pemeriksaan pusat</h3><div class="document-row"><span class="document-icon">DOC</span><div class="document-copy"><strong>' + escapeHTML(item.reviewDocument) + '</strong><span>Dokumen catatan pemeriksaan verifikator</span></div><span class="doc-state">Tercatat</span></div><p class="form-help">Nama file tercatat pada demo; berkas belum disimpan di server.</p>' : '') +
      (item.reviewNote ? '<div class="status-callout"><strong>Catatan teks lama pada data demo</strong><p>' + escapeHTML(item.reviewNote) + '</p></div>' : '') +
      '<div class="divider"></div><div class="detail-pair"><small>Keterangan usulan</small><p>' + escapeHTML(item.note || "Tidak ada keterangan tambahan.") + '</p></div><p class="form-help">Data dan nama file pada detail ini berasal dari sesi prototipe, bukan server.</p>';
    const buttons = state.role === "verifikator" && ["Diajukan", "Dalam pemeriksaan", "Perlu perbaikan"].includes(item.status)
      ? '<button class="btn btn-outline" data-action="close-modal">Tutup</button><button class="btn btn-primary" data-action="review-usulan" data-id="' + escapeHTML(item.id) + '">Buka pemeriksaan</button>'
      : '<button class="btn btn-outline" data-action="close-modal">Tutup</button>' +
        (state.role === "pelapor" && item.status === "Perlu perbaikan" ? '<button class="btn btn-primary" data-action="revise-usulan" data-id="' + escapeHTML(item.id) + '">Revisi usulan</button>' : '');
    openModal("Detail usulan " + item.ticket, "Data pengajuan, lampiran, dan riwayat pemeriksaan.", body, buttons);
  }
  function reviewDocumentUploadField(id, currentName = "") {
    return '<div class="form-field full" style="margin-top:14px"><label for="' + id + '">Dokumen catatan pemeriksaan <span class="form-help">(wajib jika hasil Perlu perbaikan)</span></label><div class="upload-box"><span class="upload-symbol">⇧</span><div><strong>' + escapeHTML(currentName || "Pilih dokumen catatan pemeriksaan") + '</strong><span>PDF, DOC, atau DOCX. Unggah hasil pemeriksaan yang menjelaskan perbaikan yang diperlukan.</span></div><label class="btn btn-outline btn-sm">Pilih dokumen<input id="' + id + '" name="reviewDocument" type="file" class="doc-input" accept=".pdf,.doc,.docx"></label></div><div class="form-help">Wajib diisi bila hasilnya Perlu perbaikan. Pada prototipe ini hanya nama file yang dicatat; file belum disimpan di server.</div></div>';
  }
  function reviewUsulanModal(item) {
    if (!item || state.role !== "verifikator") { toast("Aksi pemeriksaan hanya tersedia untuk Verifikator Pusat."); return; }
    const linkedCpcl = CPCL.find(x => x.kelTani.toLowerCase() === String(item.kelompok || "").toLowerCase() && x.kabKota === item.wilayah);
    const body = '<div class="detail-pairs"><div class="detail-pair"><small>Nomor usulan</small><strong>' + escapeHTML(item.ticket) + '</strong></div><div class="detail-pair"><small>Status saat ini</small><strong>' + badge(item.status) + '</strong></div><div class="detail-pair"><small>Wilayah</small><strong>' + escapeHTML(item.wilayah) + '</strong></div><div class="detail-pair"><small>Kelompok</small><strong>' + escapeHTML(item.kelompok) + '</strong></div></div><div class="divider"></div><h3 class="panel-title">Lampiran yang diperiksa</h3>' +
      '<div class="document-row"><span class="document-icon">PDF</span><div class="document-copy"><strong>Surat permohonan bantuan kegiatan</strong><span>' + escapeHTML(item.surat || "Belum tersedia") + '</span></div><span class="doc-state">' + (item.surat ? "Ada" : "Kurang") + '</span></div>' +
      '<div class="document-row"><span class="document-icon">PDF</span><div class="document-copy"><strong>Hasil verifikasi lapangan kabupaten</strong><span>' + escapeHTML(item.verifikasi || "Belum tersedia") + '</span></div><span class="doc-state">' + (item.verifikasi ? "Ada" : "Kurang") + '</span></div>' +
      '<div class="divider"></div><h3 class="panel-title">Informasi referensi CPCL</h3>' + cpclDetailPairs(linkedCpcl, item) +
      '<div class="divider"></div><form id="review-usulan-form"><div class="form-field"><label for="review-result">Hasil pemeriksaan <span class="required">*</span></label><select id="review-result" name="result"><option value="Sesuai">Sesuai</option><option value="Perlu perbaikan">Perlu perbaikan</option></select></div>' +
      reviewDocumentUploadField("review-usulan-document", item.reviewDocument || "") +
      (item.reviewNote ? '<div class="status-callout"><strong>Catatan teks lama pada data demo</strong><p>' + escapeHTML(item.reviewNote) + '</p></div>' : '') +
      '</form><p class="form-help">Dokumen hasil verifikasi kabupaten adalah bukti masukan. Verifikator pusat menyimpan keputusan pemeriksaan secara terpisah.</p>';
    openModal("Pemeriksaan usulan", "Catat hasil dan alasan pemeriksaan sebagai verifikator pusat.", body, '<button class="btn btn-outline" data-action="close-modal">Batal</button><button class="btn btn-primary" data-action="save-review-usulan" data-id="' + escapeHTML(item.id) + '">Simpan hasil pemeriksaan</button>');
  }
  function detailLaporanModal(item) {
    if (!item) return;
    const body = '<div class="detail-pairs"><div class="detail-pair"><small>Nomor laporan</small><strong>' + escapeHTML(item.ticket) + '</strong></div><div class="detail-pair"><small>Status</small><strong>' + badge(item.status) + '</strong></div><div class="detail-pair"><small>Usulan terkait</small><strong>' + escapeHTML(item.usulan) + '</strong></div><div class="detail-pair"><small>Wilayah</small><strong>' + escapeHTML(item.wilayah) + '</strong></div><div class="detail-pair"><small>Kelompok</small><strong>' + escapeHTML(item.kelompok) + '</strong></div><div class="detail-pair"><small>Jenis laporan</small><strong>' + escapeHTML(item.jenis) + '</strong></div></div><div class="divider"></div><h3 class="panel-title">Lampiran laporan</h3>' +
      [['BAST', item.bast], ['LPJ', item.lpj], ['Bukti kuitansi', item.kuitansi ? item.kuitansi + ' kuitansi tercatat' : 'Belum tersedia'], ['Foto dokumentasi', item.foto]].map(x => '<div class="document-row"><span class="document-icon">DOC</span><div class="document-copy"><strong>' + x[0] + '</strong><span>' + escapeHTML(x[1] || "Belum tersedia") + '</span></div><span class="doc-state">' + (x[1] ? "Tercatat" : "Belum ada") + '</span></div>').join("") +
      (item.reviewDocument ? '<div class="divider"></div><h3 class="panel-title">Dokumen catatan pemeriksaan pusat</h3><div class="document-row"><span class="document-icon">DOC</span><div class="document-copy"><strong>' + escapeHTML(item.reviewDocument) + '</strong><span>Dokumen catatan pemeriksaan verifikator</span></div><span class="doc-state">Tercatat</span></div><p class="form-help">Nama file tercatat pada demo; berkas belum disimpan di server.</p>' : '') +
      (item.reviewNote ? '<div class="status-callout"><strong>Catatan teks lama pada data demo</strong><p>' + escapeHTML(item.reviewNote) + '</p></div>' : '') +
      '<p class="form-help">Pengesahan hanya mencatat hasil pemeriksaan laporan di website, bukan konfirmasi penyelesaian seluruh pekerjaan fisik.</p>';
    const buttons = state.role === "verifikator" && ["Diajukan", "Dalam pemeriksaan", "Perlu perbaikan"].includes(item.status)
      ? '<button class="btn btn-outline" data-action="close-modal">Tutup</button><button class="btn btn-primary" data-action="review-laporan" data-id="' + escapeHTML(item.id) + '">Buka pemeriksaan</button>'
      : '<button class="btn btn-outline" data-action="close-modal">Tutup</button>';
    openModal("Detail laporan " + item.ticket, "Status, dokumen, dan keterkaitan ke usulan.", body, buttons);
  }
  function reviewLaporanModal(item) {
    if (!item || state.role !== "verifikator") { toast("Pengesahan hanya tersedia untuk Verifikator Pusat."); return; }
    const body = '<div class="detail-pairs"><div class="detail-pair"><small>Nomor laporan</small><strong>' + escapeHTML(item.ticket) + '</strong></div><div class="detail-pair"><small>Usulan terkait</small><strong>' + escapeHTML(item.usulan) + '</strong></div><div class="detail-pair"><small>Wilayah</small><strong>' + escapeHTML(item.wilayah) + '</strong></div><div class="detail-pair"><small>Status saat ini</small><strong>' + badge(item.status) + '</strong></div></div><div class="divider"></div><h3 class="panel-title">Pemeriksaan dokumen laporan</h3>' +
      [['BAST', item.bast], ['LPJ', item.lpj], ['Bukti kuitansi', item.kuitansi ? item.kuitansi + ' kuitansi' : ""], ['Foto dokumentasi', item.foto]].map(x => '<div class="document-row"><span class="document-icon">DOC</span><div class="document-copy"><strong>' + x[0] + '</strong><span>' + escapeHTML(x[1] || "Belum tercatat pada contoh") + '</span></div><span class="doc-state">' + (x[1] ? "Ada" : "Periksa") + '</span></div>').join("") +
      '<div class="divider"></div><form id="review-laporan-form"><div class="form-field"><label for="laporan-review-result">Hasil pemeriksaan <span class="required">*</span></label><select id="laporan-review-result" name="result"><option value="Disahkan">Disahkan</option><option value="Perlu perbaikan">Perlu perbaikan</option></select></div>' +
      reviewDocumentUploadField("review-laporan-document", item.reviewDocument || "") +
      (item.reviewNote ? '<div class="status-callout"><strong>Catatan teks lama pada data demo</strong><p>' + escapeHTML(item.reviewNote) + '</p></div>' : '') +
      '</form><p class="form-help">Bila laporan perlu diperbaiki, lampirkan dokumen catatan pemeriksaan. Dokumen tersebut akan menjadi acuan pelapor untuk revisi.</p><p class="form-help">Pimpinan dan Super Admin tidak memiliki aksi pengesahan laporan. Ketersediaan berkas saja tidak mengesahkan otomatis.</p>';
    openModal("Pemeriksaan laporan", "Periksa BAST, LPJ, kuitansi, dan bukti dokumentasi sebelum menetapkan hasil.", body, '<button class="btn btn-outline" data-action="close-modal">Batal</button><button class="btn btn-primary" data-action="save-review-laporan" data-id="' + escapeHTML(item.id) + '">Simpan hasil pemeriksaan</button>');
  }
  function newAccountModal() {
    if (state.role !== "admin") { toast("Hanya Super Admin yang dapat mengelola akun pada prototipe."); return; }
    const body = '<form id="account-form"><div class="form-grid"><div class="form-field"><label for="account-name">Nama pengguna <span class="required">*</span></label><input id="account-name" name="name" required placeholder="Nama lengkap"></div><div class="form-field"><label for="account-email">Email <span class="required">*</span></label><input id="account-email" name="email" type="email" required placeholder="nama@contoh.id"></div><div class="form-field"><label for="account-role">Role <span class="required">*</span></label><select id="account-role" name="role"><option value="pelapor">Pelapor Daerah</option><option value="verifikator">Verifikator Pusat</option><option value="pimpinan">Pimpinan</option><option value="admin">Super Admin</option></select></div><div class="form-field"><label for="account-region">Cakupan / wilayah</label><input id="account-region" name="wilayah" placeholder="Contoh: Kabupaten Bogor"></div></div></form><p class="form-help">Penambahan ini hanya membuat baris simulasi di browser. Belum mengundang pengguna atau membuat akun autentikasi.</p>';
    openModal("Tambah pengguna", "Akun produksi harus diterbitkan melalui mekanisme undangan yang aman.", body, '<button class="btn btn-outline" data-action="close-modal">Batal</button><button class="btn btn-primary" data-action="save-account">Simpan akun simulasi</button>');
  }
  function saveReview(id, type) {
    const item = type === "usulan" ? state.usulan.find(x => x.id === id) : state.laporan.find(x => x.id === id);
    const form = $(type === "usulan" ? "#review-usulan-form" : "#review-laporan-form");
    if (!item || !form || state.role !== "verifikator") { toast("Tidak berwenang melakukan tindakan ini."); return; }
    const data = new FormData(form);
    const result = String(data.get("result") || "");
    const fileInput = type === "usulan" ? $("#review-usulan-document", form) : $("#review-laporan-document", form);
    const selectedFile = fileInput && fileInput.files && fileInput.files[0] ? fileInput.files[0] : null;
    const documentName = selectedFile ? selectedFile.name : "";
    if (result === "Perlu perbaikan" && !documentName) {
      toast("Lampirkan dokumen catatan pemeriksaan sebelum meminta perbaikan.");
      if (fileInput) fileInput.focus();
      return;
    }
    if (type === "usulan") {
      item.status = result;
      item.reviewDocument = documentName || "";
      item.reviewNote = "";
      item.rounds = (item.rounds || 0) + 1;
    } else {
      if (result === "Disahkan") {
        const hasSomeDoc = !!(item.bast || item.lpj || item.foto || item.kuitansi);
        if (!hasSomeDoc) { toast("Belum ada lampiran tercatat; periksa dokumen terlebih dahulu."); return; }
      }
      item.status = result;
      item.reviewDocument = documentName || "";
      item.reviewNote = "";
      item.reviewedAt = "Hari ini (demo)";
    }
    persist(); closeModal(); renderPage();
    toast(type === "usulan" ? "Hasil pemeriksaan usulan tersimpan pada demo." : result === "Disahkan" ? "Laporan simulasi telah disahkan." : "Catatan perbaikan laporan disimpan.");
  }
  function saveAccount() {
    if (state.role !== "admin") { toast("Tidak berwenang."); return; }
    const form = $("#account-form");
    if (!form || !form.reportValidity()) return;
    const d = new FormData(form);
    const email = String(d.get("email") || "").trim().toLowerCase();
    if (state.accounts.some(x => x.email.toLowerCase() === email)) { toast("Email sudah ada dalam data simulasi."); return; }
    state.accounts.push({ name: String(d.get("name") || "").trim(), email, role: String(d.get("role") || "pelapor"), wilayah: String(d.get("wilayah") || "").trim(), active: true });
    persist(); closeModal(); renderPage(); toast("Akun simulasi ditambahkan di browser.");
  }
  function reviseUsulan(id) {
    const item = state.usulan.find(x => x.id === id);
    if (!item || state.role !== "pelapor" || item.status !== "Perlu perbaikan") { toast("Usulan ini tidak tersedia untuk revisi pada role ini."); return; }
    const reviewFeedback = item.reviewDocument
      ? '<h3 class="panel-title">Dokumen catatan pemeriksaan pusat</h3><div class="document-row"><span class="document-icon">DOC</span><div class="document-copy"><strong>' + escapeHTML(item.reviewDocument) + '</strong><span>Dokumen yang diberikan verifikator sebagai acuan revisi.</span></div><span class="doc-state">Tercatat</span></div><p class="form-help">Pada prototipe hanya nama file yang tersimpan, jadi dokumen asli belum dapat dibuka atau diunduh.</p>'
      : '<div class="status-callout"><strong>Catatan pemeriksaan pusat (data demo lama)</strong><p>' + escapeHTML(item.reviewNote || "Dokumen catatan belum tercatat pada contoh ini.") + '</p></div>';
    const body = reviewFeedback + '<form id="revise-usulan-form"><div class="form-field" style="margin-top:14px"><label for="revise-note">Tanggapan / keterangan revisi</label><textarea id="revise-note" name="note" required placeholder="Jelaskan tindak lanjut atas dokumen pemeriksaan"></textarea></div><p class="form-help">Demo mempertahankan ID usulan yang sama dan menambah putaran revisi. Penggantian lampiran resmi perlu aturan D-05.</p></form>';
    openModal("Revisi usulan " + item.ticket, "Perbaiki data atau dokumen sesuai catatan verifikator.", body, '<button class="btn btn-outline" data-action="close-modal">Batal</button><button class="btn btn-primary" data-action="save-revision" data-id="' + escapeHTML(id) + '">Kirim ulang</button>');
  }
  function saveRevision(id) {
    const item = state.usulan.find(x => x.id === id);
    const form = $("#revise-usulan-form");
    if (!item || !form || !form.reportValidity()) return;
    const note = new FormData(form).get("note");
    item.status = "Diajukan";
    item.note = "Revisi pelapor: " + String(note || "").trim();
    item.rounds = (item.rounds || 0) + 1;
    item.date = "Hari ini (demo)";
    persist(); closeModal(); renderPage(); toast("Revisi simulasi dikirim dengan ID usulan tetap.");
  }
  function action(actionName, target, event) {
    switch (actionName) {
      case "toggle-usulan-combo": {
        const options = $("#" + target + "-options");
        if (!options) break;
        if (!options.hidden) {
          closeUsulanComboOptions();
        } else {
          closeUsulanComboOptions(target);
          renderUsulanComboOptions(target, true);
          $("#" + target)?.focus();
        }
        break;
      }
      case "select-usulan-option": {
        const input = $("#" + target);
        if (input) {
          input.value = event?.currentTarget?.dataset?.value || event?.target?.closest("[data-value]")?.dataset?.value || "";
          syncUsulanComboFromSelection(target);
          closeUsulanComboOptions();
          Object.keys(USULAN_COMBO_FIELDS).forEach(id => renderUsulanComboOptions(id, false));
        }
        break;
      }
      case "reset-usulan-filters":
        $$(".filter-provinsi,.filter-kabupaten,.filter-kecamatan,.filter-desa,.filter-kelompok,.filter-status", $("#page-content")).forEach(select => { select.value = ""; });
        const usulanSearch = $(".table-search", $("#page-content"));
        if (usulanSearch) usulanSearch.value = "";
        refreshUsulanFilterOptions();
        toast("Semua filter usulan direset.");
        break;
      case "new-usulan": newUsulanModal(); break;
      case "new-laporan": newLaporanModal(); break;
      case "new-account": newAccountModal(); break;
      case "go-verifikasi": go("verifikasi"); break;
      case "go-laporan-review": go("laporan"); break;
      case "go-monitoring": go("monitoring"); break;
      case "export": toast("Ekspor dinyatakan sebagai aksi demo; file resmi belum dihasilkan."); break;
      case "import-cpcl": openModal("Impor referensi CPCL", "Simulasi alur impor; belum tersambung ke database.", '<div class="notice-strip"><span class="notice-icon">i</span><span>Pada sistem sebenarnya, unggah Excel harus menampilkan pemetaan kolom, hasil setiap baris, dan rekap berhasil/ditolak/duplikat. Tidak boleh ada baris yang terlewat tanpa alasan.</span></div><div class="upload-box"><span class="upload-symbol">⇧</span><div><strong>Pilih file Excel CPCL (simulasi)</strong><span>Belum ada file yang diunggah ke server.</span></div><label class="btn btn-outline btn-sm">Pilih file<input type="file" id="cpcl-file" accept=".xlsx,.xls,.csv"></label></div>', '<button class="btn btn-outline" data-action="close-modal">Batal</button><button class="btn btn-primary" data-action="fake-import">Simulasikan validasi</button>'); break;
      case "detail-usulan": closeModal(); detailUsulanModal(state.usulan.find(x => x.id === target)); break;
      case "review-usulan": closeModal(); reviewUsulanModal(state.usulan.find(x => x.id === target)); break;
      case "revise-usulan": closeModal(); reviseUsulan(target); break;
      case "detail-laporan": closeModal(); detailLaporanModal(state.laporan.find(x => x.id === target)); break;
      case "review-laporan": closeModal(); reviewLaporanModal(state.laporan.find(x => x.id === target)); break;
      case "close-modal": closeModal(); break;
      case "submit-usulan-draft": submitUsulan($("#usulan-form"), "draft"); break;
      case "submit-usulan": submitUsulan($("#usulan-form"), "send"); break;
      case "submit-laporan-draft": submitLaporan($("#laporan-form"), "draft"); break;
      case "submit-laporan": submitLaporan($("#laporan-form"), "send"); break;
      case "save-review-usulan": saveReview(target, "usulan"); break;
      case "save-review-laporan": saveReview(target, "laporan"); break;
      case "save-account": saveAccount(); break;
      case "toggle-account":
        if (state.role !== "admin") { toast("Tidak berwenang."); break; }
        if (state.accounts[Number(target)]) { state.accounts[Number(target)].active = !state.accounts[Number(target)].active; persist(); renderPage(); toast("Status akun simulasi diubah."); }
        break;
      case "save-revision": saveRevision(target); break;
      case "fake-import":
        const file = $("#cpcl-file");
        if (!file || !file.files || !file.files.length) { toast("Pilih file terlebih dahulu untuk simulasi."); break; }
        closeModal();
        openModal("Hasil validasi simulasi", "Contoh hasil validasi, bukan hasil pembacaan file sebenarnya.", '<div class="metric-grid" style="grid-template-columns:repeat(3,minmax(0,1fr))">' + metric("Berhasil", 0, "Belum diproses", "✓") + metric("Ditolak", 0, "Belum diproses", "!", "rose") + metric("Duplikat", 0, "Belum diproses", "↺", "amber") + '</div><p class="form-help">File "' + escapeHTML(file.files[0].name) + '" tidak dibaca atau dikirim ke server. Implementasi impor sebenarnya belum dibuat.</p>', '<button class="btn btn-primary" data-action="close-modal">Mengerti</button>');
        break;
      case "guide-report": openModal("Panduan lampiran laporan", "Bahan konfirmasi kebutuhan dokumen.", '<p>BAST dan LPJ merupakan kategori dokumen laporan. LPJ dapat memiliki beberapa kuitansi atau bukti pendukung. Kebutuhan foto pada tahap 0/50/100 serta format, ukuran, dan jumlah file harus dipastikan pada keputusan D-08.</p><p class="form-help">Dokumen tersedia bukan berarti laporan sudah diperiksa atau disahkan.</p>'); break;
      case "cpcl-detail":
        const cp = CPCL.find(x => x.kode === target);
        if (cp) openModal("Detail referensi " + cp.kode, "Seluruh 13 atribut CPCL sesuai struktur kolom yang kamu berikan.", '<div class="detail-pairs"><div class="detail-pair"><small>Kode CPCL</small><strong>' + escapeHTML(cp.kode) + '</strong></div><div class="detail-pair"><small>Jenis kelompok</small><strong>' + escapeHTML(cp.jenis) + '</strong></div></div>' + cpclDetailPairs(cp), '<button class="btn btn-outline" data-action="close-modal">Tutup</button>');
        break;
      case "backdrop-close": if (event && event.target === event.currentTarget) closeModal(); break;
      default: break;
    }
  }

  document.addEventListener("click", event => {
    if (!event.target.closest(".combo-wrap") && !event.target.closest(".combo-options")) closeUsulanComboOptions();
    const loginRoleOption = event.target.closest("[data-login-role]");
    if (loginRoleOption) {
      const role = loginRoleOption.dataset.loginRole;
      if (ROLE[role]) {
        $("#demo-role").value = role;
        $$(".demo-role-card").forEach(card => {
          const selected = card.dataset.loginRole === role;
          card.classList.toggle("selected", selected);
          card.setAttribute("aria-pressed", String(selected));
        });
      }
      return;
    }
    const roleOption = event.target.closest("[data-role-option]");
    if (roleOption) { setRole(roleOption.dataset.roleOption); const menu = $("#role-menu"); if (menu) menu.remove(); return; }
    const pageLink = event.target.closest("[data-page]");
    const actionTarget = event.target.closest("[data-action]");
    if (actionTarget) {
      event.preventDefault();
      action(actionTarget.dataset.action, actionTarget.dataset.id, event);
      return;
    }
    if (pageLink) {
      event.preventDefault();
      go(pageLink.dataset.page);
    }
  });
  document.addEventListener("submit", event => {
    if (event.target.id === "login-demo-form") {
      event.preventDefault();
      setRole($("#demo-role").value);
      return;
    }
    if (["usulan-form", "laporan-form", "account-form", "review-usulan-form", "review-laporan-form", "revise-usulan-form"].includes(event.target.id)) event.preventDefault();
  });
  document.addEventListener("focusin", event => {
    if (event.target.matches(".usulan-combo")) {
      closeUsulanComboOptions(event.target.id);
      renderUsulanComboOptions(event.target.id, true);
    }
  });
  document.addEventListener("input", event => {
    if (event.target.matches(".usulan-combo")) {
      closeUsulanComboOptions(event.target.id);
      renderUsulanComboOptions(event.target.id, true);
      return;
    }
    if (event.target.matches(".table-search")) {
      event.target.dispatchEvent(new Event("change", { bubbles: true }));
    }
  });
  document.addEventListener("change", event => {
    if (event.target.matches(".usulan-combo")) {
      syncUsulanComboFromSelection(event.target.id);
      closeUsulanComboOptions();
      return;
    }
    if (state.page === "usulan" && event.target.closest("#page-content") &&
        event.target.matches(".table-search,.filter-status,.filter-provinsi,.filter-kabupaten,.filter-kecamatan,.filter-desa,.filter-kelompok")) {
      if (event.target.matches(".filter-provinsi,.filter-kabupaten,.filter-kecamatan,.filter-desa,.filter-kelompok")) refreshUsulanFilterOptions();
      else applyUsulanFilters();
      return;
    }
    if (event.target.matches(".doc-input")) {
      const box = event.target.closest(".upload-box");
      const strong = box && $("strong", box);
      if (strong && event.target.files && event.target.files[0]) strong.textContent = event.target.files[0].name;
    }
    if (event.target.id === "cpcl-file") {
      const strong = event.target.closest(".upload-box") && $("strong", event.target.closest(".upload-box"));
      if (strong && event.target.files && event.target.files[0]) strong.textContent = event.target.files[0].name;
    }
    if (event.target.matches(".table-search,.filter-status,.filter-region,.cpcl-type")) {
      const panel = event.target.closest(".panel");
      if (!panel) return;
      const search = ($(".table-search", panel)?.value || "").toLowerCase().trim();
      const status = $(".filter-status", panel)?.value || "";
      const region = $(".filter-region", panel)?.value || "";
      const cpclType = $(".cpcl-type", panel)?.value || "";
      const rows = $$("tbody tr", panel);
      let count = 0;
      rows.forEach(row => {
        const text = row.textContent.toLowerCase();
        const match = text.includes(search) && (!status || text.includes(status.toLowerCase())) && (!region || text.includes(region.toLowerCase())) && (!cpclType || text.includes(cpclType.toLowerCase()));
        row.hidden = !match;
        if (match) count++;
      });
      const result = $("#result-count", panel);
      if (result) result.textContent = count + " data";
    }
  });

  $("#enter-demo").addEventListener("click", () => setRole($("#demo-role").value));
  $("#toggle-password").addEventListener("click", () => {
    const input = $("#demo-password");
    const visible = input.type === "password";
    input.type = visible ? "text" : "password";
    $("#toggle-password").setAttribute("aria-label", visible ? "Sembunyikan password" : "Tampilkan password");
  });
  $("#forgot-password").addEventListener("click", () => toast("Pemulihan kata sandi akan dihubungkan saat autentikasi resmi diimplementasikan."));
  $$(".demo-role-card").forEach(card => card.setAttribute("aria-pressed", String(card.classList.contains("selected"))));
  $("#exit-demo").addEventListener("click", () => {
    $("#app-screen").hidden = true; $("#login-screen").hidden = false; closeModal();
  });
  $("#profile-menu").addEventListener("click", () => {
    let menu = $("#role-menu");
    if (menu) { menu.remove(); return; }
    menu = document.createElement("div");
    menu.id = "role-menu"; menu.className = "role-menu";
    menu.innerHTML = '<div style="padding:8px 10px 6px;font-size:9px;color:#89958c;font-weight:800;text-transform:uppercase;letter-spacing:.08em">Ganti role demo</div>' +
      Object.entries(ROLE).map(([key, value]) => '<button class="role-option ' + (key === state.role ? "active" : "") + '" data-role-option="' + key + '">' + escapeHTML(value.name) + '</button>').join("");
    $("#app-screen").insertBefore(menu, $("#app-screen").children[1]);
  });
  $("#notification-button").addEventListener("click", () => toast("Notifikasi pada prototipe belum terhubung ke pusat notifikasi."));
  $("#dismiss-notice").addEventListener("click", () => { state.noticeDismissed = true; $(".notice-strip").hidden = true; });
  $("#demo-role").addEventListener("change", () => {});
})();