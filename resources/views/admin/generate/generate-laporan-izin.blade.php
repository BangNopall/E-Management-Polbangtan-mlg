<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Rekapitulasi Perizinan Mahasiswa</title>
    <style>
        @page {
            margin: 1.2cm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 9pt;
            line-height: 1.2;
            color: #000;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }
        .header h2 {
            margin: 0;
            font-size: 13pt;
            font-weight: bold;
            text-transform: uppercase;
        }
        .header h3 {
            margin: 2px 0 0 0;
            font-size: 11pt;
            font-weight: bold;
        }
        .header p {
            margin: 2px 0 0 0;
            font-size: 8pt;
            font-style: italic;
        }
        .meta-info {
            margin-bottom: 10px;
            font-size: 8.5pt;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        .table th, .table td {
            border: 1px solid #333;
            padding: 5px;
            font-size: 8pt;
        }
        .table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <h2>POLITEKNIK PEMBANGUNAN PERTANIAN MALANG</h2>
        <h3>LAPORAN REKAPITULASI PERIZINAN MAHASISWA ASRAMA</h3>
        <p>Jl. Dr. Cipto 145 Bedali Lawang Malang 65215 Telp/Fax. (0341) 427771</p>
    </div>

    <div class="meta-info">
        <strong>Periode Laporan:</strong> {{ $tanggalMulai ?? 'Awal' }} s/d {{ $tanggalSelesai ?? 'Sekarang' }}<br>
        <strong>Dicetak Pada:</strong> {{ date('d F Y H:i') }} WIB
    </div>

    <table class="table">
        <thead>
            <tr>
                <th style="width: 3%;">No</th>
                <th style="width: 15%;">Nomor Surat</th>
                <th style="width: 18%;">Nama Mahasiswa</th>
                <th style="width: 12%;">Prodi / Kelas</th>
                <th style="width: 12%;">Jenis Izin</th>
                <th style="width: 15%;">Tujuan</th>
                <th style="width: 10%;">Waktu Berangkat</th>
                <th style="width: 10%;">Waktu Kembali</th>
                <th style="width: 5%;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($izins as $index => $item)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $item->nomor_surat ?? '-' }}</td>
                    <td><strong>{{ $item->nama_snapshot }}</strong><br><span style="color: #555;">{{ $item->nirm_snapshot ?? '-' }}</span></td>
                    <td>{{ $item->prodi_snapshot }}<br>{{ $item->kelas_snapshot }}</td>
                    <td>{{ optional($item->jenisIzin)->nama ?? '-' }}</td>
                    <td>{{ $item->tujuan_lokasi }}</td>
                    <td class="text-center">{{ optional($item->waktu_berangkat)->format('d/m/Y H:i') }}</td>
                    <td class="text-center">{{ optional($item->waktu_kembali)->format('d/m/Y H:i') }}</td>
                    <td class="text-center"><strong>{{ strtoupper($item->status) }}</strong></td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 15px;">Tidak ada data perizinan pada periode ini.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
