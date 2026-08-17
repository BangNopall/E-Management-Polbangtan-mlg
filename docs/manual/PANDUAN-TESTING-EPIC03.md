# 📖 Panduan Pengujian Browser: System Flow Epic 03 (Modul Workflow Sistem Perizinan)

Dokumen ini berisi panduan pengujian menyeluruh (*End-to-End Browser Testing*) untuk **Epic 03 — Modul Workflow Sistem Perizinan** pada aplikasi E-Management Asrama Polbangtan Malang.

---

## 👥 Roles & Akun Pengujian

Sebelum memulai pengujian, pastikan Anda telah memiliki data seeder/pengguna atau dapat login menggunakan akun sesuai perannya masing-masing:

| Peran / Role | `role_id` | Akses Utama di Browser |
|---|---|---|
| **Admin System** | `1` | `/admin/izin/jenis`, `/admin/pejabat`, `/admin/izin/monitor`, `/admin/izin/data` |
| **Operator / Petugas Jaga** | `2` | `/admin/kamera` (Scanner Gerbang), `/admin/izin/inbox` |
| **Mahasiswa** | `3` | `/dashboard/izin` (Pengajuan, QR, Riwayat Izin) |
| **Pelatih / Pembina UKM** | `4` / `5` | `/admin/izin/inbox` (Review Persetujuan UKM) |
| **Dosen PA / Pejabat** | `1` / User Staf | `/admin/izin/inbox` (Review Persetujuan Dosen PA/Pejabat) |

---

## 🚀 Alur Pengujian Sistem Flow (E2E Test Journey)

```text
[1. Setup Admin] ──> [2. Submit Mahasiswa] ──> [3. Review Approver] ──> [4. Surat & QR PDF]
                                                                                │
[7. Monitor & Laporan] <── [6. Kepulangan & Tiba] <── [5. Scan Out Gerbang] <──┘
```

---

### Tahap 1: Setup Master Data & Konfigurasi Alur (Admin)
> **Tujuan**: Memastikan jenis perizinan dan alur pejabat berwenang sudah dikonfigurasi di sistem.

1. Login sebagai **Admin** (`role_id = 1`).
2. Buka menu **Kelola Pejabat** (`/admin/pejabat`):
   - Pastikan data pejabat seperti **Kaprodi**, **Wadir 3**, atau **Kepala Asrama** sudah terisi.
3. Buka menu **Kelola Jenis Izin** (`/admin/izin/jenis`):
   - Klik **Tambah Jenis Izin Baru** (`/admin/jenis/create`).
   - Buat Jenis Izin (misal Kode: `IZIN_BIASA`, Nama: `Izin Keluar Asrama Biasa`).
   - Susun **Editor Alur Persetujuan (Workflow Steps)**:
     - **Langkah 1**: Label `Persetujuan Dosen PA`, Resolver: `Dosen PA Mahasiswa`.
     - **Langkah 2**: Label `Persetujuan Kaprodi`, Resolver: `Pejabat Berdasarkan Jabatan` (Centang: `Kaprodi`).
   - Klik **Simpan Konfigurasi Jenis Izin**.

---

### Tahap 2: Pengajuan Perizinan (Mahasiswa)
> **Tujuan**: Mahasiswa mengajukan izin dan sistem otomatis membekukan rantai penandatangan.

1. Switch/Login sebagai **Mahasiswa** (`role_id = 3`).
   *Catatan: Pastikan profil mahasiswa sudah lengkap (memiliki Prodi, Kelas, Dosen PA, dan Blok Asrama).*
2. Buka menu **Perizinan Asrama** (`/dashboard/izin`).
3. Klik tombol **Buat Pengajuan Izin** (`/dashboard/izin/create`).
4. Isi Form Pengajuan:
   - Pilih Jenis Izin (`IZIN_BIASA`).
   - Tentukan Waktu Berangkat (misal 2 jam dari sekarang) & Waktu Kembali (misal 6 jam dari sekarang).
   - Isi Tujuan Lokasi (misal `Puskesmas Malang`) dan Keperluan (`Pemeriksaan Kesehatan`).
5. Klik **Kirim Pengajuan Izin**.
6. **Hasil Expected**:
   - Pengajuan berstatus **`diajukan`**.
   - Halaman detail (`/dashboard/izin/{id}`) menampilkan alur persetujuan di mana **Langkah 1 (Dosen PA)** berstatus **`menunggu`** dan Langkah 2 masih **`dilewati / belum aktif`**.

---

### Tahap 3: Inbox & Eksekusi Persetujuan (Staf / Approver)
> **Tujuan**: Approver menerima notifikasi inbox dan menyetujui secara berurutan.

#### A. Eksekusi Langkah 1 (Dosen PA):
1. Login sebagai akun **Dosen PA** mahasiswa terkait.
2. Perhatikan menu sidebar **Inbox Perizinan** (`/admin/izin/inbox`) — terdapat **Badge Merah** jumlah persetujuan pending.
3. Klik pengajuan mahasiswa tersebut untuk membuka halaman **Review** (`/admin/izin/inbox/{approval_id}`).
4. Klik **Setujui Pengajuan**.
5. **Hasil Expected**:
   - Langkah 1 berubah menjadi **`disetujui`** (tercatat Waktu & IP Audit).
   - Sistem otomatis mengaktifkan **Langkah 2 (Kaprodi)**.

