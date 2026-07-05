const sidebarToggle = document.querySelector(".sidebar-toggle"),
    sidebarOverlay = document.querySelector(".sidebar-overlay"),
    sidebarMenu = document.querySelector(".sidebar-menu"),
    main = document.querySelector(".main");
sidebarToggle.addEventListener("click", function (e) {
    e.preventDefault(),
        sidebarMenu.classList.toggle("-translate-x-full"),
        sidebarOverlay.classList.toggle("hidden"),
        main.classList.toggle("active");
}),
    sidebarOverlay.addEventListener("click", function (e) {
        e.preventDefault(),
            main.classList.add("active"),
            sidebarOverlay.classList.add("hidden"),
            sidebarMenu.classList.add("-translate-x-full");
    }),
    document.querySelectorAll(".sidebar-dropdown-toggle").forEach(function (e) {
        e.addEventListener("click", function (t) {
            t.preventDefault();
            let a = e.closest(".group");
            a.classList.contains("selected")
                ? a.classList.remove("selected")
                : (document
                      .querySelectorAll(".sidebar-dropdown-toggle")
                      .forEach(function (e) {
                          e.closest(".group").classList.remove("selected");
                      }),
                  a.classList.add("selected"));
        });
    }),
    window.addEventListener("load", function () {
        let e = document.getElementById("preloader");
        e.style.display = "none";
    });
const popperInstance = {};
function hideDropdown() {
    document.querySelectorAll(".dropdown-menu").forEach(function (e) {
        e.classList.add("hidden");
    });
}
function showPopper(e) {
    popperInstance[e].setOptions(function (e) {
        return {
            ...e,
            modifiers: [
                ...e.modifiers,
                { name: "eventListeners", enabled: !0 },
            ],
        };
    }),
        popperInstance[e].update();
}
function hidePopper(e) {
    popperInstance[e].setOptions(function (e) {
        return {
            ...e,
            modifiers: [
                ...e.modifiers,
                { name: "eventListeners", enabled: !1 },
            ],
        };
    });
}
function handleMediaChange(e) {
    let t = document.querySelector(".main");
    e.matches ? t.classList.remove("active") : t.classList.add("active");
}
document.querySelectorAll(".dropdown").forEach(function (e, t) {
    let a = "popper-" + t,
        n = e.querySelector(".dropdown-toggle"),
        o = e.querySelector(".dropdown-menu");
    (o.dataset.popperId = a),
        (popperInstance[a] = Popper.createPopper(n, o, {
            modifiers: [
                { name: "offset", options: { offset: [0, 8] } },
                { name: "preventOverflow", options: { padding: 24 } },
            ],
            placement: "bottom-end",
        }));
}),
    document.addEventListener("click", function (e) {
        let t = e.target.closest(".dropdown-toggle"),
            a = e.target.closest(".dropdown-menu");
        if (t) {
            let n = t.closest(".dropdown").querySelector(".dropdown-menu"),
                o = n.dataset.popperId;
            n.classList.contains("hidden")
                ? (hideDropdown(), n.classList.remove("hidden"), showPopper(o))
                : (n.classList.add("hidden"), hidePopper(o));
        } else a || hideDropdown();
    });
const mediaQuery = window.matchMedia("(min-width: 768px)");
function updateDateTime() {
    var e = document.getElementById("liveDateTime"),
        t = new Date(),
        a =
            t.getDate() +
            " " +
            getMonthAbbreviation(t.getMonth()) +
            " " +
            t.getFullYear() +
            " - " +
            leadingZero(t.getHours()) +
            ":" +
            leadingZero(t.getMinutes()) +
            ":" +
            leadingZero(t.getSeconds());
    (e.innerText = a), setTimeout(updateDateTime, 1e3);
}
function getMonthAbbreviation(e) {
    return [
        "Jan",
        "Feb",
        "Mar",
        "Apr",
        "May",
        "Jun",
        "Jul",
        "Aug",
        "Sep",
        "Oct",
        "Nov",
        "Dec",
    ][e];
}
function leadingZero(e) {
    return e < 10 ? "0" + e : e;
}
handleMediaChange(mediaQuery),
    mediaQuery.addListener(handleMediaChange),
    document.querySelectorAll("[data-tab]").forEach(function (e) {
        e.addEventListener("click", function (t) {
            t.preventDefault();
            let a = e.dataset.tab,
                n = e.dataset.tabPage,
                o = document.querySelector(
                    '[data-tab-for="' + a + '"][data-page="' + n + '"]'
                );
            document
                .querySelectorAll('[data-tab="' + a + '"]')
                .forEach(function (e) {
                    e.classList.remove("active");
                }),
                document
                    .querySelectorAll('[data-tab-for="' + a + '"]')
                    .forEach(function (e) {
                        e.classList.add("hidden");
                    }),
                e.classList.add("active"),
                o.classList.remove("hidden");
        });
    }),
    updateDateTime();
