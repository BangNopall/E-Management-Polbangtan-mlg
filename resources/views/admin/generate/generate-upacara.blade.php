<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Kegiatan Wajib Mahasiswa</title>
</head>
<style>
    .td-center-atas,
    .title {
        text-align: center
    }

    .kotak,
    .kotak1 {
        background: #fff;
        border: 2px solid #dcdcdc;
        border-radius: 8px
    }

    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
        font-family: sans-serif
    }

    .container {
        width: 730px;
        height: auto;
        margin: 28px auto
    }

    .kotak {
        margin: 28px auto auto;
        padding: 10px
    }

    .kotak1 {
        margin: 12px auto auto;
        padding: 12px
    }

    .title-profil {
        line-height: 18px
    }

    .garis {
        border: 2px solid #dcdcdc;
        border-top-width: 0;
        border-left-width: 0;
        border-right-width: 0;
        margin-bottom: 4px;
        margin-top: 4px
    }

    .profil {
        display: flex;
        flex-direction: row;
        gap: 12px
    }

    .td-bawah,
    .td-center,
    .td-center-atas,
    .td-nocenter {
        padding: 8px;
        vertical-align: inherit;
        display: table-cell
    }

    .profil .foto-profil {
        width: 150px;
        height: 190px;
        border-radius: 8px;
        background: #cfcfcf
    }

    .profil .foto-profil img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        border-radius: 4px
    }

    .profil .tabel {
        color: #2b2b2b
    }

    table {
        line-height: 1.5;
        font-size: 12px
    }

    .title-kategori {
        font-size: 14px;
        font-weight: 400;
        margin-top: 10px;
        margin-bottom: 8px
    }

    .kotak-tabel,
    sub {
        border-radius: 4px;
        margin-top: 8px
    }

    .tabel-pelanggaran {
        border-radius: 4px;
        width: 100%;
        font-size: 11px;
        color: rgb(107 114 128 / 1);
        border-collapse: collapse;
        text-align: center
    }

    .head-pelanggaran {
        font-size: 12px;
        color: rgb(55 65 81 / 1);
        text-transform: uppercase;
        background: rgb(255 237 213 / 1)
    }

    .td-nocenter {
        text-align: left;
        font-weight: 700;
        text-align: -internal-center
    }

    .td-center-atas {
        font-weight: 700;
        text-align: -internal-center;
        white-space: nowrap
    }

    .tr-pelanggaran {
        background: rgb(255 247 237 / 1);
        border: 2px solid #e0e0e0;
        border-top-width: 0;
        border-left-width: 0;
        border-right-width: 0
    }

    .tr-pelanggarann {
        color: rgb(75 85 99 / 1);
        background: rgb(255 247 237 / 1);
        border: 2px solid #e0e0e0;
        font-size: 12px;
        font-weight: 700;
        border-top-width: 0;
        border-left-width: 0;
        border-right-width: 0
    }

    .td-bawah,
    .td-center {
        font-weight: 400
    }

    .td-center {
        text-align: center;
        text-align: -internal-center
    }

    .td-bawah {
        width: 100%;
        text-align: left;
        text-align: -internal-center
    }

    .alpha,
    .izin,
    .hadir {
        color: #fff;
        padding: 4px;
        border-radius: 3px;
        text-align: center;
        font-weight: 500;
        width: 80px;
        margin: 0 auto
    }

    .page-break {
        page-break-after: always
    }

    .hadir {
        background: #0d8e63
    }

    .alpha {
        background: #ae1010
    }

    .izin {
        background: #ecc94b
    }

    .td-centerr {
        padding: 8px
    }
</style>

