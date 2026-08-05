# Plan: Pengembangan Lanjutan Modul UKM Dinamis Batch 3

**Source PRD**: `.claude/prds/pengembangan-lanjutan-ukm-dinamis-batch-3.prd.md`
**Selected Milestone**: Milestone 1 - 5 (Comprehensive Delivery Batch 3)
**Complexity**: Medium

## Summary
Rencana implementasi untuk 7 fitur baru Modul UKM Dinamis Batch 3. Fitur mencakup verifikasi paginasi frontend di seluruh modul UKM, pemindahan navlink sidebar Data Absen Kegiatan ke Main Navlink, penambahan halaman Riwayat Absen UKM untuk mahasiswa, tab Rekap Presensi anggota (Hadir/Alpha) & modal detail kegiatan kalender pada detail UKM, integrasi data presensi UKM pada detail kegiatan mahasiswa, serta pembuatan fitur Export Laporan UKM (PDF & Excel) di `/generate-laporan`.

## Patterns to Mirror
| Category | Source | Pattern |
|---|---|---|
| Routing & Middleware | `routes/web.php:120-180` | `Route::middleware(['auth', 'role:...'])->group(...)` |
| Controllers | `app/Http/Controllers/UkmMahasiswaController.php:39-50` | Thin controller methods with `paginate(20)` & compact view returns |
| Blade & Navigation | `resources/views/partials/nav.blade.php:48-73` | Sub-nav dropdown list with `Request::is()` active state checks |
| PDF Export | `app/Http/Controllers/GenerateReportController.php:53-117` | `Pdf::loadView('admin.generate....', compact(...))` |
| Excel Export | `app/Exports/laporanAbsen.php` | `Maatwebsite\Excel\Concerns\FromView` rendering Blade templates to XLSX |
| Tests | `tests/Feature/Ukm/UkmMahasiswaTest.php` | `RefreshDatabase`, `$this->actingAs($user)->get(...)`, assertions on response & DB |

## Files to Change
| File | Action | Why |
|---|---|---|
| `resources/views/partials/nav.blade.php` | UPDATE | Pindahkan navlink Data Absen Kegiatan ke Main Navlink & tambah sub-nav Riwayat UKM mahasiswa |
| `routes/web.php` | UPDATE | Tambah route `/dashboard/riwayat-ukm` & route export laporan UKM (`admin.generate.ukm.pdf`/`excel`) |
| `app/Http/Controllers/UkmMahasiswaController.php` | UPDATE | Tambah method `riwayatUkm()` untuk halaman riwayat absen UKM mahasiswa |
| `resources/views/ukm/riwayat-ukm.blade.php` | CREATE | View riwayat absen UKM mahasiswa (mirip `riwayat-kegiatan.blade.php`) |
| `app/Http/Controllers/UkmController.php` | UPDATE | Pass data rekap presensi per jadwal ke view `admin.ukm.show` |
| `resources/views/admin/ukm/show.blade.php` | UPDATE | Tambah Tab "Rekap Presensi", tempatkan modal detail kegiatan, dan update script FullCalendar |
| `resources/views/admin/ukm/partials/rekap_presensi_table.blade.php` | CREATE | Partial view tabel rekap presensi anggota (Hadir/Alpha) per jadwal UKM |
| `resources/views/partials/modals/ukm-detail-kegiatan.blade.php` | CREATE | Modal pop-up detail kegiatan saat event kalender diklik |
| `app/Http/Controllers/kegiatanAsramaController.php` | UPDATE | Tambah query data presensi UKM pada `detailDataAbsenKegiatan()` |
| `resources/views/admin/detail-absenkegiatan.blade.php` | UPDATE | Tambah seksi tabel "Riwayat Presensi UKM" mahasiswa |
| `app/Http/Controllers/GenerateReportController.php` | UPDATE | Tambah method `generateLaporanUkm()` |
| `app/Exports/LaporanUkmExport.php` | CREATE | Maatwebsite Excel export class untuk Laporan Presensi UKM |
| `resources/views/admin/generate.blade.php` | UPDATE | Tambah card/form "Export Laporan UKM" pada halaman Generate Report |
| `resources/views/admin/generate/generate-ukm-pdf.blade.php` | CREATE | Blade template untuk PDF laporan UKM |
| `resources/views/admin/generate/generate-ukm-excel.blade.php` | CREATE | Blade template untuk Excel laporan UKM |
| `tests/Feature/Ukm/UkmBatch3Test.php` | CREATE | Feature test suite untuk memvalidasi 7 fitur baru |

