# System Flow — Modul UKM Dinamis (Epic 01)

> Dokumen ini menjelaskan alur sistem (system flow) dari Modul UKM Dinamis yang telah diimplementasikan di E-Management Polbangtan-mlg. Lihat juga [DESIGN-Epic01-Modul-UKM-Dinamis.md](./DESIGN-Epic01-Modul-UKM-Dinamis.md) untuk desain arsitektur awal dan [DESIGN-Epic01-Frontend-UKM-Dinamis.md](./DESIGN-Epic01-Frontend-UKM-Dinamis.md) untuk spesifikasi frontend.

---

## 1. Konsep Utama & Arsitektur Database Generik

Modul UKM Dinamis dirancang agar penambahan UKM baru **tidak memerlukan migrasi/perubahan skema database**. Seluruh UKM (apa pun jumlahnya) dikelola melalui 4 tabel generik:

```text
┌──────────────┐       1:N       ┌─────────────────┐       1:N       ┌──────────────────┐
│     ukms     ├─────────────────┤   ukm_members   ├─────────────────┤  users (Mahasiswa│
│ (Daftar UKM) │                 │(Keanggotaan UKM)│                 │   / Pelatih /    │
└──────┬───────┘                 └─────────────────┘                 │    Pembina)      │
       │                                                             └──────────────────┘
       │ 1:N
┌──────┴───────┐       1:N       ┌──────────────────┐
│  ukm_jadwals ├─────────────────┤   ukm_presensis  │
│  (Kegiatan)  │                 │(Catatan Presensi)│
└──────────────┘                 └──────────────────┘
```

| Tabel | Fungsi | Kolom Kunci |
|---|---|---|
| `ukms` | Master data UKM | `nama`, `slug` (unik), `is_active` |
| `ukm_members` | Keanggotaan (staf & mahasiswa) | `ukm_id`, `user_id`, `peran` (anggota/pelatih/pembina), `status` (aktif/nonaktif) — unique `(ukm_id, user_id)` |
| `ukm_jadwals` | Jadwal kegiatan UKM | `ukm_id`, `tanggal`, `mulai_acara`, `selesai_acara`, `status_verifikasi` |
| `ukm_presensis` | Catatan kehadiran per kegiatan | `ukm_jadwal_id`, `user_id`, `status_kehadiran` — unique `(ukm_jadwal_id, user_id)` |

---

## 2. Peta Peran & Hak Akses (Role Matrix)

Sistem membedakan hak akses berdasarkan `role_id` global **dan** status keanggotaan staf aktif di masing-masing UKM (per-UKM scoping untuk mencegah IDOR lintas UKM):

| Peran (Role) | `role_id` | Hak Akses Utama di Modul UKM Dinamis |
|---|---|---|
| **Admin** | `1` | Full Control: Buat UKM baru, kelola anggota/staf, buat jadwal, scan QR, verifikasi jadwal, unduh laporan — untuk UKM mana pun. |
| **Pelatih** | `4` | Operasional UKM Binaannya saja: Lihat detail UKM binaan, buat jadwal kegiatan, buka scanner QR, ajukan verifikasi ke Pembina. |
| **Pembina** | `5` | Verifikasi & Pengawasan UKM Binaannya saja: Lihat antrean verifikasi, setujui/tolak jadwal kegiatan, unduh laporan PDF. |
| **Mahasiswa** | `3` | Peserta: Lihat halaman "UKM Saya", tampilkan QR Code presensi, lihat riwayat kehadiran UKM. |

---

## 3. Alur Kerja Sistem (Detailed Workflow Flows)

### Flow 1 — Pengelolaan Anggota & Staf UKM

```text
[Admin] ──► Buka Detail UKM ──► Tambah Anggota (Pilih User & Peran: Anggota/Pelatih/Pembina)
                                           │
                                           ▼
                                 [Validasi Aturan]
                                 - User & UKM harus unik (DB Unique Constraint)
                                 - Mahasiswa hanya bisa jadi 'anggota'
                                 - Staf (Pelatih/Pembina) hanya bisa jadi 'pelatih'/'pembina'
                                           │
                                           ▼
                                [Simpan ukm_members] (Status: 'aktif')
```

### Flow 2 — Penjadwalan Kegiatan & Fan-Out Presensi Otomatis

Saat Pelatih atau Admin membuat kegiatan latihan/wajib baru, sistem secara otomatis menyiapkan lembar presensi untuk seluruh mahasiswa anggota aktif dalam satu transaksi database:

```text
[Pelatih / Admin] ──► Input Form Jadwal (Judul, Tanggal, Jam, Jenis)
                                   │
                                   ▼
                       [DB::transaction()]
  ┌────────────────────────────────┴────────────────────────────────┐
  │ 1. Buat 1 baris di `ukm_jadwals` (status_verifikasi = 'draft')   │
  │ 2. Ambil seluruh mahasiswa aktif dari `ukm_members`              │
  │    (relasi anggotaAktif: peran='anggota' & status='aktif')       │
  │ 3. Batch Insert N baris ke `ukm_presensis` (status = 'Alpha')    │
  │    dalam 1 query SQL (UkmPresensi::insert())                    │
  └────────────────────────────────┬────────────────────────────────┘
                                   │
                                   ▼
                 [Jadwal & Lembar Presensi Siap Digunakan]
```

### Flow 3 — Siklus Hidup Verifikasi Jadwal (State Machine)

Untuk menjaga tertib administrasi, jadwal kegiatan yang dibuat Pelatih harus diverifikasi oleh Pembina UKM sebelum dianggap resmi:

