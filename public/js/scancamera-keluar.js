const cameraSelect = document.getElementById("cameraSelect"),
    qrCodeReader = new Html5Qrcode("reader");
let beepSound = new Audio("/audio/beep.mp3"),
    config = { fps: 10, qrbox: { width: 250, height: 250 } };
const qrCodeSuccessCallback = (e, t) => {
    let a;
    try {
        a = JSON.parse(e);
    } catch (err) {
        console.warn("Format QR Code tidak valid:", e);
        return;
    }

    if (!a || typeof a !== "object") return;

    beepSound.play();
    qrCodeReader.stop();

    if (document.getElementById("payload")) {
        document.getElementById("payload").value = a.payload || "";
    }
    if (document.getElementById("user_id")) {
        document.getElementById("user_id").value = a.user_id || "";
    }
    if (document.getElementById("date")) {
        document.getElementById("date").value = a.date || "";
    }
    if (document.getElementById("time")) {
        document.getElementById("time").value = a.time || "";
    }
    if (document.getElementById("scanner")) {
        document.getElementById("scanner").value = a.scanner || "";
    }
    if (document.getElementById("status")) {
        document.getElementById("status").value = a.status || "";
    }

    const form = document.getElementById("form");
    if (form) {
        form.submit();
    }
};
qrCodeReader.start({ facingMode: "user" }, config, qrCodeSuccessCallback),
    Html5Qrcode.getCameras()
        .then((e) => {
            e &&
                e.length > 0 &&
                (e.forEach((e) => {
                    let t = document.createElement("option");
                    (t.value = e.id),
                        (t.text = e.label || `Camera ${e.id}`),
                        cameraSelect.appendChild(t);
                }),
                (cameraSelect.disabled = !1),
                cameraSelect.addEventListener("change", function () {
                    let e = cameraSelect.value;
                    btnstop.classList.remove("bg-gray-500"),
                        btnstop.classList.remove("cursor-not-allowed"),
                        btnstop.classList.add("bg-teal-800"),
                        (btnstop.disabled = !1),
                        qrCodeReader.clear(),
                        console.log(`Selection changed to cameraId: ${e}`),
                        (cameraSelect.disabled = !0),
                        qrCodeReader
                            .start(
                                e,
                                { fps: 10, qrbox: { width: 350, height: 350 } },
                                (e, t) => {
                                    qrCodeSuccessCallback(e, t);
                                    cameraSelect.disabled = !1;
                                },
                                (e) => {
                                    console.log(`KODE QR TIDAK ADA ( ${e} )`);
                                }
                            )
                            .catch((e) => {
                                console.log(`Error = ${e}`);
                            });
                }));
        })
        .catch((e) => {
            console.error("Error getting cameras:", e);
        });
const btnstop = document.getElementById("btnstop");
btnstop.addEventListener("click", function () {
    qrCodeReader.stop(),
        btnstop.classList.add("bg-gray-500"),
        btnstop.classList.add("cursor-not-allowed"),
        btnstop.classList.remove("bg-teal-800"),
        (btnstop.disabled = !0),
        (cameraSelect.disabled = !1);
});
