@extends('layouts.main')
@section('container')
    <!-- start: Main -->
    <div class="px-3 py-6 md:p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
            <div class="bg-white rounded-md border-2 p-4 ">
                <div class="flex justify-between">
                    <div>
                        <div class="text-xl font-semibold mb-12">Pengguna Aktif</div>
                        <div class="text-sm font-medium text-green-700 flex items-center">
                            <span class="relative flex h-3 w-3 mr-2">
                                <span
                                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-500 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                            {{ $activeuser }} Pengguna
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-md border border-gray-100 p-4 shadow-md shadow-black/5">
                <div class="flex justify-between">
                    <div>
                        <div class="text-xl font-semibold mb-12">Total Pengguna</div>
                        <div class="text-sm font-medium text-amber-700 flex items-center">
                            <div class="w-3 h-3 rounded-full bg-amber-500 animate-pulse mr-2"></div>
                            {{ $totaluser }} Pengguna
                        </div>
                    </div>
                </div>
            </div>
            <div class="bg-white rounded-md border border-gray-100 p-4 shadow-md shadow-black/5">
                <div class="flex justify-between">
                    <div>
                        <div class="text-xl font-semibold mb-12">Piket Absen Keluar</div>
                        <div class="text-sm font-medium text-utama flex items-center m-1">
                            {{ $petugas1 }}
                        </div>
                        <div class="text-sm font-medium text-utama flex items-center m-1">
                            {{ $petugas2 }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="flex flex-col justify-between gap-5">
            <div class="bg-white border-2 p-4 rounded-md">
                <div class="flex justify-between mb-4 items-start">
                    <div class="font-medium">Status Presensi Keluar Asrama</div>
                </div>
                <div class="overflow-x-auto">
                    <!-- Alasan Presensi -->
                    <div class="flex flex-col md:flex-row justify-normal md:justify-between">
                        <!-- Icon -->
                        {{-- jika is_active sama dengan 1 dan null --}}
                        @if ($status->status == 'diluar')
                            <div class="flex items-center">
                                <i class="ri-information-line text-utama text-6xl mr-2"></i>
                                <!-- Keterangan Alasan -->
                                <div>
                                    <h3 class="text-lg font-semibold">Berada Diluar Asrama</h3>
                                </div>
                            </div>
                            <div class="text-sm mt-5 md:mt-0 text-gray-500">
                                Batas Waktu Keluar:<br>
                                Jam 06.00 WIB - Jam 22.00 WIB
                            </div>
                        @endif
                        @if ($status->status == 'didalam')
                            <div class="flex items-center">
                                <i class="ri-shield-check-line text-utama text-6xl mr-2"></i>
                                <!-- Keterangan Alasan -->
                                <div>
                                    <h3 class="text-lg font-semibold">Berada Diarea Asrama</h3>
                                </div>
                            </div>
                            <div class="text-sm mt-5 md:mt-0 text-gray-500">
                                Batas Waktu Keluar:<br>
                                Jam 06.00 WIB - Jam 22.00 WIB
                            </div>
                        @endif
                        @if ($status->status == 'telat')
                            <div class="flex items-center">
                                <i class="ri-information-line text-utama text-6xl mr-2"></i>
                                <!-- Keterangan Alasan -->
                                <div>
                                    <h3 class="text-lg font-semibold">Berada Diarea Asrama Dengan status Telat</h3>
                                </div>
                            </div>
                            <div class="text-sm mt-5 md:mt-0 text-gray-500">
                                Batas Waktu Keluar:<br>
                                Jam 06.00 WIB - Jam 22.00 WIB
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 mt-6">
            <div class="bg-white border-2 p-4 rounded-md lg:col-span-2">
                <div class="flex justify-between mb-4 items-start">
                    <div class="font-medium">Kalender Stastik</div>
                </div>
                <div class="overflow-x-auto">
                    <div id="calendar" class=" text-sm md:text-lg"></div>
                </div>
            </div>
            <div class="bg-white border-2 p-4 rounded-md h-[auto] lg:h-[76%]">
                <div class="flex justify-between items-start">
                    <div class="font-medium">Detail Rekapan Anda</div>
                </div>
                <div class="text-sm text-gray-800 font-medium underline mt-2">Absen Keluar Asrama</div>
                <ul class="mt-2">
                    <li class="text-sm border border-gray-300 relative mb-[-1px] p-2 rounded">
                        Diluar Asrama
                        <span
                            class="float-right bg-utama p-1 rounded text-xs text-white inline-block whitespace-nowrap align-middle leading-none">{{ $total_days_bulanan_diluar }}
                            Hari</span>
                    </li>
                    <li class="text-sm border border-gray-300 relative mb-[-1px] p-2 rounded">
                        Telat Masuk Asrama
                        <span
                            class="float-right bg-utama text-xs p-1 rounded text-white inline-block whitespace-nowrap align-middle leading-none">{{ $total_days_bulanan_telat }}
                            Hari</span>
                    </li>
                </ul>
                <div class="text-sm text-gray-800 underline mt-3">Pelanggaran Anda</div>
                <ul class="mt-2">
                    <li class="text-sm border border-gray-300 relative mb-[-1px] p-2 rounded">
                        Total Poin
                        @if ($total_point == 0)
                            <span class="float-right bg-utama p-1 rounded text-xs text-white inline-block whitespace-nowrap align-middle leading-none"> {{ $total_point }}</span>
                        @elseif ($total_point <= 50)
                            <span class="float-right bg-yellow-500 p-1 rounded text-xs text-white inline-block whitespace-nowrap align-middle leading-none"> {{ $total_point }}</span>
                        @elseif ($total_point >= 50 && $total_point <= 80)
                            <span class="float-right bg-orange-500 p-1 rounded text-xs text-white inline-block whitespace-nowrap align-middle leading-none"> {{ $total_point }}</span>
                        @elseif ($total_point >= 100)
                            <span class="float-right bg-red-500 p-1 rounded text-xs text-white inline-block whitespace-nowrap align-middle leading-none"> {{ $total_point }}</span>
                        @endif
                    </li>
                </ul>
                <div class="text-sm text-gray-800 underline mt-3">Absen Kegiatan Wajib</div>
                <ul class="mt-2">
                    <li class="text-sm border border-gray-300 relative mb-[-1px] p-2 rounded justify-between flex">
                        Upacara
                        <div class="flex gap-1">
                            <span class="float-right bg-green-500 p-1 rounded text-xs text-white inline-block whitespace-nowrap align-middle leading-none">{{ $rekapKegiatan['UPACARA']['Hadir'] }}</span>
                            <span class="float-right bg-red-500 p-1 rounded text-xs text-white inline-block whitespace-nowrap align-middle leading-none">{{ $rekapKegiatan['UPACARA']['Alpha'] }}</span>
                        </div>
                    </li>
                    <li class="text-sm border border-gray-300 relative mb-[-1px] p-2 rounded justify-between flex">
                        Apel
                        <div class="flex gap-1">
                            <span class="float-right bg-green-500 p-1 rounded text-xs text-white inline-block whitespace-nowrap align-middle leading-none">{{ $rekapKegiatan['APEL']['Hadir'] }}</span>
                            <span class="float-right bg-red-500 p-1 rounded text-xs text-white inline-block whitespace-nowrap align-middle leading-none">{{ $rekapKegiatan['APEL']['Alpha'] }}</span>
                        </div>
                    </li>
                    <li class="text-sm border border-gray-300 relative mb-[-1px] p-2 rounded justify-between flex">
                        Senam
                        <div class="flex gap-1">
                            <span class="float-right bg-green-500 p-1 rounded text-xs text-white inline-block whitespace-nowrap align-middle leading-none">{{ $rekapKegiatan['SENAM']['Hadir'] }}</span>
                            <span class="float-right bg-red-500 p-1 rounded text-xs text-white inline-block whitespace-nowrap align-middle leading-none">{{ $rekapKegiatan['SENAM']['Alpha'] }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    <!-- end: Main -->
    <script src="{{ asset('js/kalendar.js') }}"></script>
    <script src="{{ asset('js/library/index.global.min.js') }}" type="text/javascript"></script>
@endsection
