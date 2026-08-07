# Rencana Implementasi Epic 03 — Modul Workflow Sistem Perizinan (Roadmap M0 – M6)

**Proyek:** E-Management Polbangtan-mlg  
**Epic:** 03 — Workflow Sistem Perizinan  
**Dokumen Spesifikasi Rujukan:** `docs/design/DESIGN-Epic03-Modul-Perizinan.md`, `docs/design/DESIGN-Epic03-SystemFlow-Perizinan.md`, `docs/design/DESIGN-Epic03-Frontend-Perizinan.md`  
**Status:** Planned  
**Tanggal:** 7 Agustus 2026  

---

## 📌 Milestone 0 (M0): Fondasi Data Pejabat & Dosen PA
**Fokus**: Menyediakan prasyarat resolusi penandatangan tanpa memblokir pengembangan akibat pengumpulan data Dosen PA.

### 1. Berkas yang Dibuat / Diubah
- **Migrasi & Model Baru**:
  - `database/migrations/2026_08_07_000001_create_pejabats_table.php` (Tabel `pejabats`)
  - `database/migrations/2026_08_07_000002_add_dosen_pa_id_to_kelas_table.php` (Tambah `dosen_pa_id` pada `kelas`)
  - `app/Models/Pejabat.php`
- **Controller & Views (Admin)**:
  - `app/Http/Controllers/Admin/PejabatController.php`
  - `resources/views/admin/pejabat/index.blade.php` (UI Kelola Pejabat)
  - `app/Imports/DosenPaImport.php` (Impor Excel Dosen PA per kelas)
- **Routes & Seeder**:
  - `routes/web.php` (Route admin `pejabat.*`)
  - `database/seeders/PejabatSeeder.php` (Seed Kaprodi, Kepala Asrama, Unit Kemahasiswaan)

### 2. Urutan Pengerjaan
1. Buat migrasi `pejabats` dan penambahan kolom `dosen_pa_id` pada tabel `kelas`.
2. Buat model `Pejabat` beserta scope `scopeUntuk($jabatan, $lingkup, $lingkupId)`.
3. Tambahkan relasi `dosenPa()` pada model `Kelas`.
4. Buat `PejabatController` dan view `resources/views/admin/pejabat/index.blade.php` untuk CRUD pejabat.
5. Buat kelas impor `DosenPaImport` untuk pembaruan Dosen PA berbasis file Excel per kelas.
6. Buat `PejabatSeeder` untuk mengisikan data ±6-10 pejabat utama (Kaprodi per prodi, Kepala Asrama, Unit Kemahasiswaan).

### 3. Gerbang Kualitas (Quality Gates)
- `php artisan test --filter PejabatTest`: Verifikasi CRUD pejabat, validasi rentang tanggal jabatan, dan pengujian fitur impor Dosen PA.
- Verifikasi bahwa `pejabats` terisi minimal 6 pejabat utama sebelum melangkah ke M1.

### 4. Risiko Utama & Mitigasi
- **Risiko**: Data Dosen PA belum diserahkan oleh semua Prodi saat modul siap.
- **Mitigasi**: Menerapkan *fallback* otomatis ke Kaprodi pada `ApproverResolver` (Lapis 1), sehingga M0 tidak tertahan oleh data Dosen PA.

---

## 📌 Milestone 1 (M1): Mesin Alur Persetujuan & Service Layer
**Fokus**: Membangun 5 tabel utama perizinan, `ApproverResolver`, `PengajuanIzinService`, dan state machine.

### 1. Berkas yang Dibuat / Diubah
- **Migrasi**:
  - `database/migrations/2026_08_07_000003_create_jenis_izins_table.php`
  - `database/migrations/2026_08_07_000004_create_izin_workflow_steps_table.php`
  - `database/migrations/2026_08_07_000005_create_pengajuan_izins_table.php`
  - `database/migrations/2026_08_07_000006_create_izin_approvals_table.php`
