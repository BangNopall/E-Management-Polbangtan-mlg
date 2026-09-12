@extends('layouts.main')
@section('container')
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
    
    <div class="px-3 py-6 mt-5 md:p-6 md:pt-7">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 md:gap-12 mb-6">
            <div class="bg-white rounded-sm border-2 p-3 shadow-black/5">
                <div class="flex justify-between gap-2">
                    <div
                        class="bg-utama min-w-[60px] md:min-w-[70px] h-[65px] md:h-[75px] inline-flex items-center justify-center rounded shadow-xl -mt-0 md:-mt-8">
                        <i class="ri-user-2-fill text-white text-2xl md:text-3xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 text-right">Jumlah Mahasiswa</div>
                        <div class="text-xl font-medium text-right text-gray-700">{{ $userCount }}</div>
                    </div>
                </div>
                <div class="border-b border-gray-300 mt-4 md:mt-7 mb-2"></div>
                <div class="text-sm text-gray-500">
                    <i class="ri-check-line mr-1"></i> Terdaftar
                </div>
            </div>
            <div class="bg-white rounded-sm border-2 p-3 shadow-black/5">
                <div class="flex justify-between gap-2">
                    <div
                        class="bg-utama min-w-[60px] md:min-w-[70px] h-[65px] md:h-[75px] inline-flex items-center justify-center rounded shadow-xl -mt-0 md:-mt-8">
                        <i class="ri-settings-5-line text-white text-2xl md:text-3xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 text-right">Jumlah Petugas</div>
                        <div class="text-xl font-medium text-right text-gray-700">{{ $jumlahPetugas }}</div>
                    </div>
                </div>
                <div class="border-b border-gray-300 mt-4 md:mt-7 mb-2"></div>
                <div class="text-sm text-gray-500">
                    <i class="ri-check-line mr-1"></i> Terdaftar
                </div>
            </div>
            <div class="bg-white rounded-sm border-2 p-3 shadow-black/5">
                <div class="flex justify-between gap-2">
                    <div
                        class="bg-utama min-w-[60px] md:min-w-[70px] h-[65px] md:h-[75px] inline-flex items-center justify-center rounded shadow-xl -mt-0 md:-mt-8">
                        <i class="ri-star-fill text-white text-2xl md:text-3xl"></i>
                    </div>
                    <div>
                        <div class="text-sm text-gray-500 text-right">Jumlah Ruangan</div>
                        <div class="text-xl font-medium text-right text-gray-700">{{ $ruanganTerisi }}</div>
                    </div>
                </div>
                <div class="border-b border-gray-300 mt-4 md:mt-7 mb-2"></div>
                <div class="text-sm text-gray-500">
                    <i class="ri-home-4-line mr-1"></i> Asrama Polbangtan Malang
                </div>
            </div>
        </div>

        {{-- Row Kartu Ringkasan Modul Baru (Perizinan & UKM) --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Izin Berjalan</span>
                    <span class="p-1.5 bg-sky-100 text-sky-700 rounded-md text-base"><i class="ri-flight-takeoff-line"></i></span>
                </div>
                <div class="mt-2 text-2xl font-bold text-sky-800">{{ $izinBerjalanCount ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">Mahasiswa sedang di luar</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Izin Terlambat</span>
                    <span class="p-1.5 bg-rose-100 text-rose-700 rounded-md text-base"><i class="ri-time-line"></i></span>
                </div>
                <div class="mt-2 text-2xl font-bold text-rose-800">{{ $izinTerlambatCount ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">Lewat batas waktu kembali</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Menunggu Persetujuan</span>
                    <span class="p-1.5 bg-amber-100 text-amber-700 rounded-md text-base"><i class="ri-inbox-archive-line"></i></span>
                </div>
                <div class="mt-2 text-2xl font-bold text-amber-800">{{ $izinPendingCount ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">Antrean verifikasi izin</div>
            </div>
            <div class="bg-white border border-gray-200 rounded-lg p-4 shadow-2xs">
                <div class="flex items-center justify-between">
                    <span class="text-xs font-semibold text-gray-500 uppercase">Jadwal UKM Hari Ini</span>
                    <span class="p-1.5 bg-teal-100 text-teal-700 rounded-md text-base"><i class="ri-team-line"></i></span>
                </div>
                <div class="mt-2 text-2xl font-bold text-teal-800">{{ $ukmJadwalHariIniCount ?? 0 }}</div>
                <div class="text-xs text-gray-500 mt-1">Kegiatan klub terverifikasi</div>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-6 mb-6 mt-5 md:mt-8">
            <div class="w-full">
                <div class="bg-white border-2 shadow-black/5 p-3 rounded-md lg:col-span-2 mb-6">
                    <div class="bg-utama rounded p-3">
                        <div class="text-white font-semibold text-md">Status Kehadiran Mahasiswa Hari Ini</div>
                        <div class="text-gray-300 text-sm">{{ $formattedDate }}</div>
                    </div>
                    <div class="my-3 border border-gray-200 rounded-lg divide-y divide-gray-100">
                        <!-- Di Dalam Asrama -->
                        <div class="flex justify-between items-center p-3 hover:bg-gray-50">
                            <div class="flex items-center">
                                <i class="ri-home-wifi-line text-emerald-600 text-2xl mr-3"></i>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-800">Di Dalam Asrama</h4>
                                    <span class="text-xs text-gray-500">Mahasiswa berada di lingkungan asrama</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-bold bg-emerald-100 text-emerald-800 rounded-full">{{ $userStatusDidalam ?? 0 }} Mhs</span>
                        </div>
                        <!-- Keluar Asrama Reguler -->
                        <div class="flex justify-between items-center p-3 hover:bg-gray-50">
                            <div class="flex items-center">
                                <i class="ri-walk-line text-amber-600 text-2xl mr-3"></i>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-800">Keluar Asrama (Reguler / Harian)</h4>
                                    <span class="text-xs text-gray-500">Keluar tanpa surat izin (wajib kembali &lt; 22:00)</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-bold bg-amber-100 text-amber-800 rounded-full">{{ $userStatus }} Mhs</span>
                        </div>
                        <!-- Izin Keluar Resmi -->
                        <div class="flex justify-between items-center p-3 hover:bg-gray-50">
                            <div class="flex items-center">
                                <i class="ri-file-shield-2-line text-sky-600 text-2xl mr-3"></i>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-800">Izin Keluar Asrama (Resmi / Berizin)</h4>
                                    <span class="text-xs text-gray-500">Keluar dengan surat persetujuan resmi (IB / Dinas / Sakit)</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-bold bg-sky-100 text-sky-800 rounded-full">{{ $userStatusIzin ?? 0 }} Mhs</span>
                        </div>
                        <!-- Terlambat -->
                        <div class="flex justify-between items-center p-3 hover:bg-gray-50">
                            <div class="flex items-center">
                                <i class="ri-alarm-warning-line text-rose-600 text-2xl mr-3"></i>
                                <div>
                                    <h4 class="text-sm font-bold text-gray-800">Terlambat Kembali</h4>
                                    <span class="text-xs text-gray-500">Melebihi batas jam malam atau batas kembali izin</span>
                                </div>
                            </div>
                            <span class="px-2.5 py-1 text-xs font-bold bg-rose-100 text-rose-800 rounded-full">{{ $userStatusTelat ?? 0 }} Mhs</span>
                        </div>
                    </div>
                    <div class="flex flex-col md:flex-row justify-normal md:justify-between px-1 text-xs text-gray-500">
                        <div class="font-medium">
                            <i class="ri-time-line mr-1"></i> Batas Waktu Gerbang Harian:
                        </div>
                        <div>
                            Jam {{ $jamMulai ?? '06:00' }} WIB - Jam {{ $jamSelesai ?? '22:00' }} WIB
                        </div>
                    </div>

                </div>
                <div class="bg-white border-2 shadow-black/5 p-3 rounded-md lg:col-span-2">
                    <div>
                        <canvas id="myChart"></canvas>
                    </div>
                    <div class="flex flex-col justify-between my-3 items-start">
                        <div class="font-medium text-lg">Grafik Asrama Polbangtan Malang</div>
                        <p class="text-gray-500 text-sm">Jumlah perizinan mahasiswa keluar asrama dalam 7 hari terakhir</p>
                    </div>
                    <div class="border-b border-gray-300 my-2"></div>
                    <a href="/absensi-mahasiswa" class="text-utama text-sm">
                        <i class="ri-list-check mr-1"></i>Lihat Data
                    </a>
                </div>
            </div>

            <div class="bg-white border-2 shadow-black/5 w-full h-full lg:w-[80%] p-6 rounded-md">
                <div class="flex flex-col justify-between mb-3 items-start">
                    <div class="font-medium text-lg">Absensi Keluar Asrama</div>
                    <p class="text-gray-500 text-sm">Data perizinan mahasiswa keluar asrama dalam 7 hari terakhir</p>
                </div>
                <div class="border-b border-gray-300 my-2"></div>
                <div class="h-screen overflow-y-auto">
                    @foreach ($absen7days as $d)
                        <div class="flex items-center space-x-4 py-3 border-b border-gray-100">
                                <div class="font-semibold text-sm md:text-lg">{{ $d->user->name }}</div>
                                @isset($d->user->kelas->nama_kelas)
                                    <div class="text-gray-500 text-sm">{{ $d->user->kelas->nama_kelas }} -
                                        {{ strftime('%d %B %Y', strtotime($d->presence_date)) }}</div>
                                @endisset
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="{{ asset('js/dashboardAdmin.js') }}"></script>
@endsection
