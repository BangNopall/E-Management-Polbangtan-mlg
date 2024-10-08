@extends('layouts.main')
@section('container')
    <div class="px-3 py-6 md:p-6 mb-5">
        <h1 class="font-semibold text-2xl md:text-3xl mb-3">Detail Laporan</h1>
        <div class="text-gray-600 text-sm">Detail laporan pelanggaran anda</div>
        <div class="border-b border-gray-300 my-5"></div>
        <form action="" method="post">
            <div class="w-full">
                @if ($data->statusPelanggaran == 'progressing')
                    {{-- tampilkan ini klo statusnya progress --}}
                    <div id="alert-additional-content-4"
                        class="p-4 mb-4 text-yellow-800 border border-yellow-300 rounded-lg bg-yellow-50" role="alert">
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                            </svg>
                            <span class="sr-only">Info</span>
                            <h3 class="text-lg font-medium">Peringatan!</h3>
                        </div>
                        <div class="mt-2 mb-4 text-sm">
                            Status laporan pelanggaran saat ini masih dalam tahap <span class="font-medium">Progress</span>.
                            Harap selesaikan hukuman atau sanksi yang diberikan oleh
                            pelatih. jika sudah selesai, harap laporkan pelatih untuk merubah status menjadi <span
                                class="font-medium">Selesai</span>.
                        </div>
                        <div class="flex">
                            <button type="button"
                                class="text-yellow-800 bg-transparent border border-yellow-800 hover:bg-yellow-900 hover:text-white focus:ring-4 focus:outline-none focus:ring-yellow-300 font-medium rounded-lg text-xs px-3 py-1.5 text-center"
                                data-dismiss-target="#alert-additional-content-4" aria-label="Close">
                                Tutup
                            </button>
                        </div>
                    </div>
                @elseif ($data->statusPelanggaran == 'Done')
                    {{-- tampilkan ini klo statusnya selesai --}}
                    <div id="alert-additional-content-3"
                        class="p-4 mb-4 text-green-800 border border-green-300 rounded-lg bg-green-50" role="alert">
                        <div class="flex items-center">
                            <svg class="flex-shrink-0 w-4 h-4 me-2" aria-hidden="true" xmlns="http://www.w3.org/2000/svg"
                                fill="currentColor" viewBox="0 0 20 20">
                                <path
                                    d="M10 .5a9.5 9.5 0 1 0 9.5 9.5A9.51 9.51 0 0 0 10 .5ZM9.5 4a1.5 1.5 0 1 1 0 3 1.5 1.5 0 0 1 0-3ZM12 15H8a1 1 0 0 1 0-2h1v-3H8a1 1 0 0 1 0-2h2a1 1 0 0 1 1 1v4h1a1 1 0 0 1 0 2Z" />
                            </svg>
                            <span class="sr-only">Info</span>
                            <h3 class="text-lg font-medium">Excellent!</h3>
                        </div>
                        <div class="mt-2 mb-4 text-sm">
                            Status laporan pelanggaran saat ini <span class="font-medium">selesai</span>. Jangan mengulangi
                            kembali kesalahan anda dan tetap patuhi peraturan.
                        </div>
                        <div class="flex">
                            <button type="button"
                                class="text-green-800 bg-transparent border border-green-800 hover:bg-green-900 hover:text-white focus:ring-4 focus:outline-none focus:ring-green-300 font-medium rounded-lg text-xs px-3 py-1.5 text-center"
                                data-dismiss-target="#alert-additional-content-3" aria-label="Close">
                                tutup
                            </button>
                        </div>
                    </div>
                @endif

                <div class="bg-white rounded-lg p-2 border-2 mt-3">
                    <div class="text-lg font-medium">Pelanggaran</div>
                    <div class="border-b border-gray-300 my-1"></div>
                    <div class="text-gray-900 text-sm font-medium mt-2">{{ $data->AZ }}. {{ $data->kategori_pelanggaran }}</div>
                    <div class="relative overflow-x-auto rounded mt-2">
                        <table class="w-full text-sm text-left text-gray-500">
                            <thead class="text-xs text-gray-700 uppercase bg-orange-100">
                                <tr>
                                    <th scope="col" class="px-3 py-2">
                                        Bentuk Pelanggaran
                                    </th>
                                    <th scope="col" class="px-3 py-2">
                                        Poin
                                    </th>
                                    <th scope="col" class="px-3 py-2">
                                        Kategori
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-orange-50 border-b">
                                    <td class="px-3 py-2 min-w-[580px]">
                                        {{ $data->jenis_pelanggaran }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $data->poin }}
                                    </td>
                                    <td class="px-3 py-2">
                                        {{ $data->sub_kategori }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="bg-red-50 rounded-lg p-2 border-2 border-red-300 mt-3">
                    <div class="text-lg font-medium">Hukuman / Sanksi</div>
                    <div class="border-b border-gray-300 my-1"></div>
                    <div class="text-gray-900 text-sm mt-2">{{ $data->accDokumenRole }} : {{ $data->accDokumenName }}</div>
                    <div class="block p-2.5 w-full text-sm text-gray-900 bg-red-100 rounded-lg border border-red-500 mt-2">
                        {{ $data->Hukuman }}</div>
                </div>
            </div>
            <button type="button" onclick="window.location.href='/dashboard/riwayat-pelanggaran'"
                class="mt-2 focus:outline-none text-white bg-red-700 hover:bg-red-800 focus:ring-2 focus:ring-red-300 font-medium rounded-lg text-sm px-2.5 py-1.5 me-2">Kembali</button>
        </form>
    </div>
@endsection
