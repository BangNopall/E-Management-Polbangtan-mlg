$(document).ready(function () {
    $("#blok_ruangan").on("change", function () {
        var a = $(this).val();
        $.ajax({
            url: "/absensi-mahasiswa/getNomorRuangan",
            type: "GET",
            data: { blok_ruangan: a },
            success: function (a) {
                $("#nomor_ruangan").empty(),
                    $("#nomor_ruangan").append(
                        "<option value='' selected hidden>-</option>"
                    ),
                    $.each(a, function (a, e) {
                        $("#nomor_ruangan").append(
                            "<option selected hidden>Pilih ruangan</option><option value='" +
                                e +
                                "'>" +
                                e +
                                "</option>"
                        );
                    });
            },
            error: function () {
                console.log(
                    "Terjadi kesalahan saat mengambil data nomor ruangan."
                );
            },
        });
    }),
        $("#nomor_ruangan").on("change", function () {
            var a, e;
            (a = $("#blok_ruangan").val()),
                (e = $("#nomor_ruangan").val()),
                $.ajax({
                    url: "/absensi-mahasiswa/getDataAbsen",
                    type: "GET",
                    data: { blok_ruangan: a, nomor_ruangan: e },
                    success: function (a) {
                        var e, n;
                        (e = a),
                            (n = $("#result")).empty(),
                            $.each(e.data, function (a, e) {
                                var t = "";
                                e.status
                                    ? "didalam" === e.status
                                        ? (t =
                                              '<div class="bg-green-500 text-white rounded p-1 text-center">Di dalam Asrama</div>')
                                        : "diluar" === e.status
                                        ? (t =
                                              '<div class="bg-red-500 text-white rounded p-1 text-center">Di luar Asrama</div>')
                                        : "telat" === e.status &&
                                          (t =
                                              '<div class="bg-orange-500 text-white rounded p-1 text-center">Telat Masuk Asrama</div>')
                                    : (t =
                                          '<div class="bg-red-500 text-white rounded p-1 text-center">Belum Absen</div>');
                                var s = "";
                                if (
                                    e.presence_keluar &&
                                    "-" !== e.presence_keluar
                                ) {
                                    var r = e.presence_keluar.split(",");
                                    $.each(r, function (a, e) {
                                        s +=
                                            '<div class="py-1">' + e + "</div>";
                                    });
                                } else s = "-";
                                var o = "";
                                if (
                                    e.presence_masuk &&
                                    "-" !== e.presence_masuk
                                ) {
                                    var i = e.presence_masuk.split(",");
                                    $.each(i, function (a, e) {
                                        o +=
                                            '<div class="py-1">' + e + "</div>";
                                    });
                                } else o = "-";
                                var d =
                                    '<tr class="bg-white border-b"><th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">' +
                                    (a + 1) +
                                    '</th><td class="px-6 py-4">' +
                                    e.nim +
                                    '</td><td class="px-6 py-4">' +
                                    e.name +
                                    '</td><td class="px-6 py-4">' +
                                    t +
                                    '</td><td class="px-6 py-4">' +
                                    s +
                                    '</td><td class="px-6 py-4">' +
                                    o +
                                    '</td><td class="px-6 py-4">' +
                                    new Date().toLocaleDateString("en-US", {
                                        day: "numeric",
                                        month: "long",
                                        year: "numeric",
                                    }) +
                                    "<br></td></tr>";
                                n.append(d);
                            });
                    },
                    error: function () {
                        console.log(
                            "Terjadi kesalahan saat mengambil data absen."
                        );
                    },
                });
        });
});
