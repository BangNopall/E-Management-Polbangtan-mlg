# System Flow — Epic 03: Modul Workflow Sistem Perizinan

**Proyek:** E-Management Polbangtan-mlg · **Companion:** `DESIGN-Epic03-Modul-Perizinan.md`, `DESIGN-Epic03-Frontend-Perizinan.md`, `RESEARCH-Epic03-Perizinan.md`
**Status:** Proposed · **Tanggal:** 6 Agustus 2026
**Perubahan sejak revisi 5 Agu:** memuat tiga keputusan terkonfirmasi (§14 dokumen utama) — `petugas_jaga` disatukan, validator dua jabatan, Dosen PA memakai fallback Kaprodi.

---

## 0. Peta alur besar

```
  ┌──────────────────────────────── SEKALI SAJA (M0) ────────────────────────────────┐
  │  Admin isi Data Pejabat:  Kaprodi (per prodi) · Kepala Asrama · Unit Kemahasiswaan │
  │  Dosen PA menyusul bertahap lewat impor Excel — TIDAK memblokir apa pun            │
  └───────────────────────────────────────────────────────────────────────────────────┘
                                        │
  ┌─────────────────────────────────────▼─────────────────────────────────────────────┐
  │  A. PENGAJUAN          Mahasiswa isi 4 field → sistem bentuk rantai → submit       │
  │  B. PERSETUJUAN        4 langkah berurutan, tiap approver setujui/tolak            │
  │  C. PENERBITAN         Nomor surat + PDF ber-kop + QR                              │
  │  D. PEMAKAIAN          Scan gerbang keluar → status 'izin' (bukan 'diluar')        │
  │  E. DI LOKASI          RT/panitia konfirmasi tiba lewat QR, tanpa akun             │
  │  F. KEPULANGAN         Scan gerbang masuk → 'selesai' atau 'terlambat'             │
  └───────────────────────────────────────────────────────────────────────────────────┘
```

Enam fase. Fase A–C menggantikan kertas. **Fase D–F adalah bagian yang mustahil dilakukan kertas** —
dan itulah alasan epic ini dikerjakan.

---

## 1. Fase A — Pengajuan

### 1.1 Sequence

```
Mahasiswa        UI (Alpine)      IzinController    ApproverResolver     DB
    │                 │                  │                  │             │
    │ buka /izin/create                  │                  │             │
    │────────────────>│                  │                  │             │
    │                 │ GET jenis izin aktif                │             │
    │                 │─────────────────>│─────────────────────────────── >│
    │                 │<── daftar jenis + properti (JSON) ──────────────── │
    │                 │                  │                  │             │
    │ pilih "Ijin Keluar Asrama"         │                  │             │
    │────────────────>│ x-show field UKM (butuh_ukm = true) │             │
    │                 │                  │                  │             │
    │ isi 4 field     │                  │                  │             │
    │────────────────>│ GET /izin/pratinjau-alur?jenis=1&ukm=3            │
    │                 │─────────────────>│─────────────────>│             │
    │                 │                  │   resolve tiap langkah         │
    │                 │                  │<── daftar approver ────────────│
    │                 │<══ PRATINJAU RANTAI ═══════════════│             │
    │  ┌──────────────────────────────────────────┐         │             │
    │  │ 1 Pembina UKM Silat — Pak Budi        ✓ │         │             │
    │  │ 2 Dosen PA 2A — (belum ada) → Kaprodi  ⚠ │  ← fallback TERLIHAT  │
    │  │ 3 Petugas jaga — ditentukan 12 Agu     ⏳│         │             │
    │  │ 4 Ka. Asrama / Unit Kemahasiswaan      ✓ │         │             │
    │  └──────────────────────────────────────────┘         │             │
    │                 │                  │                  │             │
    │ klik "Ajukan"   │                  │                  │             │
    │────────────────>│ POST /dashboard/izin                │             │
    │                 │─────────────────>│                  │             │
    │                 │       PengajuanIzinService::ajukan()               │
    │                 │                  │  ┌── DB::transaction ──────────┐│
    │                 │                  │  │ 1 validasi (§1.2)          ││
    │                 │                  │  │ 2 snapshot identitas       ││
    │                 │                  │  │ 3 resolve langkah          ││
    │                 │                  │  │ 4 BEKUKAN → izin_approvals ││
    │                 │                  │  │ 5 status = 'diajukan'      ││
    │                 │                  │  │ 6 buka langkah 1           ││
    │                 │                  │  └────────────────────────────┘│
    │<── redirect ke /dashboard/izin/{id} + notifikasi approver langkah 1  │
```

