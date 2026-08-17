# User Research — Epic 03: Modul Workflow Sistem Perizinan

**Proyek:** E-Management Polbangtan-mlg
**Epic:** 03 — Workflow Sistem Perizinan
**Penulis:** Muhammad Naufal Mathara R
**Status:** Proposed
**Tanggal:** 5 Agustus 2026
**Basis analisis:** branch `dev` (commit `5eb1ba2`) + 2 form fisik: `IJIN_KELUAR.docx` (kode form **AR.009**) dan `SURAT_IB_NEW.docx`
**Companion:** `DESIGN-Epic03-Modul-Perizinan.md`, `DESIGN-Epic03-Frontend-Perizinan.md`

---

## 0. Catatan sumber (baca dulu)

PRD Epic 03 **tidak tersedia** saat dokumen ini ditulis — yang ada hanya dua form fisik dan
referensi silang di `docs/adr/ADR-004` ("Refactor RBAC tetap dibutuhkan untuk Epic 01, 03, dan 04").
Karena itu, kebutuhan di bawah **direkonstruksi dari artefak** (form + kode existing), bukan
dikutip dari PRD. Setiap kesimpulan yang belum terverifikasi ditandai **[ASUMSI]** dan masuk ke
daftar di §6 untuk divalidasi sebelum implementasi dimulai.

Riset ini **tidak menunda** implementasi. Rekomendasinya: jalankan §3 (7 wawancara, 3 hari)
paralel dengan Milestone 0 di dokumen desain.

---

## 1. Apa yang sudah diketahui dari artefak (desk research)

### 1.1 Dekomposisi dua form

