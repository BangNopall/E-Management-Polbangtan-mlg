var a = $('meta[name="csrf-token"]').attr("content");
$(document).ready(function () {
    $("#blok_ruangan").change(function () {
        var n = $("#blok_ruangan").val();
        $.ajax({
            type: "post",
            url: "/data-mahasiswa/get-nomor-ruangan",
            data: { blok_ruangan: n, _token: a },
            success: function (a) {
                !(function updateNomorRuangan(a) {
                    var n = $("#nomor_ruangan");
                    n.empty(),
                        n.append(
                            "<option selected hidden>Pilih Ruangan</option>"
                        ),
                        $.each(a, function (a, t) {
                            n.append(
                                '<option value="' + t + '">' + t + "</option>"
                            );
                        });
                })(a);
            },
            error: function (a, n, t) {
                console.error(t);
            },
        });
    }),
        $("#blok_ruangan").change(function () {
            var n = $("#blok_ruangan").val();
            $.ajax({
                type: "post",
                url: "/data-mahasiswa/search-blokruangan",
                data: { blok_ruangan: n, _token: a },
                success: function (a) {
                    document.getElementById("result").innerHTML = a.table;
                },
            });
        }),
        $("#nomor_ruangan").change(function () {
            var n = $("#blok_ruangan").val(),
                t = $("#nomor_ruangan").val();
            $.ajax({
                type: "post",
                url: "/data-mahasiswa/search-mahasiswa-by-data",
                data: { blok_ruangan: n, nomor_ruangan: t, _token: a },
                success: function (a) {
                    !(function updateTable(a) {
                        var n = $("#result");
                        n.empty(),
                            $.each(a, function (a, t) {
                                var e = t.kelas.nama_kelas || "-",
                                    o = t.no_kamar || "-",
                                    r = t.blok.name || "-" + o,
                                    s = t.no_hp || "-",
                                    u =
                                        '<tr class="bg-white border-b"><th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap">' +
                                        (a + 1) +
                                        '</th><td class="px-6 py-4">' +
                                        (t.nim || "-") +
                                        '</td><td class="px-6 py-4">' +
                                        t.name +
                                        '</td><td class="px-6 py-4">' +
                                        e +
                                        '</td><td class="px-6 py-4">' +
                                        r +
                                        '</td><td class="px-6 py-4">' +
                                        s +
                                        '</td><td class="px-6 py-4"><div class="flex gap-1"><a href="/data-mahasiswa/edit/' +
                                        t.id +
                                        '" class="bg-utama text-white rounded p-2">Edit</a><form action="/data-mahasiswa/' +
                                        t.id +
                                        '" method="post"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}"><button class="bg-red-500 text-white rounded p-2">Hapus</button></form></div></td></tr>';
                                n.append(u);
                            });
                    })(a);
                },
            });
        }),
        $(document).ready(function () {
            $("#search-input").on("input", function () {
                var n = $(this).val();
                $.ajax({
                    type: "post",
                    url: "/data-mahasiswa/searchbar-mahasiswa",
                    data: { search_term: n, _token: a },
                    success: function (a) {
                        document.getElementById("result").innerHTML = a.table;
                    },
                    error: function (a, n, t) {
                        console.error(t);
                    },
                });
            });
        });
});
