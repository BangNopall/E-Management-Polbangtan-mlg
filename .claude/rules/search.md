# Aturan Pencarian & Navigasi Repositori

Untuk menghemat token dan menjaga efisiensi context window, patuhi aturan pencarian berikut tanpa pengecualian:

1. DILARANG menggunakan `grep`, `ripgrep`, atau `rg` untuk mencari string di dalam basis kode.
2. DILARANG membaca seluruh isi direktori hanya untuk mencari implementasi fungsi.
3. WAJIB menggunakan perintah `/mgrep` untuk semua pencarian kode lokal.
4. Gunakan `/mgrep --web [query]` jika kamu membutuhkan dokumentasi terbaru terkait dependensi yang digunakan (seperti versi spesifik dari framework), daripada mengandalkan tebakan atau data latih lama.