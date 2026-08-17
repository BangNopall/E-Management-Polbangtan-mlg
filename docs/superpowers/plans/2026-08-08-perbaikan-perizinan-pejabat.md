# Perbaikan Perizinan Mahasiswa & Data Pejabat UI Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Fix student leave submission when Dosen PA is unassigned, clarify Dosen PA / Operator resolver fallback across forms, explain Pejabat data fields in UI, and fix HTML nesting padding bug on Pejabat edit modal.

**Architecture:** Remove `dosen_pa_id` requirement from `StorePengajuanIzinRequest` Gerbang 1 validation since `ApproverResolver` safely falls back to Operator role users (`role_id = 2`). Add helper text to Admin Jenis Izin form and Student Create Izin view. Refactor `admin/pejabat/index.blade.php` to move Edit Pejabat modals outside `<tbody>`, ensuring valid HTML syntax and restoring Flowbite `p-4 md:p-5` modal padding.

**Tech Stack:** PHP 8.4, Laravel 11/13, Tailwind CSS v4, Alpine.js, PHPUnit.

## Global Constraints

- PSR-12 standard, strict validation rules.
- Maintain existing `ApproverResolver` fallback logic (`User::OPERATOR_ROLE_ID`).
- Keep Tailwind / Flowbite styling conventions (`bg-white border-2 rounded-lg p-3`, `p-4 md:p-5`).
- Never run `migrate:fresh` or `db:seed` on non-test environments.

---

### Task 1: Fix Backend Submission Validation for Unassigned Dosen PA

**Files:**
- Modify: `app/Http/Requests/StorePengajuanIzinRequest.php:60-70`
- Test: `tests/Feature/Izin/IzinMahasiswaTest.php`

**Interfaces:**
- Consumes: `App\Services\Izin\ApproverResolver::resolveDosenPa()`
- Produces: Successful permission request creation when `user.kelas.dosen_pa_id` is `null` (resolving fallback to Operator).

- [ ] **Step 1: Write failing test for submission without Dosen PA assigned on class**

Add a new test method to `tests/Feature/Izin/IzinMahasiswaTest.php`:

```php
    public function test_pengajuan_izin_sukses_meskipun_kelas_belum_memiliki_dosen_pa(): void
    {
        // Arrange: Ciptakan operator user untuk fallback resolver
        $operator = User::factory()->create(['role_id' => \App\Models\User::OPERATOR_ROLE_ID, 'name' => 'Operator Staff']);

        // Set dosen_pa_id pada kelas mahasiswa menjadi null
        $this->student->kelas->update(['dosen_pa_id' => null]);

        $payload = [
            'jenis_izin_id' => $this->jenisBiasa->id,
            'keperluan' => 'Izin ke apotek tanpa Dosen PA',
            'tujuan_lokasi' => 'Apotek K-24',
            'waktu_berangkat' => now()->addHours(3)->toDateTimeString(),
            'waktu_kembali' => now()->addHours(6)->toDateTimeString(),
        ];

        // Act
        $response = $this->actingAs($this->student)->post(route('home.izin.store'), $payload);

        // Assert
        $pengajuan = PengajuanIzin::where('user_id', $this->student->id)->where('keperluan', 'Izin ke apotek tanpa Dosen PA')->first();
        $this->assertNotNull($pengajuan);
        $response->assertRedirect(route('home.izin.show', $pengajuan->id));

        // Approver langkah 1 harus mengarah ke operator user (fallback)
        $this->assertEquals($operator->id, $pengajuan->approvals->first()->approver_user_id);
    }
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --filter test_pengajuan_izin_sukses_meskipun_kelas_belum_memiliki_dosen_pa`  
Expected: FAIL with session error on `profil`.

- [ ] **Step 3: Modify StorePengajuanIzinRequest to remove dosen_pa_id check from Gerbang 1**

In `app/Http/Requests/StorePengajuanIzinRequest.php`, update lines 58-71:

```php
            // Gerbang 1: Profil Lengkap
            $user->loadMissing('kelas');
            if (
                !$user->prodi_id ||
                !$user->kelas_id ||
                !$user->blok_ruangan_id
            ) {
                $v->errors()->add(
                    'profil',
                    'Profil Anda belum lengkap (Prodi, Kelas, atau Blok Ruangan belum terdata). Silakan hubungi admin asrama.'
                );
                return;
            }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --filter test_pengajuan_izin_sukses_meskipun_kelas_belum_memiliki_dosen_pa`  
Expected: PASS.

- [ ] **Step 5: Run all IzinMahasiswaTest suite**

Run: `php artisan test --filter IzinMahasiswaTest`  
Expected: PASS (all tests green).

- [ ] **Step 6: Commit changes**

```bash
git add app/Http/Requests/StorePengajuanIzinRequest.php tests/Feature/Izin/IzinMahasiswaTest.php
git commit -m "fix(izin): allow student leave submission when class has no assigned dosen_pa"
```

---

### Task 2: Enhance Dosen PA / Operator Resolver Information on Admin & Student Views

