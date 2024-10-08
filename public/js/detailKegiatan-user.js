document.addEventListener("DOMContentLoaded", function () {
    var e = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    let a = document.getElementById("filterDropdown");
    a.addEventListener("change", function () {
        let t = new FormData(a[0]),
            l = document.querySelectorAll(
                'input[name="status_kehadiran"]:checked'
            ),
            n = document.querySelector('input[name="tanggal_kegiatan"]'),
            r = document.querySelector('input[name="user_id"]');
        l.forEach((e) => {
            t.append("status_kehadiran[]", e.value);
        }),
            n && t.append("tanggal_kegiatan", n.value),
            r && t.append("user_idInput", r.value),
            fetch("/data-kegiatan-wajib-filter", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": e },
                body: t,
            })
                .then((e) => e.json())
                .then((e) => {
                    void 0 !== e.tableUpacara && "" !== e.tableUpacara
                        ? ($("#tableUpacara").html(e.tableUpacara),
                          $("#tableUpacaraNull").html(""))
                        : ($("#tableUpacara").html(""),
                          $("#tableUpacaraNull").html(
                              '<div class="text-center py-5 text-xs w-full">Riwayat belum tersedia.</div>'
                          )),
                        void 0 !== e.tableApel && "" !== e.tableApel
                            ? ($("#tableApel").html(e.tableApel),
                              $("#tableApelNull").html(""))
                            : ($("#tableApel").html(""),
                              $("#tableApelNull").html(
                                  '<div class="text-center py-5 text-xs w-full">Riwayat belum tersedia.</div>'
                              )),
                        void 0 !== e.tableSenam && "" !== e.tableSenam
                            ? ($("#tableSenam").html(e.tableSenam),
                              $("#tableSenamNull").html(""))
                            : ($("#tableSenam").html(""),
                              $("#tableSenamNull").html(
                                  '<div class="text-center py-5 text-xs w-full">Riwayat belum tersedia.</div>'
                              ));
                })
                .catch((e) => console.error("Error:", e));
    });
});

function toggleEditMode() {
    var btnEdit = document.getElementById('btnedit');
    var isEditMode = btnEdit.classList.contains('bg-red-500'); // Assuming red color represents edit mode

    var elements = document.querySelectorAll('[id^=edit]');
    elements.forEach(function(element) {
        element.value = element.previousElementSibling.innerText.trim();
        element.classList.toggle('hidden', isEditMode);
        document.getElementById('simpan1').classList.toggle('hidden', isEditMode);
        document.getElementById('simpan2').classList.toggle('hidden', isEditMode);
        document.getElementById('simpan3').classList.toggle('hidden', isEditMode);
    });

    var baseElements = document.querySelectorAll('[id^=base]');
    baseElements.forEach(function(element) {
        element.classList.toggle('hidden', !isEditMode);
    });

    // Toggle button color based on edit mode
    btnEdit.classList.toggle('bg-red-500', !isEditMode);
    btnEdit.classList.toggle('bg-orange-300', isEditMode);
}