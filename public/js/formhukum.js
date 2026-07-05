var checkboxes = document.querySelectorAll('input[name="jenis_pelanggaran[]"]');
checkboxes.forEach(function (e) {
    e.addEventListener("change", function () {
        checkboxes.forEach(function (c) {
            c !== e && (c.checked = !1);
        });
    });
});
