function showTab(t, e) {
    for (
        var a = document.getElementsByClassName("tab-content"), o = 0;
        o < a.length;
        o++
    )
        a[o].style.display = "none";
    for (
        var r = document.getElementsByClassName("tab-button"), o = 0;
        o < r.length;
        o++
    )
        r[o].classList.remove("text-utama", "border-b-2", "border-utama"),
            r[o].classList.add(
                "text-gray-500",
                "hover:text-utama",
                "hover:border-utama",
                "hover:border-b",
                "transition-all",
                "duration-500",
                "ease-in-out"
            );
    (document.getElementById(t + "Tab").style.display = "block"),
        (document.getElementById(t).style.display = "block"),
        e.classList.add("text-utama", "border-b-2", "border-utama"),
        e.classList.remove(
            "text-gray-500",
            "hover:text-utama",
            "hover:border-utama",
            "hover:border-b",
            "transition-all",
            "duration-500",
            "ease-in-out"
        );
}
document.addEventListener("DOMContentLoaded", function () {
    var t = document.getElementById("importForm"),
        e = document.getElementById("loadingAnimation");
    t.addEventListener("submit", function () {
        e.classList.remove("hidden");
    });
});
