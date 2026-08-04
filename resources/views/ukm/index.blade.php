@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-1">UKM Saya</h1>
        <div class="text-gray-600 text-sm">Daftar Unit Kegiatan Mahasiswa yang Anda ikuti dan jadwal kegiatan mendatang</div>
        <div class="border-b border-gray-300 my-5"></div>

        @include('partials.alert')

        @if ($memberships->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach ($memberships as $membership)
                    <div class="bg-white border-2 rounded-lg p-4 flex flex-col justify-between">
                        <div>
                            <div class="flex justify-between items-start mb-2">
                                <h2 class="font-semibold text-xl text-gray-900">{{ $membership->ukm->nama }}</h2>
                                <span class="bg-teal-100 text-teal-800 text-xs font-medium px-2.5 py-0.5 rounded capitalize">
                                    {{ $membership->peran }}
                                </span>
                            </div>
                            <p class="text-gray-600 text-sm mb-4">
                                {{ $membership->ukm->deskripsi ?? 'Tidak ada deskripsi.' }}
                            </p>

                            <h3 class="font-medium text-sm text-gray-800 mb-2">Jadwal Mendatang:</h3>
                            @if ($membership->ukm->jadwals->count() > 0)
                                <ul class="space-y-2 text-xs">
                                    @foreach ($membership->ukm->jadwals->take(5) as $jadwal)
                                        <li class="p-2 bg-gray-50 border rounded flex justify-between items-center">
                                            <div>
                                                <div class="font-medium text-gray-900">{{ $jadwal->judul }}</div>
                                                <div class="text-gray-500">
                                                    {{ \Carbon\Carbon::parse($jadwal->tanggal)->format('d M Y') }} · {{ $jadwal->mulai_acara }}
                                                </div>
                                            </div>
                                            <span class="text-gray-400 capitalize">{{ str_replace('_', ' ', $jadwal->jenis) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <div class="text-xs text-gray-400 italic py-2">Belum ada jadwal mendatang.</div>
                            @endif
                        </div>

                        <div class="mt-4 pt-3 border-t flex justify-between items-center text-xs">
                            <span class="text-gray-500">Status: <strong class="text-green-600 capitalize">{{ $membership->status }}</strong></span>
                            <a href="{{ route('home.kodeqr') }}" class="text-teal-700 hover:underline font-medium flex items-center gap-1">
                                <i class="ri-qr-code-line"></i> Tampilkan QR Saya
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white border-2 rounded-lg p-6 text-center text-gray-500">
                <i class="ri-team-line text-4xl text-gray-300 mb-2 block"></i>
                Anda belum terdaftar dalam UKM mana pun. Hubungi Pelatih atau Admin UKM untuk pendaftaran anggota.
            </div>
        @endif
    </div>
@endsection
