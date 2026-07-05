const dropdownRadioButton = $("#dropdownRadioButton"),
    filterRadioButtons = $('input[name="filter-radio"]');
filterRadioButtons.on("change", function () {
    $(this).val();
    let t = $(this).next("label").text();
    dropdownRadioButton.find("span#filterLabel").text(t);
}),
    document.addEventListener("DOMContentLoaded", function () {
        let t = document.getElementById("filterSelect");
        t.addEventListener("change", function () {
            this.form.submit();
        });
    });
