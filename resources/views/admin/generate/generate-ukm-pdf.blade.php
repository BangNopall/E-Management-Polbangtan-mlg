<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Presensi UKM {{ $ukmNama }}</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #333; padding: 6px 8px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; text-transform: uppercase; }
        .header { text-align: center; margin-bottom: 20px; }
        .header h2 { margin: 0; padding: 0; font-size: 16px; }
        .header p { margin: 4px 0; font-size: 11px; color: #555; }
        .badge-hadir { color: #15803d; font-weight: bold; }
        .badge-alpha { color: #b91c1c; font-weight: bold; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN PRESENSI UKM</h2>
        <p>UKM: {{ $ukmNama }} | Politeknik Pembangunan Pertanian Malang</p>
        <p>Tanggal Cetak: {{ date('d F Y') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 30px; text-align: center;">NO</th>
                <th>NAMA MAHASISWA</th>
                <th>NIM</th>
                <th>JUDUL KEGIATAN</th>
                <th>TANGGAL</th>
                <th>JAM SCAN</th>
                <th>STATUS</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($presensis as $index => $p)
                <tr>
                    <td style="text-align: center;">{{ $loop->iteration }}</td>
                    <td>{{ $p->user->name ?? '-' }}</td>
                    <td>{{ $p->user->nim ?? '-' }}</td>
                    <td>{{ $p->jadwal->judul ?? '-' }}</td>
                    <td>{{ $p->jadwal->tanggal ? \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d/m/Y') : '-' }}</td>
                    <td>{{ $p->jam_scan ? \Carbon\Carbon::parse($p->jam_scan)->format('H:i') : '-' }}</td>
                    <td>
                        @if ($p->status_kehadiran === 'Hadir')
                            <span class="badge-hadir">Hadir</span>
                        @else
                            <span class="badge-alpha">Alpha</span>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" style="text-align: center;">Belum ada data presensi UKM.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
