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
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Detail Absen Keluar</h1>
        <div class="text-gray-600 text-sm">Detail riwayat absen keluar mahasiswa Asrama Polbangtan-mlg</div>
        <div class="border-b border-gray-300 my-5"></div>
        <div class="w-full">
            <div class="bg-white rounded-lg p-3 border-2">
                <div class="flex justify-between items-center">
                    <div class="text-lg font-medium">Profil Siswa</div>
                    @if ($user->status == 'didalam')
                        <div class="text-sm font-medium px-2 md:px-3 py-1 text-center bg-green-500 rounded text-white">
                            Didalam
                        </div>
                    @endif
                    @if ($user->status == 'diluar')
                        <div class="text-sm font-medium px-2 md:px-3 py-1 text-center bg-red-600 rounded text-white">
                            Diluar
                        </div>
                    @endif
                    @if ($user->status == 'telat')
                        <div class="text-sm font-medium px-2 md:px-3 py-1 text-center bg-orange-600 rounded text-white">
                            Telat Masuk
                        </div>
                    @endif
                </div>
                <div class="border-b border-gray-300 my-1"></div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="w-[150px] h-[190px] rounded mx-auto sm:mx-0">
                        @if ($user->image)
                            <img src="{{ asset('storage/images/' . $user->image) }}"
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
                                    <td>{{ $user->nim }}</td>
                                </tr>
                                <tr>
                                    <td width="150">Nama</td>
                                    <td>:</td>
                                    <td>{{ $user->name }}</td>
                                </tr>
                                <tr>
                                    <td width="150">Program Studi</td>
                                    <td>:</td>
                                    <td>
                                        @if ($user->prodi_id == null)
                                            -
                                        @else
                                            {{ $user->prodi->prodi }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td width="150">Blok Ruangan</td>
                                    <td>:</td>
                                    <td>
                                        @if ($user->blok_ruangan_id == null)
                                            -
                                        @else
                                            {{ $user->blok->name }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td width="150">Nomor Ruangan</td>
                                    <td>:</td>
                                    <td>{{ $user->no_kamar }}</td>
                                </tr>
                                <tr>
                                    <td width="150">Kelas</td>
                                    <td>:</td>
                                    <td>
                                        @if ($user->kelas_id == null)
                                            -
                                        @else
                                            {{ $user->kelas->nama_kelas }}
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td width="150">Asal Daerah</td>
                                    <td>:</td>
                                    <td>{{ $user->asal_daerah }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-lg p-3 mt-5 border-2">
                <div class="text-lg font-medium">Data Absen Keluar</div>
                <div class="border-b border-gray-300 my-1"></div>
                <ol class="relative text-gray-500 mt-2">
                    <li class="mb-5">
                        @if ($dataPresensi->isNotEmpty())
                            <div class="relative overflow-x-auto border border-gray-200 rounded w-full">
                                <table class="w-full text-sm text-center">
                                    <thead class="text-gray-700 uppercase bg-orange-100 text-xs">
                                        <tr class="whitespace-nowrap">
                                            <th scope="col" class="px-3 py-2">
                                                NO
                                            </th>
                                            <th scope="col" class="px-3 py-2">
                                                Hari/Tanggal
                                            </th>
                                            <th scope="col" class="px-3 py-2">
                                                Jam Keluar
                                            </th>
                                            <th scope="col" class="px-3 py-2">
                                                Jam Masuk
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="tableUpacara">
                                        @include('admin.partials.data_absen_keluar_table', [
                                            'dataPresensi' => $dataPresensi,
                                        ])
                                    </tbody>
                                </table>
                                <div id="tableUpacaraNull"></div>
                        @endif
                        </form>
                        @if ($dataPresensi->isEmpty())
                            <div class="relative overflow-x-auto border border-gray-200 bg-orange-50 rounded w-full">
                                <table class="w-full text-sm text-center">
                                    <thead class="text-gray-700 uppercase bg-orange-100 text-xs">
                                        <tr class="whitespace-nowrap">
                                            <th scope="col" class="px-3 py-2">
                                                NO
                                            </th>
                                            <th scope="col" class="px-3 py-2">
                                                Hari/Tanggal
                                            </th>
                                            <th scope="col" class="px-3 py-2">
                                                Jam Mulai
                                            </th>
                                            <th scope="col" class="px-3 py-2">
                                                Jam Selesai
                                            </th>
                                            <th scope="col" class="px-3 py-2">
                                                Status Kegiatan
                                            </th>
                                            <th scope="col" class="px-3 py-2">
                                                Jam kehadiran
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                                <div class="text-center py-5 text-xs w-full">Riwayat belum tersedia.</div>
                            </div>
                        @endif
                    </li>
                </ol>
                <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                    aria-label="Table navigation">
                    <span class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">Showing
                        <span class="font-semibold text-gray-900">{{ $dataPresensi->firstItem() }}</span> to
                        <span class="font-semibold text-gray-900">{{ $dataPresensi->lastItem() }}</span> of
                        <span
                            class="font-semibold text-gray-900">{{ $dataPresensi->total() }}</span>
                    </span>
                    <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                        <li>
                            <a href="{{ $dataPresensi->previousPageUrl() }}"
                                class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                        </li>
                        @foreach ($dataPresensi->getUrlRange($dataPresensi->currentPage() - 1, $dataPresensi->currentPage() + 1) as $num => $url)
                            <li>
                                <a href="{{ $url }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $dataPresensi->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                            </li>
                        @endforeach
                        <li>
                            <a href="{{ $dataPresensi->nextPageUrl() }}"
                                class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                        </li>
                    </ul>
                </nav>
            </div>
            <div class="bg-white rounded-lg p-3 border-2 mt-3">
                <div class="text-lg font-medium">Jumlah Presensi</div>
                <div class="border-b border-gray-300 my-1"></div>
                <div class="relative overflow-x-auto rounded mt-2">
                    <table class="w-full text-sm text-left text-gray-500">
                        <thead class="text-xs text-gray-700 uppercase bg-orange-100">
                            <tr>
                                <th scope="col" class="px-3 py-2">
                                    JENIS PRESENSI
                                </th>
                                <th scope="col" class="px-3 py-2 text-center">
                                    Jumlah
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="bg-orange-50 border-b">
                                <td class="px-3 py-2 min-w-[580px]">
                                    Diluar Asrama
                                </td>
                                <td class="px-3 py-2 text-center">
                                    {{ $dataTotal['totalDiluar'] }}
                                </td>
                            </tr>
                            <tr class="bg-orange-50 border-b">
                                <td class="px-3 py-2 min-w-[580px]">
                                    Didalam Asrama
                                </td>
                                <td class="px-3 py-2 text-center">
                                    {{ $dataTotal['totalDidalam'] }}
                                </td>
                            </tr>
                            <tr class="bg-orange-50 border-b">
                                <td class="px-3 py-2 min-w-[580px]">
                                    Telat Masuk Asrama
                                </td>
                                <td class="px-3 py-2 text-center">
                                    {{ $dataTotal['totalTelat'] }}
                                </td>
                            </tr>
                            <tr class="bg-orange-50 border-t border-gray-800 text-gray-600 font-medium">
                                <td class="px-3 py-2 min-w-[580px]">
                                    Total Seluruh Jenis Presensi
                                </td>
                                <td class="px-3 py-2 text-center">
                                    {{ $dataTotal['totalPresensi'] }}
                                </td>
                            </tr>
                        </tbody>
                    </table>

                </div>
            </div>
            <button type="button" onclick="window.location.href='/data-absen-keluar'"
                class="mt-2 focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-2 focus:ring-red-300 font-medium rounded-lg text-sm px-2.5 py-1.5 me-2">Kembali</button>
        </div>
    </div>
@endsection
