# Frontend Design — Epic 03: Modul Workflow Sistem Perizinan

**Proyek:** E-Management Polbangtan-mlg · **Companion:** `DESIGN-Epic03-Modul-Perizinan.md`, `RESEARCH-Epic03-Perizinan.md`
**Stack UI existing:** Blade + Tailwind v4 + Flowbite + Alpine.js + FullCalendar + html5-qrcode · **Ikon:** Remix Icon (`ri-*`)
**Prinsip:** *konsistensi > kreativitas.* Semua halaman baru menyalin pola view existing, bukan gaya baru.

---

## 0. Keputusan yang memengaruhi frontend

- **Tidak ada role baru.** Menu perizinan digate dengan `role:` yang sudah ada; kepemilikan langkah
  (`izin_approvals.approver_user_id`) yang menentukan isi inbox — **bukan** role. Konsekuensi: menu
  "Persetujuan Izin" muncul untuk semua staf, tapi isinya kosong bagi yang tidak punya langkah.
  → Tampilkan **badge jumlah** agar tidak ada yang membuka halaman kosong berulang kali.
- **Mobile-first untuk layar approver.** Riset (JTBD Dosen PA) menuntut keputusan dalam hitungan
  detik dari HP. Halaman review persetujuan adalah satu-satunya layar di aplikasi ini yang
  **wajib** dirancang untuk lebar 360 px terlebih dahulu, lalu diperlebar.
- **PDF meniru form kertas.** Bukan estetika — mitigasi adopsi [A5]. Tata letak `generate-izin`
  mengikuti `IJIN_KELUAR.docx` sedekat mungkin.
- **Halaman publik (verifikasi & konfirmasi RT) tidak memakai `layouts.main`.** Pengguna tidak login
  dan tidak boleh melihat navigasi aplikasi. Buat layout minimal tersendiri.

---

## 1. Pola design system yang wajib diikuti

Kerangka setiap view baru, sama seperti `admin/jadwal-kegiatan.blade.php`:

```blade
@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Judul Halaman</h1>
        <div class="text-gray-600 text-sm">Subjudul singkat</div>
        <div class="border-b border-gray-300 my-5"></div>

        @include('partials.alert')

        {{-- konten: kartu bg-white border-2 rounded-lg p-3 --}}
    </div>
@endsection
```

| Elemen | Kelas / sumber existing |
|---|---|
| Kartu/panel | `bg-white border-2 rounded-lg p-3` |
| Warna utama | `bg-utama` (teal) |
| Alert | `partials/alert` |
| Form "tambah" | Flowbite accordion (`data-accordion="collapse"`) |
| Tabel + filter live | `admin/partials/*_table.blade.php` di-refresh via AJAX (JSON `{table: ...}`) |
| Modal konfirmasi | `partials/modals/*` |
| Kalender | FullCalendar (lihat `piket-petugas`) |
| Pagination | `{{ $paginator->links() }}` |
| PDF | `admin/generate/*` + dompdf |

**Badge status izin** — pakai utilitas Tailwind yang sudah muncul di view lain, jangan bikin token warna baru:

| Status | Kelas |
|---|---|
| `draft` | `bg-gray-200 text-gray-800` |
| `diajukan` | `bg-yellow-200 text-yellow-800` |
| `disetujui` | `bg-green-200 text-green-800` |
| `berjalan` | `bg-blue-200 text-blue-800` |
| `selesai` | `bg-green-100 text-green-700` |
| `ditolak` / `terlambat` | `bg-red-200 text-red-800` |
| `dibatalkan` / `kadaluarsa` | `bg-gray-300 text-gray-700` |

---

## 2. Inventaris view

Kolom **Reuse dari** = view lama yang di-clone sebagai titik awal.

