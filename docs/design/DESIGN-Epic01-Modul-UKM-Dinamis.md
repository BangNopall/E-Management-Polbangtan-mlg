# Design Document — Epic 01: Modul UKM Dinamis

**Proyek:** E-Management Polbangtan-mlg
**Epic:** 01 — Dynamic UKM Generator
**Penulis:** Muhammad Naufal Mathara R
**Status:** Proposed
**Tanggal:** 3 Agustus 2026
**Basis analisis:** branch `nopal/new-option`

---

## 0. TL;DR (baca ini dulu)

Epic 01 memang "mirip Kegiatan Wajib" — tapi justru **modul Kegiatan Wajib yang ada sekarang adalah contoh cara yang salah**, dan PRD Anda (poin *Out of Scope*: "Pembuatan 50 template UKM statis") secara implisit meminta Anda **jangan menirunya**.

Rekomendasi inti: bangun **satu mesin absensi generik** (4 tabel: `ukms`, `ukm_members`, `ukm_jadwals`, `ukm_presensis`). Menambah UKM baru = **1 baris INSERT**, tanpa tabel baru, tanpa `switch`, tanpa view kamera baru. Ini kebalikan total dari pola Kegiatan Wajib yang butuh migrasi + model + view + 5 cabang `switch` setiap kali menambah 1 jenis kegiatan.

Dimensi baru yang **tidak ada** di Kegiatan Wajib dan wajib Anda tambahkan: **keanggotaan (membership)**. Kegiatan Wajib meng-enroll semua orang per-blok otomatis; UKM bersifat *opt-in per-mahasiswa*.

---

## 1. Analisis Kondisi Saat Ini (yang jadi dasar keputusan)

### 1.1 Apa yang dilakukan Kegiatan Wajib hari ini

| Komponen | Implementasi sekarang |
|---|---|
| Jenis kegiatan | Hard-coded `ENUM('Apel','Upacara','Senam')` di kolom `jadwal_kegiatan_asramas.jenis_kegiatan` |
| Absensi | **3 tabel terpisah** berskema identik: `presensi_apels`, `presensi_upacaras`, `presensi_senams` |
| Scanner QR | **3 view** (`kamera-apel/upacara/senam`) + 3 route + 3 method controller nyaris identik |
| Logika tulis presensi | `pushPresense()` punya **3 cabang `if-elseif`** yang cuma beda nama Model |
| Buat jadwal | `createJadwalKegiatanStore()` punya `switch($kegiatan)` 3 cabang |
| Laporan | 3 template PDF (`generate-apel/senam/upacara`) |

### 1.2 Biaya menambah 1 jenis kegiatan baru hari ini

Untuk menambah "Renang" saja, dengan pola sekarang Anda harus menyentuh **±7 tempat**: migrasi tabel baru → model baru → view kamera baru → route baru → cabang `switch` di `createJadwalKegiatanStore` → cabang di `pushPresense` → cabang di `editDataKegiatanWajib` → template laporan baru. **Itulah yang PRD larang** ("50 template statis" = out of scope).

### 1.3 Aset yang BISA dipakai ulang (biar efisien)

- **QR mahasiswa tidak perlu diubah sama sekali.** Payload `{user_id, date, time, status, scanner}` sudah generik. Kamera UKM cukup tahu "saya sedang absen untuk jadwal UKM #X".
- Pola validasi jendela waktu (`diffInSeconds` ≤ 30 detik + cek `mulai_acara`/`selesai_acara`) di `validateQR()` bisa disalin apa adanya.
- Pola `html5-qrcode`, layout `layouts/main.blade.php`, middleware `role:`, ekspor PDF (`barryvdh/laravel-dompdf` + `FromView`) — semua reusable.

---

## 2. Brainstorming — Menantang Asumsi (mode: thinking partner)

> Catatan Anda: *"modul fitur Epic 01 ini mungkin mirip seperti modul fitur yang udah ada yaitu kegiatan wajib."*