- **Models**:
  - `app/Models/JenisIzin.php`
  - `app/Models/IzinWorkflowStep.php`
  - `app/Models/PengajuanIzin.php`
  - `app/Models/IzinApproval.php`
- **Services Core**:
  - `app/Services/Perizinan/ApproverResolver.php` (Strategi: `pejabat`, `dosen_pa`, `pembina_ukm`, `petugas_jaga` + fallback)
  - `app/Services/Perizinan/PengajuanIzinService.php` (Satu-satunya manajer transisi status & penguncian transaksi)
- **Seeder**:
  - `database/seeders/JenisIzinSeeder.php` (Seed 4 jenis izin: `IZIN_KELUAR`, `IB`, `MENINGGALKAN_KELAS`, `DELEGASI` beserta workflow steps)

### 2. Urutan Pengerjaan
1. Eksekusi 4 migrasi inti skema perizinan.
2. Buat kelima model dengan `protected $guarded = ['id']` dan lengkapi relasi antar model.
3. Tulis `ApproverResolver` lengkap dengan 4 strategi penentuan approver dan mekanika *fallback*.
4. Tulis `PengajuanIzinService` membungkus method `ajukan()`, `setujui()`, `tolak()`, `batalkan()` dalam `DB::transaction` dan `lockForUpdate`.
5. Tulis `JenisIzinSeeder` untuk mengonversi Form A (AR.009) dan Form B (IB) menjadi baris data konfigurasi.

### 3. Gerbang Kualitas (Quality Gates)
- `php artisan test --filter ApproverResolverTest`: Unit test 4 strategi resolver (termasuk kasus kandidat kosong & fallback).
- `php artisan test --filter PengajuanIzinServiceTest`: Unit test state machine (transisi status valid, penolakan, pembatalan, dan penanganan konkurensi).

### 4. Risiko Utama & Mitigasi
- **Risiko**: Logika transisi status tercecer di controller di kemudian hari.
- **Mitigasi**: Enforce Hard Rule #3 dari `CLAUDE.md`: Dilarang keras melakukan `$izin->update(['status' => ...])` di luar `PengajuanIzinService`.

---

## 📌 Milestone 2 (M2): UI Pengajuan Mahasiswa & Inbox Persetujuan Staf
**Fokus**: Menyediakan alur end-to-end pengajuan, peninjauan (inbox), persetujuan, dan progress tracker.

### 1. Berkas yang Dibuat / Diubah
- **Controllers**:
  - `app/Http/Controllers/Student/PengajuanIzinController.php`
  - `app/Http/Controllers/Admin/IzinApprovalController.php`
- **Views**:
  - `resources/views/izin/index.blade.php` (Daftar izin mahasiswa)
  - `resources/views/izin/create.blade.php` (Form pengajuan + Pratinjau Alur Alpine.js)
  - `resources/views/izin/show.blade.php` (Detail pengajuan)
  - `resources/views/izin/partials/tracker.blade.php` (Component Stepper Vertikal)
  - `resources/views/admin/izin/inbox.blade.php` (Inbox persetujuan staf)
  - `resources/views/admin/izin/review.blade.php` (Layar keputusan mobile-first 360px)
  - `resources/views/admin/izin/partials/inbox_table.blade.php`
- **Navigasi & Route**:
  - `resources/views/partials/nav.blade.php` (Tambah badge count & menu perizinan)
  - `routes/web.php` (Route `home.izin.*` dan `admin.izin.*`)

### 2. Urutan Pengerjaan
1. Buat `PengajuanIzinController` (mahasiswa) untuk menampilkan riwayat dan memproses submit izin.
2. Buat endpoint AJAX pratinjau alur `GET /dashboard/izin/pratinjau-alur` yang memanfaatkan `ApproverResolver`.
3. Buat view `resources/views/izin/create.blade.php` dengan Alpine.js untuk menampilkan pratinjau alur persetujuan sebelum submit.
4. Buat `IzinApprovalController` (staf) untuk inbox persetujuan dan aksi `putuskan()`.
5. Buat view `resources/views/admin/izin/review.blade.php` teroptimasi mobile (360px) lengkap dengan validasi catatan saat menolak.
6. Buat komponen partial `tracker.blade.php` untuk menampilkan status 4 langkah persetujuan.

