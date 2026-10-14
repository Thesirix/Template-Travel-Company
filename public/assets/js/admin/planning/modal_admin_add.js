let modeMaladieAdminActif = false;
let ancienneDateDebutAdmin = "";
let ancienneDateFinAdmin = "";

$("#btnMaladieToggleAdmin").on("click", function () {
    modeMaladieAdminActif = !modeMaladieAdminActif;

    $("#form-error-msg-admin").hide().text("");

    if (modeMaladieAdminActif) {
        $("#maladie_fromAdmin").val("");
        $("#maladie_toAdmin").val("");
        $("#blocMaladieDatesAdmin").show();
        $("#startDateConge").closest(".mb-3").hide();
        $("#endDateConge").closest(".mb-3").hide();
        $("#adminCongeTypes").closest(".mb-3").hide();
        $(this).addClass("active btn-success").removeClass("btn-danger");
    } else {
        $("#startDateConge").val("");
        $("#endDateConge").val("");
        $("#blocMaladieDatesAdmin").hide();
        $("#startDateConge").closest(".mb-3").show();
        $("#endDateConge").closest(".mb-3").show();
        $("#adminCongeTypes").closest(".mb-3").show();
        $(this).removeClass("active btn-success").addClass("btn-danger");
    }
});

$("#startDateConge, #endDateConge, #maladie_fromAdmin, #maladie_toAdmin").on(
    "focus",
    function () {
        this.showPicker && this.showPicker();
    }
);

// -----------------------reset modal admin--------------------

function resetModalAjoutCongeAdmin() {
    $("#agenceSelect").val($("#agenceSelect option:first").val());
    $("#agentSelect")
        .empty()
        .append("<option selected disabled>Choisissez un agent</option>");
    $("#titleConge").val("");
    $("#startDateConge").val("");
    $("#endDateConge").val("");
    $("#maladie_fromAdmin").val("");
    $("#maladie_toAdmin").val("");

    ["conge", "repos", "ferie", "autre"].forEach((type) => {
        $("#admin_" + type).val(0);
    });

    // Repasse en mode classique
    if (modeMaladieAdminActif) {
        $("#btnMaladieToggleAdmin").trigger("click"); // repasse en mode classique si activé
    }

    $("#form-error-msg-admin").hide().text("");
}

// ------------------ajout modal congé admin--------------------

$("#btnOuvrirModalAdminConge").on("click", function () {
    resetModalAjoutCongeAdmin();
    $("#modalAjoutCongeAdmin").modal("show");
    loadAgences();
});

function loadAgences() {
    $.get(window.route.planningResources, function (data) {
        let agences = {};
        data.forEach(function (res) {
            const parts = res.groupId.split("#");
            if (parts.length === 2) {
                agences[parts[1]] = parts[0];
            }
        });
        let $agenceSelect = $("#agenceSelect");
        $agenceSelect
            .empty()
            .append("<option selected disabled>Choisissez une agence</option>");
        for (let id in agences) {
            $agenceSelect.append(
                '<option value="' + id + '">' + agences[id] + "</option>"
            );
        }
        $("#agentSelect")
            .empty()
            .append("<option selected disabled>Choisissez un agent</option>");
    });
}

$("#agenceSelect").on("change", function () {
    let agenceId = $(this).val();
    $.get(window.route.planningResources, function (data) {
        let $agentSelect = $("#agentSelect");
        $agentSelect
            .empty()
            .append("<option selected disabled>Choisissez un agent</option>");
        data.forEach(function (res) {
            if (res.groupId.split("#")[1] == agenceId) {
                $agentSelect.append(
                    '<option value="' +
                        res.id.replace("gent-", "") +
                        '">' +
                        res.title +
                        "</option>"
                );
            }
        });
    });
});

