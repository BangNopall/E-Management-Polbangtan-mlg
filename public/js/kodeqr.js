document.addEventListener("DOMContentLoaded", function () {
    let n = document.getElementById("showQRCodeBtn"),
        e = document.getElementById("closeQRCodeBtn"),
        t = document.getElementById("qrCode");
    n.addEventListener("click", function () {
        t.classList.remove("hidden");
    }),
        e.addEventListener("click", function () {
            t.classList.add("hidden");
        });
});
var countdown = 30,
    countdownInterval = setInterval(function () {
        (document.getElementById("waktu").innerText = countdown),
            --countdown < 0 &&
                (clearInterval(countdownInterval), location.reload());
    }, 1e3);
