# Frontend Design — Epic 01: Modul UKM Dinamis

**Proyek:** E-Management Polbangtan-mlg · **Companion:** `DESIGN-Epic01-Modul-UKM-Dinamis.md`
**Stack UI existing:** Blade + Tailwind v4 + Flowbite 4 + Alpine.js + FullCalendar + html5-qrcode · **Ikon:** Remix Icon (`ri-*`)
**Prinsip:** *konsistensi > kreativitas.* Semua halaman baru menyalin pola view yang sudah ada, bukan gaya baru.

---

## 0. Keputusan yang memengaruhi frontend

- **Role `pembina` (5) ditambahkan.** Menu & halaman verifikasi di-gate `role:admin,pembina`.
- **Pelatih & Pembina = staf.** Menu "Kelola UKM/Anggota/Jadwal/Scan" hanya untuk staf; mahasiswa hanya lihat "UKM Saya" + riwayat. Ini menyederhanakan percabangan menu di `nav.blade.php`.

---

## 1. Pola design system yang WAJIB diikuti (hasil audit view existing)

Setiap view baru harus memakai kerangka yang sama seperti `admin/jadwal-kegiatan.blade.php`:

```blade
@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Judul Halaman</h1>
        <div class="text-gray-600 text-sm">Subjudul singkat</div>
        <div class="border-b border-gray-300 my-5"></div>

        @include('partials.alert')   {{-- lihat §5: ekstrak alert jadi partial --}}

        {{-- konten: kartu bg-white border-2 rounded-lg p-3 --}}
    </div>
@endsection
```

Komponen & token yang sudah ada dan dipakai ulang:

| Elemen | Kelas / sumber existing |
|---|---|
| Kartu/panel | `bg-white border-2 rounded-lg p-3` |
| Warna utama | `bg-utama` (teal) + `div-shadow-kodeqr` untuk panel scanner |
| Alert sukses/gagal | blok Flowbite hijau/merah dismissible (kini di `partials/alert`) |
| Form "tambah" | Flowbite **accordion** (`data-accordion="collapse"`) — lihat `jadwal-kegiatan` |
| Tabel + filter live | partial di `admin/partials/*_table.blade.php` di-refresh via AJAX (JSON `{table: ...}`) |
| Modal hapus/konfirmasi | `partials/modals/hapus.blade.php` dsb. |
| Kalender jadwal | FullCalendar (lihat `piket-petugas`) |
| Scanner | `#reader` + `js/library/html5-qrcode.min.js` + form hidden (`user_id,date,time,scanner`) |
| Pagination | `{{ $paginator->links() }}` (Laravel default, sudah dipakai) |

> Jangan buat warna/spacing/komponen baru. Kalau butuh badge status, pakai util Tailwind yang sudah muncul di view lain (mis. `bg-green-200 text-green-800`).

---

## 2. Inventaris View (jawaban langsung: ini bagian frontend yang kemarin belum ada)

Kolom **Reuse dari** = view lama yang di-clone sebagai titik awal.

| # | View baru | Untuk | US | Reuse dari |
|---|---|---|---|---|
| **Admin — Kelola UKM** |
| 1 | `admin/ukm/index.blade.php` | Daftar UKM + tombol "UKM Baru" (accordion form) + toggle aktif | 1.1 | `admin/jadwal-kegiatan.blade.php` |
| 2 | `admin/ukm/partials/ukm_table.blade.php` | Baris tabel UKM (AJAX search/filter) | 1.1 | `admin/partials/jadwal_kegiatan_table.blade.php` |
| 3 | `admin/ukm/show.blade.php` | Detail 1 UKM: tab **Anggota / Jadwal / Rekap** | 1.0–1.4 | `admin/detail-absenkegiatan.blade.php` |
| **Admin/Pelatih — Anggota** |
| 4 | `admin/ukm/partials/anggota_table.blade.php` | Tabel anggota + tombol tambah/keluarkan | 1.0 | `admin/partials/data_kegiatan_table.blade.php` |
| 5 | `partials/modals/ukm-tambah-anggota.blade.php` | Modal pilih mahasiswa (search NIM/nama) | 1.0 | `partials/modals/*` + pola search `dataKegiatanWajibSearch` |
| **Pelatih — Jadwal** |
| 6 | `admin/ukm/jadwal.blade.php` | Buat jadwal (accordion) + daftar/kalender jadwal UKM | 1.2 | `admin/jadwal-kegiatan.blade.php` + FullCalendar `piket-petugas` |
| 7 | `admin/ukm/partials/jadwal_table.blade.php` | Baris jadwal + status verifikasi + aksi | 1.2 | `admin/partials/jadwal_kegiatan_table.blade.php` |
| **Pelatih — Scanner (yang penting)** |
| 8 | `admin/ukm/kamera-ukm.blade.php` | **SATU** halaman scan generik, berparameter `ukm_jadwal_id` | 1.3 | `admin/kamera-apel.blade.php` |
| **Pembina — Verifikasi** |
| 9 | `admin/ukm/verifikasi.blade.php` | Daftar jadwal `menunggu` + Approve/Reject + catatan | 1.4 | `admin/data-pelanggaran.blade.php` (pola approve/reject) |
| **Mahasiswa** |
| 10 | `ukm/index.blade.php` | "UKM Saya": kartu UKM yang diikuti + jadwal terdekat | 1.3 | `index.blade.php` (dashboard mhs) |
| 11 | `ukm/riwayat.blade.php` | Riwayat kehadiran UKM (Hadir/Izin/Alpha) | 1.3 | `riwayat-kegiatan.blade.php` |
| **Laporan** |
| 12 | `admin/generate/generate-ukm.blade.php` | Template PDF laporan kegiatan UKM (generik, ber-`ukm_id`) | 1.4 | `admin/generate/generate-apel.blade.php` |

