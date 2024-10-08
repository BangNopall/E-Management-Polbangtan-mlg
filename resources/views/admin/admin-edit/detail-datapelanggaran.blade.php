@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        @if (session()->has('success'))
            <div id="alert-3" class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-200" role="alert">
                <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Info</span>
                <div class="ml-3 text-sm font-medium">
                    {{ session('success') }}
                </div>
                <button type="button"
                    class="ml-auto -mx-1.5 -my-1.5 bg-green-200 text-green-500 rounded-lg focus:ring-2 focus:ring-green-400 p-1.5 hover:bg-green-200 inline-flex items-center justify-center h-8 w-8"
                    data-dismiss-target="#alert-3" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>
        @endif
        @if (session()->has('error'))
            <div id="alert-2" class="flex items-center p-4 mb-4 text-red-800 rounded-lg bg-red-200" role="alert">
                <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="currentColor"
                    viewBox="0 0 20 20">
                    <path
                        d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                </svg>
                <span class="sr-only">Info</span>
                <div class="ml-3 text-sm font-medium">
                    {{ session('error') }}
                </div>
                <button type="button"
                    class="ml-auto -mx-1.5 -my-1.5 bg-red-200 text-red-500 rounded-lg focus:ring-2 focus:ring-red-400 p-1.5 hover:bg-red-200 inline-flex items-center justify-center h-8 w-8"
                    data-dismiss-target="#alert-2" aria-label="Close">
                    <span class="sr-only">Close</span>
                    <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none"
                        viewBox="0 0 14 14">
                        <path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6" />
                    </svg>
                </button>
            </div>
        @endif
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Detail Laporan</h1>
        <div class="text-gray-600 text-sm">Detail riwayat laporan pelanggaran siswa Asrama Polbangtan-mlg</div>
        <div class="border-b border-gray-300 my-5"></div>
        <div class="w-full">
            <div class="bg-white rounded-lg p-3 border-2">
                <div class="text-lg font-medium">Profil Siswa</div>
                <div class="border-b border-gray-300 my-1"></div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="w-[150px] h-[190px] rounded mx-auto sm:mx-0">
                        @if ($data[0]['user']['image'])
                            <img src="{{ asset('storage/images/' . $data[0]['user']['image']) }}"
                                class="h-full w-full object-cover rounded" id="fotoProfil" alt="Foto Profil">
                        @else
                            <img src="https://placehold.co/8000x8000" class="h-full w-full object-cover rounded"
                                id="fotoProfil" alt="Foto Profil">
                        @endif
                    </div>
                    <div class="text-gray-800">
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
            <div class="bg-white rounded-lg p-3 border-2 mt-3">
                <div class="text-lg font-medium">Pelanggaran</div>
                <div class="border-b border-gray-300 my-1"></div>
                @foreach ($groupedData as $azKey => $kategoriPelanggaran)
                    @foreach ($kategoriPelanggaran as $kategori => $pelanggaran)
                        <div class="text-gray-900 text-sm font-medium mt-2">{{ $azKey }}. {{ $kategori }}</div>
                        <div class="relative overflow-x-auto rounded mt-2">
                            <table class="w-full text-sm text-left text-gray-500">
                                <thead class="text-xs text-gray-700 uppercase bg-orange-100">
                                    <tr>
                                        <th scope="col" class="px-3 py-2">
                                            BENTUK PELANGGARAN
                                        </th>
                                        <th scope="col" class="px-3 py-2 text-center">
                                            TANGGAL
                                        </th>
                                        <th scope="col" class="px-3 py-2 text-center">
                                            POIN
                                        </th>
                                        <th scope="col" class="px-3 py-2 text-center">
                                            KATEGORI
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($pelanggaran as $detailPelanggaran)
                                        <tr class="bg-orange-50 border-b">
                                            <td class="px-3 py-2 w-[900px] min-w-[580px]">
                                                {{ $detailPelanggaran['jenis_pelanggaran'] }}
                                            </td>
                                            <td class="px-3 py-2 text-center whitespace-nowrap">
                                                12 Juni 2021
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                {{ $detailPelanggaran['poin'] }}
                                            </td>
                                            <td class="px-3 py-2 text-center">
                                                {{ $detailPelanggaran['sub_kategori'] }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endforeach
                @endforeach
            </div>
            <div class="bg-white rounded-lg p-3 border-2 mt-3">
                <div class="text-lg font-medium">Total Poin Siswa</div>
                <div class="border-b border-gray-300 my-1"></div>
                <div class="relative overflow-x-auto rounded mt-2">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-orange-100">
                            <tr>
                                <th scope="col" class="px-3 py-2">
                                    KATEGORI
                                </th>
                                <th scope="col" class="px-3 py-2 text-center">
                                    TOTAL POIN
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($groupedDataraport as $kategoriKey => $kategoriPelanggaran)
                                @if ($kategoriKey !== 'totalPoin')
                                    <tr class="bg-orange-50 border-b">
                                        <td class="px-3 py-2 min-w-[580px]">
                                            {{ $kategoriKey }}
                                        </td>
                                        <td class="px-3 py-2 text-center">
                                            {{ $kategoriPelanggaran['totalPoinKategori'] }}
                                        </td>
                                    </tr>
                                @endif
                            @endforeach
                            <tr class="bg-orange-50 border-t border-gray-800 text-gray-600 font-medium">
                                <td class="px-3 py-2 min-w-[580px]">
                                    Total Seluruh Katagori
                                </td>
                                <td class="px-3 py-2 text-center">
                                    {{ $groupedDataraport['totalPoin'] }}
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <button type="button" onclick="window.location.href='/data-pelanggaran'"
                class="mt-2 focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-2 focus:ring-red-300 font-medium rounded-lg text-sm px-2.5 py-1.5 me-2">Kembali</button>
        </div>
    </div>
@endsection