### 1.2 Gerbang validasi saat submit (urutan pemeriksaan)

```
1. Jenis izin aktif?                                     ─gagal→ 422
2. waktu_kembali > waktu_berangkat?                      ─gagal→ 422
3. Durasi ≤ jenis.maks_durasi_jam?                       ─gagal→ 422 "maks 24 jam"
4. Diajukan ≥ jenis.min_ajukan_jam sebelum berangkat?    ─gagal→ 422 "ajukan H-1"
5. butuh_ukm dan ukm_id diisi & mahasiswa anggota aktif? ─gagal→ 422
6. Tidak ada izin lain yang rentangnya beririsan?        ─gagal→ 422 (§8.4)
7. ►► SEMUA langkah resolve_saat='submit' menghasilkan
      minimal 1 kandidat (setelah fallback)?             ─gagal→ 422 dgn pesan spesifik
8. Simpan.
```

**Langkah 7 adalah inti prinsip "gagal lebih awal".** Bila Kaprodi prodi mahasiswa belum terdata,
mahasiswa mendapat pesan *"Prodi TRPL belum memiliki Ketua Program Studi terdaftar — hubungi admin
asrama"* **detik itu juga** — bukan setelah menunggu tiga hari lalu menemukan suratnya mengendap.

Langkah 3 (`petugas_jaga`) dikecualikan dari pemeriksaan ini karena `resolve_saat = 'langkah_aktif'`.

### 1.3 Detail resolusi satu langkah (dengan fallback)

```
resolveLangkah(step, pengajuan):

  kondisi step terpenuhi?  ─tidak→  buat izin_approvals status='dilewati'
   │                                catatan: "tidak berlaku: pemohon tanpa UKM"
   ▼ ya
  jalankan resolver utama:
   ├─ pejabat      → pejabats WHERE jabatan IN (step.jabatan)      ← ARRAY (§14.2)
   │                 AND (lingkup='global' OR lingkup_id = prodi/blok mahasiswa)
   │                 AND is_active AND masa jabatan mencakup hari ini
   ├─ dosen_pa     → kelas(mahasiswa).dosen_pa_id
   ├─ pembina_ukm  → ukm_members WHERE ukm_id=pengajuan.ukm_id
   │                 AND peran='pembina' AND status='aktif'
   └─ petugas_jaga → jadwal_petugas WHERE date = DATE(waktu_berangkat)
                     → [petugas1_id, petugas2_id] keduanya, buang yang null   ← §14.1
   │
   ├─ kandidat ada? ──ya──> pilih sesuai mode:
   │                          any → simpan SEMUA kandidat sbg calon,
   │                                approver_user_id = kandidat[0],
   │                                tapi otorisasi cek keanggotaan daftar (§2.3)
   │                          all → satu baris approval per kandidat
   │
   └─ kandidat KOSONG ──> jalankan fallback_resolver (bila diisi)      ← §14.3
                           ├─ ada kandidat → pakai, tandai
                           │                 catatan_sistem = "fallback: Dosen PA
                           │                 belum terdata, dialihkan ke Kaprodi"
                           └─ tetap kosong →
                                 resolve_saat='submit'       → GAGALKAN submit (§1.2 no.7)
                                 resolve_saat='langkah_aktif'→ eskalasi (§3.3)
```

Catatan penting: **hasil fallback ikut ditampilkan di pratinjau (§1.1)**. Mahasiswa melihat
*"Dosen PA 2A — belum ada, dialihkan ke Kaprodi"* sebelum submit. Transparansi ini yang membuat
fallback terasa sebagai fitur, bukan sebagai kejanggalan sistem.

