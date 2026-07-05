@foreach ($dataPresensi as $Presensi)
    <tr class="bg-orange-50 whitespace-nowrap">
        <td class="px-3 py-2">
            {{ $loop->iteration }}
        </td>
        <td class="px-3 py-2">
            {{ $Presensi->formatted_date }}
        </td>
        <td class="px-3 py-2">
            {{ $Presensi->presence_keluar }}
        </td>
        <td class="px-3 py-2">
            {{ $Presensi->presence_masuk }}
        </td>
    </tr>
@endforeach
