@extends('layouts.main')

@section('container')
<div class="px-4 pt-6" x-data="formPengajuanIzin({{ json_encode($jenisIzins) }})">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Buat Pengajuan Izin Baru</h1>
            <p class="text-sm text-gray-500">Isi formulir pengajuan izin di bawah ini dengan data yang valid.</p>
        </div>
        <a href="{{ route('home.izin.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition">
            <i class="ri-arrow-left-line mr-1"></i> Kembali
        </a>
    </div>

    @include('partials.alert')

    @if ($profileIncomplete)
        <div class="p-4 mb-4 text-sm text-amber-800 rounded-lg bg-amber-50 border border-amber-200" role="alert">
            <div class="flex items-center font-bold mb-1">
                <i class="ri-error-warning-line text-lg mr-2 text-amber-600"></i> Profil Belum Lengkap!
            </div>
            <p>Data Prodi, Kelas, atau Blok Ruangan Anda belum terdaftar lengkap di sistem. Anda tidak dapat mengirimkan pengajuan izin sebelum data profil Anda dilengkapi oleh admin asrama.</p>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Form Section -->
        <div class="lg:col-span-2 bg-white border-2 border-gray-100 rounded-lg p-5 shadow-sm">
            <form action="{{ route('home.izin.store') }}" method="POST">
                @csrf

                <!-- Jenis Izin -->
                <div class="mb-4">
                    <label for="jenis_izin_id" class="block mb-2 text-sm font-semibold text-gray-900">Jenis Perizinan <span class="text-rose-500">*</span></label>
                    <select id="jenis_izin_id" name="jenis_izin_id" x-model="selectedJenisId" @change="onJenisChange()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" required>
                        <option value="">-- Pilih Jenis Izin --</option>
                        <template x-for="item in jenisIzins" :key="item.id">
                            <option :value="item.id" x-text="item.nama"></option>
                        </template>
                    </select>
                    @error('jenis_izin_id')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- UKM Selection (Conditional) -->
                <div class="mb-4" x-show="selectedJenis && selectedJenis.butuh_ukm" x-transition>
                    <label for="ukm_id" class="block mb-2 text-sm font-semibold text-gray-900">UKM / Ormawa Penyelenggara <span class="text-rose-500">*</span></label>
                    <select id="ukm_id" name="ukm_id" x-model="selectedUkmId" @change="fetchPratinjauAlur()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5">
                        <option value="">-- Pilih UKM Saya --</option>
                        @foreach ($myUkms as $ukm)
                            <option value="{{ $ukm->id }}">{{ $ukm->nama }}</option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500">Wajib memilih UKM tempat Anda terdaftar sebagai anggota aktif.</p>
                    @error('ukm_id')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Keperluan -->
                <div class="mb-4">
                    <label for="keperluan" class="block mb-2 text-sm font-semibold text-gray-900">Keperluan Perizinan <span class="text-rose-500">*</span></label>
                    <textarea id="keperluan" name="keperluan" rows="3" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" placeholder="Jelaskan alasan/keperluan mengajukan izin secara rinci..." required>{{ old('keperluan') }}</textarea>
                    @error('keperluan')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tujuan Lokasi & Alamat -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label for="tujuan_lokasi" class="block mb-2 text-sm font-semibold text-gray-900">Kota / Tujuan Lokasi <span class="text-rose-500">*</span></label>
                        <input type="text" id="tujuan_lokasi" name="tujuan_lokasi" value="{{ old('tujuan_lokasi') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" placeholder="Contoh: Surabaya / Rumah Orang Tua" required>
                        @error('tujuan_lokasi')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="alamat_tujuan" class="block mb-2 text-sm font-semibold text-gray-900">Alamat Lengkap Tujuan</label>
                        <input type="text" id="alamat_tujuan" name="alamat_tujuan" value="{{ old('alamat_tujuan') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" placeholder="Jl. Raya No. X, Desa/Kec, Kab...">
                        @error('alamat_tujuan')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Waktu Berangkat & Kembali -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label for="waktu_berangkat" class="block mb-2 text-sm font-semibold text-gray-900">Waktu Keberangkatan <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" id="waktu_berangkat" name="waktu_berangkat" x-model="waktuBerangkat" @change="fetchPratinjauAlur()" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" required>
                        @error('waktu_berangkat')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="waktu_kembali" class="block mb-2 text-sm font-semibold text-gray-900">Waktu Perkiraan Kembali <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" id="waktu_kembali" name="waktu_kembali" value="{{ old('waktu_kembali') }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-teal-500 focus:border-teal-500 block w-full p-2.5" required>
                        @error('waktu_kembali')
                            <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="flex items-center justify-end space-x-3 pt-4 border-t border-gray-200">
                    <a href="{{ route('home.izin.index') }}" class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Batal</a>
                    <button type="submit" :disabled="profileIncomplete" class="px-5 py-2 text-sm font-medium text-white bg-teal-700 hover:bg-teal-800 rounded-lg shadow-sm transition disabled:opacity-50 disabled:cursor-not-allowed">
                        <i class="ri-send-plane-fill mr-1"></i> Kirim Pengajuan Izin
                    </button>
                </div>
            </form>
        </div>

        <!-- Live Preview Alur Approver -->
        <div class="bg-gray-50 border-2 border-gray-200 rounded-lg p-5">
            <h3 class="font-bold text-gray-900 text-base mb-1 flex items-center">
                <i class="ri-git-commit-line text-teal-600 mr-2"></i> Pratinjau Alur Persetujuan
            </h3>
            <p class="text-xs text-gray-500 mb-4">Estimasi pejabat penandatangan yang akan memproses izin ini.</p>

            <div x-show="isLoading" class="text-center py-6">
                <i class="ri-loader-4-line text-2xl text-teal-600 animate-spin block mb-1"></i>
                <span class="text-xs text-gray-500">Menganalisis alur persetujuan...</span>
            </div>

            <div x-show="!isLoading && steps.length === 0" class="text-center py-8 text-gray-400">
                <i class="ri-route-line text-3xl block mb-1"></i>
                <span class="text-xs">Pilih Jenis Izin terlebih dahulu untuk melihat pratinjau alur penandatangan.</span>
            </div>

            <div x-show="!isLoading && steps.length > 0" class="space-y-3">
                <template x-for="step in steps" :key="step.urutan">
                    <div class="bg-white p-3 rounded-lg border border-gray-200 text-xs shadow-2xs">
                        <div class="flex items-center justify-between mb-1">
                            <span class="font-bold text-gray-800" x-text="'Langkah ' + step.urutan + ': ' + step.label"></span>
                            <span class="px-2 py-0.5 text-[10px] font-semibold rounded bg-teal-50 text-teal-700 border border-teal-200" x-text="step.mode.toUpperCase()"></span>
                        </div>
                        <div class="text-gray-600 mt-1">
                            <span class="font-medium text-gray-500">Penandatangan:</span>
                            <template x-if="step.candidates.length > 0">
                                <ul class="list-disc list-inside mt-0.5 text-gray-800 font-semibold">
                                    <template x-for="c in step.candidates" :key="c.id">
                                        <li x-text="c.name"></li>
                                    </template>
                                </ul>
                            </template>
                            <template x-if="step.candidates.length === 0">
                                <p class="text-rose-600 font-semibold mt-0.5">⚠️ Kandidat penandatangan tidak ditemukan di sistem!</p>
                            </template>
                        </div>
                        <template x-if="step.is_fallback">
                            <span class="inline-block mt-1 text-[10px] text-amber-700 bg-amber-50 px-1.5 py-0.5 rounded border border-amber-200" x-text="'ℹ️ Menggunakan fallback resolver (' + step.resolver_used + ')'">
                            </span>
                        </template>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

<script>
    function formPengajuanIzin(jenisIzinsData) {
        return {
            jenisIzins: jenisIzinsData,
            selectedJenisId: '',
            selectedJenis: null,
            selectedUkmId: '',
            waktuBerangkat: '',
            steps: [],
            isLoading: false,
            profileIncomplete: {{ $profileIncomplete ? 'true' : 'false' }},

            onJenisChange() {
                this.selectedJenis = this.jenisIzins.find(j => j.id == this.selectedJenisId) || null;
                if (!this.selectedJenis?.butuh_ukm) {
                    this.selectedUkmId = '';
                }
                this.fetchPratinjauAlur();
            },

            fetchPratinjauAlur() {
                if (!this.selectedJenisId) {
                    this.steps = [];
                    return;
                }

                this.isLoading = true;
                const params = new URLSearchParams({
                    jenis_izin_id: this.selectedJenisId,
                    ukm_id: this.selectedUkmId,
                    waktu_berangkat: this.waktuBerangkat
                });

                fetch(`{{ route('home.izin.pratinjau-alur') }}?${params.toString()}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        this.steps = data.steps;
                    }
                })
                .catch(err => console.error(err))
                .finally(() => {
                    this.isLoading = false;
                });
            }
        }
    }
</script>
@endsection