```text
  ┌─────────┐    Pelatih/Admin klik "Ajukan Verifikasi"    ┌──────────┐
  │  DRAFT  ├──────────────────────────────────────────────►│ MENUNGGU │
  └─────────┘                                                └────┬─────┘
                                                                   │
                                                Pembina UKM menelaah antrean
                                                                   │
                                            ┌──────────────────────┴──────────────────────┐
                                            ▼                                              ▼
                                   ┌─────────────────┐                            ┌────────────────┐
                                   │    DISETUJUI     │                            │    DITOLAK      │
                                   │ (verified_by/at) │                            │ (catatan_pembina)│
                                   └─────────────────┘                            └────────────────┘
```

### Flow 4 — Pemindaian QR & Presensi Real-Time

Proses absensi dilakukan menggunakan kamera scanner staf dan QR Code dinamis di ponsel mahasiswa:

```text
[Mahasiswa] Buka /dashboard/kode-qr ──► Generate Dynamic QR Payload JSON:
                                        { user_id, date, time, scanner: "absensi" }
                                                    │
                                                    ▼
[Pelatih/Admin] Buka /kamera-ukm/{jadwal} ──► Scan QR Mahasiswa via Kamera HTML5
                                                    │
                                                    ▼
                                   [POST /api/kamera-ukm/{jadwal}]
                                                    │
                                                    ▼
                                         [Pemeriksaan Keamanan Berlapis]
                                         1. Apakah staf berhak di UKM ini? (isStaffOfUkm — anti-IDOR)
                                         2. Selisih waktu QR ≤ 30 detik dari waktu server? (freshness)
                                         3. Mahasiswa anggota aktif UKM ini?
                                         4. Waktu scan berada dalam jendela jam kegiatan?
                                         5. Mahasiswa belum berstatus 'Hadir'? (no double scan)
                                                    │
                                                    ▼
                                      [Update Record Presensi]
                                      - status_kehadiran = 'Hadir'
                                      - jam_kehadiran = waktu_scan
```

### Flow 5 — Dashboard Mahasiswa & Pelaporan PDF

```text
[Mahasiswa] ──► Buka /dashboard/ukm ("UKM Saya") ──► Tampil kartu UKM aktif + 5 jadwal mendatang
            ──► Buka /dashboard/ukm/riwayat      ──► Tampil tabel riwayat presensi (paginated 15)

[Pembina / Pelatih / Admin] ──► POST admin/ukm/{ukm}/laporan/pdf (filter tanggal opsional)
                                            │
                                            ▼
                               [Otorisasi staf UKM ini? — anti-IDOR]
                                            │
                                            ▼
                               [Render barryvdh/laravel-dompdf]
                               - Profil UKM & jumlah anggota (loadCount)
                               - Rekap kehadiran (Hadir / Alpha / Izin per kegiatan)
                                            │
                                            ▼
                               [Download PDF Laporan Resmi]
```

---

## 4. Perlindungan Keamanan (Anti-IDOR)

Seluruh endpoint operasional staf (buat jadwal, buka scanner, submit scan, unduh laporan, lihat detail UKM, verifikasi jadwal) memvalidasi **keanggotaan staf aktif** pada UKM target — bukan hanya `role_id` global. Contoh: Pelatih UKM Voli **tidak bisa** membuat jadwal, membuka scanner, atau mengunduh laporan milik UKM Basket. Pola pengecekan seragam:

```php
UkmMember::where('ukm_id', $targetUkmId)
    ->where('user_id', $user->id)
    ->whereIn('peran', ['pelatih', 'pembina']) // atau spesifik salah satu
    ->where('status', 'aktif')
    ->exists();
```

Admin (`role_id = 1`) selalu bypass pengecekan ini.

---

## 5. Optimasi Database & Performa

| Optimasi | Lokasi | Dampak |
|---|---|---|
| Batch insert fan-out presensi | `UkmJadwalController::store()` | Dari 2N query (loop `firstOrCreate`) → 1 query `INSERT` kolektif |
| Index `(user_id, status)` | `ukm_members` | Hindari full table scan pada query dashboard mahasiswa |
| Index `(ukm_id, status_verifikasi)` | `ukm_jadwals` | Percepat antrean verifikasi Pembina |
| Index `(user_id, created_at)` | `ukm_presensis` | Percepat riwayat presensi mahasiswa |
| `loadCount('members')` | `UkmLaporanController` | Hindari N+1 query `COUNT(*)` saat render PDF |
| Eager load tanpa `limit()` di closure | `UkmMahasiswaController::index()` | Hindari bug limit global Eloquent; slicing dilakukan di Collection (`->take(5)`) |

---

## 6. Referensi Kode Terkait

| Komponen | File |
|---|---|
| CRUD UKM | `app/Http/Controllers/UkmController.php` |
| Keanggotaan | `app/Http/Controllers/UkmMemberController.php` |
| Penjadwalan & Fan-out | `app/Http/Controllers/UkmJadwalController.php` |
| Scanner QR | `app/Http/Controllers/UkmScanController.php` |
| Verifikasi Pembina | `app/Http/Controllers/UkmVerifikasiController.php` |
| Laporan PDF | `app/Http/Controllers/UkmLaporanController.php` |
| Dashboard Mahasiswa | `app/Http/Controllers/UkmMahasiswaController.php` |
| Seeder Contoh | `database/seeders/UkmSeeder.php` |
| Test Suite | `tests/Feature/Ukm/*.php`, `tests/Feature/UkmSmokeTest.php` |