**12 view** (5 di antaranya partial kecil). Bandingkan modul lama: 3 view kamera + 3 template laporan hanya untuk 3 jenis kegiatan — modul baru pakai **1 kamera + 1 laporan** untuk UKM tak-terbatas.

---

## 3. Spec per-halaman (ringkas, cukup untuk langsung dibangun)

### 3.1 `admin/ukm/index.blade.php` — Daftar & Buat UKM (US 1.1)
- Header standar "Manajemen UKM".
- **Accordion "Tambah UKM"** (pola `jadwal-kegiatan`): field `nama` (required, unique), `deskripsi` (textarea), opsional pilih pelatih (dropdown staf `role_id=4`). Submit → `admin.ukm.store`.
  - *JTBD Admin terpenuhi:* create = 1 field wajib, 1 klik. Bukan wizard.
- **Tabel UKM**: kolom Nama · Jumlah Anggota · Pelatih · Status (badge aktif/nonaktif) · Aksi (Detail, Toggle aktif, Hapus via modal).
- Search live (AJAX) → refresh `ukm_table` partial (pola JSON `{table}` dari `dataKegiatanWajibSearch`).

### 3.2 `admin/ukm/show.blade.php` — Detail UKM (hub)
Tiga tab Flowbite (`data-tabs-toggle`):
1. **Anggota** → embed view #4 + tombol "Tambah Anggota" (modal #5).
2. **Jadwal** → embed view #6.
3. **Rekap** → ringkasan Hadir/Izin/Alpha per anggota (pola `rekapKegiatan` di `dataKegiatanWajibDetail`).