Betul secara **domain** (jadwal → absen → rekap → verifikasi). Tapi kalau "mirip" diartikan **"salin polanya"**, Anda akan mereplikasi utang teknis. Mari uji 3 asumsi.

**Asumsi 1 — "UKM = jenis kegiatan, jadi tambahkan saja ke enum/pola yang ada."**
Salah arah. Apel/Senam/Upacara jumlahnya tetap 3 dan dikelola developer. UKM **dinamis, dibuat admin saat runtime**, jumlahnya tak terbatas. Entitas runtime tidak boleh hidup di enum atau di nama tabel. → UKM harus jadi **baris data**, bukan tipe di kode.

**Asumsi 2 — "Absensi UKM butuh tabel presensi sendiri (seperti presensi_apels)."**
Salah. Justru 3 tabel presensi itu **satu sumber duplikasi**. Absensi seharusnya di-key oleh **jadwal**, bukan oleh jenis. Satu tabel `ukm_presensis` melayani **semua** UKM selamanya.

**Asumsi 3 (yang PRD lupa sebutkan) — "Anggota UKM sudah otomatis ada."**
US 1.3 bilang *"Sebagai Mahasiswa anggota UKM..."* — tapi PRD **tidak pernah mendefinisikan cara mahasiswa jadi anggota**. Kegiatan Wajib memakai `User::where('blok_ruangan_id', $blok)` (semua orang di blok). UKM tidak begitu — ini **opt-in**. Tanpa mekanisme membership, US 1.2 (fan-out presensi ke anggota) dan US 1.3 (absen anggota) tidak bisa jalan. **Ini gap PRD yang harus Anda tutup** (lihat §7 US tambahan 1.0).

**Teknik SCAMPER — "Combine":** peran Pelatih & Pembina tidak perlu tabel/role global sendiri. Gabungkan jadi kolom `peran` pada tabel keanggotaan. Satu orang bisa jadi *pelatih* di UKM Renang sekaligus *anggota* di UKM Silat — role di-scope **per-UKM**, bukan global.

**Kesimpulan brainstorming:** Epic 01 sebenarnya adalah kesempatan membangun **versi generik yang benar** dari Kegiatan Wajib. Bangun mesinnya untuk UKM sekarang; Kegiatan Wajib bisa dimigrasikan ke mesin yang sama nanti (di luar scope Epic ini).

---

## 3. Persona & Jobs-to-be-Done (ringkas)

| Persona | Job-to-be-Done | Menyentuh US |
|---|---|---|
| **Admin Asrama** | "Saat UKM baru dibentuk, saya ingin cukup mengetik namanya dan sistem langsung siap dipakai absen & jadwal." | 1.1 |
| **Pelatih UKM** | "Saya ingin menjadwalkan latihan/kegiatan wajib untuk anggota saya, lalu tinggal scan kehadiran." | 1.2, 1.3 |
| **Mahasiswa (anggota)** | "Saya ingin absen UKM pakai QR yang **sama** dengan QR asrama saya, tanpa aplikasi/kode baru." | 1.3 |
| **Pembina/Verifikator** | "Saya ingin melihat & memverifikasi laporan kegiatan UKM binaan saya sebagai bukti akuntabilitas." | 1.4 |

**JTBD kunci Admin** menegaskan syarat desain: *create UKM harus 1 aksi*, bukan wizard 7 langkah. Kalau desain memaksa admin bikin tabel/isi enum → desain gagal memenuhi job-nya.

---

## 4. ADR-005 — Skema Penyimpanan UKM Dinamis

**Status:** Proposed · **Deciders:** Pak Yongki, Naufal · **Tanggal:** 3 Agustus 2026

### Context
Epic 01 menuntut: membuat entitas UKM baru **otomatis meng-generate** fungsi Absensi, Jadwal, dan Kegiatan Wajib — tanpa membuat 50 template statis (out of scope PRD). Codebase punya pola Kegiatan Wajib yang hard-coded per-jenis (3 tabel, 3 view, banyak `switch`).

