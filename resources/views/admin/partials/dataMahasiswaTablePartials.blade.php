@foreach ($mahasiswa as $mhs)
    <tr class="bg-white border-b">
        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">
            {{ $loop->iteration }}
        </th>
        <td class="px-6 py-4">
            {{ $mhs->nim }}
        </td>
        <td class="px-6 py-4">
            {{ $mhs->name }}
        </td>
        <td class="px-6 py-4">
            @if ($mhs->kelas_id != null)
                {{ $mhs->kelas->nama_kelas }}
            @else
                -
            @endif
        </td>
        <td class="px-6 py-4">
            @if ($mhs->blok_ruangan_id != null)
                {{ $mhs->blok->name }}{{ $mhs->no_kamar }}
            @else
                -
            @endif
        </td>
        <td class="px-6 py-4">
            {{ $mhs->no_hp }}
        </td>
        <td class="px-6 py-4">
            <div class="flex gap-1">
                <a href="{{ route('admin.dataMahasiswaEdit', $mhs->id) }}"
                    class="bg-utama text-white rounded p-2">Edit</a>
                <form action="{{ route('admin.dataMahasiswaDestroy', $mhs->id) }}" method="post">
                    @method('DELETE')
                    @csrf
                    @include('partials.modals.hapusdata')
                    <button type="button" class="bg-red-500 text-white rounded p-2" id="deleteButton"
                        data-modal-target="hapusdataModal{{ $mhs->id }}"
                        data-modal-toggle="hapusdataModal{{ $mhs->id }}">Hapus</button>
                </form>
            </div>
        </td>
    </tr>
@endforeach
