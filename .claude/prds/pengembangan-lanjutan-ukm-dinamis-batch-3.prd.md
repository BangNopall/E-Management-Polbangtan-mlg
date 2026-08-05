# Pengembangan Lanjutan Modul UKM Dinamis Batch 3

## Problem
Setelah penyempurnaan alur kerja dan guard verifikasi jadwal pada Batch 2, pengelola UKM (Admin, Pelatih, Pembina) dan Mahasiswa masih menghadapi beberapa keterbatasan transparansi data presensi, pelaporan, serta navigasi sistem:
1. **Mahasiswa** tidak memiliki halaman khusus untuk melihat riwayat kehadiran UKM secara terstruktur (seperti riwayat keluar asrama atau kegiatan wajib).
2. **Staf Pengelola (Admin, Pelatih, Pembina)** tidak dapat memantau secara cepat rekap presensi per jadwal UKM pada halaman detail UKM (siapa yang sudah Hadir dan siapa yang belum/Alpha).
3. **Pengawas Asrama** saat melihat detail presensi individual mahasiswa di halaman *Data Absen Kegiatan* hanya mendapati data kegiatan wajib, tanpa informasi keaktifan presensi UKM mahasiswa tersebut.
4. **Petugas & Admin** sering kesulitan menemukan navlink *Data Absen Kegiatan* karena tersembunyi di dalam sub-nav dropdown *Kegiatan Wajib*.
5. **Pelatih/Admin** yang melihat kalender kegiatan UKM tidak bisa melihat detail kegiatan secara cepat saat mengklik event kalender.
6. **Pimpinan/Admin** belum memiliki fitur untuk mengekspor laporan presensi UKM ke format PDF dan Excel melalui halaman terpusat *Export Laporan*.

## Evidence
- Permintaan langsung dari pengguna (Product Owner):
  - *"1. Apakah di frontend sudah di terapkan untuk pagination di setiap tabel pada modul fitur ukm?"*
  - *"2. Pada role mahasiswa, buatkan Riwayat absen untuk UKM sama seperti riwaya keluar asrama"*
  - *"3. Pada role admin, pelatih, dan pembina buatkan riwayat absen untuk UKM agar tahu siapa saja yang udah presensi dan belum, mungkin bisa ditaruh pada page detail ukm lalu bikin tabs baru..."*
  - *"4. Pada page Data Absen Kegiatan di bagian detail data kegiatan setiap mahasiswa tambahkan juga riwaya ukmnya"*
  - *"5. Di sidebar untuk navlink Data Absen Kegiatan di pindah ke atas menjadi main navlink aja tidak usah di dalam sub navlink Kegiatan Wajib"*
  - *"6. Pada page detail UKM bagian jadwal di tabs kalender tersebut menampilkan judul, saat judul tersebut di klik muncul pop up modal detail data kegiatan tersebut"*
  - *"7. Pada page Export Laporan, tolong tambahkan untuk export laporan UKM harus bisa Generate PDF dan Excel"*

## Users
- **Mahasiswa (Role 3)**: Melihat riwayat presensi UKM pribadi di menu Riwayat Absen.
- **Admin (Role 1), Pelatih (Role 4), Pembina (Role 5)**: Memantau rekap presensi anggota UKM per jadwal, melihat detail kegiatan via kalender modal, mengekspor laporan PDF/Excel, serta mengawasi presensi gabungan mahasiswa.
- **Operator / Petugas (Role 2)**: Mengakses menu Data Absen Kegiatan lebih cepat melalui Main Navlink sidebar.

## Hypothesis
Kami percaya bahwa dengan **menambahkan halaman Riwayat Absen UKM mahasiswa, tab rekap presensi pada detail UKM, integrasi presensi UKM pada detail kegiatan mahasiswa, pemindahan navlink utama sidebar, modal detail kalender kegiatan, serta ekspor laporan PDF/Excel terpusat**, kami akan **meningkatkan transparansi data presensi UKM dan efisiensi pelaporan kegiatan asrama bagi mahasiswa, pengelola, maupun pimpinan**.
Kami akan tahu ini berhasil apabila:
1. Mahasiswa dapat mengakses `/dashboard/riwayat-ukm` dari sub-nav Riwayat Absen.
2. Pengelola dapat melihat tab "Rekap Presensi" di detail UKM dengan status `Hadir` dan `Alpha` per anggota.
3. Detail data absen kegiatan mahasiswa (`/data-kegiatan-wajib/{user}`) menampilkan seksi riwayat presensi UKM.
4. Menu *Data Absen Kegiatan* muncul sebagai Main Navlink di sidebar.
5. Klik event kalender di detail UKM memunculkan modal detail kegiatan.
6. Laporan UKM dapat di-generate dalam format PDF & Excel pada `/generate-laporan`.
7. Seluruh test suite (`php artisan test`) berjalan 100% hijau.

## Success Metrics
| Metric | Target | How measured |
|---|---|---|
| Kelengkapan Riwayat Presensi Mahasiswa | 100% presensi UKM mahasiswa dapat diakses via UI | Feature test `UkmMahasiswaTest` & manual verification |
| Transparansi Rekap Presensi Pengelola | Status Hadir/Alpha seluruh anggota dapat dipantau per jadwal | Feature test `UkmPresensiTest` |
| Ekspor Laporan PDF & Excel UKM | File PDF & XLSX berhasil ter-generate tanpa error | Feature test `GenerateReportTest` |
| Navigasi & UX Sidebar | Sub-nav Data Absen Kegiatan berpindah ke Main Navlink | Inspection view `layouts/main.blade.php` |
| Regresi Test Suite | 0 test gagal | `php artisan test` |

