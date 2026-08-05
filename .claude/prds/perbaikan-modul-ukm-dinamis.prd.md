# Perbaikan Modul UKM Dinamis

## Problem
Modul UKM Dinamis (Epic 01) sudah berjalan di produksi, tetapi ditemukan 6 cacat fungsional yang mengganggu tiga peran pengguna: Admin, Pelatih, dan khususnya Pembina. Pembina — peran yang bertanggung jawab memverifikasi jadwal kegiatan UKM — tidak dapat mengakses halaman profilnya sendiri maupun logout dari sistem, yang merupakan cacat aksesibilitas dasar. Admin kehilangan visibilitas jadwal UKM dalam bentuk kalender, tidak bisa menghapus UKM yang sudah tidak relevan, dan status keanggotaan tidak konsisten saat sebuah UKM dinonaktifkan. Proses penambahan staf UKM juga rawan salah pilih karena dropdown user tidak difilter sesuai peran dan tidak mendukung pencarian.

## Evidence
- Laporan langsung dari pengguna (product owner) setelah UKM Dinamis dipakai di lingkungan nyata: "Role pembina tidak dapat membuka halaman profil-admin" dan "Pembina dibikin bisa logout karena saat ini pembina tidak bisa logout" — bug aksesibilitas kritis yang mengunci Pembina dari akun mereka sendiri.
- Investigasi kode mengonfirmasi root cause tiap isu (lihat referensi file pada bagian Delivery Milestones), termasuk kondisi exclude eksplisit `role_id == 1 || 2 || 4` (tanpa 5/Pembina) pada `headnav.blade.php` dan `ProfileController.php` — bukti langsung bahwa Pembina sengaja/tidak sengaja dikecualikan saat role ini ditambahkan ke sistem.
- `UkmController::destroy()` dan cascade delete pada migrasi sudah terimplementasi di backend tapi tidak ada tombol UI — bukti fitur "setengah jadi" yang perlu diselesaikan, bukan dibangun dari nol.

## Users
- **Primary**: Pembina UKM (role_id 5) — staf pembina yang perlu mengelola profil, memverifikasi jadwal kegiatan, dan logout dengan aman dari sistem.
- **Primary**: Admin (role_id 1) — mengelola siklus hidup UKM (aktivasi/nonaktivasi/hapus) dan keanggotaan staf/anggota.
- **Secondary**: Pelatih (role_id 4) — mengalami masalah serupa pada form tambah anggota saat memilih staf.
- **Not for**: Mahasiswa (role_id 3) — tidak terdampak langsung oleh keenam isu ini.

## Hypothesis
Kami percaya **perbaikan akses Pembina (profil & logout), penambahan tampilan kalender jadwal UKM, penyempurnaan form staf dengan search-dropdown terfilter peran, cascade status keanggotaan saat UKM dinonaktifkan, dan fitur hapus UKM permanen** akan **menghilangkan hambatan operasional harian Admin/Pelatih/Pembina dalam mengelola UKM Dinamis** untuk **seluruh staf pengelola UKM**.
Kami akan tahu ini berhasil ketika **Pembina dapat login, mengelola profil, dan logout tanpa kendala; Admin dapat melihat jadwal UKM dalam kalender, menghapus UKM nonaktif, dan status anggota otomatis konsisten dengan status UKM; serta seluruh test suite (`php artisan test`) tetap hijau 100%**.

## Success Metrics
| Metric | Target | How measured |
|---|---|---|
| Akses Pembina ke profil & logout | 100% berhasil, 0 redirect gagal/tombol hilang | Manual QA + feature test per role |
| Tab kalender jadwal UKM menampilkan event | Seluruh jadwal `ukm_jadwals` UKM terkait tampil di kalender | Manual QA + feature test |
| Konsistensi status anggota vs status UKM | 0 anggota UKM nonaktif yang masih berstatus 'aktif' | Query DB pasca-deactivate + feature test |
| Regresi test suite | 0 test gagal | `php artisan test` full run |
| Cakupan test baru untuk 6 fix | ≥ 80% baris kode berubah tercakup test | `php artisan test --coverage` (jika tersedia) atau review manual assertion |