### Decision
Adopsi **skema generik terpadu** (Opsi B): UKM sebagai baris data; jadwal & absensi generik yang di-key oleh `ukm_id`/`ukm_jadwal_id`, bukan oleh nama/jenis UKM. Skema dirancang agar Kegiatan Wajib **dapat** dimigrasikan ke mesin yang sama di masa depan (Opsi C), tapi migrasi itu **di luar scope Epic 01**.

### Opsi yang Dipertimbangkan

**Opsi A — Replikasi pola Kegiatan Wajib (tabel/enum per-jenis)**

| Dimensi | Penilaian |
|---|---|
| Kompleksitas | Rendah di awal, meledak seiring waktu |
| Skalabilitas | Buruk — tiap UKM baru = migrasi + kode |
| Kesesuaian PRD | **Melanggar** ("50 template statis" out of scope) |

Pros: paling familiar (tinggal copy-paste). Cons: langsung menciptakan utang teknis; kontradiktif dengan tujuan Epic. → **Ditolak.**

**Opsi B — Skema generik terpadu (DIPILIH)**

| Dimensi | Penilaian |
|---|---|
| Kompleksitas | Sedang di awal (4 tabel dirancang benar), rendah selamanya setelah itu |
| Skalabilitas | Sangat baik — UKM baru = 1 INSERT, 0 baris kode |
| Kesesuaian PRD | Tepat memenuhi "generator dinamis" |
| Familiaritas tim | Menengah — butuh sedikit disiplin relasi Eloquent |

Pros: menambah UKM tanpa kode; satu jalur absensi; menutup gap membership. Cons: butuh desain relasi yang benar sejak awal; menyisakan duplikasi Kegiatan Wajib untuk sementara. → **Dipilih.**

**Opsi C — Refactor Kegiatan Wajib + UKM ke satu mesin sekarang**

| Dimensi | Penilaian |
|---|---|
| Kompleksitas | Tinggi — menyentuh kode produksi yang sudah jalan |
| Risiko | Tinggi — regresi pada absensi Apel/Senam/Upacara yang dipakai harian |
| Nilai jangka panjang | Terbaik — menghapus semua duplikasi |

Pros: satu mesin untuk semuanya. Cons: risiko regresi tinggi, scope membengkak melewati Epic 01. → **Ditunda** (kandidat ADR-006 nanti).

### Trade-off Analysis
Opsi B adalah titik tengah yang sama filosofinya dengan **ADR-004 Anda** (SSO handoff, bukan migrasi penuh konseling): pilih jalur **risiko rendah, nilai tinggi, scope terkendali**, dan terima duplikasi sementara secara sadar. Kita bangun "cara yang benar" untuk fitur baru (UKM) tanpa membongkar yang sudah jalan (Kegiatan Wajib).

### Consequences
- **Lebih mudah:** menambah/menonaktifkan UKM, absensi lintas-UKM, laporan generik, penambahan jenis jadwal.
- **Lebih sulit sementara:** ada dua "gaya" absensi berdampingan (lama per-jenis vs baru generik) sampai Opsi C dijalankan.
- **Yang perlu ditinjau ulang nanti:** migrasi Kegiatan Wajib ke mesin generik (ADR-006), dan apakah `peran` di membership cukup atau perlu tabel role-per-UKM khusus.

### Action Items
1. [ ] Buat 4 migrasi (§5.2) + 4 model (§5.3)
2. [ ] Tambah/putuskan role Pembina (§7, keputusan terbuka)
3. [ ] Implementasi CRUD UKM + membership → scheduling → scanner generik → verifikasi (§6 roadmap)

---

## 5. System Design

### 5.1 ERD (Entity-Relationship)

