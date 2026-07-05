const passwordInput = document.getElementById("passwordInput"),
    togglePassword = document.getElementById("togglePassword"),
    showEye = document.getElementById("showEye");
togglePassword.addEventListener("click", function () {
    "password" === passwordInput.type
        ? ((passwordInput.type = "text"),
          showEye.classList.remove("ri-eye-line"),
          showEye.classList.add("ri-eye-off-line"))
        : ((passwordInput.type = "password"),
          showEye.classList.remove("ri-eye-off-line"),
          showEye.classList.add("ri-eye-line"));
});