### 3.3 `admin/ukm/jadwal.blade.php` — Jadwal Pelatih (US 1.2)
- **Accordion "Buat Jadwal"**: `judul`, `jenis` (radio: Latihan / Kegiatan Wajib), `tanggal`, `mulai_acara`, `selesai_acara`, `lokasi`. Submit → `admin.ukm.jadwal.store` → fan-out presensi anggota.
- **Dua mode lihat**: daftar tabel (#7) + toggle **kalender** FullCalendar (event = jadwal UKM). Warna event beda per `jenis`.
- Tiap baris jadwal punya badge `status_verifikasi` + tombol "Buka Scanner" (→ #8) + "Ajukan Verifikasi" (set `menunggu`).

### 3.4 `admin/ukm/kamera-ukm.blade.php` — Scanner Generik (US 1.3) ⭐
Ini penyederhanaan terbesar. Clone `kamera-apel.blade.php`, lalu:
- Judul panel dinamis: `Scan UKM — {{ $jadwal->ukm->nama }} · {{ $jadwal->judul }}` (bukan hardcoded "Kamera Apel").
- Form action → `admin.ukm.scan.store` dengan `{{ $jadwal->id }}` di URL. Field hidden **tetap sama** (`user_id,date,time,scanner`) karena QR mahasiswa tidak berubah.
- **JS di-reuse apa adanya**: `html5-qrcode.min.js` + `scancamera-kegiatan.js`. Hanya endpoint form yang beda → idealnya JS baca `action` dari elemen `<form>` (bukan hardcode URL) supaya satu file JS melayani semua jadwal. Jika `scancamera-kegiatan.js` meng-hardcode route, buat `scancamera-ukm.js` tipis yang override endpoint.

```blade
{{-- inti perbedaan dari kamera-apel: --}}
<div class="text-lg font-semibold">Scan: {{ $jadwal->ukm->nama }}</div>
<p class="text-white text-sm">{{ $jadwal->judul }} · {{ $jadwal->tanggal }}</p>
...
<form action="{{ route('admin.ukm.scan.store', $jadwal->id) }}" method="post" id="form">
    @csrf
    <input type="hidden" name="user_id" id="user_id">
    <input type="hidden" name="date"    id="date">
    <input type="hidden" name="time"    id="time">
    <input type="hidden" name="scanner" id="scanner">
</form>
```
> Hasil: **satu** halaman kamera menggantikan `kamera-apel` + `kamera-upacara` + `kamera-senam`, dan otomatis melayani UKM apa pun tanpa view baru.

### 3.5 `admin/ukm/verifikasi.blade.php` — Pembina (US 1.4)
- Gate `role:admin,pembina`, dan scope ke UKM tempat user ber-`peran='pembina'`.
- Tabel jadwal berstatus `menunggu`: UKM · Judul · Tanggal · Pelatih · Rekap hadir · Aksi **Approve/Reject** (+ textarea `catatan_pembina`). Pola tombol approve/reject meniru alur `Pelanggaran` (pending→confirmed/rejected).

### 3.6 `ukm/index.blade.php` & `ukm/riwayat.blade.php` — Mahasiswa
- "UKM Saya": kartu tiap UKM yang diikuti + jadwal terdekat + tombol "Tampilkan QR" (mengarah ke `kodeqr` yang **sudah ada** — tidak ada QR baru).
- Riwayat: tabel Hadir/Izin/Alpha + badge status_acara (Upcoming/Berlangsung/Selesai), meniru `riwayat-kegiatan.blade.php`.

---

## 4. Perubahan Navigasi (`partials/nav.blade.php`)

Tambah grup menu collapsible baru, **di-gate per role** (pola `@if` role sudah dipakai di nav):

```blade
{{-- STAF: admin/pelatih/pembina --}}
@if(in_array(auth()->user()->role_id, [1,4,5]))
  <li class="group"> {{-- Grup "UKM" --}}
    <button data-collapse-toggle="ukmMenu" ...><i class="ri-team-line ..."></i>
      <span class="...">UKM</span> ...</button>
    <ul id="ukmMenu" class="{{ Request::is('ukm*') ? 'block':'hidden' }} ...">
      @if(auth()->user()->role_id === 1)
        <li><a href="{{ route('admin.ukm.index') }}" ...>• Kelola UKM</a></li>
      @endif
      @if(in_array(auth()->user()->role_id,[1,4]))
        <li><a href="..." ...>• Jadwal & Scan</a></li>
      @endif
      @if(in_array(auth()->user()->role_id,[1,5]))
        <li><a href="{{ route('admin.ukm.verifikasi',...) }}" ...>• Verifikasi</a></li>
      @endif
    </ul>
  </li>
@endif

{{-- MAHASISWA (role 3): tambahkan submenu "UKM Saya" di grup yang sudah ada --}}
@if(auth()->user()->role_id === 3)
  <li><a href="{{ route('home.ukmSaya') }}" ...>• UKM Saya</a></li>
@endif
```

Active-state pakai `Request::is('ukm*')` seperti menu lain. Ikon Remix (`ri-team-line`) selaras dengan set ikon existing.

---

## 5. Refactor kecil yang sekalian dibereskan (best practice, opsional tapi disarankan)

Selama audit, blok **alert sukses/gagal ±25 baris disalin identik** di banyak view (jadwal-kegiatan, kamera-apel, dst.). Karena kita menambah 12 view baru, ekstrak sekali:

```bash
# resources/views/partials/alert.blade.php  (isi: blok @if success / @if error yang sudah ada)
```
Lalu semua view baru cukup `@include('partials.alert')`. Ini mencegah menyebarkan duplikasi yang sama ke modul baru — sejalan dengan semangat "hindari pola copy-paste" di dokumen arsitektur.

---

## 6. Ringkasan Frontend vs Modul Lama

| Aspek UI | Kegiatan Wajib (lama) | UKM Dinamis (baru) |
|---|---|---|
| Halaman kamera scan | 3 (apel/upacara/senam) | **1** generik berparameter |
| Template laporan | 3 | **1** ber-`ukm_id` |
| Menambah unit baru | butuh view/route baru | **0 view baru** (murni data) |
| Menu | statis | di-gate per role (admin/pelatih/pembina/mahasiswa) |
| QR mahasiswa | — | dipakai ulang, **tanpa perubahan** |
| Alert | disalin per view | 1 partial `@include` |

---

## 7. Kesimpulan

Ya — dengan dokumen ini, desain Epic 01 kini **mencakup frontend**: inventaris 12 view, spec tiap halaman, adaptasi scanner generik, perubahan nav ber-gate role, dan reuse komponen dari view yang sudah ada. Semuanya menempel pada design system existing (Blade/Flowbite/Tailwind v4/Alpine), jadi tidak ada gaya baru yang perlu dipelihara.

Frontend ini dibangun paralel dengan milestone backend (dokumen arsitektur §6): view #1–5 di Milestone B, #6–7 di C, #8 di D, #9 & #12 di E, #10–11 & nav di F.