```
users ────────┐ (1)                         (1) ┌──────── users
              │                                  │  (created_by / verified_by)
              │ M                              N │
        ┌─────┴───────┐                    ┌─────┴────────┐
        │ ukm_members │  N ───────────── 1 │     ukms     │
        │─────────────│                    │──────────────│
        │ ukm_id  FK ─┼───────────────────>│ id (PK)      │
        │ user_id FK  │                    │ nama (unique)│
        │ peran ENUM  │                    │ slug         │
        │  (anggota/  │                    │ deskripsi    │
        │   pelatih/  │                    │ is_active    │
        │   pembina)  │                    │ created_by FK│
        │ status ENUM │                    └─────┬────────┘
        └─────────────┘                          │ 1
                                                 │
                                                 │ N
                                          ┌──────┴────────┐
                                          │  ukm_jadwals  │
                                          │───────────────│
                            ┌────────────>│ id (PK)       │
                            │             │ ukm_id     FK │
                            │             │ judul         │
                            │             │ jenis ENUM    │  ('latihan' | 'kegiatan_wajib')
                            │             │ tanggal       │
                            │             │ mulai_acara   │
                            │             │ selesai_acara │
                            │             │ lokasi        │
                            │             │ status_verif  │  ('draft'|'menunggu'|'disetujui'|'ditolak')
                            │             │ verified_by FK│
                            │             │ created_by  FK│
                            │             └──────┬────────┘
                            │                    │ 1
                            │                    │ N
                            │             ┌──────┴─────────┐
                            │             │ ukm_presensis  │
                            │ N           │────────────────│
                            └─────────────┤ ukm_jadwal_id  │  UNIQUE(ukm_jadwal_id, user_id)
                        (user_id FK)      │ user_id     FK │
                                          │ status_kehadiran│ ('Hadir'|'Izin'|'Alpha')
                                          │ jam_kehadiran  │
                                          └────────────────┘
```

**Prinsip kunci:** nama UKM hanya hidup di `ukms.nama` (data). Tidak ada nama UKM di enum, di nama tabel, atau di `switch`. `ukm_presensis` adalah **satu** tabel untuk semua absensi UKM selamanya.

### 5.2 Migrasi (siap tempel)

Perintah generate:

```bash
php artisan make:migration create_ukms_table
php artisan make:migration create_ukm_members_table
php artisan make:migration create_ukm_jadwals_table
php artisan make:migration create_ukm_presensis_table
```

**`create_ukms_table`**

```php
Schema::create('ukms', function (Blueprint $table) {
    $table->id();
    $table->string('nama')->unique();
    $table->string('slug')->unique();
    $table->text('deskripsi')->nullable();
    $table->boolean('is_active')->default(true);
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();
});
```

**`create_ukm_members_table`** — dimensi membership (yang tidak ada di Kegiatan Wajib)

```php
Schema::create('ukm_members', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ukm_id')->constrained('ukms')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->enum('peran', ['anggota', 'pelatih', 'pembina'])->default('anggota');
    $table->enum('status', ['aktif', 'nonaktif'])->default('aktif');
    $table->date('tanggal_bergabung')->nullable();
    $table->timestamps();

    $table->unique(['ukm_id', 'user_id']); // 1 orang 1 baris per UKM
    $table->index(['ukm_id', 'peran', 'status']);
});
```

**`create_ukm_jadwals_table`** — generalisasi `jadwal_kegiatan_asramas`

```php
Schema::create('ukm_jadwals', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ukm_id')->constrained('ukms')->cascadeOnDelete();
    $table->string('judul');
    $table->enum('jenis', ['latihan', 'kegiatan_wajib'])->default('latihan');
    $table->date('tanggal');
    $table->time('mulai_acara');
    $table->time('selesai_acara');
    $table->string('lokasi')->nullable();
    // Epic 1.4 — verifikasi pembina
    $table->enum('status_verifikasi', ['draft','menunggu','disetujui','ditolak'])->default('draft');
    $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamp('verified_at')->nullable();
    $table->text('catatan_pembina')->nullable();
    $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
    $table->timestamps();

    $table->index(['ukm_id', 'tanggal']);
});
```

**`create_ukm_presensis_table`** — SATU tabel untuk semua absensi UKM