**Files:**
- Modify: `resources/views/admin/izin/jenis/form.blade.php:118-123`
- Modify: `resources/views/izin/create.blade.php:21-24`

**Interfaces:**
- Consumes: `ApproverResolver` fallback information
- Produces: Clear UI hints explaining automatic fallback to Operator when Dosen PA is unassigned.

- [ ] **Step 1: Update Admin Jenis Izin Form (`resources/views/admin/izin/jenis/form.blade.php`)**

In `resources/views/admin/izin/jenis/form.blade.php`, update lines 118-123 to include fallback text and helper note:

```html
                                <div class="md:col-span-1">
                                    <label class="block font-bold text-gray-800 mb-1">Resolver Penandatangan</label>
                                    <select :name="'steps[' + index + '][resolver]'" x-model="step.resolver" class="w-full bg-white border border-gray-300 rounded-lg p-2 text-xs" required>
                                        <option value="dosen_pa">Dosen PA Mahasiswa (Fallback: Operator)</option>
                                        <option value="pembina_ukm">Pembina UKM Terkait</option>
                                        <option value="petugas_jaga">Petugas Jaga / Piket (Fallback: Pelatih/Operator)</option>
                                        <option value="pejabat">Pejabat Berdasarkan Jabatan</option>
                                    </select>
                                    <p x-show="step.resolver === 'dosen_pa'" class="text-[10px] text-gray-500 mt-1">
                                        ℹ️ Memilih Dosen PA mahasiswa. Jika kelas belum diset Dosen PA, otomatis dialihkan ke akun staf ber-role Operator.
                                    </p>
                                </div>
```

- [ ] **Step 2: Update Student Create Izin Alert (`resources/views/izin/create.blade.php`)**

In `resources/views/izin/create.blade.php`, update lines 17-24 to clarify that missing Dosen PA does not block submission:

```html
    @if ($profileIncomplete)
        <div class="p-4 mb-4 text-sm text-amber-800 rounded-lg bg-amber-50 border border-amber-200" role="alert">
            <div class="flex items-center font-bold mb-1">
                <i class="ri-error-warning-line text-lg mr-2 text-amber-600"></i> Profil Belum Lengkap!
            </div>
            <p>Data Prodi, Kelas, atau Blok Ruangan Anda belum terdaftar lengkap di sistem. Anda tidak dapat mengirimkan pengajuan izin sebelum data profil Anda dilengkapi oleh admin asrama.</p>
        </div>
    @endif
```

- [ ] **Step 3: Test views rendering**

Run: `php artisan test --filter IzinMahasiswaTest`  
Expected: PASS.

- [ ] **Step 4: Commit changes**

```bash
git add resources/views/admin/izin/jenis/form.blade.php resources/views/izin/create.blade.php
git commit -m "docs(ui): clarify dosen_pa and operator resolver fallback on jenis izin and create forms"
```

---

### Task 3: Refactor Data Pejabat View (Fix Edit Modal Padding & Add Field Hints)

**Files:**
- Modify: `resources/views/admin/pejabat/index.blade.php`
- Test: `tests/Feature/Izin/PejabatTest.php`

**Interfaces:**
- Consumes: Pejabat model list (`$pejabats`) and user options (`$users`)
- Produces: Clean HTML5 table markup with edit modals rendered outside `<table>`, fixing Flowbite modal padding (`p-4 md:p-5`) and adding clear descriptions for `lingkup`, `id_lingkup`, `mulai_menjabat`, and `selesai_menjabat`.

- [ ] **Step 1: Check existing PejabatTest**

Run: `php artisan test --filter PejabatTest`  
Expected: PASS.

- [ ] **Step 2: Refactor `resources/views/admin/pejabat/index.blade.php` structure**

1. Move `<div id="editPejabatModal{{ $pejabat->id }}" ...>` blocks out of `<tbody>` rows.
2. Render modals after `</div>` of table box.
3. Add explanatory helper text on form inputs for both Tambah and Edit modals:
   - **Lingkup**: `<p class="text-[10px] text-gray-500 mt-1">Cakupan wewenang: Global (seluruh kampus), Prodi (jurusan tertentu), Blok (ruangan asrama).</p>`
   - **ID Lingkup**: `<p class="text-[10px] text-gray-500 mt-1">Isi ID Prodi atau ID Blok jika Lingkup bertipe Prodi/Blok. Dikosongkan jika Global.</p>`
   - **Mulai Menjabat**: `<p class="text-[10px] text-gray-500 mt-1">Tanggal awal resmi menjabat.</p>`
   - **Selesai Menjabat**: `<p class="text-[10px] text-gray-500 mt-1">Tanggal akhir menjabat. Kosongkan jika masih aktif sampai sekarang.</p>`

- [ ] **Step 3: Run PejabatTest to verify no breaking changes**

Run: `php artisan test --filter PejabatTest`  
Expected: PASS.

- [ ] **Step 4: Commit changes**

```bash
git add resources/views/admin/pejabat/index.blade.php
git commit -m "fix(ui): refactor pejabat edit modal HTML outside tbody to fix padding and add field hints"
```
