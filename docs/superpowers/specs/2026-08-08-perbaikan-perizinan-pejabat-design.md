# Design Specification: Perbaikan Perizinan Mahasiswa, Info Resolver Dosen PA/Operator, & UI Data Pejabat

**Tanggal**: 2026-08-08  
**Status**: APPROVED  
**Target Branch**: `feat/epic-03`  

---

## 1. Ringkasan & Tujuan

Dokumen ini mendesain 4 poin perbaikan pada modul perizinan asrama (Epic 03) dan manajemen data pejabat:
1. **Perbaikan Pengajuan Izin Mahasiswa**: Memperbaiki validasi `StorePengajuanIzinRequest` agar mahasiswa dapat mengirim pengajuan izin meskipun `dosen_pa_id` di kelasnya belum ditentukan (karena sudah didukung fallback otomatis ke role **Operator** di `ApproverResolver`).
2. **Kejelasan Informasi Resolver Dosen PA / Operator**: Menambahkan keterangan pada antarmuka admin (`admin/izin/jenis/form.blade.php`) dan antarmuka mahasiswa (`izin/create.blade.php`) bahwa alur Dosen PA memiliki fallback otomatis ke Staf Operator.
3. **Kejelasan Field Data Pejabat**: Menjelaskan dan menambahkan petunjuk UI mengenai fungsi field `lingkup`, `id_lingkup`, `mulai_menjabat`, dan `selesai_menjabat` pada halaman Data Pejabat.
4. **Fix Layout Modal Edit Data Pejabat**: Memperbaiki kerusakkan padding `p-4 md:p-5` pada modal edit pejabat di `admin/pejabat/index.blade.php` akibat sintaks HTML invalid (elemen `<div>` modal yang berada langsung di dalam `<tbody>`).

---

## 2. Perubahan Arsitektur & Komponen

### 2.1 Form Request Validasi (`app/Http/Requests/StorePengajuanIzinRequest.php`)
- **Masalah**: Gerbang 1 validasi mewajibkan `!optional($user->kelas)->dosen_pa_id`. Jika bernilai `null`, validasi menggagalkan pengajuan izin dengan pesan kesalahan profil.
- **Solusi**: Hapus syarat `!optional($user->kelas)->dosen_pa_id` dari Gerbang 1 validasi di `StorePengajuanIzinRequest`. Pengecekan profil tetap mewajibkan `prodi_id`, `kelas_id`, dan `blok_ruangan_id`.
- **Alasan**: `ApproverResolver::resolveDosenPa()` secara aman telah menangani kasus `dosen_pa_id` kosong dengan melakukan fallback otomatis ke pengguna ber-role `OPERATOR_ROLE_ID` (`role_id = 2`).

### 2.2 Informasi Resolver pada View Jenis Izin & Pratinjau
- **File**: `resources/views/admin/izin/jenis/form.blade.php`
  - Ubah opsi select resolver `dosen_pa` menjadi:  
    `Dosen PA Mahasiswa (Fallback: Operator)`
  - Tambahkan teks penjelasan di bawah select box:  
    `Mencari Dosen PA yang terhubung dengan kelas mahasiswa. Jika kelas belum diset Dosen PA, otomatis dialihkan ke akun staf Operator.`
- **File**: `resources/views/izin/create.blade.php`
  - Pastikan teks pratinjau alur persetujuan menampilkan keterangan jelas saat resolver `dosen_pa` atau fallback `operator` digunakan.

### 2.3 Penjelasan Field & Fix HTML Layout Data Pejabat (`resources/views/admin/pejabat/index.blade.php`)
- **Penjelasan Fungsi Field**:
  - `lingkup`: Tingkat cakupan wewenang pejabat (`global` = seluruh kampus/asrama, `prodi` = program studi spesifik, `blok` = blok ruangan asrama spesifik).
  - `id_lingkup` / `lingkup_id`: ID spesifik prodi (tabel `prodis`) atau blok ruangan (tabel `blok_ruangans`). Diisi jika lingkup bertipe `prodi` atau `blok`; dikosongkan jika `global`.
  - `mulai_menjabat`: Tanggal resmi mulai menjabat.
  - `selesai_menjabat`: Tanggal resmi selesai menjabat (dikosongkan jika masih aktif menjabat hingga sekarang).
- **Perbaikan Modal Edit HTML**:
  - Pindahkan modal edit `id="editPejabatModal{{ $pejabat->id }}"` keluar dari struktur `<tbody>...</tbody>`.
  - Ditempatkan di bagian bawah view setelah elemen `</table>`.
  - Hal ini memulihkan struktur HTML5 yang valid dan memastikan padding Flowbite `p-4 md:p-5` berfungsi dengan benar.

---

## 3. Rencana Pengujian (Testing Plan)

1. **Feature Test (`tests/Feature/Izin/IzinMahasiswaSubmitTest.php` atau test terkait)**:
   - Verifikasi pengajuan izin berhasil disimpan ketika mahasiswa memiliki `kelas_id` namun `kelas.dosen_pa_id` bernilai `null`.
   - Verifikasi bahwa kandidat persetujuan yang dihasilkan oleh `ApproverResolver` mengarah ke akun ber-role Operator.
2. **Visual & UI Verification**:
   - Memastikan form create izin mahasiswa dapat dikirim tanpa hambatan validasi profil Dosen PA.
   - Memastikan modal edit pejabat pada `admin/pejabat/index.blade.php` menampilkan padding `p-4 md:p-5` secara presisi.
