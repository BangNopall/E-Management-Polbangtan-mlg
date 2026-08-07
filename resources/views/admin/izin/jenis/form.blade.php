@extends('layouts.main')

@section('container')
<div class="px-4 pt-6" x-data="jenisIzinForm({{ json_encode($steps) }})">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">
                {{ $jenisIzin->exists ? 'Edit Jenis Izin & Alur Persetujuan' : 'Tambah Jenis Izin Baru' }}
            </h1>
            <p class="text-sm text-gray-500">Atur parameter perizinan dan susun urutan alur persetujuan pejabat berwenang.</p>
        </div>
        <a href="{{ route('admin.jenis.index') }}" class="inline-flex items-center px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
            <i class="ri-arrow-left-line mr-1"></i> Kembali
        </a>
    </div>

    @include('partials.alert')

    <form action="{{ $jenisIzin->exists ? route('admin.jenis.update', $jenisIzin->id) : route('admin.jenis.store') }}" method="POST">
        @csrf
        @if ($jenisIzin->exists)
            @method('PUT')
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Parameter Utama Jenis Izin -->
            <div class="lg:col-span-1 bg-white border-2 border-gray-100 rounded-xl p-5 shadow-xs space-y-4">
                <h3 class="font-bold text-gray-900 text-base pb-2 border-b border-gray-100 flex items-center">
                    <i class="ri-file-settings-line text-teal-700 mr-2"></i> Parameter Jenis Perizinan
                </h3>

                <div>
                    <label for="kode" class="block text-xs font-bold text-gray-900 mb-1">Kode Jenis Izin <span class="text-rose-500">*</span></label>
                    <input type="text" id="kode" name="kode" value="{{ old('kode', $jenisIzin->kode) }}" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-xs font-mono font-bold focus:ring-teal-500 focus:border-teal-500 uppercase" placeholder="Contoh: IZIN_KELUAR" required>
                    @error('kode') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label for="nama" class="block text-xs font-bold text-gray-900 mb-1">Nama Jenis Perizinan <span class="text-rose-500">*</span></label>
                    <input type="text" id="nama" name="nama" value="{{ old('nama', $jenisIzin->nama) }}" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-xs focus:ring-teal-500 focus:border-teal-500" placeholder="Contoh: Izin Keluar Asrama Biasa" required>
                    @error('nama') <p class="text-rose-600 text-[11px] mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label for="min_ajukan_jam" class="block text-xs font-bold text-gray-900 mb-1">Min Lead Time (Jam)</label>
                        <input type="number" id="min_ajukan_jam" name="min_ajukan_jam" value="{{ old('min_ajukan_jam', $jenisIzin->min_ajukan_jam ?? 2) }}" min="0" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-xs focus:ring-teal-500 focus:border-teal-500" required>
                    </div>
                    <div>
                        <label for="maks_durasi_jam" class="block text-xs font-bold text-gray-900 mb-1">Maks Durasi (Jam)</label>
                        <input type="number" id="maks_durasi_jam" name="maks_durasi_jam" value="{{ old('maks_durasi_jam', $jenisIzin->maks_durasi_jam ?? 12) }}" min="1" class="w-full bg-gray-50 border border-gray-300 rounded-lg p-2.5 text-xs focus:ring-teal-500 focus:border-teal-500" required>
                    </div>
                </div>

                <div class="space-y-2 pt-2 border-t border-gray-100 text-xs">
                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="butuh_bermalam" value="1" {{ old('butuh_bermalam', $jenisIzin->butuh_bermalam) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500">
                        <span class="font-medium text-gray-800">Termasuk Izin Bermalam</span>
                    </label>

                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="butuh_ukm" value="1" {{ old('butuh_ukm', $jenisIzin->butuh_ukm) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500">
                        <span class="font-medium text-gray-800">Wajib Memilih UKM / Ormawa</span>
                    </label>

                    <label class="flex items-center space-x-2">
                        <input type="checkbox" name="butuh_konfirmasi_tiba" value="1" {{ old('butuh_konfirmasi_tiba', $jenisIzin->butuh_konfirmasi_tiba) ? 'checked' : '' }} class="rounded text-teal-600 focus:ring-teal-500">
                        <span class="font-medium text-gray-800">Memerlukan Konfirmasi Kedatangan Eksternal</span>
                    </label>

                    <label class="flex items-center space-x-2 pt-2">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $jenisIzin->exists ? $jenisIzin->is_active : true) ? 'checked' : '' }} class="rounded text-emerald-600 focus:ring-emerald-500">
                        <span class="font-bold text-emerald-800">Status Jenis Izin Aktif</span>
                    </label>
                </div>
            </div>

            <!-- Editor Alur Persetujuan (Workflow Steps) -->
            <div class="lg:col-span-2 bg-white border-2 border-gray-100 rounded-xl p-5 shadow-xs space-y-4">
                <div class="flex items-center justify-between pb-2 border-b border-gray-100">
                    <div>
                        <h3 class="font-bold text-gray-900 text-base flex items-center">
                            <i class="ri-git-merge-line text-teal-700 mr-2"></i> Editor Alur Persetujuan (Workflow Steps)
                        </h3>
                        <p class="text-xs text-gray-500">Susun urutan langkah penandatangan secara berurutan (1, 2, 3...).</p>
                    </div>
                    <button type="button" @click="addStep()" class="inline-flex items-center px-3 py-1.5 bg-teal-50 hover:bg-teal-100 text-teal-800 border border-teal-200 text-xs font-bold rounded-lg transition">
                        <i class="ri-add-line mr-1"></i> Tambah Langkah
                    </button>
                </div>

                @error('steps')
                    <div class="p-3 bg-rose-50 border border-rose-200 rounded-lg text-xs text-rose-800 font-bold">
                        ⚠️ {{ $message }}
                    </div>
                @enderror

                <div class="space-y-4">
                    <template x-for="(step, index) in steps" :key="index">
                        <div class="p-4 border-2 border-gray-100 rounded-xl bg-gray-50/50 relative space-y-3">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-xs text-teal-800 bg-teal-50 px-2.5 py-1 rounded border border-teal-200" x-text="'Langkah ' + (index + 1)"></span>
                                <input type="hidden" :name="'steps[' + index + '][urutan]'" :value="index + 1">
                                <button type="button" @click="removeStep(index)" class="text-rose-600 hover:text-rose-800 text-xs font-semibold flex items-center">
                                    <i class="ri-delete-bin-line mr-1"></i> Hapus Langkah
                                </button>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-xs">
                                <div class="md:col-span-1">
                                    <label class="block font-bold text-gray-800 mb-1">Label / Nama Langkah</label>
                                    <input type="text" :name="'steps[' + index + '][label]'" x-model="step.label" class="w-full bg-white border border-gray-300 rounded-lg p-2 text-xs" placeholder="Contoh: Persetujuan Dosen PA" required>
                                </div>

                                <div class="md:col-span-1">
                                    <label class="block font-bold text-gray-800 mb-1">Resolver Penandatangan</label>
                                    <select :name="'steps[' + index + '][resolver]'" x-model="step.resolver" class="w-full bg-white border border-gray-300 rounded-lg p-2 text-xs" required>
                                        <option value="dosen_pa">Dosen PA Mahasiswa</option>
                                        <option value="pembina_ukm">Pembina UKM Terkait</option>
                                        <option value="petugas_jaga">Petugas Jaga / Piket</option>
                                        <option value="pejabat">Pejabat Berdasarkan Jabatan</option>
                                    </select>
                                </div>

                                <div class="md:col-span-1">
                                    <label class="block font-bold text-gray-800 mb-1">Penyelesaian (Resolve Saat)</label>
                                    <select :name="'steps[' + index + '][resolve_saat]'" x-model="step.resolve_saat" class="w-full bg-white border border-gray-300 rounded-lg p-2 text-xs" required>
                                        <option value="submit">Submit (Pembekuan Awal)</option>
                                        <option value="langkah_aktif">Langkah Aktif (Dinamis)</option>
                                    </select>
                                </div>
                            </div>

                            <!-- Pilihan Jabatan (Hanya tampil jika resolver == 'pejabat') -->
                            <div x-show="step.resolver === 'pejabat'" class="pt-2 border-t border-gray-200 space-y-1.5 text-xs">
                                <label class="block font-bold text-gray-800">Target Jabatan Pejabat <span class="text-rose-500">*</span></label>
                                <div class="grid grid-cols-2 md:grid-cols-4 gap-2 bg-white p-2.5 rounded-lg border border-gray-200">
                                    <label class="flex items-center space-x-1.5">
                                        <input type="checkbox" :name="'steps[' + index + '][jabatan][]'" value="direktur" x-model="step.jabatan" class="rounded text-teal-600">
                                        <span>Direktur</span>
                                    </label>
                                    <label class="flex items-center space-x-1.5">
                                        <input type="checkbox" :name="'steps[' + index + '][jabatan][]'" value="wadir_1" x-model="step.jabatan" class="rounded text-teal-600">
                                        <span>Wadir 1</span>
                                    </label>
                                    <label class="flex items-center space-x-1.5">
                                        <input type="checkbox" :name="'steps[' + index + '][jabatan][]'" value="wadir_3" x-model="step.jabatan" class="rounded text-teal-600">
                                        <span>Wadir 3</span>
                                    </label>
                                    <label class="flex items-center space-x-1.5">
                                        <input type="checkbox" :name="'steps[' + index + '][jabatan][]'" value="kaprodi" x-model="step.jabatan" class="rounded text-teal-600">
                                        <span>Kaprodi</span>
                                    </label>
                                    <label class="flex items-center space-x-1.5">
                                        <input type="checkbox" :name="'steps[' + index + '][jabatan][]'" value="kepala_asrama" x-model="step.jabatan" class="rounded text-teal-600">
                                        <span>Kepala Asrama</span>
                                    </label>
                                </div>
                                <input type="hidden" :name="'steps[' + index + '][mode]'" value="any">
                            </div>
                        </div>
                    </template>
                </div>

                <div class="pt-4 border-t border-gray-100 flex items-center justify-end space-x-3">
                    <a href="{{ route('admin.jenis.index') }}" class="px-4 py-2 text-xs font-semibold text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg transition">Batal</a>
                    <button type="submit" class="px-5 py-2 text-xs font-bold text-white bg-teal-700 hover:bg-teal-800 rounded-lg shadow-xs transition">
                        <i class="ri-save-line mr-1"></i> Simpan Konfigurasi Jenis Izin
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
    function jenisIzinForm(initialSteps) {
        return {
            steps: initialSteps.length > 0 ? initialSteps : [
                { urutan: 1, label: 'Persetujuan Dosen PA', resolver: 'dosen_pa', mode: 'any', resolve_saat: 'submit', jabatan: [] }
            ],

            addStep() {
                this.steps.push({
                    urutan: this.steps.length + 1,
                    label: 'Persetujuan Langkah ' + (this.steps.length + 1),
                    resolver: 'pejabat',
                    mode: 'any',
                    resolve_saat: 'submit',
                    jabatan: ['kaprodi']
                });
            },

            removeStep(index) {
                if (this.steps.length <= 1) {
                    alert('Minimal 1 langkah persetujuan wajib ada.');
                    return;
                }
                this.steps.splice(index, 1);
            }
        }
    }
</script>
@endsection
