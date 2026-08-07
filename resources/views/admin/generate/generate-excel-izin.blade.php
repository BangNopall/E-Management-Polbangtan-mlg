<table>
    <thead>
        <tr>
            <th colspan="9" style="font-weight: bold; text-align: center; font-size: 14pt;">
                LAPORAN REKAPITULASI PERIZINAN MAHASISWA ASRAMA POLBANGTAN MALANG
            </th>
        </tr>
        <tr>
            <th colspan="9" style="text-align: center; font-size: 10pt;">
                Periode: {{ $tanggalMulai ?? 'Awal' }} s/d {{ $tanggalSelesai ?? 'Sekarang' }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold; background-color: #D9D9D9; text-align: center;">NO</th>
            <th style="font-weight: bold; background-color: #D9D9D9; text-align: center;">NOMOR SURAT</th>
            <th style="font-weight: bold; background-color: #D9D9D9;">NAMA MAHASISWA</th>
            <th style="font-weight: bold; background-color: #D9D9D9;">NIRM</th>
            <th style="font-weight: bold; background-color: #D9D9D9;">PRODI / KELAS</th>
            <th style="font-weight: bold; background-color: #D9D9D9;">JENIS IZIN</th>
            <th style="font-weight: bold; background-color: #D9D9D9;">TUJUAN LOKASI</th>
            <th style="font-weight: bold; background-color: #D9D9D9; text-align: center;">WAKTU BERANGKAT</th>
            <th style="font-weight: bold; background-color: #D9D9D9; text-align: center;">WAKTU KEMBALI</th>
            <th style="font-weight: bold; background-color: #D9D9D9; text-align: center;">STATUS</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($izins as $index => $item)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $item->nomor_surat ?? '-' }}</td>
                <td>{{ $item->nama_snapshot }}</td>
                <td>{{ $item->nirm_snapshot ?? '-' }}</td>
                <td>{{ $item->prodi_snapshot }} ({{ $item->kelas_snapshot }})</td>
                <td>{{ optional($item->jenisIzin)->nama ?? '-' }}</td>
                <td>{{ $item->tujuan_lokasi }}</td>
                <td>{{ optional($item->waktu_berangkat)->format('d/m/Y H:i') }}</td>
                <td>{{ optional($item->waktu_kembali)->format('d/m/Y H:i') }}</td>
                <td style="font-weight: bold;">{{ strtoupper($item->status) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