| Aspek | `IJIN_KELUAR.docx` (AR.009) | `SURAT_IB_NEW.docx` |
|---|---|---|
| Judul | Surat Ijin Keluar Asrama | Surat Ijin Keluar Asrama/**Ijin Bermalam** dan **Ijin Meninggalkan Kelas** |
| Identitas | Nama, Kelas/Prodi, NIRM, No. HP | Nama, Kelas/Prodi, NIRM, No. HP |
| Field pembeda | **\*Anggota UKM/ORMAWA** | **Nomor Kamar** |
| Waktu | Waktu berangkat, waktu kembali, tanggal berangkat, tanggal kembali (4 field terpisah) | Tanggal berangkat/waktu, tanggal kembali/waktu (2 field gabungan) |
| Blok 1 — Persetujuan Kegiatan & Akademik | **Pembina ORMAWA/UKM** (menyetujui) + **Dosen PA** (mengetahui) | **Ketua Program Studi** (menyetujui) + **Dosen Pembimbing Akademik** (mengetahui) |
| Blok 2 — Validasi Asrama | **Petugas Piket Asrama** (mengetahui) + **Kepala Asrama/Unit Kemahasiswaan** (memvalidasi) | **Pelatih Harian Asrama** (mengetahui) + **Kepala Asrama/Unit Kemahasiswaan** (memvalidasi) |
| Konfirmasi tujuan | "Telah tiba di ... pada tanggal ..." + ttd **\*RT setempat** — berlaku untuk *delegasi lomba/kompetisi/IB* | Sama |

**Temuan kunci #1 — struktur sama, penandatangan beda.**
Kedua form punya **kerangka identik**: 4 tanda tangan dalam 2 blok berurutan. Yang berbeda hanya
**siapa** yang mengisi slot 1 dan slot 3. Ini bukan dua proses; ini **satu proses dengan tabel
penandatangan yang berbeda**. Implikasi desain langsung: alur harus **data-driven**, bukan dua
form hardcoded (lihat ADR-006).

**Temuan kunci #2 — penandatangan ditentukan secara dinamis, bukan statis.**
"Dosen PA" bergantung pada kelas mahasiswa. "Pembina ORMAWA/UKM" bergantung pada UKM yang ditulis.
"Ketua Prodi" bergantung pada prodi. "Petugas Piket Asrama" bergantung pada **tanggal keberangkatan**.
Tidak satu pun bisa diselesaikan dengan role global. Ini menjelaskan kenapa ADR-004 menyebut Epic 03
butuh refactor RBAC.

**Temuan kunci #3 — ada langkah setelah surat selesai.**
"Telah tiba di ..." adalah bukti bahwa surat bukan artefak sekali pakai. Ada *lifecycle* pasca-persetujuan:
berangkat → tiba → kembali. Sebagian besar sistem perizinan digital berhenti di "disetujui" dan
melewatkan bagian ini. Di sini justru bagian itulah yang punya nilai paling besar (§1.2).

### 1.2 Temuan dari kode: enum `izin` yang sudah ada tapi mati

Tiga tempat di skema existing sudah menyediakan slot untuk perizinan, tapi **tidak pernah diisi**:

| Lokasi | Isi enum | Status |
|---|---|---|
| `users.status` | `didalam`, `diluar`, `telat`, **`izin`** | `izin` tidak pernah ditulis oleh kode mana pun |
| `presences.log_status` | `didalam`, `diluar`, `telat`, **`izin`**, `non-active` | idem |
| `presensi_apels/senams/upacaras.status_kehadiran` | `Hadir`, `Alpha`, **`Izin`** | hanya bisa diubah manual oleh admin |
| `ukm_presensis.status_kehadiran` | `Hadir`, **`Izin`**, `Alpha` | idem |

Verifikasi: `grep -rn "'izin'" app/` tidak menemukan satu pun penulisan status `izin` pada alur
gerbang. `QRController::presense()` hanya menulis `didalam`/`diluar`/`telat`.

> **Ini temuan paling penting dalam riset ini.** Desainer skema awal jelas mengantisipasi modul
> perizinan, lalu modul itu tidak pernah dibangun. Akibatnya, hari ini **mahasiswa yang izin resmi
> tetap tercatat `telat` di gerbang dan `Alpha` di kegiatan wajib** — kertasnya sah, datanya salah.
> Epic 03 bukan sekadar "digitalisasi form"; Epic 03 adalah **menutup lubang integritas data yang
> sudah menganga sejak awal.** Bingkai ini yang harus dipakai saat menjual proyeknya ke Pak Yongki.

### 1.3 Aset existing yang bisa dipakai ulang

| Aset | Lokasi | Dipakai untuk |
|---|---|---|
| `ukm_members.peran = 'pembina'` (scoped per UKM) | Epic 01 | Resolver penandatangan "Pembina ORMAWA/UKM" — **sudah jadi, tinggal dibaca** |
| `jadwal_petugas` (`date`, `petugas1_id`, `petugas2_id`) | existing | Resolver "Petugas Piket Asrama" per tanggal — **sudah jadi** |
| Pola approve/reject + `catatan` + `verified_by`/`verified_at` | `UkmVerifikasiController` | Pola langkah persetujuan; jangan bikin gaya baru |
| Pola scoping "bukan admin harus anggota aktif" | `UkmVerifikasiController::index()` | Cegah IDOR lintas-prodi/UKM di inbox persetujuan |
| `barryvdh/laravel-dompdf` + `admin/generate/*` | existing | Cetak surat izin agar mirip form kertas |
| `simplesoftwareio/simple-qrcode` | existing | QR verifikasi di surat cetak |
| Middleware `ValidateSignature` (sudah terdaftar) | existing | Link publik bertanda tangan untuk konfirmasi RT |
| Alur `Pelanggaran` (pending → confirmed → done, `users.point`) | existing | Sanksi keterlambatan kembali — jangan bikin mekanisme sanksi baru |

---

## 2. Journey map kondisi saat ini (as-is, berbasis artefak) **[ASUMSI — wajib divalidasi]**

```
       ┌── Mahasiswa ──┬── Pembina/Kaprodi ──┬── Dosen PA ──┬── Piket/Pelatih ──┬── Ka. Asrama ──┬── Satpam ──┬── RT tujuan
       │               │                     │              │                   │                │            │
 T-3h  │ cetak &       │                     │              │                   │                │            │
       │ isi form      │                     │              │                   │                │            │
 T-2h  │ cari & antre ─┼─> ttd basah         │              │                   │                │            │
 T-1d  │ cari & antre ─┼─────────────────────┼─> ttd basah  │                   │                │            │
 T-3h  │ ke asrama ────┼─────────────────────┼──────────────┼─> ttd basah       │                │            │
 T-1h  │ ke kantor ────┼─────────────────────┼──────────────┼───────────────────┼─> validasi     │            │
 T-0   │ tunjukkan ────┼─────────────────────┼──────────────┼───────────────────┼────────────────┼─> lihat    │
 T+n   │ minta ttd ────┼─────────────────────┼──────────────┼───────────────────┼────────────────┼────────────┼─> ttd
 T+n   │ kembali,      │                     │              │                   │                │  scan QR   │
       │ serahkan      │                     │              │                   │  arsip map     │  → 'telat' │
       │ surat         │                     │              │                   │                │    ✗       │
```

**Titik nyeri yang bisa dihipotesiskan dari struktur form (belum diverifikasi):**

| # | Nyeri | Siapa | Bukti pendukung dari artefak |
|---|---|---|---|
| P1 | Butuh berhari-hari karena 4 orang harus ditemui fisik, berurutan | Mahasiswa | 4 blok ttd di 2 lokasi berbeda (kampus + asrama) |
| P2 | Mahasiswa tidak tahu surat sedang di meja siapa | Mahasiswa | Tidak ada mekanisme status di kertas |
| P3 | **Izin sah tetap tercatat `telat`/`Alpha`** | Mahasiswa, Admin | §1.2 — enum `izin` mati |
| P4 | Tidak ada rekap: berapa orang di luar asrama malam ini? | Ka. Asrama, Piket | Surat diarsip sebagai kertas |
| P5 | Ttd mudah dipalsukan, tidak ada jejak waktu | Ka. Asrama | Ttd basah tanpa nomor surat/jam |
| P6 | Petugas piket ganti tiap hari — surat tertahan kalau yang ttd tidak jaga | Mahasiswa | `jadwal_petugas` harian |
| P7 | Konfirmasi RT sering tidak kembali (mahasiswa lupa/RT sulit ditemui) | Ka. Asrama | Ttd RT di halaman terpisah, tanpa deadline |
| P8 | Tidak bisa menjawab "siapa yang belum kembali?" secara real time | Ka. Asrama, orang tua | Tidak ada `waktu_kembali` yang ter-track |

**P3 dan P8 adalah kandidat "killer feature".** Keduanya mustahil diselesaikan dengan kertas —
sedangkan P1 dan P2 hanya *lebih cepat* dengan digital. Jika waktu implementasi terbatas,
prioritaskan yang mustahil dilakukan kertas, bukan yang sekadar lebih cepat.

---

## 3. Rencana riset (3 hari, 7 partisipan)

### 3.1 Tujuan riset

1. Memvalidasi/menggugurkan hipotesis nyeri P1–P8 dan mengurutkannya menurut dampak nyata.
2. Menemukan **jenis izin yang tidak terwakili dua form ini** (izin sakit? pulang kampung? izin
   pribadi tanpa kegiatan? izin rombongan?).
3. Memetakan **aturan tak tertulis** — kapan surat boleh dilewati, siapa yang boleh mendelegasikan
   tanda tangannya, apa yang terjadi kalau mahasiswa kembali terlambat.
4. Mengukur **kesediaan approver memakai aplikasi** — ini asumsi paling berisiko (§6, A1).

### 3.2 Metode & partisipan

| Metode | Partisipan | n | Durasi | Kenapa metode ini |
|---|---|---|---|---|
| Wawancara mendalam | Mahasiswa (2: 1 aktif ORMAWA, 1 tidak) | 2 | 45 mnt | Dua persona berbeda: form A vs form B |
| Wawancara mendalam | Dosen PA | 1 | 30 mnt | Approver dengan beban terbesar (semua izin lewat sini) |
| Wawancara mendalam | Ketua Prodi **atau** Pembina UKM | 1 | 30 mnt | Approver blok 1 |
| Wawancara mendalam | Petugas piket / Pelatih harian | 1 | 30 mnt | Approver yang berganti tiap hari — kasus terberat |
| Wawancara mendalam | Kepala Asrama / Unit Kemahasiswaan | 1 | 45 mnt | Pemilik proses + pemilik keputusan adopsi |
| Observasi kontekstual | Satpam/petugas gerbang | 1 | 30 mnt | Realitas verifikasi di gerbang (sinyal? senter? malam?) |
| Analisis artefak | Arsip surat 1 bulan terakhir | ~50 lbr | 2 jam | Data kuantitatif gratis: distribusi jenis izin, durasi, jam sibuk |

> **Analisis arsip adalah langkah dengan rasio nilai/biaya tertinggi.** Menghitung 50 lembar surat
> lama memberi angka nyata (berapa % IB vs keluar harian, berapa lama rata-rata, hari apa paling
> ramai) tanpa perlu menunggu jadwal siapa pun. Kerjakan ini duluan.

### 3.3 Panduan wawancara — Mahasiswa (45 menit)

**Pembuka (5 mnt).** Perkenalan, jelaskan ini riset untuk memperbaiki proses izin, bukan penilaian.
Tidak ada jawaban benar/salah. Minta izin merekam.

**Konteks (10 mnt) — gali perilaku, bukan opini.**
- Kapan terakhir kali kamu mengurus surat izin? Coba ceritakan dari awal sampai akhir.
- (setelah cerita) Berapa lama total dari mulai isi form sampai surat siap dipakai?
- Ada bagian yang bikin kamu harus bolak-balik? Bagian mana?
- Kamu tahu dari mana form mana yang harus dipakai?

**Deep dive (20 mnt).**
- Pernah ada surat yang akhirnya tidak selesai/tidak jadi dipakai? Apa yang terjadi?
- Kalau salah satu penanda tangan tidak ada di tempat, kamu biasanya ngapain?
- *(P3)* Pernah kejadian kamu sudah izin resmi tapi tetap tercatat alpha atau telat? Ceritakan.
- *(P7)* Untuk delegasi/IB, bagian "telah tiba di..." itu prakteknya bagaimana? Siapa yang tanda tangan?
- Pernah keluar asrama tanpa surat? *(jangan menghakimi — ini pertanyaan paling berharga di panduan ini)*
  Apa yang membuat kamu memilih tidak mengurus surat waktu itu?
- Kalau kamu terlambat kembali dari izin, apa yang terjadi?

**Reaksi (5 mnt).** Tunjukkan sketsa alur digital (pengajuan → tracker status → surat ber-QR).
- Bagian mana yang paling kamu butuhkan? Bagian mana yang tidak penting?
- Apa yang bikin kamu ragu memakai ini?

**Penutup (5 mnt).** Ada yang belum saya tanyakan tapi penting? Terima kasih.

### 3.4 Panduan wawancara — Approver (Dosen PA / Kaprodi / Pembina / Piket, 30 menit)

**Konteks (8 mnt).**
- Dalam seminggu, kira-kira berapa surat izin yang Bapak/Ibu tanda tangani?
- Kapan biasanya mahasiswa datang membawa surat? Hari apa, jam berapa?
- Apa yang Bapak/Ibu **periksa** sebelum tanda tangan? *(ini menentukan data apa yang harus tampil di layar approver — pertanyaan paling penting di panduan ini)*

**Deep dive (17 mnt).**
- Pernah menolak? Alasan penolakan yang paling sering apa?
- Kalau sedang di luar kota / tidak masuk, surat mahasiswa bagaimana? Ada yang mewakili?
- Apakah pernah tanda tangan tanpa benar-benar memeriksa? Dalam situasi apa? *(tanyakan dengan hati-hati; jawabannya menentukan apakah persetujuan massal boleh ada)*
- Kalau harus menyetujui lewat HP, apa yang membuat Bapak/Ibu **tidak** mau melakukannya?
- Apakah tanda tangan Bapak/Ibu di surat ini punya konsekuensi kalau mahasiswanya bermasalah di jalan?
  *(menggali kebutuhan legal — menentukan §ADR-008)*

**Reaksi (5 mnt).** Tunjukkan sketsa inbox persetujuan + tombol setujui/tolak + catatan.

### 3.5 Panduan observasi — Satpam/gerbang (30 menit, di lokasi, sore–malam)

Amati, jangan tanya duluan:
- Apakah petugas benar-benar membaca surat, atau hanya melihat sekilas?
- Berapa detik interaksi rata-rata? *(menentukan apakah verifikasi QR realistis)*
- Ada sinyal seluler? Ada perangkat? Penerangan cukup untuk memindai QR malam hari?
- Apa yang terjadi kalau mahasiswa tidak bisa menunjukkan surat?

> Jawaban dari observasi ini **menentukan** apakah verifikasi gerbang boleh bergantung pada
> internet. Kalau sinyal buruk, surat PDF cetak dengan QR **tetap wajib** dan verifikasi online
> hanya jadi pelengkap — bukan sebaliknya.

---

## 4. Persona & Jobs-to-be-Done

| Persona | Job-to-be-Done | Job emosional/sosial | Menyentuh |
|---|---|---|---|
| **Mahasiswa** | "Ketika saya harus keluar asrama, saya ingin izinnya beres tanpa mengorbankan jam kuliah untuk berburu tanda tangan, supaya saya bisa fokus ke kegiatan yang jadi alasan izin itu." | Tidak ingin terlihat sebagai mahasiswa bermasalah | US 3.1, 3.2 |
| **Mahasiswa delegasi lomba** | "Ketika saya mewakili kampus, saya ingin surat saya cepat dan sah di mata panitia/RT tujuan, supaya keberangkatan tidak batal." | Bangga mewakili, malu kalau tertahan administrasi | US 3.1, 3.7 |
| **Dosen PA** | "Ketika mahasiswa bimbingan saya mengajukan izin, saya ingin tahu cukup konteks untuk menyetujui dalam hitungan detik, supaya tidak menumpuk." | Tidak mau ikut disalahkan kalau mahasiswanya celaka | US 3.3, 3.4 |
| **Ketua Prodi / Pembina UKM** | "Saya ingin memastikan izin ini benar terkait kegiatan resmi, bukan alasan karangan." | Menjaga nama prodi/UKM | US 3.3 |
| **Petugas Piket / Pelatih Harian** | "Saat shift saya, saya ingin tahu siapa saja yang berhak keluar malam ini tanpa membolak-balik map." | Tidak mau kecolongan saat jaganya | US 3.4, 3.6 |
| **Kepala Asrama** | "Saya ingin tahu, kapan pun ditanya, siapa yang sedang di luar dan kapan seharusnya kembali." | Bertanggung jawab pada orang tua & pimpinan | US 3.5, 3.6, 3.8 |
| **Admin/Operator** | "Saya ingin laporan perizinan bulanan tanpa menghitung kertas satu per satu." | — | US 3.8 |
| **RT / panitia di lokasi tujuan** *(pengguna eksternal, tanpa akun)* | "Saya diminta membenarkan bahwa anak ini benar tiba di tempat saya — saya mau melakukannya dalam 10 detik tanpa install apa pun." | Tidak mau repot | US 3.7 |

**Konsekuensi desain dari JTBD terakhir:** RT **tidak boleh** diminta membuat akun, login, atau
memasang aplikasi. Satu-satunya rancangan yang memenuhi job-nya adalah **tautan bertanda tangan
sekali pakai** yang dibuka dari QR di surat cetak (lihat ADR-008).

**Konsekuensi desain dari JTBD Dosen PA:** "dalam hitungan detik" berarti layar persetujuan harus
memuat **semua konteks keputusan di satu layar tanpa scroll** — nama, kelas, keperluan, tujuan,
durasi, riwayat pelanggaran/keterlambatan sebelumnya. Kalau approver harus mengklik untuk melihat
detail, desainnya gagal memenuhi job-nya.

---

## 5. How Might We

Dari nyeri P1–P8, direframe menjadi ruang solusi:

1. HMW membuat mahasiswa mendapat kepastian izin **sebelum** hari keberangkatan, tanpa menambah beban approver?
2. HMW membuat approver menyelesaikan satu persetujuan **di bawah 15 detik**, dari HP, tanpa membuka laptop?
3. HMW memastikan izin yang sah **otomatis** memperbaiki catatan kehadiran, tanpa admin menyunting manual?
4. HMW membuat Kepala Asrama tahu siapa yang di luar **tanpa bertanya kepada siapa pun**?
5. HMW membuat surat digital **sama dipercayanya** dengan tanda tangan basah di mata satpam dan RT?
6. HMW menangani approver yang tidak responsif tanpa membuat persetujuan jadi formalitas kosong?
7. HMW membuat mahasiswa **memilih** mengurus izin resmi ketimbang keluar diam-diam?
8. HMW mengubah "telah tiba di..." dari kewajiban yang sering terlupa menjadi sesuatu yang mahasiswa **mau** lakukan?

> HMW #7 adalah yang paling sering dilupakan. Sistem perizinan yang lebih ketat tapi lebih ribet
> justru **menaikkan** angka keluar tanpa izin. Ukuran keberhasilan Epic 03 bukan "berapa izin
> tercatat", melainkan **rasio izin resmi terhadap total keluar** (lihat §7 M4).

---

## 6. Asumsi & risiko (diurutkan berdasarkan risiko)

| # | Asumsi | Risiko bila salah | Cara termurah menguji |
|---|---|---|---|
| **A1** | Approver (Dosen PA, Kaprodi, Ka. Asrama) mau dan mampu menyetujui lewat aplikasi | **Fatal.** Alur mandek di langkah manusia; mahasiswa kembali ke kertas dan sistem jadi beban ganda | Wawancara §3.4 + tes 1 minggu: 5 izin nyata lewat WhatsApp+form sederhana, ukur waktu responsnya. **Lakukan sebelum menulis kode apa pun.** |
| **A2** | Semua mahasiswa punya Dosen PA yang terdata dan setiap prodi punya Kaprodi terdata | **Tinggi.** Rantai persetujuan tidak bisa di-resolve; pengajuan gagal saat submit | Query `users`/`kelas`/`prodis` hari ini. Kalau kosong → Milestone 0 wajib jadi prasyarat |
| **A3** | Dua form ini mencakup semua jenis izin yang dipakai | Sedang. Muncul jenis izin tak terduga setelah rilis | Analisis arsip §3.2 + tanya Ka. Asrama |
| **A4** | Satpam punya perangkat & sinyal untuk memindai QR di gerbang | Sedang. Verifikasi online tidak terpakai | Observasi §3.5 |
| **A5** | Surat digital diterima sebagai sah oleh pihak eksternal (RT, panitia lomba, orang tua) | Sedang. Mahasiswa tetap butuh kertas → nilai digitalisasi turun | Tanya Ka. Asrama tentang preseden + siapkan PDF cetak sebagai default (mitigasi, bukan penghapusan risiko) |
| **A6** | Persetujuan berbasis jejak audit (siapa/kapan/IP) cukup secara hukum untuk administrasi internal | Rendah–sedang. Kalau surat harus punya kekuatan hukum eksternal, butuh TTE tersertifikasi (BSrE) | Konfirmasi ke Ka. Asrama/bagian hukum. Lihat ADR-008 |
| **A7** | Mahasiswa mengajukan izin **sebelum** berangkat, bukan mengurus setelahnya | Rendah. Kalau banyak izin retroaktif, state machine perlu jalur "izin susulan" | Analisis arsip: bandingkan tanggal ttd vs tanggal berangkat |
| **A8** | Beban ~50–300 pengajuan/minggu, puncak Jumat sore | Rendah. Tidak mengubah arsitektur (MySQL sanggup), hanya mengubah desain notifikasi | Analisis arsip |

**Urutan tindakan yang benar:** uji A1 dan A2 **lebih dulu**. Keduanya bisa menggugurkan atau
mengubah bentuk seluruh epic, dan keduanya bisa diuji dalam 2 hari tanpa menulis satu baris kode.

---

## 7. Metrik keberhasilan

Baseline diambil dari analisis arsip (§3.2) sebelum rilis.

| # | Metrik | Baseline (kertas) | Target 3 bulan | Cara ukur |
|---|---|---|---|---|
| M1 | Median waktu pengajuan → disetujui | *(ukur dari arsip)* — hipotesis: >24 jam | **< 4 jam** | `approved_at - submitted_at` |
| M2 | % pengajuan yang mandek >48 jam di satu langkah | — | **< 5 %** | Query `izin_approvals` status `menunggu` |
| M3 | **Kesalahan pencatatan kehadiran (`telat`/`Alpha` padahal izin sah)** | hipotesis: mendekati 100 % | **0** | Cross-check `presences` × izin aktif |
| M4 | **Rasio izin resmi terhadap total keluar asrama** *(metrik utama)* | tidak terukur hari ini | naik & terukur | `pengajuan_izins` ÷ scan keluar gerbang |
| M5 | % kembali terlambat dari izin | tidak terukur | terukur, lalu turun | `kembali_at > waktu_kembali` |
| M6 | % konfirmasi tiba (delegasi/IB) yang kembali terisi | hipotesis: rendah | **> 80 %** | Kolom konfirmasi tiba terisi |
| M7 | Adopsi approver: % langkah diselesaikan di aplikasi (bukan diminta manual) | 0 % | **> 90 %** | `izin_approvals.acted_at` terisi |

> **M3 adalah metrik yang membuktikan nilai epic ini, dan satu-satunya yang mustahil dicapai dengan
> memperbaiki proses kertas.** Jadikan ini angka pertama di slide laporan.

---

## 8. Ringkasan untuk masuk ke desain

Empat kesimpulan riset yang langsung membentuk arsitektur (detail di `DESIGN-Epic03-Modul-Perizinan.md`):

1. **Satu proses, banyak varian penandatangan** → alur wajib data-driven (ADR-006), bukan dua tabel form.
2. **Penandatangan ditentukan dinamis (per-kelas, per-prodi, per-UKM, per-tanggal)** → butuh lapisan
   *resolver* penandatangan, bukan penambahan role global (ADR-007).
3. **Nilai terbesar ada di integrasi, bukan di form** → gerbang, presensi kegiatan, dan pelanggaran
   wajib masuk scope Epic 03; enum `izin` yang mati harus dihidupkan.
4. **Bottleneck-nya manusia, bukan mesin** → optimasi diarahkan ke kecepatan keputusan approver
   (inbox, konteks satu layar, notifikasi, SLA), bukan ke throughput database.
