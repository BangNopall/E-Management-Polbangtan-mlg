<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Surat Izin Keluar Asrama - {{ $pengajuan->nomor_surat }}</title>
    <style>
        @page {
            margin: 1.2cm 1.5cm 1.2cm 1.5cm;
        }
        body {
            font-family: 'Arial', Arial, Helvetica, sans-serif;
            font-size: 11pt;
            line-height: 1.25;
            color: #000;
        }
        .top-meta {
            width: 100%;
            margin-bottom: 4px;
        }
        .form-code {
            float: right;
            font-size: 8.5pt;
            font-weight: bold;
            border: 1px solid #000;
            padding: 2px 6px;
        }

        /* Styling Kop Surat Resmi Kementan */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }
        .header-logo {
            width: 80px;
            text-align: center;
            vertical-align: middle;
            padding-right: 8px;
        }
        .header-text {
            text-align: center;
            vertical-align: middle;
        }
        .header-text .instansi-kementan {
            margin: 0;
            font-size: 11pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header-text .instansi-eselon {
            margin: 1px 0;
            font-size: 9.5pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header-text .instansi-satker {
            margin: 2px 0;
            font-size: 12pt;
            font-weight: bold;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }
        .header-text .instansi-alamat {
            margin: 0;
            font-size: 8pt;
            line-height: 1.2;
        }

        /* Garis Dobel Khas Naskah Dinas Resmi */
        .kop-separator {
            width: 100%;
            border-top: 3px solid #000;
            border-bottom: 1px solid #000;
            height: 2px;
            margin-top: 5px;
            margin-bottom: 14px;
        }

        .title-section {
            text-align: center;
            margin-bottom: 12px;
        }
        .title-section h2 {
            margin: 0;
            font-size: 12pt;
            font-weight: bold;
            text-decoration: underline;
            text-transform: uppercase;
        }
        .title-section p {
            margin: 2px 0 0 0;
            font-size: 9.5pt;
            font-weight: bold;
        }
        .info-table {
            width: 100%;
            margin-bottom: 10px;
            border-collapse: collapse;
        }
        .info-table td {
            padding: 2.5px 3px;
            vertical-align: top;
        }
        .info-label {
            width: 28%;
        }
        .info-colon {
            width: 2%;
            text-align: center;
        }
        .info-value {
            width: 70%;
            font-weight: bold;
        }
        .approval-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 8px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .approval-table th, .approval-table td {
            border: 1px solid #000;
            padding: 5px;
            text-align: center;
            font-size: 9.5pt;
        }
        .approval-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .qr-section {
            margin-top: 8px;
            width: 100%;
            page-break-inside: avoid;
        }
        .qr-table {
            width: 100%;
            border-collapse: collapse;
        }
        .manual-box {
            border: 1px solid #333;
            padding: 6px 8px;
            margin-top: 10px;
            font-size: 8.5pt;
            background-color: #fafafa;
            page-break-inside: avoid;
        }
        .manual-box h5 {
            margin: 0 0 4px 0;
            font-size: 8.5pt;
            text-transform: uppercase;
            font-weight: bold;
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <!-- Header Kop Surat Resmi Sesuai Format Docx -->
    <table class="header-table">
        <tr>
            <td class="header-logo">
                @php
                    // Prioritaskan logo resmi Kementan jika ada di folder img/
                    $logoKementan = public_path('img/logokementan.png');

                    $logoFinalPath = file_exists($logoKementan) ? $logoKementan 
                                    : (file_exists($logoPolbangtan) ? $logoPolbangtan 
                                    : (file_exists($logoAsrama) ? $logoAsrama : null));

                    $logoBase64 = $logoFinalPath ? base64_encode(file_get_contents($logoKementan)) : null;
                @endphp

                @if ($logoBase64)
                    <img src="data:image/png;base64,{{ $logoBase64 }}" width="75" alt="Logo Instansi">
                @endif
            </td>
            <td class="header-text">
                <div class="instansi-kementan">KEMENTERIAN PERTANIAN</div>
                <div class="instansi-eselon">BADAN PENYULUHAN DAN PENGEMBANGAN SUMBER DAYA MANUSIA PERTANIAN</div>
                <div class="instansi-satker">POLITEKNIK PEMBANGUNAN PERTANIAN MALANG</div>
                <div class="instansi-alamat">
                    Jalan Dr. Cipto 144 A Bedali, Lawang – Malang 65200 Kotak Pos 144<br>
                    Telepon (0341) 427771, 427772, 427379, Fax. 427774<br>
                    Website: www.polbangtanmalang.ac.id &nbsp; Email: official@polbangtanmalang.ac.id
                </div>
            </td>
        </tr>
    </table>

    <!-- Garis Pemisah Kop Ganda (Tebal - Tipis) -->
    <div class="kop-separator"></div>

    <!-- Judul Dokumen -->
    <div class="title-section">
        <h2>SURAT IZIN KELUAR / MENINGGALKAN ASRAMA</h2>
        <p>Nomor: {{ $pengajuan->nomor_surat }}</p>
    </div>

    <p style="margin-bottom: 6px;">Diberikan izin keluar/meninggalkan asrama kepada mahasiswa di bawah ini:</p>

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
            <td class="info-value">{{ $pengajuan->prodi_snapshot ?? '-' }} / {{ $pengajuan->kelas_snapshot ?? '-' }}</td>
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
            <td class="info-value">{{ $pengajuan->tujuan_lokasi }} ({{ $pengajuan->alamat_tujuan ?? 'Tidak ada rincian alamat' }})</td>
        </tr>
        <tr>
            <td class="info-label">Waktu Keberangkatan</td>
            <td class="info-colon">:</td>
            <td class="info-value">
                {{ $pengajuan->waktu_berangkat ? \Carbon\Carbon::parse($pengajuan->waktu_berangkat)->translatedFormat('d F Y H:i') : '-' }} WIB
            </td>
        </tr>
        <tr>
            <td class="info-label">Waktu Perkiraan Kembali</td>
            <td class="info-colon">:</td>
            <td class="info-value">
                {{ $pengajuan->waktu_kembali ? \Carbon\Carbon::parse($pengajuan->waktu_kembali)->translatedFormat('d F Y H:i') : '-' }} WIB
            </td>
        </tr>
        <tr>
            <td class="info-label">Keperluan / Alasan</td>
            <td class="info-colon">:</td>
            <td class="info-value" style="font-weight: normal; font-style: italic;">"{{ $pengajuan->keperluan }}"</td>
        </tr>
    </table>

    <!-- Tabel Rantai Persetujuan Pejabat Bertanggal -->
    <h4 style="margin: 8px 0 4px 0; font-size: 9.5pt; text-transform: uppercase;">Rantai Persetujuan / Lembar Pengesahan Resmi:</h4>
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
            @forelse ($pengajuan->approvals as $app)
                <tr>
                    <td>{{ $app->urutan }}</td>
                    <td style="text-align: left;">{{ $app->label_snapshot }}</td>
                    <td style="text-align: left; font-weight: bold;">{{ $app->approver_nama_snapshot ?? '-' }}</td>
                    <td>{{ $app->acted_at ? \Carbon\Carbon::parse($app->acted_at)->format('d/m/Y H:i') : '-' }}</td>
                    <td>
                        @if ($app->status === 'disetujui')
                            <span style="color: #0b730b; font-weight: bold;">DISETUJUI</span>
                        @elseif ($app->status === 'dilewati')
                            <span style="color: #666666;">DILEWATI</span>
                        @else
                            <span style="color: #d97706; font-weight: bold;">{{ strtoupper($app->status) }}</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align: center; color: #888;">Belum ada persetujuan.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Verification QR Code & Signature Section -->
    <div class="qr-section">
        <table class="qr-table">
            <tr>
                <td style="width: 35%; text-align: center; vertical-align: middle;">
                    @if (!empty($qrBase64))
                        <img src="{!! $qrBase64 !!}" width="105" height="105" alt="QR Verifikasi">
                        <p style="margin: 3px 0 0 0; font-size: 7pt; color: #555;">Pindai QR ini untuk verifikasi keabsahan surat resmi</p>
                    @endif
                </td>
                <td style="width: 65%; text-align: right; vertical-align: top; font-size: 9.5pt;">
                    <p style="margin: 0;">Malang, {{ \Carbon\Carbon::parse($pengajuan->disetujui_at ?? now())->translatedFormat('d F Y') }}</p>
                    <p style="margin: 2px 0 0 0; font-weight: bold;">Manajemen Asrama Polbangtan Malang</p>
                    <p style="margin: 35px 0 0 0; font-size: 7.5pt; color: #555; font-style: italic;">
                        *Dokumen ini sah secara elektronik &amp; telah disetujui via Sistem Perizinan Asrama
                    </p>
                </td>
            </tr>
        </table>
    </div>

    <!-- Cadangan Manual Pengawasan Lapangan -->
    <div class="manual-box">
        <h5>VERIFIKASI MANUAL KEDATANGAN MAHASISWA DI ASRAMA (CADANGAN BLOK PETUGAS PIKET):</h5>
        <table style="width: 100%; font-size: 8.5pt;">
            <tr>
                <td style="width: 50%;">Tiba Kembali Pada Tanggal : .................................................</td>
                <td style="width: 50%;">Jam Tiba Aktual : .................... WIB</td>
            </tr>
            <tr>
                <td style="width: 50%;">Status Keterlambatan : [&nbsp;&nbsp;] Tepat Waktu &nbsp;&nbsp;&nbsp; [&nbsp;&nbsp;] Terlambat</td>
                <td style="width: 50%;">Paraf / Tanda Tangan Petugas Jaga : ............................</td>
            </tr>
        </table>
    </div>
</body>
</html>