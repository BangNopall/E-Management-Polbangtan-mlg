# Plan: Penyempurnaan Alur & Tata Kelola Modul UKM Dinamis

**Source PRD**: `.claude/prds/penyempurnaan-alur-ukm-dinamis.prd.md`
**Selected Milestone**: Milestone 1–5 (seluruh batch, dikirim sekaligus)
**Complexity**: Large (8 isu, 5 milestone, ~18 file)

## Summary
Menegakkan `status_verifikasi` sebagai gerbang perilaku sistem (scanner menolak jadwal non-`disetujui`, mahasiswa hanya melihat jadwal sah), memulihkan jalan keluar operasional yang hilang (reaktivasi anggota, hapus jadwal draft), menampilkan data yang selama ini tersimpan tapi tidak dirender (`catatan_pembina`, `lokasi`, `selesai_acara`), dan memaginasi seluruh tabel modul UKM 20 baris per halaman. Seluruh perubahan mengikuti siklus TDD RED→GREEN dan memakai pola scoping anti-IDOR yang sudah mapan di modul ini.

> **BLOKER DITEMUKAN SAAT GROUNDING — harus diperbaiki lebih dulu (Task 0).**
> `app/Http/Controllers/UkmController.php:44` berisi `dd($request->all());` sebagai baris pertama `store()`. **Fitur "Tambah UKM" saat ini rusak total di produksi** — request selalu mati dengan dump dan UKM tidak pernah tersimpan. Diperkenalkan di commit `f685dd8` (HEAD saat ini), sudah masuk branch. Test `UkmCrudTest::test_admin_bisa_membuat_ukm_dengan_satu_field_wajib` gagal dengan *"Fatal error: Premature end of PHP process"* — artinya **test suite saat ini TIDAK hijau**, berlawanan dengan asumsi "0 regresi" di PRD. Ini di luar 8 isu yang dilaporkan, tapi harus dibereskan agar validasi batch ini bermakna.

## Patterns to Mirror

| Category | Source | Pattern |
|---|---|---|
| Scoping anti-IDOR (staf) | `UkmScanController.php:124-139` | `isStaffOfUkm()` private helper: admin bypass via `role_id === User::ADMIN_ROLE_ID`, lalu `UkmMember::where(ukm_id)->where(user_id)->whereIn('peran',['pelatih','pembina'])->where('status','aktif')->exists()` |
| Scoping anti-IDOR (pembina) | `UkmVerifikasiController.php:25-33` | Varian dengan `->where('peran','pembina')` saja, dipakai untuk endpoint verifikasi |
| Guard status + redirect | `UkmJadwalController.php:80-83` | Cek status lalu `return redirect()->route(...)->with('error', ...)` — **bukan** `abort()`, agar user melihat pesan yang bisa ditindaklanjuti |
| Guard hak akses | `UkmJadwalController.php:77` | `abort_unless($kondisi, 403, 'Pesan bahasa Indonesia')` untuk pelanggaran otorisasi |
| Error handling controller | `UkmMemberController.php:15-29` | `try { ... } catch (\Throwable $th) { return redirect()->back()->with('error', $th->getMessage()); }` |
| Flash message sukses | `UkmMemberController.php:26` | `redirect()->back()->with('success', '...')` dengan pesan bahasa Indonesia |
| Pagination + links | `UkmController.php:30` + `ukm_table.blade.php:86` | `$query->latest()->paginate(N)` di controller, `{{ $ukms->links() }}` di blade |
| Badge status | `anggota_table.blade.php:35-39` | `@if ($member->status === 'aktif')` → span hijau, `@else` → span abu-abu |
| Tombol aksi + konfirmasi | `anggota_table.blade.php:45-52` | `<form method="post" onsubmit="return confirm('...')">` + `@csrf` + `@method('DELETE')` |
| Choices.js search-dropdown | `ukm-tambah-anggota.blade.php:85-90` | `new Choices(select, { searchEnabled: true, itemSelectText: '', placeholder: true, placeholderValue: '...' })` |
| Test: helper user | `UkmMemberTest.php:25-33` | `makeUser(int $roleId)` dengan `blok_ruangan_id/kelas_id/prodi_id => null` |
| Test: assertion DB | `UkmMemberTest.php:49-54` | `assertDatabaseHas('ukm_members', [...])` / `assertDatabaseMissing` |
| Test: nama method | `UkmMemberTest.php:35` | Bahasa Indonesia deskriptif: `test_admin_bisa_menambah_mahasiswa_sebagai_anggota` |

