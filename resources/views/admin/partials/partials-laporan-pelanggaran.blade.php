{{-- <tr class="bg-white border-b hover:bg-gray-50">
    <td class="px-3 py-4">
        {{ $i++ }}
    </td> --}}
    <td class="px-6 py-4">
        {{ $users->user->nim }}
    </td>
    <td class="px-6 py-4">
        {{ $users->user->name }}
    </td>
    <td class="px-6 py-4">
        {{ $users->date }}
    </td>

    @if ($users->statusPelanggaran == 'scanned')
        <td class="px-6 py-4">
            <div class="text-sm font-medium p-1 w-full text-center bg-red-500 rounded text-white">
                Non Verified
            </div>
        </td>
    @elseif ($users->statusPelanggaran == 'submitted')
        <td class="px-6 py-4">
            <div class="text-sm font-medium p-1 w-full text-center bg-yellow-500 rounded text-white">
                Verify Process
            </div>
        </td>
    @elseif ($users->statusPelanggaran == 'rejected')
        <td class="px-6 py-4">
            <div class="text-sm font-medium p-1 w-full text-center bg-red-500 rounded text-white">
                Verify Rejected
            </div>
        </td>
    @elseif ($users->statusPelanggaran == 'progressing')
        <td class="px-6 py-4">
            <div class="text-sm font-medium p-1 w-full text-center bg-yellow-500 rounded text-white">
                progressing
            </div>
        </td>
    @elseif ($users->statusPelanggaran == 'Done')
        <td class="px-6 py-4">
            <div class="text-sm font-medium p-1 w-full text-center bg-green-700 rounded text-white">
                Done
            </div>
        </td>
    @endif
    <td class="px-6 py-4">
        <a href="/laporan-pelanggaran/open" class="font-medium text-blue-600 hover:underline">Buka</a>
    </td>
</tr>
