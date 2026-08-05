<div class="overflow-x-auto">
    {{-- Isu #1: tombol 'Aktifkan Semua' massal di header tabel anggota.
         Hanya aktif apabila UKM induknya berstatus aktif. --}}
    @if ($ukm->is_active && $ukm->members->where('status', 'nonaktif')->count() > 0)
        <div class="mb-3 flex justify-end">
            <form action="{{ route('admin.ukm.anggota.aktifkanSemua', $ukm->id) }}" method="post" class="inline"
                onsubmit="return confirm('Aktifkan kembali seluruh anggota nonaktif di UKM {{ $ukm->nama }}?')">
                @csrf
                @method('PATCH')
                <button type="submit"
                    class="text-xs font-medium px-3 py-1.5 rounded text-white bg-teal-600 hover:bg-teal-700 flex items-center gap-1">
                    <i class="ri-checkbox-circle-line"></i> Aktifkan Semua Anggota
                </button>
            </form>
        </div>
    @endif
    <table class="w-full text-sm text-left text-gray-500">
        <thead class="text-xs text-gray-700 uppercase bg-gray-50">
            <tr>
                <th scope="col" class="px-4 py-3">No</th>
                <th scope="col" class="px-4 py-3">Nama</th>
                <th scope="col" class="px-4 py-3">NIM / Email</th>
                <th scope="col" class="px-4 py-3">Peran</th>
                <th scope="col" class="px-4 py-3">Status</th>
                <th scope="col" class="px-4 py-3">Tanggal Bergabung</th>
                <th scope="col" class="px-4 py-3">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($anggotas as $index => $member)
                <tr class="bg-white border-b hover:bg-gray-50">
                    <td class="px-4 py-3 font-medium text-gray-900">{{ $loop->iteration }}</td>
                    <td class="px-4 py-3 font-medium text-gray-900">
                        {{ $member->user->name ?? 'User Tidak Ditemukan' }}
                    </td>
                    <td class="px-4 py-3">
                        <div>{{ $member->user->nim ?? '-' }}</div>
                        <div class="text-xs text-gray-400">{{ $member->user->email ?? '-' }}</div>
                    </td>
                    <td class="px-4 py-3">
                        @if ($member->peran === 'pelatih')
                            <span class="bg-purple-100 text-purple-800 text-xs font-medium px-2.5 py-0.5 rounded">Pelatih</span>
                        @elseif ($member->peran === 'pembina')
                            <span class="bg-indigo-100 text-indigo-800 text-xs font-medium px-2.5 py-0.5 rounded">Pembina</span>
                        @else
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">Anggota</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if ($member->status === 'aktif')
                            <span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Aktif</span>
                        @else
                            <span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">Nonaktif</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        {{ $member->tanggal_bergabung ? \Carbon\Carbon::parse($member->tanggal_bergabung)->format('d M Y') : '-' }}
                    </td>
                    <td class="px-4 py-3 flex items-center gap-2">
                        @if ($member->status === 'nonaktif' && $ukm->is_active)
                            <form action="{{ route('admin.ukm.anggota.aktifkan', [$ukm->id, $member->id]) }}" method="post" class="inline">
                                @csrf
                                @method('PATCH')
                                <button type="submit" class="text-xs text-teal-600 hover:text-teal-800 font-medium flex items-center gap-1">
                                    <i class="ri-checkbox-circle-line"></i> Aktifkan
                                </button>
                            </form>
                        @endif
                        <form action="{{ route('admin.ukm.anggota.destroy', [$ukm->id, $member->id]) }}" method="post"
                            onsubmit="return confirm('Keluarkan {{ $member->user->name ?? 'anggota' }} dari UKM ini?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs text-red-600 hover:text-red-800 font-medium flex items-center gap-1">
                                <i class="ri-delete-bin-line"></i> Keluarkan
                            </button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="px-4 py-6 text-center text-gray-500">
                        Belum ada anggota terdaftar di UKM ini.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
    <div class="mt-4">
        {{ $anggotas->links() }}
    </div>
</div>
