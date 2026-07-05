function toggleDateInput() {
    var e = document.getElementById("kelasSelect").value;
    (document.getElementById("weekly").style.display = "none"),
        (document.getElementById("monthly").style.display = "none"),
        (document.getElementById("weeklyInput").value = ""),
        (document.getElementById("monthlyInput").value = ""),
        "mingguan" === e
            ? (document.getElementById("weekly").style.display = "block")
            : "bulanan" === e &&
              (document.getElementById("monthly").style.display = "block");
}
