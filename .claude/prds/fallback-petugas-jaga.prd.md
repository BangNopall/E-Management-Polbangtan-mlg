# PRD: Fallback Petugas Jaga bila Jadwal Petugas Piket Kosong pada Form Pengajuan Izin Mahasiswa

## Problem
Saat mahasiswa mengajukan izin pada halaman `/dashboard/izin/create`, langkah alur "Petugas Piket Asrama / Pelatih Harian Asrama" di-resolve menggunakan strategi `petugas_jaga` berdasarkan tanggal keberangkatan (`waktu_berangkat`). Apabila pada tanggal tersebut admin belum meng-generate jadwal piket pada tabel `jadwal_petugas`, resolver mengembalikan 0 kandidat sehingga menampilkan peringatan `⚠️ Kandidat penandatangan tidak ditemukan di sistem!`. Hal ini menghambat pengajuan izin mahasiswa dan pengujian alur persetujuan.

## Evidence
- Peringatan `⚠️ Kandidat penandatangan tidak ditemukan di sistem!` ditemui oleh pengguna saat membuat pengajuan izin mahasiswa.
- Spesifikasi desain `DESIGN-Epic03-Modul-Perizinan.md` §8.2 secara eksplisit mengatur fallback berjenjang bila jadwal piket pada tanggal tertentu belum terisi: *"Saat langkah `petugas_jaga` terbuka tapi tidak ada jadwal piket untuk tanggal itu → fallback berjenjang: (1) siapa pun ber-role pelatih/operator yang aktif..."*

## Users
- **Mahasiswa**: Dapat membuat pengajuan izin dengan lancar tanpa terhalang tanggal keberangkatan yang jadwal piketnya belum sempat diisi oleh Admin.
- **Pelatih / Staf Operator / Admin**: Memiliki akun ber-role Pelatih (`role_id = 4`) atau Operator (`role_id = 2`) yang dapat menyetujui pengajuan izin mahasiswa sebagai Petugas Jaga saat jadwal piket belum dibuat.

## Hypothesis
Kami meyakini bahwa **menambahkan logika fallback otomatis pada `ApproverResolver::resolvePetugasJaga()` ke akun ber-role Pelatih (`role_id = 4`) atau Operator (`role_id = 2`) ketika `JadwalPetugas` pada tanggal keberangkatan tidak ditemukan** akan **menghilangkan blocker pengajuan mahasiswa** dan **menjamin alur persetujuan tetap berjalan**.
Kami tahu kami benar ketika pratinjau alur pada halaman `/dashboard/izin/create` selalu menampilkan kandidat penandatangan Petugas Jaga walau jadwal piket belum dibuat, dan 100% test suite perizinan tetap PASS.

## Success Metrics
| Metric | Target | How measured |
|---|---|---|
| Resolusi Petugas Jaga | 100% Berhasil Resolusi (No 0 Candidate Error) | `ApproverResolverTest` & Pratinjau Alur Mahasiswa |
| Fallback Precision | Mengutamakan Jadwal Piket jika ada; Fallback jika null | Unit Test `ApproverResolverTest` |
| Test Suite Regression | 100% Pass | `php artisan test` |

## Scope
**MVP**
1. Memperbarui `ApproverResolver::resolvePetugasJaga()` agar jika `JadwalPetugas` tidak ditemukan pada tanggal keberangkatan yang diminta, otomatis mengambil user aktif ber-role Pelatih (`role_id = 4`) atau Operator (`role_id = 2`).
2. Menambahkan unit test pada `IzinResolverTest` untuk memverifikasi perilaku fallback `resolvePetugasJaga()`.
3. Memastikan seluruh 59+ test suite perizinan tetap PASS.

**Out of scope**
- Mengubah struktur tabel `jadwal_petugas` atau alur CRUD jadwal piket admin.

## Delivery Milestones
| # | Milestone | Outcome | Status | Plan |
|---|---|---|---|---|
| 1 | Fallback `resolvePetugasJaga` & Unit Test Verification | `resolvePetugasJaga()` aman dan selalu mengembalikan kandidat valid | pending | `.claude/plans/fallback-petugas-jaga.plan.md` |

## Open Questions
- [x] *Apakah role Pelatih (`role_id = 4`) dan Operator (`role_id = 2`) sudah bisa mengakses `/admin/izin/inbox`?* Ya, middleware route persetujuan adalah `role:admin,operator,pelatih,pembina`.

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Banyak kandidat staf jika fallback aktif | Low | Low | Pengajuan menggunakan mode `any` (satu orang yang memproses duluan akan mengunci langkah tersebut) |

---
*Status: READY FOR IMPLEMENTATION*
