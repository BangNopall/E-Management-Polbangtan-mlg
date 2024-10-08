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
        <div class="flex flex-col lg:flex-row gap-6 mb-6 mt-5 md:mt-12">
            <div class="w-full">
                <div class="bg-white border-2 shadow-black/5 p-3 rounded-md lg:col-span-2 mb-6">
                    <div class="bg-utama rounded p-3">
                        <div class="text-white font-semibold text-md">Absensi Siswa Hari ini</div>
                        <div class="text-gray-300 text-sm">{{ $formattedDate }}</div>
                    </div>
                    <div class="my-2 border-2 border-utama rounded">
                        <ul class="list-group">
                            <!-- Data "Keluar" -->
                            <li class="list-group-item flex flex-col md:flex-row justify-between items-center p-2 md:p-3">
                                <div class="flex flex-col md:flex-row justify-normal md:justify-between">
                                    <!-- Icon -->
                                    <div class="flex items-center">
                                        <i class="ri-information-line text-utama text-4xl mr-2"></i>
                                        <!-- Keterangan Alasan -->
                                        <div>
                                            <h3 class="text-md md:text-lg font-medium">Diluar Asrama</h3>
                                        </div>
                                    </div>
                                </div>
                                <span class="badge bg-red-500 text-sm text-white p-1 rounded">{{ $userStatus }}
                                    <small>Mahasiswa</small></span>
                            </li>
                        </ul>
                    </div>
                    <div class="flex flex-col md:flex-row justify-normal md:justify-between">
                        <div class="text-sm font-medium text-gray-500">
                            Batas Waktu Keluar:
                        </div>
                        <div class="text-sm mt-0 text-gray-500">
                            Jam 06.00 WIB - Jam 22.00 WIB
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

            <div class="bg-white border-2 shadow-black/5 w-full lg:w-[80%] p-6 rounded-md">
                <div class="flex flex-col justify-between mb-3 items-start">
                    <div class="font-medium text-lg">Absensi Keluar Asrama</div>
                    <p class="text-gray-500 text-sm">Data perizinan mahasiswa keluar asrama dalam 7 hari terakhir</p>
                </div>
                <div class="border-b border-gray-300 my-2"></div>
                <div class="h-[550px] overflow-y-auto">
                    @foreach ($absen7days as $d)
                        <div class="flex items-center space-x-4 py-3">
                                <div class="font-semibold text-sm md:text-lg">{{ $d->user->name }}</div>
                                @isset($record->user->kelas->nama_kelas)
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
