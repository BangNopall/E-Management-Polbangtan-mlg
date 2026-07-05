@foreach ($Users as $user)
    <tr class="bg-white border-b hover:bg-gray-50">
        <td class="px-3 py-4">
            {{ $loop->iteration }}
        </td>
        <td class="px-6 py-4">
            {{ $user->nim }}
        </td>
        <td class="px-6 py-4">
            {{ $user->name }}
        </td>
        <td class="px-6 py-4">
            @if ($user->kelas_id == null)
                -
            @else
                {{ $user->kelas->nama_kelas }}
            @endif
        </td>
        <td class="px-6 py-4">
            @if ($user->blok_ruangan_id == null)
                -
            @else
                {{ $user->blok->name }}
            @endif
        </td>
        <td class="px-6 py-4">
            <a href="{{ route('admin.dataKegiatanWajibDetail', $user->id) }}"
                class="font-medium text-blue-600 hover:underline">Buka</a>
        </td>
    </tr>
@endforeach