---

## 2. Fase B — Persetujuan berantai

### 2.1 Alur satu langkah

```
   ┌─────────────────────────────────────────────────────────────┐
   │ langkah N: status 'menunggu', dibuka_at = now()             │
   │ → notifikasi in-app ke approver + badge di nav              │
   └───────────────────────────┬─────────────────────────────────┘
                               │
        ┌──────────────────────┼──────────────────────┐
        │                      │                      │
   ┌────▼─────┐          ┌─────▼─────┐         ┌──────▼───────┐
   │ SETUJUI  │          │  TOLAK    │         │ SLA terlewat │
   │          │          │ (+alasan  │         │ (job harian) │
   │          │          │  wajib)   │         │              │
   └────┬─────┘          └─────┬─────┘         └──────┬───────┘
        │                      │                      │
   catat acted_by/at/     status pengajuan       kirim pengingat;
   ip/user_agent          = 'ditolak';           langkah TETAP
        │                 langkah sisa           menunggu
        │                 = 'dilewati'           (tidak auto-approve)
        │                      │
   ada langkah N+1?            ▼
   ├─ ya → buka langkah N+1   SELESAI (ditolak)
   │       resolve_saat='langkah_aktif'? → resolve SEKARANG
   │       notifikasi approver berikutnya
   │
   └─ tidak → SEMUA DISETUJUI → lanjut Fase C
```

**SLA tidak pernah menyetujui otomatis.** Pengingat boleh, eskalasi (menambah approver alternatif)
boleh, tapi persetujuan diam-diam menghapus seluruh nilai kontrol proses ini.

### 2.2 Contoh nyata — Form A, mahasiswa UKM Silat, prodi TRPL belum punya Dosen PA

```
 t=0     Naufal submit                          status: diajukan, langkah_aktif=1
         izin_approvals dibekukan:
           #1 "Menyetujui, Pembina ORMAWA/UKM"   → Pak Budi        menunggu
           #2 "Mengetahui, Dosen PA"             → Bu Sari (Kaprodi, fallback) menunggu
           #3 "Mengetahui, Petugas Piket Asrama" → (belum resolve)  menunggu
           #4 "Memvalidasi, Ka. Asrama/Unit Kmhs"→ Pak Yongki | Bu Dewi  menunggu

 t+2j    Pak Budi setujui                       #1 disetujui, #2 dibuka
 t+5j    Bu Sari setujui                        #2 disetujui, #3 dibuka
         ►► resolve_saat='langkah_aktif' → baca jadwal_petugas 12 Agu
            → [Pak Andi, Mas Riko] — keduanya jadi kandidat, mode any
 t+6j    Mas Riko setujui (Pak Andi tidak perlu) #3 disetujui, #4 dibuka
 t+7j    Bu Dewi (Unit Kemahasiswaan) validasi   #4 disetujui  ← Pak Yongki di luar kota
         ►► SEMUA DISETUJUI → Fase C
```

Dua hal yang perlu diperhatikan pada contoh ini:

- **t+7j:** langkah terakhir tidak mandek meski Kepala Asrama tidak ada — inilah manfaat langsung
  keputusan §14.2. Di proses kertas, ini kasus yang paling sering menahan surat semalaman.
- **t+5j:** petugas jaga baru ditentukan saat langkahnya terbuka, bukan saat submit — sehingga
  perubahan jadwal piket antara pengajuan dan keberangkatan tidak merusak apa pun.

### 2.3 Otorisasi saat approver menekan tombol

```php
// Bukan berbasis role. Berbasis kepemilikan langkah.
DB::transaction(function () use ($approvalId) {
    $a = IzinApproval::whereKey($approvalId)->lockForUpdate()->first();

    abort_unless($a->status === 'menunggu', 409, 'Langkah ini sudah diputuskan petugas lain.');
    abort_unless($a->urutan === $a->pengajuan->langkah_aktif, 409, 'Langkah sebelumnya belum selesai.');
    abort_unless($a->kandidat->contains(auth()->id()), 403, 'Anda bukan penandatangan langkah ini.');
    // ...
});
```

