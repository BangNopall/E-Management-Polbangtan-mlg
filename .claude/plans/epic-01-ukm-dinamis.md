# Plan: Epic 01 — Modul UKM Dinamis

**Source design:** `docs/design/DESIGN-Epic01-Modul-UKM-Dinamis.md` (arsitektur, ADR-005)
**Companion:** `docs/design/DESIGN-Epic01-Frontend-UKM-Dinamis.md` (12 view)
**Branch:** `feat/epic-01`
**Complexity:** Large (6 milestone, ~20 file baru, 6 file produksi disentuh)
**Status:** Menunggu konfirmasi — **belum ada kode yang ditulis**

---

## Summary

Membangun mesin absensi generik 4 tabel (`ukms`, `ukm_members`, `ukm_jadwals`, `ukm_presensis`)
sesuai ADR-005. Menambah UKM baru = 1 INSERT, tanpa migrasi/view/`switch` baru. Dikerjakan
dalam 6 milestone A–F yang masing-masing bisa di-commit & diuji terpisah, dengan test ditulis
**sebelum** implementasi (TDD).

Modul Kegiatan Wajib lama **tidak disentuh** (keputusan §7.4). Kode produksi yang disentuh
terbatas pada 6 file, semuanya untuk mengakomodasi role `pembina` (5) dan navigasi.

---

## Keputusan yang menutup konflik desain vs realitas kode

Dua konflik ditemukan saat audit dan sudah dikonfirmasi user pada 3 Agustus 2026:

### Konflik 1 — Role `pembina` (5) kena redirect loop → **Tambal 3 titik hardcode**

Dokumen desain menyatakan "middleware `EnsureUserHasRole` sudah generik, `role:pembina`
langsung jalan tanpa ubah kode middleware." **Itu benar untuk middleware-nya**, tapi audit
menemukan 3 titik lain yang hardcode role ID sebagai list integer dan tidak disebut di
dokumen mana pun:

| # | Lokasi | Kondisi sekarang | Akibat kalau dibiarkan |
|---|---|---|---|
| 1 | `app/Http/Controllers/AuthController.php:authDashboard()` | `if role_id == 1\|\|2\|\|4 → admin.index; if == 3 → home.index` | role 5 tidak match apa pun → method `return null` → halaman kosong setelah login |
| 2 | `routes/web.php:98` | `Route::middleware('role:admin,operator,pelatih')` membungkus `admin.index` | `EnsureUserHasRole` gagal → redirect ke `admin.index` → gagal lagi → **infinite redirect loop** |
| 3 | `resources/views/partials/nav.blade.php:94` | `@if role_id == 1 \|\| 2 \|\| 4` | Pembina tidak melihat menu staf sama sekali |

**Keputusan:** tambal ketiganya (edit minimal ~3 baris). Titik 1 & 2 dikerjakan di
**Milestone A** (supaya akun pembina bisa login sejak awal dan dipakai menguji Milestone E),
titik 3 di **Milestone F** bersama perubahan nav lain.

### Konflik 2 — TDD mustahil tanpa DB tes → **DB MySQL tes terpisah + `.env.testing`**

`phpunit.xml` punya `DB_CONNECTION=sqlite` dalam keadaan **dikomentari**. Akibatnya kedua
test file existing sengaja menghindari `RefreshDatabase` dan menembak DB dev MySQL langsung
(`User::where(...)->firstOrFail()`) — didokumentasikan eksplisit di komentar
`tests/Feature/KonselingHandoffTest.php`.

Menguji 4 tabel baru + constraint `unique` + fan-out transaksional **wajib** punya DB tes.
**Keputusan:** buat database MySQL `*_testing` + `.env.testing`, pakai `RefreshDatabase`
untuk semua test UKM baru.

Alasan MySQL dipilih di atas SQLite in-memory: skema bergantung pada kolom `ENUM` dan
`cascadeOnDelete`. SQLite memperlakukan enum sebagai teks bebas dan butuh
`PRAGMA foreign_keys=ON` — suite hijau di SQLite bisa menyembunyikan pelanggaran constraint
yang nyata di produksi. Ini jadi task **A0**, dikerjakan sebelum migrasi pertama ditulis.

