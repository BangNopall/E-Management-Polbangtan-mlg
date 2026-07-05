document.addEventListener("DOMContentLoaded", function () {
    let e = document.getElementById("showPopup"),
        t = document.getElementById("closePopup"),
        n = document.getElementById("popup");
    e.addEventListener("click", function () {
        n.classList.remove("hidden");
    }),
        t.addEventListener("click", function () {
            n.classList.add("hidden");
        });
});

const passwordInput = document.getElementById("password");
const togglePasswordButton = document.getElementById("togglePassword");
const showEyeIcon = document.getElementById("showEye");

togglePasswordButton.addEventListener("click", () => {
    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        showEyeIcon.classList.remove("ri-eye-line");
        showEyeIcon.classList.add("ri-eye-off-line");
    } else {
        passwordInput.type = "password";
        showEyeIcon.classList.remove("ri-eye-off-line");
        showEyeIcon.classList.add("ri-eye-line");
    }
});
