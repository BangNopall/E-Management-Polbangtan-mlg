# Penyempurnaan Alur & Tata Kelola Modul UKM Dinamis

## Problem
Setelah batch perbaikan pertama (lihat `.claude/prds/perbaikan-modul-ukm-dinamis.prd.md`) dipakai di lingkungan nyata, muncul 8 cacat baru yang terpusat pada dua sumber: **alur verifikasi jadwal yang tidak ditegakkan** dan **jalan buntu operasional yang tidak punya jalan keluar**. Cacat paling serius bersifat integritas data — jadwal yang belum/tidak disetujui Pembina tetap bisa dibuka scanner-nya dan tetap tampil sebagai jadwal resmi di dashboard mahasiswa, sehingga presensi bisa terkumpul untuk kegiatan yang secara formal belum sah. Cacat paling menghambat bersifat jalan buntu — cascade nonaktif anggota yang diperkenalkan di batch sebelumnya tidak punya operasi kebalikan, sehingga Admin yang mengaktifkan kembali sebuah UKM tidak punya cara apapun di UI untuk memulihkan keanggotaan.

## Evidence
- Laporan langsung dari pengguna (product owner) setelah batch perbaikan pertama dipakai: *"Ketika UKM di nonaktifkan semua user akan statusnya nonaktif, tolong buatkan bagaimana caranya mengaktifkan usernya lagi"* — konfirmasi bahwa cascade dari milestone sebelumnya menciptakan jalan buntu operasional yang tidak diantisipasi saat keputusan "admin kelola manual" diambil.
- *"Saat status jadwal tersebut belum disetujui harusnya tidak dapat membuka kamera scan serta di role mahasiswa tidak tampil di jadwal mendatang"* — pengguna mengidentifikasi sendiri bahwa status verifikasi tidak menggerakkan perilaku sistem manapun.
- Investigasi kode mengonfirmasi bahwa `status_verifikasi` saat ini **murni dekoratif**: `UkmScanController::show()`/`store()` hanya memeriksa keanggotaan staf tanpa memeriksa status jadwal, dan `UkmMahasiswaController::index()` hanya memfilter `tanggal >= hari ini` tanpa memfilter status. Kolom `catatan_pembina` tersimpan di database tapi tidak pernah dirender di tabel jadwal manapun — alasan penolakan menjadi write-only.
- Investigasi juga mengonfirmasi 3 dari 8 isu adalah fitur "setengah jadi": `UkmJadwalController` tidak punya `destroy()` sama sekali (isu #7), dropdown mahasiswa dilewat saat Choices.js diterapkan ke dropdown staf (isu #2), dan halaman verifikasi Pembina menampilkan jadwal `draft` lengkap dengan tombol Setujui/Tolak padahal alur mensyaratkan pengajuan lebih dulu (isu #3).

## Users
- **Primary**: Admin (role_id 1) — mengelola siklus hidup UKM dan keanggotaan; saat ini terjebak tanpa jalan keluar setelah menonaktifkan lalu mengaktifkan kembali sebuah UKM.
- **Primary**: Pembina UKM (role_id 5) — memverifikasi jadwal; saat ini dibingungkan oleh jadwal `draft` yang muncul di antrian verifikasinya dengan tombol yang menyesatkan.
- **Primary**: Pelatih (role_id 4) — membuat dan mengelola jadwal; tidak bisa menghapus jadwal yang salah buat, dan tidak pernah melihat alasan penolakan dari Pembina.
- **Primary**: Mahasiswa anggota UKM (role_id 3) — melihat jadwal mendatang; saat ini disuguhi jadwal yang belum sah dan tanpa informasi lokasi maupun jam selesai.
- **Not for**: Operator (role_id 2) — tidak memiliki kewenangan pada modul UKM.

## Hypothesis
Kami percaya **penegakan status verifikasi sebagai gerbang perilaku sistem (scanner & visibilitas mahasiswa), pemulihan jalan keluar operasional (reaktivasi anggota & hapus jadwal draft), dan pelengkapan informasi yang sudah tersimpan tapi tidak ditampilkan (catatan pembina, lokasi & jam selesai)** akan **menghilangkan risiko presensi terkumpul pada kegiatan yang belum sah sekaligus membebaskan Admin/Pelatih dari jalan buntu operasional** untuk **seluruh peran pengelola dan anggota UKM**.
Kami akan tahu ini berhasil ketika **tidak ada satupun presensi baru yang bisa dibuat pada jadwal berstatus selain `disetujui`; Admin dapat memulihkan keanggotaan tanpa akses database; Pelatih melihat alasan penolakan dan dapat menghapus jadwal draft; mahasiswa hanya melihat jadwal sah beserta lokasi dan rentang waktunya; serta seluruh test suite (`php artisan test`) tetap hijau 100%**.

## Success Metrics
| Metric | Target | How measured |
|---|---|---|
| Scanner terbuka untuk jadwal belum disetujui | 0 kejadian (HTTP 403) | Feature test per status (`draft`/`menunggu`/`ditolak`/`disetujui`) |
| Jadwal belum disetujui tampil di dashboard mahasiswa | 0 baris | Feature test `assertDontSee` + query filter |
| Admin memulihkan keanggotaan tanpa akses DB | 100% berhasil via UI | Manual QA + feature test aktif-all & aktif-per-user |
| Catatan penolakan Pembina terbaca Pelatih/Admin | 100% jadwal ditolak menampilkan catatan | Feature test `assertSee` catatan |
| Jadwal `draft` muncul di antrian verifikasi Pembina | 0 baris | Feature test pada query verifikasi |
| Regresi test suite | 0 test gagal | `php artisan test` full run |

## Scope
**MVP** — Delapan perbaikan dikirim sebagai satu batch, dikelompokkan menjadi 5 milestone berdasarkan kohesi (bukan urutan laporan):

1. **Penegakan status verifikasi** — jadwal non-`disetujui` menolak akses scanner (server-side guard, bukan sekadar menyembunyikan tombol) dan tidak muncul di jadwal mendatang mahasiswa *(isu #4)*.
2. **Alur verifikasi Pembina dirapikan** — jadwal `draft` disembunyikan dari antrian verifikasi Pembina dan penyetujuan atas `draft` ditolak server-side; Pelatih wajib "Ajukan Verifikasi" lebih dulu *(isu #3, sesuai keputusan user)*.
3. **Jalan keluar operasional dipulihkan** — reaktivasi anggota (per-user dan aktifkan-semua, hanya tersedia saat UKM berstatus aktif) *(isu #1)*, dan hapus jadwal berstatus `draft` oleh Pelatih/Admin *(isu #7)*.
4. **Informasi tersembunyi ditampilkan** — `catatan_pembina` dirender di tabel jadwal detail UKM untuk Pelatih/Admin *(isu #5)*; `lokasi` dan `selesai_acara` ditampilkan di jadwal mendatang mahasiswa *(isu #6)*; dropdown mahasiswa memakai search-dropdown seperti dropdown staf *(isu #2)*.
5. **Pagination 20 data per tabel** — seluruh tabel modul UKM dipaginasi 20 baris; dua tabel pada halaman detail UKM (anggota & jadwal) memakai nama parameter halaman terpisah agar saling independen *(isu #8, sesuai keputusan user)*.

**Out of scope**
- Auto-reaktivasi anggota saat UKM diaktifkan kembali — keputusan sebelumnya dipertahankan; yang ditambahkan adalah *jalan manual*-nya, bukan otomatisasi.
- Edit jadwal (update) — hanya hapus untuk status `draft` yang masuk scope; mengubah jadwal yang sudah diajukan/disetujui memerlukan desain alur re-verifikasi tersendiri.
- Soft-delete / restore untuk jadwal — konsisten dengan keputusan hapus UKM permanen di batch sebelumnya; tabel `ukm_jadwals` tidak punya kolom `deleted_at`.
- Notifikasi ke Pelatih saat jadwal ditolak — di luar MVP; menampilkan catatan di tabel sudah cukup untuk memvalidasi hipotesis.
- Migrasi search-dropdown ke form lain di luar modul UKM — tetap konsisten dengan pembatasan scope batch sebelumnya.
- Pagination untuk laporan PDF — laporan sengaja mengambil seluruh data.

## Delivery Milestones
<!-- Business outcomes, not engineering tasks. /plan turns each into a plan. -->
<!-- Status: pending | in-progress | complete -->

| # | Milestone | Outcome | Status | Plan |
|---|---|---|---|---|
| 1 | Status verifikasi menggerakkan sistem | Scanner menolak jadwal non-`disetujui` (403) dan jadwal tersebut hilang dari jadwal mendatang mahasiswa | complete | `.claude/plans/penyempurnaan-alur-ukm-dinamis.plan.md` |
| 2 | Antrian verifikasi Pembina bersih | Jadwal `draft` tidak muncul di halaman verifikasi; penyetujuan atas `draft` ditolak server-side | complete | `.claude/plans/penyempurnaan-alur-ukm-dinamis.plan.md` |
| 3 | Jalan keluar operasional tersedia | Admin dapat mengaktifkan kembali anggota (per-user & semua) saat UKM aktif; Pelatih/Admin dapat menghapus jadwal `draft` | complete | `.claude/plans/penyempurnaan-alur-ukm-dinamis.plan.md` |
| 4 | Informasi lengkap terlihat | Catatan penolakan Pembina terbaca Pelatih/Admin; mahasiswa melihat lokasi & jam mulai–selesai; dropdown mahasiswa mendukung pencarian | complete | `.claude/plans/penyempurnaan-alur-ukm-dinamis.plan.md` |
| 5 | Seluruh tabel UKM terpaginasi | Semua tabel modul UKM menampilkan 20 data per halaman dengan navigasi halaman yang saling independen | complete | `.claude/plans/penyempurnaan-alur-ukm-dinamis.plan.md` |

## Open Questions
- [ ] Apakah jadwal berstatus `ditolak` boleh dihapus juga oleh Pelatih (selain `draft`)? — Assumption saat ini: **tidak**, hanya `draft` yang boleh dihapus sesuai permintaan eksplisit user; jadwal ditolak dipertahankan sebagai jejak audit.
- [ ] Setelah jadwal ditolak, apakah Pelatih perlu jalan untuk mengajukan ulang (`ditolak` → `menunggu`) setelah memperbaiki? — TBD; tidak diminta di batch ini, tapi berpotensi menjadi jalan buntu berikutnya jika jadwal ditolak tidak bisa diapa-apakan sama sekali.
- [ ] Apakah presensi yang sudah terlanjur terkumpul pada jadwal non-`disetujui` sebelum perbaikan ini perlu dibersihkan? — TBD, perlu query audit pasca-deploy.

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Menegakkan guard scanner secara server-side dapat memutus alur kerja Pelatih yang selama ini terbiasa scan tanpa menunggu persetujuan Pembina | Sedang | Sedang | Pesan error eksplisit menyebutkan status jadwal saat ini dan langkah yang diperlukan; sosialisasikan perubahan alur ke Pelatih sebelum deploy |
| Data presensi historis pada jadwal non-`disetujui` menjadi tidak konsisten dengan aturan baru | Sedang | Sedang | Perbaikan hanya mencegah presensi *baru*; jalankan query audit pasca-deploy untuk mengukur skala data lama (lihat Open Questions) |
| Reaktivasi massal ("Aktifkan Semua") dapat mengaktifkan anggota yang sengaja dinonaktifkan sebelum UKM dinonaktifkan — sistem tidak membedakan keduanya | Sedang | Sedang | Konfirmasi eksplisit sebelum eksekusi + sediakan opsi aktif per-user sebagai alternatif presisi |
| Menambah pagination pada relasi yang selama ini di-eager-load dapat mengubah perilaku view lain yang mengasumsikan koleksi penuh | Rendah | Sedang | Jalankan `php artisan test` penuh; review manual seluruh view yang mengonsumsi relasi `members`/`jadwals` termasuk laporan PDF |
| Dua paginator pada satu halaman berpotensi saling mereset jika nama parameter halaman tidak dipisahkan | Rendah | Rendah | Keputusan user sudah menetapkan nama parameter terpisah per tabel; verifikasi via feature test navigasi halaman |

---
*Status: DRAFT — requirements only. Implementation planning pending via /plan.*