Tiga `abort` ini menutup tiga celah sekaligus: balapan dua petugas piket, loncat langkah lewat
POST langsung, dan approver dari prodi/UKM lain.

---

## 3. Fase C — Penerbitan surat

```
  semua langkah disetujui
        │
        ▼
  DB::transaction:
   1. NomorSuratService::generate()   → "AR.009/0042/VIII/2026"
      (SELECT ... FOR UPDATE pada counter, aman dari balapan)
   2. qr_token = Str::random(40)      ← identitas publik, BUKAN id
   3. status = 'disetujui', disetujui_at = now()
        │
        ├─────> PembebasanPresensiService::jalankan($izin)
        │        cari jadwal kegiatan wajib + ukm_jadwal yang beririsan
        │        [waktu_berangkat, waktu_kembali]
        │        UPDATE presensi SET status_kehadiran='Izin' WHERE status='Alpha'
        │        ► 'Hadir' TIDAK pernah ditimpa
        │
        └─────> notifikasi mahasiswa: "Izin disetujui, surat siap diunduh"

  Mahasiswa buka /dashboard/izin/{id}/surat
        │
        ▼
  SuratIzinPdfService: Blade → dompdf
        kop surat (ekstrak dari word/media docx)
      + data *_snapshot (bukan data hidup)
      + daftar 4 persetujuan: nama, jabatan, tanggal-jam
      + QR → URL::signedRoute('izin.verifikasi', qr_token)
      + blok "Telah tiba di ..." kosong (cadangan manual)
```

---

## 4. Fase D — Scan gerbang keluar (integrasi paling sensitif)

```
Mahasiswa tunjukkan QR   Petugas scan     QRController::presense()
        │                     │                     │
        │                     │  POST /presense/api │
        │                     │────────────────────>│
        │                     │                     │
        │                     │      ┌──────────────▼──────────────┐
        │                     │      │ validasi QR & jendela waktu │  ← existing
        │                     │      │ (tidak diubah sedikit pun)  │
        │                     │      └──────────────┬──────────────┘
        │                     │                     │
        │                     │      ┌──────────────▼──────────────────────┐
        │                     │      │ ►► BLOK BARU (satu-satunya sisipan) │
        │                     │      │ $izin = IzinGateResolver            │
        │                     │      │          ::aktifUntuk($user, now()) │
        │                     │      └──────────────┬──────────────────────┘
        │                     │                     │
        │                     │          ┌──────────┴──────────┐
        │                     │      ada izin?              tidak ada
        │                     │          │                      │
        │                     │  ┌───────▼────────┐    ┌────────▼─────────┐
        │                     │  │ log_status =   │    │ SELURUH LOGIKA   │
        │                     │  │   'izin'       │    │ EXISTING, UTUH,  │
        │                     │  │ users.status = │    │ TAK TERSENTUH    │
        │                     │  │   'izin'       │    │ (didalam/diluar/ │
        │                     │  │ izin.keluar_at │    │  telat)          │
        │                     │  │   = now()      │    └──────────────────┘
        │                     │  │ status =       │
        │                     │  │  'berjalan'    │
        │                     │  └────────────────┘
```

**Kriteria `IzinGateResolver::aktifUntuk()`** (read-only, satu query):

```sql
SELECT * FROM pengajuan_izins
WHERE user_id = ?
  AND status IN ('disetujui','berjalan')
  AND ? BETWEEN DATE_SUB(waktu_berangkat, INTERVAL 2 HOUR)   -- toleransi berangkat awal
             AND DATE_ADD(waktu_kembali,  INTERVAL 6 HOUR)   -- toleransi kembali telat
ORDER BY waktu_berangkat LIMIT 1
```

Toleransi diperlukan agar mahasiswa yang berangkat 30 menit lebih awal tidak terlempar ke jalur
`telat`. Validasi tumpang tindih (§8.4 dokumen utama) menjamin hasilnya selalu paling banyak satu baris.

**Kenapa ini aman untuk kode produksi harian:**

