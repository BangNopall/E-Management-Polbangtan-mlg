@extends('layouts.main')

@section('container')
<div class="px-3 pt-4 sm:px-6 sm:pt-6" x-data="{ showTolakModal: false }">
    <div class="mb-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-gray-900 flex items-center">
                <i class="ri-shield-check-line text-teal-700 mr-2"></i> Review Keputusan Perizinan
            </h1>
            <p class="text-xs sm:text-sm text-gray-500">
                Meninjau berkas pengajuan izin <span class="font-bold text-gray-800">{{ $pengajuan->nama_snapshot }}</span> (Langkah {{ $approval->urutan }} dari {{ $pengajuan->approvals->count() }})
            </p>
        </div>
        <div>
            @if (in_array($pengajuan->status, ['disetujui', 'berjalan', 'selesai']))
                <a href="{{ route('admin.izin.persetujuan.pdf', $pengajuan->id) }}" target="_blank" class="inline-flex items-center px-3 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-semibold rounded-lg transition mr-1">
                    <i class="ri-file-pdf-line mr-1"></i> Download PDF
                </a>
            @endif
            <a href="{{ route('admin.izin.persetujuan.inbox') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                <i class="ri-arrow-left-line mr-1"></i> Kembali ke Inbox
            </a>
        </div>
    </div>

    @include('partials.alert')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Details Column (Mobile-first layout) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Details Card -->
            <div class="bg-white border-2 border-gray-100 rounded-xl p-4 sm:p-5 shadow-xs">
                <div class="flex items-center justify-between pb-3 mb-4 border-b border-gray-100">
                    <div>
                        <span class="text-[10px] text-gray-400 font-bold uppercase tracking-wider block">Jenis Perizinan</span>
                        <h3 class="text-base sm:text-lg font-bold text-gray-900">{{ optional($pengajuan->jenisIzin)->nama ?? '-' }}</h3>
                    </div>
                    <span class="px-3 py-1 text-xs font-bold rounded-full bg-amber-100 text-amber-800 border border-amber-300">
                        LANGKAH {{ $approval->urutan }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs sm:text-sm mb-4">
                    <div>
                        <span class="text-xs text-gray-400 block">Waktu Keberangkatan</span>
                        <span class="font-bold text-teal-800 flex items-center mt-0.5">
                            <i class="ri-flight-takeoff-line mr-1.5 text-teal-600"></i>
                            {{ optional($pengajuan->waktu_berangkat)->format('d M Y H:i') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Perkiraan Kembali</span>
                        <span class="font-bold text-amber-800 flex items-center mt-0.5">
                            <i class="ri-flight-land-line mr-1.5 text-amber-600"></i>
                            {{ optional($pengajuan->waktu_kembali)->format('d M Y H:i') }}
                        </span>
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">Tujuan Lokasi</span>
                        <span class="font-semibold text-gray-900 block mt-0.5">{{ $pengajuan->tujuan_lokasi }}</span>
                        @if ($pengajuan->alamat_tujuan)
                            <span class="text-xs text-gray-500 block mt-0.5">{{ $pengajuan->alamat_tujuan }}</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-xs text-gray-400 block">UKM / Ormawa</span>
                        <span class="font-semibold text-gray-900 block mt-0.5">{{ optional($pengajuan->ukm)->nama ?? '-' }}</span>
                    </div>
                </div>

                <div class="pt-3 border-t border-gray-100 text-xs sm:text-sm">
                    <span class="text-xs text-gray-400 block mb-1">Keperluan</span>
                    <p class="text-gray-800 bg-gray-50 p-3 rounded-lg border border-gray-100 font-medium leading-relaxed">{{ $pengajuan->keperluan }}</p>
                </div>
            </div>

            <!-- Identitas Pemohon Snapshot Card -->
            <div class="bg-white border-2 border-gray-100 rounded-xl p-4 sm:p-5 shadow-xs">
                <h3 class="font-bold text-gray-900 text-sm sm:text-base mb-3 pb-2 border-b border-gray-100 flex items-center">
                    <i class="ri-user-vcard-line text-teal-700 mr-2"></i> Snapshot Identitas Pemohon
                </h3>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 text-xs">
                    <div>
                        <span class="text-gray-400 block">Nama Mahasiswa</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->nama_snapshot }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">NIRM</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->nirm_snapshot ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Program Studi</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->prodi_snapshot ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Kelas</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->kelas_snapshot ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">Blok / Kamar</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->blok_snapshot ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block">No. HP / WA</span>
                        <span class="font-bold text-gray-800">{{ $pengajuan->no_hp_snapshot ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Form Keputusan Action Bar -->
            <div class="bg-white border-2 border-teal-100 rounded-xl p-4 sm:p-5 shadow-md">
                <h3 class="font-bold text-gray-900 text-base mb-1 flex items-center">
                    <i class="ri-question-answer-line text-teal-700 mr-2"></i> Formulir Keputusan Approver
                </h3>
                <p class="text-xs text-gray-500 mb-4">Pilih keputusan akhir untuk pengajuan izin langkah ini.</p>

                <div class="flex flex-col sm:flex-row items-center gap-3">
                    <!-- Form Setujui -->
                    <form action="{{ route('admin.izin.persetujuan.putuskan', $pengajuan->id) }}" method="POST" class="w-full sm:w-auto flex-1" onsubmit="return confirm('Apakah Anda yakin ingin MENYETUJUI pengajuan izin ini?')">
                        @csrf
                        <input type="hidden" name="keputusan" value="setujui">
                        <button type="submit" class="w-full inline-flex items-center justify-center px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm rounded-lg shadow-sm transition">
                            <i class="ri-check-line text-lg mr-1.5"></i> Setujui Pengajuan Izin
                        </button>
                    </form>

                    <!-- Tombol Buka Modal Tolak -->
                    <button type="button" @click="showTolakModal = true" class="w-full sm:w-auto flex-1 inline-flex items-center justify-center px-5 py-2.5 bg-rose-600 hover:bg-rose-700 text-white font-bold text-sm rounded-lg shadow-sm transition">
                        <i class="ri-close-line text-lg mr-1.5"></i> Tolak Pengajuan Izin
                    </button>
                </div>
            </div>
        </div>

        <!-- Progress Tracker Sidebar -->
        <div class="bg-white border-2 border-gray-100 rounded-xl p-4 sm:p-5 shadow-xs h-fit">
            <h3 class="font-bold text-gray-900 text-sm sm:text-base mb-2 flex items-center">
                <i class="ri-git-merge-line text-teal-700 mr-2"></i> Rantai Persetujuan
            </h3>
            <p class="text-xs text-gray-500 mb-4">Status progress alur penandatanganan berkas perizinan ini.</p>

            @include('izin.partials.tracker', ['approvals' => $pengajuan->approvals])
        </div>
    </div>

    <!-- Modal Penolakan (Alasan Penolakan Wajib) -->
    <div x-show="showTolakModal" x-cloak class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div x-show="showTolakModal" x-transition.opacity class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="showTolakModal = false"></div>

            <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

            <div x-show="showTolakModal" x-transition class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-lg border border-gray-100 transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full p-6">
                <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-4">
                    <h3 class="text-lg font-bold text-rose-700 flex items-center" id="modal-title">
                        <i class="ri-error-warning-line mr-2 text-xl"></i> Tolak Pengajuan Izin
                    </h3>
                    <button type="button" @click="showTolakModal = false" class="text-gray-400 hover:text-gray-600">
                        <i class="ri-close-line text-xl"></i>
                    </button>
                </div>

                <form action="{{ route('admin.izin.persetujuan.putuskan', $pengajuan->id) }}" method="POST">
                    @csrf
                    <input type="hidden" name="keputusan" value="tolak">

                    <div class="mb-4">
                        <label for="catatan" class="block text-xs font-bold text-gray-900 mb-2">
                            Alasan Penolakan <span class="text-rose-500">*</span>
                        </label>
                        <textarea id="catatan" name="catatan" rows="4" class="w-full text-xs sm:text-sm bg-gray-50 border border-gray-300 rounded-lg p-3 focus:ring-rose-500 focus:border-rose-500" placeholder="Berikan alasan yang jelas dan eksplisit mengapa pengajuan izin ini ditolak..." required></textarea>
                        <p class="text-[11px] text-gray-500 mt-1">Alasan penolakan ini akan dapat dibaca langsung oleh mahasiswa pemohon.</p>
                    </div>

                    <div class="flex items-center justify-end space-x-3 pt-3 border-t border-gray-100">
                        <button type="button" @click="showTolakModal = false" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">
                            Batal
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-rose-600 hover:bg-rose-700 rounded-lg shadow-xs transition">
                            <i class="ri-send-plane-line mr-1"></i> Kirim Penolakan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
