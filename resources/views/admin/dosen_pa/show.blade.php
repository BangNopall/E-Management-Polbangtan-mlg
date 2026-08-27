@extends('layouts.main')

@section('container')
<div class="px-3 py-6 md:p-6 mb-5">
    <div class="flex items-center justify-between mb-3">
        <div>
            <h1 class="font-semibold text-2xl md:text-3xl">Detail Dosen PA</h1>
            <div class="text-gray-600 text-sm mt-1">Informasi dan daftar mahasiswa bimbingan Dosen PA</div>
        </div>
        <a href="{{ route('admin.dosen_pa.index') }}" class="bg-gray-500 text-white px-4 py-2 rounded hover:bg-gray-600 transition-all text-sm font-semibold shadow">Kembali</a>
    </div>
    <div class="border-b border-gray-300 my-5"></div>

    <div class="bg-white rounded-lg p-0 sm:p-5 mt-5 border-2 border-gray-200">
        <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Informasi Dosen</h2>
        <div class="text-sm text-gray-700 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <p class="text-gray-500 mb-1">Nama Lengkap</p>
                <p class="font-semibold">{{ $dosenPa->name }}</p>
            </div>
            <div>
                <p class="text-gray-500 mb-1">Email</p>
                <p class="font-semibold">{{ $dosenPa->email }}</p>
            </div>
            <div>
                <p class="text-gray-500 mb-1">Total Mahasiswa Bimbingan</p>
                <p class="font-semibold">{{ $mahasiswaList->count() }} Mahasiswa</p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg p-0 sm:p-5 mt-5 border-2 border-gray-200">
        <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Daftar Mahasiswa Bimbingan</h2>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg border border-gray-200">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-white uppercase bg-utama">
                    <tr>
                        <th scope="col" class="px-6 py-3">No</th>
                        <th scope="col" class="px-6 py-3">Nama Mahasiswa</th>
                        <th scope="col" class="px-6 py-3">NIM</th>
                        <th scope="col" class="px-6 py-3">Kelas / Prodi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mahasiswaList as $idx => $mhs)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $idx + 1 }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $mhs->name }}</td>
                            <td class="px-6 py-4">{{ $mhs->nim ?? '-' }}</td>
                            <td class="px-6 py-4">{{ $mhs->kelas->nama_kelas ?? '-' }} / {{ $mhs->prodi->nama_prodi ?? '-' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center">Belum ada mahasiswa bimbingan</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
