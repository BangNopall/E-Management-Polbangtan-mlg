@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Manajemen UKM</h1>
        <div class="text-gray-600 text-sm">Kelola Unit Kegiatan Mahasiswa (UKM) Polbangtan Malang</div>
        <div class="border-b border-gray-300 my-5"></div>

        @include('partials.alert')

        <div class="w-full bg-white border-2 rounded-lg p-3 mb-5">
            <div id="accordion-flush1" data-accordion="collapse" data-active-classes="bg-white text-gray-900"
                data-inactive-classes="text-gray-500">
                <h2 id="accordion-flush-heading-1">
                    <button type="button"
                        class="flex items-center font-normal justify-between gap-5 py-2 text-gray-500 border-b border-gray-200 w-full"
                        data-accordion-target="#accordion-flush-body-1" aria-expanded="false"
                        aria-controls="accordion-flush-body-1">
                        <span class="font-medium text-gray-700">Tambah UKM Baru</span>
                        <i data-accordion-icon aria-hidden="true" class="ri-add-box-line text-md shrink-0"></i>
                    </button>
                </h2>
                <div id="accordion-flush-body-1" class="hidden" aria-labelledby="accordion-flush-heading-1">
                    <div class="mt-3">
                        <form action="{{ route('admin.ukm.store') }}" method="post">
                            @csrf
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-3">
                                <div>
                                    <label for="nama" class="block mb-1 font-medium text-gray-700 text-sm">Nama UKM <span class="text-red-500">*</span></label>
                                    <input type="text" name="nama" id="nama"
                                        class="w-full p-2 rounded border border-gray-300 focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-sm"
                                        placeholder="Contoh: UKM Robotik" required>
                                </div>
                                <div>
                                    <label for="pelatih_id" class="block mb-1 font-medium text-gray-700 text-sm">Pelatih / Pembina Staf (Opsional)</label>
                                    <select name="pelatih_id" id="pelatih_id"
                                        class="w-full p-2 rounded border border-gray-300 focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-sm">
                                        <option value="">-- Pilih Staf Pelatih --</option>
                                        @foreach ($stafPelatih as $staf)
                                            <option value="{{ $staf->id }}">{{ $staf->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="deskripsi" class="block mb-1 font-medium text-gray-700 text-sm">Deskripsi Singkat</label>
                                <textarea name="deskripsi" id="deskripsi" rows="2"
                                    class="w-full p-2 rounded border border-gray-300 focus:border-teal-600 focus:ring-1 focus:ring-teal-600 text-sm"
                                    placeholder="Jelaskan deskripsi dan profil kegiatan UKM..."></textarea>
                            </div>
                            <div>
                                <button class="bg-utama rounded text-sm py-2 px-4 text-white hover:bg-teal-700 font-medium" type="submit">
                                    Simpan UKM
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="w-full bg-white border-2 rounded-lg p-3">
            <div class="flex flex-col md:flex-row justify-between items-center mb-4 gap-3">
                <div class="font-semibold text-lg text-gray-800">Daftar UKM</div>
                <div class="w-full md:w-64">
                    <input type="text" id="search-ukm" placeholder="Cari UKM..."
                        class="w-full p-2 border rounded text-sm focus:border-teal-600 focus:ring-1 focus:ring-teal-600">
                </div>
            </div>

            <div id="ukm-table-container">
                @include('admin.ukm.partials.ukm_table')
            </div>
        </div>
    </div>

    <script>
        document.getElementById('search-ukm')?.addEventListener('input', function(e) {
            const search = e.target.value;
            fetch(`{{ route('admin.ukm.index') }}?search=${encodeURIComponent(search)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                if (data.table) {
                    document.getElementById('ukm-table-container').innerHTML = data.table;
                }
            });
        });
    </script>
@endsection