## Scope
**MVP** — Perbaiki keenam isu sebagai bug-fix terpisah namun dikirim dalam satu batch: (1) render kalender jadwal UKM di halaman detail, (2) Pembina dapat membuka profil-admin, (3) Pembina dapat logout, (4) search-dropdown user terfilter peran pada form tambah anggota/staf (via Choices.js), (5) cascade `ukm_members.status` → 'nonaktif' saat UKM dinonaktifkan (tanpa auto-reaktivasi saat UKM diaktifkan kembali — admin kelola manual), (6) tombol hapus UKM permanen yang hanya aktif saat UKM berstatus nonaktif, dengan cascade delete existing dan konfirmasi sebelum eksekusi.

**Out of scope**
- Migrasi library search-dropdown ke seluruh form lain di aplikasi (hanya diterapkan pada form tambah anggota/staf UKM) — dampak lebih luas didiskusikan terpisah jika hasil MVP ini baik.
- Fitur restore/undo setelah UKM dihapus permanen — tidak ada soft-delete di scope ini, karena migrasi saat ini belum punya kolom `deleted_at`.
- Auto-reaktivasi status anggota saat UKM diaktifkan kembali — sesuai keputusan, admin mengelola ulang secara manual.
- Perombakan role & permission system secara menyeluruh — fix ini hanya menambal kondisi role yang hilang (Pembina), bukan redesain sistem role.

## Delivery Milestones
<!-- Business outcomes, not engineering tasks. /plan turns each into a plan. -->
<!-- Status: pending | in-progress | complete -->

| # | Milestone | Outcome | Status | Plan |
|---|---|---|---|---|
| 1 | Akses Pembina dipulihkan (profil & logout) | Pembina bisa membuka `/profil-admin` dan logout dari sistem seperti role staf lainnya | complete | `.claude/plans/perbaikan-modul-ukm-dinamis.plan.md` |
| 2 | Kalender jadwal UKM tampil di halaman detail | Tab kalender pada detail UKM menampilkan seluruh `ukm_jadwals` terkait UKM tersebut | complete | `.claude/plans/perbaikan-modul-ukm-dinamis.plan.md` |
| 3 | Form tambah anggota/staf disempurnakan | Dropdown user terfilter sesuai peran (pelatih/pembina) dan mendukung pencarian (search-dropdown) | complete | `.claude/plans/perbaikan-modul-ukm-dinamis.plan.md` |
| 4 | Konsistensi status anggota saat UKM nonaktif | Menonaktifkan UKM otomatis meng-nonaktifkan seluruh `ukm_members` terkait | complete | `.claude/plans/perbaikan-modul-ukm-dinamis.plan.md` |
| 5 | Admin dapat menghapus UKM permanen | Tombol hapus UKM (aktif hanya saat UKM nonaktif) dengan konfirmasi dan cascade delete data terkait | complete | `.claude/plans/perbaikan-modul-ukm-dinamis.plan.md` |

## Open Questions
- [ ] Apakah perlu audit role_id lain (mis. Operator) terhadap kemungkinan exclusion serupa di file yang sama (`headnav.blade.php`, `ProfileController.php`) di luar 6 isu ini? — TBD, disarankan quick-scan saat implementasi milestone 1.
- [ ] Apakah tombol hapus UKM perlu dibatasi tambahan (misal hanya jika UKM tidak pernah punya histori jadwal) mengingat cascade delete akan menghapus seluruh `ukm_jadwals`/`ukm_presensis` terkait secara permanen? — Assumption saat ini: cukup syarat "UKM nonaktif" + konfirmasi tegas, sesuai keputusan user.

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Menambah Choices.js sebagai dependency baru bisa berdampak ke bundle size / build Vite | Rendah | Rendah | Muat library hanya pada halaman yang butuh (lazy/scoped), verifikasi build tetap sukses |
| Cascade delete UKM permanen menghapus histori presensi yang mungkin masih dibutuhkan untuk laporan/audit | Sedang | Tinggi | Syarat "hanya saat nonaktif" + konfirmasi eksplisit sudah mengurangi risiko; pertimbangkan ekspor laporan sebelum hapus (di luar scope MVP) |
| Perubahan cascade status anggota bisa mempengaruhi query/laporan lain yang mengasumsikan `ukm_members.status` selalu mencerminkan partisipasi aktif tanpa mempertimbangkan status UKM induk | Rendah | Sedang | Jalankan `php artisan test` penuh + review manual endpoint terkait laporan PDF & dashboard mahasiswa setelah fix milestone 4 |

---
*Status: DRAFT — requirements only. Implementation planning pending via /plan.*
