let modeMaladieAdminEditActif = false;

$("#adminStatus").on("change", function () {
    const newStatus = $(this).val();
    if (newStatus === "rejected") {
        $("#adminReasonContainer").show();
    } else {
        $("#adminReasonContainer").hide();
        $("#adminReason").val("");
    }
});
window.selectedEventModificationToken = null;
let typeEvenementEdition = null;

document
    .getElementById("calendar")
    .addEventListener("contextmenu", function (e) {
        const eventEl = e.target.closest(".fc-event");
        if (eventEl) {
            e.preventDefault();

            const eventId = eventEl.getAttribute("data-event-id");
            const event = calendar.getEventById(eventId);
            if (!event) return;

            window.selectedEvent = event;

            window.selectedEventModificationToken =
                event.extendedProps.modification_token || null;

            const ext = event.extendedProps;

            $("#adminStatusContainer").show();
            $("#adminCongeTypesContainer").show();

            if (ext.type === "maladie") {
                modeMaladieAdminEditActif = true;

                $("#adminStatusContainer").hide();
                $("#adminCongeTypesContainer").hide();
                $("#adminReasonContainer").hide();
                $("#adminReason").val("");

                $("#admin_conge_edit").val(0);
                $("#admin_repos_edit").val(0);
                $("#admin_ferie_edit").val(0);
                $("#admin_autre_edit").val(0);

                if (ext.jours_par_type) {
                    try {
                        const jours = JSON.parse(ext.jours_par_type);
                    } catch (e) {
                        console.error("Impossible de parser jours_par_type", e);
                    }
                }
            } else {
                modeMaladieAdminEditActif = false;

                $("#adminStatus").prop("disabled", false);

                if (ext.jours_par_type) {
                    try {
                        const jours = JSON.parse(ext.jours_par_type);
                        $("#admin_conge_edit").val(jours.conge || 0);
                        $("#admin_repos_edit").val(jours.repos || 0);
                        $("#admin_ferie_edit").val(jours.ferie || 0);
                        $("#admin_autre_edit").val(jours.autre || 0);
                    } catch (e) {}
                }
            }

            $("#adminAgentName").text(ext.agent_name);
            $("#adminReasonConge").text(event.title);

            const start = new Date(event.start);
            start.setDate(start.getDate() + 1);
            $("#adminStartDate").val(start.toISOString().split("T")[0]);

            const end = new Date(event.end);
            end.setDate(end.getDate());
            $("#adminEndDate").val(end.toISOString().split("T")[0]);

            let status = ext.status;
            if (status === "pending") {
                status = "approved";
            }

            $("#adminStatus").val(status);

            if (!$("#adminStatus").val()) {
                $("#adminStatus").val("approved");
            }

            const reason = ext.rejection_reason ?? "";
            $("#adminReason").val(reason);

            if (status === "rejected") {
                $("#adminReasonContainer").show();
            } else {
                $("#adminReasonContainer").hide();
                $("#adminReason").val("");
            }

            if (ext.jours_par_type) {
                try {
                    const jours = JSON.parse(ext.jours_par_type);
                    $("#admin_conge_edit").val(jours.conge || 0);
                    $("#admin_repos_edit").val(jours.repos || 0);
                    $("#admin_ferie_edit").val(jours.ferie || 0);
                    $("#admin_autre_edit").val(jours.autre || 0);
                } catch (e) {}
            }

            if (ext.type === "maladie") {
                typeEvenementEdition = "maladie";
                $("#adminStatus").val("maladie").prop("disabled", true);
                $("#adminStatusContainer").hide();
                $("#adminCongeTypesContainer").hide();
                $("#adminReasonContainer").hide();
                $("#adminReason").val("");
            } else {
                typeEvenementEdition = "conge";
                $("#adminStatus").prop("disabled", false);
                $("#adminStatusContainer").show();
                $("#adminCongeTypesContainer").show();
            }

            $("#form-error-msg-admin-update").hide().text("");
            $("#adminManageModal").modal("show");
        }
    });

$("#editReasonBtn").on("click", function () {
    $("#adminReason").prop("readonly", false).focus();
});