$("#formAjoutCongeAdmin").on("submit", function (e) {
    e.preventDefault();
    $("#form-error-msg-admin").hide();

    const resourceId = $("#agentSelect").val();
    const title = $("#titleConge").val().trim();
    const start = $("#startDateConge").val();
    const end = $("#endDateConge").val();
    const maladie_fromAdmin = $("#maladie_fromAdmin").val();
    const maladie_toAdmin = $("#maladie_toAdmin").val();
    const nbJoursTotal = calculerJoursEntre(start, end);
    const nbJoursSaisis = totalJoursAdmin();

    if (nbJoursTotal !== nbJoursSaisis) {
        $("#form-error-msg-admin")
            .text(
                "⛔ La répartition des jours ne correspond pas à la durée totale (" +
                    nbJoursTotal +
                    " jour(s))."
            )
            .show();
        return;
    }

    let errorMsg = "";
    if (!resourceId) errorMsg += "⛔ Veuillez choisir un agent.\n";
    if (!title) errorMsg += "⛔ Le nom du congé est requis.\n";
    if (modeMaladieAdminActif) {
        if (!maladie_fromAdmin)
            errorMsg += "⛔ La date de début du congé maladie est requise.\n";
        if (!maladie_toAdmin)
            errorMsg += "⛔ La date de fin du congé maladie est requise.\n";
        if (
            maladie_fromAdmin &&
            maladie_toAdmin &&
            new Date(maladie_toAdmin) < new Date(maladie_fromAdmin)
        ) {
            errorMsg +=
                "⛔ La date de fin maladie ne peut pas être avant la date de début.\n";
        }
    } else {
        if (!start) errorMsg += "⛔ La date de début est requise.\n";
        if (!end) errorMsg += "⛔ La date de fin est requise.\n";
        if (start && end && new Date(end) < new Date(start)) {
            errorMsg +=
                "⛔ La date de fin ne peut pas être avant la date de début.\n";
        }
    }

    if (errorMsg !== "") {
        $("#form-error-msg-admin").text(errorMsg).show();
        return;
    }

    let postData;
    if (modeMaladieAdminActif) {
        const endMaladie = new Date(maladie_toAdmin);
        endMaladie.setDate(endMaladie.getDate() + 1);
        const endMaladieFormatted = endMaladie.toISOString().split("T")[0];
        const maladieDays = calculerJoursEntre(
            maladie_fromAdmin,
            maladie_toAdmin
        );

        postData = {
            _token: window.route.csrfToken,
            resourceId: parseInt(resourceId, 10),
            title: title,
            start_date: maladie_fromAdmin,
            end_date: endMaladieFormatted,
            type: "maladie",
            status: "maladie",

            jours_par_type: JSON.stringify({
                conge: 0,
                repos: 0,
                ferie: 0,
                autre: 0,
                maladie: maladieDays,
            }),
        };
    } else {
        const endInclusive = new Date(end);
        endInclusive.setDate(endInclusive.getDate() + 1);
        const endFormatted = endInclusive.toISOString().split("T")[0];

        const joursParType = {};
        ["conge", "repos", "ferie", "autre"].forEach(function (type) {
            const val = parseInt($("#admin_" + type).val()) || 0;
            joursParType[type] = val;
        });
        joursParType.maladie = 0;

        const nbJoursTotal = nbJoursSaisis;
        const nbRepos = joursParType.repos || 0;

        let mainType;
        if (nbJoursTotal === 1 && nbRepos === 1) {
            mainType = "repos";
        } else {
            mainType = "conge";
        }

        postData = {
            _token: window.route.csrfToken,
            resourceId: parseInt(resourceId, 10),
            title: title,
            start_date: start,
            end_date: endFormatted,
            jours_par_type: JSON.stringify(joursParType),
            type: mainType,
        };
    }

    $.ajax({
        url: window.route.planningAdd,
        type: "POST",
        data: postData,
        success: function (response) {
            if (response.success) {
                $("#modalAjoutCongeAdmin").modal("hide");
                $("#formAjoutCongeAdmin")[0].reset();

                calendar.refetchEvents();
                showMessage("✅ Congé ajouté avec succès !");
            } else {
                $("#form-error-msg-admin")
                    .text("⛔ " + response.message)
                    .show();
            }
        },
        error: function (xhr) {
            $("#form-error-msg-admin")
                .text("⛔ Erreur serveur : " + xhr.responseText)
                .show();
        },
    });
});

document
    .getElementById("startDateConge")
    .addEventListener("focus", function () {
        ancienneDateDebutAdmin = this.value;
    });
document.getElementById("endDateConge").addEventListener("focus", function () {
    ancienneDateFinAdmin = this.value;
});

document
    .getElementById("startDateConge")
    .addEventListener("change", autoRemplirCongeAdmin);
document
    .getElementById("endDateConge")
    .addEventListener("change", autoRemplirCongeAdmin);
