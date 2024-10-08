for (var today = new Date(), labels = [], i = 6; i >= 0; i--) {
    var e = new Date(today);
    e.setDate(today.getDate() - i);
    var a = e.toLocaleDateString(void 0, { month: "long" }),
        t = e.getDate();
    labels.push(a + " " + t);
}
const apiUrl = "/getDataPresenceUserLast7Days";
fetch("/getDataPresenceUserLast7Days")
    .then((e) => e.json())
    .then((e) => {
        var e = {
                labels: labels,
                datasets: [
                    {
                        label: "Siswa",
                        data: e.data,
                        backgroundColor: "rgba(255, 99, 132, 0.2)",
                        borderColor: "rgba(255, 99, 132, 1)",
                        borderWidth: 1,
                    },
                ],
            },
            a = {
                type: "line",
                data: e,
                options: {
                    responsive: !0,
                    scales: { x: { beginAtZero: !0 }, y: { beginAtZero: !0 } },
                },
            },
            t = document.getElementById("myChart").getContext("2d"),
            o = new Chart(t, a);
    });
