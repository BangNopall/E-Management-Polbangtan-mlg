# PRD: Perbaikan Frontend Alpine.js Workflow Steps, Options Pengajuan Izin, & Kejelasan Role Dosen PA

## Problem
1. **Frontend Alpine.js Inactive**: Berkas `resources/js/app.js` belum mengimpor dan menjalankan `Alpine.start()`. Akibatnya, elemen reactive Alpine (`x-data`, `x-for`, `@click`) di halaman admin (Kelola Jenis Izin) dan mahasiswa (Form Pengajuan Izin) tidak berjalan sama sekali.
2. **Kelola Jenis Izin**: Admin tidak dapat melihat daftar langkah (`steps`) maupun menekan tombol "Tambah Langkah" karena template Alpine `x-for` dan method `addStep()` tidak dieksekusi.
3. **Form Pengajuan Mahasiswa**: Opsi dropdown Jenis Perizinan (`<option x-for="...">`) dan Pratinjau Alur Persetujuan tidak muncul karena Alpine.js mati.
4. **Kebingungan Role Dosen PA**: Pengguna membutuhkan penjelasan dan kepastian teknis mengenai akun dan `role_id` yang digunakan oleh Dosen Pembimbing Akademik (Dosen PA) untuk memproses persetujuan izin di inbox.

## Evidence
- `resources/js/app.js` memuat `axios`, `jquery`, `popper`, `choices.js`, dan `flowbite`, tetapi belum memuat `import Alpine from 'alpinejs'; window.Alpine = Alpine; Alpine.start();`.
- Dosen PA di-resolve lewat relasi `kelas.dosen_pa_id` -> `users.id` yang diizinkan mengakses `/admin/izin/inbox` melalui middleware `role:admin,operator,pelatih,pembina`.

## Users
- **Admin System**: Mengelola Jenis Izin dan menyusun urutan alur persetujuan (Workflow Steps).
- **Mahasiswa**: Memilih Jenis Izin dan melihat pratinjau alur persetujuan secara real-time.
- **Dosen PA / Staf**: Mengakses Inbox Persetujuan (`/admin/izin/inbox`) untuk menyetujui/menolak pengajuan mahasiswa bimbingannya.

## Hypothesis
Kami meyakini bahwa **menginisialisasi Alpine.js secara global pada `resources/js/app.js` dan membangun ulang aset Vite (`npm run build`)** akan **memulihkan 100% interaktivitas komponen frontend** pada Kelola Jenis Izin dan Form Pengajuan Mahasiswa.
Kami tahu kami benar ketika tombol Tambah Langkah dapat diklik, opsi Jenis Izin muncul di dropdown mahasiswa, pratinjau alur merender kandidat approver, dan seluruh 162 pengujian otomatis tetap PASS 100%.

## Success Metrics
| Metric | Target | How measured |
|---|---|---|
| Inisialisasi Alpine.js | Active (window.Alpine ready) | Browser Console / Vite build output |
| Form Jenis Izin | Step `x-for` & `addStep()` bekerja | Manual browser / UI render |
| Form Pengajuan Izin | Opsi dropdown & pratinjau alur merender | Manual browser / UI render |
| Test Suite Regression | 100% Pass (162 tests) | `php artisan test` |

## Scope
**MVP**
1. Mengimpor dan menginisialisasi Alpine.js pada `resources/js/app.js` (`import Alpine from 'alpinejs'; window.Alpine = Alpine; Alpine.start();`).
2. Menjalankan kompilasi Vite (`npm run build`).
3. Memberikan penjelasan dokumentasi teknis yang tegas mengenai arsitektur Akun & Role Dosen PA pada sistem E-Management.

**Out of scope**
- Merubah struktur database atau logika backend `PengajuanIzinService` & `ApproverResolver` (logika backend sudah 100% solid & teruji).

## Delivery Milestones
| # | Milestone | Outcome | Status | Plan |
|---|---|---|---|---|
| 1 | Integrasi Alpine.js Bundler | Alpine.js diimpor dan di-start secara global di app.js | in-progress | `.claude/plans/perbaikan-ui-workflow-dan-dosen-pa.plan.md` |
| 2 | Verifikasi Frontend & Dokumentasi Dosen PA | Form Jenis Izin, Pengajuan Mahasiswa, & Dokumen Role Dosen PA selesai | pending | `.claude/plans/perbaikan-ui-workflow-dan-dosen-pa.plan.md` |

## Open Questions
- Tidak ada. Penyebab masalah frontend sudah teridentifikasi 100% pada bundler `resources/js/app.js`.

---
*Status: READY FOR IMPLEMENTATION*
