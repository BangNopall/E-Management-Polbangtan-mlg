<table>
    <thead>
        <tr>
            <th>TANGGAL : {{ strftime('%d %B %Y', strtotime($startDate)) }} - {{ strftime('%d %B %Y', strtotime($endDate)) }}</th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td>BLOK : A</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>LAPORAN MINGGUAN E-ABSEN ASRAMA POLBANGTAN MALANG</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <td>#</td>
            <td>TANGGAL</td>
            <td>NIM</td>
            <td>NAMA</td>
            <td>KELAS</td>
            <td>NOMOR KAMAR</td>
            <td>JAM KELUAR DAN MASUK</td>
        </tr>
        @foreach ($data as $index => $d)
            <tr>
                <th scope="row">{{ $index + 1 }}</th>
                <td>{{ $d->presence_date }}</td>
                <td>{{ $d->user->nim }}</td>
                <td>{{ $d->user->name }}</td>
                <td>{{ $d->user->kelas->nama_kelas }}</td>
                <td>{{ $d->user->no_kamar }}</td>
                <td>{{ $d->presence_keluar }} WIB - {{ $d->presence_masuk }} WIB</td>
            </tr>
        @endforeach
    </tbody>
</table>