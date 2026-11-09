# SITERA

SITERA adalah aplikasi web internal untuk mengelola laporan kegiatan irigasi dari tingkat daerah hingga pusat. Aplikasi mendukung pembuatan dan pengiriman laporan, pemeriksaan oleh Verifikator, pemantauan oleh Pimpinan, pengelolaan akun dan data CPCL oleh Super Admin, serta ekspor rekap.

## Teknologi

- PHP 8.5 dan Laravel 13.x (versi yang diuji pada proyek ini: Laravel 13.39).
- MariaDB/MySQL untuk penyimpanan aplikasi.
- Blade untuk halaman server-rendered, dengan CSS dan JavaScript untuk antarmuka.
- Composer untuk dependensi PHP.
- Vite tersedia untuk entry point `resources/css/app.css` dan `resources/js/app.js`. Aset role operasional dan prototype juga disimpan di `public/sitera/`.

## Empat role

- **Pelapor Daerah** — memilih CPCL sesuai wilayah, membuat draf dan mengirim paket laporan, mengelola lampiran, melihat status dan mengajukan koreksi.
- **Verifikator Pusat** — meninjau antrean laporan terkirim, mencatat hasil Sesuai/Tidak sesuai, dan melihat riwayat verifikasinya.
- **Pimpinan Kementan** — memantau dashboard dan rekap secara baca-saja, menerapkan filter, serta mengekspor laporan.
- **Super Admin** — mengelola akun dan status/PIC, mereset akses, mengelola dan mengimpor data CPCL, serta melihat Audit Log.

Akses data dan tindakan dibatasi di sisi server berdasarkan autentikasi, role, kepemilikan, serta wilayah yang relevan.

## Persyaratan

- PHP 8.5 dengan ekstensi Laravel yang diperlukan; untuk koneksi MariaDB aktifkan `pdo_mysql`.
- Composer 2.x.
- MariaDB/MySQL yang dapat diakses aplikasi.
- Ekstensi PHP `zip` diperlukan untuk membaca file XLSX saat impor CPCL. Dukungan ekspor PDF menggunakan Dompdf.
- Node.js dan npm hanya diperlukan jika mengembangkan atau membangun aset Vite. Aset `public/sitera/` tersedia langsung sebagai berkas statis.

Gunakan versi dependensi yang dikunci dalam `composer.lock`. `package-lock.json` tidak tersedia di repository ini; jangan menganggap versi dependency NPM terkunci.

## Instalasi lokal

1. Clone repository, lalu masuk ke direktori proyek.
2. Pasang dependency PHP:

   ```bash
   composer install
   ```

3. Salin `.env.example` ke `.env`, lalu konfigurasi `APP_URL`, koneksi database, session, cache, dan nilai aplikasi lain sesuai lingkungan. Jangan commit `.env` atau memasukkan credential ke source code.
   Pastikan mengatur `SITERA_DEFAULT_PASSWORD` dengan nilai unik dan kuat khusus instalasi ini. Password ini merupakan kredensial awal sementara saat Super Admin mendaftarkan akun baru atau mereset akses pengguna. Setiap pengguna wajib mengganti password awal tersebut saat login pertama kali sebelum akun dapat diaktifkan.
4. Buat application key:

   ```bash
   php artisan key:generate
   ```

5. Buat database kosong pada MariaDB/MySQL. Contoh SQL berikut membuat database dengan karakter UTF-8:

   ```sql
   CREATE DATABASE sitera CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

6. Isi konfigurasi koneksi di `.env` menggunakan database, host, port, username, dan password milik lingkungan Anda. Jangan gunakan database operasional untuk eksperimen atau pengujian destruktif.
7. Jalankan migrasi secara normal setelah memastikan target database yang dipilih:

   ```bash
   php artisan migrate
   ```

8. Seeder standar hanya menyiapkan role aplikasi:

   ```bash
   php artisan db:seed
   ```

   Jangan jalankan seeder bertanda demo pada database operasional. Seeder pencarian/filter bersifat opt-in dan ditujukan hanya untuk lingkungan lokal yang telah disetujui. Jangan gunakan `migrate:fresh` pada database berisi data.

## Menjalankan aplikasi

```bash
php artisan serve
```

Buka alamat lokal yang ditampilkan Artisan. Pengguna masuk melalui halaman login; akun dibuat dan dikelola oleh Super Admin sesuai alur aktivasi aplikasi.

Jika mengubah entry point Vite, pasang dependency frontend terlebih dahulu sesuai kebijakan proyek, lalu jalankan:

```bash
npm run dev
```

Untuk membangun aset Vite:

```bash
npm run build
```

Perintah frontend tersebut tidak diperlukan hanya untuk menyajikan aset statis yang sudah berada di `public/sitera/`.

## Pengujian dan pemeriksaan

Jalankan rangkaian tes PHP:

```bash
vendor/bin/phpunit
```

Jalankan tes JavaScript:

```bash
node --test tests/js/*.test.cjs
```

Kompilasi cache Blade untuk memeriksa template:

```bash
php artisan view:cache
```

Baseline proyek yang tercatat saat dokumentasi ini diperbarui adalah **173 tes PHP dengan 1.045 assertions**, **17 tes JavaScript**, **48 routes**, dan **33 template Blade**. Tes ini menggunakan konfigurasi pengujian; hasilnya bukan pengganti pengujian visual pada browser atau validasi deployment produksi.

## Struktur direktori

```text
app/                 Controller, middleware, model, notifikasi, dan service aplikasi
bootstrap/           Bootstrap Laravel dan registrasi provider
config/              Konfigurasi Laravel dan SITERA
database/
  factories/         Factory untuk kebutuhan tes
  migrations/        Riwayat perubahan skema (pertahankan migration historis)
  seeders/           Seeder role dan seeder data demo opt-in
public/
  sitera/            Prototype, stylesheet, script, logo, dan aset role
  css/               CSS publik bersama
resources/
  css/, js/          Entry point Vite
  data/              Katalog wilayah Indonesia
  views/             Template Blade per role dan workflow
routes/              Definisi route web dan console
storage/             Placeholder dan direktori runtime Laravel
tests/
  Feature/           Tes alur aplikasi dan akses role
  Unit/              Tes unit PHP
  js/                Tes JavaScript dengan Node test runner
```

## Catatan keamanan dan data

- `.env`, credential, database lokal, hasil build, cache, log, session, dan file runtime tidak boleh masuk commit.
- Migration historis merupakan bagian dari riwayat skema dan tidak boleh dihapus hanya karena terlihat kosong.
- Data uji yang ada di database lokal ditandai `FILTER-DEMO`; jangan mencampurkannya dengan data operasional atau deployment.
- Backup, salinan database, dan dump harus disimpan di luar repository.
- Repository baseline tidak dengan sendirinya membuktikan kesiapan produksi; uji konfigurasi, akses, penyimpanan lampiran, backup, dan deployment secara terpisah.

## Lisensi

Periksa ketentuan lisensi yang ditetapkan pemilik proyek sebelum distribusi. Lisensi framework Laravel tidak otomatis menentukan lisensi aplikasi SITERA.

## Repository

Remote Git:

```text
git@github.com:agungggggggg/sitera.git
```

Clone menggunakan SSH setelah autentikasi GitHub/SSH pada mesin Anda dikonfigurasi.