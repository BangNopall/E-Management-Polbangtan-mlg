<!-- Modal Detail Kegiatan UKM -->
<div id="modal-detail-kegiatan-ukm" tabindex="-1" aria-hidden="true"
    class="fixed top-0 left-0 right-0 z-50 hidden w-full p-4 overflow-x-hidden overflow-y-auto md:inset-0 h-[calc(100%-1rem)] max-h-full flex items-center justify-center bg-black/50">
    <div class="relative w-full max-w-lg max-h-full">
        <div class="relative bg-white rounded-lg shadow border-2">
            <!-- Modal header -->
            <div class="flex items-center justify-between p-4 border-b rounded-t bg-gray-50">
                <h3 class="text-lg font-semibold text-gray-900" id="detail-modal-judul">
                    Detail Kegiatan UKM
                </h3>
                <button type="button"
                    class="text-gray-400 bg-transparent hover:bg-gray-200 hover:text-gray-900 rounded-lg text-sm w-8 h-8 ms-auto inline-flex justify-center items-center"
                    data-modal-hide="modal-detail-kegiatan-ukm" onclick="closeModalDetailKegiatan()">
                    <i class="ri-close-line text-xl"></i>
                    <span class="sr-only">Tutup modal</span>
                </button>
            </div>
            <!-- Modal body -->
            <div class="p-6 space-y-4 text-sm text-gray-700">
                <div class="grid grid-cols-3 gap-2">
                    <span class="font-medium text-gray-500">Jenis Kegiatan</span>
                    <span class="col-span-2 font-semibold capitalize text-gray-900" id="detail-modal-jenis">-</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="font-medium text-gray-500">Tanggal</span>
                    <span class="col-span-2 text-gray-900" id="detail-modal-tanggal">-</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="font-medium text-gray-500">Waktu Acara</span>
                    <span class="col-span-2 text-gray-900" id="detail-modal-waktu">-</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="font-medium text-gray-500">Lokasi</span>
                    <span class="col-span-2 text-gray-900" id="detail-modal-lokasi">-</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="font-medium text-gray-500">Status Verifikasi</span>
                    <span class="col-span-2" id="detail-modal-status">-</span>
                </div>
                <div class="grid grid-cols-3 gap-2">
                    <span class="font-medium text-gray-500">Catatan Pembina</span>
                    <span class="col-span-2 text-gray-700 bg-gray-50 p-2 rounded border" id="detail-modal-catatan">-</span>
                </div>
            </div>
            <!-- Modal footer -->
            <div class="flex items-center justify-end p-4 border-t border-gray-200 rounded-b bg-gray-50">
                <button type="button" onclick="closeModalDetailKegiatan()"
                    class="text-gray-500 bg-white hover:bg-gray-100 focus:ring-4 focus:outline-none focus:ring-teal-300 rounded-lg border border-gray-200 text-sm font-medium px-5 py-2.5 hover:text-gray-900">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    function openModalDetailKegiatan(data) {
        document.getElementById('detail-modal-judul').innerText = data.judul || 'Detail Kegiatan';
        document.getElementById('detail-modal-jenis').innerText = (data.jenis === 'kegiatan_wajib') ? 'Kegiatan Wajib' : 'Latihan';
        document.getElementById('detail-modal-tanggal').innerText = data.tanggal || '-';
        document.getElementById('detail-modal-waktu').innerText = (data.mulai_acara && data.selesai_acara) ? `${data.mulai_acara} - ${data.selesai_acara}` : '-';
        document.getElementById('detail-modal-lokasi').innerText = data.lokasi || '-';
        document.getElementById('detail-modal-catatan').innerText = data.catatan_pembina || '-';

        const statusEl = document.getElementById('detail-modal-status');
        if (data.status_verifikasi === 'disetujui') {
            statusEl.innerHTML = '<span class="bg-green-100 text-green-800 text-xs font-medium px-2.5 py-0.5 rounded">Disetujui</span>';
        } else if (data.status_verifikasi === 'menunggu') {
            statusEl.innerHTML = '<span class="bg-amber-100 text-amber-800 text-xs font-medium px-2.5 py-0.5 rounded">Menunggu</span>';
        } else if (data.status_verifikasi === 'ditolak') {
            statusEl.innerHTML = '<span class="bg-red-100 text-red-800 text-xs font-medium px-2.5 py-0.5 rounded">Ditolak</span>';
        } else {
            statusEl.innerHTML = '<span class="bg-gray-100 text-gray-800 text-xs font-medium px-2.5 py-0.5 rounded">Draft</span>';
        }

        document.getElementById('modal-detail-kegiatan-ukm').classList.remove('hidden');
    }

    function closeModalDetailKegiatan() {
        document.getElementById('modal-detail-kegiatan-ukm').classList.add('hidden');
    }
</script>
