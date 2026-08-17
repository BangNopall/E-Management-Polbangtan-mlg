# Design Document — Epic 03: Modul Workflow Sistem Perizinan

**Proyek:** E-Management Polbangtan-mlg
**Epic:** 03 — Workflow Sistem Perizinan
**Penulis:** Muhammad Naufal Mathara R
**Status:** Proposed
**Tanggal:** 5 Agustus 2026
**Basis analisis:** branch `dev` (commit `5eb1ba2`) + `IJIN_KELUAR.docx` (AR.009) + `SURAT_IB_NEW.docx`
**Companion:** `RESEARCH-Epic03-Perizinan.md` (riset & asumsi), `DESIGN-Epic03-Frontend-Perizinan.md` (view & UI)
**Prasyarat:** Epic 01 (UKM Dinamis) — dipakai sebagai sumber data Pembina UKM

---

## 0. TL;DR (baca ini dulu)

**Dua form kertas itu bukan dua fitur.** Keduanya punya kerangka identik — 4 tanda tangan dalam 2
blok — dan hanya berbeda pada **siapa** yang mengisi slot 1 dan slot 3. Kalau dibangun sebagai dua
form terpisah, Anda mengulang persis kesalahan modul Kegiatan Wajib (3 tabel identik, 3 view kamera,
`switch` di mana-mana) yang sudah dihindari di Epic 01.

**Rekomendasi inti: bangun satu mesin alur persetujuan generik (5 tabel).** Jenis izin dan rantai
penandatangannya adalah **data**, bukan kode. Menambah jenis izin baru = beberapa baris `INSERT`,
nol baris kode, nol migrasi.

**Bagian yang paling bernilai bukan formnya.** Skema aplikasi sudah punya enum `izin` di
`users.status`, `presences.log_status`, dan keempat tabel presensi — dan **tidak ada satu baris kode
pun yang pernah menulisnya**. Artinya hari ini mahasiswa yang izin resmi tetap tercatat `telat` di
gerbang dan `Alpha` di kegiatan wajib. Epic 03 menutup lubang integritas data itu. Itu argumen
penjualan epic ini, bukan "form jadi online".

**Keputusan arsitektur utama:**

| # | Keputusan | Rujukan |
|---|---|---|
| 1 | Alur persetujuan data-driven (`jenis_izins` + `izin_workflow_steps`), bukan form hardcoded | ADR-006 |
| 2 | Penandatangan di-*resolve* lewat strategi, bukan lewat penambahan role global | ADR-007 |
| 3 | Tanda tangan = jejak audit + tautan bertanda tangan bawaan Laravel, bukan gambar ttd, bukan HMAC buatan sendiri | ADR-008 |
| 4 | Integrasi gerbang dilakukan sebagai **satu blok `if`** di `QRController`, bukan penulisan ulang | §7.8 |

**Estimasi:** 4–5 minggu untuk satu developer, terbagi 6 milestone yang masing-masing bisa dirilis.

---

## 1. Kondisi saat ini yang menjadi dasar keputusan

### 1.1 Yang sudah ada dan bisa langsung dipakai

| Aset existing | Kegunaan untuk Epic 03 |
|---|---|
| `ukm_members` dengan `peran = 'pembina'`, scoped per UKM (Epic 01) | **Sumber data "Pembina ORMAWA/UKM"** — sudah jadi |
| `jadwal_petugas` (`date`, `petugas1_id`, `petugas2_id`) | **Sumber data "Petugas Piket Asrama"** per tanggal — sudah jadi |
| Enum `izin` di `users.status` & `presences.log_status` | Slot integrasi gerbang — sudah ada, belum pernah diisi |
| Enum `Izin` di `presensi_apels/senams/upacaras` & `ukm_presensis` | Slot pembebasan kehadiran kegiatan — idem |
| Alur `Pelanggaran` (`scanned → submitted → progressing → Done`) + `users.point` | Sanksi terlambat kembali — **jangan bikin mekanisme sanksi baru** |
| Pola approve/reject `UkmVerifikasiController` (`verified_by`, `verified_at`, `catatan_*`) | Pola langkah persetujuan |
| Middleware `ValidateSignature` (terdaftar) + `barryvdh/laravel-dompdf` + `simple-qrcode` | Surat cetak ber-QR + konfirmasi RT tanpa akun |

### 1.2 Yang belum ada (gap sebenarnya)

| Gap | Dampak |
|---|---|
| Tidak ada relasi **Dosen PA → mahasiswa/kelas** | Langkah "Mengetahui, Dosen PA" tidak bisa di-resolve |
| Tidak ada relasi **Ketua Prodi → prodi** | Langkah "Menyetujui, Ketua Program Studi" tidak bisa di-resolve |
| Tidak ada penanda **Kepala Asrama / Unit Kemahasiswaan** | Langkah validasi akhir tidak bisa di-resolve |
| `jadwal_petugas` tidak membedakan *petugas piket* vs *pelatih harian* | Form A dan Form B butuh dua peran berbeda dari sumber yang sama |
| Role hanya 5 dan **bernilai tunggal** (`users.role_id`) | Seorang dosen tidak bisa sekaligus "Dosen PA" dan "Kaprodi" |

Gap terakhir adalah alasan ADR-004 menulis *"Refactor RBAC tetap dibutuhkan untuk Epic 01, 03, dan 04."*
Namun — lihat ADR-007 — jawabannya **bukan** menambah role.

---

## 2. Brainstorming: menantang asumsi sebelum menetapkan desain

### Asumsi 1 — "Ada dua form, jadi buat dua fitur."

**Salah.** Bandingkan kerangkanya:

```
Form A (AR.009)                        Form B (IB / Meninggalkan Kelas)
─────────────────────────              ─────────────────────────
Blok 1  ①  Pembina ORMAWA/UKM     ←→   ①  Ketua Program Studi      ← BEDA
        ②  Dosen PA               ←→   ②  Dosen Pembimbing Akademik  ← SAMA
Blok 2  ③  Petugas Piket Asrama   ←→   ③  Pelatih Harian Asrama      ← BEDA
        ④  Kepala Asrama          ←→   ④  Kepala Asrama              ← SAMA
```

Struktur, urutan, semantik ("menyetujui" lalu "mengetahui"), dan lifecycle-nya identik. Yang berbeda
hanya **dua baris data**. Membangun dua tabel untuk perbedaan dua baris data adalah definisi
duplikasi. → **Jenis izin harus jadi baris data, bukan tipe di kode.** (Ini pelajaran yang sama
persis dengan ADR-005 di Epic 01.)

### Asumsi 2 — "Cukup tambahkan role `kaprodi`, `dosen_pa`, `kepala_asrama`."

**Salah, dan ini jebakan paling mahal di epic ini.** Tiga alasan:

1. `users.role_id` **bernilai tunggal**. Satu dosen bisa menjadi Dosen PA kelas 2A *sekaligus*
   Ketua Prodi. Dengan role tunggal, dia harus memilih salah satu.
2. Role bersifat **global**, sedangkan pertanyaan sebenarnya bersifat **relasional**:
   bukan *"siapa yang berperan Dosen PA?"* melainkan ***"siapa Dosen PA milik mahasiswa ini?"***
   Role tidak bisa menjawab pertanyaan itu.
3. "Petugas Piket Asrama" bahkan **berganti setiap hari**. Itu bukan role sama sekali — itu jadwal.

→ Yang dibutuhkan bukan role tambahan, melainkan **lapisan resolusi penandatangan** (ADR-007).

### Asumsi 3 — "Selesai kalau surat sudah disetujui."

**Salah, dan ini justru bagian bernilainya.** Perhatikan blok "Telah tiba di ..... pada tanggal .....
(ttd RT setempat)". Form kertasnya sendiri sudah mengakui bahwa surat punya **siklus hidup pasca-persetujuan**:

```
diajukan → disetujui → berangkat → tiba di tujuan → kembali → selesai
```

Sistem perizinan yang berhenti di "disetujui" hanya menyalin kertas ke layar. Yang membuat versi
digital **lebih baik**, bukan sekadar lebih cepat, adalah tiga hal di kolom kanan:

| Yang kertas juga bisa (hanya lebih cepat) | Yang **mustahil** dilakukan kertas |
|---|---|
| Isi form, kumpulkan tanda tangan | Otomatis mengubah `telat` → `izin` di gerbang |
| Cetak surat | Otomatis mengubah `Alpha` → `Izin` di presensi kegiatan wajib |
| Arsipkan | Menjawab *real time*: "siapa yang di luar sekarang dan kapan seharusnya kembali?" |

**Kalau waktu terbatas, kerjakan kolom kanan.** Kolom kiri tanpa kolom kanan menghasilkan sistem
yang cuma memindahkan pekerjaan, bukan menghilangkannya.

### Inversi — "Bagaimana cara membuat sistem ini gagal total?"

Latihan reverse brainstorming, lalu setiap jawabannya dibalik menjadi syarat desain:

| Cara menggagalkan | Syarat desain hasil pembalikannya |
|---|---|
| Buat approver harus buka laptop & login setiap kali | Persetujuan harus selesai dari HP, ≤ 3 ketukan, konteks lengkap tanpa scroll |
| Biarkan pengajuan mandek tanpa ada yang tahu | SLA per langkah + penanda "menunggu X jam" + notifikasi + dashboard tunggakan |
| Bikin mahasiswa tetap harus cetak & keliling minta ttd basah | Surat digital bernomor + QR verifikasi diakui sebagai keluaran final |
| Terima pengajuan yang penandatangannya belum tentu ada | **Validasi rantai penandatangan bisa di-resolve pada saat submit**, bukan saat approval |
| Gagal saat Dosen PA sedang cuti | Delegasi + eskalasi otomatis setelah SLA lewat |
| Bikin izin resmi lebih ribet daripada kabur lewat pagar | Alur pengajuan ≤ 60 detik; status transparan; tanpa pengulangan input yang sistem sudah tahu |

Baris terakhir adalah risiko produk terbesar epic ini: **sistem perizinan yang lebih ketat tapi
lebih menyusahkan akan menaikkan angka keluar tanpa izin.** Ukuran keberhasilan bukan "berapa izin
tercatat", melainkan rasio izin resmi terhadap total keluar (M4 di dokumen riset).

### SCAMPER — dua langkah yang paling berbuah