### Temuan positif (mengurangi scope)

**`public/js/scancamera-kegiatan.js` sudah generik.** Baris 13 & 55 hanya memanggil
`document.getElementById("form").submit()` — endpoint tidak pernah di-hardcode, JS
mengirim ke `action` apa pun yang dibawa `<form>`. Fallback di dokumen frontend §3.4
("kalau hardcoded, buat `scancamera-ukm.js` tipis") **tidak diperlukan**.
→ **0 file JS baru** di Milestone D.

---

## Patterns to Mirror

Semua pola di bawah sudah diverifikasi ada di repo, dengan referensi baris.

| Kategori | Sumber | Pola yang diikuti |
|---|---|---|
| Migrasi | `database/migrations/2024_01_15_001647_create_presensi_apels_table.php` | Closure `Schema::create`, `$table->id()`, `foreignId()->constrained()` |
| Model | `app/Models/PresensiApel.php:8-21` | `use HasFactory`, `protected $guarded = ['id']`, relasi `belongsTo` dengan FK eksplisit |
| Naming | Design §5.2 catatan konvensi | **snake_case bersih** (`ukm_jadwal_id`) + Model PascalCase — sengaja beda dari `jadwalKegiatanAsrama_id` lama |
| Controller | `app/Http/Controllers/kegiatanAsramaController.php:createJadwalKegiatanStore` | `try/catch \Throwable` → `redirect()->route(...)->with('error'\|'success', ...)` |
| Error scanner | `app/Http/Controllers/QRControllerKegiatan.php:134-173` | `throw new \Exception('pesan Indonesia')` di private method, ditangkap di method publik |
| Validasi waktu | `QRControllerKegiatan.php:51-56` | `Carbon::createFromFormat('H:i:s')` + `diffInSeconds` ≤ 30 detik |
| Fan-out | `kegiatanAsramaController.php` (loop `$searchUser`) | Loop user → `create([... 'status_kehadiran' => 'Alpha'])`. **Digeneralisasi:** loop anggota, 1 tabel, dibungkus `DB::transaction()` (yang lama tidak punya transaksi) |
| Test | `tests/Feature/KonselingHandoffTest.php` | Docblock Indonesia yang menjelaskan *kenapa* pendekatan tesnya begitu; assert redirect 302 bukan 403 |
| Route | `routes/web.php:98-175` | Group `middleware('role:...')->name('admin.')` |
| Factory | `database/factories/PresensiApelFactory.php` | `definition()` return array. **Diperbaiki:** pakai relasi factory, bukan `numberBetween` hardcode |
| View | `resources/views/admin/jadwal-kegiatan.blade.php` | `@extends('layouts.main')` + `@section('container')` + accordion Flowbite |
| Scanner view | `resources/views/admin/kamera-apel.blade.php:86-95` | Form hidden `user_id,date,time,scanner` + 2 `<script>` tag |

**Tidak ada pola existing untuk:** membership/keanggotaan opt-in, alur verifikasi berjenjang
pada jadwal, dan test yang memakai `RefreshDatabase`. Ketiganya baru di codebase ini.

---

## Milestone A — Skema, Model & Fondasi Tes

**Tujuan:** `migrate:fresh` sukses, relasi Eloquent hidup, akun pembina bisa login, suite tes punya DB.

### Files

