# Plan: Perbaikan Modul UKM Dinamis

**Source PRD**: `.claude/prds/perbaikan-modul-ukm-dinamis.prd.md`
**Selected Milestone**: Milestone 1–5 (seluruh 6 isu dikirim dalam satu batch, sesuai keputusan user)
**Complexity**: Medium

## Summary
Enam perbaikan independen namun saling berdekatan pada Modul UKM Dinamis: memulihkan akses Pembina (profil + logout), merender kalender jadwal UKM dengan FullCalendar (sudah ter-install di `node_modules`, sudah dipakai di dashboard admin), mengganti dropdown staf statis menjadi search-dropdown Choices.js yang terfilter per peran, menambahkan cascade status `ukm_members` saat UKM dinonaktifkan via Eloquent Observer, dan mengekspos tombol hapus UKM permanen (backend `destroy()` sudah ada, hanya kurang UI) yang hanya aktif saat UKM berstatus nonaktif.

## Patterns to Mirror
| Category | Source | Pattern |
|---|---|---|
| Role exclusion bug | `resources/views/partials/headnav.blade.php:27`, `app/Http/Controllers/ProfileController.php:22,77,159,166` | Kondisi `role_id == 1 \|\| 2 \|\| 4` / `isAdmin() \|\| isOperator() \|\| isPelatih()` berulang di 4 titik — semua perlu ditambah Pembina (role 5) agar konsisten, bukan cuma 1 titik |
| FullCalendar init | `public/js/kalendar.js:1-30`, `resources/views/index.blade.php:170-171` | `fetch()` → `new FullCalendar.Calendar(el, {...}).render()`; library dimuat via `<script src="{{ asset('js/library/index.global.min.js') }}">` (bukan bundling Vite) |
| Per-UKM staff scoping (anti-IDOR) | `app/Http/Controllers/UkmVerifikasiController.php:22-30`, `UkmController.php:72-80` | `UkmMember::where('ukm_id', ...)->where('user_id', ...)->whereIn('peran', [...])->where('status','aktif')->exists()`, admin bypass via `role_id !== User::ADMIN_ROLE_ID` |
| FormRequest validation + inline closure rule | `app/Http/Requests/StoreUkmMemberRequest.php:20-45` | Custom closure rule sudah memvalidasi peran vs role user di backend — filter dropdown di frontend adalah UX tambahan, bukan pengganti validasi ini |
| Controller try/catch + flash message | `UkmController.php:88-116` | `try { ... } catch (\Throwable $th) { redirect()->back()->with('error', $th->getMessage()); }` |
| Delete confirmation di Blade | `resources/views/admin/ukm/partials/anggota_table.blade.php:45-46` | `onsubmit="return confirm('...')"` pada form DELETE — pola native `confirm()`, tidak ada SweetAlert di codebase |
| Test lokasi & gaya | `tests/Feature/Ukm/*.php` (mis. `PembinaRoleTest.php`, `UkmCrudTest.php`) | `RefreshDatabase`, helper privat `makeUser(int $roleId)`, nama method `test_snake_case_bahasa_indonesia`, assert redirect/status/database |

