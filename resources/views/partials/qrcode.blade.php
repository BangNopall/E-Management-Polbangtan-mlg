<div id="qrCode" class="bg-black/70 h-screen w-full fixed top-0 left-0 z-30 hidden">
    <div class="bg-white rounded-lg px-3 md:px-5 py-5 w-[95%] md:max-w-[600px] mx-auto mt-10 md:mt-5">
        <h2 class="text-2xl font-semibold mb-4 text-gray-800 text-center">Kode QR</h2>
        <div class="flex items-center my-3 ml-2">
            <div class="w-[55px] h-[55px] md:w-12 md:h-12 rounded-xl">
                @if ($user->image)
                    <img src="{{ asset('storage/images/' . $user->image) }}" class="rounded-xl w-full h-full"
                        id="fotoProfil" alt="Foto Profil">
                @else
                    <img src="https://placehold.co/36x36" class="rounded-xl w-full h-full" id="fotoProfil"
                        alt="Foto Profil">
                @endif
            </div>
            <div class="ml-3">
                <p class="text-sm font-semibold text-black">{{ $user->name }}</p>
                <p class="text-sm text-gray-700">{{ $user->blok->name }}{{ $user->no_kamar }}</p>
            </div>
        </div>
        <div class="flex flex-col gap-3 justify-center items-center mb-4">
            <div class="text-xs text-red-400 text-center">Kode qr akan expired dalam waktu <span
                    id="waktu">30</span>s</div>
            <div class="bg-cover object-fill inisvg">
                {{ $QrCode }}
            </div>
        </div>
        <p class="text-gray-600 text-sm text-center">Pindai kode QR untuk melakukan presensi.</p>
        <div class="mt-6">
            <button id="closeQRCodeBtn"
                class="bg-utama text-white rounded-lg font-medium px-5 py-2 text-md hover:bg-teal-800 w-full">
                Tutup Kode QR
            </button>
        </div>
    </div>
</div>
