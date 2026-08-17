# ADR-004: Integrasi Konseling via SSO, Bukan Migrasi

**Status:** Accepted — menggantikan (supersedes) ADR-001
**Tanggal:** 2 Agustus 2026
**Deciders:** Pak Yongki (document owner), Muhammad Naufal Mathara R (developer)

## Context

ADR-001 memutuskan modul Konseling ditulis ulang dan dipindahkan ke E-Management,
lalu E-Klinik dinonaktifkan. Setelah menilai ulang beban kerjanya (migrasi data
berisiko, port ~30 view Tailwind 3 ke 4, dan refactor kontrol akses data psikologis
yang rumit), diputuskan menempuh jalur lebih ringan.

Prasyarat keputusan ini: **E-Klinik tetap dipelihara.** Konselor tetap bekerja di
E-Klinik. Yang diselesaikan hanyalah pengalaman "satu pintu" bagi mahasiswa — masalah
navigasi dan identitas, bukan masalah lokasi kode.

## Decision

E-Management berperan sebagai **Identity Provider**. Mahasiswa login sekali di
E-Management, lalu diantar ke E-Klinik lewat **tiket bertanda tangan berumur pendek**
(HMAC), tanpa login ulang. Modul konseling tetap berada dan berjalan di E-Klinik.

Tidak ada migrasi data. Tidak ada penonaktifan E-Klinik.

## Options Considered

Sudah dibahas lengkap di ADR-001 dan ADR-002. Ringkasnya:

- **Opsi B (migrasi penuh, ADR-001):** benar secara arsitektur jangka panjang, tetapi
  6-7 minggu kerja dengan risiko tinggi pada data psikologis. **Ditinggalkan.**
- **Opsi 3 (SSO handoff, ADR ini):** ~3-5 hari kerja, risiko rendah, tetapi menyisakan
  dua codebase untuk dipelihara. **Dipilih.**

## Desain Teknis

Alur handoff:

1. Mahasiswa sudah login di E-Management, menekan menu "Layanan Konseling".
2. E-Management membuat payload berisi NIM, waktu kedaluwarsa (60 detik), dan nonce
   unik. Payload ditandatangani HMAC-SHA256 dengan kunci rahasia bersama.
3. Mahasiswa diarahkan ke endpoint SSO E-Klinik membawa payload dan tanda tangan.
4. E-Klinik memverifikasi tanda tangan (`hash_equals`, aman terhadap timing),
   memeriksa kedaluwarsa, memeriksa nonce belum pernah dipakai (anti-replay), lalu
   mencari user berdasarkan NIM dan menjalankan `Auth::login`.
5. Mahasiswa masuk ke dashboard konseling E-Klinik.

Prinsip keamanan:

- Kunci rahasia disimpan di `.env` kedua aplikasi, tidak pernah di dalam kode.
- Masa berlaku tiket sangat pendek (60 detik).
- Nonce disimpan di tabel `sso_tickets` di E-Klinik untuk mencegah tiket dipakai ulang.
- Perbandingan tanda tangan memakai `hash_equals`, bukan `==`.
- Wajib HTTPS di kedua sisi (tiket lewat URL, aman karena berumur sangat pendek).
- Tiket hanya membawa identitas (NIM). Otorisasi apa yang boleh dilakukan konselor
  tetap ditentukan oleh data role E-Klinik sendiri, bukan dari tiket.
- User yang tidak ditemukan berdasarkan NIM **ditolak dan dicatat**, bukan dibuatkan
  otomatis.

## Consequences

**Menjadi lebih mudah:**
- Tidak ada migrasi data konseling sama sekali
- Data psikologis tidak pernah menyentuh database asrama — kerahasiaan justru lebih
  terjaga, dan seluruh kebutuhan Policy/enkripsi/audit log di sisi asrama gugur
- Fitur rujukan (`request_rujukan`) tetap berfungsi apa adanya karena `surat_rujukans`
  di E-Klinik tetap ada
- Pekerjaan turun dari 6-7 minggu menjadi sekitar 3-5 hari

**Menjadi lebih sulit:**
- Dua codebase dipelihara selamanya. E-Klinik tetap di Laravel 10 / PHP 8.1, yang
  suatu saat perlu upgrade demi dukungan keamanan.
- Ada satu titik integrasi (endpoint SSO) yang menjadi permukaan keamanan baru dan
  harus dijaga.

**Yang perlu ditinjau ulang:**
- Jika di kemudian hari tidak ada lagi yang merawat E-Klinik, keputusan ini gugur dan
  migrasi penuh (ADR-001) kembali menjadi relevan.

## Dampak ke ADR lain

- **ADR-001 (migrasi):** digantikan oleh ADR ini. Status diubah menjadi Superseded.
- **ADR-002 (rujukan & arsip):** sebagian besar tidak lagi berlaku. Fitur rujukan
  tetap hidup di E-Klinik, dan tidak ada arsip/cutover karena E-Klinik tidak dimatikan.
  Status diubah menjadi Superseded, kecuali catatan retensi data yang tetap relevan
  bila suatu saat E-Klinik benar-benar dipensiunkan.
- **ADR-003 (RBAC):** tetap berlaku, tetapi bukan lagi prasyarat konseling. Refactor
  RBAC tetap dibutuhkan untuk Epic 01, 03, dan 04, jadi tetap dikerjakan — hanya tidak
  lagi menghalangi integrasi konseling.

## Prasyarat

Satu-satunya pekerjaan dari rencana lama yang tetap wajib: **NIM di E-Klinik.**
Tabel `users` E-Klinik belum punya kolom NIM (nilainya ada di `cdmis`). Handoff
mengenali user lewat NIM, jadi E-Klinik harus bisa mencari user berdasarkan NIM.
Lihat playbook Fase 1 (rekonsiliasi identitas) yang tetap dipakai.

## Action Items

1. [ ] Pastikan kolom `nim` ada dan terisi di tabel `users` E-Klinik (prasyarat)
2. [ ] Sepakati kunci rahasia bersama, simpan di `.env` kedua aplikasi
3. [ ] Bangun penerbit tiket + menu "Layanan Konseling" di E-Management
4. [ ] Bangun penerima tiket (endpoint SSO) + tabel anti-replay di E-Klinik
5. [ ] Uji alur lengkap, termasuk kasus gagal (tiket kedaluwarsa, tanda tangan salah,
       tiket dipakai ulang, NIM tidak ditemukan)
6. [ ] Pastikan HTTPS aktif di kedua sisi
