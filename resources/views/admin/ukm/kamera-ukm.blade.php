@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <div class="mx-auto">
            @include('partials.alert')

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
                    <div class="text-lg font-semibold">Scan UKM — {{ $jadwal->ukm->nama }}</div>
                    <p class="text-white text-sm text-center">{{ $jadwal->judul }} ({{ $jadwal->tanggal }})</p>
                </div>
                <div class="bg-teal-600 w-full md:w-[400px] mx-auto rounded-lg p-3">
                    <h1 class="font-medium text-md text-white">Petugas Scanner</h1>
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
                            <p class="text-xs text-teal-200">{{ $user->role->name ?? 'Staf' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <form action="{{ route('admin.ukm.scan.store', $jadwal->id) }}" method="post" id="form">
        @csrf
        <input type="hidden" name="user_id" id="user_id">
        <input type="hidden" name="date" id="date">
        <input type="hidden" name="time" id="time">
        <input type="hidden" name="scanner" id="scanner">
    </form>
    <script src="{{ asset('js/library/html5-qrcode.min.js') }}" type="text/javascript"></script>
    <script type="text/javascript" src="{{ asset('js/scancamera-ukm.js') }}"></script>
@endsection
