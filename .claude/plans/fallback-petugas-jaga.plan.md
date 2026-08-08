# Plan: Fallback Petugas Jaga bila Jadwal Petugas Piket Kosong pada Form Pengajuan Izin Mahasiswa

**Source PRD**: `.claude/prds/fallback-petugas-jaga.prd.md`
**Selected Milestone**: 1 — Fallback `resolvePetugasJaga` & Unit Test Verification
**Complexity**: Small

## Summary
Menambahkan logika fallback otomatis pada `ApproverResolver::resolvePetugasJaga()` agar ketika data `JadwalPetugas` tidak ditemukan pada tanggal keberangkatan yang diminta, sistem secara otomatis mengambil akun ber-role **Pelatih (`role_id = 4`)** dan **Operator (`role_id = 2`)** sebagai kandidat penandatangan Petugas Jaga. Hal ini menghilangkan blocker `⚠️ Kandidat penandatangan tidak ditemukan di sistem!` pada pengajuan izin mahasiswa.

## Patterns to Mirror
| Category | Source | Pattern |
|---|---|---|
| Resolver Strategy | `app/Services/Izin/ApproverResolver.php:134-144` | Fallback Collection strategy |
| Role Constants | `app/Models/User.php:23-25` | `User::OPERATOR_ROLE_ID`, `User::PELATIH_ROLE_ID` |
| Feature Test | `tests/Feature/Izin/IzinResolverTest.php:164-188` | Assertion on resolved officers candidates |

## Files to Change
| File | Action | Why |
|---|---|---|
| `app/Services/Izin/ApproverResolver.php` | UPDATE | Perbarui `resolvePetugasJaga()` dengan fallback ke Pelatih & Operator jika `JadwalPetugas` null/empty |
| `tests/Feature/Izin/IzinResolverTest.php` | UPDATE | Tambahkan test `test_resolver_petugas_jaga_fallback_ke_role_pelatih_dan_operator_jika_jadwal_null()` |
| `.claude/prds/fallback-petugas-jaga.prd.md` | UPDATE | Tandai milestone sebagai completed |

## Tasks
### Task 1: Update `ApproverResolver::resolvePetugasJaga()` dengan Fallback Role Pelatih/Operator
- **Action**:
  - Pada `resolvePetugasJaga(?string $date)`, jika `$jadwal` tidak ditemukan atau `$officers` kosong:
    - Ambil user aktif ber-role `User::PELATIH_ROLE_ID` (4) atau `User::OPERATOR_ROLE_ID` (2).
- **Mirror**: `app/Services/Izin/ApproverResolver.php:134-144`
- **Validate**: `php artisan test --filter=IzinResolverTest`

### Task 2: Verifikasi & Tambah Feature Test pada `IzinResolverTest`
- **Action**:
  - Tambahkan unit/feature test `test_resolver_petugas_jaga_fallback_ke_role_pelatih_dan_operator_jika_jadwal_null()`.
- **Mirror**: `tests/Feature/Izin/IzinResolverTest.php:164-188`
- **Validate**: `php artisan test --filter=IzinResolverTest`

### Task 3: Verifikasi Suite Test Perizinan & Update PRD
- **Action**:
  - Jalankan `php artisan test` untuk memastikan 100% test suite PASS tanpa regresi.
  - Update status milestone pada PRD.
- **Validate**: PASS 100%.

## Validation
```bash
php artisan test --filter=IzinResolverTest
php artisan test
```

## Risks
| Risk | Likelihood | Impact | Mitigation |
|---|---|---|---|
| Mengabaikan jadwal piket jika terisi | Low | High | Pengecekan `$jadwal` dan `$officers->isNotEmpty()` tetap diutamakan terlebih dahulu sebelum mengeksekusi fallback |
