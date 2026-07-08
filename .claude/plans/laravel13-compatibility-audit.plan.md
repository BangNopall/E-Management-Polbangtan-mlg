# Plan: Laravel 13 Compatibility Audit — Milestone 1 & 2

**Source PRD**: `.claude/prds/laravel13-compatibility-audit.prd.md`
**Selected Milestone**: Milestone 1 (Audit deprecated patterns) + Milestone 2 (Fix model misconfiguration) — digabung karena temuan runtime check sudah mengkonfirmasi semua isu
**Complexity**: Small

## Summary

Runtime checks sudah dijalankan dan menghasilkan temuan konkret. `maatwebsite/excel` dan `barryvdh/laravel-dompdf` berjalan normal di Laravel 13. `simplesoftwareio/simple-qrcode` berjalan tapi menghasilkan 3 PHP 8.4 deprecation notices (akan breaking di PHP 8.5). Dua model memiliki misconfiguration yang perlu difix sebelum deploy. Plan ini menyelesaikan Milestone 1 + 2 sekaligus karena audit sudah selesai via runtime check.

## Temuan Runtime Check (sudah selesai)

| Package | Hasil | Detail |
|---|---|---|
| `maatwebsite/excel` | ✅ OK | Compatible dengan L13 |
| `barryvdh/laravel-dompdf` | ✅ OK | Compatible dengan L13 |
| `simplesoftwareio/simple-qrcode` | ⚠️ Deprecated warnings | 3 PHP 8.4 deprecation notices — berjalan tapi akan breaking di PHP 8.5 |
| `VerifyCsrfToken` → `PreventRequestForgery` | ✅ Sudah difix | Dilakukan di sesi upgrade |

## Patterns to Mirror

| Category | Source | Pattern |
|---|---|---|
| Model mass assignment | `app/Models/User.php:32` | Gunakan `$fillable` eksplisit — tidak pakai `$guarded` |
| Test assertion | `tests/Feature/ExampleTest.php:18` | `$response->assertStatus(302)` |
| Test class | `tests/Feature/ExampleTest.php` | Extend `Tests\TestCase`, method `test_*` style |

## Files to Change

| File | Action | Why |
|---|---|---|
| `app/Models/Kelas.php` | UPDATE | `$protected` (typo) → `$guarded = ['id']` |
| `app/Models/Presence.php` | UPDATE | Hapus `$guarded = ['']` yang konflik dengan `$fillable` |
| `CLAUDE.md` | UPDATE | Dokumentasikan PHP 8.4 deprecation warning dari simple-qrcode |
| `.claude/prds/laravel13-compatibility-audit.prd.md` | UPDATE | Update milestone status setelah fix selesai |

## Tasks

### Task 1: Fix Kelas.php — typo `$protected` → `$guarded`
- **Action**: Ganti `protected $protected = ['id']` dengan `protected $guarded = ['id']`
- **Mirror**: Pola guarded di model lain di project ini
- **Validate**: `php artisan tinker --execute="App\Models\Kelas::make(['id' => 999]); echo 'OK';"` — `id` tidak boleh mass-assignable

### Task 2: Fix Presence.php — hapus `$guarded = ['']` yang konflik
- **Action**: Hapus baris `protected $guarded = [''];` — model sudah punya `$fillable` yang benar
- **Mirror**: `app/Models/User.php:32` — gunakan hanya `$fillable`
- **Validate**: `php artisan tinker --execute="App\Models\Presence::make(['user_id' => 1]); echo 'OK';"` — harus bekerja

### Task 3: Dokumentasikan simple-qrcode PHP 8.4 deprecation di CLAUDE.md
- **Action**: Tambah catatan di bagian "Known Issues" atau "Environment Setup Notes" di CLAUDE.md bahwa simple-qrcode menghasilkan PHP 8.4 deprecation notices yang akan menjadi fatal di PHP 8.5
- **Validate**: CLAUDE.md terbaca dengan catatan warning tersebut

### Task 4: Update PRD milestone status
- **Action**: Set Milestone 1 → `complete`, Milestone 2 → `complete`, Milestone 3 → `complete` (packages verified via tinker), update plan cell dengan path ke file ini
- **Validate**: PRD terbaca dengan status yang benar

## Validation

```bash
cd "/Users/noxval/_PROJECT_/Polbangtan-mlg/E-Management Polbangtan-mlg"

# Run test suite
php artisan test

# Verify model fixes
php artisan tinker --execute="App\Models\Kelas::make(['id' => 999]); echo 'Kelas guarded OK';"
php artisan tinker --execute="App\Models\Presence::make(['user_id' => 1]); echo 'Presence fillable OK';"
```

## Risks

| Risk | Likelihood | Mitigation |
|---|---|---|
| PHP 8.5 breaking simple-qrcode | Low (future) | Monitor simplesoftwareio/simple-qrcode GitHub untuk patch |
| Kelas `$guarded` vs `$fillable` strategy | Low | `$guarded = ['id']` adalah safe minimum — id tidak bisa dioverride |

## Acceptance

- [ ] `Kelas.php` tidak lagi memiliki `$protected` (typo) — diganti `$guarded = ['id']`
- [ ] `Presence.php` tidak lagi memiliki `$guarded = ['']` — hanya `$fillable` yang aktif
- [ ] `php artisan test` — 2/2 passed
- [ ] Deprecation warning `simple-qrcode` terdokumentasi di `CLAUDE.md`
- [ ] PRD Milestone 1, 2, 3 diupdate ke `complete`
