@foreach ($dataKegiatanSenam as $dataSenam)
    <tr class="bg-orange-50 whitespace-nowrap">
        <td class="px-3 py-2">
            {{ $loop->iteration }}
        </td>
        <td class="px-3 py-2">
            {{ $dataSenam->formatted_date }}
        </td>
        <td class="px-3 py-2">
            {{ $dataSenam->jadwalKegiatanAsrama->mulai_acara }}
        </td>
        <td class="px-3 py-2">
            {{ $dataSenam->jadwalKegiatanAsrama->selesai_acara }}
        </td>
        <td class="px-3 py-2 justify-center items-center flex flex-col">
            <div id="base_{{ $loop->index }}">
                @if ($dataSenam->status_kehadiran == 'Hadir')
                    <div class="bg-green-500 w-[100px] text-white p-1 rounded text-center">
                        Hadir</div>
                @elseif ($dataSenam->status_kehadiran == 'Alpha')
                    <div class="bg-red-500 w-[100px] text-white p-1 rounded text-center">
                        Alpha</div>
            @elseif ($dataSenam->status_kehadiran == 'Izin')
                <div class="bg-yellow-500 w-[100px] text-white p-1 rounded text-center">
                    Izin</div>
                @endif
            </div>
            <select name="status_kehadiran[]" id="edit_{{ $loop->index }}" class="text-center text-xs rounded p-1 border border-utama hidden">
                <option hidden value="{{ $dataSenam->status_kehadiran }}" selected>{{ $dataSenam->status_kehadiran }}</option>
                <option value="Hadir">Hadir</option>
                <option value="Alpha">Alpha</option>
                <option value="Izin">Izin</option>
            </select>
            <input type="hidden" name="presensi_senam_ids[]" value="{{ $dataSenam->id }}">
        </td>
        <td class="px-3 py-2">
            {{ $dataSenam->jam_kehadiran }}
        </td>
    </tr>
@endforeach