#### B. Eksekusi Langkah 2 / Final Step (Kaprodi):
1. Login sebagai akun **Kaprodi**.
2. Buka `/admin/izin/inbox`, pilih pengajuan tersebut.
3. Klik **Setujui Pengajuan**.
4. **Hasil Expected**:
   - Status utama pengajuan otomatis berubah dari `diajukan` -> **`disetujui`**.
   - Sistem menggenerasi **Nomor Surat Resmi** (format: `AR.009/XXX/VIII/2026`) dan **QR Token**.

---

### Tahap 4: Cetak Surat Izin & Verifikasi QR Signed URL (Mahasiswa / Publik)
> **Tujuan**: Mengunduh PDF resmi dan menguji verifikasi publik anti-pemalsuan.

1. Login kembali sebagai **Mahasiswa** -> Buka detail izin (`/dashboard/izin/{id}`).
2. Klik tombol **Unduh Surat Izin (PDF)**.
   - File PDF terunduh dengan Kop Resmi, Kode AR.009, Tabel Penandatangan bertanggal, dan QR Code di sudut bawah.
3. **Uji Verifikasi Publik**:
   - Pindai QR Code pada PDF (atau salin URL verifikasi publik dari QR).
   - Buka URL di browser (misal: `http://localhost:8000/verifikasi-izin/{qr_token}?signature=...`).
   - **Hasil Expected**: Halaman verifikasi publik terbuka (Status: **`SURAT IZIN VALID`**), menampilkan Identitas Mahasiswa & Penandatangan, tetapi **TIDAK** menampilkan isi `Keperluan` atau `No HP` demi kerahasiaan data privasi.

---

### Tahap 5: Integrasi Gerbang Scanning Asrama (Operator / Petugas Scan)
> **Tujuan**: Mahasiswa keluar gerbang asrama membawa izin aktif.

1. Login sebagai **Operator / Petugas Jaga** (`role_id = 2`).
2. Buka Kamera Scanner Gerbang (`/admin/kamera`).
3. Scan QR Code milik Mahasiswa (atau kirimkan payload QR via scanner).
4. **Hasil Expected**:
   - Sistem mengenali Mahasiswa memiliki Izin Aktif.
   - Log presensi gerbang tercatat sebagai **`log_status = izin`** (bukan `diluar` atau `telat`).
   - Status Mahasiswa berubah menjadi `status = 'izin'`.
   - Status Pengajuan Izin beranjak dari `disetujui` -> **`berjalan`** (`berangkat_at` tercatat otomatis).
   - Log jadwal kegiatan asrama (Apel/Senam/Upacara) pada rentang waktu izin otomatis berubah dari `Alpha` -> **`Izin`**.

---

### Tahap 6: Kepulangan, Konfirmasi Tiba, & Penanganan Keterlambatan
> **Tujuan**: Menguji proses kembali ke asrama (Tepat Waktu vs Terlambat).

#### Skenario A: Kembali Tepat Waktu
1. Operator melakukan scan masuk di `/admin/kamera` sebelum `waktu_kembali`.
2. Status pengajuan berubah menjadi **`selesai`**, `kembali_at` tercatat, dan status lokasi mahasiswa kembali ke `didalam`.

#### Skenario B: Kembali Terlambat (*Late Return*)
1. Operator melakukan scan masuk di `/admin/kamera` **setelah melewati** `waktu_kembali`.
2. Status pengajuan berubah menjadi **`terlambat`**.
3. **Hasil Expected**: Sistem secara otomatis mencatat **Baris Pelanggaran Disiplin (`Pelanggaran`)** dengan status `submitted` untuk diproses lebih lanjut oleh Pelatih/Admin.

#### Skenario C: Konfirmasi Tiba Eksternal (Jika `butuh_konfirmasi_tiba = 1`)
1. Buka URL Signed Konfirmasi Tiba yang dikirimkan ke pihak penerima/orang tua.
2. Isi Nama Penerima & Kontak Tiba -> Klik **Konfirmasi Kedatangan**.
3. Re-open link yang sama -> Menampilkan **Tanda Terima Statis** (Link Sekali Pakai / Single-use Signed URL).

---

### Tahap 7: Monitor Asrama Real-Time & Ekspor Laporan Audit (Admin)
> **Tujuan**: Admin memantau seluruh aktivitas mahasiswa keluar-masuk dan mengunduh laporan.

1. Login sebagai **Admin**.
2. Buka **Monitor Perizinan Asrama** (`/admin/izin/monitor`):
   - Terdapat 3 Kartu Ringkasan (*Sedang Berjalan*, *Terlambat*, *Mendatang*).
   - Tabel pemantauan otomatis memperbarui data (*auto-refresh*) setiap **60 detik** via Vanilla JS fetch.
3. Buka **Data Perizinan & Audit Trail** (`/admin/izin/data`):
   - Klik **Detail** pada salah satu izin -> Tampilan halaman audit trail lengkap yang merekam IP, User Agent, dan timestamp setiap aksi persetujuan.
   - Klik **Export PDF** (`/admin/izin/data-export/pdf`) -> Mengunduh rekap PDF perizinan.
   - Klik **Export Excel** (`/admin/izin/data-export/excel`) -> Mengunduh spreadsheet XLSX resmi.

---

## 💡 Tips & Shortcut Testing Quick-Start

- **Menjalankan Automated Tests**:
  Jika ingin meyakinkan logika sistem sebelum menguji di browser, jalankan command:
  ```bash
  php artisan test --filter=Izin
  ```
- **Simulasi Waktu Terlambat (Artisan Command)**:
  Untuk menguji pemicu otomatis tanpa menunggu jam habis:
  ```bash
  php artisan izin:periksa-keterlambatan
  php artisan izin:tandai-kadaluarsa
  ```