```php
Schema::create('ukm_presensis', function (Blueprint $table) {
    $table->id();
    $table->foreignId('ukm_jadwal_id')->constrained('ukm_jadwals')->cascadeOnDelete();
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->enum('status_kehadiran', ['Hadir', 'Izin', 'Alpha'])->default('Alpha');
    $table->time('jam_kehadiran')->nullable();
    $table->timestamps();

    $table->unique(['ukm_jadwal_id', 'user_id']); // cegah presensi ganda
});
```

> **Konvensi:** saya pakai snake_case bersih (`ukm_id`, `ukm_jadwal_id`) dan Model PascalCase, sengaja **berbeda** dari inkonsistensi lama (`jadwalKegiatanAsrama_id`, model camelCase) untuk memulai modul baru yang bersih. FK `unique` + `index` adalah pengaman yang tidak dimiliki tabel presensi lama.

### 5.3 Model & Relasi (skeleton)

```php
// app/Models/Ukm.php
class Ukm extends Model {
    protected $guarded = ['id'];
    public function members()   { return $this->hasMany(UkmMember::class); }
    public function jadwals()   { return $this->hasMany(UkmJadwal::class); }
    // anggota aktif saja (dipakai saat fan-out presensi)
    public function anggotaAktif() {
        return $this->hasMany(UkmMember::class)
                    ->where('peran','anggota')->where('status','aktif');
    }
    public function pelatih() {
        return $this->hasMany(UkmMember::class)->where('peran','pelatih');
    }
}

// app/Models/UkmMember.php
class UkmMember extends Model {
    protected $guarded = ['id'];
    public function ukm()  { return $this->belongsTo(Ukm::class); }
    public function user() { return $this->belongsTo(User::class); }
}

// app/Models/UkmJadwal.php
class UkmJadwal extends Model {
    protected $guarded = ['id'];
    public function ukm()      { return $this->belongsTo(Ukm::class); }
    public function presensis(){ return $this->hasMany(UkmPresensi::class); }
    public function verifier() { return $this->belongsTo(User::class,'verified_by'); }
}

// app/Models/UkmPresensi.php
class UkmPresensi extends Model {
    protected $guarded = ['id'];
    public function jadwal(){ return $this->belongsTo(UkmJadwal::class,'ukm_jadwal_id'); }
    public function user()  { return $this->belongsTo(User::class); }
}
```

Tambahan relasi di `User.php`:

```php
public function ukmMemberships() { return $this->hasMany(UkmMember::class); }
public function ukms() {
    return $this->belongsToMany(Ukm::class, 'ukm_members')
                ->withPivot('peran','status')->withTimestamps();
}
```

### 5.4 Alur Utama (mapping ke User Story)

**US 1.1 — Create UKM (Admin).** `POST /ukm` → validasi `nama` unik → `Ukm::create([...])` (slug otomatis). **Selesai.** "Auto-generate scope" bukan berarti membuat tabel — mesin jadwal/presensi generik otomatis berlaku untuk `ukm_id` baru itu. Opsional: assign pelatih saat create.

**US 1.0 (tambahan) — Kelola Anggota.** Admin/pelatih menambahkan mahasiswa ke `ukm_members` (peran `anggota`). Bisa satuan atau impor massal (pola `UsersImport` yang ada bisa dicontoh). **Prasyarat US 1.2/1.3.**

**US 1.2 — Jadwal Pelatih.** Pelatih `POST /ukm/{ukm}/jadwal` → buat 1 `UkmJadwal` → **fan-out**: untuk tiap anggota aktif, `UkmPresensi::create([... 'status_kehadiran'=>'Alpha'])`. Bungkus dalam `DB::transaction()`. Ini sepadan dengan `createJadwalKegiatanStore` tapi generik & di-scope ke **anggota**, bukan blok.

**US 1.3 — Absensi UKM (scan).** Mahasiswa tampilkan **QR asrama yang sudah ada** (tanpa perubahan) → pelatih buka `/kamera-ukm/{ukm_jadwal}` → satu method generik: cek user adalah anggota jadwal itu, cek jendela waktu (salin logika `validateQR`, 30 detik), lalu `updateOrCreate` `ukm_presensis` set `Hadir` + `jam_kehadiran`. **Satu** method, bukan tiga.