| Sifat | Konsekuensi |
|---|---|
| Aditif — hanya menambah cabang di depan | Mahasiswa tanpa izin menempuh jalur kode yang **identik** dengan hari ini |
| Enum `'izin'` **sudah ada** di `users.status` & `presences.log_status` | **Nol `ALTER TABLE`** pada tabel presensi produksi |
| Resolver read-only | Bisa diuji unit terpisah dari controller |
| Populasi terdampak awalnya nol | Regresi hanya mungkin pada mahasiswa berizin — yang hari ini belum ada |

---

## 5. Fase E — Konfirmasi tiba (tanpa akun)

```
Mahasiswa tiba di Surabaya
        │
        │ tunjukkan surat cetak ke RT/panitia
        ▼
RT pindai QR ──> GET /izin/{qr_token}/konfirmasi-tiba   [middleware: signed]
        │
        │        tanda tangan URL valid?  ─tidak→ 403
        │        token dikenal?           ─tidak→ 404
        │        sudah dikonfirmasi?      ─ya──→ tampilkan tanda terima (tidak bisa ditimpa)
        ▼
   Form 3 field: nama · sebagai (RT/panitia/...) · no HP (opsional) · foto (opsional)
        │
        ▼ POST
   isi tiba_di, tiba_at, tiba_dikonfirmasi_oleh, tiba_kontak, tiba_bukti_path
        │
        └──> notifikasi Kepala Asrama: "Naufal dikonfirmasi tiba di Surabaya oleh Pak RT"
```

Tautan berlaku sampai `waktu_kembali + 24 jam`. Tanpa CAPTCHA, tanpa OTP — tanda tangan pada URL
sudah cukup, dan setiap gesekan tambahan menurunkan tingkat pengisian (metrik M6).

Bila RT menolak/tidak bisa memakai QR, blok "Telah tiba di ..." pada surat cetak tetap tersedia
untuk diisi tangan. Jalur kertas **tidak dimatikan**, hanya tidak lagi menjadi satu-satunya jalan.

---

## 6. Fase F — Kepulangan & penutupan

```
Scan gerbang masuk
        │
        ▼
  $izin = IzinGateResolver::aktifUntuk($user, now())   → status 'berjalan'
        │
        ├── now() ≤ waktu_kembali ──────> status 'selesai'
        │                                 kembali_at = now()
        │                                 users.status = 'didalam'
        │
        └── now() >  waktu_kembali ──────> status 'terlambat'
                                          kembali_at = now()
                                          users.status = 'didalam'
                                          │
                                          ▼
                            buat Pelanggaran (jenis "Terlambat kembali dari izin")
                            statusPelanggaran = 'submitted'   ← BUKAN 'Done'
                            pengajuan_izins.pelanggaran_id = ...
                                          │
                                          ▼
                            masuk antrean staf: konfirmasi / tolak
                            poin baru dipotong setelah dikonfirmasi manusia
```

**Sistem mengusulkan; manusia memutuskan.** Terlalu banyak alasan sah untuk terlambat (kereta batal,
lomba molor, hujan). Sistem yang memotong poin tanpa mendengar akan dihindari — dan begitu dihindari,
mahasiswa kembali keluar tanpa izin, yang merusak metrik utama (M4).

### Jalur tanpa scan masuk

```
Job PeriksaKeterlambatan (tiap 30 menit)
  status='berjalan' AND now() > waktu_kembali AND kembali_at IS NULL
    → tandai 'terlambat', muncul merah di Monitor Asrama, notifikasi petugas jaga

Job TandaiIzinKadaluarsa (tiap jam)
  status='disetujui' AND now() > waktu_berangkat + 12 jam AND keluar_at IS NULL
    → status 'kadaluarsa' (surat disetujui tapi tidak pernah dipakai)
```

Keduanya idempoten — aman dijalankan berulang, karena tidak ada jaminan scheduler selalu hidup
(batasan §3.3 dokumen utama).

---

## 7. Peta integrasi ke modul existing

