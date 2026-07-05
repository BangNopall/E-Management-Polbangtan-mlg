@extends('layouts.main')
@section('container')
    @include('partials.qrcode')
    <div class="px-3 py-6 md:p-6 mb-5">
        <div class="flex justify-between items-center">
            <!-- Konten Pertama -->
            <div class="mx-auto py-[200px] hidden lg:block">
                <div class="text-7xl text-utama font-semibold">Kode QR Absen</div>
                <div class="text-gray-400 text-lg mt-3">Scan kode QR untuk presensi keluar asrama, apel, upacara, dan senam.
                </div>
            </div>
            <!-- Konten Kedua -->
            <div class="bg-utama rounded text-white div-shadow-kodeqr w-full md:w-[350px] mx-auto p-3 md:p-5">
                <div class="relative max-w-sm transition-all duration-300 filter mx-auto w-[60%] mt-5">
                    <img class="rounded-lg w-full" src="{{ asset('img/qr-test-aja.svg') }}" alt="Kode QR">
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="absolute inset-0 bg-black bg-opacity-50 rounded-lg"></div>
                        <div class="relative z-10">
                            <button id="showQRCodeBtn"
                                class="bg-[#0079FF] text-white rounded-lg font-medium px-5 py-2 text-md hover:bg-blue-700 w-full h-full">
                                Buka
                            </button>
                        </div>
                    </div>
                </div>
                <div class="text-center mt-5">
                    <div class="text-xl font-medium">Kode QR</div>
                    <div class="text-sm text-gray-200">Scan kode QR untuk presensi keluar asrama, apel, upacara, dan senam.
                    </div>
                </div>
                <div class="bg-teal-600 w-[95%] mx-auto rounded-lg mt-5 p-3">
                    <h1 class="font-medium text-md text-white">Profil Anda</h1>
                    <div class="border-b border-gray-300 mt-1 mb-3"></div>
                    <div class="flex items-center mb-2">
                        <div class="w-9 h-9 md:w-12 md:h-12 rounded-full">
                            @if ($user->image)
                                <img src="{{ asset('storage/images/' . $user->image) }}" class="rounded-full w-full h-full"
                                    id="fotoProfil" alt="Foto Profil">
                            @else
                                <img src="https://placehold.co/36x36" class="rounded-full w-full h-full" id="fotoProfil"
                                    alt="Foto Profil">
                            @endif
                        </div>
                        <div class="ml-2">
                            <p class="text-sm text-gray-200">{{ $user->name }}</p>
                        </div>
                    </div>
                    <ul class="text-gray-200 text-sm">
                        <li>
                            <span class="font-medium text-white">Prodi:</span> {{ $user->prodi->prodi }}
                        </li>
                        <li>
                            <span class="font-medium text-white">Blok Kamar:</span> Blok {{ $user->blok->name }}
                        </li>
                        <li>
                            <span class="font-medium text-white">No. Kamar:</span> {{ $user->no_kamar }}
                        </li>
                    </ul>
                </div>
                <div class="bg-teal-600 w-[95%] mx-auto rounded-lg mt-5 p-3 mb-5">
                    <h1 class="font-medium text-md text-white">Status Presensi</h1>
                    <div class="border-b border-gray-300 mt-1 my-2"></div>
                    <div class="text-sm text-gray-200 mb-1 font-medium">- <span class="underline">Keluar Asrama</span></div>
                    <div class="flex flex-col gap-2 items-center">
                        @if ($user->status == 'didalam')
                            <div class="bg-green-500 w-full text-center rounded">
                                <p class="text-sm text-white p-1">Didalam Asrama</p>
                            </div>
                        @elseif ($user->status == 'diluar')
                            <div class="bg-red-500 w-full text-center rounded">
                                <p class="text-sm text-white p-1">Diluar Asrama</p>
                            </div>
                        @elseif ($user->status == 'izin')
                            <div class="bg-yellow-500 w-full text-center rounded">
                                <p class="text-sm text-white p-1">Izin</p>
                            </div>
                        @elseif ($user->status == 'telat')
                            <div class="bg-orange-500 w-full text-center rounded">
                                <p class="text-sm text-white p-1">Telat Masuk Asrama</p>
                            </div>
                        @endif
                    </div>
                    @php
                        function getBackgroundColor($jadwal, $time)
                        {
                            if ($jadwal->status_kehadiran == 'Alpha') {
                                if ($jadwal->mulai_acara <= $time && $jadwal->selesai_acara >= $time) {
                                    $jadwal->status_kehadiran = 'Belum Absen';
                                    return 'bg-orange-600';
                                }
                                if ($jadwal->mulai_acara > $time) {
                                    $jadwal->status_kehadiran = 'Belum Dibuka';
                                    return 'bg-blue-500';
                                }
                                return 'bg-red-500';
                            } elseif ($jadwal->status_kehadiran == 'Hadir') {
                                return 'bg-green-500';
                            }
                            return 'bg-blue-600'; // default color
                        }
                    @endphp
                    <div class="text-sm text-gray-200 my-1 font-medium">
                        - <span class="underline">Kegiatan Wajib</span>
                    </div>
                    <div class="flex flex-col gap-1">
                        @foreach ($getJadwalKegiatan as $jadwal)
                            <div class="{{ getBackgroundColor($jadwal, $time) }} w-full text-center rounded">
                                <p class="text-sm text-white p-1">
                                    {{ $jadwal->jenis_kegiatan }} : {{ $jadwal->status_kehadiran }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                    @if ($getJadwalKegiatan->isEmpty())
                        <div class="bg-gray-500 w-full text-center rounded">
                            <p class="text-sm text-gray-200 p-1">Tidak ada</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/kodeqr.js') }}"></script>
@endsection
