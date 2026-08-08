# Panduan & Penjelasan Field Data Pejabat (Modul Perizinan Asrama)

Dokumen ini menjelaskan fungsi, konsep, dan skenario penggunaan field **`lingkup`**, **`id_lingkup`** (`lingkup_id`), **`mulai_menjabat`**, dan **`selesai_menjabat`** pada modul Data Pejabat Penandatangan Perizinan Asrama Polbangtan Malang.

---

## 1. Latar Belakang & Konsep Arsitektur

Dalam sistem perizinan Polbangtan Malang:
1. **Pemisahan Akun (`User`) dan Tanggung Jawab (`Pejabat`)**:
   Pejabat struktural/akademik sering mengalami mutasi atau pergantian SK. Sistem memisahkan tabel `users` (akun login) dari tabel `pejabats` (peran/wewenang struktural) agar tidak perlu membuat role baru di tabel `roles` setiap kali ada jabatan baru.
2. **Resolusi Wewenang Dinamis (ADR-007)**:
   Wewenang pejabat ditentukan oleh kombinasi **Jabatan** dan **Lingkup Wewenang**. Hal ini memungkinkan satu jenis izin (misal: *Izin Keluar*) berlaku untuk semua mahasiswa, sementara pejabat penandatangan otomatis disesuaikan dengan prodi atau blok asrama mahasiswa yang mengajukan.

---

## 2. Penjelasan Rinci Setiap Field

| Field | Tipe Data | Deskripsi & Fungsi | Contoh Nilai |
|---|---|---|---|
| **`lingkup`** | Enum (`global`, `prodi`, `blok`) | Menentukan cakupan wilayah wewenang persetujuan pejabat | `global`, `prodi`, `blok` |
| **`id_lingkup`** / **`lingkup_id`** | BigInt / Nullable | ID spesifik dari entitas target (Prodi / Blok Asrama) | `1` (Prodi A), `2` (Blok B), `null` |
| **`mulai_menjabat`** | Date / Nullable | Tanggal awal resmi menjabat sesuai SK | `2026-01-01` |
| **`selesai_menjabat`** | Date / Nullable | Tanggal akhir menjabat. **Dikosongkan (`null`) jika masih aktif menjabat hingga sekarang** | `2027-12-31` atau `null` |

---

### A. Field `lingkup` (Cakupan Wewenang)
Field ini menentukan seberapa luas wewenang persetujuan yang dimiliki pejabat:

- **`global` (Seluruh Kampus / Asrama)**:
  Pejabat berwenang menyetujui perizinan **semua mahasiswa** tanpa membedakan prodi atau tempat tinggalnya.
  *Contoh*: Kepala Asrama, Wakil Direktur III (Kemahasiswaan), Direktur.
- **`prodi` (Program Studi Spesifik)**:
  Pejabat **hanya berwenang** menyetujui izin bagi mahasiswa yang berasal dari **Program Studi tertentu**.
  *Contoh*: Ketua Program Studi Penyuluhan Pertanian Berkelanjutan (Kaprodi PPB).
- **`blok` (Blok Ruangan Asrama Spesifik)**:
  Pejabat **hanya berwenang** menyetujui izin bagi mahasiswa yang menempati **Blok Asrama tertentu**.
  *Contoh*: Pembina Asrama Putra Blok A.

---

### B. Field `id_lingkup` / `lingkup_id` (ID Entitas Target)
Field ini berpasangan langsung dengan field `lingkup`:

- **Jika `lingkup = global`**:
  Field `id_lingkup` **dikosongkan (`null`)**, karena wewenang mencakup seluruh kampus.
- **Jika `lingkup = prodi`**:
  Field `id_lingkup` diisi dengan **ID Program Studi** (dari tabel `prodis`).
  *Contoh*: `1` (Teknologi Pertanian), `2` (Penyuluhan Peternakan).
- **Jika `lingkup = blok`**:
  Field `id_lingkup` diisi dengan **ID Blok Ruangan** (dari tabel `blok_ruangans`).
  *Contoh*: `3` (Blok Asrama Flamboyan).

---

### C. Field `mulai_menjabat` & `selesai_menjabat` (Masa Berlaku SK)
Mengelola masa berlaku jabatan untuk keperluan **rekam jejak (audit trail)** dan **keabsahan dokumen**:

1. **`mulai_menjabat`**: Tanggal terhitung mulai menjabat sesuai Surat Keputusan (SK).
2. **`selesai_menjabat`**: Tanggal berakhirnya masa jabatan.
   > 💡 **PENTING**: Jika pejabat yang bersangkutan **masih aktif menjabat saat ini**, maka field `selesai_menjabat` **wajib dikosongkan (`null`)**. Sistem akan menampilkan status masa jabatan sebagai `"Sekarang"`.

#### Keuntungan Penggunaan Periode Menjabat:
- **Histori Pejabat**: Saat terjadi Sertijab (Serah Terima Jabatan) dari Bapak A ke Bapak B, data Bapak A tidak perlu dihapus. Cukup isi tanggal `selesai_menjabat` Bapak A dan non-aktifkan, lalu tambahkan record baru untuk Bapak B.
- **Integritas Surat Izin**: Surat izin yang dicetak di masa lalu tetap sah karena sistem dapat menelusuri siapa pejabat yang berwenang pada tanggal pengajuan tersebut.

---

## 3. Contoh Skenario Kerja Sistem (`ApproverResolver`)

Misalkan Mahasiswa **Ahmad** dari **Prodi Penyuluhan Peternakan (ID Prodi = 2)** mengajukan izin keluar asrama yang membutuhkan persetujuan **Kaprodi**:

```
[Pengajuan Izin Ahmad (Prodi ID: 2)]
         │
         ▼
[ApproverResolver mencari Pejabat]
  - Jabatan   : kaprodi
  - Lingkup   : prodi (ID: 2) ATAU global
  - Status    : Aktif (is_active = true)
         │
         ▼
[Sistem Menemukan Pejabat]
  - Mengarahkan approval ke Dosen Kaprodi Prodi 2
```

---

## 4. Tampilan pada Form Data Pejabat (UI)

Pada halaman **Admin > Data Pejabat** (`/admin/pejabat`), petunjuk ringkas di atas telah diintegrasikan langsung pada modal Tambah dan Edit Pejabat:

- **Lingkup**: *Wewenang: Global (seluruh kampus), Prodi (jurusan), atau Blok (asrama).*
- **ID Lingkup**: *ID spesifik Prodi/Blok jika Lingkup bertipe Prodi/Blok.*
- **Mulai Menjabat**: *Tanggal awal resmi menjabat.*
- **Selesai Menjabat**: *Kosongkan jika masih aktif menjabat saat ini.*