$("#adminManageForm").on("submit", function (e) {
    e.preventDefault();

    $("#form-error-msg-admin-update").hide().text("");

    const start = $("#adminStartDate").val();
    const end = $("#adminEndDate").val();
    let errorMsg = "";

    if (start && end && new Date(end) < new Date(start)) {
        errorMsg +=
            "⛔ La date de fin ne peut pas être avant la date de début.\n";
    }

    if (typeEvenementEdition !== "maladie") {
        const nbJoursTotal = calculerJoursEntre(start, end);
        const nbJoursSaisis =
            (parseInt($("#admin_conge_edit").val()) || 0) +
            (parseInt($("#admin_repos_edit").val()) || 0) +
            (parseInt($("#admin_ferie_edit").val()) || 0) +
            (parseInt($("#admin_autre_edit").val()) || 0);

        if (nbJoursTotal !== nbJoursSaisis) {
            errorMsg +=
                "⛔ La répartition des jours ne correspond pas à la durée totale (" +
                nbJoursTotal +
                " jour(s)).\n";
        }
    }

    if (errorMsg !== "") {
        $("#form-error-msg-admin-update").text(errorMsg).show();
        return;
    }

    let joursParType = {};
    let mainType = "conge";

    if ($("#adminStatus").val() === "rejected") {
        // En cas de refus, on ne décompte aucun jour
        joursParType = {
            conge: 0,
            repos: 0,
            ferie: 0,
            autre: 0,
            maladie: 0,
        };

        // On garde un type neutre pour éviter tout conflit backend
        mainType = "conge";
    } else if ($("#adminStatus").val() === "maladie") {
        // Cas maladie : on compte tous les jours ouvrés entre start et end
        const nbJoursMaladie = calculerJoursEntre(start, end);
        joursParType = {
            conge: 0,
            repos: 0,
            ferie: 0,
            autre: 0,
            maladie: nbJoursMaladie,
        };

        mainType = "maladie";
    } else {
        // Cas normal : on lit les valeurs saisies dans le formulaire
        joursParType = {
            conge: parseInt($("#admin_conge_edit").val()) || 0,
            repos: parseInt($("#admin_repos_edit").val()) || 0,
            ferie: parseInt($("#admin_ferie_edit").val()) || 0,
            autre: parseInt($("#admin_autre_edit").val()) || 0,
            maladie: 0,
        };

        const nbJoursSaisis =
            joursParType.conge +
            joursParType.repos +
            joursParType.ferie +
            joursParType.autre;

        if (nbJoursSaisis === 1 && joursParType.repos === 1) {
            mainType = "repos";
        } else {
            mainType = "conge";
        }
    }

    const id = selectedEvent.id;
    const endInput = new Date($("#adminEndDate").val());
    endInput.setDate(endInput.getDate() + 1);
    const endFormatted = endInput.toISOString().split("T")[0];

    $.ajax({
        url: window.route.planningUpdateAdmin.replace(":id", id),
        method: "POST",
        data: {
            _token: window.route.csrfToken,
            start_date: $("#adminStartDate").val(),
            end_date: endFormatted,
            status: $("#adminStatus").val(),
            reason: $("#adminReason").val(),
            modification_token: selectedEventModificationToken,
            jours_par_type: JSON.stringify(joursParType),
            type: mainType,
        },
        success: function (response) {
            if (response.success) {
                selectedEvent.setStart(response.start_date);
                selectedEvent.setEnd(response.end_date);
                selectedEvent.setExtendedProp("status", response.status);
                if (response.type) {
                    selectedEvent.setExtendedProp("type", response.type);
                }
                selectedEvent.setExtendedProp(
                    "rejection_reason",
                    response.reason
                );

                if (response.jours_par_type) {
                    selectedEvent.setExtendedProp(
                        "jours_par_type",
                        response.jours_par_type
                    );
                }

                if (response.modification_token) {
                    selectedEvent.setExtendedProp(
                        "modification_token",
                        response.modification_token
                    );
                    selectedEventModificationToken =
                        response.modification_token;
                }

                let color;

                // ✅ Priorité 1 : statut "rejected" → rouge
                if (response.status === "rejected") {
                    color = "red";
                }
                // ✅ Priorité 2 : selon le type, uniquement si non refusé
                else if (response.type === "maladie") {
                    color = "#f48bcc"; // rose
                } else if (response.type === "repos") {
                    color = "green"; // vert
                } else if (response.type === "conge") {
                    color = "#27e6e6"; // bleu
                } else {
                    color = "orange"; // par défaut (pending, autre, etc.)
                }

                // ✅ Application immédiate
                selectedEvent.setProp("backgroundColor", color);
                selectedEvent.setExtendedProp("status", response.status);

                $("#adminManageModal").modal("hide");
                showMessage("✅ Congé mis à jour avec succès !");
            } else if (response.concurrency_error) {
                $("#adminManageModal").modal("hide");
                showMessage(
                    "⛔ " +
                        (response.message ||
                            "Ce congé a déjà été modifié ou supprimé. Merci de rafraîchir la page.")
                );
            } else {
                $("#adminManageModal").modal("hide");
                showMessage("⛔ Erreur inconnue lors de la modification.");
            }
        },
        error: function (xhr) {
            if (
                xhr.status === 409 &&
                xhr.responseJSON &&
                xhr.responseJSON.concurrency_error
            ) {
                $("#adminManageModal").modal("hide");
                showMessage(
                    "⛔ " +
                        (xhr.responseJSON.message ||
                            "Ce congé a déjà été modifié ou supprimé. Merci de rafraîchir la page.")
                );
            } else if (xhr.status === 404) {
                $("#adminManageModal").modal("hide");
                showMessage(
                    "⛔ Ce congé a déjà été supprimé ou n'existe plus."
                );
            } else {
                alert("Erreur lors de la mise à jour : " + xhr.responseText);
            }
        },
    });
});

//    ------------------- delette admin btn-------------------------

$("#adminDeleteBtn").on("click", function () {
    if (!selectedEvent) return;

    if (!confirm("Êtes-vous sûr de vouloir supprimer ce congé ?")) return;

    const resourceId = parseInt(String(selectedEvent.id).split("-")[0], 10);

    $.ajax({
        url: window.route.planningDelete.replace(":id", resourceId),
        method: "POST",
        data: {
            _token: window.route.csrfToken,
            _method: "DELETE",
            id: resourceId,
            modification_token: selectedEventModificationToken,
        },
        success: function (response) {
            selectedEvent.remove();
            $("#adminManageModal").modal("hide");
            showMessage("✅ Congé supprimé avec succès !");
        },
        error: function (xhr) {
            if (
                (xhr.status === 409 &&
                    xhr.responseJSON &&
                    xhr.responseJSON.concurrency_error) ||
                xhr.status === 404
            ) {
                showMessage(
                    "⛔ " +
                        ((xhr.responseJSON && xhr.responseJSON.message) ||
                            "Ce congé a déjà été modifié ou supprimé. Merci de rafraîchir la page.")
                );
                $("#adminManageModal").modal("hide");
            } else {
                alert("Erreur lors de la suppression : " + xhr.responseText);
            }
        },
    });
});

document
    .getElementById("adminStartDate")
    .addEventListener("change", autoRemplirCongeAdminEdit);
document
    .getElementById("adminEndDate")
    .addEventListener("change", autoRemplirCongeAdminEdit);
