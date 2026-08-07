# Plan: Perbaikan Frontend Alpine.js Workflow Steps & Penjelasan Role Dosen PA

**Source PRD**: `.claude/prds/perbaikan-ui-workflow-dan-dosen-pa.prd.md`
**Selected Milestone**: 1 & 2 — Integrasi Alpine.js & Penjelasan Role Dosen PA
**Complexity**: Small

## Summary
Masalah tidak munculnya opsi Jenis Izin, tidak dapat menambah/melihat langkah persetujuan (Workflow Steps), dan pratinjau alur disebabkan oleh **belum diimpor dan di-start nya `alpinejs` pada `resources/js/app.js`**. Dengan menambahkan inisialisasi Alpine.js secara global dan menjalankan `npm run build`, seluruh fitur reaktif frontend akan berjalan 100%. Selain itu, dibuat penjelasan teknis mengenai arsitektur Akun & Role Dosen PA.

## Patterns to Mirror
| Category | Source | Pattern |
|---|---|---|
| JS Import | `resources/js/app.js:1-18` | ESM import & global assignment pada `window` |
| Vite Build | `package.json:5` | `npm run build` kompilasi bundler Vite |

## Files to Change
| File | Action | Why |
|---|---|---|
| `resources/js/app.js` | UPDATE | Impor `alpinejs`, assign ke `window.Alpine`, dan panggil `Alpine.start()` |
| `public/build/` | UPDATE | Hasil kompilasi bundler Vite (`npm run build`) |

## Tasks
### Task 1: Impor dan Inisialisasi Alpine.js di `resources/js/app.js`
- **Action**:
  - Tambahkan `import Alpine from 'alpinejs';`
  - Assign `window.Alpine = Alpine;`
  - Eksekusi `Alpine.start();`
- **Validate**: Jalankan `npm run build` tanpa error bundler.

### Task 2: Rebuild Asset Vite
- **Action**: Jalankan `npm run build` via Bash tool.
- **Validate**: Permukaan file asset terkompilasi di `public/build/`.

### Task 3: Verifikasi Test Suite & Penjelasan Dosen PA
- **Action**: Jalankan `php artisan test` untuk memastikan zero regresi.
- **Validate**: PASS 100%.

## Validation
```bash
npm run build
php artisan test
```

## Risks
| Risk | Likelihood | Mitigation |
|---|---|---|
| Konflik inisialisasi script lain | Low | Alpine di-start secara standar di `app.js` sebelum DOM ready atau script eksternal |