| # | View baru | Untuk | US | Reuse dari |
|---|---|---|---|---|
| **Mahasiswa** |
| 1 | `izin/index.blade.php` | Daftar pengajuan saya + tombol "Ajukan Izin" | 3.1 | `riwayat-pelanggaran.blade.php` |
| 2 | `izin/create.blade.php` | Form pengajuan (pilih jenis → field dinamis) | 3.1 | `formhukum.blade.php` |
| 3 | `izin/show.blade.php` | Detail + **progress tracker 4 langkah** | 3.2 | `detail-pelanggaran.blade.php` |
| 4 | `izin/partials/tracker.blade.php` | Komponen stepper vertikal (dipakai ulang di §5) | 3.2 | — (baru, kecil) |
| **Staf — Persetujuan** |
| 5 | `admin/izin/inbox.blade.php` | Langkah `menunggu` milik saya + badge SLA | 3.3 | `admin/laporan-pelanggaran.blade.php` |
| 6 | `admin/izin/review.blade.php` | **Layar keputusan** — setujui/tolak + catatan | 3.3 | `admin/data-pelanggaran.blade.php` |
| 7 | `admin/izin/partials/inbox_table.blade.php` | Baris inbox (AJAX filter) | 3.3 | `admin/partials/data_kegiatan_apel_table.blade.php` |
| **Staf — Data & monitor** |
| 8 | `admin/izin/data.blade.php` | Semua pengajuan + filter periode/status/prodi/blok | 3.4, 3.8 | `admin/data-absenkeluar.blade.php` |
| 9 | `admin/izin/detail.blade.php` | Detail 1 pengajuan + riwayat audit lengkap | 3.4 | `admin/detail-absenkeluar.blade.php` |
| 10 | `admin/izin/monitor.blade.php` | **"Siapa di luar sekarang"** — 3 kartu ringkas + tabel | 3.5 | `admin/index.blade.php` (kartu statistik) |
| **Admin — Konfigurasi** |
| 11 | `admin/izin/jenis/index.blade.php` | CRUD jenis izin | 3.9 | `admin/edit-pelanggaran.blade.php` |
| 12 | `admin/izin/jenis/langkah.blade.php` | Susun rantai penandatangan + **pratinjau alur** | 3.9 | `admin/jadwal-kegiatan.blade.php` |
| 13 | `admin/pejabat/index.blade.php` | Data pejabat (jabatan → orang → lingkup) | M0 | `admin/data-petugas.blade.php` |
| **Cetak & publik** |
| 14 | `admin/generate/generate-izin.blade.php` | **Template PDF surat izin** (kop + QR + daftar ttd) | 3.10 | `admin/generate/generate-pelanggaran.blade.php` |
| 15 | `admin/generate/generate-laporan-izin.blade.php` | PDF rekap periodik | 3.8 | `admin/generate/generate-apel.blade.php` |
| 16 | `layouts/publik.blade.php` | Layout minimal tanpa nav, untuk halaman publik | 3.7, 3.10 | — (baru, kecil) |
| 17 | `publik/verifikasi-izin.blade.php` | Halaman keabsahan hasil pindai QR | 3.10 | — |
| 18 | `publik/konfirmasi-tiba.blade.php` | Form RT/panitia (3 field) | 3.7 | — |

**18 view**, 4 di antaranya partial/layout kecil. Satu halaman `review` melayani **semua** jenis izin
dan **semua** langkah — bandingkan Kegiatan Wajib yang butuh 3 view kamera untuk 3 jenis kegiatan.

---

## 3. Matriks akses menu

| Menu | Ditampilkan untuk | Isi ditentukan oleh |
|---|---|---|
| Mahasiswa → **Izin Saya** | `role:user` | `pengajuan_izins.user_id = auth()->id()` |
| Staf → **Persetujuan Izin** `(n)` | `role:admin,operator,pelatih,pembina` | `izin_approvals.approver_user_id = auth()->id()` AND `status='menunggu'` |
| Staf → **Data Izin** | `role:admin,operator,pelatih,pembina` | Admin: semua. Non-admin: hanya pengajuan yang pernah/sedang melewati langkah miliknya |
| Staf → **Monitor Asrama** | `role:admin,operator,pelatih` | Semua izin `berjalan` |
| Admin → **Jenis Izin**, **Data Pejabat** | `role:admin` | — |