## Tasks

### Task 1: Restrukturisasi Sidebar Navlink & Halaman Riwayat UKM Mahasiswa
- **Action**:
  1. Di `resources/views/partials/nav.blade.php`, keluarkan navlink "Data Absen Kegiatan" (`/data-kegiatan-wajib`) dari dropdown `Kegiatan Wajib` dan tempatkan sebagai Main Navlink independen di bawah `Scan Absen Keluar` / `Piket Petugas`.
  2. Di `resources/views/partials/nav.blade.php`, tambahkan sub-nav `• UKM` di bawah dropdown `Riwayat Absen` milik Mahasiswa (Role 3) yang mengarah ke `route('home.ukm.riwayatAbsen')`.
  3. Di `routes/web.php`, daftarkan `Route::get('dashboard/riwayat-ukm', [UkmMahasiswaController::class, 'riwayatUkm'])->name('home.ukm.riwayatAbsen')`.
  4. Di `app/Http/Controllers/UkmMahasiswaController.php`, buat method `riwayatUkm()` yang mengambil `UkmPresensi::where('user_id', $user->id)->with(['jadwal.ukm'])->latest()->paginate(20)`.
  5. Buat view `resources/views/ukm/riwayat-ukm.blade.php` yang merender tabel presensi UKM mahasiswa (Tanggal, Nama UKM, Judul Kegiatan, Status Kehadiran, Jam Scan) lengkap dengan `{{ $presensis->links() }}`.
- **Mirror**: `UkmMahasiswaController::riwayat()` & `resources/views/riwayat-kegiatan.blade.php`.
- **Validate**: `php artisan test --filter=UkmBatch3Test::test_mahasiswa_bisa_melihat_riwayat_absen_ukm`

### Task 2: Tab Rekap Presensi Anggota & Pop-up Modal Detail Kegiatan pada Kalender UKM
- **Action**:
  1. Di `app/Http/Controllers/UkmController.php` method `show()`, ambil data rekap presensi per jadwal UKM (atau eager load `jadwals.presensis.user`) untuk tab Rekap Presensi.
  2. Buat `resources/views/admin/ukm/partials/rekap_presensi_table.blade.php` yang menampilkan accordion/tabel daftar jadwal disetujui, statistik Hadir/Alpha, serta rincian mahasiswa yang Hadir & Alpha (terpaginasi 20 data per halaman jadwal/presensi).
  3. Di `resources/views/admin/ukm/show.blade.php`, tambahkan tab header "Rekap Presensi" (bersandingan dengan "Daftar Anggota", "Jadwal Kegiatan", "Kalender Kegiatan").
  4. Buat `resources/views/partials/modals/ukm-detail-kegiatan.blade.php` (modal Tailwind/Flowbite) yang menampilkan detail kegiatan (Judul, Tanggal, Jam, Lokasi, Jenis, Status Verifikasi, Catatan Pembina).
  5. Di `resources/views/admin/ukm/show.blade.php`, update konfigurasi FullCalendar pada `eventClick: function(info)` agar menangkap data event dan membuka modal `#modal-detail-kegiatan-ukm` serta mengisi bidang-bidang UI-nya.
- **Mirror**: Modal pattern Flowbite `resources/views/partials/modals/ukm-tambah-anggota.blade.php` & FullCalendar script di `resources/views/admin/ukm/show.blade.php`.
- **Validate**: `php artisan test --filter=UkmBatch3Test::test_pengelola_bisa_melihat_rekap_presensi_ukm`

