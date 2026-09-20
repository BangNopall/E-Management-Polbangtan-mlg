@extends('layouts.main')

@section('container')
<div class="px-3 py-6 md:p-6 mb-5">
    <h1 class="font-semibold text-2xl md:text-3xl mb-3">Data Dosen PA</h1>
    <div class="text-gray-600 text-sm">Manajemen data Dosen Pembimbing Akademik</div>
    <div class="border-b border-gray-300 my-5"></div>

    @if(session('success'))
        <div class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200" role="alert">
            {{ session('success') }}
        </div>
    @endif
    
    @if(session('error'))
        <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200" role="alert">
            {{ session('error') }}
        </div>
    @endif

    <div class="bg-white rounded-lg p-2 sm:p-5 mt-5 border-2 border-gray-200">
        <h2 class="text-lg font-bold text-gray-800 mb-4 border-b pb-2">Import Data Dosen PA</h2>
        <div class="mb-4 text-sm text-gray-600 bg-blue-50 p-4 border border-blue-200 rounded">
            Silahkan upload file excel untuk menambahkan data Dosen PA secara massal.
        </div>
        <form action="{{ route('admin.dosen_pa.import') }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row gap-2 sm:items-end">
            @csrf
            <div class="w-full sm:w-1/2">
                <label class="block text-sm font-medium text-gray-700 mb-1" for="file_dosen">Upload File Excel</label>
                <input type="file" name="file" id="file_dosen" class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none" accept=".xls, .xlsx, .csv" required>
            </div>
            <button type="submit" class="bg-utama text-white px-4 py-2.5 rounded shadow hover:bg-teal-800 transition-all font-semibold text-sm">Import Excel</button>
        </form>

        <div class="mt-6 border border-gray-300 rounded-lg p-4 bg-gray-50">
            <h3 class="text-sm font-semibold text-gray-700 mb-2">Contoh Format Excel:</h3>
            <div class="overflow-x-auto">
                <table class="min-w-full text-xs text-left text-gray-500 bg-white">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-200">
                        <tr>
                            <th scope="col" class="px-4 py-2 border border-gray-300">nama</th>
                            <th scope="col" class="px-4 py-2 border border-gray-300">email</th>
                            <th scope="col" class="px-4 py-2 border border-gray-300">password (Opsional)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white border-b">
                            <td class="px-4 py-2 border border-gray-300">Ir. Budi Santoso</td>
                            <td class="px-4 py-2 border border-gray-300">budi@polbangtanmalang.ac.id</td>
                            <td class="px-4 py-2 border border-gray-300">password</td>
                        </tr>
                        <tr class="bg-white">
                            <td class="px-4 py-2 border border-gray-300">Dr. Siti Aminah</td>
                            <td class="px-4 py-2 border border-gray-300">siti@polbangtanmalang.ac.id</td>
                            <td class="px-4 py-2 border border-gray-300"></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-lg p-2 sm:p-5 mt-5 border-2 border-gray-200">
        <div class="flex flex-col sm:flex-row justify-between sm:items-center mb-4 border-b pb-2 gap-3">
            <h2 class="text-lg font-bold text-gray-800">Daftar Dosen PA</h2>
            <form action="{{ route('admin.dosen_pa.index') }}" method="GET" class="flex">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari Nama Dosen..." class="border-gray-300 rounded-l-md text-sm focus:ring-utama focus:border-utama p-2 border">
                <button type="submit" class="bg-utama text-white px-3 py-2 rounded-r-md hover:bg-teal-800 text-sm"><i class="ri-search-line"></i> Cari</button>
            </form>
        </div>
        <div class="relative overflow-x-auto shadow-md sm:rounded-lg border border-gray-200">
            <table class="w-full text-sm text-left text-gray-500">
                <thead class="text-xs text-white uppercase bg-utama">
                    <tr>
                        <th scope="col" class="px-6 py-3">No</th>
                        <th scope="col" class="px-6 py-3">Nama</th>
                        <th scope="col" class="px-6 py-3">Email</th>
                        <th scope="col" class="px-6 py-3 text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dosenPas as $idx => $dp)
                        <tr class="bg-white border-b hover:bg-gray-50">
                            <td class="px-6 py-4">{{ $dosenPas->firstItem() + $idx }}</td>
                            <td class="px-6 py-4 font-medium text-gray-900">{{ $dp->name }}</td>
                            <td class="px-6 py-4">{{ $dp->email }}</td>
                            <td class="px-6 py-4 flex gap-2 justify-center">
                                <a href="{{ route('admin.dosen_pa.show', $dp->id) }}" class="font-medium text-white bg-blue-600 px-3 py-1 rounded hover:bg-blue-700 transition-all text-xs">Detail</a>
                                <form action="{{ route('admin.dosen_pa.destroy', $dp->id) }}" method="POST" onsubmit="return confirm('Yakin hapus data ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="font-medium text-white bg-red-600 px-3 py-1 rounded hover:bg-red-700 transition-all text-xs">Hapus</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-4 text-center">Belum ada data Dosen PA</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $dosenPas->links() }}
        </div>
    </div>
</div>
@endsection