### 3. Gerbang Kualitas (Quality Gates)
- `php artisan test --filter PengajuanIzinFeatureTest`: Feature test untuk pengajuan mahasiswa, pembatalan, serta otorisasi IDOR.
- `php artisan test --filter IzinApprovalFeatureTest`: Test otorisasi persetujuan (Approver A tidak bisa menyetujui langkah Approver B, langkah N+1 teruji belum bisa diputuskan sebelum langkah N disetujui).

### 4. Risiko Utama & Mitigasi
- **Risiko**: IDOR — Mahasiswa / Approver dapat melihat atau memutuskan pengajuan yang bukan haknya.
- **Mitigasi**: Terapkan 3 `abort_unless` pengaman di controller sesuai §2.3 SystemFlow dan tes secara eksplisit.

---

## 📌 Milestone 3 (M3): Penomoran Surat, Cetak PDF & QR Verifikasi Publik
**Fokus**: Penerbitan dokumen resmi ber-QR dan halaman verifikasi publik tanpa login.

### 1. Berkas yang Dibuat / Diubah
- **Services**:
  - `app/Services/Perizinan/NomorSuratService.php` (Penomoran unik otomatis berurutan dengan row locking)
  - `app/Services/Perizinan/SuratIzinPdfService.php` (Render PDF via `barryvdh/laravel-dompdf` & `simple-qrcode`)
- **Controllers (Publik & Admin)**:
  - `app/Http/Controllers/Publik/VerifikasiIzinController.php`
- **Views**:
  - `resources/views/admin/generate/generate-izin.blade.php` (Template PDF kop Kementan + QR + TTD)
  - `resources/views/layouts/publik.blade.php` (Layout tanpa nav/sidebar)
  - `resources/views/publik/verifikasi-izin.blade.php` (Halaman hasil scan QR publik)
- **Routes**:
  - `routes/web.php` (Route publik `verifikasi-izin/{qr_token}` dengan middleware `signed`)

### 2. Urutan Pengerjaan
1. Buat `NomorSuratService` untuk menjamin nomor surat format `AR.009/xxxx/MON/YEAR` dibuat tanpa race condition.
2. Buat `SuratIzinPdfService` merender Blade `generate-izin.blade.php` ke PDF. Ekstrak header kop dari template docx ke `public/img/kop-surat.png`.
3. Tambahkan trigger pembuatan nomor surat dan PDF ketika `PengajuanIzinService::setujui()` mencapai langkah terakhir.
4. Buat `VerifikasiIzinController` dan view `resources/views/publik/verifikasi-izin.blade.php` untuk menampilkan status surat (BERLAKU / KEDALUWARSA) ketika QR dipindai.

### 3. Gerbang Kualitas (Quality Gates)
- `php artisan test --filter NomorSuratServiceTest`: Test keunikan nomor surat dalam kondisi akses bersamaan.
- `php artisan test --filter VerifikasiIzinTest`: Test keamanan URL signed, token acak 404, dan perlindungan data pribadi (keperluan/no HP tidak terkespos di publik).

### 4. Risiko Utama & Mitigasi
- **Risiko**: Pemalsuan surat cetak atau kebocoran data sensitif mahasiswa pada verifikasi QR publik.
- **Mitigasi**: Menggunakan `URL::signedRoute` + `qr_token` acak (bukan `id` urut) dan membatasi data publik hanya pada status keabsahan dokumen (ADR-008).

---

## 📌 Milestone 4 (M4): Integrasi Gerbang, Pembebasan Presensi & Pelanggaran
**Fokus**: Bagian paling bernilai — menghubungkan izin aktif ke scanner gerbang, presensi kegiatan wajib/UKM, dan pencatatan keterlambatan.

