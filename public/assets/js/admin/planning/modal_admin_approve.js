// -------------------approuve modal------------------

$("#confirmApproveBtn").on("click", function () {
    if (!selectedEvent) return;

    const modificationToken = selectedEvent.extendedProps.modification_token;

    if (!modificationToken) {
        showMessage(
            "⛔ Impossible de valider ce congé : le token est manquant."
        );
        return;
    }

    $.ajax({
        url: window.route.planningApprove.replace(":id", selectedEvent.id),

        method: "POST",
        data: {
            _token: window.route.csrfToken,
            modification_token: modificationToken,
        },
        success: function (response) {
            if (response.success) {
                // Mets à jour status et type
                selectedEvent.setExtendedProp("status", "approved");
                if (response.type) {
                    selectedEvent.setExtendedProp("type", response.type);
                }

                let type = response.type || selectedEvent.extendedProps.type;
                if (type === "conge") {
                    selectedEvent.setProp("backgroundColor", "#27e6e6"); // cyan direct
                } else if (type === "repos") {
                    selectedEvent.setProp("backgroundColor", "green");
                } else if (type === "maladie") {
                    selectedEvent.setProp("backgroundColor", "#f48bcc");
                } else {
                    selectedEvent.setProp("backgroundColor", "orange");
                }

                if (response.modification_token) {
                    selectedEvent.setExtendedProp(
                        "modification_token",
                        response.modification_token
                    );
                }

                $("#approveModal").modal("hide");
                showMessage("✅ Congé approuvé avec succès !");
            }
        },
        error: function (xhr) {
            const res = xhr.responseJSON;

            if (
                xhr.status === 404 ||
                (res && res.message?.includes("supprimé"))
            ) {
                showMessage(
                    "⛔ Ce congé a été supprimé. Veuillez réactualiser la page."
                );
            } else if (res && res.concurrency_error) {
                showMessage(
                    res.message ||
                        "⛔ Ce congé a déjà été modifié. Veuillez réactualiser la page."
                );
            } else {
                alert("Erreur lors de la validation.");
                console.log(xhr.responseText);
            }
        },
    });
});

$("#openRejectModal").on("click", function () {
    $("#approveModal").modal("hide");
    $("#rejectionReason").val("");
    $("#rejectModal").modal("show");
});

$("#rejectForm").on("submit", function (e) {
    e.preventDefault();

    const reason = $("#rejectionReason").val().trim();

    $.ajax({
        url: window.route.planningReject.replace(":id", selectedEvent.id),
        method: "POST",
        data: {
            _token: window.route.csrfToken,
            reason: reason,
            modification_token: selectedEventModificationToken,
        },
        success: function (response) {
            if (response.success) {
                selectedEvent.setProp("backgroundColor", "red");
                selectedEvent.setExtendedProp("status", "rejected");
                selectedEvent.setExtendedProp(
                    "modification_token",
                    response.modification_token
                );
                selectedEventModificationToken = response.modification_token;
                $("#rejectModal").modal("hide");
                showMessage("✅ Congé refusé avec succès !");
            }
        },
        error: function (xhr) {
            console.error(xhr.responseText);
            alert("Erreur lors du refus.");
        },
    });
});
