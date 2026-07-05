<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>

<body>
    <div>
        <h1>Presensi Kegiatan Apel</h1>
        <br>
        <form action="{{ route('PresensiKegiatanApelStore') }}" method="post">
            @csrf
            <h2>tes presensi</h2>
            <input type="hidden" id="user_id" name="user_id" value="{{ $user->id }}">
            <input type="hidden" id="tanggal_kegiatan" name="tanggal_kegiatan" value="{{ $today }}">
            <input type="hidden" name="time" id="time" value="{{ $time }}">
            <button type="submit">submit</button>
        </form>
        <br>
        <table>
            <h2>
                User Login Data
            </h2>
            <thead>
                <tr>
                    <th>id</th>
                    <th>Nama</th>
                    <th>blok</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>{{ $user->id }}</td>
                    <td>{{ $user->name }}</td>
                    <td>{{ $user->blok->name }}</td>
                </tr>
            </tbody>
        </table>
        <br>
        <table>
            <thead>
                <h2>Daftar Jadwal Keiatan apel hari ini</h2>
                <tr>
                    <th>id</th>
                    <th>Tanggal Kegiatan</th>
                    <th>Jenis Kegiatan</th>
                    <th>blok</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($daftarJadwalKegiatanAsrama as $jadwalKegiatanAsrama)
                    <tr>
                        <td>{{ $jadwalKegiatanAsrama->id }}</td>
                        <td>{{ $jadwalKegiatanAsrama->tanggal_kegiatan }}</td>
                        <td>{{ $jadwalKegiatanAsrama->jenis_kegiatan }}</td>
                        <td>{{ $jadwalKegiatanAsrama->blokRuangan->name }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <br>
        <table>
            <thead>
                <h2>data presensi apel hari ini</h2>
                <tr>
                    <th>id</th>
                    <th>jadwal Kegiatan Asrama id</th>
                    <th>user_id</th>
                    <th>Tanggal Kegiatan</th>
                    <th>jam kehadiran</th>
                    <th>status kehadiran</th>
                </tr>
            </thead>
            <tbody>
                @if ($presensiApel->status_kehadiran == 'Alpha')
                    <tr>
                        <td>{{ $presensiApel->id }}</td>
                        <td>{{ $presensiApel->jadwalKegiatanAsrama_id }}</td>
                        <td>{{ $presensiApel->user_id }}</td>
                        <td>{{ $presensiApel->jadwalKegiatanAsrama->tanggal_kegiatan }}</td>
                        <td>{{ $presensiApel->jam_kehadiran}}</td>
                        <td>{{ $presensiApel->status_kehadiran }}</td>
                    </tr>
                @elseif($presensiApel->status_kehadiran == 'Hadir')
                    <tr>
                        <td>{{ $presensiApel->id }}</td>
                        <td>{{ $presensiApel->jadwalKegiatanAsrama_id }}</td>
                        <td>{{ $presensiApel->user_id }}</td>
                        <td>{{ $presensiApel->jadwalKegiatanAsrama->tanggal_kegiatan }}</td>
                        <td>{{ $presensiApel->jam_kehadiran}}</td>
                        <td>{{ $presensiApel->status_kehadiran }}</td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>
</body>

</html>