> **Peringatan IDOR.** Keperluan izin bersifat pribadi ("mengurus surat rumah sakit ibu"). Terapkan
> pola scoping yang sama seperti `UkmVerifikasiController::index()`: `abort_unless` di controller,
> **bukan** sekadar menyembunyikan tombol. Halaman detail wajib diuji dengan mengakses id milik
> orang lain (lihat rencana tes §11 dokumen utama).

Penambahan di `partials/nav.blade.php` mengikuti struktur yang ada — sisipkan setelah blok
"Pelanggaran" agar pengelompokan tetap logis (kehadiran → pelanggaran → perizinan).

---

## 4. Tiga layar yang menentukan keberhasilan

### 4.1 Form pengajuan (`izin/create`) — target < 60 detik

Riset menunjukkan sistem hanya perlu menanyakan **4 hal**; sisanya sudah diketahui aplikasi.

```
┌───────────────────────────────────────────────┐
│  Ajukan Izin                                  │
├───────────────────────────────────────────────┤
│  Data Anda                       [terkunci]   │
│  Naufal · 2A / TRPL · NIRM 0221 · Kamar B-12  │  ← read-only, dari sistem
│                                               │
│  Jenis izin       [ Ijin Keluar Asrama    ▾ ] │  ← memicu field dinamis
│  UKM/ORMAWA       [ pilih UKM saya        ▾ ] │  ← muncul jika butuh_ukm
│  Keperluan        [                        ]  │
│  Tujuan/Lokasi    [                        ]  │
│  Berangkat        [ tgl ] [ jam ]             │
│  Kembali          [ tgl ] [ jam ]             │
│                                               │
│  ╭─ Alur persetujuan untuk izin ini ────────╮ │  ← PRATINJAU, sebelum submit
│  │ 1 Pembina UKM Silat — Pak Budi           │ │
│  │ 2 Dosen PA 2A — Bu Rina                  │ │
│  │ 3 Petugas Piket (ditentukan 12 Agu)      │ │
│  │ 4 Kepala Asrama — Pak Yongki             │ │
│  ╰──────────────────────────────────────────╯ │
│                            [ Ajukan Izin ]    │
└───────────────────────────────────────────────┘
```

**Pratinjau alur adalah elemen terpenting di layar ini.** Tiga alasan:

1. Menjawab nyeri P2 (*"surat saya di meja siapa?"*) **sebelum** submit, bukan sesudah.
2. Memenuhi syarat "gagal lebih awal" (ADR-007): bila Dosen PA belum terdata, mahasiswa melihatnya
   di sini — bukan setelah menunggu tiga hari.
3. Membuat mesin data-driven **terlihat**. Mahasiswa mengerti kenapa jenis izin berbeda punya
   penandatangan berbeda, tanpa perlu dijelaskan.

Implementasi: Alpine.js + endpoint `GET /dashboard/izin/pratinjau-alur?jenis_izin_id=&ukm_id=`
yang mengembalikan hasil `ApproverResolver` sebagai JSON. Tanpa halaman baru, tanpa reload.

Field dinamis (`butuh_ukm`, `butuh_bermalam`) dikendalikan `x-show` berdasarkan properti jenis izin
yang di-*embed* sebagai JSON di halaman — **bukan** `@if ($jenis === 'IB')` di Blade. Percabangan
di template adalah bentuk lain dari hardcoding yang dilarang ADR-006.

### 4.2 Layar keputusan approver (`admin/izin/review`) — target < 15 detik, di HP

Aturan mutlak: **semua yang dibutuhkan untuk memutuskan muat dalam satu layar 360×640 tanpa scroll.**
Kalau approver harus mengklik untuk melihat konteks, desainnya gagal memenuhi JTBD-nya.