### 1. Berkas yang Dibuat / Diubah
- **Services**:
  - `app/Services/Perizinan/IzinGateResolver.php` (Read-only checker izin aktif)
  - `app/Services/Perizinan/PembebasanPresensiService.php` (Ubah status presensi `Alpha` -> `Izin`)
- **Controller Existing (Modifikasi Minimal - Hard Rule #1)**:
  - `app/Http/Controllers/QRController.php` (Menyisipkan 1 blok `if ($izin)` sebelum logika `telat`)
- **Controller & Service Kegiatan & UKM**:
  - `app/Http/Controllers/UkmJadwalController.php` / `kegiatanAsramaController.php` (Pengecekan pembebasan saat pembuatan jadwal baru)
- **Seeder**:
  - `database/seeders/JenisPelanggaranIzinSeeder.php` (Seed jenis pelanggaran "Terlambat kembali dari izin")

### 2. Urutan Pengerjaan
1. Buat `IzinGateResolver` untuk mengecek ketersediaan izin aktif mahasiswa pada jam scan.
2. Sisipkan 1 blok `if ($izin)` di `QRController::presense()` tepat sebelum cek `end_time` (tanpa merubah logika existing).
3. Buat `PembebasanPresensiService` untuk membebaskan presensi kegiatan (Apel, Senam, Upacara, UKM) saat izin disetujui.
4. Hubungkan pengembalian melewati `waktu_kembali` di `PengajuanIzinService::catatScanGerbang()` ke pembuat usulan `Pelanggaran` (`statusPelanggaran = 'submitted'`).
5. Seed `JenisPelanggaran` keterlambatan izin.

### 3. Gerbang Kualitas (Quality Gates)
- `php artisan test --filter GatePerizinanIntegrationTest`: **Sangat Krusial!** Test bahwa presensi mahasiswa biasa tanpa izin TETAP berjalan 100% persis seperti semula (uji regresi), dan mahasiswa dengan izin aktif berhasil dicatat `log_status = 'izin'`.
- `php artisan test --filter PembebasanPresensiTest`: Test bahwa presensi `Alpha` otomatis berubah menjadi `Izin` dan tidak mengganggu presensi status `Hadir`.

### 4. Risiko Utama & Mitigasi
- **Risiko**: Kerusakan/regresi pada alur scan gerbang utama (`QRController::presense()`) yang berdampak ke seluruh kampus.
- **Mitigasi**: Patuhi Hard Rule #1 (hanya menyisipkan 1 blok `if`), lakukan pengujian regresi ketat dengan `php artisan test`.

---

## 📌 Milestone 5 (M5): Konfirmasi Kedatangan Tiba, Monitor Asrama & Scheduled Jobs
**Fokus**: Pengawasan real-time Kepala Asrama, konfirmasi RT tanpa akun, notifikasi, dan pemeliharaan otomatis.

### 1. Berkas yang Dibuat / Diubah
- **Controllers & Views**:
  - `app/Http/Controllers/Publik/KonfirmasiTibaController.php`
  - `resources/views/publik/konfirmasi-tiba.blade.php` (Form RT/panitia 3 field)
  - `app/Http/Controllers/Admin/IzinMonitorController.php`
  - `resources/views/admin/izin/monitor.blade.php` (Dashboard "Siapa di luar sekarang")
- **Console Jobs**:
  - `app/Console/Commands/Perizinan/TandaiIzinKadaluarsa.php` (Job harian)
  - `app/Console/Commands/Perizinan/PeriksaKeterlambatan.php` (Job tiap 30 menit)
  - `app/Console/Commands/Perizinan/IngatkanApproverTertunggak.php` (Job pengingat SLA)
  - `app/Console/Kernel.php` (Jadwal pendaftaran job)

### 2. Urutan Pengerjaan
1. Buat `KonfirmasiTibaController` dan view `resources/views/publik/konfirmasi-tiba.blade.php` untuk memfasilitasi konfirmasi kedatangan di lokasi tujuan tanpa login.
2. Buat `IzinMonitorController` dan view `resources/views/admin/izin/monitor.blade.php` dengan 3 kartu ringkas dan auto-refresh (60 detik).
3. Buat 3 Artisan console commands untuk penanganan izin kedaluwarsa, pemantauan keterlambatan, dan pengingat SLA approver.
4. Daftarkan ketiga command tersebut di `app/Console/Kernel.php`.

### 3. Gerbang Kualitas (Quality Gates)
- `php artisan test --filter KonfirmasiTibaTest`: Test konfirmasi tiba (berhasil sekali, kunjungan kedua ditolak/read-only).
- `php artisan test --filter ScheduledJobsPerizinanTest`: Test idempotensi ketiga Artisan commands.

### 4. Risiko Utama & Mitigasi
- **Risiko**: Task scheduler tidak berjalan konsisten di server produksi.
- **Mitigasi**: Merancang seluruh scheduled job agar bersifat idempoten (dapat dijalankan manual sewaktu-waktu tanpa efek samping ganda).

---

## 📌 Milestone 6 (M6): Pengelolaan Jenis Izin, Laporan & Hardening
**Fokus**: Kemandirian admin, pelaporan bulanan, hardening keamanan (IDOR/Rate Limit), dan dokumentasi.

### 1. Berkas yang Dibuat / Diubah
- **Controllers & Views Admin**:
  - `app/Http/Controllers/Admin/JenisIzinController.php`
  - `resources/views/admin/izin/jenis/index.blade.php`
  - `resources/views/admin/izin/jenis/langkah.blade.php` (Pengaturan rantai & pratinjau alur)
  - `resources/views/admin/izin/data.blade.php` (Semua pengajuan + filter)
  - `resources/views/admin/izin/detail.blade.php`
- **Exports & Laporan**:
  - `app/Exports/IzinExport.php` (Excel Export via `FromView`)
  - `resources/views/admin/generate/generate-laporan-izin.blade.php` (PDF Rekap Periodik)

### 2. Urutan Pengerjaan
1. Buat CRUD `JenisIzinController` beserta UI pengatur urutan langkah persetujuan (`izin_workflow_steps`).
2. Buat halaman kelola `data-izin` dengan filter periode, prodi, blok, dan jenis izin.
3. Buat laporan ekspor Excel (`IzinExport`) dan PDF rekap (`generate-laporan-izin.blade.php`).
4. Lakukan hardening keamanan: auditing IDOR, throttling/rate-limiting pada form publik, dan penguncian transaksi.
5. Perbarui centang status milestone pada `CLAUDE.md`.

### 3. Gerbang Kualitas (Quality Gates)
- `php artisan test --filter HardeningSecurityTest`: Audit IDOR, CSRF, XSS, dan rate-limiting.
- `php artisan test`: Seluruh suite pengujian aplikasi (termasuk fitur existing) **wajib PASS 100%**.

### 4. Risiko Utama & Mitigasi
- **Risiko**: Admin merusak rantai persetujuan melalui UI kelola langkah.
- **Mitigasi**: Tambahkan validasi ketat pada backend saat menyimpan `izin_workflow_steps` (misal: urutan harus kontinu 1..N, resolver valid).

---

## 🚦 Rangkuman Jalur Kritis & Ringkasan Estimasi

```
[M0 Data Pejabat] ──> [M1 Core & Services] ──> [M2 UI Mahasiswa & Inbox] ──> [M3 PDF & QR Signed]
                                                                                      │
[M6 Admin & Report] <── [M5 Monitor & Cron] <─────────────────────────────────────────┘ (M4 Integrasi Gerbang)
```

- **Jalur Kritis**: M0 → M1 → M2 → M3 → M4.
- **Milestone Paling Bernilai**: **M4** (Integrasi Gerbang & Pembebasan Presensi).
