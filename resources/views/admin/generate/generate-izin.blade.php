<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Izin Keluar Asrama - {{ $pengajuan->nomor_surat }}</title>
    <style>
        @page {
            margin: 1.5cm 1.5cm 1.5cm 1.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.3;
            color: #000;
        }
        .header-table {
            width: 100%;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .header-logo {
            width: 70px;
            text-align: center;
        }
        .header-text {
            text-align: center;
        }
        .header-text h3 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text h4 {
            margin: 2px 0;
            font-size: 11pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text p {
            margin: 0;
            font-size: 9pt;
            font-style: italic;
        }
        .form-code {
            position: absolute;
            top: 0;
            right: 0;
            font-size: 9pt;
            font-weight: bold;
            border: 1px solid #000;
            padding: 2px 6px;
        }
        .title-section {
            text-align: center;
            margin-bottom: 15px;
        }
        .title-section h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .title-section p {
            margin: 2px 0 0 0;
            font-size: 10pt;
            font-weight: bold;
        }
        .info-table {
            width: 100%;
            margin-bottom: 15px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 3px 4px;
            vertical-align: top;
        }
        .info-label {
            width: 28%;
            font-weight: normal;
        }
        .info-colon {
            width: 2%;
        }
        .info-value {
            width: 70%;
            font-weight: bold;
        }
        .approval-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            margin-bottom: 15px;
        }
        .approval-table th, .approval-table td {
            border: 1px solid #000;
            padding: 6px;
            text-align: center;
            font-size: 10pt;
        }
        .approval-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .qr-section {
            margin-top: 15px;
            width: 100%;
        }
        .qr-table {
            width: 100%;
        }
        .manual-box {
            border: 1px dashed #000;
            padding: 8px;
            margin-top: 15px;
            font-size: 9pt;
            background-color: #fafafa;
        }
        .manual-box h5 {
            margin: 0 0 4px 0;
            font-size: 9.5pt;
            text-transform: uppercase;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="form-code">AR.009</div>

    <!-- Header Kop Surat -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                {{-- <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path('img/logo-asrama2.jpeg'))) }}" width="60" alt=""> --}}
                <img src="data:image/jpeg;base64,{!! base64_encode(file_get_contents(public_path('img/logo-asrama2.jpeg'))) !!}" width="60" alt="">
            </td>
            <td class="header-text">
                <h3>KEMENTERIAN PERTANIAN</h3>
                <h4>BADAN PENYULUHAN DAN PENGEMBANGAN SDM PERTANIAN</h4>
                <h4>POLITEKNIK PEMBANGUNAN PERTANIAN MALANG</h4>
                <p>Jl. Dr. Cipto 145 Bedali Lawang Malang 65215 Telp/Fax. (0341) 427771</p>
            </td>
        </tr>
    </table>

    <!-- Judul Dokumen -->
    <div class="title-section">
        <h2>SURAT IZIN KELUAR / MENINGGALKAN ASRAMA</h2>
        <p>Nomor: {{ $pengajuan->nomor_surat }}</p>
    </div>

    <p style="margin-bottom: 8px;">Diberikan izin keluar/meninggalkan asrama kepada mahasiswa di bawah ini:</p>

    <!-- Data Identitas Mahasiswa Snapshot -->
    <table class="info-table">
        <tr>
            <td class="info-label">Nama Mahasiswa</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ $pengajuan->nama_snapshot }}</td>
        </tr>
        <tr>
            <td class="info-label">NIRM</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ $pengajuan->nirm_snapshot ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Program Studi / Kelas</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ $pengajuan->prodi_snapshot }} / {{ $pengajuan->kelas_snapshot }}</td>
        </tr>
        <tr>
            <td class="info-label">Blok / Ruangan Kamar</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ $pengajuan->no_kamar_snapshot ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Jenis Perizinan</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ optional($pengajuan->jenisIzin)->nama ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">Tujuan Lokasi</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ $pengajuan->tujuan_lokasi }} ({{ $pengajuan->alamat_tujuan ?? 'Tidak ada alamat rincian' }})</td>
        </tr>
        <tr>
            <td class="info-label">Waktu Keberangkatan</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ optional($pengajuan->waktu_berangkat)->format('d F Y H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="info-label">Waktu Perkiraan Kembali</td>
            <td class="info-colon">:</td>
            <td class="info-value">{{ optional($pengajuan->waktu_kembali)->format('d F Y H:i') }} WIB</td>
        </tr>
        <tr>
            <td class="info-label">Keperluan / Alasan</td>
            <td class="info-colon">:</td>
            <td class="info-value" style="font-weight: normal; font-style: italic;">"{{ $pengajuan->keperluan }}"</td>
        </tr>
    </table>

    <!-- Tabel Rantai Persetujuan Pejabat Bertanggal -->
    <h4 style="margin: 10px 0 4px 0; font-size: 10pt; text-transform: uppercase;">Rantai Persetujuan / Lembar Pengesahan Resmi:</h4>
    <table class="approval-table">
        <thead>
            <tr>
                <th style="width: 8%;">No</th>
                <th style="width: 27%;">Jabatan / Alur</th>
                <th style="width: 30%;">Nama Pejabat Penandatangan</th>
                <th style="width: 20%;">Tanggal Disetujui</th>
                <th style="width: 15%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pengajuan->approvals as $app)
                <tr>
                    <td>{{ $app->urutan }}</td>
                    <td style="text-align: left;">{{ $app->label_snapshot }}</td>
                    <td style="text-align: left; font-weight: bold;">{{ $app->approver_nama_snapshot ?? '-' }}</td>
                    <td>{{ $app->acted_at ? $app->acted_at->format('d/m/Y H:i') : '-' }}</td>
                    <td>
                        @if ($app->status === 'disetujui')
                            <span style="color: green; font-weight: bold;">DISETUJUI</span>
                        @elseif ($app->status === 'dilewati')
                            <span style="color: gray;">DILEWATI</span>
                        @else
                            <span style="color: orange;">{{ strtoupper($app->status) }}</span>
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Verification QR Code & Signature Section -->
    <div class="qr-section">
        <table class="qr-table">
            <tr>
                <td style="width: 40%; text-align: center; vertical-align: middle;">
                    @if ($qrBase64)
                        {{-- <img src="{{ $qrBase64 }}" width="110" height="110" alt=""> --}}
                        <img src="{!! $qrBase64 !!}" width="110" height="110" alt="">
                        <p style="margin: 4px 0 0 0; font-size: 7.5pt; color: #555;">Pindai QR ini untuk verifikasi keabsahan surat resmi</p>
                    @endif
                </td>
                <td style="width: 60%; text-align: right; vertical-align: top; font-size: 10pt;">
                    <p style="margin: 0;">Malang, {{ optional($pengajuan->disetujui_at ?? now())->format('d F Y') }}</p>
                    <p style="margin: 2px 0 0 0; font-weight: bold;">Manajemen Asrama Polbangtan Malang</p>
                    <p style="margin: 40px 0 0 0; font-size: 8pt; color: #666; font-style: italic;">
                        *Dokumen ini sah secara elektronik & telah disetujui via Sistem Perizinan Asrama
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Cadangan Manual Pengawasan Lapangan -->
    <div class="manual-box">
        <h5>VERIFIKASI MANUAL KEDATANGAN MAHASISWA DI ASRAMA (CADANGAN BLOK PETUGAS PIKET):</h5>
        <table style="width: 100%; font-size: 9pt;">
            <tr>
                <td style="width: 50%;">Tiba Kembali Pada Tanggal : .................................................</td>
                <td style="width: 50%;">Jam Tiba Aktual : .................... WIB</td>
            </tr>
            <tr>
                <td style="width: 50%;">Status Keterlambatan : [  ] Tepat Waktu    [  ] Terlambat</td>
                <td style="width: 50%;">Paraf / Tanda Tangan Petugas Jaga : ............................</td>
            </tr>
        </table>
    </div>
</body>
</html>
