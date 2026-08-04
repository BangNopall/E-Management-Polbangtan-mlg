<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Kegiatan UKM — {{ $ukm->nama }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: sans-serif;
        }
        .container {
            width: 730px;
            margin: 28px auto;
        }
        .title {
            text-align: center;
            font-size: 18px;
            margin-bottom: 15px;
        }
        .kotak {
            background: #fff;
            border: 2px solid #dcdcdc;
            border-radius: 8px;
            padding: 12px;
            margin-bottom: 15px;
        }
        .garis {
            border: 1px solid #dcdcdc;
            margin: 8px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            color: #333;
        }
        th, td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 10px;
        }
        .text-center {
            text-align: center;
        }
        .badge {
            padding: 3px 6px;
            border-radius: 3px;
            font-size: 10px;
            color: white;
            font-weight: bold;
            display: inline-block;
        }
        .badge-success { background: #0d8e63; }
        .badge-warning { background: #d97706; }
        .badge-danger  { background: #ae1010; }
        .badge-secondary { background: #6b7280; }
    </style>
</head>
<body>
    <div class="container">
        <h2 class="title">Laporan Kegiatan UKM — {{ $ukm->nama }}</h2>

        <div class="kotak">
            <h4>Profil UKM</h4>
            <div class="garis"></div>
            <table>
                <tr>
                    <td width="150">Nama UKM</td>
                    <td>: <strong>{{ $ukm->nama }}</strong></td>
                </tr>
                <tr>
                    <td>Deskripsi</td>
                    <td>: {{ $ukm->deskripsi ?? '-' }}</td>
                </tr>
                <tr>
                    <td>Jumlah Anggota</td>
                    <td>: {{ $ukm->members_count ?? $ukm->members()->count() }} Anggota</td>
                </tr>
                <tr>
                    <td>Status UKM</td>
                    <td>: {{ $ukm->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                </tr>
                @if ($tanggal_mulai && $tanggal_selesai)
                <tr>
                    <td>Periode Laporan</td>
                    <td>: {{ \Carbon\Carbon::parse($tanggal_mulai)->format('d M Y') }} - {{ \Carbon\Carbon::parse($tanggal_selesai)->format('d M Y') }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="kotak">
            <h4>Daftar Kegiatan & Presensi</h4>
            <div class="garis"></div>
            <table>
                <thead>
                    <tr>
                        <th class="text-center" width="30">NO</th>
                        <th>JUDUL KEGIATAN</th>
                        <th>TANGGAL & WAKTU</th>
                        <th class="text-center">HADIR</th>
                        <th class="text-center">ALPHA</th>
                        <th class="text-center">IZIN</th>
                        <th class="text-center">VERIFIKASI</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jadwals as $jadwal)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <strong>{{ $jadwal->judul }}</strong><br>
                                <span style="font-size: 9px; color: #666;">{{ ucfirst($jadwal->jenis) }}</span>
                            </td>
                            <td>
                                {{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d M Y') }}<br>
                                <span style="font-size: 9px; color: #666;">{{ $jadwal->mulai_acara }} - {{ $jadwal->selesai_acara }}</span>
                            </td>
                            <td class="text-center">{{ $jadwal->presensis->where('status_kehadiran', 'Hadir')->count() }}</td>
                            <td class="text-center">{{ $jadwal->presensis->where('status_kehadiran', 'Alpha')->count() }}</td>
                            <td class="text-center">{{ $jadwal->presensis->where('status_kehadiran', 'Izin')->count() }}</td>
                            <td class="text-center">
                                @if ($jadwal->status_verifikasi === 'disetujui')
                                    <span class="badge badge-success">Disetujui</span>
                                @elseif ($jadwal->status_verifikasi === 'menunggu')
                                    <span class="badge badge-warning">Menunggu</span>
                                @elseif ($jadwal->status_verifikasi === 'ditolak')
                                    <span class="badge badge-danger">Ditolak</span>
                                @else
                                    <span class="badge badge-secondary">Draft</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">Belum ada data kegiatan untuk UKM ini.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
