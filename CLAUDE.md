# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

**E-Management Polbangtan-mlg** is a Laravel-based dormitory management system for Politeknik Pembangunan Pertanian Malang. It handles student dormitory attendance via QR code scanning, mandatory activity tracking (Apel, Senam, Upacara), disciplinary violation management, and staff duty scheduling.

## Commands

### Development

```bash
# Start the Vite dev server (watches resources/js and resources/css)
npm run dev

# Build production assets
npm run build

# Start Laravel local server
php artisan serve
```

### Database

```bash
# Run all migrations
php artisan migrate

# Fresh migration with seeds (resets entire DB)
php artisan migrate:fresh --seed

# Run seeders only (roles, prodi, kelas, blok, pelanggaran categories)
php artisan db:seed
```

### Testing

```bash
# Run all tests
php artisan test

# Run a single test file
php artisan test tests/Feature/ExampleTest.php

# Run with filter
php artisan test --filter ExampleTest
```

### Cache & Maintenance

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan cache:clear
```

## Architecture

### Role System

Roles are stored in the `roles` table and referenced via `users.role_id`. Five roles exist with constants defined on the `User` model:

| Constant | `role_id` | Name | Access |
|---|---|---|---|
| `ADMIN_ROLE_ID` | 1 | admin | Full access, reports, system settings |
| `OPERATOR_ROLE_ID` | 2 | operator | Scanner, pelanggaran, jadwal |
| `USER_ROLE_ID` | 3 | user | Student dashboard, own QR, own history, UKM Saya |
| `PELATIH_ROLE_ID` | 4 | pelatih | Scanner, pelanggaran, UKM schedule & scanner |
| `PEMBINA_ROLE_ID` | 5 | pembina | UKM schedule verification & reports |

Access control is enforced by `EnsureUserHasRole` middleware, registered as the `role` alias. Routes use `middleware('role:admin')` or `middleware('role:admin,operator,pelatih,pembina')` for multi-role groups. On failure it redirects (not 403) to the appropriate dashboard based on the user's actual role.

### Three Parallel Attendance Systems

**System 1 — Gate Attendance (`Attendance` + `Presence`)**
Tracks students leaving/entering the dormitory. The `Attendance` table holds a daily window (`start_time`/`end_time`, default 06:00–22:00). `Presence` records each scan event with `presence_masuk`, `presence_keluar`, `log_status` (`didalam`/`diluar`/`telat`), and `is_active`. `User.status` is also updated on each scan as a live cache of the student's current location state.

**System 2 — Mandatory Activity Attendance (`PresensiApel`, `PresensiUpacara`, `PresensiSenam`)**
Three separate tables, one per activity type. Keyed by `jadwalKegiatanAsrama_id` (from `jadwal_kegiatan_asramas`) and `user_id`. Activities are scheduled per blok (dormitory block) and have a `status_kehadiran` field.

**System 3 — Dynamic Student Activity Club Attendance (`ukms`, `ukm_members`, `ukm_jadwals`, `ukm_presensis`)**
Modul UKM Dinamis manages dynamic student clubs. Any number of UKMs can be created without schema changes. Schedule creation auto-fans out `Alpha` presensi rows for active student members. QR scanning by staff (`UkmScanController`) updates status to `Hadir`. Schedules require Pembina approval workflow (`draft` -> `menunggu` -> `disetujui`/`ditolak`). Per-UKM staff membership scoping is enforced across all management endpoints to prevent cross-UKM IDOR. PDF reports use `barryvdh/laravel-dompdf`.

The two systems are completely independent — gate scans use `QRController::presense()`, activity scans use `QRControllerKegiatan`.

### QR Code Flow

1. Student visits `/dashboard/kode-qr` → `QRController::kodeqr()` generates a QR containing a JSON payload: `{user_id, date, time, status, scanner}`.
2. Admin/operator opens `/kamera-scan` (gate) or `/kamera-upacara|apel|senam` (activity cameras).
3. Camera page (using `html5-qrcode`) decodes the QR and POSTs the JSON to `/presense/api` or the respective activity API endpoint.
4. The controller validates that the scan time is within the allowed window of the QR generation time, then creates or updates the appropriate presence record.

### Disciplinary Violations (`Pelanggaran`)

Students self-report via `/dashboard/qr-hukum` → `QRControllerHukum`. Staff can also record violations via `/kamera-pelatih`. Violations are categorised via `KategoriPelanggaran` → `JenisPelanggaran` (two-level hierarchy). Each `Pelanggaran` record goes through a status workflow: pending → confirmed / rejected / done. Point deductions are tracked on `users.point`.

### Key Models and Relationships

```
User
 ├── belongsTo Role
 ├── belongsTo Kelas (academic class)
 ├── belongsTo blokRuangan (dormitory block)
 ├── belongsTo prodi (study programme)
 ├── hasMany Presence (gate logs)
 └── hasOne LoginPermission

