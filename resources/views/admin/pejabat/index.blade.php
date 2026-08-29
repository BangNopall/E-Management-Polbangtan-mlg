@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Data Pejabat</h1>
        <div class="text-gray-600 text-sm">Kelola Pejabat Penandatangan Perizinan Asrama Polbangtan-mlg</div>
        <div class="border-b border-gray-300 my-5"></div>
        <div class="bg-white rounded-lg p-3 mt-5 border-2">
            @include('partials.alert')

            <div class="flex flex-col md:flex-row justify-between items-start md:items-center">
                <div>
                    <div class="text-gray-900 text-lg font-medium">Daftar Pejabat</div>
                    <div class="text-gray-600 text-sm mt-1">Daftar pemegang jabatan akademik dan struktural</div>
                </div>
                <div class="mt-5 md:mt-0">
                    <button type="button" data-modal-target="tambahPejabatModal" data-modal-toggle="tambahPejabatModal"
                        class="bg-utama hover:bg-teal-800 text-white rounded px-3 py-2 text-sm flex items-center">
                        <i class="ri-add-line text-md mr-1"></i>Tambah Pejabat
                    </button>
                </div>
            </div>

            <div class="border-b border-gray-300 my-3"></div>

            <div class="relative overflow-x-auto">
                <table class="w-full text-sm text-left text-gray-500">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">No</th>
                            <th scope="col" class="px-6 py-3">Nama Pejabat</th>
                            <th scope="col" class="px-6 py-3">Jabatan</th>
                            <th scope="col" class="px-6 py-3">Lingkup</th>
                            <th scope="col" class="px-6 py-3">Status</th>
                            <th scope="col" class="px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($pejabats as $index => $pejabat)
                            <tr class="bg-white border-b hover:bg-gray-50">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
                                    {{ $index + 1 }}
                                </th>
                                <td class="px-6 py-4 font-medium text-gray-900">
                                    {{ $pejabat->user->name ?? 'User Tidak Ditemukan' }}
                                    <div class="text-xs text-gray-500">{{ $pejabat->user->email ?? '' }}</div>
                                </td>
                                <td class="px-6 py-4 uppercase font-semibold text-teal-800">
                                    {{ str_replace('_', ' ', $pejabat->jabatan) }}
                                </td>
                                <td class="px-6 py-4 capitalize">
                                    {{ $pejabat->lingkup }}
                                    @if ($pejabat->lingkup_id)
                                        <span class="text-xs text-gray-500">(ID: {{ $pejabat->lingkup_id }})</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if ($pejabat->is_active)
                                        <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Aktif</span>
                                    @else
                                        <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">Non-Aktif</span>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex gap-2">
                                        <button type="button" data-modal-target="editPejabatModal{{ $pejabat->id }}" data-modal-toggle="editPejabatModal{{ $pejabat->id }}"
                                            class="bg-utama text-white rounded px-2.5 py-1 text-xs hover:bg-teal-800">
                                            Edit
                                        </button>
                                        <form action="{{ route('admin.pejabat.destroy', $pejabat->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data pejabat ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="bg-red-600 text-white rounded px-2.5 py-1 text-xs hover:bg-red-700">
                                                Hapus
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-6 py-4 text-center text-gray-500">
                                    Belum ada data pejabat. Klik tombol "Tambah Pejabat" untuk menambahkan data.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Edit (Ditempatkan di luar tabel agar struktur HTML valid & padding Flowbite p-4 md:p-5 presisi) --}}
    @foreach ($pejabats as $pejabat)
        <div id="editPejabatModal{{ $pejabat->id }}" tabindex="-1" aria-hidden="true"
            class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
            <div class="relative p-4 w-full max-w-md max-h-full">
                <div class="relative bg-white rounded-lg shadow">
                    <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                        <h3 class="text-lg font-semibold text-gray-900">Edit Data Pejabat</h3>
                        <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-toggle="editPejabatModal{{ $pejabat->id }}">
                            <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                        </button>
                    </div>
                    <form action="{{ route('admin.pejabat.update', $pejabat->id) }}" method="POST" class="p-4 md:p-5">
                        @csrf
                        @method('PUT')
                        <div class="grid gap-4 mb-4 grid-cols-2">
                            <div class="col-span-2">
                                <label for="user_id" class="block mb-2 text-sm font-medium text-gray-900">Pilih User Staf/Dosen</label>
                                <select name="user_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                                    @foreach ($users as $user)
                                        <option value="{{ $user->id }}" {{ $pejabat->user_id == $user->id ? 'selected' : '' }}>{{ $user->name }} ({{ $user->email }})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-span-2">
                                <label for="jabatan" class="block mb-2 text-sm font-medium text-gray-900">Jabatan</label>
                                <select name="jabatan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                                    <option value="kaprodi" {{ $pejabat->jabatan == 'kaprodi' ? 'selected' : '' }}>Ketua Program Studi (Kaprodi)</option>
                                    <option value="kepala_asrama" {{ $pejabat->jabatan == 'kepala_asrama' ? 'selected' : '' }}>Kepala Asrama</option>
                                    <option value="unit_kemahasiswaan" {{ $pejabat->jabatan == 'unit_kemahasiswaan' ? 'selected' : '' }}>Unit Kemahasiswaan</option>
                                </select>
                            </div>
                            <div>
                                <label for="lingkup" class="block mb-1 text-sm font-medium text-gray-900">Lingkup</label>
                                <select name="lingkup" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                                    <option value="global" {{ $pejabat->lingkup == 'global' ? 'selected' : '' }}>Global</option>
                                    <option value="prodi" {{ $pejabat->lingkup == 'prodi' ? 'selected' : '' }}>Prodi</option>
                                    <option value="blok" {{ $pejabat->lingkup == 'blok' ? 'selected' : '' }}>Blok Ruangan</option>
                                </select>
                                <p class="text-[10px] text-gray-500 mt-1">Wewenang: Global (seluruh kampus), Prodi (jurusan), atau Blok (asrama).</p>
                            </div>
                            <div>
                                <label for="lingkup_id" class="block mb-1 text-sm font-medium text-gray-900">ID Lingkup (Opsional)</label>
                                <input type="number" name="lingkup_id" value="{{ $pejabat->lingkup_id }}" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" placeholder="ID Prodi/Blok">
                                <p class="text-[10px] text-gray-500 mt-1">Masukkan ID program studi (contoh: 1) jika lingkup = prodi, atau ID blok gedung jika lingkup = blok.</p>
                            </div>
                            <div class="col-span-2 flex items-center">
                                <input type="checkbox" name="is_active" value="1" id="is_active{{ $pejabat->id }}" {{ $pejabat->is_active ? 'checked' : '' }} class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500">
                                <label for="is_active{{ $pejabat->id }}" class="ms-2 text-sm font-medium text-gray-900">Status Aktif Menjabat</label>
                            </div>
                        </div>
                        <button type="submit" class="text-white inline-flex items-center bg-utama hover:bg-teal-800 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                            Simpan Perubahan
                        </button>
                    </form>
                </div>
            </div>
        </div>
    @endforeach

    {{-- Modal Tambah --}}
    <div id="tambahPejabatModal" tabindex="-1" aria-hidden="true"
        class="hidden overflow-y-auto overflow-x-hidden fixed top-0 right-0 left-0 z-50 justify-center items-center w-full md:inset-0 h-[calc(100%-1rem)] max-h-full">
        <div class="relative p-4 w-full max-w-md max-h-full">
            <div class="relative bg-white rounded-lg shadow">
                <div class="flex items-center justify-between p-4 md:p-5 border-b rounded-t">
                    <h3 class="text-lg font-semibold text-gray-900">Tambah Data Pejabat</h3>
                    <button type="button" class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center" data-modal-toggle="tambahPejabatModal">
                        <svg class="w-3 h-3" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 14 14"><path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="m1 1 6 6m0 0 6 6M7 7l6-6M7 7l-6 6"/></svg>
                    </button>
                </div>
                <form action="{{ route('admin.pejabat.store') }}" method="POST" class="p-4 md:p-5">
                    @csrf
                    <div class="grid gap-4 mb-4 grid-cols-2">
                        <div class="col-span-2">
                            <label for="user_id" class="block mb-2 text-sm font-medium text-gray-900">Pilih User Staf/Dosen</label>
                            <select name="user_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                                <option value="">-- Pilih User --</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-span-2">
                            <label for="jabatan" class="block mb-2 text-sm font-medium text-gray-900">Jabatan</label>
                            <select name="jabatan" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                                <option value="">-- Pilih Jabatan --</option>
                                <option value="kaprodi">Ketua Program Studi (Kaprodi)</option>
                                <option value="kepala_asrama">Kepala Asrama</option>
                                <option value="unit_kemahasiswaan">Unit Kemahasiswaan</option>
                            </select>
                        </div>
                        <div>
                            <label for="lingkup" class="block mb-1 text-sm font-medium text-gray-900">Lingkup</label>
                            <select name="lingkup" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" required>
                                <option value="global">Global</option>
                                <option value="prodi">Prodi</option>
                                <option value="blok">Blok Ruangan</option>
                            </select>
                            <p class="text-[10px] text-gray-500 mt-1">Wewenang: Global (seluruh kampus), Prodi (jurusan), atau Blok (asrama).</p>
                        </div>
                        <div>
                            <label for="lingkup_id" class="block mb-1 text-sm font-medium text-gray-900">ID Lingkup (Opsional)</label>
                            <input type="number" name="lingkup_id" class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-lg focus:ring-primary-500 focus:border-primary-500 block w-full p-2.5" placeholder="ID Prodi/Blok">
                            <p class="text-[10px] text-gray-500 mt-1">Masukkan ID program studi (contoh: 1) jika lingkup = prodi, atau ID blok gedung jika lingkup = blok.</p>
                        </div>
                        <div class="col-span-2 flex items-center">
                            <input type="checkbox" name="is_active" value="1" id="is_active_new" checked class="w-4 h-4 text-teal-600 bg-gray-100 border-gray-300 rounded focus:ring-teal-500">
                            <label for="is_active_new" class="ms-2 text-sm font-medium text-gray-900">Status Aktif Menjabat</label>
                        </div>
                    </div>
                    <button type="submit" class="text-white inline-flex items-center bg-utama hover:bg-teal-800 focus:ring-4 focus:outline-none focus:ring-teal-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center">
                        <i class="ri-add-line text-md mr-1"></i>Simpan Pejabat
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection
