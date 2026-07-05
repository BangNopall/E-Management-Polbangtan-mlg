@isset($dataFilterKegiatanByBlok)
    @foreach ($dataFilterKegiatanByBlok as $filter)
        <tr class="border-b text-center">
            <td class="px-4 py-3">{{ $loop->iteration }}</td>
            <td class="px-4 py-3">
                {{ $filter->mulai_acara }}
            </td>
            <td class="px-4 py-3">
                {{ $filter->selesai_acara }}
            </td>
            <td class="px-4 py-3">
                @if ($dataFilterKegiatanByBlok->count() == 1)
                    <input type="checkbox" name="checkBoxIDJadwalKegiatan[]" class="rounded p-1 text-sm" disabled checked >
                    <input type="hidden" name="checkBoxIDJadwalKegiatan[]" value="{{ $filter->id }}">
                @else
                    <input type="checkbox" name="checkBoxIDJadwalKegiatan[]" class="rounded p-1 text-sm" checked
                        value="{{ $filter->id }}">
                @endif
            </td>
        </tr>
    @endforeach
@else
    <tr class="border-b text-center">
        <td class="px-4 py-3" colspan="4">Filter Kosong Lengkapi data terlebih dahulu.</td>
    </tr>
@endisset