**Combine.** "Petugas Piket Asrama" (Form A) dan "Pelatih Harian Asrama" (Form B) bukan dua
mekanisme. Keduanya adalah *"staf asrama yang sedang bertugas pada tanggal keberangkatan"* —
sumbernya satu tabel yang sudah ada, `jadwal_petugas`. Digabungkan menjadi satu strategi resolver
berparameter, bukan dua implementasi.

**Eliminate.** Apa yang bisa dihapus dari form kertas tanpa ada yang kehilangan apa pun?
Nama, Kelas/Prodi, NIRM, No. HP, dan Nomor Kamar — **sistem sudah tahu semuanya**. Mahasiswa cukup
mengisi 4 hal: *keperluan, tujuan, kapan berangkat, kapan kembali*. Dari 11 field menjadi 4.
Itu pengurangan 64 % beban input, gratis, hanya karena datanya sudah ada.

> Peringatan penting: **field yang dihapus dari form tetap harus di-*snapshot* ke database.**
> Kalau mahasiswa naik kelas atau pindah kamar, surat lama harus tetap menampilkan data saat surat
> itu diterbitkan. Data hidup untuk pengisian; data beku untuk arsip. Lihat §7.3.

---

## 3. Requirements

### 3.1 Fungsional

| ID | Kebutuhan |
|---|---|
| F1 | Mahasiswa mengajukan izin dengan memilih jenis izin; sistem mengisi otomatis data identitas |
| F2 | Sistem membentuk rantai persetujuan sesuai jenis izin, dengan penandatangan yang di-resolve otomatis |
| F3 | Persetujuan berjalan **berurutan**; langkah N+1 baru terbuka setelah langkah N disetujui |
| F4 | Penolakan di langkah mana pun menghentikan seluruh alur dan wajib disertai alasan |
| F5 | Mahasiswa dapat memantau posisi pengajuan secara real time dan membatalkan selama belum disetujui penuh |
| F6 | Setelah disetujui penuh, sistem menerbitkan surat PDF bernomor, ber-QR, memuat daftar persetujuan digital |
| F7 | Scan gerbang keluar/masuk terhubung ke izin aktif; status ditulis `izin`, bukan `diluar`/`telat` |
| F8 | Izin yang disetujui membebaskan mahasiswa dari presensi kegiatan wajib & UKM yang beririsan waktunya (`Alpha` → `Izin`) |
| F9 | Kembali melewati `waktu_kembali` menghasilkan usulan pelanggaran otomatis (butuh konfirmasi staf) |
| F10 | Untuk jenis izin tertentu (delegasi/IB), pihak di lokasi tujuan dapat mengonfirmasi kedatangan **tanpa akun** |
| F11 | Admin dapat menambah/mengubah jenis izin dan rantai penandatangannya **tanpa deploy** |
| F12 | Rekap & laporan: siapa di luar sekarang, tunggakan persetujuan, rekap bulanan (PDF/Excel) |
| F13 | Seluruh aksi persetujuan tercatat lengkap (siapa, kapan, dari mana) dan tidak dapat diubah |

### 3.2 Non-fungsional

| Aspek | Target | Alasan |
|---|---|---|
| Skala | ~50–300 pengajuan/minggu, puncak Jumat sore (**[ASUMSI A8]**) | Beban sangat ringan; MySQL existing lebih dari cukup |
| Latensi sistem | Halaman inbox & persetujuan < 500 ms | Approver memutuskan dari HP, sering di sinyal lemah |
| **Latensi manusia** | Median pengajuan → disetujui < 4 jam | **Ini bottleneck sebenarnya**, bukan database |
| Ketersediaan gerbang | Verifikasi harus tetap jalan tanpa internet | **[ASUMSI A4]** — surat PDF cetak + QR jadi fallback wajib |
| Auditability | Jejak persetujuan permanen, tidak bisa diedit/dihapus | Surat dinas; jadi bukti bila mahasiswa bermasalah di jalan |
| Privasi | Keperluan izin bersifat pribadi; hanya penandatangan dalam rantai + admin yang boleh melihat | Cegah IDOR lintas-prodi/UKM (pola scoping Epic 01) |

### 3.3 Batasan

- Laravel 13 / PHP 8.3, MySQL, tanpa Redis, tanpa mail server terkonfigurasi, tanpa queue worker terjamin.
  → **Jangan merancang alur yang bergantung pada queue atau email untuk jalur kritis.**
  Notifikasi dibuat *in-app first*; kanal lain sebagai pelengkap opsional.
- Tim: 1 developer. → Hindari dependensi baru yang butuh pembelajaran; utamakan yang sudah dipakai.
- Konsistensi UI: Blade + Tailwind v4 + Flowbite + Alpine. → Tidak ada SPA, tidak ada framework baru.

---

## 4. ADR-006 — Mesin Alur Persetujuan Perizinan

**Status:** Proposed · **Deciders:** Pak Yongki, Kepala Asrama, Naufal · **Tanggal:** 5 Agustus 2026

### Context

Dua form perizinan punya struktur identik dengan dua slot penandatangan berbeda (§2, Asumsi 1).
Riset menunjukkan kemungkinan besar ada jenis izin lain yang belum terwakili (**[ASUMSI A3]**).
Codebase sudah punya preseden buruk (Kegiatan Wajib: 3 tabel identik) dan preseden baik
(Epic 01: satu mesin generik) untuk masalah berbentuk sama.

### Decision

Adopsi **mesin alur generik berbasis data**: jenis izin dan rantai penandatangannya disimpan sebagai
baris tabel (`jenis_izins`, `izin_workflow_steps`); setiap pengajuan **membekukan** rantainya menjadi
baris-baris `izin_approvals` pada saat submit. Alur bersifat linear dan berurutan.

### Opsi yang dipertimbangkan

**Opsi A — Dua tabel form, kolom tanda tangan hardcoded**
(`ttd_pembina_at`, `ttd_dosen_pa_at`, `ttd_piket_at`, `ttd_kepala_at`, …)

| Dimensi | Penilaian |
|---|---|
| Kompleksitas awal | Rendah |
| Skalabilitas | Buruk — jenis izin baru = migrasi + model + view + cabang `if` |
| Auditability | Buruk — tidak ada tempat menyimpan catatan/penolakan per langkah |
| Kesesuaian preseden | **Mengulang kesalahan Kegiatan Wajib** yang sudah ditinggalkan di Epic 01 |

**Pros:** paling cepat ditulis hari ini; familiar.
**Cons:** setiap perubahan kebijakan (mis. "IB > 3 hari perlu ttd Wadir") jadi pekerjaan developer +
deploy; menambah kolom `boolean` untuk setiap kemungkinan penandatangan; tabel melebar tanpa batas.
→ **Ditolak.**

**Opsi B — Mesin alur generik berbasis data (DIPILIH)**

| Dimensi | Penilaian |
|---|---|
| Kompleksitas awal | Sedang — 5 tabel dirancang benar + satu service layer |
| Skalabilitas | Sangat baik — jenis izin baru = beberapa `INSERT`, 0 baris kode |
| Auditability | Sangat baik — satu baris per langkah, memuat aktor/waktu/catatan |
| Familiaritas tim | Menengah — polanya sama dengan `ukm_jadwals.status_verifikasi` yang sudah dikuasai |

**Pros:** kebijakan berubah tanpa deploy; satu layar persetujuan melayani semua jenis izin; jejak
audit gratis sebagai efek samping struktur; siap untuk jenis izin yang belum ketahuan (**[A3]**).
**Cons:** butuh disiplin — logika transisi harus terkurung di satu service, bukan tersebar di
controller; admin bisa merusak alur kalau UI konfigurasinya lemah (mitigasi: validasi rantai + §8.5).
→ **Dipilih.**

**Opsi C — Pakai paket workflow/state-machine pihak ketiga** (mis. `spatie/laravel-model-states`
atau engine BPMN)

| Dimensi | Penilaian |
|---|---|
| Kompleksitas | Tinggi — konsep baru (state class, transition class, guard) untuk 1 developer |
| Nilai tambah | Rendah — alurnya linear 4 langkah, bukan graf bercabang |
| Ketergantungan | Menambah dependensi yang harus ikut diupgrade tiap versi Laravel |

**Pros:** transisi terjamin benar oleh library; siap untuk alur bercabang di masa depan.
**Cons:** *over-engineering.* Masalahnya adalah antrean tanda tangan berurutan, bukan orkestrasi
proses bisnis. Beban belajarnya tidak sepadan.
→ **Ditolak** (tinjau ulang bila muncul alur paralel/bercabang).

### Trade-off analysis

Opsi B mengikuti filosofi yang sama dengan ADR-004 dan ADR-005: **pilih jalur dengan nilai jangka
panjang tinggi dan scope terkendali, dan terima biaya di muka yang jelas dan terbatas.** Biaya di
muka Opsi B adalah satu service layer yang harus ditulis dengan disiplin. Biaya Opsi A dibayar
selamanya, sedikit demi sedikit, setiap kali kebijakan asrama berubah — dan kebijakan asrama
**pasti** berubah.

Perhatikan juga: Opsi A terlihat lebih murah hanya karena hari ini kita mengenal 2 form. Riset
belum memastikan itu daftar lengkapnya (**[A3]**). Opsi B memberi kelonggaran terhadap
ketidaktahuan itu dengan biaya rendah — itulah alasan sebenarnya memilihnya.

### Consequences

- **Lebih mudah:** menambah/mengubah jenis izin & rantai ttd tanpa deploy; satu layar persetujuan
  untuk semua jenis; laporan generik; jejak audit otomatis.
- **Lebih sulit:** perlu UI admin untuk mengonfigurasi alur (ada biayanya, lihat frontend doc §M5);
  logika transisi harus dijaga terpusat, dan itu butuh disiplin code review.
- **Perlu ditinjau ulang:** bila muncul kebutuhan langkah **paralel** (dua orang menyetujui
  bersamaan) atau **bercabang** (durasi > 3 hari lewat jalur berbeda), `urutan` linear perlu
  berkembang jadi graf → kandidat ADR lanjutan.

### Action items

1. [ ] Buat 5 migrasi + 5 model (§7.2–7.3)
2. [ ] Tulis `PengajuanIzinService` sebagai satu-satunya penjaga transisi status (§7.6)
3. [ ] Seed 4 jenis izin awal dari kedua form (§7.4)

---

## 5. ADR-007 — Resolusi Penandatangan (siapa yang harus tanda tangan?)

**Status:** Proposed · **Deciders:** Pak Yongki, Kepala Asrama, Naufal · **Tanggal:** 5 Agustus 2026

### Context

Empat slot penandatangan pada form ditentukan oleh empat mekanisme yang **berbeda secara mendasar**:

| Slot pada form | Ditentukan oleh | Sudah ada di sistem? |
|---|---|---|
| Pembina ORMAWA/UKM | UKM yang ditulis mahasiswa | ✅ `ukm_members.peran = 'pembina'` |
| Ketua Program Studi | Prodi mahasiswa | ❌ |
| Dosen PA | Kelas mahasiswa | ❌ |
| Petugas Piket / Pelatih Harian | **Tanggal keberangkatan** | ⚠️ `jadwal_petugas` ada, tanpa pembeda peran |
| Kepala Asrama / Unit Kemahasiswaan | Jabatan tunggal institusi | ❌ |

`users.role_id` bernilai tunggal dan global — tidak sanggup merepresentasikan satu pun dari empat
mekanisme di atas secara benar (§2, Asumsi 2).

### Decision

Perkenalkan tabel **`pejabats`** (pemetaan jabatan → orang, dengan lingkup opsional) dan sebuah
lapisan **resolver berstrategi**. Setiap langkah alur menyatakan *strategi* mana yang dipakai untuk
menemukan penandatangannya. Strategi yang datanya sudah ada (**Pembina UKM**, **Petugas Piket**)
membaca tabel existing — **tidak menduplikasi data**.

`users.role_id` **tidak diubah sama sekali**. Otorisasi menu tetap memakai `EnsureUserHasRole`
seperti sekarang; "boleh menandatangani langkah ini" ditentukan oleh **kepemilikan langkah**
(`izin_approvals.approver_user_id`), bukan oleh role.

> Ini pemisahan yang penting: **role menjawab "boleh membuka halaman apa"; resolver menjawab
> "berwenang atas dokumen yang mana".** Mencampur keduanya adalah sumber kekacauan RBAC yang
> disinggung ADR-004.

### Opsi yang dipertimbangkan

**Opsi A — Tambah role global (`kaprodi`, `dosen_pa`, `kepala_asrama`, …)**

| Dimensi | Penilaian |
|---|---|
| Kompleksitas | Rendah di permukaan |
| Kebenaran | **Salah secara model** — role tunggal, sedangkan jabatan bisa rangkap |
| Skalabilitas | Buruk — tidak bisa menjawab "Dosen PA **milik siapa**" |

**Cons:** seorang dosen tidak bisa jadi Dosen PA *dan* Kaprodi; tetap butuh tabel pemetaan terpisah
untuk tahu dosen mana milik kelas mana — jadi tabelnya tetap harus dibuat, dan role-nya jadi mubazir.
→ **Ditolak.**

**Opsi B — Tabel `pejabats` + resolver berstrategi (DIPILIH)**

| Dimensi | Penilaian |
|---|---|
| Kompleksitas | Sedang — 1 tabel + 1 service dengan 4 strategi kecil |
| Kebenaran | Tepat — jabatan rangkap, berlingkup, dan berbatas waktu semuanya terwakili |
| Reuse | Tinggi — memakai `ukm_members` & `jadwal_petugas` apa adanya |
| Skalabilitas | Baik — jabatan baru = 1 baris; strategi baru = 1 class kecil |

**Pros:** satu orang bisa memegang beberapa jabatan; jabatan bisa dibatasi lingkup (prodi/blok) dan
masa berlaku; tidak menyentuh `users.role_id` sehingga **nol risiko regresi** pada seluruh
middleware & menu existing.
**Cons:** ada satu lapisan tak-langsung yang harus dipahami pembaca kode baru; butuh UI admin untuk
mengisi pejabat (kecil).
→ **Dipilih.**

**Opsi C — Admin menunjuk penandatangan manual per pengajuan**

| Dimensi | Penilaian |
|---|---|
| Kompleksitas teknis | Sangat rendah |
| Beban operasional | **Sangat tinggi** — admin jadi bottleneck di setiap pengajuan |

**Cons:** memindahkan pekerjaan ke manusia, kebalikan dari tujuan epic. → **Ditolak** (tetap
disediakan sebagai *fallback* darurat bila resolver gagal, lihat §8.2).

### Trade-off analysis

Opsi A tampak lebih sederhana hanya kalau pertanyaannya *"siapa yang berperan sebagai Dosen PA?"*.
Pertanyaan sebenarnya adalah *"siapa Dosen PA **milik mahasiswa ini**?"* — dan begitu pertanyaannya
diucapkan dengan benar, terlihat bahwa role sama sekali bukan jawabannya. Ini contoh klasik masalah
yang terlihat seperti masalah otorisasi padahal sebenarnya masalah **pemodelan relasi**.

### Strategi resolver

| Strategi | Cara kerja | Sumber data | Untuk slot |
|---|---|---|---|
| `pejabat` | Cari `pejabats` yang `jabatan` sesuai; bila `lingkup` diisi, cocokkan dengan prodi/blok mahasiswa | `pejabats` (**baru**) | Ketua Prodi, Kepala Asrama |
| `dosen_pa` | Ambil `kelas.dosen_pa_id` milik mahasiswa | `kelas` (**+1 kolom**) | Dosen PA |
| `pembina_ukm` | Ambil `ukm_members` `peran='pembina'`, `status='aktif'` pada `ukm_id` yang dipilih mahasiswa | `ukm_members` (**existing**) | Pembina ORMAWA/UKM |
| `petugas_jaga` | Ambil `jadwal_petugas` pada `tanggal_berangkat`; saring menurut role petugas bila `parameter` diisi | `jadwal_petugas` (**existing**) | Petugas Piket / Pelatih Harian |

Semua strategi mengembalikan **daftar kandidat**. Langkah menentukan modenya:
`any` (cukup satu dari kandidat) atau `all` (semua kandidat wajib). Petugas piket → `any`
(dua petugas per hari, cukup salah satu).

### Aturan wajib: resolve saat submit, bukan saat approval

Resolusi dijalankan **sekali, pada saat pengajuan disubmit**, dan hasilnya **dibekukan** ke
`izin_approvals.approver_user_id` + `label_snapshot`.

Alasannya tiga, dan semuanya penting:

1. **Gagal lebih awal.** Kalau kelas mahasiswa belum punya Dosen PA, pengajuan **ditolak saat
   submit** dengan pesan jelas ("Kelas 2A belum memiliki Dosen PA — hubungi admin"), bukan mandek
   diam-diam berhari-hari (**[ASUMSI A2]** — ini mitigasinya).
2. **Stabilitas dokumen.** Kalau Kaprodi berganti di tengah proses, surat yang sedang berjalan tetap
   dimiliki orang yang benar; tidak ada dokumen yang tiba-tiba pindah meja.
3. **Kebenaran arsip.** Setahun kemudian, surat harus tetap menunjukkan siapa yang menandatangani
   saat itu — bukan siapa yang menjabat hari ini.

Pengecualian: `petugas_jaga` di-resolve **saat langkahnya terbuka**, bukan saat submit — karena
jadwal piket bisa berubah antara pengajuan dan keberangkatan. Perbedaan ini disimpan sebagai flag
`resolve_saat` (`submit` | `langkah_aktif`) pada definisi langkah.

### Consequences

- **Lebih mudah:** jabatan rangkap, jabatan berlingkup, pergantian pejabat, dan riwayat jabatan
  semuanya jadi urusan data; `users.role_id` tidak tersentuh (**nol risiko regresi**).
- **Lebih sulit:** ada prasyarat data yang harus diisi sebelum modul bisa dipakai (Milestone 0);
  resolver yang gagal harus punya penanganan yang jelas dan ramah (§8.2).
- **Perlu ditinjau ulang:** bila kelak Dosen PA perlu ditetapkan **per mahasiswa** (bukan per kelas),
  tambahkan `users.dosen_pa_id` sebagai override dan ubah satu strategi — perubahan terisolasi di
  satu class.

### Action items

1. [ ] Migrasi `pejabats` + kolom `kelas.dosen_pa_id`
2. [x] ~~Kolom penanda peran pada `jadwal_petugas`~~ — **tidak diperlukan**, lihat §14.1
3. [ ] `ApproverResolver` + 4 strategi + **rantai fallback** (§14.3) + tes unit per strategi
4. [ ] UI admin "Data Pejabat" (kecil, mirip Data Petugas) + impor Excel Dosen PA per kelas

---

## 6. ADR-008 — Bentuk Tanda Tangan Digital & Verifikasi Surat

**Status:** Proposed · **Deciders:** Kepala Asrama, Pak Yongki, Naufal · **Tanggal:** 5 Agustus 2026

### Context

Surat izin adalah dokumen semi-formal berkop Kementerian Pertanian yang ditunjukkan ke pihak
eksternal (satpam, panitia lomba, RT tujuan, kadang orang tua). Menghapus tanda tangan basah
menimbulkan pertanyaan sah: **apa yang membuat surat digital ini dipercaya?**

### Decision

Tiga lapis, tanpa dependensi baru:

1. **Persetujuan = jejak audit yang tidak dapat diubah.** Setiap `izin_approvals` menyimpan
   `acted_by`, `acted_at`, `acted_ip`, `acted_user_agent`. Surat PDF menampilkan daftar ini secara
   terbuka: nama, jabatan, tanggal-jam. *Yang membuktikan keabsahan adalah catatan yang bisa
   diperiksa, bukan gambar coretan.*
2. **Verifikasi publik lewat QR + tautan bertanda tangan bawaan Laravel**
   (`URL::signedRoute` + middleware `ValidateSignature` yang **sudah terdaftar** di aplikasi).
   Siapa pun yang memindai QR di surat melihat halaman ringkas: nomor surat, nama, masa berlaku,
   status (BERLAKU / KEDALUWARSA / DIBATALKAN).
3. **PDF cetak tetap menjadi keluaran resmi**, meniru tata letak form kertas (kop, blok
   persetujuan, kode form AR.009). Ini bukan nostalgia — ini mitigasi **[ASUMSI A4/A5]**: bila
   gerbang tanpa sinyal atau pihak eksternal menolak dokumen digital, kertas tetap berfungsi.

### Opsi yang dipertimbangkan

**Opsi A — Unggah gambar tanda tangan (specimen) lalu tempel ke PDF**

| Dimensi | Penilaian |
|---|---|
| Kesan "sah" bagi pengguna | Tinggi — terlihat paling mirip kertas |
| Keamanan | **Buruk** — gambar bisa disalin dari PDF mana pun lalu ditempel ke dokumen palsu |
| Risiko privasi/hukum | Tinggi — menyimpan specimen ttd pejabat adalah data sensitif (UU PDP) |