**US 1.4 — Verifikasi Pembina.** Pelatih ubah `status_verifikasi` jadwal → `menunggu`. Pembina `/ukm/{ukm}/verifikasi` melihat daftar, `Approve/Reject` → set `disetujui/ditolak`, `verified_by`, `verified_at`. Ini yang membuat laporan jadi **bukti sah**.

### 5.5 Rancangan Route (RESTful, generik)

```php
// Admin: kelola UKM & anggota
Route::middleware('role:admin')->name('admin.')->group(function () {
    Route::resource('ukm', UkmController::class);                       // 1.1
    Route::post('ukm/{ukm}/anggota',        [UkmMemberController::class,'store']);   // 1.0
    Route::delete('ukm/{ukm}/anggota/{member}', [UkmMemberController::class,'destroy']);
});

// Pelatih (+admin): jadwal & scanner
Route::middleware('role:admin,pelatih')->name('admin.')->group(function () {
    Route::post('ukm/{ukm}/jadwal',   [UkmJadwalController::class,'store']);         // 1.2
    Route::get('kamera-ukm/{jadwal}', [UkmScanController::class,'show']);            // 1.3
    Route::post('api/kamera-ukm/{jadwal}', [UkmScanController::class,'store']);      // 1.3
});

// Pembina: verifikasi
Route::middleware('role:admin,pembina')->name('admin.')->group(function () {
    Route::get('ukm/{ukm}/verifikasi', [UkmVerifikasiController::class,'index']);    // 1.4
    Route::patch('ukm/jadwal/{jadwal}/verifikasi', [UkmVerifikasiController::class,'update']);
});

// Mahasiswa: lihat UKM & riwayat (QR pakai yang sudah ada, tanpa route baru)
Route::middleware('role:user')->name('home.')->group(function () {
    Route::get('ukm-saya',            [UkmMahasiswaController::class,'index']);
    Route::get('ukm-saya/riwayat',    [UkmMahasiswaController::class,'riwayat']);
});
```

Bandingkan: modul lama butuh 6 route kamera untuk 3 jenis. Modul baru butuh **1 pasang route kamera** untuk **tak-hingga** UKM.

### 5.6 Peta Reuse (kunci efisiensi)

| Kebutuhan | Reuse dari | Perubahan |
|---|---|---|
| QR mahasiswa | `QRController::kodeqr()` | **Tidak ada** |
| UI kamera scan | `admin/kamera-*.blade.php` | Clone → **1** `kamera-ukm.blade.php` berparameter `ukm_jadwal_id` |
| Validasi jendela waktu | `QRControllerKegiatan::validateQR()` | Salin pola, generalisasi (hapus `switch`) |
| Fan-out presensi | `createJadwalKegiatanStore()` | Generalisasi: loop **anggota**, 1 tabel |
| Laporan PDF | `barryvdh/laravel-dompdf` + `FromView` | 1 template generik ber-`ukm_id` |
| Otorisasi | middleware `role:` | + cek scope keanggotaan per-UKM |

### 5.7 Skala & Keandalan
- Skala: satu asrama (~ratusan mahasiswa, puluhan UKM). `ukm_presensis` dengan index `(ukm_jadwal_id, user_id)` lebih dari cukup — **tidak perlu** sharding/queue/cache khusus.
- Keandalan: bungkus fan-out & verifikasi dalam `DB::transaction()`; constraint `unique` mencegah presensi/keanggotaan ganda di level DB (pengaman yang tidak ada di modul lama).

---

## 6. Roadmap Implementasi (berjeckpoint)

Kerjakan berurutan; tiap milestone bisa di-commit & diuji terpisah.

**Milestone A — Skema & Model**
```bash
php artisan make:migration create_ukms_table
php artisan make:migration create_ukm_members_table
php artisan make:migration create_ukm_jadwals_table
php artisan make:migration create_ukm_presensis_table
# tempel skema §5.2, lalu:
php artisan migrate
php artisan make:model Ukm && php artisan make:model UkmMember
php artisan make:model UkmJadwal && php artisan make:model UkmPresensi
```
Checkpoint: `migrate:fresh` sukses; relasi Eloquent bisa di-tinker.