## Scope
**MVP** — 7 Fitur Utama Batch 3:
1. **Verifikasi Paginasi Frontend UKM**: Memastikan komponen link paginasi (`{{ $collection->links() }}`) dirender dengan benar di semua tabel modul UKM (`admin/ukm/index`, detail UKM `anggotas` & `jadwals`, serta `verifikasi`).
2. **Halaman Riwayat Absen UKM Mahasiswa**: Menambahkan sub-nav `• UKM` pada dropdown `Riwayat Absen` di sidebar mahasiswa, mengarah ke route `/dashboard/riwayat-ukm` (menampilkan tabel presensi UKM terpaginasi 20).
3. **Tab Rekap Presensi Anggota di Detail UKM**: Menambahkan Tab baru `Rekap Presensi` pada detail UKM (`/ukm/{id}`) untuk Admin/Pelatih/Pembina yang menampilkan daftar jadwal beserta statistik & daftar mahasiswa yang Hadir / Alpha.
4. **Integrasi Riwayat Presensi UKM di Detail Absen Kegiatan**: Menambahkan seksi / tabel "Riwayat Presensi UKM" pada halaman detail absen kegiatan mahasiswa (`/data-kegiatan-wajib/{user_id}`).
5. **Restrukturisasi Sidebar Navlink**: Memindahkan navlink `Data Absen Kegiatan` dari sub-nav `Kegiatan Wajib` ke level **Main Navlink** di sidebar agar langsung dapat diakses tanpa perlu membuka dropdown.
6. **Pop-up Modal Detail Kegiatan pada Kalender UKM**: Menambahkan event click handler pada FullCalendar di tab Kalender detail UKM untuk membuka modal yang menampilkan detail judul, tanggal, waktu, lokasi, jenis, status verifikasi, dan catatan pembina.
7. **Fitur Export Laporan UKM (PDF & Excel)**: Menambahkan form pilihan laporan UKM di `/generate-laporan` dengan opsi rentang tanggal / pilihan UKM, yang mendukung pembuatan laporan PDF (`barryvdh/laravel-dompdf`) dan Excel (`maatwebsite/excel`).

**Out of scope**
- Pengubahan logika pembuatan presensi otomatis (sudah berjalan di batch sebelumnya).
- Fitur absensi manual/override presensi oleh Pembina/Pelatih di luar scanner.

## Delivery Milestones

| # | Milestone | Outcome | Status | Plan |
|---|---|---|---|---|
| 1 | Restrukturisasi Sidebar & Halaman Riwayat UKM Mahasiswa | Navlink `Data Absen Kegiatan` berada di Main Navlink; Mahasiswa memiliki menu `Riwayat Absen -> UKM` | complete | `.claude/plans/pengembangan-lanjutan-ukm-dinamis-batch-3.plan.md` |
| 2 | Tab Rekap Presensi & Pop-up Kalender Detail UKM | Detail UKM memiliki tab Rekap Presensi (Hadir/Alpha) dan kalender event dapat diklik untuk modal detail | complete | `.claude/plans/pengembangan-lanjutan-ukm-dinamis-batch-3.plan.md` |
| 3 | Integrasi Presensi UKM pada Detail Absen Mahasiswa | Halaman `/data-kegiatan-wajib/{user_id}` menampilkan seksi Riwayat Presensi UKM mahasiswa | complete | `.claude/plans/pengembangan-lanjutan-ukm-dinamis-batch-3.plan.md` |
| 4 | Export Laporan UKM (PDF & Excel) | Halaman `/generate-laporan` dapat mengekspor laporan presensi UKM ke PDF & Excel | complete | `.claude/plans/pengembangan-lanjutan-ukm-dinamis-batch-3.plan.md` |
| 5 | Verifikasi Full Test Suite & Asset Build | Seluruh paginasi, fitur baru, dan unit/feature tests terverifikasi 100% hijau | complete | `.claude/plans/pengembangan-lanjutan-ukm-dinamis-batch-3.plan.md` |

## Open Questions
- [ ] Apakah ekspor laporan UKM pada `/generate-laporan` memerlukan filter per UKM spesifik atau bisa sekaligus semua UKM? — Assumption: Diberikan dropdown filter per-UKM (dengan opsi "Semua UKM") serta rentang tanggal.

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Perubahan lokasi navlink sidebar memecahkan highlight status `Request::is()` | Rendah | Rendah | Sesuaikan kondisi `Request::is('data-kegiatan-wajib*')` pada navlink baru |
| Query rekap presensi pada detail UKM memperlambat load page jika data presensi besar | Sedang | Sedang | Gunakan paginasi atau eager loading `with(['presensis.user'])` secara efisien |
| Export Excel/PDF gagal jika data kosong | Rendah | Sedang | Berikan penanganan data kosong pada Blade template PDF dan Export Class Excel |

---
*Status: DRAFT — requirements only. Implementation planning pending via /plan.*