| File | Aksi | Alasan |
|---|---|---|
| `.env.testing` | CREATE | Koneksi DB tes terpisah (task A0) |
| `phpunit.xml` | UPDATE | Hapus komentar env DB, arahkan ke `.env.testing` |
| `database/migrations/*_create_ukms_table.php` | CREATE | Skema design §5.2 |
| `database/migrations/*_create_ukm_members_table.php` | CREATE | Dimensi membership + `unique(ukm_id,user_id)` |
| `database/migrations/*_create_ukm_jadwals_table.php` | CREATE | Jadwal + kolom verifikasi |
| `database/migrations/*_create_ukm_presensis_table.php` | CREATE | Satu tabel absensi + `unique(ukm_jadwal_id,user_id)` |
| `app/Models/Ukm.php` | CREATE | + scope `anggotaAktif()`, `pelatih()` |
| `app/Models/UkmMember.php` | CREATE | |
| `app/Models/UkmJadwal.php` | CREATE | |
| `app/Models/UkmPresensi.php` | CREATE | |
| `app/Models/User.php` | UPDATE | `const PEMBINA_ROLE_ID = 5` + `isPembina()` + relasi `ukmMemberships()`, `ukms()` |
| `database/seeders/RoleSeeder.php` | UPDATE | `Role::create(['name' => 'pembina'])` |
| `app/Http/Controllers/AuthController.php` | UPDATE | **Konflik 1 titik 1** — role 5 → `admin.index` |
| `routes/web.php` | UPDATE | **Konflik 1 titik 2** — `role:admin,operator,pelatih,pembina` pada grup `admin.index` |
| `database/factories/UkmFactory.php` + 3 lainnya | CREATE | Pakai relasi factory, bukan ID hardcode |

### Test dulu (TDD)

`tests/Feature/Ukm/UkmSchemaTest.php`:
1. `test_ukm_nama_harus_unik` — insert nama duplikat → `QueryException`
2. `test_satu_user_tidak_bisa_terdaftar_dua_kali_di_ukm_yang_sama` — `unique(ukm_id,user_id)`
3. `test_presensi_ganda_ditolak_di_level_database` — `unique(ukm_jadwal_id,user_id)`
4. `test_menghapus_ukm_menghapus_anggota_dan_jadwalnya` — `cascadeOnDelete`
5. `test_menghapus_jadwal_menghapus_presensinya` — cascade berjenjang
6. `test_relasi_anggota_aktif_hanya_mengembalikan_peran_anggota_berstatus_aktif` — scope `anggotaAktif()` tidak ikut menarik pelatih/pembina/nonaktif

`tests/Feature/Ukm/PembinaRoleTest.php`:
7. `test_pembina_diarahkan_ke_dashboard_admin_setelah_login` — **regresi Konflik 1**
8. `test_pembina_tidak_terjebak_redirect_loop_saat_membuka_dashboard_admin` — assert 200, bukan 302 berulang
9. `test_role_lain_tetap_diarahkan_seperti_sebelumnya` — admin/operator/pelatih/user tidak berubah

### Definition of Done

- [ ] `php artisan migrate:fresh --seed` sukses di DB dev **dan** DB tes
- [ ] 9 test di atas hijau
- [ ] `tests/Feature/Laravel13SmokeTest.php` & `KonselingHandoffTest.php` **tetap hijau** (bukti DB tes ter-seed benar)
- [ ] Akun pembina bisa login manual dan mendarat di `/dashboard-admin` tanpa loop
- [ ] Tabel `roles` punya 5 baris; `User::PEMBINA_ROLE_ID === 5`

**Risiko:** mengubah `authDashboard()` menyentuh alur login harian. Mitigasi: test #9 mengunci perilaku 4 role lama sebelum perubahan dibuat.

---

## Milestone B — CRUD UKM + Anggota (US 1.1, US 1.0)

**Tujuan:** admin bisa membuat UKM lewat 1 form, menambah/mengeluarkan anggota.

### Files

