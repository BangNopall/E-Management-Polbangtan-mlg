function toggleTimeInputs(e) {
    let t = document.getElementById(`waktu_${e}`),
        a = document.getElementById(`kegiatan_${e}`);
    a.checked ? (t.style.display = "flex") : (t.style.display = "none");
}
function enableEdit(rowIdx) {
    document.getElementById(`timeinput1_${rowIdx}`).removeAttribute("disabled");
    document.getElementById(`timeinput2_${rowIdx}`).removeAttribute("disabled");
    document.getElementById(`timeinput1_${rowIdx}`).classList.remove("cursor-not-allowed");
    document.getElementById(`timeinput2_${rowIdx}`).classList.remove("cursor-not-allowed");
    document.getElementById(`timelogo1_${rowIdx}`).classList.add("hidden");
    document.getElementById(`timelogo2_${rowIdx}`).classList.add("hidden");
    document.getElementById(`btnedit_${rowIdx}`).classList.add("hidden");
    document.getElementById(`btnsimpan_${rowIdx}`).classList.remove("hidden");
    document.getElementById(`btnbatal_${rowIdx}`).classList.remove("hidden");
    document.getElementById(`btnhapus_${rowIdx}`).classList.add("hidden");
}
function disableEdit(rowIdx) {
    document.getElementById(`timeinput1_${rowIdx}`).setAttribute("disabled", "disabled");
    document.getElementById(`timeinput2_${rowIdx}`).setAttribute("disabled", "disabled");
    document.getElementById(`timeinput1_${rowIdx}`).classList.add("cursor-not-allowed");
    document.getElementById(`timeinput2_${rowIdx}`).classList.add("cursor-not-allowed");
    document.getElementById(`timelogo1_${rowIdx}`).classList.remove("hidden");
    document.getElementById(`timelogo2_${rowIdx}`).classList.remove("hidden");
    document.getElementById(`btnedit_${rowIdx}`).classList.remove("hidden");
    document.getElementById(`btnsimpan_${rowIdx}`).classList.add("hidden");
    document.getElementById(`btnbatal_${rowIdx}`).classList.add("hidden");
    document.getElementById(`btnhapus_${rowIdx}`).classList.remove("hidden");
}
$(document).ready(function () {
    $(".filter-checkbox, .filter-date").on("change", function () {
        var e = $('.filter-checkbox[name="blok[]"]:checked')
                .map(function () {
                    return this.value;
                })
                .get(),
            t = $('.filter-checkbox[name="kegiatan[]"]:checked')
                .map(function () {
                    return this.value;
                })
                .get(),
            a = $("#date").val();
        $.ajax({
            type: "GET",
            url: "/jadwal-kegiatan-filter",
            data: { blok: e, kegiatan: t, tanggal_kegiatan: a },
            success: function (e) {
                $("tbody").html(e);
            },
            error: function (e) {
                console.log(e);
            },
        });
    }),
        $(document).on("click", "button.btnedit", function () {
            var e = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
                t = $(this).data("id"),
                a = $(this).closest("tr"),
                i = a.find(".mulai_acara_input").val(),
                n = a.find(".selesai_acara_input").val(),
                d =
                    '<form method="POST" action="/jadwal-kegiatan-edit/' +
                    t +
                    '">';
            (d += '<input type="hidden" name="_token" value="' + e + '">'),
                (d +=
                    '<input type="time" name="mulai_acara" class="mulai_acara_input" id="timeinput1" value="' +
                    i +
                    '" class="text-sm p-1 rounded">'),
                (d +=
                    '<input type="time" name="selesai_acara" class="selesai_acara_input" id="timeinput2" value="' +
                    n +
                    '" class="text-sm p-1 rounded">'),
                (d += '<button type="submit" class="p-1">Simpan</button>'),
                (d += "</form>"),
                a.find("td:last-child").append(d);
        });
});
$(document).ready(function() {
    // Function to handle the AJAX request
    function ajaxDataKegiatanByBlokFilter() {
        var tanggal_kegiatan = $('#tanggal_kegiatan_filter')
            .val();
        var blok_id = $('#blok_id')
            .val();
        var jenis_kegiatan = $('#jenis_kegiatan')
            .val();

        // Make an AJAX request
        $.ajax({
            type: 'GET',
            url: '/filtering-jadwal-kegiatan-by-blok',
            data: {
                tanggal_kegiatan: tanggal_kegiatan,
                blok_id: blok_id,
                jenis_kegiatan: jenis_kegiatan,
            },
            success: function(response) {
                if (response.message != null) {
                    console.log(response.message);
                    // Display a message for empty filter
                    $("#filteringJadwalKegiatanByBlok").html(
                        '<tr class="border-b text-center"><td class="px-4 py-3" colspan="4">Data Tidak Ditemukan</td></tr>'
                    );
                } else {
                    $("#filteringJadwalKegiatanByBlok").html(response.table);
                }
            },
            error: function(error) {
                console.error(error);
            }
        });
    }

    // Attach the change event to each form field
    $('#tanggal_kegiatan_filter, #blok_id, #jenis_kegiatan').on('input', function() {
        // Trigger the AJAX request on input
        ajaxDataKegiatanByBlokFilter();
    });
});
