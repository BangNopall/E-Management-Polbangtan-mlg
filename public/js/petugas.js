document.addEventListener("DOMContentLoaded", function () {
    let e = document.querySelectorAll(".edit-button"),
        t = document.getElementById("editPopup"),
        d = document.getElementById("closeEditPopup"),
        n = document.getElementById("petugas1"),
        a = document.getElementById("petugas2");
    e.forEach(function (e) {
        e.addEventListener("click", function () {
            let d = e.dataset.petugasId;
            (n.dataset.petugasId = d),
                (a.dataset.petugasId = d),
                console.log("ID Petugas:", d),
                t.classList.remove("hidden");
        });
    }),
        d.addEventListener("click", function () {
            t.classList.add("hidden");
        });
});