| File | Aksi | Alasan |
|---|---|---|
| `app/Http/Controllers/UkmController.php` | CREATE | `--resource`, US 1.1 |
| `app/Http/Controllers/UkmMemberController.php` | CREATE | `store`/`destroy`, US 1.0 |
| `app/Http/Requests/StoreUkmRequest.php` | CREATE | Validasi di boundary (rules/php/security.md) |
| `app/Http/Requests/StoreUkmMemberRequest.php` | CREATE | Termasuk aturan peran↔role |
| `routes/web.php` | UPDATE | Grup `role:admin` — resource + 2 route anggota |
| `resources/views/partials/alert.blade.php` | CREATE | Frontend §5 — ekstrak blok alert ±25 baris (saat ini disalin identik di `kamera-apel.blade.php:5-50` dkk) |
| `resources/views/admin/ukm/index.blade.php` | CREATE | View #1 |
| `resources/views/admin/ukm/partials/ukm_table.blade.php` | CREATE | View #2 |
| `resources/views/admin/ukm/show.blade.php` | CREATE | View #3, 3 tab |
| `resources/views/admin/ukm/partials/anggota_table.blade.php` | CREATE | View #4 |
| `resources/views/partials/modals/ukm-tambah-anggota.blade.php` | CREATE | View #5 |

### Test dulu (TDD)

`tests/Feature/Ukm/UkmCrudTest.php`:
1. `test_admin_bisa_membuat_ukm_dengan_satu_field_wajib` — JTBD Admin: create = 1 aksi
2. `test_slug_dibuat_otomatis_dari_nama`
3. `test_nama_ukm_duplikat_ditolak_dengan_pesan_validasi` (bukan 500)
4. `test_pelatih_tidak_bisa_membuat_ukm` — gate `role:admin`, assert 302
5. `test_mahasiswa_tidak_bisa_mengakses_halaman_kelola_ukm`
6. `test_ukm_bisa_dinonaktifkan_tanpa_menghapus_data`

`tests/Feature/Ukm/UkmMemberTest.php`:
7. `test_admin_bisa_menambah_mahasiswa_sebagai_anggota`
8. `test_menambah_anggota_yang_sudah_terdaftar_ditolak_dengan_pesan_ramah` — constraint DB tidak boleh bocor jadi 500
9. `test_staf_tidak_bisa_ditambahkan_dengan_peran_anggota` — **§7.2:** `peran='anggota'` hanya `role_id=3`
10. `test_mahasiswa_tidak_bisa_ditambahkan_dengan_peran_pelatih` — kebalikannya, `peran IN (pelatih,pembina)` hanya `role_id IN (4,5)`
11. `test_anggota_bisa_dikeluarkan_dari_ukm`

### Definition of Done

- [ ] 11 test hijau
- [ ] Admin bisa buat UKM lewat 1 field wajib + 1 klik (bukan wizard)
- [ ] Search live tabel UKM jalan via AJAX JSON `{table: ...}` (pola `dataKegiatanWajibSearch`)
- [ ] Aturan peran↔role ditegakkan di FormRequest, bukan cuma di UI
- [ ] `partials/alert.blade.php` dipakai `@include` oleh semua view baru
- [ ] Tidak ada warna/spacing/komponen baru di luar design system existing

---

## Milestone C — Penjadwalan + Fan-out Presensi (US 1.2)

**Tujuan:** pelatih buat jadwal → baris `ukm_presensis` (Alpha) muncul untuk tiap anggota aktif, dalam transaksi.

### Files

| File | Aksi | Alasan |
|---|---|---|
| `app/Http/Controllers/UkmJadwalController.php` | CREATE | US 1.2 |
| `app/Http/Requests/StoreUkmJadwalRequest.php` | CREATE | Termasuk `selesai_acara > mulai_acara` |
| `app/Services/UkmPresensiFanoutService.php` | CREATE | Fan-out dikeluarkan dari controller (rules/php/patterns.md: thin controller) |
| `routes/web.php` | UPDATE | Grup `role:admin,pelatih` |
| `resources/views/admin/ukm/jadwal.blade.php` | CREATE | View #6 + FullCalendar |
| `resources/views/admin/ukm/partials/jadwal_table.blade.php` | CREATE | View #7 |

### Test dulu (TDD)

