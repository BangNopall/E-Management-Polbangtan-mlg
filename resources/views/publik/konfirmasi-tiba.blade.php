@extends('layouts.publik')

@section('title', 'Konfirmasi Kedatangan Mahasiswa - ' . $pengajuan->nama_snapshot)

@section('container')
<div class="space-y-4">
    @include('partials.alert')

    @if ($pengajuan->tiba_at !== null)
        <!-- TANDA TERIMA STATIS (SEKALI PAKAI - SUDAH DIKONFIRMASI) -->
        <div class="bg-emerald-50 border-2 border-emerald-300 rounded-2xl p-5 text-center shadow-xs">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-emerald-100 text-emerald-600 rounded-full mb-3 shadow-inner">
                <i class="ri-checkbox-circle-fill text-3xl"></i>
            </div>
            <h2 class="text-lg font-extrabold text-emerald-900 uppercase tracking-wide">Kedatangan Terapresiasi & Dikonfirmasi</h2>
            <p class="text-xs text-emerald-700 font-medium mt-1">Terima kasih. Data kedatangan mahasiswa telah terekam secara resmi di Sistem Perizinan Asrama.</p>
        </div>

        <div class="bg-white border-2 border-gray-100 rounded-2xl p-5 shadow-xs space-y-3 text-xs">
            <h3 class="font-bold text-gray-900 text-sm pb-2 border-b border-gray-100 flex items-center">
                <i class="ri-shield-check-line text-teal-700 mr-2 text-base"></i> Tanda Terima Konfirmasi Kedatangan
            </h3>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <span class="text-gray-400 block text-[11px]">Mahasiswa Pemohon</span>
                    <span class="font-bold text-gray-900 text-sm block mt-0.5">{{ $pengajuan->nama_snapshot }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Nomor Surat Izin</span>
                    <span class="font-mono font-bold text-teal-800 block mt-0.5">{{ $pengajuan->nomor_surat }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Waktu Konfirmasi</span>
                    <span class="font-bold text-gray-800 block mt-0.5">{{ $pengajuan->tiba_at->format('d M Y H:i') }} WIB</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Lokasi Kedatangan</span>
                    <span class="font-bold text-gray-800 block mt-0.5">{{ $pengajuan->tiba_di }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Dikonfirmasi Oleh</span>
                    <span class="font-bold text-gray-800 block mt-0.5">{{ $pengajuan->tiba_dikonfirmasi_oleh }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Kontak Pengonfirmasi</span>
                    <span class="font-bold text-gray-800 block mt-0.5">{{ $pengajuan->tiba_kontak ?? '-' }}</span>
                </div>
            </div>
        </div>
    @else
        <!-- FORMULIR INPUT KONFIRMASI TIBA (3-4 FIELD RINGKAS) -->
        <div class="bg-white border-2 border-gray-100 rounded-2xl p-5 shadow-xs space-y-4">
            <div class="border-b border-gray-100 pb-3">
                <span class="text-[10px] font-bold text-teal-700 bg-teal-50 px-2 py-0.5 rounded border border-teal-200 uppercase">
                    Konfirmasi Kedatangan Eksternal
                </span>
                <h2 class="text-base font-bold text-gray-900 mt-2">Formulir Kedatangan Mahasiswa</h2>
                <p class="text-xs text-gray-500 mt-0.5">
                    Harap isi formulir di bawah ini sebagai bukti bahwa mahasiswa <strong class="text-gray-800">{{ $pengajuan->nama_snapshot }}</strong> telah sampai di lokasi tujuan dengan selamat.
                </p>
            </div>

            <form action="{{ route('publik.konfirmasi.tiba.store', ['qr_token' => $pengajuan->qr_token]) }}" method="POST" class="space-y-4 text-xs">
                @csrf

                <div>
                    <label for="tiba_dikonfirmasi_oleh" class="block font-bold text-gray-900 mb-1">
                        Nama Pemeriksa / Pengonfirmasi <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="tiba_dikonfirmasi_oleh" name="tiba_dikonfirmasi_oleh" value="{{ old('tiba_dikonfirmasi_oleh') }}" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-xs focus:ring-teal-500 focus:border-teal-500" placeholder="Contoh: Pak Supriyadi (Ketua RT 04 / Panitia Lomba)" required>
                    @error('tiba_dikonfirmasi_oleh')
                        <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tiba_di" class="block font-bold text-gray-900 mb-1">
                        Lokasi / Kota Kedatangan <span class="text-rose-500">*</span>
                    </label>
                    <input type="text" id="tiba_di" name="tiba_di" value="{{ old('tiba_di', $pengajuan->tujuan_lokasi) }}" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-xs focus:ring-teal-500 focus:border-teal-500" placeholder="Contoh: Surabaya / Sukun Malang" required>
                    @error('tiba_di')
                        <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="tiba_kontak" class="block font-bold text-gray-900 mb-1">
                        No. HP / Kontak Pengonfirmasi (Opsional)
                    </label>
                    <input type="text" id="tiba_kontak" name="tiba_kontak" value="{{ old('tiba_kontak') }}" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-xs focus:ring-teal-500 focus:border-teal-500" placeholder="08xxxxxxxxxx">
                </div>

                <div class="pt-2">
                    <button type="submit" class="w-full py-2.5 bg-teal-700 hover:bg-teal-800 text-white font-bold rounded-lg shadow-2xs transition flex items-center justify-center">
                        <i class="ri-send-plane-fill mr-1.5 text-base"></i> Kirim Konfirmasi Kedatangan
                    </button>
                </div>
            </form>
        </div>
    @endif
</div>
@endsection