## Files to Change
| File | Action | Why |
|---|---|---|
| `resources/views/partials/headnav.blade.php` | UPDATE | Tambah `role_id == 5` ke kondisi tombol logout (Isu #3) |
| `app/Http/Controllers/ProfileController.php` | UPDATE | Tambah Pembina ke 4 kondisi role-check (`index()`, `editProfile()` x2, `editProfileGmail()` x2) (Isu #2) |
| `resources/views/admin/ukm/show.blade.php` | UPDATE | Tambah FullCalendar init script + endpoint fetch event jadwal UKM saat tab kalender dibuka (Isu #1) |
| `app/Http/Controllers/UkmJadwalController.php` | UPDATE | Tambah method `events(Ukm $ukm)` mengembalikan JSON jadwal untuk FullCalendar (Isu #1) |
| `routes/web.php` | UPDATE | Tambah route GET `ukm/{ukm}/jadwal/events` → `UkmJadwalController@events` (Isu #1) |
| `resources/views/partials/modals/ukm-tambah-anggota.blade.php` | UPDATE | Ganti `<select>` staf jadi Choices.js search-dropdown, filter opsi via `data-role` per peran (Isu #4) |
| `app/Http/Controllers/UkmController.php` | UPDATE | Pastikan `$staf` membawa `role_id` untuk filter JS sisi client (Isu #4) |
| `package.json` | UPDATE | Tambah dependency `choices.js` (Isu #4) |
| `resources/js/app.js` | UPDATE | Import & expose Choices.js secara global (mengikuti pola jQuery/Popper yang sudah ada) (Isu #4) |
| `app/Observers/UkmObserver.php` | CREATE | Observer `updated()`: saat `is_active` berubah true→false, set semua `ukm_members.status = 'nonaktif'` untuk UKM tsb (Isu #5) |
| `app/Providers/AppServiceProvider.php` | UPDATE | Registrasi `Ukm::observe(UkmObserver::class)` di `boot()` (Isu #5) |
| `resources/views/admin/ukm/partials/ukm_table.blade.php` | UPDATE | Tambah tombol "Hapus" (hanya muncul saat `!$ukm->is_active`), form DELETE + `confirm()` (Isu #6) |
| `tests/Feature/Ukm/PembinaRoleTest.php` | UPDATE | Tambah test akses profil & logout untuk Pembina |
| `tests/Feature/Ukm/UkmJadwalTest.php` | UPDATE | Tambah test endpoint events kalender |
| `tests/Feature/Ukm/UkmCrudTest.php` | UPDATE | Tambah test cascade status anggota saat nonaktif + test hapus permanen (hanya saat nonaktif, ditolak saat aktif) |

## Tasks

### Task 1: Perbaiki akses Pembina — profil & logout (Isu #2, #3)
- **Action**: Tambahkan `$user->role_id == 5` ke seluruh 4 titik role-check di `ProfileController.php` (baris 22, 77, 159, 166) dan 1 titik di `headnav.blade.php` (baris 27), mengikuti gaya literal `role_id ==` yang sudah dipakai di kedua file tersebut (bukan `isPembina()`, agar konsisten dengan gaya lokal masing-masing file).
- **Mirror**: Pola existing `role_id == 1 || role_id == 2 || role_id == 4` di file yang sama.
- **Validate**: `php artisan test --filter=PembinaRoleTest`

### Task 2: Tambah test regresi akses Pembina
- **Action**: Tambah 2 test baru di `PembinaRoleTest.php`: (a) Pembina GET `/profil-admin` → 200, (b) halaman yang merender `headnav.blade.php` (mis. `/dashboard-admin`) untuk user Pembina menampilkan form logout — assert keberadaan `route('auth.logout')` sebagai form action attribute pada response body, bukan hanya teks "Logout" (lebih robust terhadap perubahan copy).
- **Mirror**: `tests/Feature/Ukm/PembinaRoleTest.php` — gaya `makeUser()` + assert redirect/status existing di file yang sama.
- **Validate**: `php artisan test --filter=PembinaRoleTest`

### Task 3: Endpoint JSON events jadwal UKM untuk FullCalendar (Isu #1)
- **Action**: Tambah method `events(Ukm $ukm)` di `UkmJadwalController` yang mengembalikan `response()->json()` array objek `{title, start, ...}` dari `$ukm->jadwals` (map `judul` → `title`, `tanggal` → `start`). Terapkan scoping anti-IDOR yang sama seperti `UkmController::show()` (admin bypass, staf harus member aktif UKM tsb).
- **Mirror**: Pola scoping `UkmVerifikasiController::index()` baris 22-30; pola JSON return simple di `UkmController::index()` baris 33-37.
- **Validate**: `php artisan test --filter=UkmJadwalTest`

### Task 4: Route + Blade untuk render kalender (Isu #1)
- **Action**: Tambah route `Route::get('ukm/{ukm}/jadwal/events', [UkmJadwalController::class, 'events'])->name('ukm.jadwal.events')` di grup role yang sama dengan route `ukm.show` (`routes/web.php` sekitar baris 153). Di `show.blade.php`, tambahkan `<script>` yang menginisialisasi `FullCalendar.Calendar` pada `#calendar` saat `toggleJadwalView('kalender')` dipanggil (lazy-init sekali saja, guard dengan flag boolean agar tidak re-render tiap klik tab). Tambahkan `<script src="{{ asset('js/library/index.global.min.js') }}">` ke `show.blade.php` (view ini extend `layouts.main` yang tidak memuat FullCalendar secara default).
- **Mirror**: `public/js/kalendar.js` (pola fetch + render), `resources/views/index.blade.php:170-171` (cara include library).
- **Validate**: Manual QA (visual, karena ini interaksi JS di browser) + endpoint `events` teruji via Task 3.

### Task 5: Search-dropdown Choices.js terfilter peran (Isu #4)
- **Action**: Install `choices.js` via npm, import di `resources/js/app.js` dan expose sebagai `window.Choices` (mengikuti pola `window.$ = window.jQuery = $`). `UkmController::show()` tetap mengirim `$staf` (semua pelatih+pembina, objek User sudah membawa `role_id`). Di `ukm-tambah-anggota.blade.php`, ubah `<select id="user_id_staf">` agar tiap `<option>` punya `data-role="{{ $s->role_id }}"`, inisialisasi `new Choices('#user_id_staf', {searchEnabled: true, ...})`, dan modifikasi `toggleUserOptions(peran)` agar memfilter opsi berdasar `data-role` (via `.setChoices()` Choices.js, karena manipulasi DOM `<option>` langsung tidak sinkron dengan instance Choices).
- **Mirror**: Struktur JS existing di file yang sama (`toggleUserOptions()`), pola import Vite di `resources/js/app.js`.
- **Validate**: Manual QA (interaksi dropdown) — tidak ada test PHP untuk perilaku JS murni.

### Task 6: Cascade status anggota via Observer (Isu #5)
- **Action**: Buat `app/Observers/UkmObserver.php` dengan method `updated(Ukm $ukm)`: jika `$ukm->wasChanged('is_active')` dan `$ukm->is_active === false`, jalankan `$ukm->members()->where('status', 'aktif')->update(['status' => 'nonaktif'])`. Registrasikan observer di `AppServiceProvider::boot()` via `Ukm::observe(UkmObserver::class)`. Sesuai keputusan user, TIDAK ada auto-reaktivasi saat `is_active` kembali true.
- **Mirror**: Tidak ada observer lain di codebase (`app/Observers` kosong) — pola baru mengikuti konvensi Laravel standar (`php artisan make:observer`).
- **Validate**: `php artisan test --filter=UkmCrudTest`

### Task 7: Test cascade status anggota (Isu #5)
- **Action**: Tambah test di `UkmCrudTest.php`: buat UKM aktif + 2 member berstatus aktif (1 anggota, 1 pelatih), admin nonaktifkan UKM via `PUT admin.ukm.update`, assert `ukm_members.status` keduanya jadi `'nonaktif'` di database. Tambah test kedua: UKM diaktifkan kembali, assert member yang sudah nonaktif TETAP nonaktif (tidak auto-reaktivasi).
- **Mirror**: `UkmCrudTest::test_admin_bisa_mengubah_status_aktif_ukm()` — pola `assertDatabaseHas`.
- **Validate**: `php artisan test --filter=UkmCrudTest`

### Task 8: Tombol hapus UKM permanen (Isu #6)
- **Action**: Di `ukm_table.blade.php`, tambahkan form `DELETE` ke `route('admin.ukm.destroy', $ukm->id)` dengan tombol "Hapus" yang HANYA dirender jika `!$ukm->is_active`, dengan `onsubmit="return confirm('Hapus UKM {{ $ukm->nama }} secara permanen? Seluruh jadwal dan presensi terkait akan ikut terhapus dan TIDAK BISA dikembalikan.')"`. Tidak perlu ubah `UkmController::destroy()` — sudah benar dan sudah cascade delete via FK di migrasi.
- **Mirror**: `anggota_table.blade.php:45-52` (pola form DELETE + `confirm()` + icon `ri-delete-bin-line`).
- **Validate**: `php artisan test --filter=UkmCrudTest`

### Task 9: Test hapus UKM permanen (Isu #6)
- **Action**: Tambah test di `UkmCrudTest.php`: (a) admin DELETE UKM nonaktif → sukses, `assertDatabaseMissing('ukms', [...])`, dan cascade — `assertDatabaseMissing('ukm_members', ['ukm_id' => $ukm->id])`; (b) non-admin (pelatih) DELETE → 302 redirect (memastikan `Route::resource(...)->except(['show'])` di grup admin-only belum berubah).
- **Mirror**: `UkmCrudTest::test_pelatih_tidak_bisa_membuat_ukm()` — pola cek role-restriction.
- **Validate**: `php artisan test --filter=UkmCrudTest`

### Task 10: Full regression + build verification
- **Action**: Jalankan `npm run build` untuk memastikan penambahan `choices.js` tidak merusak build Vite, lalu `php artisan test` penuh untuk memastikan 0 regresi di seluruh suite (bukan hanya file UKM).
- **Mirror**: N/A — verifikasi akhir standar project ini (`CLAUDE.md` command reference).
- **Validate**: `npm run build && php artisan test`

## Validation
```bash
php artisan test --filter=PembinaRoleTest
php artisan test --filter=UkmCrudTest
php artisan test --filter=UkmJadwalTest
php artisan test --filter=UkmMemberTest
npm run build
php artisan test
```

## Risks
| Risk | Likelihood | Mitigation |
|---|---|---|
| Choices.js re-init pada `<select>` yang sudah pernah di-init bisa menyebabkan dropdown duplikat/rusak saat modal dibuka berulang kali | Sedang | Simpan instance Choices di variabel global, cek `if (!window.stafChoicesInstance)` sebelum re-init; destroy+reinit saat modal ditutup jika perlu |
| Endpoint `events` FullCalendar baru tanpa scoping bisa jadi celah IDOR baru (menampilkan jadwal UKM lain ke staf yang tidak berhak) | Sedang | Terapkan guard anti-IDOR yang identik dengan `UkmController::show()` (sudah dicakup di Task 3) |
| Observer `UkmObserver` bisa ter-trigger juga saat `Ukm::create()` jika ada default `is_active` handling yang keliru, menyebabkan side-effect tak terduga saat UKM baru dibuat | Rendah | Observer hanya react ke `updated()`, bukan `created()`, dan cek eksplisit `wasChanged('is_active') && !is_active` — tidak akan ter-trigger saat create |
| Test `assertSee`/`assertStringContainsString` untuk isu #3 rapuh jika markup Blade berubah nama teks tombol di masa depan | Rendah | Gunakan assertion pada keberadaan `route('auth.logout')` action attribute, bukan hanya teks "Logout", untuk lebih robust |

## Acceptance
- [ ] All tasks complete
- [ ] Validation passes (`php artisan test` hijau penuh + `npm run build` sukses)
- [ ] Patterns mirrored, not reinvented
