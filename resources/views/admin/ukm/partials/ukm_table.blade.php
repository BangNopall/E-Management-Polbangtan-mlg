<div class="overflow-x-auto">
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th scope="col" class="px-4 py-3">No</th>
                <th scope="col" class="px-4 py-3">Nama UKM</th>
                <th scope="col" class="px-4 py-3">Jumlah Anggota</th>
                <th scope="col" class="px-4 py-3">Pelatih</th>
                <th scope="col" class="px-4 py-3">Status</th>
                <th scope="col" class="px-4 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($ukms as $index => $ukm)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">
                        {{ $ukms->firstItem() + $index }}
                    </td>
                    <td class="px-4 py-3 font-medium text-gray-900">
                        <a href="{{ route('admin.ukm.show', $ukm->id) }}" class="text-teal-700 hover:underline font-semibold">
                            {{ $ukm->nama }}
                        </a>
                        @if ($ukm->deskripsi)
                            <div class="text-xs text-gray-500 truncate max-w-xs">{{ $ukm->deskripsi }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                            {{ $ukm->members_count }} Anggota
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if ($ukm->pelatih->first() && $ukm->pelatih->first()->user)
                            {{ $ukm->pelatih->first()->user->name }}
                        @else
                            <span class="text-gray-400 italic">Belum ditunjuk</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($ukm->is_active)
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Aktif</span>
                        @else
                            <span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 flex items-center gap-2">
                        <a href="{{ route('admin.ukm.show', $ukm->id) }}"
                            class="text-white bg-teal-600 hover:bg-teal-700 font-medium rounded text-xs px-3 py-1.5 flex items-center gap-1">
                            <i class="ri-eye-line"></i> Detail
                        </a>
                        <form action="{{ route('admin.ukm.update', $ukm->id) }}" method="post" class="inline">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="nama" value="{{ $ukm->nama }}">
                            <input type="hidden" name="is_active" value="{{ $ukm->is_active ? 0 : 1 }}">
                            <button type="submit"
                                class="text-xs font-medium px-3 py-1.5 rounded text-white {{ $ukm->is_active ? 'bg-amber-600 hover:bg-amber-700' : 'bg-green-600 hover:bg-green-700' }}">
                                {{ $ukm->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                        @unless ($ukm->is_active)
                            <form action="{{ route('admin.ukm.destroy', $ukm->id) }}" method="post" class="inline"
                                onsubmit="return confirm('Hapus UKM {{ $ukm->nama }} secara permanen? Seluruh jadwal dan presensi terkait akan ikut terhapus dan TIDAK BISA dikembalikan.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                    class="text-xs font-medium px-3 py-1.5 rounded text-white bg-red-600 hover:bg-red-700 flex items-center gap-1">
                                    <i class="ri-delete-bin-line"></i> Hapus
                                </button>
                            </form>
                        @endunless
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-4 py-6 text-center text-gray-500">
                        Belum ada data UKM. Tambahkan UKM baru di atas.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

<div class="mt-4">
    {{ $ukms->links() }}
</div>
