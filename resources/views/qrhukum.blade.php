@extends('layouts.main')
@section('container')
    @include('partials.qrcode')
    <div class="px-3 py-6 md:p-6 mb-5">
        <div class="flex justify-between items-center">
            <!-- Konten Pertama -->
            <div class="mx-auto w-full hidden lg:block">
                <div class="text-7xl text-utama font-semibold">Kode QR Pelanggaran</div>
                <div class="text-gray-400 text-lg mt-3">Scan kode QR Anda untuk membuat laporan pelanggaran.</div>
            </div>
            <!-- Konten Kedua -->
            <div class="mx-auto">
                @if (session()->has('success'))
                    <div id="alert-3" class="flex items-center p-4 mb-4 text-green-800 rounded-lg bg-green-200"
                        role="alert">
                        <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
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
                    <div id="alert-2" class="flex items-center p-4 mb-4 text-red-800 rounded-lg bg-red-200"
                        role="alert">
                        <svg class="flex-shrink-0 w-4 h-4" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                            fill="currentColor" viewBox="0 0 20 20">
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
                <div class="bg-utama rounded text-white div-shadow-kodeqr w-full sm:w-[350px] mx-auto p-3 md:p-5">
                    <div class="relative max-w-sm transition-all duration-300 filter mx-auto w-[60%] mt-5">
                        <img class="rounded-lg w-full" src="{{ asset('img/qr-test-aja.svg') }}" alt="Kode QR">
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="absolute inset-0 bg-black bg-opacity-50 rounded-lg"></div>
                            <div class="relative z-10">
                                {{-- <form action="" method="get"> --}}
                                <button id="showQRCodeBtn"
                                    class="bg-[#0079FF] text-white rounded-lg font-medium px-5 py-2 text-md hover:bg-blue-700 w-full h-full">
                                    Buka
                                </button>
                                {{-- </form> --}}
                            </div>
                        </div>
                    </div>
                    <div class="text-center mt-5">
                        <div class="text-xl font-medium">Kode QR</div>
                        <div class="text-sm">Scan kode QR untuk membuat laporan pelanggaran.</div>
                    </div>
                    <div class="bg-teal-600 w-[95%] mx-auto rounded-lg mt-5 p-3">
                        <h1 class="font-medium text-md text-white">Profil Anda</h1>
                        <div class="border-b border-gray-300 mt-1 mb-3"></div>
                        <div class="flex items-center mb-2">
                            <div class="w-9 h-9 md:w-12 md:h-12 rounded-full">
                                @if ($user->image)
                                    <img src="{{ asset('storage/images/' . $user->image) }}"
                                        class="rounded-full w-full h-full" id="fotoProfil" alt="Foto Profil">
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
                        <h1 class="font-medium text-md text-white">Form Laporan</h1>
                        <div class="border-b border-gray-300 mt-1 mb-3"></div>
                        @if ($status == null)
                            <button
                                class="bg-teal-900 text-white rounded-lg px-5 py-2 text-md hover:bg-teal-700 w-full h-full"
                                type="button">Closed</button>
                        @else
                            <form action="{{ route('home.formHukumShow', ['kategori_id' => 1]) }}" method="get">
                                @csrf
                                <button
                                    class="bg-teal-900 text-white rounded-lg px-5 py-2 text-md hover:bg-teal-700 w-full h-full"
                                    type="submit">Input Form</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="{{ asset('js/kodeqr.js') }}"></script>
@endsection
