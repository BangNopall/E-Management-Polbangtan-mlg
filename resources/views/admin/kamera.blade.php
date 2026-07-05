@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <div class="mx-auto">
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
            <div class="bg-utama rounded-lg text-white div-shadow-kodeqr w-[95%] md:w-[450px] mx-auto p-3 md:p-5">
                <div class="flex justify-center items-center mb-4 w-auto mx-auto">
                    <div id="reader" style="width: 600px"></div>
                </div>
                <div class="flex gap-1 w-full">
                    <select id="cameraSelect"
                        class="rounded bg-teal-800 border-teal-900 text-gray-100 w-full focus:ring-0 focus:border-0 block flex-1 text-sm p-2"
                        disabled>
                        <option hidden selected class="bg-teal-800">Pilih Kamera</option>
                    </select>
                    <button class="rounded bg-teal-800 border-teal-900 text-gray-100 p-2" id="btnstop">Stop Scan</button>
                </div>
                <div class="text-center mb-3 mt-2 space-y-1">
                    <div class="text-lg font-semibold">Kamera Absen Keluar</div>
                    <p class="text-white text-sm text-center">Pindai kode QR dengan kamera</p>
                </div>
                <div class="bg-teal-600 w-full md:w-[400px] mx-auto rounded-lg p-3">
                    <h1 class="font-medium text-md text-white">Profil Anda</h1>
                    <div class="border-b border-gray-300 mt-1 mb-3"></div>
                    <div class="flex items-center mb-2 ml-2">
                        @if ($user->image)
                            <img src="{{ asset('storage/images/' . $user->image) }}"
                                class="rounded-full w-9 h-9 md:w-12 md:h-12" id="fotoProfil" alt="Foto Profil">
                        @else
                            <img src="https://placehold.co/36x36" class="rounded-full w-9 h-9 md:w-12 md:h-12"
                                id="fotoProfil" alt="Foto Profil">
                        @endif
                        <div class="ml-3">
                            <p class="text-sm text-gray-200">{{ $user->name }}</p>
                        </div>
                    </div>
                    <h1 class="font-medium text-md text-white">Piket hari ini</h1>
                    <div class="border-b border-gray-300 my-1"></div>
                    <div class="space-y-1">
                        <p class="text-sm font-normal text-white p-1">{{ $petugas1 }}</p>
                        <p class="text-sm font-normal text-white p-1">{{ $petugas2 }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form action="{{ route('admin.presense.api') }}" method="post" id="form">
        @csrf
        <input type="hidden" name="user_id" id="user_id">
        <input type="hidden" name="date" id="date">
        <input type="hidden" name="time" id="time">
        <input type="hidden" name="status" id="status">
        <input type="hidden" name="scanner" id="scanner">
    </form>
    <script src="{{ asset('js/library/html5-qrcode.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript" src="{{ asset('js/scancamera-keluar.js') }}"></script>
@endsection