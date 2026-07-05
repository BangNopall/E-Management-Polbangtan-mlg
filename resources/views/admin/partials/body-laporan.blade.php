<tr class="bg-white border-b hover:bg-gray-50">
    <td class="px-3 py-4">
        {{ $i++ }}
    </td>
    <td class="px-6 py-4">
        {{ $data->user->nim }}
    </td>
    <td class="px-6 py-4 min-w-[280px]">
        {{ $data->user->name }}
    </td>
    <td class="px-6 py-4 min-w-[180px]">
        {{ date('d F Y', strtotime($data->date )) }}
    </td>
    <td class="px-6 py-4]">
        @if($data->statusPelanggaran == 'submitted')
            <div class="text-sm font-medium p-1 w-full text-center bg-gray-300 rounded text-gray-700">
                Submitted
            </div>
        @elseif($data->statusPelanggaran == 'rejected')
            <div class="text-sm font-medium p-1 w-full text-center bg-red-500 rounded text-white">
                Rejected
            </div>
        @elseif($data->statusPelanggaran == 'progressing')
            <div class="text-sm font-medium p-1 w-full text-center bg-yellow-500 rounded text-white">
                Progressing
            </div>
        @endif
    </td>
    <td class="px-6 py-4">
        <a href="{{ route('admin.laporanPelanggaranOpen', $data->id) }}" class="font-medium text-blue-600 hover:underline">Buka</a>
    </td>
</tr>
