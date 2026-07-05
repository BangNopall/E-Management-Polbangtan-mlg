document.addEventListener("DOMContentLoaded", function () {
    let e = document.getElementById("passwordInput"),
        t = document.getElementById("togglePassword"),
        o = document.getElementById("showEye");
    t.addEventListener("click", function () {
        "password" === e.type
            ? ((e.type = "text"),
              o.classList.remove("ri-eye-line"),
              o.classList.add("ri-eye-off-line"))
            : ((e.type = "password"),
              o.classList.remove("ri-eye-off-line"),
              o.classList.add("ri-eye-line"));
    });
});
const fotoProfilInput = document.querySelector("#foto-profil"),
    fotoProfilPreview = document.querySelector("#fotoProfil");
fotoProfilInput.addEventListener("change", function () {
    let e = fotoProfilInput.files[0],
        t = new FileReader();
    (t.onload = function () {
        fotoProfilPreview.src = t.result;
    }),
        t.readAsDataURL(e);
});