```
                        ┌──────────────────────────┐
                        │   MODUL PERIZINAN (baru) │
                        └────────────┬─────────────┘
                                     │
     ┌───────────────┬───────────────┼───────────────┬──────────────────┐
     │ BACA          │ BACA          │ TULIS         │ TULIS            │ TULIS
     ▼               ▼               ▼               ▼                  ▼
┌──────────┐  ┌─────────────┐  ┌──────────┐  ┌──────────────┐  ┌──────────────┐
│ukm_members│  │jadwal_petugas│  │ presences│  │presensi_apel/│  │ pelanggarans │
│(Epic 01)  │  │  (existing)  │  │  users   │  │senam/upacara │  │  (existing)  │
│           │  │              │  │          │  │ukm_presensis │  │              │
│peran=     │  │petugas1_id   │  │log_status│  │              │  │status=       │
│'pembina'  │  │petugas2_id   │  │='izin'   │  │'Alpha'→'Izin'│  │'submitted'   │
│           │  │(tanpa peran) │  │status=   │  │              │  │              │
│→ resolver │  │→ resolver    │  │'izin'    │  │              │  │              │
└──────────┘  └─────────────┘  └──────────┘  └──────────────┘  └──────────────┘
   nol            nol            enum sudah      enum sudah        seed 1
 perubahan     perubahan          ada              ada          JenisPelanggaran
```

**Total perubahan pada kode produksi existing:**

| Berkas | Perubahan |
|---|---|
| `QRController::presense()` | +1 blok `if` di awal (§4) |
| `UkmJadwalController::store()` | fan-out cek izin aktif sebelum tulis `'Alpha'` |
| `KegiatanAsramaController::createJadwalKegiatanStore()` | idem |
| `partials/nav.blade.php` | +3 item menu |
| `database/migrations/…kelas` | +1 kolom `dosen_pa_id` (nullable) |

Selain itu **nol**. Tidak ada tabel existing yang di-`ALTER` selain `kelas`, dan `kelas` bukan tabel
yang ditulis setiap hari. Ini pemenuhan janji ADR-004: *selesaikan masalah dengan sentuhan sekecil
mungkin pada kode yang sudah berjalan.*

---

## 8. Ringkasan status & pemicu

| Status | Dipicu oleh | Transisi berikut yang mungkin |
|---|---|---|
| `draft` | mahasiswa simpan tanpa ajukan | `diajukan`, `dibatalkan` |
| `diajukan` | submit lolos 8 gerbang validasi | `disetujui`, `ditolak`, `dibatalkan` |
| `disetujui` | langkah terakhir disetujui | `berjalan`, `kadaluarsa`, `dibatalkan` |
| `ditolak` | satu langkah ditolak | *(final)* |
| `dibatalkan` | mahasiswa tarik sebelum disetujui penuh | *(final)* |
| `berjalan` | scan gerbang keluar | `selesai`, `terlambat` |
| `selesai` | scan masuk ≤ `waktu_kembali` | *(final)* |
| `terlambat` | scan masuk > `waktu_kembali`, atau job | *(final, + Pelanggaran)* |
| `kadaluarsa` | job: disetujui tapi tak pernah keluar | *(final)* |

Seluruh transisi hanya boleh terjadi di `PengajuanIzinService`. Tidak ada `$izin->update(['status' => …])`
di controller mana pun — ini satu-satunya cara janji ADR-006 bertahan setelah beberapa fitur tambahan.

---

## 9. Urutan implementasi mengikuti fase

| Fase | Milestone | Bisa dirilis sendiri? |
|---|---|---|
| A + B | M1–M2 | Ya — alur tanpa kertas sudah jalan meski surat belum bisa dicetak |
| C | M3 | Ya — surat resmi + verifikasi publik |
| **D + F** | **M4** | **Ya — dan inilah yang memberi nilai terbesar (metrik M3 riset)** |
| E | M5 | Ya |
| Konfigurasi & laporan | M6 | Ya |

Bila jadwal terpangkas: buang M6, pangkas M5 — **jangan pernah buang M4.** Fase A–C hanya membuat
proses kertas jadi lebih cepat; fase D dan F melakukan sesuatu yang kertas tidak akan pernah bisa.
