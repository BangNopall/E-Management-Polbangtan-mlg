# Plan: Analisis & Penyesuaian Arsitektur Dosen PA Menggunakan Role Operator (`role_id = 2`)

**Source PRD**: `.claude/prds/analisis-dosen-pa-operator-role.prd.md`
**Selected Milestone**: 1 & 2 — Penyesuaian `ApproverResolver` Strategy & Verifikasi Inbox Role Operator
**Complexity**: Small

## Summary
Melakukan analisis dan penyesuaian arsitektur pada `ApproverResolver` agar penandatanganan alur **Dosen PA** mendukung akun ber-role **Operator** (`role_id = 2`). Jika kolom `kelas.dosen_pa_id` kosong (null), `ApproverResolver::resolveDosenPa()` secara otomatis melakukan fallback menyelesaikan ke akun staf ber-role Operator (`role_id = 2`) atau Pejabat Kaprodi tanpa mematikan fleksibilitas penunjuk spesifik per kelas.

## Patterns to Mirror
| Category | Source | Pattern |
|---|---|---|
| Resolver Strategy | `app/Services/Izin/ApproverResolver.php:134-144` | Match expression & Collection strategy resolution |
| Role Constants | `app/Models/User.php:21` | `User::OPERATOR_ROLE_ID` = 2 |
| Unit Test | `tests/Unit/Services/ApproverResolverTest.php` | Resolution assertion on candidates collection |

## Files to Change
| File | Action | Why |
|---|---|---|
| `app/Services/Izin/ApproverResolver.php` | UPDATE | Perbarui `resolveDosenPa()` untuk mendukung fallback otomatis ke akun Operator (`role_id = 2`) jika `kelas.dosen_pa_id` null |
| `tests/Unit/Services/ApproverResolverTest.php` | UPDATE | Tambahkan unit test untuk skenario Dosen PA terisi vs null (fallback ke Operator) |
| `.claude/prds/analisis-dosen-pa-operator-role.prd.md` | UPDATE | Perbarui status milestone menjadi completed |

## Tasks
### Task 1: Perbarui `ApproverResolver::resolveDosenPa()` dengan Fallback Role Operator
- **Action**:
  - Pada `resolveDosenPa(?User $student)`, jika `$student->kelas->dosenPa` ada, kembalikan user Dosen PA tersebut.
  - Jika tidak ada (`kelas.dosen_pa_id` is null), cari kandidat user ber-role Operator (`User::where('role_id', User::OPERATOR_ROLE_ID)->get()`).
- **Mirror**: `app/Services/Izin/ApproverResolver.php:134-144`
- **Validate**: `php artisan test --filter=ApproverResolverTest`

### Task 2: Verifikasi & Tambah Unit Test pada `ApproverResolverTest`
- **Action**:
  - Buat/update unit test `test_resolve_dosen_pa_mengembalikan_user_operator_jika_dosen_pa_id_null()`.
- **Mirror**: `tests/Unit/Services/ApproverResolverTest.php`
- **Validate**: `php artisan test --filter=ApproverResolverTest`

### Task 3: Verifikasi Suite Test Perizinan & Update PRD
- **Action**:
  - Jalankan `php artisan test` untuk memastikan 100% test suite PASS tanpa regresi.
  - Tandai milestone pada PRD sebagai completed.
- **Validate**: PASS 100%.

## Validation
```bash
php artisan test --filter=ApproverResolverTest
php artisan test
```

## Risks
| Risk | Likelihood | Mitigation |
|---|---|---|
| Banyak akun Operator muncul di inbox | Low | Mode `any` pada `IzinApproval` memastikan persetujuan sifatnya atomic (`lockForUpdate`), siapa yang memproses duluan yang memverifikasi |
