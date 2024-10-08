var csrfToken = $('meta[name="csrf-token"]').attr("content");
$(document).ready(function () {
    $(document).ready(function () {
        $("#search-form").submit(function (a) {
            a.preventDefault();
            var t = $("#searchInput").val();
            $.ajax({
                type: "post",
                url: "/data-pelanggaran/searchdatapelanggaran",
                data: { searchInput: t, _token: csrfToken },
                success: function (a) {
                    var t, e, r;
                    (t = a),
                        (e = $("#result")).empty(),
                        (r = 1),
                        $.each(t, function (a, t) {
                            var n = t[0].user,
                                s = t[0].total_pelanggaran,
                                c =
                                    '<tr class="bg-white border-b hover:bg-gray-50"><td class="px-3 py-4">' +
                                    r++ +
                                    '</td><td class="px-6 py-4">' +
                                    n.nim +
                                    '</td><td class="px-6 py-4">' +
                                    n.name +
                                    '</td><td class="px-6 py-4">' +
                                    n.kelas.nama_kelas +
                                    '</td><td class="px-6 py-4">' +
                                    s +
                                    'x</td><td class="px-6 py-4"><a href="/data-pelanggaran/detail/' +
                                    n.id +
                                    '" class="font-medium text-blue-600 hover:underline">Buka</a></td></tr>';
                            e.append(c);
                        });
                },
                error: function (a, t, e) {
                    console.error(e);
                },
            });
        });
    });
});