## Files to Change

| File | Action | Why |
|---|---|---|
| `app/Http/Controllers/UkmController.php` | UPDATE | **Task 0**: hapus `dd()` di `store()`. Task 10: pagination anggota & jadwal terpisah di `show()`; `paginate(10)`→`paginate(20)` di `index()` |
| `app/Http/Controllers/UkmScanController.php` | UPDATE | Guard `status_verifikasi === 'disetujui'` di `show()` dan `store()` (isu #4) |
| `app/Http/Controllers/UkmMahasiswaController.php` | UPDATE | Filter `status_verifikasi = 'disetujui'` di jadwal mendatang (isu #4); `paginate(15)`→`paginate(20)` di `riwayat()` (isu #8) |
| `app/Http/Controllers/UkmVerifikasiController.php` | UPDATE | Sembunyikan `draft` dari query + tolak update atas `draft` (isu #3); `paginate(20)` (isu #8) |
| `app/Http/Controllers/UkmJadwalController.php` | UPDATE | Tambah `destroy()` untuk jadwal `draft` (isu #7) |
| `app/Http/Controllers/UkmMemberController.php` | UPDATE | Tambah `aktifkan()` (per-user) dan `aktifkanSemua()` (massal) (isu #1) |
| `routes/web.php` | UPDATE | Route DELETE jadwal, PATCH anggota aktifkan, PATCH anggota aktifkan-semua |
| `resources/views/admin/ukm/partials/jadwal_table.blade.php` | UPDATE | Kolom catatan pembina (isu #5); tombol Hapus saat draft (isu #7); sembunyikan tombol Scanner saat belum disetujui (isu #4); `links()` (isu #8) |
| `resources/views/admin/ukm/partials/anggota_table.blade.php` | UPDATE | Tombol Aktifkan per-user + Aktifkan Semua (isu #1); `links()` (isu #8) |
| `resources/views/admin/ukm/verifikasi.blade.php` | UPDATE | `links()` pagination (isu #8) |
| `resources/views/ukm/index.blade.php` | UPDATE | Tampilkan `lokasi` + `mulai_acara`–`selesai_acara` (isu #6) |
| `resources/views/partials/modals/ukm-tambah-anggota.blade.php` | UPDATE | Choices.js untuk dropdown mahasiswa (isu #2) |
| `tests/Feature/Ukm/UkmScanTest.php` | UPDATE | Test guard status verifikasi (isu #4) |
| `tests/Feature/Ukm/UkmMahasiswaTest.php` | UPDATE | Test filter jadwal mendatang + tampilan lokasi/jam (isu #4, #6) |
| `tests/Feature/Ukm/UkmVerifikasiTest.php` | UPDATE | Test draft tersembunyi + tolak approve draft (isu #3) |
| `tests/Feature/Ukm/UkmJadwalTest.php` | UPDATE | Test hapus jadwal draft + catatan pembina tampil (isu #7, #5) |
| `tests/Feature/Ukm/UkmMemberTest.php` | UPDATE | Test reaktivasi per-user & massal (isu #1) |
| `tests/Feature/Ukm/UkmCrudTest.php` | UPDATE | Test pagination 20 (isu #8) — dan test create yang saat ini gagal jadi hijau (Task 0) |

## Tasks

### Task 0: Hapus `dd()` yang merusak fitur Tambah UKM *(bloker, di luar 8 isu)*
- **Action**: Hapus baris `dd($request->all());` di `UkmController.php:44`. Tidak ada perubahan logika lain.
- **Mirror**: Method `store()` lain di repo (`UkmMemberController::store`) — langsung masuk `try` tanpa debug statement.
- **Validate**: `php artisan test --filter=UkmCrudTest` → `test_admin_bisa_membuat_ukm_dengan_satu_field_wajib` berubah dari fatal error menjadi PASS. Ini RED yang **sudah ada** sebelum saya menyentuh apapun; tidak perlu menulis test baru.

---

### Task 1: Guard scanner menolak jadwal belum disetujui *(isu #4a)*
- **Action**: RED — tambah test di `UkmScanTest.php`: pelatih membuka scanner untuk jadwal `draft`/`menunggu`/`ditolak` ditolak, `disetujui` diizinkan; termasuk untuk `store()` (POST scan). GREEN — di `UkmScanController::show()` dan `store()`, setelah cek `isStaffOfUkm`, tambah guard status. Untuk `show()` gunakan `redirect()->route('admin.ukm.show', $jadwal->ukm_id)->with('error', ...)`; untuk `store()` gunakan `redirect()->back()->with('error', ...)` konsisten dengan penanganan error scan lain.
- **Mirror**: Guard status `UkmJadwalController.php:80-83` (redirect + flash error, bukan abort). Pesan menyebut status saat ini agar Pelatih tahu langkah berikutnya.
- **Validate**: `php artisan test --filter=UkmScanTest`

### Task 2: Jadwal belum disetujui hilang dari dashboard mahasiswa *(isu #4b)*
- **Action**: RED — test di `UkmMahasiswaTest.php`: mahasiswa anggota aktif dengan 1 jadwal `disetujui` + 1 `draft` + 1 `ditolak` hanya melihat yang `disetujui`. GREEN — tambah `->where('status_verifikasi', 'disetujui')` pada closure eager-load di `UkmMahasiswaController::index():23-26`.
- **Mirror**: Closure filter yang sudah ada di baris yang sama (`->where('tanggal','>=',...)`)
- **Validate**: `php artisan test --filter=UkmMahasiswaTest`

### Task 3: Tombol Scanner disembunyikan saat jadwal belum disetujui *(isu #4c — UI)*
- **Action**: Bungkus tombol Scanner (`jadwal_table.blade.php:50-53`) dengan `@if ($jadwal->status_verifikasi === 'disetujui')`. Pelengkap Task 1, bukan pengganti — guard server-side tetap otoritatif.
- **Mirror**: Conditional rendering `@if ($jadwal->status_verifikasi === 'draft')` di baris 54 file yang sama.
- **Validate**: Feature test Task 1 menutupi sisi server; sisi UI diverifikasi manual QA.

---

### Task 4: Jadwal draft disembunyikan dari antrian verifikasi Pembina *(isu #3)*
- **Action**: RED — 2 test di `UkmVerifikasiTest.php`: (a) jadwal `draft` tidak muncul di halaman verifikasi, (b) POST approve atas jadwal `draft` ditolak dan status tidak berubah. GREEN — tambah `->where('status_verifikasi', '!=', 'draft')` di query `index():35-38`; di `update()` tambah guard sebelum `$jadwal->update()`: jika `status_verifikasi === 'draft'` → redirect dengan error "Jadwal ini belum diajukan untuk verifikasi oleh Pelatih."
- **Mirror**: Guard status `UkmJadwalController.php:80-83`; query filter `UkmController::index():22-28`
- **Validate**: `php artisan test --filter=UkmVerifikasiTest`

---

### Task 5: Reaktivasi anggota per-user dan massal *(isu #1)*
- **Action**: RED — 4 test di `UkmMemberTest.php`: (a) admin mengaktifkan 1 anggota nonaktif di UKM aktif, (b) admin mengaktifkan semua anggota sekaligus, (c) reaktivasi ditolak saat UKM masih nonaktif *(keputusan user)*, (d) mahasiswa tidak bisa mengaktifkan. GREEN — tambah 2 method di `UkmMemberController`: `aktifkan($ukmId, $memberId)` dan `aktifkanSemua($ukmId)`. Keduanya memuat `Ukm::findOrFail($ukmId)` dan menolak jika `!$ukm->is_active`. Tambah 2 route PATCH.
- **Mirror**: Struktur `try/catch` + `redirect()->back()->with(...)` dari `UkmMemberController::destroy():32-42`. Route mengikuti pola `ukm/{ukm}/anggota/{member}` yang sudah ada di `web.php:107-108`.
- **Validate**: `php artisan test --filter=UkmMemberTest`

### Task 6: Hapus jadwal berstatus draft *(isu #7)*
- **Action**: RED — 3 test di `UkmJadwalTest.php`: (a) pelatih UKM menghapus jadwal `draft` berhasil (cek `assertDatabaseMissing` untuk `ukm_jadwals` dan `ukm_presensis` karena cascade FK), (b) menghapus jadwal `disetujui` ditolak, (c) pelatih UKM lain ditolak 403. GREEN — tambah `UkmJadwalController::destroy(UkmJadwal $jadwal)` dengan scoping identik `ajukanVerifikasi()`, guard `status_verifikasi === 'draft'`, lalu `$jadwal->delete()`. Tambah route DELETE. Tambah tombol Hapus di `jadwal_table.blade.php` dalam blok `@if draft` yang sudah ada, dengan `onsubmit confirm`.
- **Mirror**: Scoping + guard `UkmJadwalController::ajukanVerifikasi():66-89` (persis, hanya beda aksi akhir). Tombol mengikuti pola hapus UKM di `ukm_table.blade.php`.
- **Validate**: `php artisan test --filter=UkmJadwalTest`

---

### Task 7: Catatan pembina terbaca Pelatih & Admin *(isu #5)*
- **Action**: RED — test di `UkmJadwalTest.php`: setelah jadwal ditolak dengan catatan, halaman detail UKM menampilkan teks catatan (`assertSee`). GREEN — tambah kolom "Catatan Pembina" di `jadwal_table.blade.php` setelah kolom Status; tampilkan `{{ $jadwal->catatan_pembina ?? '-' }}`. Sesuaikan `colspan` di blok `@empty` dari 8 → 9.
- **Mirror**: Kolom Lokasi (`jadwal_table.blade.php:35-37`) yang memakai `?? '-'`. Blade auto-escape menangani XSS untuk catatan yang diisi pembina.
- **Validate**: `php artisan test --filter=UkmJadwalTest`

### Task 8: Detail lengkap jadwal mendatang mahasiswa *(isu #6)*
- **Action**: RED — test di `UkmMahasiswaTest.php`: halaman UKM Saya menampilkan lokasi dan jam selesai. GREEN — di `ukm/index.blade.php:32-34`, ubah baris waktu menjadi `{{ tanggal }} · {{ mulai_acara }} - {{ selesai_acara }}` dan tambah baris lokasi dengan ikon, memakai `?? '-'` karena `lokasi` nullable.
- **Mirror**: Format waktu `jadwal_table.blade.php:33`; fallback `?? '-'` dari baris 36.
- **Validate**: `php artisan test --filter=UkmMahasiswaTest`

### Task 9: Search-dropdown untuk mahasiswa *(isu #2)*
- **Action**: Inisialisasi Choices.js pada `#user_id_mhs` di `ukm-tambah-anggota.blade.php`, mengikuti pola `getStafChoices()` yang sudah ada tapi **tanpa filter peran** (mahasiswa hanya satu peran). Inisialisasi saat `toggleUserOptions('anggota')` dipanggil / modal pertama dibuka.
- **Mirror**: `getStafChoices():74-93` di file yang sama — lazy singleton + opsi `searchEnabled: true`.
- **Validate**: Manual QA (interaksi JS murni, tidak ada test PHP untuk perilaku browser). `npm run build` harus sukses.

---

### Task 10: Pagination 20 data untuk seluruh tabel UKM *(isu #8)*
- **Action**:
  - `UkmController::index():30` — `paginate(10)` → `paginate(20)`
  - `UkmController::show()` — ganti eager-load `['members.user','jadwals']` dengan dua query terpaginasi memakai **nama parameter halaman terpisah** *(keputusan user)*: `$ukm->members()->with('user')->paginate(20, ['*'], 'anggota_page')` dan `$ukm->jadwals()->paginate(20, ['*'], 'jadwal_page')`, dilewatkan ke view sebagai variabel tersendiri.
  - `UkmVerifikasiController::index():35-38` — `->get()` → `->paginate(20)`
  - `UkmMahasiswaController::riwayat():42` — `paginate(15)` → `paginate(20)`
  - Blade: ubah `@forelse ($ukm->members ...)` → `@forelse ($anggotas ...)` dan `@forelse ($ukm->jadwals ...)` → `@forelse ($jadwals ...)`; tambah `{{ $x->links() }}` di ketiga tabel yang belum punya.
- **Mirror**: `UkmController.php:30` + `ukm_table.blade.php:86` (pola paginate + links yang sudah terbukti di repo ini).
- **Catatan**: `UkmLaporanController:45` **sengaja tetap `->get()`** — laporan PDF butuh seluruh data (out-of-scope di PRD). Jadwal mendatang di `ukm/index.blade.php` tetap `->take(5)` karena ringkasan kartu, bukan tabel.
- **Validate**: RED — test di `UkmCrudTest.php`: buat 25 UKM, assert halaman 1 berisi 20. `php artisan test --filter=UkmCrudTest`

---

### Task 11: Validasi menyeluruh
- **Action**: Jalankan seluruh test suite dan build produksi. Verifikasi tidak ada regresi pada laporan PDF dan dashboard yang mengonsumsi relasi `members`/`jadwals`.
- **Validate**: lihat blok Validation di bawah.

## Validation

```bash
# Per-task (dijalankan saat RED dan GREEN)
php artisan test --filter=UkmScanTest
php artisan test --filter=UkmMahasiswaTest
php artisan test --filter=UkmVerifikasiTest
php artisan test --filter=UkmJadwalTest
php artisan test --filter=UkmMemberTest
php artisan test --filter=UkmCrudTest

# Gerbang akhir
php artisan test          # harus 100% hijau, 0 gagal
npm run build             # aset Vite harus terkompilasi tanpa error
```

## Risks

| Risk | Likelihood | Mitigation |
|---|---|---|
| **Test suite saat ini sudah merah** karena `dd()` di `UkmController::store()` — asumsi "0 regresi" di PRD tidak valid sampai Task 0 selesai | Pasti (terverifikasi) | Task 0 dikerjakan lebih dulu sebelum task lain; baseline dicatat sebelum & sesudah |
| Mengganti eager-load `$ukm->jadwals` dengan paginator mengubah tipe variabel di blade — view lain yang mengonsumsi relasi ini bisa rusak | Sedang | Variabel baru (`$anggotas`, `$jadwals`) dilewatkan terpisah; relasi asli pada `$ukm` tidak dihapus, sehingga konsumen lain (laporan PDF) tetap aman. Diverifikasi test suite penuh |
| Guard scanner memutus alur Pelatih yang terbiasa scan tanpa persetujuan | Sedang | Pesan error menyebut status saat ini + langkah yang diperlukan; perlu disosialisasikan sebelum deploy |
| "Aktifkan Semua" mengaktifkan anggota yang sengaja dinonaktifkan sebelum UKM mati | Sedang | Konfirmasi eksplisit di UI + tersedia opsi per-user sebagai alternatif presisi |
| Data presensi lama pada jadwal non-`disetujui` tetap ada (perbaikan hanya mencegah yang baru) | Sedang | Tercatat sebagai Open Question di PRD; perlu query audit pasca-deploy |

## Acceptance
- [ ] Task 0 selesai — `UkmCrudTest` tidak lagi fatal error, fitur Tambah UKM berfungsi
- [ ] Semua task 1–11 selesai
- [ ] `php artisan test` 100% hijau, 0 gagal
- [ ] `npm run build` sukses
- [ ] Pola di-mirror dari kode yang ada, bukan diciptakan ulang
- [ ] Manual QA: dropdown mahasiswa (Task 9) dan tombol Scanner tersembunyi (Task 3)