→ **Ditolak.** Opsi ini menambah *tampilan* keabsahan sambil **mengurangi** keabsahan yang
sebenarnya. Ini kombinasi terburuk: pemalsuan jadi lebih mudah daripada di kertas, karena gambar
digital sempurna untuk disalin.

**Opsi B — Jejak audit + QR + tautan bertanda tangan Laravel (DIPILIH)**

| Dimensi | Penilaian |
|---|---|
| Kompleksitas | Rendah — `ValidateSignature` & `simple-qrcode` sudah ada |
| Keamanan | Baik — verifikasi merujuk ke sumber kebenaran (database), bukan ke rupa dokumen |
| Familiaritas | Tinggi — pola yang sama dengan tiket SSO ADR-004, tapi memakai fasilitas framework |

**Pros:** tidak ada rahasia baru untuk dikelola; tautan bisa diberi masa berlaku; pemalsuan surat
langsung ketahuan saat QR dipindai; RT/panitia tidak perlu akun.
**Cons:** verifikasi butuh internet (mitigasi: PDF cetak tetap ada); secara hukum bukan tanda tangan
elektronik tersertifikasi.
→ **Dipilih.**

**Opsi C — Tanda tangan elektronik tersertifikasi (BSrE/PSrE)**

| Dimensi | Penilaian |
|---|---|
| Kekuatan hukum | Tertinggi — sah penuh menurut UU ITE |
| Kompleksitas & biaya | Tinggi — integrasi instansi, sertifikat per pejabat, proses pengadaan |
| Kesesuaian scope | Berlebihan untuk administrasi internal asrama |

→ **Ditunda.** Layak ditinjau ulang bila surat izin harus punya kekuatan hukum di luar kampus
(**[ASUMSI A6]** — konfirmasi ke Kepala Asrama). Desain saat ini tidak menghalangi migrasi ke sana:
lapisan penerbitan PDF terisolasi di satu service.

### Catatan penting soal HMAC buatan sendiri

ADR-004 memakai HMAC-SHA256 yang ditulis tangan — itu **tepat di sana**, karena tiketnya menyeberang
ke aplikasi lain (E-Klinik) yang tidak berbagi `APP_KEY`. Di Epic 03, penerbit dan pemeriksa adalah
**aplikasi yang sama**. Menulis ulang HMAC sendiri di sini berarti menambah kode kriptografi tanpa
alasan, sementara Laravel sudah menyediakan `signedRoute` yang setara dan sudah teruji.
→ **Gunakan fasilitas framework; jangan menyalin pola ADR-004 hanya karena sudah familiar.**

### Consequences

