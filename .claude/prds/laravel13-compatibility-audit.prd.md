# Laravel 13 Full Compatibility Audit — E-Management Polbangtan-mlg

## Problem

Project E-Management Polbangtan-mlg telah diupgrade ke Laravel 13 (v13.19.0) dari Laravel 12.
Meskipun `composer update` berhasil dan test suite (2 test) hijau, belum ada validasi runtime
bahwa semua fitur core — QR scanning, absensi, PDF/Excel export, dan role-based auth — berjalan
tanpa breaking change di Laravel 13. Jika isu tidak ditemukan sebelum deploy, production bisa
mengalami runtime error yang berdampak langsung pada pencatatan kehadiran mahasiswa dan staff.

## Evidence

- Assumption — belum ada isu yang diobservasi di runtime. Audit ini proaktif sebelum deployment.
- Diketahui dari eksplorasi kode: 2 model memiliki misconfiguration — `Kelas.php` menggunakan
  `$protected` (typo, harusnya `$guarded`) dan `Presence.php` menggunakan `$guarded = ['']`
  (empty string) — yang bisa menyebabkan mass assignment vulnerability atau silent failure.
- Laravel 13 mengganti `VerifyCsrfToken` → `PreventRequestForgery` (sudah diupdate) dan mengubah
  cache key prefix (underscore → hyphen) yang bisa invalidate cache di production.
- `maatwebsite/excel ^3.1` **verified OK** via `php artisan tinker` — runtime compatible dengan L13.
- `barryvdh/laravel-dompdf ^3.0` **verified OK** via `php artisan tinker` — runtime compatible.
- `simplesoftwareio/simple-qrcode ^4.2` **berjalan tapi** menghasilkan 3 PHP 8.4 deprecation
  notices (implicit nullable params) — tidak breaking sekarang, tapi akan fatal di PHP 8.5.

## Users

- **Primary**: Developer/tim teknis yang bertanggung jawab deploy ke production server Polbangtan Malang
- **Secondary**: Institusi Polbangtan Malang — terpengaruh jika sistem absensi dan pelanggaran
  tidak berjalan setelah upgrade
- **Not for**: End user mahasiswa/staff (tidak berinteraksi langsung dengan proses audit)

## Hypothesis

We believe **melakukan audit runtime dan memperbaiki semua breaking/critical compatibility issues**
will **memastikan seluruh fitur QR scanning, absensi, PDF/Excel, dan auth berjalan tanpa error**
for **tim developer dan institusi Polbangtan Malang**.
We'll know we're right when **semua fitur core dapat dijalankan tanpa PHP error atau 500 response
di environment development, dengan smoke test yang pass untuk tiap fitur critical**.

## Success Metrics

| Metric | Target | How measured |
|---|---|---|
| Runtime error pada fitur core | 0 error | Manual smoke test + `php artisan test` |
| Breaking deprecated patterns di kode aplikasi | 0 pattern | Static scan via mgrep |
| Model misconfiguration | 0 issue | Code review semua model |
| Third-party packages verified functional | 3/3 packages | Functional test per package |

## Scope

**MVP — Audit dan perbaikan breaking/critical issues saja:**

1. Identifikasi dan fix semua deprecated class/method yang digunakan langsung di kode aplikasi
2. Fix model misconfiguration (`$protected` typo di Kelas.php, `$guarded = ['']` di Presence.php)
3. Verifikasi runtime ketiga package critical: `maatwebsite/excel`, `barryvdh/laravel-dompdf`,
   `simplesoftwareio/simple-qrcode`
4. Smoke test 4 area core: QR scanning flow, attendance recording, PDF/Excel export, auth+role
5. Verifikasi dan dokumentasikan dampak cache key prefix change untuk deployment runbook

**Out of scope**

- Refactor besar: pisah business logic ke service classes — deferred, bukan breaking issue
- Penambahan type hints ke semua method — code quality, tidak blocking
- Rename model ke PascalCase (`prodi`, `blokRuangan`) — naming convention, tidak blocking
- Penambahan fitur baru
- Full test suite 80% coverage — audit hanya butuh smoke test happy path per fitur

## Delivery Milestones

| # | Milestone | Outcome | Status | Plan |
|---|---|---|---|---|
| 1 | Audit deprecated patterns | Daftar semua deprecated API usage di kode aplikasi | complete | `.claude/plans/laravel13-compatibility-audit.plan.md` |
| 2 | Fix model misconfiguration | `Kelas.php` dan `Presence.php` diperbaiki, mass assignment aman | complete | `.claude/plans/laravel13-compatibility-audit.plan.md` |
| 3 | Verifikasi third-party packages | `maatwebsite/excel`, `dompdf`, `simple-qrcode` confirmed working | complete | `.claude/plans/laravel13-compatibility-audit.plan.md` |
| 4 | Smoke test fitur core | QR scanning, absensi, PDF/Excel, auth — semua pass | complete | `tests/Feature/Laravel13SmokeTest.php` |
| 5 | Cache compatibility check | Dampak cache prefix change didokumentasikan di deployment runbook | complete | `CLAUDE.md` |

## Open Questions

- [ ] Apakah `maatwebsite/excel ^3.1` butuh update ke versi minor untuk L13?
      (Composer resolve berhasil tapi belum ditest functional)
- [ ] Cache di production menggunakan driver apa (file/redis/database)? Menentukan dampak
      cache key prefix change saat upgrade di production.
- [ ] Apakah `simplesoftwareio/simple-qrcode ^4.2` kompatibel dengan PHP 8.4?
      (Runtime di PHP 8.4.23, package mungkin hanya tested sampai PHP 8.3)
- [ ] Apakah ada staging environment sebelum production deploy?

## Risks

| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| `maatwebsite/excel` runtime error di L13 | Medium | High — laporan tidak bisa diexport | Functional test export sebelum deploy |
| Cache invalidation massal saat deploy | Low | Medium — performa degradasi sementara | Warm cache setelah deploy, dokumentasikan di runbook |
| `simple-qrcode` incompatible dengan PHP 8.4 | Low | Critical — QR adalah core feature | Verifikasi via `php artisan tinker` sebelum deploy |
| Model `Presence` mass assignment vulnerability | High | Medium — silent issue yang sudah ada | Fix `$guarded = ['']` sebelum deploy |

---

*Status: DRAFT — requirements only. Implementation planning pending via /plan.*