jadwalKegiatanAsrama (activity schedule)
 └── referenced by PresensiApel / PresensiSenam / PresensiUpacara

KategoriPelanggaran
 └── hasMany JenisPelanggaran
      └── hasMany Pelanggaran → belongsTo User
```

### Frontend Stack

- **Tailwind CSS v4** (via `@tailwindcss/postcss`) + **Flowbite** for UI components
- **Alpine.js** for lightweight reactivity
- **FullCalendar** for duty/activity schedule calendar views
- **html5-qrcode** for browser-based QR scanning
- Assets compiled by **Vite** (`npm run dev` / `npm run build`)
- The single shared layout is `resources/views/layouts/main.blade.php` — all authenticated views extend it via `@yield('container')`
- Views are split into `resources/views/admin/` (staff) and root-level views (students), with reusable fragments in `resources/views/partials/`

### Reports & Exports

- PDF reports use **barryvdh/laravel-dompdf** — rendered via dedicated Blade templates in `resources/views/admin/generate/`
- Excel exports use **maatwebsite/excel** via the `FromView` concern (renders a Blade view to XLSX)
- Excel import (`UsersImport`) bulk-creates student accounts with default password `"password"` and `role_id = 3`

### Naming Conventions in Codebase

Model and controller naming is inconsistent — some use camelCase (`blokRuangan`, `prodi`, `kegiatanAsramaController`) and some PascalCase. Match the existing style when adding to an existing file. Route name prefixes are `home.` for students and `admin.` for staff (even though operator/pelatih routes also use the `admin.` prefix).

## Environment Setup

Copy `.env.example` to `.env`, generate an app key, and configure the MySQL connection. The session lifetime is set to 525,600 minutes (1 year) intentionally.

```bash
cp .env.example .env
php artisan key:generate
```

Required: MySQL database. No Redis, mail, or external services are needed for local development.

## Known Issues & Compatibility Notes

### simplesoftwareio/simple-qrcode — PHP 8.4 Deprecation Warnings

`simplesoftwareio/simple-qrcode ^4.2` (and its dependency `bacon/bacon-qr-code`) produce PHP 8.4
deprecation notices about implicit nullable parameters. These are warnings only in PHP 8.4 and do
**not** affect runtime behaviour, but they will become **fatal errors in PHP 8.5**.

Affected lines (as of July 2026):
- `vendor/simplesoftwareio/simple-qrcode/src/Generator.php:169`
- `vendor/bacon/bacon-qr-code/src/Encoder/Encoder.php:158`
- `vendor/bacon/bacon-qr-code/src/Common/ReedSolomonCodec.php:225`

**Action required before PHP 8.5 upgrade:** Check for a patched release of
`simplesoftwareio/simple-qrcode` or consider an alternative QR library.

### Cache Key Prefix Change (Laravel 12 → 13)

Laravel 13 changed the default cache key prefix suffix from underscore-separated (`_cache_`) to
hyphen-separated (`-cache-`). On first deploy after upgrade, existing cached data under the old
prefix will be unreachable — effectively a cold cache. This causes a temporary performance
degradation but no data loss. Warm the cache after deploy if needed.

## Seeder Order

The `DatabaseSeeder` must seed in dependency order (roles before users, kelas/prodi/blok before students). Existing seeders cover: `RoleSeeder`, `ProdiSeeder`, `KelasSeeder`, `BlokRuanganSeeder`, `KategoriPelanggaranSeeder`, `JenisPelanggaranSeeder`.


## Epic 03 — Modul Workflow Sistem Perizinan (SEDANG DIKERJAKAN)

### Dokumen spesifikasi — WAJIB dibaca sebelum menulis kode Epic 03

Urutan baca: `docs/design/DESIGN-Epic03-SystemFlow-Perizinan.md` (alur) →
`docs/design/DESIGN-Epic03-Modul-Perizinan.md` (skema, ADR, keputusan) →
`docs/design/DESIGN-Epic03-Frontend-Perizinan.md` (view, hanya saat kerja UI).

**Dokumen desain adalah spesifikasi, bukan saran.** Jika sebuah instruksi bertentangan
dengan dokumen desain, berhenti dan tanyakan — jangan diam-diam menyimpang. Jika Anda
menemukan alasan kuat bahwa desainnya salah, katakan alasannya dan tunggu keputusan
sebelum mengubah arah.

### Aturan yang tidak boleh dilanggar (hard rules)

1. **`app/Http/Controllers/QRController.php` adalah kode produksi harian.**
   Satu-satunya perubahan yang diizinkan adalah **menyisipkan satu blok `if`** sebelum
   logika penentuan `telat` yang sudah ada (lihat §4 SystemFlow). Dilarang: mengubah
   urutan percabangan existing, mengganti nama variabel existing, "merapikan" kode,
   mengekstrak method, atau memformat ulang berkas. Diff pada berkas ini harus bisa
   dibaca dalam 30 detik.

2. **Enum `izin` / `Izin` sudah ada di skema.** Dilarang membuat migrasi yang mengubah
   enum pada `users`, `presences`, `presensi_apels`, `presensi_senams`,
   `presensi_upacaras`, atau `ukm_presensis`. Nilainya sudah tersedia — pakai.

3. **Semua transisi `pengajuan_izins.status` hanya lewat `PengajuanIzinService`.**
   Dilarang menulis `$izin->update(['status' => ...])` di controller, job, atau
   observer mana pun.

4. **Dilarang membuat percabangan berdasarkan jenis izin di kode.**
   Tidak ada `if ($jenis === 'IB')`, `switch ($izin->jenis_izin->kode)`, atau sejenisnya
   di controller/service/Blade. Perbedaan perilaku antar jenis izin dibaca dari kolom
   `jenis_izins` dan `izin_workflow_steps`. Ini inti ADR-006.

5. **Dilarang menambah role baru ke tabel `roles` atau mengubah `users.role_id`.**
   Penandatangan di-resolve lewat `pejabats` + `ApproverResolver` (ADR-007).

6. **Dilarang menulis HMAC/kriptografi sendiri.** Verifikasi surat memakai
   `URL::signedRoute` + middleware `signed` bawaan Laravel (ADR-008). Pola HMAC di
   `KonselingTicketService` khusus untuk lintas-aplikasi ke E-Klinik — jangan disalin.

7. **Rantai persetujuan di-resolve dan dibekukan saat submit**, bukan saat approval.
   Pengecualian tunggal: langkah ber-`resolve_saat = 'langkah_aktif'`.

8. **Otorisasi persetujuan berbasis kepemilikan langkah, bukan role.** Setiap endpoint
   persetujuan wajib memuat tiga `abort` pengaman sesuai §2.3 SystemFlow.

9. **Jangan pernah menjalankan `php artisan migrate:fresh` atau `db:seed --force`**
   pada database mana pun selain sqlite in-memory milik test.

### Konvensi yang harus diikuti (dari kode existing)

- Model: `protected $guarded = ['id']` (ikuti `Ukm`, `UkmJadwal`), bukan `$fillable`.
- Migrasi: `foreignId()->constrained()`, indeks eksplisit, penamaan `2026_08_xx_xxxxxx_*`.
- Route: prefix nama `home.` untuk mahasiswa, `admin.` untuk staf.
- Otorisasi halaman: middleware `role:` yang sudah ada; scoping data di dalam controller
  dengan `abort_unless` (ikuti pola `UkmVerifikasiController::index()`).
- View: `@extends('layouts.main')`, kartu `bg-white border-2 rounded-lg p-3`,
  `@include('partials.alert')`, form tambah pakai accordion Flowbite.
- Test: pola `tests/Feature/Ukm/*`.
- PDF: `barryvdh/laravel-dompdf` + Blade di `resources/views/admin/generate/`.

### Status milestone

- [x] M0 — `pejabats`, `kelas.dosen_pa_id`, UI Data Pejabat
- [x] M1 — 5 tabel, model, `ApproverResolver`, `PengajuanIzinService`, seeder
- [ ] M2 — pengajuan mahasiswa, inbox approver, setujui/tolak
- [ ] M3 — nomor surat, PDF, QR, verifikasi publik
- [ ] M4 — integrasi gerbang, pembebasan presensi, pelanggaran keterlambatan
- [ ] M5 — konfirmasi tiba, monitor, notifikasi, job terjadwal
- [ ] M6 — CRUD jenis izin, laporan, hardening

Perbarui centang di atas setiap kali satu milestone lolos gerbang kualitasnya.