- **Lebih mudah:** tidak ada rahasia/sertifikat baru; verifikasi seketika; pemalsuan sulit.
- **Lebih sulit:** perlu edukasi singkat ke satpam & pejabat ("yang sah adalah QR-nya, bukan
  tampilannya") — masukkan ke rencana rilis, jangan dianggap urusan sepele.
- **Perlu ditinjau ulang:** bila muncul tuntutan TTE tersertifikasi (Opsi C).

---

## 7. System Design

### 7.1 ERD

```
                          ┌──────────────────────┐
                          │     jenis_izins      │  ← konfigurasi (data, bukan kode)
                          │──────────────────────│
                          │ id, kode, nama       │
                          │ butuh_bermalam       │
                          │ butuh_konfirmasi_tiba│
                          │ maks_durasi_jam      │
                          │ kode_form (AR.009)   │
                          └──────┬───────────────┘
                                 │ 1
                                 │ N
                          ┌──────┴───────────────┐
                          │ izin_workflow_steps  │  ← rantai penandatangan
                          │──────────────────────│
                          │ urutan, label        │
                          │ resolver, jabatan    │
                          │ lingkup, mode        │
                          │ kondisi, resolve_saat│
                          │ sla_jam              │
                          └──────────────────────┘
                                 ┊ (dibekukan saat submit)
                                 ▼
 users ──1───N──┐          ┌──────────────────────┐        ┌────────────────────┐
                └────────> │   pengajuan_izins    │ 1────N │  izin_approvals    │
 ukms ───0..1───────────>  │──────────────────────│        │────────────────────│
                           │ nomor_surat (unik)   │        │ urutan             │
 jenis_izins ─1──N──────>  │ user_id, jenis_izin  │        │ label_snapshot     │
                           │ ukm_id (nullable)    │        │ approver_user_id   │
                           │ keperluan, tujuan    │        │ status             │
                           │ waktu_berangkat/kembali│      │ catatan            │
                           │ *_snapshot (identitas)│       │ acted_by/at/ip/ua  │
                           │ status, langkah_aktif│        └────────────────────┘
                           │ qr_token             │
                           │ keluar_at, kembali_at│        ┌────────────────────┐
                           │ tiba_* (konfirmasi)  │ 0..1─> │  pelanggarans      │
                           └──────────────────────┘        │  (existing)        │
                                    ┊                      └────────────────────┘
        dibaca (read-only) oleh ────┼────> QRController (gerbang)
                                    ├────> presensi kegiatan wajib & UKM
                                    └────> laporan & dashboard

  Sumber resolver (dibaca, TIDAK diduplikasi):
     kelas.dosen_pa_id ─┐
     pejabats ──────────┼──> ApproverResolver ──> izin_approvals.approver_user_id
     ukm_members ───────┤
     jadwal_petugas ────┘
```

### 7.2 Migrasi

Ikuti konvensi penamaan existing (`2026_08_xx_xxxxxx_*`), `foreignId()->constrained()`, indeks eksplisit.

```php
// 1) prasyarat resolver ────────────────────────────────────────────
Schema::create('pejabats', function (Blueprint $table) {
    $table->id();
    $table->enum('jabatan', ['kaprodi', 'kepala_asrama', 'unit_kemahasiswaan', 'wadir_kemahasiswaan']);
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->enum('lingkup', ['global', 'prodi', 'blok'])->default('global');
    $table->unsignedBigInteger('lingkup_id')->nullable();   // prodi_id / blok_ruangan_id
    $table->date('mulai_menjabat')->nullable();
    $table->date('selesai_menjabat')->nullable();           // null = masih menjabat
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->index(['jabatan', 'lingkup', 'lingkup_id', 'is_active'], 'pejabats_resolver_idx');
});

Schema::table('kelas', fn (Blueprint $t) =>                  // tabel 'kelas' (bukan 'kelas_')
    $t->foreignId('dosen_pa_id')->nullable()->after('prodi_id')
      ->constrained('users')->nullOnDelete());

// 2) konfigurasi alur ──────────────────────────────────────────────
Schema::create('jenis_izins', function (Blueprint $table) {
    $table->id();
    $table->string('kode')->unique();                 // IZIN_KELUAR, IB, MENINGGALKAN_KELAS, DELEGASI
    $table->string('nama');
    $table->text('deskripsi')->nullable();
    $table->string('kode_form')->nullable();          // "AR.009"
    $table->boolean('butuh_bermalam')->default(false);
    $table->boolean('butuh_ukm')->default(false);     // form A: field *Anggota UKM/ORMAWA
    $table->boolean('butuh_konfirmasi_tiba')->default(false);
    $table->unsignedSmallInteger('maks_durasi_jam')->nullable();
    $table->unsignedSmallInteger('min_ajukan_jam')->nullable();  // H-x minimal
    $table->boolean('is_active')->default(true);
    $table->timestamps();
});

Schema::create('izin_workflow_steps', function (Blueprint $table) {
    $table->id();
    $table->foreignId('jenis_izin_id')->constrained('jenis_izins')->cascadeOnDelete();
    $table->unsignedTinyInteger('urutan');
    $table->string('label');                          // "Menyetujui, Pembina ORMAWA/UKM"
    $table->string('blok')->nullable();               // "Persetujuan Kegiatan & Akademik"
    $table->enum('resolver', ['pejabat', 'dosen_pa', 'pembina_ukm', 'petugas_jaga']);
    $table->json('jabatan')->nullable();              // ARRAY, bila resolver = pejabat.
                                                      // ["kepala_asrama","unit_kemahasiswaan"]
                                                      // = salah satu dari keduanya boleh (mode any)
    $table->enum('lingkup', ['global', 'prodi', 'blok'])->default('global');
    $table->enum('mode', ['any', 'all'])->default('any');
    $table->enum('kondisi', ['selalu', 'jika_ada_ukm', 'jika_bermalam'])->default('selalu');
    $table->enum('resolve_saat', ['submit', 'langkah_aktif'])->default('submit');
    // fallback bila resolver utama tidak menemukan kandidat (mis. Dosen PA belum terdata)
    $table->enum('fallback_resolver', ['pejabat', 'dosen_pa', 'pembina_ukm', 'petugas_jaga'])->nullable();
    $table->json('fallback_jabatan')->nullable();
    $table->unsignedSmallInteger('sla_jam')->default(24);
    $table->boolean('is_active')->default(true);
    $table->timestamps();

    $table->unique(['jenis_izin_id', 'urutan']);
});

// 3) transaksi ─────────────────────────────────────────────────────
Schema::create('pengajuan_izins', function (Blueprint $table) {
    $table->id();
    $table->string('nomor_surat')->unique()->nullable();   // diisi saat disetujui penuh
    $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
    $table->foreignId('jenis_izin_id')->constrained('jenis_izins');
    $table->foreignId('ukm_id')->nullable()->constrained('ukms')->nullOnDelete();

    $table->text('keperluan');
    $table->string('tujuan_lokasi');
    $table->text('alamat_tujuan')->nullable();
    $table->dateTime('waktu_berangkat');
    $table->dateTime('waktu_kembali');

    // snapshot identitas — form kertas mencetaknya, arsip harus membekukannya
    $table->string('nama_snapshot');
    $table->string('nirm_snapshot')->nullable();
    $table->string('kelas_snapshot')->nullable();
    $table->string('prodi_snapshot')->nullable();
    $table->string('no_kamar_snapshot')->nullable();
    $table->string('no_hp_snapshot')->nullable();

    $table->enum('status', [
        'draft', 'diajukan', 'disetujui', 'ditolak', 'dibatalkan',
        'berjalan', 'selesai', 'terlambat', 'kadaluarsa',
    ])->default('draft');
    $table->unsignedTinyInteger('langkah_aktif')->nullable();
    $table->timestamp('diajukan_at')->nullable();
    $table->timestamp('disetujui_at')->nullable();
    $table->text('alasan_penolakan')->nullable();

    $table->char('qr_token', 40)->unique()->nullable();     // handle publik, bukan id
    $table->timestamp('keluar_at')->nullable();             // scan gerbang keluar
    $table->timestamp('kembali_at')->nullable();            // scan gerbang masuk

    // konfirmasi tiba (delegasi/IB) — 1:1, sengaja tidak dipecah ke tabel terpisah
    $table->string('tiba_di')->nullable();
    $table->timestamp('tiba_at')->nullable();
    $table->string('tiba_dikonfirmasi_oleh')->nullable();   // nama RT/panitia (tanpa akun)
    $table->string('tiba_kontak')->nullable();
    $table->string('tiba_bukti_path')->nullable();

    $table->foreignId('pelanggaran_id')->nullable()->constrained('pelanggarans')->nullOnDelete();
    $table->timestamps();

    $table->index(['user_id', 'status']);
    $table->index(['status', 'waktu_kembali']);             // "siapa belum kembali?"
    $table->index(['status', 'waktu_berangkat']);           // job kadaluarsa
});

Schema::create('izin_approvals', function (Blueprint $table) {
    $table->id();
    $table->foreignId('pengajuan_izin_id')->constrained('pengajuan_izins')->cascadeOnDelete();
    $table->unsignedTinyInteger('urutan');
    $table->string('label_snapshot');                       // label saat surat dibuat
    $table->string('blok_snapshot')->nullable();
    $table->foreignId('approver_user_id')->nullable()->constrained('users')->nullOnDelete();
    $table->string('approver_nama_snapshot')->nullable();
    $table->enum('mode', ['any', 'all'])->default('any');
    $table->enum('status', ['menunggu', 'disetujui', 'ditolak', 'dilewati'])->default('menunggu');
    $table->text('catatan')->nullable();
    $table->timestamp('dibuka_at')->nullable();             // untuk hitung SLA
    $table->timestamp('acted_at')->nullable();
    $table->foreignId('acted_by')->nullable()->constrained('users')->nullOnDelete();
    $table->string('acted_ip', 45)->nullable();
    $table->string('acted_user_agent')->nullable();
    $table->timestamps();

    $table->unique(['pengajuan_izin_id', 'urutan']);
    $table->index(['approver_user_id', 'status']);          // inbox approver
});
```

**Catatan skema penting**

- `mode = 'all'` butuh lebih dari satu baris per urutan → gunakan `unique(pengajuan, urutan, approver_user_id)`
  bila mode `all` benar-benar dipakai. Untuk 4 langkah pada dua form ini, semuanya `any`;
  simpan `all` sebagai kemampuan tak terpakai dan **jangan diimplementasikan sampai ada yang butuh**.
- `qr_token` memakai token acak, **bukan** `id` — supaya orang tidak bisa menebak dan menelusuri
  surat orang lain dengan menaikkan angka.
- Kolom `tiba_*` sengaja disimpan di tabel utama (relasi 1:1, 5 kolom). Memecahnya ke tabel terpisah
  menambah `join` tanpa manfaat. Tinjau ulang bila kelak butuh multi-titik singgah.

### 7.3 Model & relasi

```
JenisIzin      hasMany WorkflowStep, hasMany PengajuanIzin
WorkflowStep   belongsTo JenisIzin
PengajuanIzin  belongsTo User, JenisIzin, Ukm(null), Pelanggaran(null)
               hasMany IzinApproval (orderBy urutan)
               scopeAktifPada($waktu), scopeBelumKembali()
IzinApproval   belongsTo PengajuanIzin, approver(User), aktor(User via acted_by)
Pejabat        belongsTo User; scopeUntuk($jabatan, $lingkup, $lingkupId)
Kelas          +belongsTo dosenPa(User)   ← tambahan pada model existing
```

Ikuti konvensi existing: `protected $guarded = ['id']` (seperti `Ukm`, `UkmJadwal`), bukan `$fillable`.

### 7.4 Seed data — dua form menjadi konfigurasi

Inilah bukti bahwa desainnya benar: **kedua form kertas berubah menjadi baris `INSERT`, bukan kode.**

```php
// A. IJIN KELUAR ASRAMA (AR.009) — jalur ORMAWA/UKM
$a = JenisIzin::create([
    'kode' => 'IZIN_KELUAR', 'nama' => 'Ijin Keluar Asrama', 'kode_form' => 'AR.009',
    'butuh_ukm' => true, 'butuh_konfirmasi_tiba' => true, 'maks_durasi_jam' => 24,
]);
$a->steps()->createMany([
  ['urutan'=>1,'blok'=>'Persetujuan Kegiatan & Akademik','label'=>'Menyetujui, Pembina ORMAWA/UKM',
   'resolver'=>'pembina_ukm','kondisi'=>'jika_ada_ukm','sla_jam'=>24],
  ['urutan'=>2,'blok'=>'Persetujuan Kegiatan & Akademik','label'=>'Mengetahui, Dosen PA',
   'resolver'=>'dosen_pa','sla_jam'=>24,
   // ← Dosen PA belum terdata (§14 no.3). Selama kolom kelas.dosen_pa_id kosong,
   //   langkah ini otomatis jatuh ke Kaprodi. Begitu data terisi, fallback berhenti
   //   dipakai dengan sendirinya — tanpa perubahan kode.
   'fallback_resolver'=>'pejabat','fallback_jabatan'=>['kaprodi']],
  ['urutan'=>3,'blok'=>'Validasi Asrama','label'=>'Mengetahui, Petugas Piket Asrama',
   'resolver'=>'petugas_jaga','resolve_saat'=>'langkah_aktif','mode'=>'any','sla_jam'=>12],
  ['urutan'=>4,'blok'=>'Validasi Asrama','label'=>'Memvalidasi, Kepala Asrama/Unit Kemahasiswaan',
   'resolver'=>'pejabat','jabatan'=>['kepala_asrama','unit_kemahasiswaan'],'mode'=>'any','sla_jam'=>12],
]);

// B. IJIN BERMALAM / MENINGGALKAN KELAS — jalur akademik
$b = JenisIzin::create([
    'kode' => 'IB', 'nama' => 'Ijin Bermalam / Meninggalkan Kelas',
    'butuh_bermalam' => true, 'butuh_konfirmasi_tiba' => true,
]);
$b->steps()->createMany([
  ['urutan'=>1,'blok'=>'Persetujuan Kegiatan & Akademik','label'=>'Menyetujui, Ketua Program Studi',
   'resolver'=>'pejabat','jabatan'=>'kaprodi','lingkup'=>'prodi','sla_jam'=>24],
  ['urutan'=>2,'blok'=>'Persetujuan Kegiatan & Akademik','label'=>'Mengetahui, Dosen Pembimbing Akademik',
   'resolver'=>'dosen_pa','sla_jam'=>24,
   'fallback_resolver'=>'pejabat','fallback_jabatan'=>['kaprodi']],
  // ↓ label BEDA dengan Form A, resolver SAMA — inilah inti ADR-006.
  //   'petugas1_id'/'petugas2_id' di jadwal_petugas tidak punya makna peran
  //   (diperlakukan identik di DashboardAdminController), jadi tidak ada yang
  //   perlu dibedakan. Kedua petugas jadi kandidat, cukup salah satu bertindak.
  ['urutan'=>3,'blok'=>'Validasi Asrama','label'=>'Mengetahui, Pelatih Harian Asrama',
   'resolver'=>'petugas_jaga','resolve_saat'=>'langkah_aktif','mode'=>'any','sla_jam'=>12],
  ['urutan'=>4,'blok'=>'Validasi Asrama','label'=>'Memvalidasi, Kepala Asrama/Unit Kemahasiswaan',
   'resolver'=>'pejabat','jabatan'=>['kepala_asrama','unit_kemahasiswaan'],'mode'=>'any','sla_jam'=>12],
]);
```

Menambah jenis izin ketiga (mis. "Izin Sakit / Pulang Berobat" dengan rantai lebih pendek) = satu
blok `INSERT` lagi. **Nol migrasi, nol controller, nol view baru.** Bandingkan dengan Opsi A
(ADR-006) yang menuntut migrasi + model + view + cabang `if` setiap kali.

### 7.5 State machine

```
                     ┌──────── batal ────────┐
                     ▼                       │
  draft ──ajukan──> diajukan ──semua ok──> disetujui ──scan keluar──> berjalan
    │                  │                       │                          │
    │                  │                       └── lewat waktu berangkat,  ├─ scan masuk ≤ waktu_kembali ──> selesai
    │                  │                           tak pernah keluar       │
    │                  │                           (job harian) ──> kadaluarsa
    │                  └── satu langkah ditolak ──> ditolak                └─ scan masuk > waktu_kembali ──> terlambat
    └── batal ──> dibatalkan                                                      └─> usulan Pelanggaran (butuh konfirmasi staf)
```

| Status | Arti | Siapa yang memicu |
|---|---|---|
| `draft` | Belum diajukan; masih bisa disunting | Mahasiswa |
| `diajukan` | Sedang berjalan di rantai persetujuan | Mahasiswa |
| `disetujui` | Semua langkah lolos; surat terbit & bernomor | Sistem |
| `ditolak` | Dihentikan di salah satu langkah | Approver |
| `dibatalkan` | Ditarik mahasiswa sebelum disetujui penuh | Mahasiswa |
| `berjalan` | Sudah keluar gerbang | Scan gerbang |
| `selesai` | Sudah kembali tepat waktu | Scan gerbang |
| `terlambat` | Kembali melewati `waktu_kembali` | Scan gerbang |
| `kadaluarsa` | Disetujui tapi tidak pernah dipakai | Job terjadwal |

**Aturan mutlak:** seluruh transisi hanya boleh terjadi di `PengajuanIzinService`. Tidak ada
`$izin->update(['status' => ...])` di controller mana pun. Ini satu-satunya cara menjaga janji
ADR-006 tetap utuh setelah 6 bulan dan beberapa fitur tambahan.

### 7.6 Service layer

| Service | Tanggung jawab |
|---|---|
| `ApproverResolver` | Menerima `WorkflowStep` + `PengajuanIzin`, mengembalikan daftar kandidat approver. Berisi 4 strategi kecil. Tidak menyentuh status. |
| `PengajuanIzinService` | `ajukan()`, `setujui()`, `tolak()`, `batalkan()`, `tandaiKeluar()`, `tandaiKembali()`. **Satu-satunya** penulis kolom `status`. Setiap method dibungkus transaksi + `lockForUpdate`. |
| `NomorSuratService` | Menghasilkan nomor surat berurutan & unik (mis. `AR.009/0042/VIII/2026`). Menggunakan penguncian baris agar aman dari race condition. |
| `SuratIzinPdfService` | Merender Blade → PDF via dompdf, menyisipkan QR (`simple-qrcode`) dan daftar persetujuan. |
| `IzinGateResolver` | **Read-only.** Menjawab: "apakah user ini punya izin yang berlaku pada waktu ini?" Dipanggil dari `QRController`. |
| `PembebasanPresensiService` | Menandai `Izin` pada presensi kegiatan wajib & UKM yang beririsan dengan rentang izin. |

### 7.7 Routes

Ikuti konvensi existing: `home.` untuk mahasiswa, `admin.` untuk staf.

```php
// Mahasiswa — role:user, prefix name 'home.'
GET    /dashboard/izin                        home.izin.index
GET    /dashboard/izin/create                 home.izin.create
POST   /dashboard/izin                        home.izin.store        (langsung diajukan)
GET    /dashboard/izin/{izin}                 home.izin.show         (+ progress tracker)
PATCH  /dashboard/izin/{izin}/batal           home.izin.batal
GET    /dashboard/izin/{izin}/surat           home.izin.surat        (PDF)

// Staf — role:admin,operator,pelatih,pembina, prefix name 'admin.'
GET    /izin/persetujuan                      admin.izin.inbox       (langkah menunggu MILIK SAYA)
GET    /izin/persetujuan/{approval}           admin.izin.review
PATCH  /izin/persetujuan/{approval}           admin.izin.putuskan    (setujui|tolak + catatan)
POST   /izin/persetujuan/massal               admin.izin.massal      (lihat §8.6 sebelum membangun)
GET    /data-izin                             admin.izin.data
GET    /data-izin/{izin}                      admin.izin.detail
GET    /izin/monitor                          admin.izin.monitor     ("siapa di luar sekarang")
POST   /generate-laporan-izin                 admin.izin.laporan

// Admin saja — konfigurasi
Route::resource('jenis-izin', JenisIzinController::class);           admin.jenis-izin.*
GET/POST /jenis-izin/{jenis}/langkah                                 admin.jenis-izin.langkah.*
Route::resource('pejabat', PejabatController::class);                admin.pejabat.*

// Publik — tanpa auth, middleware 'signed'
GET    /verifikasi-izin/{qr_token}            izin.verifikasi        (halaman keabsahan)
GET    /izin/{qr_token}/konfirmasi-tiba       izin.tiba.form         (untuk RT/panitia)
POST   /izin/{qr_token}/konfirmasi-tiba       izin.tiba.store
```

**Tidak ada route baru untuk gerbang.** Integrasi dilakukan di dalam `QRController::presense()` yang
sudah ada — lihat §7.8.

### 7.8 Integrasi gerbang: satu blok `if`, bukan penulisan ulang

`QRController::presense()` panjang, penuh percabangan, dan **dipakai setiap hari**. Menyentuhnya
adalah risiko regresi terbesar di epic ini. Karena itu perubahannya dibatasi sekeras mungkin:

```php
// Di dalam presense(), SEBELUM logika penentuan 'telat' yang sudah ada:
$izin = app(IzinGateResolver::class)->aktifUntuk($user, now());

if ($izin) {
    // Cabang izin: tidak menyentuh sedikit pun logika 'telat' existing
    app(PengajuanIzinService::class)->catatScanGerbang($izin, $user, now());
    $presenceData['log_status'] = 'izin';        // enum SUDAH ADA, selama ini kosong
    $user->update(['status' => 'izin']);         // enum SUDAH ADA, selama ini kosong
    // ... simpan presence, lalu return
}

// ↓ seluruh logika existing tetap utuh, tak tersentuh
```

Tiga hal yang membuat ini aman:

1. **Aditif, bukan modifikatif.** Mahasiswa tanpa izin aktif menempuh jalur kode yang persis sama
   seperti hari ini. Regresi hanya mungkin terjadi pada mahasiswa yang punya izin aktif — kelompok
   yang hari ini jumlahnya nol.
2. **Enumnya sudah ada.** `'izin'` sudah valid di `users.status` dan `presences.log_status`.
   **Tidak ada migrasi pada tabel yang dipakai harian** — ini penting, karena `ALTER TABLE` pada
   tabel presensi produksi adalah risiko yang tidak perlu diambil.
3. **Resolver bersifat read-only** dan mudah dites terpisah dari controller.

Filosofinya sama dengan ADR-004: **selesaikan masalah dengan sentuhan sekecil mungkin pada kode yang
sudah berjalan.**

### 7.9 Integrasi presensi kegiatan wajib & UKM

Saat izin berpindah ke `disetujui`, jalankan `PembebasanPresensiService`:

```
untuk setiap jadwalKegiatanAsrama & ukm_jadwal milik mahasiswa
    yang tanggal+jamnya beririsan [waktu_berangkat, waktu_kembali]:
        presensi.status_kehadiran: 'Alpha' → 'Izin'   (hanya bila masih 'Alpha')
```

Dua titik pemanggilan, keduanya perlu:

- **Saat izin disetujui** — untuk jadwal yang sudah dibuat sebelumnya.
- **Saat jadwal dibuat** (`UkmJadwalController::store`, `KegiatanAsramaController::createJadwalKegiatanStore`) —
  untuk jadwal yang dibuat setelah izin disetujui. Fan-out yang sekarang menulis `'Alpha'` secara
  buta; tambahkan pengecekan izin aktif.

Aturan `'Alpha' → 'Izin'` saja (jangan menimpa `'Hadir'`) mencegah kasus aneh: mahasiswa yang
ternyata hadir meski punya izin tidak boleh berubah jadi "Izin".

### 7.10 Integrasi pelanggaran

Kembali melewati `waktu_kembali` → buat `Pelanggaran` dengan `statusPelanggaran = 'submitted'`
(**bukan** langsung `Done`) dan tautkan `pengajuan_izins.pelanggaran_id`.

**Jangan menghukum otomatis.** Ada terlalu banyak alasan sah untuk terlambat (kereta batal, hujan,
lomba molor). Sistem mengusulkan; manusia memutuskan. Ini juga menjaga kepercayaan pengguna — sistem
yang menjatuhkan poin tanpa didengar akan dihindari, dan itu langsung merusak metrik M4.

Butuh seed `JenisPelanggaran` baru: *"Terlambat kembali dari izin"*, di bawah kategori kedisiplinan
yang sudah ada.

---

## 8. Kasus tepi, konkurensi, dan kegagalan

### 8.1 Dua approver menekan tombol bersamaan (mode `any`)

Petugas piket ada dua orang per tanggal. Keduanya bisa membuka inbox yang sama.

```php
DB::transaction(function () use ($approval, $user) {
    $fresh = IzinApproval::whereKey($approval->id)->lockForUpdate()->first();
    abort_if($fresh->status !== 'menunggu', 409, 'Langkah ini sudah diputuskan oleh petugas lain.');
    // ... proses
});
```

Yang pertama menang; yang kedua mendapat pesan sopan, bukan error 500.

### 8.2 Rantai penandatangan tidak bisa di-resolve

Contoh: kelas mahasiswa belum punya Dosen PA (**[ASUMSI A2]** — kemungkinan besar terjadi di awal).

- **Saat submit:** validasi seluruh langkah ber-`resolve_saat = 'submit'`. Bila ada yang kosong →
  **tolak submit** dengan pesan spesifik dan dapat ditindaklanjuti: *"Kelas 2A belum memiliki Dosen
  PA. Hubungi admin asrama."* Jangan pernah membiarkan pengajuan masuk lalu diam.
- **Saat langkah `petugas_jaga` terbuka** tapi tidak ada jadwal piket untuk tanggal itu →
  fallback berjenjang: (1) siapa pun ber-role `pelatih`/`operator` yang aktif, (2) bila tetap kosong,
  eskalasi ke `kepala_asrama` dan tandai `dilewati` dengan catatan sistem yang tercatat di audit.
- **Fallback darurat:** admin boleh menunjuk approver manual untuk satu pengajuan (Opsi C ADR-007
  yang ditolak sebagai desain utama, tetapi berguna sebagai katup pengaman). Aksinya dicatat.

### 8.3 Approver adalah pemohonnya sendiri

Pembina UKM yang mengajukan izin untuk dirinya sendiri. → Bila `approver_user_id === user_id`,
tandai langkah `dilewati` dengan catatan *"dilewati otomatis: pemohon adalah penandatangan"*, atau
naikkan ke pejabat di atasnya. **Putuskan mana yang benar bersama Kepala Asrama** — ini keputusan
kebijakan, bukan keputusan teknis.

### 8.4 Pengajuan tumpang tindih

Mahasiswa punya izin aktif 10–14 Agustus lalu mengajukan lagi untuk 12 Agustus. → Validasi:
tolak bila ada pengajuan berstatus `diajukan|disetujui|berjalan` yang rentang waktunya beririsan.
Ini juga mencegah `IzinGateResolver` menghadapi ambiguitas dua izin aktif sekaligus.

### 8.5 Admin mengubah alur saat ada pengajuan berjalan

Tidak berpengaruh — rantai **sudah dibekukan** ke `izin_approvals` saat submit. Perubahan hanya
berlaku untuk pengajuan berikutnya. Ini manfaat langsung dari keputusan pembekuan di ADR-007, dan
alasan kenapa `label_snapshot` disimpan alih-alih di-`join` ke `izin_workflow_steps`.

### 8.6 Persetujuan massal — bahaya yang perlu disadari

Bulk approve menyelesaikan nyeri approver (P1), tetapi bila terlalu mudah, persetujuan berubah
menjadi stempel kosong dan **seluruh nilai kontrolnya hilang**. Rekomendasi:

- Bangun **hanya setelah** wawancara §3.4 memastikan approver memang butuh (jangan asumsikan).
- Batasi: maksimal 10 per aksi, wajib mencentang satu per satu (bukan "pilih semua"), tidak tersedia
  untuk langkah validasi akhir Kepala Asrama.
- Tetap catat satu baris audit per pengajuan, dengan penanda bahwa aksinya massal.

### 8.7 Job terjadwal

| Job | Frekuensi | Tugas |
|---|---|---|
| `TandaiIzinKadaluarsa` | tiap jam | `disetujui` + lewat `waktu_berangkat` + `keluar_at` null → `kadaluarsa` |
| `PeriksaKeterlambatan` | tiap 30 mnt | `berjalan` + lewat `waktu_kembali` → tandai & beri tahu piket |
| `IngatkanApproverTertunggak` | harian, pagi | Langkah `menunggu` melewati `sla_jam` → notifikasi in-app |

Semuanya idempoten dan aman dijalankan berulang — karena **[batasan §3.3]** tidak ada jaminan
scheduler selalu hidup.

---

## 9. User stories & acceptance criteria

| ID | Sebagai | Saya ingin | Acceptance criteria (ringkas) |
|---|---|---|---|
| **3.1** | Mahasiswa | mengajukan izin dalam < 60 detik | Identitas terisi otomatis; hanya 4 field diisi manual; validasi durasi & tumpang tindih; submit gagal disertai pesan yang bisa ditindaklanjuti |
| **3.2** | Mahasiswa | tahu pengajuan saya sedang di meja siapa | Progress tracker 4 langkah dengan nama & jabatan, waktu tunggu per langkah, alasan bila ditolak |
| **3.3** | Approver | memutuskan dalam < 15 detik dari HP | Inbox hanya berisi langkah milik saya yang `menunggu`; **konteks lengkap tanpa scroll**; setujui/tolak + catatan; tolak wajib beralasan |
| **3.4** | Petugas Piket | tahu siapa yang berhak keluar malam ini | Daftar izin `disetujui`/`berjalan` untuk tanggal berjalan; pencarian per nama/NIRM |
| **3.5** | Kepala Asrama | tahu siapa yang sedang di luar | Halaman monitor real time: di luar sekarang, jatuh tempo hari ini, lewat waktu |
| **3.6** | Sistem | tidak salah mencatat kehadiran | Scan gerbang saat izin aktif → `log_status='izin'`, bukan `telat`; presensi beririsan → `Izin`, bukan `Alpha` |
| **3.7** | RT/panitia di tujuan | mengonfirmasi kedatangan dalam 10 detik | Pindai QR → form 3 field (nama, kontak, opsional foto) → simpan; **tanpa akun, tanpa instalasi** |
| **3.8** | Admin | laporan bulanan tanpa menghitung kertas | Filter periode/prodi/blok/jenis; ekspor PDF & Excel memakai pola `FromView` existing |
| **3.9** | Admin | menambah jenis izin baru tanpa developer | CRUD jenis izin + langkah; validasi rantai; pratinjau alur |
| **3.10** | Semua | surat digital dipercaya | PDF meniru form kertas + kop + kode form; daftar persetujuan bertanggal; QR verifikasi publik |

---

## 10. Roadmap

Setiap milestone dirancang **bisa dirilis sendiri** dan memberi nilai nyata — sehingga bila waktu
habis di tengah jalan, yang sudah jadi tetap terpakai.

| M | Isi | Durasi | Nilai yang dirilis |
|---|---|---|---|
| **M0** | Migrasi `pejabats` + `kelas.dosen_pa_id`; UI Data Pejabat; **isi Kaprodi + Kepala Asrama + Unit Kemahasiswaan** (±6–10 orang). Dosen PA **tidak** termasuk — ditangani fallback §14.3 | 1 hari | Belum ada fitur, tapi jadi fondasi resolver |
| **M1** | Skema inti (5 tabel), model, `ApproverResolver`, `PengajuanIzinService`, seed 2 jenis izin | 5 hari | Mesinnya jadi; teruji lewat unit test |
| **M2** | Pengajuan mahasiswa + progress tracker + inbox approver + setujui/tolak | 5 hari | **Alur end-to-end tanpa kertas jalan.** Rilis terbatas 1 prodi. |
| **M3** | Nomor surat + PDF ber-kop + QR + halaman verifikasi publik | 3 hari | Surat digital bisa dicetak & dipercaya. Mitigasi [A4/A5]. |
| **M4** | **Integrasi:** gerbang, pembebasan presensi, pelanggaran keterlambatan | 4 hari | **Metrik M3 riset (kesalahan pencatatan → 0).** Bagian paling bernilai. |
| **M5** | Konfirmasi tiba (RT), monitor real time, notifikasi in-app, job terjadwal | 5 hari | Lifecycle penuh + visibilitas Kepala Asrama |
| **M6** | CRUD jenis izin & langkah, laporan PDF/Excel, hardening, dokumentasi | 4 hari | Mandiri dari developer |

**Total ± 28 hari kerja (5–6 minggu).**

**Total menyusut jadi ± 27 hari** setelah M0 diringankan (§14.3).

**Jalur kritis:** M0 → M1 → M2. M0 sekarang hanya butuh data ±6–10 pejabat, bukan Dosen PA seluruh
kelas — pengumpulan Dosen PA berjalan paralel dan tidak memblokir apa pun. Yang tetap tidak boleh
dilewati: **jangan mulai M1 sebelum daftar Kaprodi, Kepala Asrama, dan Unit Kemahasiswaan ada di
tangan**, karena tanpa itu rantai persetujuan tidak bisa di-resolve sama sekali.

**Bila waktu dipangkas:** buang M6 dan sebagian M5, **jangan pernah buang M4.** M4 adalah satu-satunya
milestone yang memberi sesuatu yang mustahil dicapai proses kertas.

---

## 11. Strategi pengujian

Mengikuti pola `tests/Feature/Ukm/*` yang sudah ada.

| Lapis | Cakupan |
|---|---|
| **Unit** | `ApproverResolver` — satu test per strategi, termasuk kasus kandidat kosong. `NomorSuratService` — keunikan di bawah konkurensi. |
| **Feature — alur** | Happy path 4 langkah; penolakan di tiap posisi menghentikan alur; pembatalan; langkah `jika_ada_ukm` dilewati saat tanpa UKM; rantai tak-ter-resolve ditolak saat submit |
| **Feature — otorisasi** | Approver A tidak bisa memutuskan langkah milik B (403); mahasiswa tidak bisa melihat izin mahasiswa lain (IDOR); langkah 3 tidak bisa diputuskan saat langkah 2 masih `menunggu` |
| **Feature — integrasi** | Scan gerbang saat izin aktif → `log_status='izin'`; **scan gerbang tanpa izin tetap berperilaku persis seperti sebelumnya (uji regresi wajib)**; presensi beririsan → `Izin`; kembali terlambat → `Pelanggaran` `submitted` |
| **Feature — publik** | Tautan tanpa tanda tangan valid → 403; token acak → 404; konfirmasi tiba dua kali → ditolak |
| **Konkurensi** | Dua approver menyetujui bersamaan → satu berhasil, satu 409 |
| **Skema** | Pola `UkmSchemaTest` — kolom, indeks, dan constraint sesuai desain |

Uji regresi gerbang adalah yang **paling penting**: `QRController` dipakai setiap hari oleh seluruh
kampus, dan itulah satu-satunya kode produksi berisiko tinggi yang disentuh epic ini.

---

## 12. Anti-pattern yang harus dihindari

| ❌ Jangan | ✅ Lakukan | Kenapa |
|---|---|---|
| Membuat `pengajuan_ijin_keluar` dan `pengajuan_ib` sebagai dua tabel | Satu tabel + `jenis_izin_id` | Ini persis kesalahan `presensi_apels/senams/upacaras` |
| `if ($izin->jenis === 'IB')` di controller | Baca properti dari `jenis_izins` | Setiap `if` seperti ini adalah kebocoran konfigurasi ke dalam kode |
| Kolom `ttd_dosen_pa_at`, `ttd_kaprodi_at`, … | Baris di `izin_approvals` | Kolom tidak bisa menyimpan catatan, penolakan, atau IP |
| `$izin->update(['status' => 'disetujui'])` di controller | Lewat `PengajuanIzinService` | Transisi yang tersebar akan menghasilkan status tidak konsisten |
| Menambah role global untuk tiap penandatangan | `pejabats` + resolver | `users.role_id` bernilai tunggal; jabatan bisa rangkap |
| Membuat tabel `presensi_izins` baru | Pakai enum `Izin` yang sudah ada | Enumnya sudah disediakan sejak awal — pakai |
| Menulis ulang `QRController::presense()` | Satu blok `if` di depan | Kode itu dipakai harian; risiko regresi tidak sepadan |
| Menyimpan gambar tanda tangan pejabat | Jejak audit + QR | Gambar justru lebih mudah dipalsukan (ADR-008) |
| Menulis HMAC sendiri untuk QR | `URL::signedRoute` | Penerbit & pemeriksa ada di aplikasi yang sama |
| Memakai `id` sebagai identitas publik surat | `qr_token` acak | Mencegah penelusuran surat orang lain dengan menebak angka |
| Menjatuhkan poin pelanggaran otomatis | Usulan berstatus `submitted` | Sistem yang menghukum tanpa mendengar akan dihindari pengguna |

---

## 13. Out of scope & yang perlu ditinjau ulang

**Sengaja tidak dikerjakan di Epic 03:**

- Migrasi Kegiatan Wajib ke mesin generik (masih milik ADR-006 Epic 01 yang ditunda)
- Notifikasi WhatsApp/email (tidak ada infrastruktur; **[A1]** akan menunjukkan apakah in-app cukup)
- Tanda tangan elektronik tersertifikasi BSrE (ADR-008 Opsi C)
- Aplikasi mobile khusus (Blade responsif sudah memadai)
- Persetujuan paralel/bercabang (`mode = 'all'` disiapkan di skema, tidak diimplementasikan)
- Izin retroaktif/susulan (tergantung hasil **[A7]**)
- Integrasi ke SIAKAD untuk "ijin meninggalkan kelas" (butuh kesepakatan lintas sistem)

**Yang perlu ditinjau ulang seiring waktu:**

1. Bila muncul kebutuhan langkah paralel/bercabang → `urutan` linear perlu berkembang jadi graf.
2. Bila Dosen PA ternyata per-mahasiswa, bukan per-kelas → tambah `users.dosen_pa_id` sebagai override.
3. Bila **[A1]** menunjukkan approver tidak responsif → butuh delegasi & eskalasi otomatis
   sebagai fitur kelas satu, bukan sekadar pengingat.
4. Bila surat harus sah secara hukum di luar kampus → ADR-008 Opsi C.
5. Bila volume tumbuh jauh melampaui **[A8]** → tinjau indeks & paginasi; arsitekturnya sendiri
   tidak perlu berubah.

---

## 14. Keputusan yang sudah ditetapkan (5 Agustus 2026)

Ketiga pertanyaan pembuka sudah dijawab. Berikut jawabannya dan konsekuensinya ke desain.

### 14.1 `jadwal_petugas` tidak membedakan piket dan pelatih — **dan memang tidak perlu**

Hasil pemeriksaan kode: `petugas1_id` dan `petugas2_id` adalah **dua slot tanpa makna peran**.
`DashboardAdminController` memperlakukan keduanya identik pada validasi (`piketPetugasGenerateJadwal`),
pembaruan (`updatePiketPetugas`), dan pembuatan massal (kedua kolom di-`null`-kan bersamaan). Model
`JadwalPetugas` pun hanya punya dua relasi anonim `petugas1()`/`petugas2()`.

**Keputusan:** *jangan* menambah kolom pembeda. "Petugas Piket Asrama" dan "Pelatih Harian Asrama"
diperlakukan sebagai satu konsep — **staf asrama yang bertugas pada tanggal keberangkatan** — dengan
resolver `petugas_jaga` mode `any` (kedua petugas jadi kandidat, cukup salah satu bertindak).
Perbedaan nama pada surat ditangani oleh `label` di konfigurasi langkah, bukan oleh kode.

Ini justru pembuktian ADR-006 bekerja: dua istilah berbeda di dua form berbeda diselesaikan **tanpa
satu pun percabangan kode dan tanpa satu pun `ALTER TABLE` pada tabel produksi.**

> Bila kelak asrama benar-benar ingin memisahkan keduanya, tambahkan `jadwal_petugas.peran1`/`peran2`
> dan isi `mode`/filter di resolver. Perubahannya terisolasi di satu strategi.

### 14.2 "Kepala Asrama" dan "Unit Kemahasiswaan" adalah **dua jabatan**, salah satu boleh memvalidasi

**Konsekuensi skema:** kolom `izin_workflow_steps.jabatan` berubah dari `string` menjadi **`json`
(array)**, dan langkah validasi akhir diisi `["kepala_asrama","unit_kemahasiswaan"]` dengan
`mode = 'any'`. `ApproverResolver` strategi `pejabat` mengembalikan gabungan pemegang **semua**
jabatan dalam array itu.

Manfaat sampingan yang penting: ini menghapus titik kegagalan tunggal pada langkah terakhir. Kalau
Kepala Asrama sedang di luar kota, izin tidak mandek — Unit Kemahasiswaan bisa memvalidasi. Bagi
mahasiswa, ini kemungkinan besar **perbaikan terbesar dibanding proses kertas**, karena langkah
terakhir adalah yang paling sering menahan surat semalaman.

### 14.3 Data Dosen PA **belum ada** — dan ini tidak boleh memblokir M1

Ini satu-satunya jawaban yang berdampak pada jadwal. Menunggu data Dosen PA seluruh kelas terkumpul
bisa memakan berminggu-minggu dan berada **di luar kendali developer**. Menjadikannya prasyarat
berarti menyandera seluruh epic pada pekerjaan administratif orang lain.

**Keputusan: jalankan tiga lapis, jangan menunggu.**

**Lapis 1 — Fallback berjenjang di level data (bukan kode).**
Langkah Dosen PA diberi `fallback_resolver = 'pejabat'`, `fallback_jabatan = ['kaprodi']`.
Perilakunya:

```
resolve langkah "Dosen PA" untuk mahasiswa X:
    kelas(X).dosen_pa_id terisi?  ──ya──> Dosen PA tersebut
                                  ──tidak──> Kaprodi prodi(X)   ← fallback otomatis
```

Kaprodi jauh lebih sedikit (satu per prodi, mungkin 4–6 orang total) dan datanya bisa dikumpulkan
dalam satu percakapan. Dengan ini modul **bisa dipakai sejak hari pertama** meski `kelas.dosen_pa_id`
seluruhnya kosong.

**Lapis 2 — Pengisian bertahap, tanpa deploy.**
Begitu satu prodi menyerahkan daftar Dosen PA-nya, admin mengisi kolomnya lewat UI/impor Excel.
Prodi itu **langsung** memakai Dosen PA yang benar; prodi lain tetap memakai fallback. Tidak ada
migrasi, tidak ada rilis, tidak ada koordinasi *big bang*. Peralihannya terjadi per-baris data.

**Lapis 3 — Impor Excel, bukan pengetikan manual.**
Reuse pola `UsersImport` + `maatwebsite/excel` yang sudah dipakai `sistemAdminImportClass`.
Template: `kode_kelas | nim_atau_email_dosen`. Prodi mengisi spreadsheet — format yang sudah mereka
pakai sehari-hari — dan admin mengunggahnya. Ini mengubah pengumpulan data dari proyek berminggu-minggu
menjadi pekerjaan satu jam per prodi.

**Konsekuensi ke roadmap:** M0 tidak lagi menjadi blocker jalur kritis. Isinya menyusut jadi
*"migrasi `pejabats` + `kelas.dosen_pa_id` + isi data Kaprodi & Kepala Asrama/Unit Kemahasiswaan"* —
sekitar 6–10 orang, bisa selesai dalam sehari. Pengumpulan Dosen PA berjalan **paralel** sepanjang
M1–M5 dan tidak menghalangi apa pun.

> **Prinsip yang berlaku umum:** ketika sebuah fitur bergantung pada data yang belum ada, jangan
> jadikan data itu prasyarat — **jadikan ketiadaannya sebagai satu kondisi yang ditangani sistem.**
> Alur data-driven ADR-006 membuat penanganan itu berupa dua kolom konfigurasi, bukan cabang `if`.

---

## 15. Best practice bila riset pengguna tidak bisa dilakukan

Wawancara 7 partisipan tidak memungkinkan. Itu situasi normal dalam proyek KKN/skripsi berjadwal
ketat — dan **bukan alasan untuk membangun berdasarkan tebakan.** Ganti "bertanya sebelum
membangun" dengan **"membangun dengan cara yang membuat kesalahan murah dan cepat ketahuan."**

### 15.1 Ambil data yang tidak butuh menjadwalkan siapa pun

| Sumber | Yang bisa didapat | Biaya |
|---|---|---|
| **Arsip surat izin 1–2 bulan terakhir** (minta ke Unit Kemahasiswaan, hitung sendiri) | Distribusi jenis izin, durasi rata-rata, hari & jam puncak, jeda tanggal ttd pertama → terakhir (= baseline metrik M1), berapa % blok "Telah tiba di..." benar-benar terisi (= baseline M6) | ~2 jam, tanpa wawancara |
| **Query database sendiri** | Berapa mahasiswa berstatus `telat` per bulan (= besaran masalah M3), sebaran `jadwal_petugas` yang kosong, kelengkapan `kelas`/`prodi` | ~30 menit |
| **Form kertas itu sendiri** | Sudah diperas habis di §1 dokumen riset | selesai |

Arsip surat adalah pengganti wawancara yang paling kuat: itu **rekaman perilaku nyata**, bukan
laporan diri. Dalam banyak hal ia lebih dipercaya daripada wawancara.

### 15.2 Ganti riset dengan pilot sempit

Alih-alih bertanya *"apakah approver mau memakai aplikasi?"* (asumsi A1, yang paling berisiko),
**jalankan ke satu prodi saja di M2** dan amati selama 2 minggu. Yang diamati:

- Median waktu langkah `dibuka_at → acted_at` per jenis approver
- Berapa langkah yang lewat SLA
- Berapa mahasiswa yang tetap datang membawa kertas

Angka-angka itu menjawab A1 **lebih meyakinkan daripada wawancara mana pun** — karena riset
menanyakan niat, pilot mengukur perbuatan. Syaratnya cuma satu: *instrumentasi harus terpasang sejak
M2*, bukan ditambahkan belakangan. Karena itu `dibuka_at` dan `acted_at` sudah masuk skema §7.2.

### 15.3 Jalankan berdampingan dengan kertas selama 1 bulan (dual-run)

Jangan matikan jalur kertas saat rilis. Selama masa dual-run, kertas menjadi **jaring pengaman
sekaligus alat ukur**: proporsi mahasiswa yang masih memilih kertas adalah indikator kegagalan
paling jujur yang bisa Anda dapatkan, dan gratis.

Matikan jalur kertas hanya setelah proporsinya turun sendiri — bukan berdasarkan tanggal di rencana.

### 15.4 Rancang agar kesalahan bisa dibatalkan

Karena keputusan diambil tanpa riset, kemungkinan salahnya lebih tinggi. Kompensasinya: pastikan
setiap keputusan berisiko **murah untuk dikoreksi**.

| Keputusan yang mungkin salah | Cara membatalkannya |
|---|---|
| Rantai persetujuan 4 langkah terlalu panjang | Ubah baris `izin_workflow_steps` — tanpa deploy (ADR-006) |
| SLA 24 jam terlalu ketat/longgar | Kolom `sla_jam`, ubah per langkah |
| Persetujuan massal ternyata dibutuhkan | Sudah diberi rambu di §8.6; bangun belakangan |
| Petugas piket ternyata harus dibedakan | Terisolasi di satu strategi resolver (§14.1) |
| Dosen PA ternyata per-mahasiswa | Tambah `users.dosen_pa_id` sebagai override, satu strategi berubah |
| Approver menolak memakai aplikasi | Fallback penunjukan manual (§8.2) + kertas masih hidup (§15.3) |

Perhatikan polanya: **hampir semua risiko sudah bermuara ke perubahan data atau satu class kecil.**
Itu bukan kebetulan — itulah alasan sebenarnya memilih arsitektur data-driven ketika Anda sedang
membangun tanpa kepastian.

### 15.5 Yang tetap wajib ditanyakan (5 menit, bukan wawancara)

Tiga pertanyaan ini tidak bisa diganti data, tapi cukup dijawab lewat pesan singkat ke Unit
Kemahasiswaan:

1. **Siapa pemegang jabatan Kaprodi (per prodi), Kepala Asrama, dan Unit Kemahasiswaan hari ini?**
   → Wajib untuk M0. Tanpa ini modul tidak jalan sama sekali.
2. **Kalau mahasiswa terlambat kembali, sanksinya apa hari ini?**
   → Menentukan `JenisPelanggaran` yang di-seed dan besaran poinnya (§7.10).
3. **Apakah surat izin ini pernah diminta pihak luar sebagai bukti resmi?**
   → Menentukan apakah ADR-008 Opsi C (TTE tersertifikasi) perlu dinaikkan prioritasnya.

Pertanyaan 1 memblokir M0. Pertanyaan 2 dan 3 memblokir M4 dan bisa ditanyakan sambil jalan.
