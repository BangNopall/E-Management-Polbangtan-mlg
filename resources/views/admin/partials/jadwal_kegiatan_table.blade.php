@foreach ($daftarJadwalKegiatanAsrama as $jadwal)
    <tr class="border-b">
        <td class="px-4 py-3">{{ $loop->iteration }}</td>
        <td class="px-4 py-3">{{ $jadwal->jenis_kegiatan }}</td>
        <td class="px-4 py-3 whitespace-nowrap">{{ $jadwal->formatted_date }}</td>
        <td class="px-4 py-3">{{ $jadwal->blokRuangan->name }}</td>
        <td class="px-4 py-3">
            <form action="{{ route('admin.editJadwalKegiatanAsrama', $jadwal->id) }}" method="post">
                @csrf
                <div class="flex relative items-center">
                    <input type="time" name="mulai_acara" class="mulai_acara_input"
                        id="timeinput1_{{ $loop->iteration }}" value="{{ $jadwal->mulai_acara }}"
                        class="text-sm p-1 rounded cursor-not-allowed" disabled>
                    <i class="ri-time-line text-lg -ml-7" id="timelogo1_{{ $loop->iteration }}"></i>
                </div>
        </td>
        <td class="px-4 py-3">
            <div class="flex relative items-center">
                <input type="time" name="selesai_acara" class="selesai_acara_input"
                    id="timeinput2_{{ $loop->iteration }}" value="{{ $jadwal->selesai_acara }}"
                    class="text-sm p-1 rounded cursor-not-allowed" disabled>
                <i class="ri-time-line text-lg -ml-7" id="timelogo2_{{ $loop->iteration }}"></i>
            </div>
        </td>
        <td class="px-4 py-3">
            <div class="flex justify-center items-center gap-2">
                <button id="btnedit_{{ $loop->iteration }}" type="button" class="bg-utama text-white rounded p-2"
                    onclick="enableEdit({{ $loop->iteration }})">Edit</button>
                <button id="btnsimpan_{{ $loop->iteration }}" type="submit" class="bg-green-600 text-white rounded p-2 hidden">Simpan</button>
                </form>
                <div>
                    <form action="{{ route('admin.deleteJadwalKegiatanAsrama', $jadwal->id) }}" method="post">
                        @csrf
                        @include('partials.modals.hapusjadwal')
                        <button type="button" class="bg-red-500 text-white rounded p-2" id="btnhapus_{{ $loop->iteration }}"
                        data-modal-target="hapusdataModal{{ $jadwal->id }}"
                        data-modal-toggle="hapusdataModal{{ $jadwal->id }}">Hapus</button>
                    </form>
                    <button id="btnbatal_{{ $loop->iteration }}" type="button" class="bg-red-500 text-white rounded p-2 hidden"
                        onclick="disableEdit({{ $loop->iteration }})">Batal</button>
                </div>
            </div>
        </td>
    </tr>
@endforeach
