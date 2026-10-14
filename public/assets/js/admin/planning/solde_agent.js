let pendingSoldeXhr = null;
let lastSoldeAgentId = null;
let currentPresenceController = null;

// ---------------------------- Jours travaillés depuis janvier ----------------------------
function majJoursTravaillesDepuisJanvier(gentId) {
    // Annule la requête précédente si elle existe
    if (currentPresenceController) {
        currentPresenceController.abort();
    }

    currentPresenceController = new AbortController();
    const signal = currentPresenceController.signal;

    const url = `${
        window.route.planningAttendances
    }?gent_id=${encodeURIComponent(gentId)}&start=${new Date().getFullYear()}-01-01`;

    const el = document.getElementById("presenceCounterYear");
    if (el) el.textContent = "…";

    fetch(url, { credentials: "same-origin", signal })
        .then((res) => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then((dates) => {
            if (gentId !== lastSoldeAgentId) return;

            let listeDates = [];
            if (Array.isArray(dates)) {
                listeDates = dates;
            } else if (typeof dates === "object" && dates !== null) {
                listeDates = Object.values(dates).flat();
            }

            const annee = new Date().getFullYear();
            const debutAnnee = new Date(`${annee}-01-01`);
            const nb = listeDates.filter(
                (d) => new Date(d) >= debutAnnee
            ).length;

            const el = document.getElementById("presenceCounterYear");
            if (el) el.textContent = `${nb}`;
        })
        .catch((err) => {
            if (err.name === "AbortError") return;
            console.error("Erreur présences agent :", err);
        });
}

// ---------------------------- Clic sur le nom d’un agent ----------------------------
$(document).on(
    "click",
    ".fc-datagrid-body td.fc-resource .fc-datagrid-cell-main",
    function () {
        const nom = $(this).text().trim();
        if (!nom || nom === "Agents") return;

        const td = $(this).closest("td.fc-resource")[0];
        const rid = td?.dataset?.resourceId;
        const agentId = rid ? rid.split("-")[1] : null;
        if (!agentId) return;

        $("#soldeAgentName").text(nom);
        $("#soldeAgentId").val(agentId);
        $("#form-error-msg-solde").hide().text("");
        $("#agentSoldeModal").modal("show");

        $(
            "#solde_conge_view, #solde_repos_view, #solde_ferie_view, #solde_autre_view"
        )
            .text("…")
            .removeClass("d-none");
        $("#solde_conge, #solde_repos, #solde_ferie, #solde_autre")
            .val("")
            .addClass("d-none");
        $("#btnSaveSolde, #btnCancelEdit").addClass("d-none");
        $("#btnEditSolde").removeClass("d-none");

        if (pendingSoldeXhr && pendingSoldeXhr.readyState !== 4) {
            pendingSoldeXhr.abort();
        }

        lastSoldeAgentId = agentId;

        majJoursTravaillesDepuisJanvier(agentId);

        pendingSoldeXhr = $.ajax({
            url: window.route.planningAgentCounters,
            method: "GET",
            data: { gent_id: agentId },
            success: function (res) {
                if (agentId !== lastSoldeAgentId) return;

                const b = res?.balances || {};
                $("#solde_conge_view").text(b.conge ?? 0);
                $("#solde_repos_view").text(b.repos ?? 0);
                $("#solde_ferie_view").text(b.ferie ?? 0);
                $("#solde_autre_view").text(b.autre ?? 0);

                $("#solde_conge").val(b.conge ?? 0);
                $("#solde_repos").val(b.repos ?? 0);
                $("#solde_ferie").val(b.ferie ?? 0);
                $("#solde_autre").val(b.autre ?? 0);

                window.updateRightTableDebounced();
            },
            error: function () {
                if (agentId !== lastSoldeAgentId) return;
                $("#form-error-msg-solde")
                    .text("⛔ Impossible de récupérer le solde.")
                    .show();
                $(
                    "#solde_conge_view, #solde_repos_view, #solde_ferie_view, #solde_autre_view"
                ).text("-");
            },
        });
    }
);

// ---------------------------- Mode édition du solde ----------------------------
$("#btnEditSolde").on("click", function () {
    $(
        "#solde_conge_view, #solde_repos_view, #solde_ferie_view, #solde_autre_view"
    ).addClass("d-none");
    $("#solde_conge, #solde_repos, #solde_ferie, #solde_autre").removeClass(
        "d-none"
    );
    $("#btnSaveSolde, #btnCancelEdit").removeClass("d-none");
    $("#btnEditSolde").addClass("d-none");
    $("#form-error-msg-solde").hide().text("");
});

$("#btnCancelEdit").on("click", function () {
    $(
        "#solde_conge_view, #solde_repos_view, #solde_ferie_view, #solde_autre_view"
    ).removeClass("d-none");
    $("#solde_conge, #solde_repos, #solde_ferie, #solde_autre").addClass(
        "d-none"
    );
    $("#btnSaveSolde, #btnCancelEdit").addClass("d-none");
    $("#btnEditSolde").removeClass("d-none");
    $("#form-error-msg-solde").hide().text("");
});

// ---------------------------- Soumission du formulaire ----------------------------
$("#formSoldeAgent").on("submit", function (e) {
    e.preventDefault();

    const agentId = $("#soldeAgentId").val();
    const data = {
        gent_id: agentId,
        conge: $("#solde_conge").val(),
        repos: $("#solde_repos").val(),
        ferie: $("#solde_ferie").val(),
        autre: $("#solde_autre").val(),
        _token: window.route.csrfToken,
    };

    $("#btnSaveSolde").prop("disabled", true);

    $.ajax({
        url: window.route.planningCounterUpdate,
        method: "POST",
        data: data,
        success: function (res) {
            if (!res || !res.success) {
                $("#form-error-msg-solde")
                    .text((res && res.message) || "Erreur inconnue")
                    .show();
                return;
            }

            $("#solde_conge_view").text(data.conge);
            $("#solde_repos_view").text(data.repos);
            $("#solde_ferie_view").text(data.ferie);
            $("#solde_autre_view").text(data.autre);

            $("#btnCancelEdit").trigger("click");

            try {
                const newRestant =
                    res.restant_display != null
                        ? res.restant_display
                        : res.restant != null
                        ? res.restant
                        : null;

                if (newRestant != null) {
                    const row = lastAgentMetricsData.find(
                        (r) => String(r.agent_id) === String(agentId)
                    );
                    if (row) {
                        row.restant =
                            Math.round(parseFloat(newRestant) * 100) / 100;
                        buildTableRows(lastAgentMetricsData);
                    }
                }
            } catch (_) {}

            updateRightTableDebounced();
        },
        error: function () {
            $("#form-error-msg-solde")
                .text("⛔ Erreur lors de la sauvegarde.")
                .show();
        },
        complete: function () {
            $("#btnSaveSolde").prop("disabled", false);
        },
    });
});