### Task 3: Integrasi Data Presensi UKM pada Detail Absen Kegiatan Mahasiswa
- **Action**:
  1. Di `app/Http/Controllers/kegiatanAsramaController.php` method `detailDataAbsenKegiatan($id)`, tambahkan query `$presensiUkm = UkmPresensi::where('user_id', $id)->with(['jadwal.ukm'])->latest()->paginate(20, ['*'], 'ukm_page');`.
  2. Di `resources/views/admin/detail-absenkegiatan.blade.php`, tambahkan seksi Card "Riwayat Presensi UKM" di bawah seksi kegiatan wajib yang menampilkan tabel presensi UKM mahasiswa (Tanggal, Nama UKM, Judul Kegiatan, Jam Scan, Status Kehadiran) beserta `{{ $presensiUkm->links() }}`.
- **Mirror**: `kegiatanAsramaController.php:202-353` & `resources/views/admin/detail-absenkegiatan.blade.php`.
- **Validate**: `php artisan test --filter=UkmBatch3Test::test_detail_absen_kegiatan_mahasiswa_menampilkan_riwayat_ukm`

### Task 4: Fitur Export Laporan UKM (PDF & Excel)
- **Action**:
  1. Di `routes/web.php`, daftarkan route POST `generate-laporan/ukm` mengarah ke `GenerateReportController::generateLaporanUkm`.
  2. Di `resources/views/admin/generate.blade.php`, buat Card Form "Laporan Presensi UKM" yang memiliki input dropdown UKM (Semua / pilihan UKM), Rentang Tanggal (Tanggal Mulai - Tanggal Selesai), dan tombol pilihan Format (PDF / Excel).
  3. Di `app/Http/Controllers/GenerateReportController.php`, tambahkan method `generateLaporanUkm(Request $request)` yang memvalidasi input, mengambil data `UkmPresensi::with(['user.kelas', 'user.prodi', 'jadwal.ukm'])`, dan merender ke PDF (`Pdf::loadView('admin.generate.generate-ukm-pdf')`) atau Excel (`Excel::download(new LaporanUkmExport(...))`).
  4. Buat `app/Exports/LaporanUkmExport.php` (mengimplementasikan `FromView`) dan view `resources/views/admin/generate/generate-ukm-excel.blade.php`.
  5. Buat view `resources/views/admin/generate/generate-ukm-pdf.blade.php` berformat DomPDF (A4 portrait/landscape) yang rapi.
- **Mirror**: `GenerateReportController::pdfReport` & `App\Exports\laporanAbsen`.
- **Validate**: `php artisan test --filter=UkmBatch3Test::test_admin_bisa_generate_laporan_ukm_pdf_dan_excel`

### Task 5: Validasi Seluruh Paginasi & Full Test Suite Run
- **Action**:
  1. Review dan verifikasi bahwa seluruh tabel modul UKM (`admin/ukm/index`, `admin/ukm/show` anggota & jadwal & rekap presensi, `admin/ukm/verifikasi`, `dashboard/riwayat-ukm`, `detail-absenkegiatan`) merender `{{ $collection->links() }}` dan mendukung paginasi 20 baris secara konsisten.
  2. Jalankan `php artisan test` untuk memastikan 100% test suite hijau.
  3. Jalankan `npx vite build` untuk memastikan asset frontend terkompilasi tanpa error.
- **Validate**: `php artisan test` & `npx vite build`

## Validation
```bash
php artisan test --filter=UkmBatch3Test
php artisan test
npx vite build
```

## Risks
| Risk | Likelihood | Mitigation |
|---|---|---|
| Konflik nama parameter paginasi saat ada 3 paginator di detail UKM | Sedang | Gunakan parameter paginasi unik: `anggota_page`, `jadwal_page`, `rekap_page` |
| DomPDF memory overflow jika data ekspor sangat besar | Rendah | Batasi rentang query atau gunakan chunking jika diperlukan |

## Acceptance
- [ ] Navlink Data Absen Kegiatan pindah ke Main Navlink sidebar
- [ ] Sub-nav Riwayat UKM mahasiswa tersedia di `/dashboard/riwayat-ukm`
- [ ] Detail UKM memiliki Tab Rekap Presensi & modal detail kalender kegiatan
- [ ] Halaman detail absen kegiatan mahasiswa memuat riwayat presensi UKM
- [ ] Export laporan UKM mendukung format PDF & Excel pada `/generate-laporan`
- [ ] Paginasi 20 baris aktif di semua tabel modul UKM
- [ ] `php artisan test` 100% PASS
