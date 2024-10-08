<table>
    <tr><td>DAFTAR REKAPAN HADIR KEGIATAN WAJIB SISWA TAHUN 2024</td></tr>
    <tr><td>BLOK {{ $blokName }}</td></tr>
    <tr><td>TANGGAL {{ $dateRangeText }}</td></tr>
    <tr><td>KEGIATAN {{ $requestKegiatan }}</td></tr>
    <tr><td>NO</td><td>NAMA</td><td>KELAS</td><td>Hari &amp; Tgl</td></tr>
    <tr>
        <td></td><td></td><td></td>
        @foreach ($tanggalkegiatan as $date)
            @php $carbonDate = Carbon\Carbon::parse($date); @endphp
            <td>{{ $carbonDate->translatedFormat('d') }}</td>
        @endforeach
    </tr>
    @foreach ($getPresensiAllData as $item)
        <tr>
            <td>{{ $loop->iteration }}</td>
            <td>{{ $item[0]['user']['name'] }}</td>
            <td>{{ $item[0]['user']['kelas']['nama_kelas'] }}</td>
            @foreach ($tanggalkegiatan as $date)
                @php $presensi = $item->firstWhere('formatted_date', $date); @endphp
                @if ($presensi)
                    @if ($presensi['status_kehadiran'] == 'Hadir')<td>H</td>
                    @elseif ($presensi['status_kehadiran'] == 'Izin')<td>I</td>
                    @elseif ($presensi['status_kehadiran'] == 'Alpha')<td>A</td>
                    @endif
                @else
                    <td></td>
                @endif
            @endforeach
        </tr>
    @endforeach
</table>
