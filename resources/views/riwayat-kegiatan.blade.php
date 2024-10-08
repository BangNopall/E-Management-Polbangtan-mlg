@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Riwayat Kegiatan Wajib</h1>
        <div class="text-gray-600 text-sm">Daftar absen kegiatan wajib mahasiswa Polbangtan-mlg</div>
        <div class="border-b border-gray-300 my-5"></div>
        {{-- alert --}}
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
        <div class="bg-white rounded-lg p-3 mt-5 border-2">
            <ol class="relative text-gray-500 border-s border-gray-200 ml-3 my-5">
                <li class="mb-8 ms-6">
                    <div class="flex flex-col gap-3 lg:gap-5">
                        <div class="flex flex-col justify-center items-start w-[140px]">
                            <i
                                class="ri-flag-line absolute flex items-center justify-center w-8 h-8 bg-red-500 rounded-full -start-4 ring-4 ring-red-200 text-white"></i>
                            <div class="font-medium text-red-400">Upacara</div>
                        </div>
                        @if ($allDataKegiatan['dataKegiatanUpacara']->isNotEmpty())
                            <div class="relative overflow-x-auto border-2 border-gray-200 rounded-lg w-full">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr class="">
                                            <th scope="col" class="px-3 py-3">
                                                NO
                                            </th>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Hari/Tanggal
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam Mulai
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam Selesai
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Status Kegiatan
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Status Presensi
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam kehadiran
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($allDataKegiatan['dataKegiatanUpacara'] as $dataKegiatanUpacara)
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-3 py-4">
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $dataKegiatanUpacara->formatted_date }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $dataKegiatanUpacara->jadwalKegiatanAsrama->mulai_acara }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $dataKegiatanUpacara->jadwalKegiatanAsrama->selesai_acara }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $dataKegiatanUpacara->status_acara }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if ($dataKegiatanUpacara->status_acara == 'Upcoming Event')
                                                        Presensi Belum Dibuka
                                                    @elseif ($dataKegiatanUpacara->status_acara == 'Acara Selesai' && $dataKegiatanUpacara->status_kehadiran == 'Hadir')
                                                        {{ $dataKegiatanUpacara->status_kehadiran }}
                                                    @elseif ($dataKegiatanUpacara->status_acara == 'Acara Selesai' && $dataKegiatanUpacara->status_kehadiran == 'Alpha')
                                                        {{ $dataKegiatanUpacara->status_kehadiran }}
                                                    @elseif ($dataKegiatanUpacara->status_acara == 'Sedang Berlangsung' && $dataKegiatanUpacara->status_kehadiran == 'Hadir')
                                                        {{ $dataKegiatanUpacara->status_kehadiran }}
                                                    @elseif ($dataKegiatanUpacara->status_acara == 'Sedang Berlangsung' && $dataKegiatanUpacara->status_kehadiran == 'Alpha')
                                                        Belum Presensi
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if ($dataKegiatanUpacara->status_acara == 'Upcoming Event')
                                                        Presensi Belum Dibuka
                                                    @elseif ($dataKegiatanUpacara->status_acara == 'Acara Selesai' && $dataKegiatanUpacara->status_kehadiran == 'Hadir')
                                                        {{ $dataKegiatanUpacara->jam_kehadiran }}
                                                    @elseif ($dataKegiatanUpacara->status_acara == 'Acara Selesai' && $dataKegiatanUpacara->status_kehadiran == 'Alpha')
                                                        {{ $dataKegiatanUpacara->status_kehadiran }}
                                                    @elseif ($dataKegiatanUpacara->status_acara == 'Sedang Berlangsung' && $dataKegiatanUpacara->status_kehadiran == 'Hadir')
                                                        {{ $dataKegiatanUpacara->jam_kehadiran }}
                                                    @elseif ($dataKegiatanUpacara->status_acara == 'Sedang Berlangsung' && $dataKegiatanUpacara->status_kehadiran == 'Alpha')
                                                        {{ $dataKegiatanUpacara->status_kehadiran }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif ($allDataKegiatan['dataKegiatanUpacara']->isEmpty())
                            <div class="relative overflow-x-auto border-2 border-gray-200 rounded-lg w-full">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr class="">
                                            <th scope="col" class="px-3 py-3">
                                                NO
                                            </th>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Hari/Tanggal
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam Mulai
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam Selesai
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Status Kegiatan
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Status Presensi
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam kehadiran
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                                <div class="text-center my-5 text-xs w-full">Riwayat belum tersedia.</div>
                            </div>
                        @endif
                    </div>
                </li>
                <li class="mb-8 ms-6">
                    <div class="flex flex-col gap-3 lg:gap-5">
                        <div class="flex flex-col justify-center items-start w-[140px]">
                            <i
                                class="ri-team-line absolute flex items-center justify-center text-white w-8 h-8 bg-yellow-500 rounded-full -start-4 ring-2 ring-yellow-200"></i>
                            <h3 class="font-medium text-yellow-500">Apel</h3>
                        </div>
                        @if ($allDataKegiatan['dataKegiatanApel']->isNotEmpty())
                            <div class="relative overflow-x-auto border-2 border-gray-200 rounded-lg w-full">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr class="">
                                            <th scope="col" class="px-3 py-3">
                                                NO
                                            </th>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Hari/Tanggal
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam Mulai
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam Selesai
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Status Kegiatan
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Status Presensi
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam kehadiran
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($allDataKegiatan['dataKegiatanApel'] as $dataKegiatanApel)
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-3 py-4">
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $dataKegiatanApel->formatted_date }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $dataKegiatanApel->jadwalKegiatanAsrama->mulai_acara }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $dataKegiatanApel->jadwalKegiatanAsrama->selesai_acara }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $dataKegiatanApel->status_acara }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if ($dataKegiatanApel->status_acara == 'Upcoming Event')
                                                        Presensi Belum Dibuka
                                                    @elseif ($dataKegiatanApel->status_acara == 'Acara Selesai' && $dataKegiatanApel->status_kehadiran == 'Hadir')
                                                        {{ $dataKegiatanApel->status_kehadiran }}
                                                    @elseif ($dataKegiatanApel->status_acara == 'Acara Selesai' && $dataKegiatanApel->status_kehadiran == 'Alpha')
                                                        {{ $dataKegiatanApel->status_kehadiran }}
                                                    @elseif ($dataKegiatanApel->status_acara == 'Sedang Berlangsung' && $dataKegiatanApel->status_kehadiran == 'Hadir')
                                                        {{ $dataKegiatanApel->status_kehadiran }}
                                                    @elseif ($dataKegiatanApel->status_acara == 'Sedang Berlangsung' && $dataKegiatanApel->status_kehadiran == 'Alpha')
                                                        Belum Presensi
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if ($dataKegiatanApel->status_acara == 'Upcoming Event')
                                                        Presensi Belum Dibuka
                                                    @elseif ($dataKegiatanApel->status_acara == 'Acara Selesai' && $dataKegiatanApel->status_kehadiran == 'Hadir')
                                                        {{ $dataKegiatanApel->jam_kehadiran }}
                                                    @elseif ($dataKegiatanApel->status_acara == 'Acara Selesai' && $dataKegiatanApel->status_kehadiran == 'Alpha')
                                                        {{ $dataKegiatanApel->status_kehadiran }}
                                                    @elseif ($dataKegiatanApel->status_acara == 'Sedang Berlangsung' && $dataKegiatanApel->status_kehadiran == 'Hadir')
                                                        {{ $dataKegiatanApel->jam_kehadiran }}
                                                    @elseif ($dataKegiatanApel->status_acara == 'Sedang Berlangsung' && $dataKegiatanApel->status_kehadiran == 'Alpha')
                                                        {{ $dataKegiatanApel->status_kehadiran }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif ($allDataKegiatan['dataKegiatanApel']->isEmpty())
                            <div class="relative overflow-x-auto border-2 border-gray-200 rounded-lg w-full">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr class="">
                                            <th scope="col" class="px-3 py-3">
                                                NO
                                            </th>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Hari/Tanggal
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam Mulai
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam Selesai
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Status Kegiatan
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Status Presensi
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam kehadiran
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                                <div class="text-center my-5 text-xs w-full">Riwayat belum tersedia.</div>
                            </div>
                        @endif
                    </div>
                </li>
                <li class="ms-6">
                    <div class="flex flex-col gap-3 lg:gap-5">
                        <div class="flex flex-col justify-center items-start w-[140px]">
                            <i
                                class="ri-user-smile-fill absolute flex items-center justify-center text-white w-8 h-8 bg-green-500 rounded-full -start-4 ring-2 ring-green-200"></i>
                            <h3 class="font-medium text-green-400">Senam</h3>
                        </div>
                        @if ($allDataKegiatan['dataKegiatanSenam']->isNotEmpty())
                            <div class="relative overflow-x-auto border-2 border-gray-200 rounded-lg w-full">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr class="">
                                            <th scope="col" class="px-3 py-3">
                                                NO
                                            </th>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Hari/Tanggal
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam Mulai
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam Selesai
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Status Kegiatan
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Status Presensi
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam kehadiran
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($allDataKegiatan['dataKegiatanSenam'] as $dataKegiatanSenam)
                                            <tr class="bg-white border-b hover:bg-gray-50">
                                                <td class="px-3 py-4">
                                                    {{ $loop->iteration }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $dataKegiatanSenam->formatted_date }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $dataKegiatanSenam->jadwalKegiatanAsrama->mulai_acara }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    {{ $dataKegiatanSenam->jadwalKegiatanAsrama->selesai_acara }}
                                                </td>
                                                <td class="px-6 py-4">
                                                    {{ $dataKegiatanSenam->status_acara }}
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if ($dataKegiatanSenam->status_acara == 'Upcoming Event')
                                                        Presensi Belum Dibuka
                                                    @elseif ($dataKegiatanSenam->status_acara == 'Acara Selesai' && $dataKegiatanSenam->status_kehadiran == 'Hadir')
                                                        {{ $dataKegiatanSenam->status_kehadiran }}
                                                    @elseif ($dataKegiatanSenam->status_acara == 'Acara Selesai' && $dataKegiatanSenam->status_kehadiran == 'Alpha')
                                                        {{ $dataKegiatanSenam->status_kehadiran }}
                                                    @elseif ($dataKegiatanSenam->status_acara == 'Sedang Berlangsung' && $dataKegiatanSenam->status_kehadiran == 'Hadir')
                                                        {{ $dataKegiatanSenam->status_kehadiran }}
                                                    @elseif ($dataKegiatanSenam->status_acara == 'Sedang Berlangsung' && $dataKegiatanSenam->status_kehadiran == 'Alpha')
                                                        Belum Presensi
                                                    @endif
                                                </td>
                                                <td class="px-6 py-4 whitespace-nowrap">
                                                    @if ($dataKegiatanSenam->status_acara == 'Upcoming Event')
                                                        Presensi Belum Dibuka
                                                    @elseif ($dataKegiatanSenam->status_acara == 'Acara Selesai' && $dataKegiatanSenam->status_kehadiran == 'Hadir')
                                                        {{ $dataKegiatanSenam->jam_kehadiran }}
                                                    @elseif ($dataKegiatanSenam->status_acara == 'Acara Selesai' && $dataKegiatanSenam->status_kehadiran == 'Alpha')
                                                        {{ $dataKegiatanSenam->status_kehadiran }}
                                                    @elseif ($dataKegiatanSenam->status_acara == 'Sedang Berlangsung' && $dataKegiatanSenam->status_kehadiran == 'Hadir')
                                                        {{ $dataKegiatanSenam->jam_kehadiran }}
                                                    @elseif ($dataKegiatanSenam->status_acara == 'Sedang Berlangsung' && $dataKegiatanSenam->status_kehadiran == 'Alpha')
                                                        {{ $dataKegiatanSenam->status_kehadiran }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif ($allDataKegiatan['dataKegiatanSenam']->isEmpty())
                            <div class="relative overflow-x-auto border-2 border-gray-200 rounded-lg w-full">
                                <table class="w-full text-sm text-left">
                                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                                        <tr class="">
                                            <th scope="col" class="px-3 py-3">
                                                NO
                                            </th>
                                            <th scope="col" class="px-6 py-3 whitespace-nowrap">
                                                Hari/Tanggal
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam Mulai
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam Selesai
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Status Kegiatan
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Status Presensi
                                            </th>
                                            <th scope="col" class="px-6 py-3">
                                                Jam kehadiran
                                            </th>
                                        </tr>
                                    </thead>
                                </table>
                                <div class="text-center my-5 text-xs w-full">Riwayat belum tersedia.</div>
                            </div>
                        @endif
                    </div>
                </li>
            </ol>

            @if ($keyPagination == 'dataKegiatanUpacara')
                <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                    aria-label="Table navigation">
                    <span class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                        Showing <span
                            class="font-semibold text-gray-900">{{ $allDataKegiatan['dataKegiatanUpacara']->firstItem() }}</span>
                        to <span
                            class="font-semibold text-gray-900">{{ $allDataKegiatan['dataKegiatanUpacara']->lastItem() }}</span>
                        of <span
                            class="font-semibold text-gray-900">{{ $allDataKegiatan['dataKegiatanUpacara']->total() + $allDataKegiatan['dataKegiatanApel']->total() + $allDataKegiatan['dataKegiatanSenam']->total() }}</span>
                    </span>
                    <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                        <li>
                            @if ($allDataKegiatan['dataKegiatanUpacara']->onFirstPage())
                                <span
                                    class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg">Previous</span>
                            @else
                                <a href="{{ $allDataKegiatan['dataKegiatanUpacara']->previousPageUrl() }}"
                                    class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                            @endif
                        </li>
                        @foreach ($allDataKegiatan['dataKegiatanUpacara']->getUrlRange($allDataKegiatan['dataKegiatanUpacara']->currentPage() - 1, $allDataKegiatan['dataKegiatanUpacara']->currentPage() + 1) as $num => $url)
                            <li>
                                <a href="{{ $url }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $allDataKegiatan['dataKegiatanUpacara']->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                            </li>
                        @endforeach
                        <li>
                            @if ($allDataKegiatan['dataKegiatanUpacara']->hasMorePages())
                                <a href="{{ $allDataKegiatan['dataKegiatanUpacara']->nextPageUrl() }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                            @else
                                <span
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg">Next</span>
                            @endif
                        </li>
                    </ul>
                </nav>
            @elseif ($keyPagination == 'dataKegiatanApel')
                <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                    aria-label="Table navigation">
                    <span class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                        Showing <span
                            class="font-semibold text-gray-900">{{ $allDataKegiatan['dataKegiatanApel']->firstItem() }}</span>
                        to <span
                            class="font-semibold text-gray-900">{{ $allDataKegiatan['dataKegiatanApel']->lastItem() }}</span>
                        of <span
                            class="font-semibold text-gray-900">{{ $allDataKegiatan['dataKegiatanUpacara']->total() + $allDataKegiatan['dataKegiatanApel']->total() + $allDataKegiatan['dataKegiatanSenam']->total() }}</span>
                    </span>
                    <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                        <li>
                            @if ($allDataKegiatan['dataKegiatanApel']->onFirstPage())
                                <span
                                    class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg">Previous</span>
                            @else
                                <a href="{{ $allDataKegiatan['dataKegiatanApel']->previousPageUrl() }}"
                                    class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                            @endif
                        </li>
                        @foreach ($allDataKegiatan['dataKegiatanApel']->getUrlRange($allDataKegiatan['dataKegiatanApel']->currentPage() - 1, $allDataKegiatan['dataKegiatanApel']->currentPage() + 1) as $num => $url)
                            <li>
                                <a href="{{ $url }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $allDataKegiatan['dataKegiatanApel']->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                            </li>
                        @endforeach
                        <li>
                            @if ($allDataKegiatan['dataKegiatanApel']->hasMorePages())
                                <a href="{{ $allDataKegiatan['dataKegiatanApel']->nextPageUrl() }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                            @else
                                <span
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg">Next</span>
                            @endif
                        </li>
                    </ul>
                </nav>
            @elseif ($keyPagination == 'dataKegiatanSenam')
                <nav class="flex items-center flex-column flex-wrap md:flex-row justify-between pt-4"
                    aria-label="Table navigation">
                    <span class="text-sm font-normal text-gray-500 mb-4 md:mb-0 block w-full md:inline md:w-auto">
                        Showing <span
                            class="font-semibold text-gray-900">{{ $allDataKegiatan['dataKegiatanSenam']->firstItem() }}</span>
                        to <span
                            class="font-semibold text-gray-900">{{ $allDataKegiatan['dataKegiatanSenam']->lastItem() }}</span>
                        of <span
                            class="font-semibold text-gray-900">{{ $allDataKegiatan['dataKegiatanUpacara']->total() + $allDataKegiatan['dataKegiatanApel']->total() + $allDataKegiatan['dataKegiatanSenam']->total() }}</span>
                    </span>
                    <ul class="inline-flex -space-x-px rtl:space-x-reverse text-sm h-8">
                        <li>
                            @if ($allDataKegiatan['dataKegiatanSenam']->onFirstPage())
                                <span
                                    class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg">Previous</span>
                            @else
                                <a href="{{ $allDataKegiatan['dataKegiatanSenam']->previousPageUrl() }}"
                                    class="flex items-center justify-center px-3 h-8 ms-0 leading-tight text-gray-500 bg-white border border-gray-300 rounded-s-lg hover:bg-gray-100 hover:text-gray-700">Previous</a>
                            @endif
                        </li>
                        @foreach ($allDataKegiatan['dataKegiatanSenam']->getUrlRange($allDataKegiatan['dataKegiatanSenam']->currentPage() - 1, $allDataKegiatan['dataKegiatanSenam']->currentPage() + 1) as $num => $url)
                            <li>
                                <a href="{{ $url }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 {{ $num == $allDataKegiatan['dataKegiatanSenam']->currentPage() ? 'active' : '' }} hover:bg-gray-100 hover:text-gray-700">{{ $num }}</a>
                            </li>
                        @endforeach
                        <li>
                            @if ($allDataKegiatan['dataKegiatanSenam']->hasMorePages())
                                <a href="{{ $allDataKegiatan['dataKegiatanSenam']->nextPageUrl() }}"
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg hover:bg-gray-100 hover:text-gray-700">Next</a>
                            @else
                                <span
                                    class="flex items-center justify-center px-3 h-8 leading-tight text-gray-500 bg-white border border-gray-300 rounded-e-lg">Next</span>
                            @endif
                        </li>
                    </ul>
                </nav>
            @endif
        </div>
    </div>
@endsection
