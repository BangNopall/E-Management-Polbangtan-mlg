var keluar = [];
document.addEventListener("DOMContentLoaded", function () {
    fetch("/get-presence-date")
        .then((e) => e.json())
        .then((e) => {
            e.forEach((e) => {
                keluar.push({ title: "Keluar", start: e.presence_date });
            });
            var t = document.getElementById("calendar");
            new FullCalendar.Calendar(t, {
                height: 450,
                initialView: "dayGridMonth",
                headerToolbar: {
                    left: "prev,next",
                    center: "title",
                    right: "",
                },
                events: keluar,
                eventContent: function (e) {
                    var t = document.createElement("div");
                    return (
                        (t.innerHTML = e.event.title),
                        (t.className = " px-1 text-white text-center text-sm"),
                        t.classList.add("bg-red-500"),
                        { domNodes: [t] }
                    );
                },
            }).render();
        });
});
