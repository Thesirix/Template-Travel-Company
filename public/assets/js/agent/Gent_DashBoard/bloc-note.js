$(document).on("click", ".blacknote", function () {
    $("#deteblocknotebutton").attr("blocknoteid", $(this).attr("blacknoteid"));
    $("#deleteblacknote").modal("show");
});

$(document).on("click", "#deteblocknotebutton", function () {
    const id = $(this).attr("blocknoteid");
    $.ajax({
        type: "POST",
        url: route.deleteNote,
        data: {
            _token: route.csrfToken,
            id: id,
        },
        success: function (data) {
            $(".blacknote" + data).hide();
        },
        error: function (data) {},
    });
});

$(document).on("click", "#addBlacknoteBtn", function () {
    const name = $("#blacknotename").val().trim();

    if (name.length === 0) return alert("Le nom ne peut pas être vide.");

    console.log("Tentative d'ajout avec : " + name);

    $.ajax({
        type: "POST",
        url: route.addNote,
        data: {
            _token: route.csrfToken,
            blacknotename: name,
        },
        success: function (data) {
            $("#blacknote-card-body").append(
                `<li class="blacknote blacknote${data.id}" blacknoteid="${data.id}" style="cursor:pointer;">${data.text}</li>`
            );

            $("#addblacknote").modal("hide");

            $("#blacknotename").val("");

            $(".blacknote")
                .off("click")
                .on("click", function () {
                    $("#deteblocknotebutton").attr(
                        "blocknoteid",
                        $(this).attr("blacknoteid")
                    );
                    $("#deleteblacknote").modal("show");
                });
        },
        error: function (xhr) {
            console.error("Erreur AJAX :", xhr);
            alert("Erreur lors de l'ajout.");
        },
    });
});