`tests/Feature/Ukm/UkmJadwalTest.php`:
1. `test_membuat_jadwal_membuat_presensi_alpha_untuk_setiap_anggota_aktif`
2. `test_fan_out_melewati_anggota_berstatus_nonaktif`
3. `test_fan_out_tidak_mengabsen_pelatih_dan_pembina` — **§7.2:** staf tidak ikut diabsen
4. `test_fan_out_dibatalkan_seluruhnya_jika_terjadi_error_di_tengah` — `DB::transaction()`, assert 0 baris tersisa. **Pengaman yang tidak dimiliki modul lama.**
5. `test_jadwal_dengan_selesai_sebelum_mulai_ditolak`
6. `test_jadwal_baru_berstatus_verifikasi_draft`
7. `test_pelatih_hanya_bisa_menjadwalkan_untuk_ukm_yang_dia_bina` — scope keanggotaan per-UKM, bukan sekadar gate role
8. `test_ukm_tanpa_anggota_tetap_bisa_dijadwalkan_tanpa_error`

### Definition of Done

- [ ] 8 test hijau
- [ ] Fan-out **selalu** dalam `DB::transaction()` — test #4 membuktikan rollback
- [ ] Jumlah baris `ukm_presensis` == jumlah anggota aktif, tepat
- [ ] Kalender FullCalendar memberi warna berbeda per `jenis` (latihan / kegiatan_wajib)
- [ ] Otorisasi per-UKM diverifikasi, bukan cuma gate role global

---

## Milestone D — Scanner QR Generik (US 1.3) ⭐

**Tujuan:** satu halaman + satu method melayani semua UKM. QR mahasiswa **tidak berubah sama sekali**.

### Files

| File | Aksi | Alasan |
|---|---|---|
| `app/Http/Controllers/UkmScanController.php` | CREATE | `show` + `store`, **1 method** bukan 3 |
| `routes/web.php` | UPDATE | `kamera-ukm/{jadwal}` + `api/kamera-ukm/{jadwal}` |
| `resources/views/admin/ukm/kamera-ukm.blade.php` | CREATE | View #8 — clone `kamera-apel.blade.php`, judul dinamis, `action` ber-`{jadwal->id}` |

**Tidak dibuat:** file JS baru. `public/js/scancamera-kegiatan.js` sudah membaca `action`
dari elemen `<form>` (terverifikasi di baris 13 & 55) → dipakai ulang apa adanya.
**Tidak diubah:** `QRController::kodeqr()` — QR mahasiswa tetap sama persis.

### Test dulu (TDD)

`tests/Feature/Ukm/UkmScanTest.php`:
1. `test_scan_valid_mengubah_status_alpha_menjadi_hadir_beserta_jam_kehadiran`
2. `test_scan_kedua_kali_ditolak_dengan_pesan_sudah_presensi` — anti-scan-ganda
3. `test_qr_lebih_dari_30_detik_ditolak_sebagai_expired` — salin ambang `QRControllerKegiatan.php:55`
4. `test_scan_sebelum_acara_dimulai_ditolak`
5. `test_scan_setelah_acara_selesai_ditolak`
6. `test_mahasiswa_bukan_anggota_ukm_ditolak` — **pengganti cek blok** di modul lama
7. `test_qr_pelanggaran_ditolak_di_scanner_ukm` — `scanner != 'absensi'`
8. `test_satu_endpoint_melayani_dua_ukm_berbeda_tanpa_kode_tambahan` — **membuktikan tesis ADR-005**
9. `test_scan_untuk_jadwal_yang_tidak_ada_menghasilkan_error_ramah` (bukan 500)

### Definition of Done

- [ ] 9 test hijau
- [ ] **Satu** pasang route + **satu** view + **satu** method melayani UKM tak-terbatas
- [ ] Test #8 lulus tanpa satu baris pun kode khusus per-UKM
- [ ] `git diff` membuktikan `QRController.php` dan `scancamera-kegiatan.js` **tidak tersentuh**
- [ ] Uji manual: scan QR asrama existing di `/kamera-ukm/{jadwal}` berhasil

---

## Milestone E — Verifikasi Pembina + Laporan (US 1.4)

**Tujuan:** pembina approve/reject jadwal; laporan PDF generik ter-render.

### Files