```
┌──────────────────────────────┐  360px
│ ← Persetujuan Izin      3/12 │
├──────────────────────────────┤
│ Naufal Mathara               │
│ 2A / TRPL · NIRM 0221        │
│                              │
│ Ijin Keluar Asrama           │
│ Lomba LKTI Universitas X     │
│ 📍 Surabaya                  │
│ 🕐 12 Agu 06:00 → 12 Agu 21:00│
│    (15 jam)                  │
│                              │
│ ⚠ 1× terlambat kembali (Jul) │ ← sinyal keputusan, bukan sekadar data
│ ✓ Disetujui: Pembina UKM     │
│   Pak Budi · 10 Agu 14:20    │
│                              │
│ Catatan (wajib bila menolak) │
│ [                          ] │
│                              │
│ [   Setujui   ] [   Tolak  ] │ ← tombol besar, jempol-friendly
└──────────────────────────────┘
```

Keputusan desain yang perlu disadari:

- **Baris "1× terlambat kembali"** berasal dari pertanyaan wawancara *"apa yang Bapak/Ibu periksa
  sebelum tanda tangan?"* (§3.4 riset). **Jangan bangun sebelum jawabannya diketahui** — kalau
  ternyata approver tidak memeriksa itu, baris ini hanya menambah kebisingan. Ini contoh field yang
  desainnya **menunggu data riset**, bukan menebak.
- **Tombol Setujui dan Tolak berdampingan, ukuran sama.** Membuat "Tolak" lebih kecil atau
  tersembunyi mendorong persetujuan refleks — persis yang merusak nilai kontrol proses ini.
- **Counter "3/12"** memberi rasa kemajuan; menurut riset ini yang membuat approver menyelesaikan
  antreannya sekaligus alih-alih satu-dua lalu berhenti.
- **Tolak wajib beralasan** — ditegakkan di server, bukan hanya `required` di HTML.

### 4.3 Monitor asrama (`admin/izin/monitor`) — layar untuk Kepala Asrama

Menjawab P8 dan JTBD Kepala Asrama. Tiga kartu di atas, satu tabel di bawah:

```
┌──────────────┐ ┌──────────────┐ ┌──────────────┐
│ Di luar      │ │ Jatuh tempo  │ │ Lewat waktu  │
│      23      │ │ hari ini  8  │ │      2   ⚠   │
└──────────────┘ └──────────────┘ └──────────────┘

Nama          Kelas  Tujuan     Kembali        Status
Naufal M.     2A     Surabaya   12 Agu 21:00   Di luar
Rina S.       1B     Blitar     12 Agu 18:00   ⚠ Lewat 3 jam
```

Kartu "Lewat waktu" adalah satu-satunya elemen di seluruh aplikasi yang boleh memakai warna merah
mencolok — ini informasi yang, ketika muncul, memang harus segera ditindaklanjuti.

Auto-refresh 60 detik via `fetch` sederhana; jangan tambahkan websocket/Livewire hanya untuk ini.

---

## 5. Progress tracker (`izin/partials/tracker`)

Komponen stepper vertikal, dipakai di `izin/show` (mahasiswa), `admin/izin/detail` (staf), dan
pratinjau di `izin/create`. Satu komponen, tiga tempat — jangan bikin tiga versi.

```
● Menyetujui, Pembina ORMAWA/UKM        ✓ disetujui
│ Pak Budi · 10 Agu 14:20
│
● Mengetahui, Dosen PA                  ⏳ menunggu 6 jam
│ Bu Rina
│
○ Mengetahui, Petugas Piket Asrama      – belum terbuka
│ (ditentukan pada 12 Agu)
│
○ Memvalidasi, Kepala Asrama            – belum terbuka
  Pak Yongki
```

- Label diambil dari `label_snapshot`, **bukan** dari `izin_workflow_steps` — supaya surat lama tetap
  menampilkan kalimat yang benar meski admin mengubah konfigurasi (§8.5 dokumen utama).