<body>
    <div class="container">
        <h2 class="title">Laporan Kegiatan Wajib Mahasiswa</h2>
        <div class="kotak">
            <h4 class="title-profil">Profil Siswa</h4>
            <div class="garis"></div>
            <div class="profil">
                <div class="tabel">
                    <table>
                        <tbody>
                            <tr>
                                <td width="150">NIM</td>
                                <td>:</td>
                                <td>{{ $getPresensiUpacara[0]->user->nim }}</td>
                            </tr>
                            <tr>
                                <td width="150">Nama</td>
                                <td>:</td>
                                <td>{{ $getPresensiUpacara[0]->user->name }}</td>
                            </tr>
                            <tr>
                                <td width="150">Program Studi</td>
                                <td>:</td>
                                <td>{{ $getPresensiUpacara[0]->user->prodi->prodi }}</td>
                            </tr>
                            <tr>
                                <td width="150">Blok Ruangan</td>
                                <td>:</td>
                                <td>{{ $getPresensiUpacara[0]->user->blok->name }}</td>
                            </tr>
                            <tr>
                                <td width="150">Nomor Ruangan</td>
                                <td>:</td>
                                <td>{{ $getPresensiUpacara[0]->user->no_kamar }}</td>
                            </tr>
                            <tr>
                                <td width="150">Kelas</td>
                                <td>:</td>
                                <td>{{ $getPresensiUpacara[0]->user->kelas->nama_kelas }}</td>
                            </tr>
                            <tr>
                                <td width="150">Asal Daerah</td>
                                <td>:</td>
                                <td>{{ $getPresensiUpacara[0]->user->asal_daerah }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="kotak1">
            <h4 class="title-profil">Kehadiran Siswa</h4>
            <div class="garis"></div>
            <div class="title-kategori">Upacara</div>
            <div style="color: #808080; font-size: 11px;">Tanggal : {{ $dateRangeText }}</div>
            <div class="kotak-tabel">
                <table class="tabel-pelanggaran">
                    <thead class="head-pelanggaran">
                        <tr>
                            <td class="td-center-atas">NO</td>
                            <td class="td-center-atas">TANGGAL</td>
                            <td class="td-center-atas">JAM MULAI</td>
                            <td class="td-center-atas">JAM SELESAI</td>
                            <td class="td-center-atas">STATUS KEGIATAN</td>
                            <td class="td-center-atas">JAM KEHADIRAN</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($getPresensiUpacara as $presensi)
                            <tr class="tr-pelanggaran">
                                <td class="td-center">{{ $loop->iteration }}</td>
                                <td class="td-center">{{ $presensi->formatted_date }}</td>
                                <td class="td-center">{{ $presensi->formatted_mulai_acara }}</td>
                                <td class="td-center">{{ $presensi->formatted_selesai_acara }}</td>
                                <td class="td-centerr">
                                    @if ($presensi->status_kehadiran == 'Hadir')
                                        <div class="hadir">Hadir</div>
                                    @elseif ($presensi->status_kehadiran == 'Alpha')
                                        <div class="alpha">Alpha</div>
                                    @elseif ($presensi->status_kehadiran == 'Izin')
                                        <div class="izin">Izin</div>
                                    @endif
                                </td>
                                <td class="td-center">
                                    @isset($presensi->jam_kehadiran)
                                        {{ $presensi->formatted_jam_kehadiran }} 
                                    @endisset
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        <div class="page-break"></div>
        <div class="kotak1">
            <h4 class="title-profil">Jumlah Kehadiran</h4>
            <div class="garis"></div>
            <div class="kotak-tabel">
                <table class="tabel-pelanggaran">
                    <thead class="head-pelanggaran">
                        <tr>
                            <td class="td-nocenter">Jenis Kegiatan</td>
                            <td class="td-center-atas">HADIR</td>
                            <td class="td-center-atas">ALPHA</td>
                            <td class="td-center-atas">IZIN</td>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="tr-pelanggarann">
                            <td class="td-bawah">Upacara</td>
                            <td class="td-center">{{ $getPresensiUpacara[0]->total_hadir }}</td>
                            <td class="td-center">{{ $getPresensiUpacara[0]->total_alpha }}</td>
                            <td class="td-center">{{ $getPresensiUpacara[0]->total_izin }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
