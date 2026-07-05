<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Laporan Pelanggaran Mahasiswa</title>
</head>
<style>
.td-center-atas,.title{text-align:center}.kotak,.kotak1{background:#fff;border:2px solid #dcdcdc;border-radius:8px}*{margin:0;padding:0;box-sizing:border-box;font-family:sans-serif}.container{width:730px;height:auto;margin:28px auto}.kotak{margin:28px auto auto;padding:10px}.kotak1{margin:12px auto auto;padding:12px}.title-profil{line-height:18px}.garis{border:2px solid #dcdcdc;border-top-width:0;border-left-width:0;border-right-width:0;margin-bottom:4px;margin-top:4px}.profil{display:flex;flex-direction:row;gap:12px}.td-bawah,.td-center,.td-center-atas,.td-nocenter{padding:8px;vertical-align:inherit;display:table-cell}.profil .foto-profil{width:150px;height:190px;border-radius:8px;background:#cfcfcf}.profil .foto-profil img{width:100%;height:100%;object-fit:cover;border-radius:4px}.profil .tabel{color:#2b2b2b}table{line-height:1.5;font-size:12px}.title-kategori{font-size:14px;font-weight:400;margin-top:10px;margin-bottom:8px}.kotak-tabel,sub{border-radius:4px;margin-top:8px}.tabel-pelanggaran{border-radius:4px;width:100%;font-size:11px;color:rgb(107 114 128 / 1);border-collapse:collapse}.head-pelanggaran{font-size:12px;color:rgb(55 65 81 / 1);text-transform:uppercase;background:rgb(255 237 213 / 1)}.td-nocenter{text-align:left;font-weight:700;text-align:-internal-center}.td-center-atas{font-weight:700;text-align:-internal-center;white-space:nowrap}.tr-pelanggaran{background:rgb(255 247 237 / 1);border:2px solid #e0e0e0;border-top-width:0;border-left-width:0;border-right-width:0}.tr-pelanggarann{color:rgb(75 85 99 / 1);background:rgb(255 247 237 / 1);border:2px solid #e0e0e0;font-size:12px;font-weight:700;border-top-width:0;border-left-width:0;border-right-width:0}.td-bawah,.td-center{font-weight:400}.td-center{text-align:center;text-align:-internal-center}.td-bawah{width:100%;text-align:left;text-align:-internal-center}.page-break{page-break-after:always}
</style>
<body>
    <div class="container">
        <h2 class="title">Laporan Pelanggaran Mahasiswa</h2>
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
                                <td>{{ $data[0]['user']['nim'] }}</td>
                            </tr>
                            <tr>
                                <td width="150">Nama</td>
                                <td>:</td>
                                <td>{{ $data[0]['user']['name'] }}</td>
                            </tr>
                            <tr>
                                <td width="150">Program Studi</td>
                                <td>:</td>
                                <td>{{ $data[0]['user']['prodi']['prodi'] }}</td>
                            </tr>
                            <tr>
                                <td width="150">Blok Ruangan</td>
                                <td>:</td>
                                <td>{{ $data[0]['user']['blok']['name'] }}</td>
                            </tr>
                            <tr>
                                <td width="150">Nomor Ruangan</td>
                                <td>:</td>
                                <td>{{ $data[0]['user']['no_kamar'] }}</td>
                            </tr>
                            <tr>
                                <td width="150">Kelas</td>
                                <td>:</td>
                                <td>{{ $data[0]['user']['kelas']['nama_kelas'] }}</td>
                            </tr>
                            <tr>
                                <td width="150">Asal Daerah</td>
                                <td>:</td>
                                <td>{{ $data[0]['user']['asal_daerah'] }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="kotak1">
            <h4 class="title-profil">Pelanggaran</h4>
            <div class="garis"></div>
            @foreach ($groupedData as $azKey => $kategoriPelanggaran)
                @foreach ($kategoriPelanggaran as $kategori => $pelanggaran)
                    <div class="title-kategori">
                        {{ $azKey }}. {{ $kategori }}
                    </div>
                    <div class="kotak-tabel">
                        <table class="tabel-pelanggaran">
                            <thead class="head-pelanggaran">
                                <tr>
                                    <td class="td-nocenter">BENTUK PELANGGARAN</td>
                                    <td class="td-center-atas">TANGGAL</td>
                                    <td class="td-center-atas">POIN</td>
                                    <td class="td-center-atas">KATEGORI</td>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pelanggaran as $item)
                                    <tr class="tr-pelanggaran">
                                        <td class="td-bawah">{{ $item['jenis_pelanggaran'] }}</td>
                                        <td class="td-center">{{ $item['poin'] }}</td>
                                        <td class="td-center">12 Juni 2024</td>
                                        <td class="td-center">{{ $item['sub_kategori'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endforeach
            @endforeach
        </div>
        <div class="page-break"></div>
        <div class="kotak1">
            <h4 class="title-profil">Total Poin Siswa</h4>
            <div class="garis"></div>
            <div class="kotak-tabel">
                <table class="tabel-pelanggaran">
                    <thead class="head-pelanggaran">
                        <tr>
                            <td class="td-nocenter">KETEGORI</td>
                            <td class="td-center-atas">TOTAL POIN</td>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($groupedDataraport as $kategoriKey => $kategoriPelanggaran)
                            @if ($kategoriKey !== 'totalPoin')
                                <tr class="tr-pelanggaran">
                                    <td class="td-bawah">{{ $kategoriKey }}</td>
                                    <td class="td-center">{{ $kategoriPelanggaran['totalPoinKategori'] }}</td>
                                </tr>
                            @endif
                        @endforeach
                        <tr class="tr-pelanggarann">
                            <td class="td-bawah">Total Seluruh Kategori</td>
                            <td class="td-center">{{ $groupedDataraport['totalPoin'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>