- Langkah `dilewati` ditampilkan abu-abu dengan alasannya — jangan disembunyikan; transparansi
  langkah yang dilewati justru yang membuat sistem dipercaya.
- Waktu tunggu (`menunggu 6 jam`) dihitung dari `dibuka_at`, bukan dari `created_at` pengajuan.

---

## 6. Template PDF surat izin (`admin/generate/generate-izin`)

Meniru `IJIN_KELUAR.docx` sedekat mungkin — **ini keputusan manajemen perubahan, bukan estetika.**

```
┌─────────────────────────────────────────────────┐
│  [logo]  KEMENTERIAN PERTANIAN                  │  ← kop, salin dari header docx
│          POLITEKNIK PEMBANGUNAN PERTANIAN       │
│  ─────────────────────────────────────────────  │
│           SURAT IJIN KELUAR ASRAMA              │
│           Nomor: AR.009/0042/VIII/2026          │  ← tambahan digital
│                                                 │
│  NAMA LENGKAP        : Naufal Mathara R         │  ← dari *_snapshot
│  KELAS / PRODI       : 2A / TRPL                │
│  NIRM                : 0221...                  │
│  NOMOR TELEPON / HP  : 0812...                  │
│  *ANGGOTA UKM/ORMAWA : UKM Silat                │
│  KEPERLUAN/KEGIATAN  : Lomba LKTI               │
│  TUJUAN/LOKASI       : Surabaya                 │
│  WAKTU BERANGKAT     : 06:00                    │
│  ...                                            │
│                                                 │
│  1. PERSETUJUAN KEGIATAN & AKADEMIK             │  ← blok_snapshot
│     ✓ Menyetujui, Pembina ORMAWA/UKM            │
│       Pak Budi — 10 Agu 2026 14:20              │  ← ganti ttd basah
│     ✓ Mengetahui, Dosen PA                      │
│       Bu Rina — 10 Agu 2026 16:05               │
│                                                 │
│  2. VALIDASI ASRAMA                             │
│     ✓ Mengetahui, Petugas Piket Asrama          │
│     ✓ Memvalidasi, Kepala Asrama                │
│                                                 │
│  ┌────────┐  Pindai untuk memverifikasi         │
│  │  QR    │  keabsahan surat ini                │
│  └────────┘  polbangtan.../verifikasi-izin/...  │
│                                                 │
│  Telah tiba di ................................ │  ← tetap disediakan sebagai
│  Pada tanggal .................................. │     cadangan manual bila RT
│  ______________________  *RT Setempat           │     tidak bisa memakai QR
└─────────────────────────────────────────────────┘
```

Catatan implementasi:

- Ekstrak kop surat dari `word/media/` di dalam `.docx` (sudah tersedia: `image1.jpg`, dst.) dan
  simpan ke `public/img/kop-surat.png`. Jangan menggambar ulang.
- Blok "Telah tiba di ..." **tetap dicetak dalam bentuk kosong** meski konfirmasi digital tersedia.
  Ini mitigasi [A5]: bila RT di lokasi tujuan tidak bisa/mau memakai QR, jalur kertas masih ada.
  Begitu konfirmasi digital masuk, blok ini dicetak terisi.
- dompdf tidak mendukung sebagian CSS modern — gunakan tabel dan CSS inline sederhana, sama seperti
  template `admin/generate/*` yang sudah ada.

---

## 7. Halaman publik (tanpa login)

### 7.1 Verifikasi (`publik/verifikasi-izin`)

Dibuka dari QR oleh satpam/panitia. Harus terbaca **dalam 2 detik, sambil berdiri, di luar ruangan.**

```
┌─────────────────────────┐
│      ✓ BERLAKU          │  ← besar, hijau; atau merah "TIDAK BERLAKU"
│                         │
│  AR.009/0042/VIII/2026  │
│  Naufal Mathara R       │
│  2A / TRPL              │
│  Surabaya               │
│  s.d. 12 Agu 2026 21:00 │
│                         │
│  Politeknik Pembangunan │
│  Pertanian Malang       │
└─────────────────────────┘
```