**Milestone B — CRUD UKM + Anggota (US 1.1, 1.0)**
```bash
php artisan make:controller UkmController --resource
php artisan make:controller UkmMemberController
```
Checkpoint: admin bisa buat UKM (1 form), tambah/hapus anggota.

**Milestone C — Penjadwalan + fan-out presensi (US 1.2)**
```bash
php artisan make:controller UkmJadwalController
```
Checkpoint: pelatih buat jadwal → baris `ukm_presensis` (Alpha) muncul untuk tiap anggota aktif (dalam transaction).

**Milestone D — Scanner QR generik (US 1.3)**
```bash
php artisan make:controller UkmScanController
```
Checkpoint: scan QR asrama existing di `/kamera-ukm/{jadwal}` → status jadi Hadir, anti-scan-ganda jalan.

**Milestone E — Verifikasi Pembina + Laporan (US 1.4)**
```bash
php artisan make:controller UkmVerifikasiController
```
Checkpoint: pembina approve/reject; laporan PDF generik ter-render.

**Milestone F — Seed, nav, uji asap**
- Seeder contoh UKM + anggota; tambah menu "UKM" di `partials/nav.blade.php`.
- Tulis smoke test (ikuti pola tes Laravel 13 Milestone 4 yang sudah ada).
Checkpoint: `php artisan test` hijau.

---

## 7. Keputusan (dikonfirmasi 3 Agu 2026)

1. **Role Pembina — DIKONFIRMASI: tambah role global.** Tambah `Role` baru `pembina` (`role_id = 5`) sebagai gerbang akses kasar (middleware), dikombinasikan dengan `ukm_members.peran` untuk scope per-UKM. Implikasi:
   - `RoleSeeder`: tambah `Role::create(['name'=>'pembina'])`.
   - `User.php`: tambah `const PEMBINA_ROLE_ID = 5;` + method `isPembina()`.
   - Middleware `EnsureUserHasRole` sudah generik (baca nama role dari tabel), jadi `role:pembina` langsung jalan tanpa ubah kode middleware.

2. **Pelatih = staf saja — DIKONFIRMASI.** Pelatih & Pembina adalah **akun staf** (`role_id` 4 & 5), bukan mahasiswa. Konsekuensi desain:
   - `ukm_members.peran = 'anggota'` **hanya** untuk mahasiswa (`role_id = 3`).
   - `peran IN ('pelatih','pembina')` **hanya** untuk staf (`role_id IN (4,5)`).
   - Fan-out presensi (US 1.2) hanya menyasar baris `peran='anggota'` — staf tidak ikut diabsen. Ini juga menyederhanakan gating menu (lihat dokumen Frontend §4).

3. **Cara menambah anggota.** Satuan (pilih dari daftar mahasiswa) untuk MVP; impor massal Excel (meniru `UsersImport`) menyusul. *(default MVP satuan — bisa diubah)*

4. **Kegiatan Wajib lama** dibiarkan apa adanya di Epic 01 (sesuai ADR-005). Migrasi ke mesin generik = kandidat ADR-006 terpisah.

---

## 8. Ringkasan Satu Layar

| Aspek | Kegiatan Wajib (lama) | UKM Dinamis (usulan) |
|---|---|---|
| Entitas jenis | Enum + nama tabel (kode) | Baris di `ukms` (data) |
| Tabel absensi | 3 (per jenis) | **1** (`ukm_presensis`) |
| Tambah 1 unit baru | migrasi+model+view+route+3 switch | **1 INSERT** |
| Keanggotaan | Otomatis per-blok | **Opt-in** via `ukm_members` |
| Scanner | 3 view/route/method | **1** generik berparameter |
| Verifikasi | Tidak ada | `status_verifikasi` + jejak pembina |
