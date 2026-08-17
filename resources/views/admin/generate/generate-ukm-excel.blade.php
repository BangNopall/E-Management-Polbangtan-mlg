<table>
    <thead>
        <tr>
            <th colspan="7" style="font-weight: bold; font-size: 14px; text-align: center;">
                LAPORAN PRESENSI UKM {{ strtoupper($ukmNama) }}
            </th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">
                Politeknik Pembangunan Pertanian Malang - Tanggal Cetak: {{ date('d/m/Y') }}
            </th>
        </tr>
        <tr>
            <th colspan="7" style="text-align: center;">
                Rentang Tanggal: {{ $startDateStr }} s/d {{ $endDateStr }}
            </th>
        </tr>
        <tr></tr>
        <tr style="background-color: #e5e7eb; font-weight: bold;">
            <th>No</th>
            <th>Nama Mahasiswa</th>
            <th>NIM</th>
            <th>Judul Kegiatan</th>
            <th>Tanggal</th>
            <th>Jam Scan</th>
            <th>Status Kehadiran</th>
        </tr>
    </thead>
    <tbody>
        @forelse ($presensis as $index => $p)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $p->user->name ?? '-' }}</td>
                <td>{{ $p->user->nim ?? '-' }}</td>
                <td>{{ $p->jadwal->judul ?? '-' }}</td>
                <td>{{ $p->jadwal->tanggal ? \Carbon\Carbon::parse($p->jadwal->tanggal)->format('d/m/Y') : '-' }}</td>
                <td>{{ $p->jam_kehadiran ? \Carbon\Carbon::parse($p->jam_kehadiran)->format('H:i') : '-' }}</td>
                <td>{{ $p->status_kehadiran }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="7">Belum ada data presensi UKM.</td>
            </tr>
        @endforelse
    </tbody>
</table>