Tampilkan seminimal mungkin. Halaman ini bisa dilihat siapa saja yang memegang surat cetak —
**jangan menampilkan keperluan izin** (bisa bersifat pribadi), nomor HP, atau nomor kamar.

### 7.2 Konfirmasi tiba (`publik/konfirmasi-tiba`)

Untuk RT/panitia. JTBD-nya: *selesai dalam 10 detik, tanpa akun, tanpa instalasi.*

```
┌─────────────────────────────┐
│ Konfirmasi Kedatangan       │
│                             │
│ Naufal Mathara R            │
│ Politeknik Pembangunan      │
│ Pertanian Malang            │
│ Tujuan: Surabaya            │
│                             │
│ Nama Anda      [          ] │
│ Sebagai        [ RT      ▾] │
│ No. HP (ops.)  [          ] │
│ Foto (ops.)    [ ambil    ] │
│                             │
│ [    Konfirmasi Tiba    ]   │
└─────────────────────────────┘
```

- 3 field, dua di antaranya opsional. Setiap field tambahan menurunkan tingkat penyelesaian (M6).
- Tautan bertanda tangan berlaku sampai `waktu_kembali` + 24 jam. Sekali dikonfirmasi, halaman
  berubah menjadi tanda terima — kunjungan kedua tidak boleh menimpa data.
- Tidak ada CAPTCHA, tidak ada verifikasi OTP. Tanda tangan pada URL sudah cukup; menambah gesekan
  di sini akan mengorbankan M6 demi risiko yang kecil.

---

## 8. Aksesibilitas & detail yang mudah terlewat

- **Status tidak boleh hanya dibedakan warna.** Setiap badge memuat teks status. Sebagian pengguna
  mengalami buta warna, dan sebagian besar surat dicetak hitam-putih.
- **Target sentuh ≥ 44 px** pada tombol Setujui/Tolak — approver menggunakan HP, kadang sambil berdiri.
- **Zona waktu.** Simpan sebagai `Asia/Jakarta`, tampilkan konsisten, dan **selalu cantumkan tanggal
  bersama jam** pada surat & tracker (`12 Agu 21:00`, bukan `21:00`) — izin bermalam melintasi hari,
  dan "kembali jam 6" tanpa tanggal adalah sumber sengketa.
- **Keadaan kosong.** Inbox kosong → *"Tidak ada izin menunggu persetujuan Anda"*, bukan tabel kosong.
- **Status pengiriman.** Tombol submit dikunci saat proses berjalan; pengajuan ganda karena ketukan
  dobel di sinyal lemah adalah kejadian yang harus diantisipasi, bukan dianggap tidak mungkin.
- **Cetak.** `izin/show` harus punya `@media print` yang wajar, karena sebagian mahasiswa akan
  menekan Ctrl+P di halaman detail alih-alih mengunduh PDF resmi.

---

## 9. Urutan pengerjaan frontend

Selaras dengan milestone di dokumen utama.

| M | View |
|---|---|
| M0 | 13 (Data Pejabat) |
| M2 | 1, 2, 3, 4, 5, 6, 7 — **alur end-to-end pertama kali terlihat** |
| M3 | 14 (PDF), 16, 17 (verifikasi publik) |
| M4 | tidak ada view baru — integrasi gerbang tak terlihat di UI, tapi paling bernilai |
| M5 | 10 (monitor), 18 (konfirmasi tiba), badge notifikasi di nav |
| M6 | 8, 9, 11, 12, 15 |

**Bangun #6 (layar keputusan approver) sedini mungkin dan ujikan ke satu Dosen PA sungguhan sebelum
melanjutkan.** Layar itu memikul asumsi paling berisiko dalam seluruh epic ([A1]: apakah approver
mau memakai aplikasi). Semua view lain hanya berguna kalau jawabannya "ya".
