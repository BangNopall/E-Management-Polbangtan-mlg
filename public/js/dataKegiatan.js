function performSearch(e) {
    fetch("/data-kegiatan-wajib/search", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({ search: e }),
    })
        .then((e) => {
            if (!e.ok) throw Error(`HTTP error! Status: ${e.status}`);
            return e.json();
        })
        .then((e) => {
            document.getElementById("result").innerHTML = e.table;
        })
        .catch((e) => console.error("Error:", e));
}
