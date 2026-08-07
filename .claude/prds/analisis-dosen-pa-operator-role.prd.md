# PRD: Analisis & Penyesuaian Arsitektur Dosen PA Menggunakan Role Operator (`role_id = 2`)

## Problem
Pada modul perizinan Epic 03, penandatanganan alur "Mengetahui, Dosen Pembimbing Akademik" sebelumnya di-resolve melalui relasi `kelas.dosen_pa_id` -> `users.id`. Pengguna mempertanyakan apakah entitas `dosen_pa_id` di tabel `kelas` dapat disederhanakan/dihilangkan dan dialihkan sepenuhnya ke akun ber-role **Operator** (`role_id = 2`) yang bertindak sebagai Dosen PA, sehingga manajemen akun menjadi lebih mudah tanpa perlu memasangkan satu per satu Dosen PA ke tiap kelas.

## Evidence
- Kebingungan pengguna saat mengonfigurasi data kelas & Dosen PA pada pengujian browser.
- Pada skala operasional Polbangtan Malang, akun staf pengelola perizinan dan dosen pembimbing akademis dapat disatukan di bawah peran **Operator** (`role_id = 2`), yang sudah memiliki akses navigasi ke menu staf (`/admin/*`).
- Spesifikasi ADR-007 membolehkan strategi fallback jika data Dosen PA spesifik kelas belum terisi.

## Users
- **Mahasiswa**: Mengajukan izin dan membutuhkan alur persetujuan Dosen PA yang otomatis terhubung ke staf/operator terkait.
- **Staf / Dosen PA (Role Operator)**: Mengakses inbox `/admin/izin/inbox` dengan akun Operator (`role_id = 2`) untuk menyetujui/menolak pengajuan mahasiswa bimbingan.
- **Admin System**: Memerlukan konfigurasi persetujuan yang fleksibel tanpa kerumitan pengisian data awal yang masif.

## Hypothesis
Kami meyakini bahwa **mendukung persetujuan Dosen PA oleh akun ber-role Operator (`role_id = 2`) — baik secara langsung via `ApproverResolver` maupun melalui mekanisme fallback —** akan **menyederhanakan operasional manajemen akun Dosen PA bagi Admin** tanpa merusak integritas alur persetujuan generik Epic 03.
Kami tahu kami benar ketika akun Operator (`role_id = 2`) dapat menerima & memproses pengajuan Dosen PA di inbox persetujuan, dan seluruh 162+ test suite otomatis tetap PASS 100%.

## Analisis Komparatif Arsitektur (ADR-007 vs Proposal Role Operator)

| Dimensi | Pendekatan Relasional (`kelas.dosen_pa_id`) | Pendekatan Role Operator (`role_id = 2`) | Pendekatan Hybrid (Dukungan Dua-duanya - REKOMENDASI) |
|---|---|---|---|
| **Penentuan Approver** | Spesifik 1 Dosen PA per Kelas Mahasiswa | Seluruh akun Operator (`role_id = 2`) dapat menjadi kandidat Dosen PA | Mengutamakan `dosen_pa_id` spesifik jika terisi; jika kosong/opsional, otomatis fallback ke Operator / Kaprodi |
| **Kemudahan Setup** | Butuh pengisian ID Dosen PA di tiap Kelas | Zero setup (cukup punya akun Operator) | Zero setup di awal, bisa diperdetail per kelas kapan saja |
| **Presisi Inbox** | Sangat Tinggi (Hanya Dosen PA mahasiswa ybs) | Menengah (Semua Operator melihat inbox persetujuan) | Tinggi & Fleksibel |
| **Dampak Kode** | Menggunakan relasi `kelas.dosen_pa_id` | Resolver membaca `User::where('role_id', OPERATOR_ROLE_ID)` | Memperbarui `ApproverResolver::resolveDosenPa()` dengan fallback otomatis |

## Success Metrics
| Metric | Target | How measured |
|---|---|---|
| Otorisasi Inbox Operator | 100% Akses Inbox Izin | `role:admin,operator,pelatih,pembina` middleware check |
| Resolusi Dosen PA | Resolusi berhasil ke User Operator | Unit test `ApproverResolverTest` & Feature Test `IzinM1Test` |
| Test Suite Regression | 100% Pass (162+ tests) | `php artisan test` |

## Scope
**MVP**
1. Menyesuaikan `ApproverResolver::resolveDosenPa()` agar jika `kelas.dosen_pa_id` tidak diisi (null), secara otomatis melakukan fallback menyelesaikan ke akun staf ber-role Operator (`role_id = 2`) / Pejabat Kaprodi.
2. Memastikan akun dengan `role_id = 2` (Operator) memiliki otorisasi penuh membaca dan menyetujui langkah Dosen PA pada inbox `/admin/izin/inbox`.
3. Meng-update dokumentasi teknis dan panduan pengujian browser.

**Out of scope**
- Menghapus kolom `dosen_pa_id` dari skema migrasi (dibiarkan opsional `nullable()` agar proyek tetap mendukung penunjukan spesifik per kelas di masa mendatang jika diperlukan).

## Delivery Milestones
| # | Milestone | Outcome | Status | Plan |
|---|---|---|---|---|
| 1 | Penyesuaian `ApproverResolver` & Strategy Fallback Operator | `resolveDosenPa()` otomatis mendukung akun Operator | pending | `.claude/plans/analisis-dosen-pa-operator-role.plan.md` |
| 2 | Verifikasi Feature Test & UI Inbox | Feature tests PASS 100% dan pengujian inbox role Operator verified | pending | `.claude/plans/analisis-dosen-pa-operator-role.plan.md` |

## Open Questions
- [x] *Apakah akun Operator (`role_id = 2`) sudah memiliki hak akses ke `/admin/izin/inbox`?* Ya, route `/admin/izin/*` menggunakan middleware `role:admin,operator,pelatih,pembina`.

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Ambuitas persetujuan jika banyak Operator | Low | Medium | Menggunakan mode `any` (salah satu Operator yang menyetujui duluan akan memproses langkah tersebut secara atomic dengan `lockForUpdate`) |

---
*Status: READY FOR IMPLEMENTATION*
