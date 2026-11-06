# Referensi wilayah Indonesia

`regions-id.json` berisi nama 38 provinsi dan 514 kabupaten/kota yang digunakan pada form Pelapor untuk pilihan wilayah bertingkat.

Sumber data saat pengambilan: API publik Wilayah Indonesia, https://wilayah.id/ (endpoint `/api/provinces.json` dan `/api/regencies/{kode-provinsi}.json`). Metadata provinsi sumber mencantumkan pembaruan 2025-07-04. Dataset dibundel lokal agar form dan validasi tidak bergantung pada ketersediaan API eksternal saat runtime.

Jika ada pemekaran/perubahan nama wilayah, dataset ini perlu diperbarui dari sumber resmi terkini dan diuji kembali sebelum dirilis.