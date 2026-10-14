$("input[name='options[]']").on("change", function () {
    if ($(this).is(":checked")) {
        $("input[name='options[]']").not(this).prop("checked", false);
    }
});

// checkbox Devis/vente
function updateIsVente() {
    const isVenteChecked = $("#Vente").is(":checked"); // Vérifie si "Vente" est cochée
    $("#is_vente").val(isVenteChecked ? 1 : 0); // Met à jour la valeur cachée
    console.log(
        "Valeur actuelle de is_vente après changement :",
        $("#is_vente").val()
    );
}

$("#Vente").on("change", updateIsVente);
updateIsVente();

// save la description
$("#save_description").click(function (event) {
    event.preventDefault();
    $(".spinner-overlay").css("display", "flex");

    // Récupérer l'ID du devis à partir du champ caché
    let estimateId = $("#client_vol_estimate_id").val();
    let comment = $("#comment").val();
    let isVente = $("#is_vente").val();
    let clientId = $("#client_id").val();
    let marge = $("#marge").val();

    if (!estimateId) {
        showErrorToast("Veuillez d'abord créer un devis");
        $(".spinner-overlay").css("display", "none");
        return;
    }

    let provenance = [];
    $("input[name='provenance[]']:checked").each(function () {
        provenance.push($(this).val());
    });

    let prestation = [];
    $("input[name='options[]']:checked").each(function () {
        prestation.push($(this).val());
    });

    if (provenance.length === 0) {
        showErrorToast("Veuillez cocher au moins une option de provenance.");
        $(".spinner-overlay").css("display", "none");
        return;
    }

    if (prestation.length === 0) {
        showErrorToast("Veuillez cocher au moins une option de prestation.");
        $(".spinner-overlay").css("display", "none");
        return;
    }

    if (!marge || isNaN(marge) || marge <= 0) {
        showErrorToast("Veuillez entrer une marge valide (supérieure à 0).");
        $(".spinner-overlay").css("display", "none");
        return;
    }

    if (comment.trim() === "") {
        showErrorToast("Veuillez saisir un commentaire.");
        $(".spinner-overlay").css("display", "none");
        return;
    }

    provenance = provenance.join(", ");
    prestation = prestation.join(", ");

    $("#description-message-container").html("");

    // envoyer `comment` et `is_vente`
    $.ajax({
        url: route.addDevis,
        type: "POST",
        data: {
            client_vol_estimate_id: estimateId,
            comment: comment,
            is_vente: isVente,
            marge: marge,
            _token: $('meta[name="csrf-token"]').attr("content"),
        },
        success: function (response) {
            if (response.status === 200) {
                if (clientId) {
                    $.ajax({
                        url: route.addClient,
                        type: "POST",
                        data: {
                            client_id: clientId,
                            provenance: provenance,
                            _token: $('meta[name="csrf-token"]').attr(
                                "content"
                            ),
                        },
                        success: function (response) {
                            if (response.status === 200) {
                                // Si les deux requêtes sont réussies (success)
                                showValideToast(
                                    "Description enregistrée avec succès."
                                );
                            } else {
                                showErrorToast(response.message);
                            }
                            $(".spinner-overlay").css("display", "none");
                        },
                        error: function (xhr) {
                            showErrorToast(serverError(xhr, "Erreur lors de la mise à jour de la provenance."));
                            $(".spinner-overlay").css("display", "none");

                            console.error(
                                "Erreur lors de la mise à jour de la provenance",
                                xhr.responseText
                            );
                        },
                    });
                }
                $(".btn-save-description").prop("disabled", true);
            } else {
                showErrorToast(response.message);
                $(".spinner-overlay").css("display", "none");
            }
        },
        error: function (xhr) {
            showErrorToast(serverError(xhr, "Erreur lors de la mise à jour du commentaire et de l'état de vente."));
            console.error(
                "Erreur lors de la mise à jour du commentaire et de l'état de vente",
                xhr.responseText
            );
            $(".spinner-overlay").css("display", "none");
        },
    });
});