| File | Aksi | Alasan |
|---|---|---|
| `app/Http/Controllers/UkmVerifikasiController.php` | CREATE | `index` + `update` |
| `app/Http/Controllers/UkmLaporanController.php` | CREATE | PDF generik ber-`ukm_id` |
| `routes/web.php` | UPDATE | Grup `role:admin,pembina` |
| `resources/views/admin/ukm/verifikasi.blade.php` | CREATE | View #9 |
| `resources/views/admin/generate/generate-ukm.blade.php` | CREATE | View #12 — **1** template menggantikan pola 3 template lama |

### Test dulu (TDD)

`tests/Feature/Ukm/UkmVerifikasiTest.php`:
1. `test_pelatih_bisa_mengajukan_jadwal_dari_draft_ke_menunggu`
2. `test_pembina_bisa_menyetujui_jadwal_dan_jejaknya_tercatat` — `verified_by` + `verified_at`
3. `test_pembina_bisa_menolak_jadwal_dengan_catatan`
4. `test_menolak_tanpa_catatan_ditolak` — akuntabilitas
5. `test_pelatih_tidak_bisa_menyetujui_jadwalnya_sendiri` — **separation of duties**
6. `test_pembina_hanya_melihat_jadwal_ukm_binaannya` — scope per-UKM, bukan semua UKM
7. `test_jadwal_berstatus_draft_tidak_muncul_di_antrean_verifikasi`
8. `test_jadwal_yang_sudah_disetujui_tidak_bisa_diubah_lagi`

`tests/Feature/Ukm/UkmLaporanTest.php`:
9. `test_laporan_pdf_ter_render_untuk_ukm_mana_pun` — assert `content-type: application/pdf`
10. `test_laporan_hanya_memuat_data_ukm_yang_diminta` — tanpa kebocoran lintas-UKM

### Definition of Done

- [ ] 10 test hijau
- [ ] Perubahan status dibungkus `DB::transaction()`
- [ ] Setiap approve/reject meninggalkan jejak `verified_by` + `verified_at`
- [ ] Test #5 & #6 membuktikan otorisasi berbasis scope, bukan cuma role
- [ ] Satu template PDF melayani semua UKM (bandingkan: 3 template untuk 3 kegiatan lama)

---

## Milestone F — Seeder, Navigasi & Uji Asap

**Tujuan:** modul terpasang di UI, ada data contoh, suite penuh hijau.

### Files

| File | Aksi | Alasan |
|---|---|---|
| `database/seeders/UkmSeeder.php` | CREATE | UKM contoh + anggota + jadwal |
| `database/seeders/DatabaseSeeder.php` | UPDATE | `$this->call(UkmSeeder::class)` **setelah** RoleSeeder & user (urutan dependensi) |
| `resources/views/partials/nav.blade.php` | UPDATE | **Konflik 1 titik 3** + grup menu "UKM" ber-gate role |
| `app/Http/Controllers/UkmMahasiswaController.php` | CREATE | `index` + `riwayat` |
| `routes/web.php` | UPDATE | Grup `role:user` — `ukm-saya`, `ukm-saya/riwayat` |
| `resources/views/ukm/index.blade.php` | CREATE | View #10 |
| `resources/views/ukm/riwayat.blade.php` | CREATE | View #11 |

### Test dulu (TDD)

`tests/Feature/Ukm/UkmMahasiswaTest.php`:
1. `test_mahasiswa_hanya_melihat_ukm_yang_dia_ikuti`
2. `test_riwayat_menampilkan_status_hadir_izin_alpha`
3. `test_mahasiswa_tidak_melihat_riwayat_mahasiswa_lain` — kebocoran data
4. `test_mahasiswa_tanpa_ukm_melihat_halaman_kosong_yang_ramah` (bukan error)

`tests/Feature/Ukm/UkmNavigasiTest.php`:
5. `test_admin_melihat_menu_kelola_ukm`
6. `test_pelatih_melihat_menu_jadwal_dan_scan_tapi_tidak_kelola_ukm`
7. `test_pembina_melihat_menu_verifikasi` — **regresi Konflik 1 titik 3**
8. `test_mahasiswa_hanya_melihat_menu_ukm_saya`

