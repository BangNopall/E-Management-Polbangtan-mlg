const cameraSelect = document.getElementById("cameraSelect"),
    qrCodeReader = new Html5Qrcode("reader");
let beepSound = new Audio("/audio/beep.mp3"),
    config = { fps: 10, qrbox: { width: 250, height: 250 } };

const qrCodeSuccessCallback = (decodedText, decodedResult) => {
    let payload = JSON.parse(decodedText);
    beepSound.play();
    qrCodeReader.stop();

    document.getElementById("user_id").value = payload.user_id;
    document.getElementById("date").value = payload.date;
    document.getElementById("time").value = payload.time;
    document.getElementById("scanner").value = payload.scanner;

    const form = document.getElementById("form");
    form.submit();
};

qrCodeReader.start({ facingMode: "user" }, config, qrCodeSuccessCallback)
    .catch((err) => {
        console.error("Error starting camera:", err);
    });

Html5Qrcode.getCameras()
    .then((cameras) => {
        if (cameras && cameras.length > 0) {
            cameras.forEach((camera) => {
                let opt = document.createElement("option");
                opt.value = camera.id;
                opt.text = camera.label || `Camera ${camera.id}`;
                cameraSelect.appendChild(opt);
            });
            cameraSelect.disabled = false;

            cameraSelect.addEventListener("change", function () {
                let selectedId = cameraSelect.value;
                btnstop.classList.remove("bg-gray-500", "cursor-not-allowed");
                btnstop.classList.add("bg-teal-800");
                btnstop.disabled = false;

                qrCodeReader.clear();
                cameraSelect.disabled = true;

                qrCodeReader
                    .start(
                        selectedId,
                        { fps: 10, qrbox: { width: 350, height: 350 } },
                        (decodedText) => {
                            let payload = JSON.parse(decodedText);
                            beepSound.play();
                            qrCodeReader.stop();

                            document.getElementById("user_id").value = payload.user_id;
                            document.getElementById("date").value = payload.date;
                            document.getElementById("time").value = payload.time;
                            document.getElementById("scanner").value = payload.scanner;

                            const form = document.getElementById("form");
                            form.submit();
                        },
                        (err) => {
                            console.log(`Scan error: ${err}`);
                        }
                    )
                    .catch((err) => {
                        console.error(`Camera switch error: ${err}`);
                    });
            });
        }
    })
    .catch((err) => {
        console.error("Error getting cameras:", err);
    });

const btnstop = document.getElementById("btnstop");
btnstop.addEventListener("click", function () {
    qrCodeReader.stop();
    btnstop.classList.add("bg-gray-500", "cursor-not-allowed");
    btnstop.classList.remove("bg-teal-800");
    btnstop.disabled = true;
    cameraSelect.disabled = false;
});