`tests/Feature/Ukm/UkmSmokeTest.php`:
9. `test_semua_halaman_ukm_termuat_tanpa_error` — ikuti pola `Laravel13SmokeTest`

### Definition of Done

- [ ] Seluruh suite `php artisan test` hijau (test UKM baru **dan** 2 test file lama)
- [ ] `php artisan migrate:fresh --seed` sukses dari nol
- [ ] Menu UKM tampil sesuai role: admin/pelatih/pembina/mahasiswa berbeda-beda
- [ ] Mahasiswa dari `ukm/index` bisa menuju `kodeqr` existing — **tanpa QR baru**
- [ ] Uji asap manual satu putaran penuh: buat UKM → tambah anggota → jadwal → scan → verifikasi → PDF

---

## Validation

```bash
# Setup sekali di awal Milestone A (task A0)
mysql -u root -e "CREATE DATABASE IF NOT EXISTS emanagement_testing;"
cp .env .env.testing     # lalu ubah DB_DATABASE=emanagement_testing
php artisan migrate:fresh --seed --env=testing

# Per milestone
php artisan test --filter Ukm          # test milestone berjalan
php artisan test                        # seluruh suite, termasuk 2 file lama
php artisan migrate:fresh --seed        # skema bisa dibangun dari nol

# Sebelum commit tiap milestone
php artisan route:list --name=ukm       # route terdaftar sesuai rencana
git diff --stat                         # konfirmasi tidak ada file di luar daftar tersentuh
```

---

## Risks

| Risiko | Kemungkinan | Dampak | Mitigasi |
|---|---|---|---|
| Ubah `authDashboard()` merusak login role lama | Sedang | **Tinggi** — semua user | Test A#9 mengunci perilaku 4 role lama **sebelum** kode diubah |
| DB tes belum ter-seed → 2 test lama pecah | Tinggi | Sedang | DoD Milestone A eksplisit mensyaratkan keduanya tetap hijau |
| Pelanggaran constraint bocor jadi 500, bukan pesan ramah | Sedang | Sedang | Test B#8 secara khusus menguji jalur ini |
| Fan-out gagal separuh jalan → presensi tidak konsisten | Rendah | **Tinggi** — integritas data | `DB::transaction()` + test C#4 membuktikan rollback |
| Otorisasi hanya berbasis role global, lupa scope per-UKM | Sedang | **Tinggi** — pelatih A mengelola UKM B | Test C#7, E#5, E#6 menguji scope, bukan cuma role |
| Scope melebar ke refactor Kegiatan Wajib | Sedang | Sedang | ADR-005 §7.4 eksplisit menundanya ke ADR-006; DoD D memeriksa `git diff` |
| `enum` MySQL berperilaku beda dari harapan | Rendah | Sedang | DB tes memakai MySQL, bukan SQLite — sengaja |

---

## Acceptance (Epic 01)

- [ ] Menambah UKM baru = 1 INSERT, **0 baris kode** (dibuktikan test D#8)
- [ ] **1** tabel presensi, **1** view kamera, **1** template laporan melayani UKM tak-terbatas
- [ ] QR mahasiswa dipakai ulang tanpa satu pun perubahan
- [ ] Kegiatan Wajib lama tidak tersentuh
- [ ] Seluruh test hijau; setiap milestone punya commit terpisah
- [ ] Role `pembina` (5) berfungsi penuh tanpa redirect loop
- [ ] Pola desain diikuti, bukan diciptakan ulang

---

## Ringkasan Angka

| Metrik | Jumlah |
|---|---|
| Milestone | 6 (A–F) |
| Tabel baru | 4 |
| Model baru | 4 |
| Controller baru | 7 |
| View baru | 13 (12 dari dokumen frontend + `partials/alert`) |
| File JS baru | **0** (scanner JS existing sudah generik) |
| File produksi disentuh | 6 (`User`, `AuthController`, `routes/web`, `nav.blade`, `RoleSeeder`, `DatabaseSeeder`) |
| Test ditulis lebih dulu | 60 |
